<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\AccurateGlobal;
use Barryvdh\DomPDF\Facade\Pdf;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class KaryawanController extends Controller
{
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
    /**
     * ============================
     *  HALAMAN DETAIL (FINAL OPTIMAL)
     * ============================
     */
    public function show($encrypted, Request $request)
    {
        $decoded = Hashids::decode($encrypted);
        $id = $decoded[0] ?? null;
        if (!$id) abort(404, 'ID item tidak valid.');

        $acc = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];
        $branchName = $request->input('branchName');

        $baseUrl = 'https://public.accurate.id/accurate/api';

        /** ---------------------------------------------
         * 1. DETAIL ITEM
         * --------------------------------------------- */
        $detailResp = Http::withHeaders([
            'Authorization' => "Bearer $token",
            'X-Session-ID'  => $session,
        ])->timeout(60)->retry(3, 300)->get("$baseUrl/item/detail.do", [
            'id' => $id,
        ]);

        $item = $detailResp->json()['d'] ?? null;
        $fileName = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray();
        if (!$item) return back()->with('error', 'Gagal mengambil detail item.');
        $note = $item['notes'];

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

        /** ---------------------------------------------
         * 4. GAMBAR LIST
         * --------------------------------------------- */
        $fileName = collect($item['detailItemImage'] ?? [])
            ->pluck('fileName')   // <-- pakai thumbnail
            ->filter()
            ->values()
            ->toArray();

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
                'PANDA STORE BANJARBARU','PANDA SC BANJARBARU', 'PANDA STORE LANDASAN ULIN',
            ],
            'reseller' => [
                'RESELLER ZAKI','RESELLER MARDANI',
            ],
            'transit' => [
                'TRANSIT (AOL SYSTEM)',
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
        $warehousesTransit    = $processUnit($warehousesTransit);

        /** ---------------------------------------------
         * 9. HILANGKAN STOK 0
         * --------------------------------------------- */
        $warehousesStore      = $warehousesStore->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
        $warehousesTsc        = $warehousesTsc->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
        $warehousesReseller   = $warehousesReseller->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
        $warehousesKonsinyasi = $warehousesKonsinyasi->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
        $warehousesPanda      = $warehousesPanda->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
        $warehousesTransit    = $warehousesTransit->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();

        return view('items.karyawan.detail', [
            'item'          => $item,
            'images'        => $fileName,
            'session'       => $session,
            'branchName'    => $branchName,
            'prices' => [
                'user'     => $userPrice,
                'reseller' => $resellerPrice,
            ],
            'note' => $note,
            'unitPrices' => $unitPrices,
            'hasMultiUnitPrices' => $hasMultiUnitPrices,
            // Kirim ke Blade
            'warehousesStore'      => $warehousesStore,
            'warehousesTsc'        => $warehousesTsc,
            'warehousesReseller'   => $warehousesReseller,
            'warehousesKonsinyasi' => $warehousesKonsinyasi,
            'warehousesPanda'      => $warehousesPanda,
            'warehousesTransit'    => $warehousesTransit,
        ]);
    }

    public function proxyImage(Request $request)
    {
        $file = $request->query('file');
        $session = $request->query('session');

        if (!$file || !$session) {
            return response('Missing params', 400);
        }

        // URL asli Accurate (WAJIB)
        $url = "https://public.accurate.id{$file}?session={$session}";

        // Token & Session
        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $accurateSession = $acc['session_id'];

        // Request ke Accurate pakai header WAJIB
        $response = Http::withHeaders([
            'Authorization' => "Bearer $token",
            'X-Session-ID'  => $accurateSession,
        ])->get($url);

        if (!$response->successful()) {
            return response()->file(public_path('images/noimage.jpg'));
        }

        return response($response->body(), 200)
            ->header('Content-Type', $response->header('Content-Type') ?? 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=3600');
    }


public function getPrice(Request $request, $id)
{
    $branchName = $request->input('branchName');

    $acc = AccurateGlobal::token();
    $token = $acc['access_token'];
    $session = $acc['session_id'];

    $baseUrl = 'https://public.accurate.id/accurate/api';

     /** ---------------------------------------------
         * 1. DETAIL ITEM
         * --------------------------------------------- */
        $detailResp = Http::withHeaders([
            'Authorization' => "Bearer $token",
            'X-Session-ID'  => $session,
        ])->timeout(8)->get("$baseUrl/item/detail.do", [
            'id' => $id,
        ]);

        $item = $detailResp->json()['d'] ?? null;
        $fileName = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray();
        if (!$item) return back()->with('error', 'Gagal mengambil detail item.');
        $note = $item['notes'];

        // =============================
        // UNIT ID berdasar gudang pertama
        // =============================
        $firstWH = $item['detailWarehouseData'][0] ?? null;

    $unitId = 50; // default PCS

    if ($firstWH) {
        // contoh "6 PCS"
        $rawUnit = explode(' ', $firstWH['balanceUnit'] ?? '');
        $unitName = strtoupper($rawUnit[1] ?? 'PCS');

        if (isset($this->unitMap[$unitName])) {
            $unitId = $this->unitMap[$unitName];
        }
    }

    // =============================
    // 2. HARGA USER
    // =============================
    $defaultResp = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'X-Session-ID'  => $session,
    ])->get("{$baseUrl}/item/get-selling-price.do", [
        'id'         => $id,
        'branchName' => $branchName,
    ]);

    $userData = $defaultResp['d'] ?? [];
    $userPrice = $this->getPriceByUnitId($userData, $unitId);

    if (isset($userData['discountRule'][0]['discount'])) {
        $disc = floatval($userData['discountRule'][0]['discount']);
        $userPrice -= ($userPrice * $disc / 100);
    }

    // =============================
    // 3. HARGA RESELLER
    // =============================
    $resellerResp = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'X-Session-ID'  => $session,
    ])->get("{$baseUrl}/item/get-selling-price.do", [
        'id'                  => $id,
        'branchName'          => $branchName,
        'priceCategoryName'   => 'RESELLER',
        'discountCategoryName'=> 'RESELLER',
    ]);

    $resellerData = $resellerResp['d'] ?? [];
    $resellerPrice = $this->getPriceByUnitId($resellerData, $unitId);

    if (isset($resellerData['discountRule'][0]['discount'])) {
        $disc = floatval($resellerData['discountRule'][0]['discount']);
        $resellerPrice -= ($resellerPrice * $disc / 100);
    }

    // =============================
    // 4. KUMPULKAN SEMUA HARGA PER UNIT/PACK
    // =============================
    $unitPrices = [];

    if (isset($userData['unitPriceRule'])) {
        foreach ($userData['unitPriceRule'] as $r) {
            $uid  = $r['unitId'];
            $price = $r['price'];

            $unitName = array_search($uid, $this->unitMap, true) ?? $uid;
            $unitPrices[$unitName]['user'] = $price;
        }
    }

    if (isset($resellerData['unitPriceRule'])) {
        foreach ($resellerData['unitPriceRule'] as $r) {
            $uid  = $r['unitId'];
            $price = $r['price'];

            $unitName = array_search($uid, $this->unitMap, true) ?? $uid;
            $unitPrices[$unitName]['reseller'] = $price;
        }
    }

    // apply discount per unit
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

    return response()->json([
        'user'             => $userPrice,
        'reseller'         => $resellerPrice,
        'unitPrices'       => $unitPrices,
        'hasMultiUnit'     => count($unitPrices) > 1,
        'unitIdUsed'       => $unitId,
    ]);
}


    /**
     * ============================
     *  AJAX – REALTIME STOCK
     * ============================
     */
    public function getWarehouseStock(Request $request)
    {
        $itemId   = $request->id;
        $warehouse = $request->warehouse;
        $branch    = $request->branchName;

        $acc     = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];

        $resp = Http::withHeaders([
            'Authorization' => "Bearer $token",
            'X-Session-ID'  => $session,
        ])->get("https://public.accurate.id/accurate/api/item/get-on-sales.do", [
            'id'            => $itemId,
            'warehouseName' => $warehouse,
            'branchName'    => $branch,
        ]);

        return [
            'stock' => $resp->json()['d']['availableStock'] ?? 0
        ];
    }

    public function getBranches(Request $request)
    {
        $page = (int) $request->query('page', 1);

        // Cache key dinamis per halaman
        $cacheKey = "accurate_branches_page_{$page}";

        // Cache selama 1 jam
        $cached = Cache::remember($cacheKey, 3600, function () use ($page) {

            $acc = AccurateGlobal::token();
            $token = $acc['access_token'];
            $session = $acc['session_id'];

            $baseUrl = 'https://public.accurate.id/accurate/api';

            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->timeout(10)->get("{$baseUrl}/branch/list.do", [
                'sp.page'     => $page,
                'sp.pageSize' => 50,
            ]);

            $json = $resp->json();

            return [
                'data' => collect($json['d'] ?? [])->map(fn($b) => [
                    'id'   => $b['id'] ?? null,
                    'name' => $b['name'] ?? 'Tanpa Nama',
                ])->values(),

                'totalPage' => $json['sp']['pageCount'] ?? 1,
            ];
        });

        return response()->json($cached);
    }

    /**
     * AJAX: Load Image (base64)
     */
    public function getItemImage(Request $request)
    {
        $file = $request->query('file');
        $session = $request->query('session');

        if (!$file || !$session) {
            return response("", 200);
        }

        try {
            // Pastikan file diawali slash
            if (strpos($file, '/') !== 0) {
                $file = '/' . $file;
            }

            $url = "https://public.accurate.id{$file}?session={$session}";

            $resp = Http::timeout(10)->get($url);

            if (!$resp->successful()) {
                return response("", 200);
            }

            return base64_encode($resp->body());

        } catch (\Throwable $e) {
            return response("", 200);
        }
    }

    public function exportPdf($encrypted, Request $request)
    {
        // =============================
        // 1. Decode ID Item
        // =============================
        $decoded = Hashids::decode($encrypted);
        $id = $decoded[0] ?? null;
        if (!$id) abort(404, 'ID item tidak valid.');

        // =============================
        // 2. Ambil Token Accurate
        // =============================
        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        // FILTER dari user
        $branchName      = $request->input('branchName');
        $priceType       = $request->input('priceType', 'all');
        $warehouseFilter = (array) $request->input('warehouses', []);

        $baseUrl = 'https://public.accurate.id/accurate/api';

        // =============================
        // 3. Ambil Detail Item
        // =============================
        $detailResp = Http::withHeaders([
            'Authorization' => "Bearer $token",
            'X-Session-ID'  => $session,
        ])->timeout(10)->get("$baseUrl/item/detail.do", ['id' => $id]);

        $item = $detailResp->json()['d'] ?? null;
        if (!$item) abort(404, 'Gagal mengambil detail item.');

        $note = $item['notes'] ?? '';

        // =============================
        // 4. Ambil Gambar Item (base64)
        // =============================
        $imagesBase64 = [];

        $imageList = collect($item['detailItemImage'] ?? [])
            ->pluck('fileName')
            ->filter()
            ->values()
            ->toArray();

        foreach ($imageList as $file) {
            $url = "https://public.accurate.id{$file}?session={$session}";

            try {
                $resp = Http::withHeaders([
                    'Authorization' => "Bearer $token",
                    'X-Session-ID'  => $session,
                ])->timeout(10)->get($url);

                if ($resp->successful()) {
                    $imagesBase64[] = 'data:image/jpeg;base64,' . base64_encode($resp->body());
                }
            } catch (\Throwable $e) {}
        }

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

        // =============================
        // 6. GUDANG – sesuai logic terbaru
        // =============================
        $warehouses = collect($item['detailWarehouseData'] ?? [])
            ->map(function ($wh) {
                $raw = trim($wh['balanceUnit'] ?? '');

                preg_match('/^([\d.,]+)/', $raw, $m);
                $first = isset($m[1])
                    ? (float) str_replace(',', '.', str_replace('.', '', $m[1]))
                    : null;

                $balance = $wh['balance'] ?? $first;

                preg_match_all('/\b([A-Za-z]+)\b/', $raw, $units);

                if (count($units[1]) > 1) {
                    $wh['unit_display'] = $raw;
                } elseif ($first !== null && abs($first - $balance) > 0.001) {
                    $wh['unit_display'] = $raw;
                } else {
                    $unitOnly = preg_replace('/^[\d.,]+\s+/', '', $raw);
                    $wh['unit_display'] = strtoupper($unitOnly);
                }

                return $wh;
            })
            ->filter(fn($w) => ($w['balance'] ?? 0) > 0)
            ->values();

        // =============================
        // 7. Kelompok Gudang
        // =============================
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
                'PANDA STORE BANJARBARU','PANDA SC BANJARBARU', 'PANDA STORE LANDASAN ULIN',
            ],
            'reseller' => [
                'RESELLER ZAKI','RESELLER MARDANI',
            ],
        ];

        $filteredGroups = [];

        foreach ($groups as $key => $names) {
            $filteredGroups[$key] = $warehouses->filter(
                fn($w) => in_array(strtoupper($w['name']), $names)
            )->values();
        }

        // KONSINYASI
        $filteredGroups['konsinyasi'] = $warehouses->filter(function ($w) {
            return isset($w['description']) &&
                str_contains(strtolower($w['description']), 'konsinyasi');
        })->values();

        foreach ($filteredGroups as $key => $group) {
            $filteredGroups[$key] = $group->map(function($w) use ($id, $branchName) {
                $newStock = $this->getRealtimeStock($id, $w['name'], $branchName);
                $w['balance'] = $newStock;

                return $w;
            })->filter(fn($x) => $x['balance'] > 0)->values();
        }

        // =============================
        // 8. Filter Gudang sesuai pilihan user
        // =============================
        if (!empty($warehouseFilter)) {
            foreach ($filteredGroups as $key => $group) {
                if (!in_array($key, $warehouseFilter)) {
                    unset($filteredGroups[$key]);
                }
            }
        }

        // =============================
        // 9. GENERATE PDF
        // =============================
        $pdf = Pdf::loadView('items.karyawan.pdf', [
            'item'       => $item,
            'images'     => $imagesBase64,
            'prices' => [
                'user'     => $userPrice,
                'reseller' => $resellerPrice,
            ],
            'priceType'  => $priceType,
            'branchName' => $branchName,
            'warehouses' => $filteredGroups,
            'session'    => $session,
            'unitPrices' => $unitPrices,
            'hasMultiUnitPrices' => $hasMultiUnitPrices,
        ])->setPaper('a4', 'portrait');

        $cleanName = preg_replace('/[\/\\\\:*?"<>|]+/', '-', $item['name']);

        return $pdf->stream("Detail_{$cleanName}.pdf");
    }

    private function getRealtimeStock($itemId, $warehouseName, $branchName)
    {
        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        $resp = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Session-ID'  => $session,
        ])->timeout(10)->get("https://public.accurate.id/accurate/api/item/get-on-sales.do", [
            'id'            => $itemId,
            'warehouseName' => $warehouseName,
            'branchName'    => $branchName,
        ]);

        return $resp->json()['d']['availableStock'] ?? 0;
    }
}
