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
    protected AccurateClient $accurate;

    public function __construct(AccurateClient $accurate)
    {
        $this->accurate = $accurate;
    }

    public function index2(Request $request)
    {
        $user   = Auth::user();
        $status = $user->status;

        $acc     = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];

        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $perPage    = 11;
        $page       = max(1, (int) $request->query('page', 1));
        $offset     = ($page - 1) * $perPage;

        $search     = trim($request->query('search', ''));
        $categoryId = $request->query('category_id');
        $priceMode  = $request->query('price_mode', 'default');
        $stokAda    = $request->query('stok_ada', '1');
        $minPrice   = $request->filled('min_price') ? floatval(str_replace(['.', ','], ['', '.'], $request->input('min_price'))) : null;
        $maxPrice   = $request->filled('max_price') ? floatval(str_replace(['.', ','], ['', '.'], $request->input('max_price'))) : null;
        $usePriceFilter = ($minPrice !== null || $maxPrice !== null);

        $items = collect();
        $currentPageAccurate = 1;
        $pageCountAccurate = 1;
        $skipped = 0;
        $limitNeed = $perPage + 1; // ambil 1 ekstra untuk cek hasMore

        do {
            $query = [
                'sp.page'      => $currentPageAccurate,
                'sp.pageSize'  => 100,
                'fields'       => 'id,name,no,availableToSell,itemCategory.name,availableToSellInAllUnit,detailItemImage',
                'filter.suspended' => false,
            ];

            if ($search !== '') {
                $query['filter.keywords.op'] = 'CONTAIN';
                $query['filter.keywords.val[0]'] = $search;
            }
            if (!empty($categoryId)) {
                $query['filter.itemCategoryId'] = $categoryId;
            }

            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->get("{$baseUrl}/item/list.do", $query);

            if (!$resp->successful()) break;

            $json = $resp->json();
            $pageCountAccurate = $json['sp']['pageCount'] ?? 1;
            $data = collect($json['d'] ?? []);

            // Filter stok ada
            $filtered = ($stokAda == '1')
                ? $data->filter(fn($i) => ($i['availableToSell'] ?? 0) > 0)->values()
                : $data->values();

            // Filter harga
            if ($usePriceFilter) {
                $filtered = $filtered->map(function ($it) use ($token, $session) {
                    // 🔹 Tambahkan parameter kategori harga 'RESELLER'
                    $it['price'] = $this->getPriceGlobal($it['id'], $token, $session, 'RESELLER');
                    return $it;
                })->filter(function ($it) use ($minPrice, $maxPrice) {
                    if ($minPrice !== null && $it['price'] < $minPrice) return false;
                    if ($maxPrice !== null && $it['price'] > $maxPrice) return false;
                    return true;
                })->values();
            }

            // Skip item sampai offset tercapai
            if ($skipped < $offset) {
                $canSkip = min($filtered->count(), $offset - $skipped);
                $skipped += $canSkip;
                $filtered = $filtered->slice($canSkip)->values();
            }

            // Tambah item sampai melebihi limitNeed (biar bisa tahu hasMore)
            if ($filtered->isNotEmpty()) {
                $need = $limitNeed - $items->count();
                $items = $items->merge($filtered->take($need));
            }

            if ($items->count() >= $limitNeed) break;

            $currentPageAccurate++;
        } while ($currentPageAccurate <= $pageCountAccurate);

        // 🔹 Tentukan apakah masih ada halaman berikutnya
        $hasMore = $items->count() > $perPage;

        // 🔹 Potong ke jumlah tampilan (12)
        $items = $items->take($perPage)->values();

        // 🔹 PageCount realistis (bukan dari Accurate)
        $pageCount = $hasMore ? $page + 1 : $page;

        // Tambah harga default kalau belum
        if (!$usePriceFilter) {
            $items = $items->map(function ($it) use ($token, $session) {
                $it['price'] = $this->getPriceGlobal($it['id'], $token, $session, 'RESELLER');
                return $it;
            });
        }

        // Tambahkan gambar
        $items = $items->map(function ($item) {
            $item['fileName'] = collect($item['detailItemImage'] ?? [])
                ->pluck('fileName')->filter()->values()->toArray();
            return $item;
        });

        // Categories cache
        $categories = Cache::remember("accurate:categories:global", 1800, function () use ($token, $session, $baseUrl) {
            $cats = collect();
            $page = 1;
            do {
                $resp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Session-ID'  => $session,
                ])->get("{$baseUrl}/item-category/list.do", [
                    'sp.page' => $page,
                    'sp.pageSize' => 100,
                    'fields' => 'id,name,parent',
                ]);
                if (!$resp->successful()) break;
                $json = $resp->json();
                $cats = $cats->merge($json['d'] ?? []);
                $page++;
            } while (($json['sp']['pageCount'] ?? 1) >= $page);
            return $cats->values();
        });

        $viewData = [
            'items'      => $items,
            'page'       => $page,
            'pageCount'  => $pageCount,
            'categories' => $categories,
            'status'     => $status,
            'categoryId' => $categoryId,
            'stokAda'    => $stokAda,
            'minPrice'   => $minPrice,
            'maxPrice'   => $maxPrice,
            'session'    => $session,
            'search'     => $search,
        ];

        if ($request->ajax()) {
            return response()->view('reseller.index', $viewData);
        }

        return view('reseller.index', $viewData);
    }

    /**
     * Ambil harga Accurate per item (cached)
     */
    private function getPriceGlobal($itemId, $token, $session, $priceCategory = 'RESELLER')
    {
        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
            'id' => $itemId,
            'priceCategoryName' => $priceCategory,
        ]);

        $json = $resp->json();
        $data = $json['d'] ?? [];

        // 🔹 Ini yang penting — ambil dari field yang benar
        return $data['unitPrice']
            ?? ($data['unitPriceRule'][0]['price'] ?? 0);
    }

    public function getItemDetails($encrypted)
    {
        // 🔹 Decode ID dari Hashid
        $decoded = Hashids::decode($encrypted);
        $id = $decoded[0] ?? null;

        if (!$id) {
            abort(404, 'ID item tidak valid');
        }

        // 🔹 Ambil akun Accurate dari user login
        $user = Auth::user();
        $account = $user->accurateAccount;
        if (! $account) {
            return back()->with('error', 'Akun Accurate belum dikaitkan dengan pengguna ini.');
        }

        $accountId = $account->id;
        $sid   = app(SessionResolver::class)->ensureSessionId($accountId);
        $session = $sid;

        // 🔹 Ambil detail item pakai AccurateClient
        $resp = $this->accurate->request($accountId, 'GET', '/item/detail.do', [
            'id' => $id,
        ]);

        $item = $resp['json']['d'] ?? null;
        if (!$item) {
            return back()->with('error', 'Gagal mengambil data item dari Accurate.');
        }

        $fileName = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray();

        $prices = [];

        // ==================================================
        // CASE 2️⃣: Jika user adalah KARYAWAN → ambil dua harga
        // ==================================================
            $defaultResp = $this->accurate->request($accountId, 'GET', '/item/get-selling-price.do', [
                'id' => $id,
            ]);
            $prices['user'] = $defaultResp['json']['d']['unitPrice']
                ?? ($defaultResp['json']['d']['unitPriceRule'][0]['price'] ?? 0);

            // 2. Harga reseller
            $resellerResp = $this->accurate->request($accountId, 'GET', '/item/get-selling-price.do', [
                'id' => $id,
                'priceCategoryName' => 'RESELLER',
                'discountCategoryName' => 'RESELLER',
            ]);
            $resellerPrice = $resellerResp['json']['d']['unitPrice']
            ?? ($resellerResp['json']['d']['unitPriceRule'][0]['price'] ?? 0);

            // Cek diskon di discountRule
            $discountRule = $resellerResp['json']['d']['discountRule'][0] ?? null;

            // Kalau ada diskon numerik, kurangi harga
            if ($discountRule && isset($discountRule['discount']) && is_numeric($discountRule['discount'])) {
                $discountPercent = floatval($discountRule['discount']);
                $resellerPrice = $resellerPrice - ($resellerPrice * $discountPercent / 100);
            }

            $prices['reseller'] = $resellerPrice;

            // Default tampilkan harga user
            $item['price'] = $prices['user'];
        
        // 🔹 Ambil daftar gudang
        $warehouses = collect($item['detailWarehouseData'] ?? [])->map(function ($wh) {
            $unitParts = explode(' ', $wh['balanceUnit']);
            $wh['unit'] = $unitParts[1] ?? null;
            return $wh;
        });

        // ============================================================
        // 🚀 Update stok real-time Accurate dengan Http::pool (batch)
        // ============================================================

        $warehouseNames = $warehouses->pluck('name')->filter()->values();
        $updatedStocks = [];

        // Bagi jadi batch (misal 10 per batch)
        $chunks = $warehouseNames->chunk(8);
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $token = app(\App\Services\Accurate\TokenResolver::class)->getValidAccessToken($accountId);
        $sid   = app(\App\Services\Accurate\SessionResolver::class)->ensureSessionId($accountId);

        foreach ($chunks as $batch) {
            $responses = Http::pool(fn($pool) =>
                $batch->map(fn($name) =>
                    $pool->as($name)->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'X-Session-ID'  => $sid,
                    ])->get("{$baseUrl}/item/get-on-sales.do", [
                        'id' => $item['id'],
                        'warehouseName' => $name,
                    ])
                )
            );

            foreach ($responses as $name => $resp) {
                if ($resp->successful()) {
                    $json = $resp->json();
                    $updatedStocks[$name] = $json['d']['availableStock'] ?? null;
                } else {
                    Log::warning("Gagal ambil stok untuk gudang {$name}");
                }
            }

            // jeda antar batch biar aman dari rate-limit
            usleep(500000); // 0.5 detik antar batch
        }

        // 🔁 Update stok pada daftar warehouse
        $warehouses = $warehouses->map(function ($wh) use ($updatedStocks) {
            $name = $wh['name'] ?? null;
            if ($name && isset($updatedStocks[$name])) {
                $wh['balance'] = $updatedStocks[$name]; // stok real-time Accurate
            }
            return $wh;
        });

        // Filter hanya stok > 0
        $warehouses = $warehouses->filter(function ($wh) {
            return isset($wh['balance']) && $wh['balance'] > 0;
        })->values();

        // ============================================================
        // Memisahkan gudang sesuai dengan name / deskripsi
        // ============================================================

        // 1️⃣ Gudang konsinyasi (berdasarkan deskripsi)
        $warehousesKonsinyasi = $warehouses->filter(function ($wh) {
            return isset($wh['description']) && Str::contains(strtolower($wh['description']), 'konsinyasi');
        })->values();

        // 2️⃣ Gudang store (berdasarkan nama)
        $storeNames = [
            'TSTORE KAYUTANGI',
            'TSTORE BANJARBARU A. YANI',
            'TSTORE BANJARBARU P. BATUR',
            'TSTORE BELITUNG',
            'TSTORE MARTAPURA',
            'TDC',
            'STORE PALANGKARAYA',
            'LANDASAN ULIN',
            'PANDA STORE BANJARBARU',
        ];
        $warehousesStore = $warehouses->filter(function ($wh) use ($storeNames) {
            return in_array(strtoupper($wh['name'] ?? ''), $storeNames);
        })->values();
        
        // 2️⃣ Gudang store (berdasarkan nama)
        $resellerNames = [
            'RESELLER ZAKI',
            'RESELLER MARDANI',
        ];
        $warehousesReseller = $warehouses->filter(function ($wh) use ($resellerNames) {
            return in_array(strtoupper($wh['name'] ?? ''), $resellerNames);
        })->values();

        $tscNames = [
            'TSC BANJARBARU A. YANI',
            'TSC BANJARBARU P. BATUR',
            'TSC BELITUNG',
            'TSC KAYUTANGI',
            'TSC LANDASAN ULIN',
            'TSC MARTAPURA',
            'TSC PALANGKARAYA',
        ];
        
        $warehousesTsc = $warehouses->filter(function ($wh) use ($tscNames) {
            return in_array(strtoupper($wh['name'] ?? ''), $tscNames);
        })->values();

        $totalKonsinyasi = $warehousesKonsinyasi->sum('balance');
        $totalStore      = $warehousesStore->sum('balance');
        $totalReseller   = $warehousesReseller->sum('balance');
        $totalTsc        = $warehousesTsc->sum('balance');

        // ============================================================

        // 🔹 Return ke view seperti biasa
        return view('reseller.detail', [
            'item'                => $item,
            'warehouses'          => $warehouses,
            'warehousesKonsinyasi'=> $warehousesKonsinyasi,
            'warehousesStore'     => $warehousesStore,
            'warehousesReseller'  => $warehousesReseller,
            'warehousesTsc'       => $warehousesTsc,
            'totalKonsinyasi'     => $totalKonsinyasi,
            'totalStore'          => $totalStore,
            'totalReseller'       => $totalReseller,
            'totalTsc'            => $totalTsc,
            'images'              => $fileName,
            'price'               => $item['price'],
            'session'             => $session,
            'prices'              => $prices,
        ]);
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
        ])->get("{$baseUrl}/item/detail.do", ['id' => $id]);

        $item = $resp->json()['d'] ?? null;
        if (!$item) {
            return back()->with('error', 'Gagal mengambil data item dari Accurate.');
        }

        $fileName = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray();

        // ============================================================
        // 🔹 Ambil dua harga: USER & RESELLER
        // ============================================================
        $prices = [];

        // Harga USER
        $defaultResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
            'id' => $id,
            'branchName' => $branchName, // 🔹 tambahan penting
        ]);

        
        $userPrice = $defaultResp['d']['unitPrice']
        ?? ($defaultResp['d']['unitPriceRule'][0]['price'] ?? 0);

        $discountRule = $defaultResp['d']['discountRule'][0] ?? null;
        if ($discountRule && isset($discountRule['discount']) && is_numeric($discountRule['discount'])) {
            $discountPercent = floatval($discountRule['discount']);
            $userPrice -= ($userPrice * $discountPercent / 100);
        }
        
        $prices['user'] = $userPrice;

        // Harga RESELLER
        $resellerResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
            'id' => $id,
            'priceCategoryName' => 'RESELLER',
            'discountCategoryName' => 'RESELLER',
            'branchName' => $branchName, // 🔹 tambahan penting
        ]);

        $resellerPrice = $resellerResp['d']['unitPrice']
            ?? ($resellerResp['d']['unitPriceRule'][0]['price'] ?? 0);

        // Diskon kalau ada
        $discountRule = $resellerResp['d']['discountRule'][0] ?? null;
        if ($discountRule && isset($discountRule['discount']) && is_numeric($discountRule['discount'])) {
            $discountPercent = floatval($discountRule['discount']);
            $resellerPrice -= ($resellerPrice * $discountPercent / 100);
        }
        $prices['reseller'] = $resellerPrice;

        // ============================================================
        // 🔹 Ambil gudang & update stok real-time (pakai pool)
        // ============================================================
        $warehouses = collect($item['detailWarehouseData'] ?? [])->map(function ($wh) {
            $unitParts = explode(' ', $wh['balanceUnit']);
            $wh['unit'] = $unitParts[1] ?? null;
            return $wh;
        });

        $warehouseNames = $warehouses->pluck('name')->filter()->values();
        $updatedStocks = [];

        $chunks = $warehouseNames->chunk(8);
        foreach ($chunks as $batch) {
            $responses = Http::pool(fn($pool) =>
                $batch->map(fn($name) =>
                    $pool->as($name)->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'X-Session-ID'  => $session,
                    ])->get("{$baseUrl}/item/get-on-sales.do", [
                        'id' => $item['id'],
                        'warehouseName' => $name,
                    ])
                )
            );

            foreach ($responses as $name => $resp) {
                if ($resp->successful()) {
                    $json = $resp->json();
                    $updatedStocks[$name] = $json['d']['availableStock'] ?? null;
                }
            }
            usleep(500000);
        }

        // Update stok realtime
        $warehouses = $warehouses->map(function ($wh) use ($updatedStocks) {
            $name = $wh['name'] ?? null;
            if ($name && isset($updatedStocks[$name])) {
                $wh['balance'] = $updatedStocks[$name];
            }
            return $wh;
        })->filter(fn($wh) => isset($wh['balance']) && $wh['balance'] > 0)->values();

        // ============================================================
        // 🔹 Pisah berdasarkan tipe gudang
        // ============================================================

        // Konsinyasi
        $warehousesKonsinyasi = $warehouses->filter(fn($wh) =>
            isset($wh['description']) && Str::contains(strtolower($wh['description']), 'konsinyasi')
        )->values();

        //Store
        $storeNames = [
            'TSTORE KAYUTANGI', 'TSTORE BANJARBARU A. YANI', 'TSTORE BANJARBARU P. BATUR',
            'TSTORE BELITUNG', 'TSTORE MARTAPURA', 'TDC',
            'STORE PALANGKARAYA', 'LANDASAN ULIN',
        ];
        $warehousesStore = $warehouses->filter(fn($wh) =>
            in_array(strtoupper($wh['name'] ?? ''), $storeNames)
        )->values();
        
        //Panda
        $pandaNames = [
            'PANDA STORE BANJARBARU', 'PANDA SC BANJARBARU',
        ];
        $warehousesPanda = $warehouses->filter(fn($wh) =>
            in_array(strtoupper($wh['name'] ?? ''), $pandaNames)
        )->values();

        //Reseller
        $resellerNames = ['RESELLER ZAKI', 'RESELLER MARDANI'];
        $warehousesReseller = $warehouses->filter(fn($wh) =>
            in_array(strtoupper($wh['name'] ?? ''), $resellerNames)
        )->values();

        //Tsc
        $tscNames = [
            'TSC BANJARBARU A. YANI', 'TSC BANJARBARU P. BATUR', 'TSC BELITUNG',
            'TSC KAYUTANGI', 'TSC LANDASAN ULIN', 'TSC MARTAPURA', 'TSC PALANGKARAYA',
        ];
        $warehousesTsc = $warehouses->filter(fn($wh) =>
            in_array(strtoupper($wh['name'] ?? ''), $tscNames)
        )->values();

        return view('reseller.detail', [
            'item'                => $item,
            'warehouses'          => $warehouses,
            'warehousesKonsinyasi'=> $warehousesKonsinyasi,
            'warehousesStore'     => $warehousesStore,
            'warehousesReseller'  => $warehousesReseller,
            'warehousesTsc'       => $warehousesTsc,
            'warehousesPanda'     => $warehousesPanda,
            'totalKonsinyasi'     => $warehousesKonsinyasi->sum('balance'),
            'totalStore'          => $warehousesStore->sum('balance'),
            'totalReseller'       => $warehousesReseller->sum('balance'),
            'totalTsc'            => $warehousesTsc->sum('balance'),
            'images'              => $fileName,
            'session'             => $session,
            'prices'              => $prices,
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
