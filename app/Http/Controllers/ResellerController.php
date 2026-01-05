<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\AccurateGlobal;
use App\Models\AccurateAccount;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\Accurate\AccurateClient;
use App\Services\Accurate\SessionResolver;

class ResellerController extends Controller
{
    private function fetchItemsForList(Request $request)
    {
        $acc     = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $perPage = 10;
        $pageWeb = max(1, (int) $request->query('page', 1));
        $offset  = ($pageWeb - 1) * $perPage;

        $search     = trim($request->query('search', ''));
        $categoryId = $request->query('category_id');
        $stokAda    = $request->query('stok_ada', '1');
        $priceMode  = $request->query('price_mode', 'default');

        $minPrice = $request->filled('min_price')
            ? floatval(str_replace(['.', ','], ['', '.'], $request->input('min_price')))
            : null;

        $maxPrice = $request->filled('max_price')
            ? floatval(str_replace(['.', ','], ['', '.'], $request->input('max_price')))
            : null;

        $usePriceFilter = ($minPrice !== null || $maxPrice !== null);
        $priceCategory  = $priceMode === 'reseller' ? 'RESELLER' : 'RESELLER';

        // ----------------------------------------------------
        //       HYBRID SMART SCAN CONFIG (AMAN)
        // ----------------------------------------------------
        $targetBase = $offset + $perPage + 1;
        $targetDeep = $perPage;
        $deepStep   = 50;
        $maxLimit   = 1000;

        $buffer      = collect();
        $rawScanned  = 0;
        $pageAcc     = 1;

        $rowsNeeded = $targetBase; // FIX: variabel wajib

        // ----------------------------------------------------
        //     HELPER FILTER ( harga + stok + push buffer )
        // ----------------------------------------------------
        $processRow = function (&$buffer, $row) use (
            $stokAda, $usePriceFilter, $minPrice, $maxPrice,
            $token, $session, $priceCategory
        ) {

            if ($stokAda === '1' && ($row['availableToSell'] ?? 0) <= 0) {
                return false;
            }

            if ($usePriceFilter) {
                $price = $this->getPriceGlobal($row['id'], $token, $session, $priceCategory);

                if ($minPrice !== null && $price < $minPrice) return false;
                if ($maxPrice !== null && $price > $maxPrice) return false;

                $row['price'] = $price;
            }

            $buffer->push($row);
            return true;
        };

        // ----------------------------------------------------
        //                     BASE SCAN
        // ----------------------------------------------------
        while ($buffer->count() < $rowsNeeded && $rawScanned < $maxLimit) {

            $query = [
                'sp.page'         => $pageAcc,
                'sp.pageSize'     => 100,
                'fields'          => 'id,name,no,availableToSell,itemCategory.name,availableToSellInAllUnit,detailItemImage',
                'filter.suspended'=> false,
            ];

            // SEARCH (CONTAIN)
            if ($search !== '') {
                $query['filter.keywords.op']      = 'CONTAIN';
                $query['filter.keywords.val[0]']  = $search;
            }

            // CATEGORY FILTER
            if (!empty($categoryId)) {
                $query['filter.itemCategoryId'] = $categoryId;
            }

            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->timeout(10)->get("$baseUrl/item/list.do", $query);

            if (!$resp->successful()) break;

            $json  = $resp->json();
            $rows  = collect($json['d'] ?? []);
            $rawScanned += $rows->count();

            if ($rows->isEmpty()) break;

            foreach ($rows as $row) {
                $processRow($buffer, $row);
                if ($buffer->count() >= $rowsNeeded) break;
            }

            $pageCount = $json['sp']['pageCount'] ?? null;
            if ($pageCount && $pageAcc >= $pageCount) break;

            $pageAcc++;
        }

        // ----------------------------------------------------
        //            DEEP SCAN (Jika stok jarang)
        // ----------------------------------------------------
        if ($buffer->count() < $offset + $perPage) {

            $extraPages = 1;

            while (
                $buffer->count() < ($offset + $perPage + $targetDeep)
                && $rawScanned < $maxLimit
            ) {

                $startPage = $pageAcc + $extraPages;

                for ($p = $startPage; $p < $startPage + 2; $p++) {

                    if ($rawScanned >= $maxLimit) break 2;

                    $query = [
                        'sp.page'         => $p,
                        'sp.pageSize'     => 100,
                        'fields'          => 'id,name,no,availableToSell,itemCategory.name,availableToSellInAllUnit,detailItemImage',
                        'filter.suspended'=> false,
                    ];

                    if ($search !== '') {
                        $query['filter.keywords.op']      = 'CONTAIN';
                        $query['filter.keywords.val[0]']  = $search;
                    }

                    if (!empty($categoryId)) {
                        $query['filter.itemCategoryId'] = $categoryId;
                    }

                    $resp = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'X-Session-ID'  => $session,
                    ])->timeout(10)->get("$baseUrl/item/list.do", $query);

                    if (!$resp->successful()) break 2;

                    $json = $resp->json();
                    $rows = collect($json['d'] ?? []);
                    if ($rows->isEmpty()) break 2;

                    $rawScanned += $rows->count();

                    foreach ($rows as $row) {
                        $processRow($buffer, $row);
                        if ($buffer->count() >= ($offset + $perPage + $targetDeep)) break 3;
                    }
                }

                $extraPages += 2;
            }
        }

        // ----------------------------------------------------
        //             PAGINATION RESULT
        // ----------------------------------------------------
        $totalFiltered = $buffer->count();
        $items         = $buffer->slice($offset, $perPage)->values();
        $hasMore       = $totalFiltered > ($offset + $items->count());

        // Gambar + encrypted ID
        $items = $items->map(function ($item) {
            $item['fileName'] = collect($item['detailItemImage'] ?? [])
                ->pluck('fileName')->filter()->values()->toArray();

            $item['encryptedId'] = Hashids::encode($item['id']);
            return $item;
        });

        return [
            'items'      => $items,
            'page'       => $pageWeb,
            'pageCount'  => $hasMore ? $pageWeb + 1 : $pageWeb,
            'totalItems' => $totalFiltered,
            'filters'    => compact('search', 'categoryId', 'stokAda', 'minPrice', 'maxPrice', 'priceMode'),
        ];
    }

    // ===================================================
    //                        INDEX
    // ===================================================
    public function index2(Request $request)
    {
        $data = $this->fetchItemsForList($request);

        // CACHE CATEGORY (tidak bikin 503)
        $categories = Cache::remember("accurate:categories:global", 86400, function () {
            $acc = AccurateGlobal::token();
            $token   = $acc['access_token'];
            $session = $acc['session_id'];
            $baseUrl = rtrim(config('services.accurate.base_api'), '/');

            $cats = collect();
            $page = 1;

            do {
                $resp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Session-ID'  => $session,
                ])->timeout(10)->get("$baseUrl/item-category/list.do", [
                    'sp.page'     => $page,
                    'sp.pageSize' => 100,
                    'fields'      => 'id,name,parent',
                ]);

                if (!$resp->successful()) break;

                $json = $resp->json();
                $cats = $cats->merge($json['d'] ?? []);

                $page++;
                $pageCount = $json['sp']['pageCount'] ?? 1;

            } while ($page <= $pageCount);

            return $cats->values();
        });

        return view('reseller.index', [
            'items'      => $data['items'],
            'page'       => $data['page'],
            'pageCount'  => $data['pageCount'],
            'totalItems' => $data['totalItems'],
            'categories' => $categories,
            'filters'    => $data['filters'],
        ]);
    }

    // ===================================================
    //                 PRICE API (AMAN)
    // ===================================================
    private function getPriceGlobal($itemId, $token, $session, $priceCategory = 'RESELLER')
    {
        try {
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->timeout(8)->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
                'id'                 => $itemId,
                'priceCategoryName'  => $priceCategory,
            ]);

            if (!$resp->successful()) return 0;

            $data = $resp->json()['d'] ?? [];

            return $data['unitPrice']
                ?? ($data['unitPriceRule'][0]['price'] ?? 0);

        } catch (\Throwable $e) {
            return 0;
        }
    }

    // ===================================================
    //                  AJAX PRICE
    // ===================================================
    public function ajaxPriceReseller(Request $request)
    {
        $id = $request->query('id');
        $mode = $request->query('RESELLER', 'RESELLER');

        if (!$id) {
            return response()->json(['price' => 0]);
        }

        // 🟩 KEY cache unik per item + mode harga
        $cacheKey = "price:{$id}:{$mode}";

        // 🟦 CEK CACHE DULU (24 jam)
        if (Cache::has($cacheKey)) {
            return response()->json([
                'price' => Cache::get($cacheKey),
                'cache' => true,
            ]);
        }

        $acc     = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];

        try {
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])
            ->timeout(8)
            ->retry(3, 300)
            ->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
                'id'                 => $id,
                'priceCategoryName'  => $mode,
            ]);

            if (!$resp->successful()) {
                return response()->json(['price' => 0]);
            }

            $data = $resp->json()['d'] ?? [];
            $price = $data['unitPrice']
                ?? ($data['unitPriceRule'][0]['price'] ?? 0);

            Cache::put($cacheKey, $price, now()->addMinutes(1));

            return response()->json(['price' => $price, 'cache' => false,]);

        } catch (\Throwable $e) {
            return response()->json(['price' => 0]);
        }
    }

    private $unitMap = [
        '1'       => 52850,
        'BATANG'  => 53550,
        'BOX'     => 53950,
        'BTL'     => 53200,
        'CAM'     => 53450,
        'DUS'     => 53300,
        'HPP'     => 52950,
        'IKAT'    => 53400,
        'KALENG'  => 53600,
        'KARUNG'  => 53700,
        'KG'      => 53900,
        'KLG'     => 53350,
        'METER'   => 52701,
        'MTR'     => 52750,
        'PACK'    => 53000,
        'PAJAK'   => 53750,
        'PAKET'   => 53100,
        'PCH'     => 53151,
        'PCS'     => 50,
        'POTONG'  => 53500,
        'RIT'     => 53650,
        'ROLL'    => 52900,
        'SAK'     => 53150,
        'SET'     => 53800,
        'UNIT'    => 53050,
        'RIM'     => 53850,
    ];

    private function getPriceByUnitId($data, $unitId)
    {
        if (!isset($data['unitPriceRule'])) {
            return $data['unitPrice'] ?? 0;
        }

        foreach ($data['unitPriceRule'] as $rule) {
            if ((int)$rule['unitId'] === (int)$unitId) {
                return $rule['price'];
            }
        }

        // fallback
        return $data['unitPrice'] ?? ($data['unitPriceRule'][0]['price'] ?? 0);
    }



    public function show($encrypted, Request $request)
    {
            // 🔹 Decode ID dari Hashid
        $decoded = Hashids::decode($encrypted);
        $id = $decoded[0] ?? null;

        if (!$id) {
            abort(404, 'ID item tidak valid');
        }

        // ============================================================
        // 🔹 Ambil token & session dari AccurateGlobal
        // ============================================================
        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];
        $branchName = $request->input('branchName');

        $baseUrl = 'https://public.accurate.id/accurate/api';

        // 🔹 Ambil detail item
        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->timeout(60)->retry(3, 300)->get("{$baseUrl}/item/detail.do", ['id' => $id]);

        $item = $resp->json()['d'] ?? null;
        if (!$item) {
            return back()->with('error', 'Gagal mengambil data item dari Accurate.');
        }

        $fileName = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray();

        // =============================
        // UNIT ID berdasar gudang pertama
        // =============================
        $firstWH = $item['detailWarehouseData'][0] ?? null;

        $unitId = 50; // default PCS

        if ($firstWH) {
            // balanceUnit: "6 PCS"
            $rawUnit = explode(' ', $firstWH['balanceUnit'] ?? '');
            $unitName = strtoupper($rawUnit[1] ?? 'PCS');

            // cocokkan ke map
            if (isset($this->unitMap[$unitName])) {
                $unitId = $this->unitMap[$unitName];
            }
        }

        // =============================
        // HARGA USER
        // =============================
        $defaultResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
            'id'         => $id,
            'branchName' => $branchName,
        ]);

        $userData = $defaultResp['d'] ?? [];
        $userPrice = $this->getPriceByUnitId($userData, $unitId);

        // apply discount
        if (isset($userData['discountRule'][0]['discount'])) {
            $disc = floatval($userData['discountRule'][0]['discount']);
            $userPrice -= ($userPrice * $disc / 100);
        }

        // =============================
        // HARGA RESELLER
        // =============================
        $resellerResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
            'id'                  => $id,
            'priceCategoryName'   => 'RESELLER',
            'discountCategoryName'=> 'RESELLER',
            'branchName'          => $branchName,
        ]);

        $resellerData = $resellerResp['d'] ?? [];
        $resellerPrice = $this->getPriceByUnitId($resellerData, $unitId);

        // apply discount
        if (isset($resellerData['discountRule'][0]['discount'])) {
            $disc = floatval($resellerData['discountRule'][0]['discount']);
            $resellerPrice -= ($resellerPrice * $disc / 100);
        }

        $prices = [
            'user'     => $userPrice,
            'reseller' => $resellerPrice,
        ];

        // =============================================
        // AMBIL SEMUA HARGA PER UNIT & DISKON PER UNIT
        // =============================================
        $unitPrices = [];

        if (isset($userData['unitPriceRule'])) {
            foreach ($userData['unitPriceRule'] as $r) {
                $unitId = $r['unitId'];
                $price  = $r['price'];        
                $unitName = array_search($unitId, $this->unitMap, true) ?? $unitId;

                $unitPrices[$unitName]['user'] = $price;
            }
        }

        if (isset($resellerData['unitPriceRule'])) {
            foreach ($resellerData['unitPriceRule'] as $r) {
                $unitId = $r['unitId'];
                $price  = $r['price'];
                $unitName = array_search($unitId, $this->unitMap, true) ?? $unitId;

                $unitPrices[$unitName]['reseller'] = $price;
            }
        }

        // apply discount per-unit (jika ada)
        foreach ($unitPrices as $unit => &$p) {
            if (isset($userData['discountRule'][0]['discount']) && isset($p['user'])) {
                $disc = floatval($userData['discountRule'][0]['discount']);
                $p['user'] -= ($p['user'] * $disc / 100);
            }
            if (isset($resellerData['discountRule'][0]['discount']) && isset($p['reseller'])) {
                $disc = floatval($resellerData['discountRule'][0]['discount']);
                $p['reseller'] -= ($p['reseller'] * $disc / 100);
            }
        }

        $hasMultiUnitPrices = count($unitPrices) > 1;


        // // ============================================================
        // // 🔹 Ambil dua harga: USER & RESELLER
        // // ============================================================
        // $prices = [];

        // // Harga USER
        // $defaultResp = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . $token,
        //     'X-Session-ID'  => $session,
        // ])->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
        //     'id' => $id,
        //     'branchName' => $branchName, // 🔹 tambahan penting
        // ]);

        
        // $userPrice = $defaultResp['d']['unitPrice']
        // ?? ($defaultResp['d']['unitPriceRule'][0]['price'] ?? 0);

        // $discountRule = $defaultResp['d']['discountRule'][0] ?? null;
        // if ($discountRule && isset($discountRule['discount']) && is_numeric($discountRule['discount'])) {
        //     $discountPercent = floatval($discountRule['discount']);
        //     $userPrice -= ($userPrice * $discountPercent / 100);
        // }
        
        // $prices['user'] = $userPrice;

        // // Harga RESELLER
        // $resellerResp = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . $token,
        //     'X-Session-ID'  => $session,
        // ])->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
        //     'id' => $id,
        //     'priceCategoryName' => 'RESELLER',
        //     'discountCategoryName' => 'RESELLER',
        //     'branchName' => $branchName, // 🔹 tambahan penting
        // ]);

        // $resellerPrice = $resellerResp['d']['unitPrice']
        //     ?? ($resellerResp['d']['unitPriceRule'][0]['price'] ?? 0);

        // // Diskon kalau ada
        // $discountRule = $resellerResp['d']['discountRule'][0] ?? null;
        // if ($discountRule && isset($discountRule['discount']) && is_numeric($discountRule['discount'])) {
        //     $discountPercent = floatval($discountRule['discount']);
        //     $resellerPrice -= ($resellerPrice * $discountPercent / 100);
        // }
        // $prices['reseller'] = $resellerPrice;

        /** ---------------------------------------------
         * 5. GUDANG AWAL (belum realtime)
         * --------------------------------------------- */
        $warehouses = collect($item['detailWarehouseData'] ?? [])
        ->map(function ($wh) {
            $unitParts = explode(' ', $wh['balanceUnit'] ?? '');
            $wh['unit'] = $unitParts[1] ?? null;
            return $wh;
        });

        /** ---------------------------------------------
         * 6. GROUP GUDANG
         * --------------------------------------------- */
        $groups = [
            'store' => [
                'TSTORE KAYUTANGI','TSTORE BANJARBARU A. YANI','TSTORE BANJARBARU P. BATUR',
                'TSTORE BELITUNG','TSTORE MARTAPURA','TDC','STORE PALANGKARAYA','LANDASAN ULIN',
            ],
            'tsc' => [
                'TSC BANJARBARU A. YANI','TSC BANJARBARU P. BATUR','TSC BELITUNG','TSC KAYUTANGI',
                'TSC LANDASAN ULIN','TSC MARTAPURA','TSC PALANGKARAYA',
            ],
            'panda' => [
                'PANDA STORE BANJARBARU','PANDA SC BANJARBARU',
            ],
            'reseller' => [
                'RESELLER ZAKI','RESELLER MARDANI',
            ],
        ];

        /** ---------------------------------------------
         * 7. FILTER GUDANG PER KELOMPOK
         * --------------------------------------------- */
        foreach ($groups as $key => $names) {
            ${"warehouses" . ucfirst($key)} = $warehouses->filter(fn($w) =>
                in_array(strtoupper($w['name'] ?? ''), $names)
            )->values();
        }

        $warehousesKonsinyasi = $warehouses->filter(function($w){
            return isset($w['description']) 
                && Str::contains(strtolower($w['description']), 'konsinyasi');
        })->values();

        /** ---------------------------------------------
         * 8. PROSES UNIT (balanceUnit parsing)
         * --------------------------------------------- */
        $processUnit = function ($collection) {
            return $collection->map(function ($wh) {

            $raw = trim($wh['balanceUnit'] ?? '');

            if ($raw === '') {
                $wh['unit_display'] = '';
                return $wh;
            }

            // Ambil angka depan
            preg_match('/^([\d.,]+)/', $raw, $m);
                $first = isset($m[1])
                    ? (float) str_replace(',', '.', str_replace('.', '', $m[1]))
                    : null;

                $balance = isset($wh['balance']) ? (float)$wh['balance'] : $first;

                // Ambil semua unit yg muncul (PCS, BOX, ROLL, METER, dll)
                preg_match_all('/\b([A-Za-z]+)\b/', $raw, $units);

                // Jika ada lebih dari 1 unit → tampil RAW
                if (count($units[1]) > 1) {
                    $wh['unit_display'] = $raw;
                    return $wh;
                }

                // Jika angka depan tidak sama dengan balance → tampil RAW
                if ($first !== null && abs($first - $balance) > 0.0001) {
                    $wh['unit_display'] = $raw;
                    return $wh;
                }

                // Jika cuma 1 unit → tampil unit saja (tanpa angka)
                $unitOnly = preg_replace('/^[\d.,]+\s+/', '', $raw);
                $wh['unit_display'] = strtoupper($unitOnly);

                return $wh;
            });
        };

        // Apply processing
        $warehousesStore      = $processUnit($warehousesStore);
        $warehousesTsc        = $processUnit($warehousesTsc);
        $warehousesReseller   = $processUnit($warehousesReseller);
        $warehousesKonsinyasi = $processUnit($warehousesKonsinyasi);
        $warehousesPanda      = $processUnit($warehousesPanda);

        /** ---------------------------------------------
         * 9. HILANGKAN STOK 0
         * --------------------------------------------- */
        $warehousesStore      = $warehousesStore->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
        $warehousesTsc        = $warehousesTsc->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
        $warehousesReseller   = $warehousesReseller->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
        $warehousesKonsinyasi = $warehousesKonsinyasi->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
        $warehousesPanda      = $warehousesPanda->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
        

        return view('reseller.detail', [
            'item'       => $item,
            'images'     => $fileName,
            'session'    => $session,
            'branchName' => $branchName,
            'prices' => [
                'user'     => $userPrice,
                'reseller' => $resellerPrice,
            ],
            'unitPrices' => $unitPrices,
            'hasMultiUnitPrices' => $hasMultiUnitPrices,
            // Kirim ke Blade
            'warehousesStore'      => $warehousesStore,
            'warehousesTsc'        => $warehousesTsc,
            'warehousesReseller'   => $warehousesReseller,
            'warehousesKonsinyasi' => $warehousesKonsinyasi,
            'warehousesPanda'      => $warehousesPanda,
        ]);
    }

    public function getPrice(Request $request, $id)
    {
        $branchName = $request->input('branchName');

        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        $baseUrl = 'https://public.accurate.id/accurate/api';

        // Harga USER
        $defaultResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("{$baseUrl}/item/get-selling-price.do", [
            'id' => $id,
            'branchName' => $branchName,
        ]);

        $userPrice = $defaultResp['d']['unitPrice']
        ?? ($defaultResp['d']['unitPriceRule'][0]['price'] ?? 0);

        $discountRule = $defaultResp['d']['discountRule'][0] ?? null;
        if ($discountRule && isset($discountRule['discount']) && is_numeric($discountRule['discount'])) {
            $discountPercent = floatval($discountRule['discount']);
            $userPrice -= ($userPrice * $discountPercent / 100);
        }

        // Harga RESELLER
        $resellerResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("{$baseUrl}/item/get-selling-price.do", [
            'id' => $id,
            'priceCategoryName' => 'RESELLER',
            'discountCategoryName' => 'RESELLER',
            'branchName' => $branchName,
        ]);

        $resellerPrice = $resellerResp['d']['unitPrice']
            ?? ($resellerResp['d']['unitPriceRule'][0]['price'] ?? 0);

        $discountRule = $resellerResp['d']['discountRule'][0] ?? null;
        if ($discountRule && isset($discountRule['discount'])) {
            $disc = floatval($discountRule['discount']);
            $resellerPrice -= ($resellerPrice * $disc / 100);
        }

        return response()->json([
            'user' => $userPrice,
            'reseller' => $resellerPrice,
        ]);
    }
}
