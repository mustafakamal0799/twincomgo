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
        $user = Auth::user();
        $status = $user->status;
        $account = $user->accurateAccount;

        if (! $account) {
            return back()->with('error', 'Akun Accurate belum dikaitkan dengan pengguna ini.');
        }

        // =================== PARAMETER FILTER ===================
        $targetPage = max(1, (int) $request->query('page', 1));
        $perPage    = 12;
        $search     = trim($request->query('search', ''));
        $categoryId = $request->query('category_id');
        $stokAda    = $request->query('stok_ada', '1');
        $minPrice   = $request->input('min_price') !== null ? floatval(str_replace(['.', ','], ['', '.'], $request->input('min_price'))) : null;
        $maxPrice   = $request->input('max_price') !== null ? floatval(str_replace(['.', ','], ['', '.'], $request->input('max_price'))) : null;

        $usePriceFilter = ($minPrice !== null || $maxPrice !== null);
        $targetOffset   = ($targetPage - 1) * $perPage;
        $priceCategory  = $status;
        $discountCategory = $priceCategory;

        $accountId = $account->id;
        $sid   = app(SessionResolver::class)->ensureSessionId($accountId);
        $session = $sid;

        // =================== PHASE 1: Ambil item dari Accurate ===================
        $eligible = collect();
        $collected = collect();
        $currentPage = 1;
        $pageCount = 1;
        $maxFetch = 20;

        do {
            $query = [
                'sp.page'      => $currentPage,
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

            // 🔹 Gunakan AccurateClient
            $resp = $this->accurate->request($accountId, 'GET', '/item/list.do', $query);
            $json = $resp['json'] ?? [];

            $data = collect($json['d'] ?? []);
            $pageCount = $json['sp']['pageCount'] ?? 1;

            // 🔹 Filter stok
            $stockOk = ($stokAda == '1')
                ? $data->filter(fn($i) => ($i['availableToSell'] ?? 0) > 0)->values()
                : $data->values();

            // 🔹 Jika ada filter harga, ambil harga langsung
            if ($usePriceFilter) {
                foreach ($stockOk as $it) {
                    $price = $this->getPrice($accountId, $it['id'], $priceCategory, $discountCategory);
                    $it['price'] = $price;

                    if ($minPrice !== null && $price < $minPrice) continue;
                    if ($maxPrice !== null && $price > $maxPrice) continue;

                    $eligible->push($it);
                    
                }
            } else {
                $collected = $collected->merge($stockOk);
            }

            if ($usePriceFilter) {
                if ($eligible->count() >= ($targetOffset + $perPage)) break;
            } else {
                if ($collected->count() >= ($targetOffset + $perPage)) break;
            }

            $currentPage++;
        } while ($currentPage <= $pageCount && $currentPage <= $maxFetch);

        // =================== PHASE 2: Pagination + harga ===================
        if ($usePriceFilter) {
            $totalEligible = $eligible->count();
            $pageCount = max(1, ceil($totalEligible / $perPage));
            $items = $eligible->slice($targetOffset, $perPage)->values();
        } else {
            $totalEligible = $collected->count();
            $pageCount = max(1, ceil($totalEligible / $perPage));
            $pageItems = $collected->slice($targetOffset, $perPage)->values();
            $items = $pageItems->map(function ($item) use ($accountId, $priceCategory, $discountCategory) {
                $item['price'] = $this->getPrice($accountId, $item['id'], $priceCategory, $discountCategory);
                return $item;
            });
        }

        $totalEligible = $usePriceFilter ? $eligible->count() : $collected->count();
        $pageCount = max(1, ceil($totalEligible / $perPage));

        // =================== PHASE 3: Ambil kategori (cached) ===================
        $categories = Cache::remember("accurate:categories:{$accountId}", 1800, function () use ($accountId) {
            $cats = collect();
            $page = 1;
            do {
                $resp = $this->accurate->request($accountId, 'GET', '/item-category/list.do', [
                    'sp.page' => $page,
                    'sp.pageSize' => 100,
                    'fields' => 'id,name,parent',
                ]);
                $json = $resp['json'] ?? [];
                $cats = $cats->merge($json['d'] ?? []);
                $page++;
            } while (($json['sp']['pageCount'] ?? 1) >= $page);
            return $cats;
        });

        // 🔹 Tambahkan daftar fileName ke setiap item (kalau ada)
        $items = $items->map(function ($item) {
            $fileNames = collect($item['detailItemImage'] ?? [])
                ->pluck('fileName')
                ->filter()
                ->values()
                ->toArray();

            $item['fileName'] = $fileNames;
            return $item;
        });

        // =================== RETURN ===================
        return view('reseller.index', [
            'items'      => $items,
            'page'       => $targetPage,
            'pageSize'   => $perPage,
            'pageCount'  => $pageCount,
            'search'     => $search,
            'status'     => $status,
            'categories' => $categories,
            'categoryId' => $categoryId,
            'minPrice'   => $minPrice,
            'maxPrice'   => $maxPrice,
            'stokAda'    => $stokAda,
            'session'    => $session,
        ]);
    }

    /**
     * Ambil harga Accurate per item (cached)
     */
    private function getPrice(int $accountId, int $itemId, string $priceCategory, string $discountCategory): float
    {
        $cacheKey = "accurate:price:{$accountId}:{$itemId}:{$priceCategory}";
        return Cache::remember($cacheKey, 1800, function () use ($accountId, $itemId, $priceCategory, $discountCategory) {
            $resp = $this->accurate->request(
                $accountId,
                'GET',
                '/item/get-selling-price.do',
                [
                    'id' => $itemId,
                    'priceCategoryName' => $priceCategory,
                    'discountCategoryName' => $discountCategory,
                ]
            );

            $data = $resp['json']['d'] ?? [];
            return $data['unitPrice'] ?? ($data['unitPriceRule'][0]['price'] ?? 0);
        });
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

        $fileNames = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values();

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
            'images'              => $fileNames,
            'price'               => $item['price'],
            'fileName'            => $fileName,
            'session'             => $session,
            'prices'              => $prices,
        ]);
    }
}
