<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Foreach_;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->has('stok_ada')) {
            return redirect()->route('items.index', array_merge($request->all(), ['stok_ada' => 1]));
        }

        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');


        $search = $request->input('q');
        $categoryId = $request->input('id');

        $page = $request->get('page', 1);
        $stokAda = $request->input('stok_ada');
        $pageSize = 100;
        
        $params = [
            'sp.page' => $page,
            'sp.pageSize' => $pageSize,
            'fields' => 'id,name,no,availableToSell,itemCategoryId,detailSellingPrice',
            'filter.suspended' => 'false',
            'filter.keywords.op' => 'CONTAIN',
            'filter.keywords.val[0]' => $search,
            'filter.itemCategoryId' => $categoryId,
        ];


        $respon = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ])->get('https://public.accurate.id/accurate/api/item/list.do', $params);

        if ($respon->successful()) {
            $data = $respon->json();

           
            if (!isset($data['sp']) || !isset($data['d'])) {
                return back()->withErrors('Data tidak ditemukan atau format API tidak sesuai.');
            }            

            $items = $data['d'];           
            $apiPagination = $data['sp'];

            if ($stokAda) {
                $items = array_filter($items, function ($item) {
                    return isset($item['availableToSell']) && $item['availableToSell'] > 0;
                });
            }

            // Laravel-style pagination array
            $pagination = [
                'current_page' => $apiPagination['page'],
                'data' => $items,
                'first_page_url' => route('items.index', ['page' => 1, 'search' => $search]),
                'from' => ($apiPagination['page'] - 1) * $apiPagination['pageSize'] + 1,
                'last_page' => ceil($apiPagination['rowCount'] / $apiPagination['pageSize']),
                'last_page_url' => route('items.index', ['page' => ceil($apiPagination['rowCount'] / $apiPagination['pageSize']), 'search' => $search]),
                'next_page_url' => $apiPagination['page'] < ceil($apiPagination['rowCount'] / $apiPagination['pageSize']) 
                    ? route('items.index', ['page' => $apiPagination['page'] + 1, 'search' => $search]) 
                    : null,
                'path' => route('items.index'),
                'per_page' => $apiPagination['pageSize'],
                'prev_page_url' => $apiPagination['page'] > 1 
                    ? route('items.index', ['page' => $apiPagination['page'] - 1, 'search' => $search]) 
                    : null,
                'to' => min($apiPagination['page'] * $apiPagination['pageSize'], $apiPagination['rowCount']),
                'total' => $apiPagination['rowCount']
            ];


            $allCategories = collect(); // Kumpulan semua kategori
            $page = 1;

            do {
                $paramses = [
                    'sp.page' => $page,
                    'fields' => 'id,name',
                    'sp.pageSize' => 100 // atau nilai maksimum
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Session-ID' => $session
                ])->get('https://public.accurate.id/accurate/api/item-category/list.do', $paramses);

                $data = $response->json();

                // Simpan data kategori yang ada
                $categories = collect($data['d'] ?? []);
                $allCategories = $allCategories->merge($categories);

                $totalPages = $data['sp']['pageCount'] ?? 1;
                $page++;

            } while ($page <= $totalPages);

            return view('items.index', [
                'items' => $items,
                'pagination' => (object)$pagination,
                'search' => $search,
                'categories' => $allCategories,
            ]);
        } else {
            $data = $respon->json();
            return back()->withErrors('Gagal Mengambil Data Item: ' . json_encode($data));
        }
    }



    protected function fetchItemDetails($id, $headers)
    {
        $response = Http::withHeaders($headers)->get("https://public.accurate.id/accurate/api/item/detail.do?id=$id");
        if (!$response->successful()) {
            return null;
        }
        return $response->json()['d'] ?? null;
    }

    protected function fetchSalesInvoiceList($headers)
    {
        $saleInvoiceList = [];
        $page = 1;

        do {
            $response = Http::timeout(100)->withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-invoice/list.do", [
                'sp.page' => $page,
                'sp.pageSize' => 100,
                'fields' => 'id,number',
                'filter.reverseInvoice' => 'true',
            ]);

            if (!$response->successful()) break;

            $data = $response->json();

            $saleInvoiceList = array_merge($saleInvoiceList, $data['d'] ?? []);
            $hasNext = ($page * 100) < ($data['sp']['rowCount'] ?? 0);
            $page++;

        } while ($hasNext);

        return $saleInvoiceList;
    }

    protected function fetchSalesOrderList($headers)
    {
        $salesOrderList = [];
        $page = 1;

        do {
            $response = Http::timeout(100)->withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-order/list.do", [
                'sp.page' => $page,
                'sp.pageSize' => 100,
                'fields' => 'id,statusName,number',
                'filter.status.op' => 'EQUAL',
                'filter.status.val[0]' => 'WAITING',
                'filter.status.val[1]' => 'QUEUE',
            ]);

            if (!$response->successful()) break;

            $data = $response->json();

            $salesOrderList = array_merge($salesOrderList, $data['d'] ?? []);
            $hasNext = ($page * 100) < ($data['sp']['rowCount'] ?? 0);
            $page++;

        } while ($hasNext);

        return $salesOrderList;
    }


    protected function fetchSalesOrderDetailsBatch($salesOrderList, $headers, $itemIdUtama, &$stokNew)
    {
        $batchSize = 13;
        $salesOrderChunks = array_chunk($salesOrderList, $batchSize);

        foreach ($salesOrderChunks as $chunk) {
            $responses = Http::pool(fn ($pool) =>
                collect($chunk)->map(fn ($so) =>
                    $pool->timeout(100)->withHeaders($headers)
                        ->get("https://public.accurate.id/accurate/api/sales-order/detail.do?id=" . $so['id'])
                )->all()
            );

            foreach ($responses as $index => $detailResponse) {
                $so = $chunk[$index];

                if (!$detailResponse->successful()) {
                    Log::warning("Gagal ambil detail SO ID: {$so['id']}");
                    continue;
                }

                $detail = $detailResponse->json()['d'];

                if (!in_array($detail['statusName'], ['Menunggu diproses', 'Sebagian diproses'])) {
                    Log::info("Lewatkan SO #{$detail['number']} karena status: {$detail['statusName']}");
                    continue;
                }

                foreach ($detail['detailItem'] as $itemDetail) {
                    if ($itemDetail['itemId'] != $itemIdUtama) {
                        continue;
                    }

                    $warehouseId = $itemDetail['warehouseId'] ?? null;
                    $quantity = (float) $itemDetail['availableQuantity'];

                    if ($warehouseId === null) {
                        Log::warning("Gudang ID tidak ditemukan untuk item di SO #{$detail['number']}");
                        continue;
                    }

                    if (isset($stokNew[$warehouseId])) {
                        $stokNew[$warehouseId]['balance'] -= $quantity;
                        Log::info("✔️ Dikurangi dari Gudang ID: $warehouseId | Qty: $quantity | Sisa: {$stokNew[$warehouseId]['balance']} | SO#: {$detail['number']}");
                    } else {
                        Log::warning("❌ Gudang ID: $warehouseId tidak ditemukan di data stok | SO#: {$detail['number']}");
                    }
                }
            }
            usleep(500000); // Delay 2 detik antara batch untuk menghindari rate limit
            Log::info("Batch selesai");
        }
    }

    protected function fetchMatchingInvoices($salesInvoiceList, $headers, $itemIdUtama, &$stokNew)
    {
        $batchSize = 10;
        $salesInvoiceChunks = array_chunk($salesInvoiceList, $batchSize);

        foreach ($salesInvoiceChunks as $batch) {
            $responses = Http::pool(fn ($pool) =>
                collect($batch)->map(fn ($invoice) =>
                    $pool->timeout(100)->withHeaders($headers)
                        ->get("https://public.accurate.id/accurate/api/sales-invoice/detail.do?id=" . $invoice['id'])
                )->all()
            );
            Log::info("Memproses batch invoice dengan " . count($batch) . " item");
            if (count($responses) === 0) {
                Log::warning("Tidak ada respons untuk batch invoice ID: " . implode(', ', array_column($batch, 'id')));
                continue;
            }
            Log::info("Jumlah respons: " . count($responses));

            foreach ($responses as $index => $response) {
                if (!$response->successful()) {
                    Log::warning("Gagal ambil detail invoice ID: {$batch[$index]['id']}");
                    continue;
                }

                $invoiceDetail = $response->json()['d'];
                $invoiceId = $batch[$index]['id'];

                if (isset($invoiceDetail['reverseInvoice']) && $invoiceDetail['reverseInvoice'] === true) {
                    foreach ($invoiceDetail['detailItem'] as $itemInvoice) {
                        if (!isset($itemInvoice['item']['id']) || $itemInvoice['item']['id'] != $itemIdUtama) {
                            continue;
                        }

                        $warehouseId = $itemInvoice['warehouseId'] ?? null;
                        $quantity = (float) $itemInvoice['quantity'];

                        if ($warehouseId === null) {
                            Log::warning("Gudang ID tidak ditemukan untuk item di SI #{$invoiceDetail['number']}");
                            continue;
                        }

                        if (isset($stokNew[$warehouseId])) {
                            if ($stokNew[$warehouseId]['balance'] <= 0) {
                                Log::warning("Stok Gudang ID: $warehouseId tidak cukup untuk pengurangan di SI #{$invoiceDetail['number']}");
                            } elseif ($stokNew[$warehouseId]['balance'] < $quantity) {
                                Log::warning("Stok Gudang ID: $warehouseId kurang dari qty pengurangan di SI #{$invoiceDetail['number']}. Stok: {$stokNew[$warehouseId]['balance']}, Qty: $quantity");
                                $stokNew[$warehouseId]['balance'] = 0;
                            } else {
                                $stokNew[$warehouseId]['balance'] -= $quantity;
                                Log::info("✔️ Dikurangi dari Gudang ID: $warehouseId | Qty: $quantity | Sisa: {$stokNew[$warehouseId]['balance']} | SI#: {$invoiceDetail['number']}");
                            }
                        } else {
                            // Tambahkan warehouseId baru dengan balance 0 agar tidak error
                            $stokNew[$warehouseId] = [
                                'name' => 'Unknown Warehouse',
                                'balance' => 0,
                            ];
                            Log::warning("❌ Gudang ID: $warehouseId tidak ditemukan di data stok, ditambahkan dengan balance 0 | SI#: {$invoiceDetail['number']}");
                        }
                    }
                }
            }
            usleep(500000);
            Log::info("Batch invoice selesai");
        }
    }

    public function getMatchingInvoicesAjax(Request $request)
    {
        $itemIdUtama = $request->input('item_id');
        if (!$itemIdUtama) {
            return response()->json([
                'success' => false,
                'message' => 'Item ID harus diisi.'
            ], 400);
        }

        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        // Inisialisasi array stokNew kosong
        $stokNew = [];

        $salesInvoiceList = $this->fetchSalesInvoiceList($headers);
        $this->fetchMatchingInvoices($salesInvoiceList, $headers, $itemIdUtama, $stokNew);

        return response()->json([
            'success' => true,
            'stokNew' => $stokNew,
        ]);
    }

    protected function fetchAllBranches($headers)
    {
            $allBranches = collect();
            $page = 1;

            do {
                $paramses = [
                    'sp.page' => $page,
                    'fields' => 'id,name',
                    'sp.pageSize' => 100
                ];

                $response = Http::withHeaders($headers)
                    ->get("https://public.accurate.id/accurate/api/branch/list.do", $paramses);

                if (!$response->successful()) break;

                $data = $response->json();
                $branches = collect($data['d'] ?? []);

                $allBranches = $allBranches->merge($branches);

                $totalPage = $data['totalPage'] ?? 1;

                if ($page >= $totalPage) break;

                $page++;

            } while (true);

            return $allBranches;
    }

    protected function fetchAllCategories($headers)
    {
        return Cache::remember('allCategories', 600, function () use ($headers) {
            $allCategories = collect();
            $page = 1;

            do {
                $paramses = [
                    'sp.page' => $page,
                    'fields' => 'id,name',
                    'sp.pageSize' => 100
                ];

                $response = Http::withHeaders($headers)
                    ->get('https://public.accurate.id/accurate/api/item-category/list.do', $paramses);

                if (!$response->successful()) break;

                $data = $response->json();

                $categories = collect($data['d'] ?? []);
                $allCategories = $allCategories->merge($categories);

                $totalPages = $data['sp']['pageCount'] ?? 1;
                $page++;

            } while ($page <= $totalPages);

            return $allCategories;
        });
    }

    protected function fetchAdjustedPrice($headers, $selectedBranchId, $itemId)
    {
        if (!$selectedBranchId) {
            return [null, null, null];
        }

        // Ambil nomor item (no) dari itemId
        $itemDetailsResponse = Http::withHeaders($headers)
            ->get("https://public.accurate.id/accurate/api/item/detail.do", ['id' => $itemId]);

        if (!$itemDetailsResponse->successful()) {
            Log::warning("fetchAdjustedPrice: Gagal ambil detail item untuk itemId $itemId");
            return [null, null, null];
        }

        $itemDetails = $itemDetailsResponse->json()['d'] ?? null;
        if (!$itemDetails || !isset($itemDetails['no'])) {
            Log::warning("fetchAdjustedPrice: Nomor item tidak ditemukan untuk itemId $itemId");
            return [null, null, null];
        }

        $itemNo = $itemDetails['no'];

        $params = [
            'no' => $itemNo,
            'branchName' => $selectedBranchId,
        ];

        $response = Http::withHeaders($headers)
            ->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", $params);

        if ($response->successful()) {
            $data = $response->json();
            Log::info("fetchAdjustedPrice response data: " . json_encode($data));
            $unitPrice = $data['d']['unitPrice'] ?? null;
            $discItem = $data['d']['itemDiscPercent'] ?? null;
            return [$unitPrice, $discItem];
        }

        Log::warning("fetchAdjustedPrice: Gagal ambil harga dari Accurate, status: {$response->status()}");
        return [null, null];
    }

    public function getItemDetails($id)
    {
        $user = Auth::user();
        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        $item = $this->fetchItemDetails($id, $headers);
        if (!$item) {
            return back()->withErrors('Gagal Mengambil Data Item');
        }

        // Detail Gudang dan Garansi Reseller
        $detailWarehouse = $item['detailWarehouseData'];
        $garansiUser = $item['charField6'];
        $garansiReseller = $item['charField7'];

        $fileName = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray();

        $konsinyasiWarehouses = collect($detailWarehouse)->filter(function ($w) {
            return Str::contains(Str::lower($w['description'] ?? ''), 'konsinyasi');
        });

        $tscWarehouses = collect($detailWarehouse)->filter(function ($w) {
            return Str::contains(Str::lower($w['name'] ?? ''), [
                'tsc', 'panda sc banjarbaru',
            ]);
        });

        $nonKonsinyasiWarehouses = collect($detailWarehouse)->filter(function ($w) {
            return is_null($w['description']) &&
                !Str::contains(Str::lower($w['name']), [
                    'reseller','tsc','twintos','twinmart',
                    'marketing','asp','bazar','bina',
                    'dkv','af','barang rusak', 'sc landasan ulin', 'panda store landasan ulin', 'sc banjarbaru'
                ]);
        });

        $resellerWarehouses = collect($detailWarehouse)->filter(function ($w) {
            return Str::contains(Str::lower($w['name'] ?? ''), [
                'reseller',
            ]);
        });

        $transitWarehouses = collect($detailWarehouse)->filter(function ($w) {
            return Str::contains(Str::lower($w['name'] ?? ''), [
                'transit',
            ]);
        });

        if (strtolower($user->status) === 'karyawan' || strtolower($user->status) === 'admin') {
            $filteredWarehouses = $konsinyasiWarehouses
                ->merge($tscWarehouses)
                ->merge($nonKonsinyasiWarehouses)
                ->merge($transitWarehouses)
                ->merge($resellerWarehouses);
        } elseif (strtolower($user->status) === 'reseller') {
             $filteredWarehouses = $konsinyasiWarehouses
                ->merge($nonKonsinyasiWarehouses);
        } else {
            $filteredWarehouses = collect();
        }
        $stokNew = [];

        foreach ($filteredWarehouses as $warehouseDetail) {
            $stokNew[$warehouseDetail['id']] = [
                'name' => $warehouseDetail['name'],
                'balance' => $warehouseDetail['balance']
            ];
        }

        $allBranches = $this->fetchAllBranches($headers);

        $selectedBranchId = request('branch_id') ;

        list($unitPrice, $discItem) = $this->fetchAdjustedPrice($headers, $selectedBranchId, $id);

        // Ambil harga awal user dan reseller dari detailSellingPrice
        $sellingPrices = collect($item['detailSellingPrice']);
        $resellerPrice = $sellingPrices
            ->first(fn($p) => strtolower($p['priceCategory']['name']) === 'reseller')['price'] ?? 0;
        $userPrice = $sellingPrices
            ->first(fn($p) => strtolower($p['priceCategory']['name']) === 'user')['price'] ?? 0;

        return view('items.detail', [
            'item' => $item,
            'stokNew' => $stokNew,
            'garansiReseller' => $garansiReseller,
            'garansiUser' => $garansiUser,
            'fileName' => $fileName,
            'konsinyasiWarehouses' => $konsinyasiWarehouses,
            'nonKonsinyasiWarehouses' => $nonKonsinyasiWarehouses,
            'tscWarehouses' => $tscWarehouses,
            'session' => $session,
            'allBranches' => $allBranches,
            'selectedBranchId' => $selectedBranchId,
            'unitPrice' => $unitPrice,
            'discItem' => $discItem,
            'adjustedPrice' => $unitPrice,
            'resellerWarehouses' => $resellerWarehouses,
            'transitWarehouses' => $transitWarehouses,
        ]);
    }

    public function getSalesOrderStockAjax(Request $request)
    {
        $itemIdUtama = $request->input('item_id');

        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        if (!$itemIdUtama) {
            return response()->json([
                'success' => false,
                'message' => 'Item ID harus diisi.'
            ], 400);
        }

        $user = Auth::user();
        $item = $this->fetchItemDetails($itemIdUtama, $headers);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data item.'
            ], 500);
        }

        $detailWarehouse = $item['detailWarehouseData'];

        $konsinyasiWarehouses = collect($detailWarehouse)->filter(function ($w) {
            return Str::contains(Str::lower($w['description'] ?? ''), 'konsinyasi');
        });

        $tscWarehouses = collect($detailWarehouse)->filter(function ($w) {
            return Str::contains(Str::lower($w['name'] ?? ''), [
                'tsc', 'panda sc banjarbaru',
            ]);
        });

        $nonKonsinyasiWarehouses = collect($detailWarehouse)->filter(function ($w) {
            return is_null($w['description']) &&
                !Str::contains(Str::lower($w['name']), [
                    'reseller','tsc','twintos','twinmart',
                    'marketing','asp','bazar','bina',
                    'dkv','af','barang rusak', 'sc landasan ulin', 'panda store landasan ulin', 'sc banjarbaru'
                ]);
        });

        // Ubah di sini: gunakan semua gudang tanpa filter
        $filteredWarehouses = collect($detailWarehouse);

        $stokNew = [];

        foreach ($filteredWarehouses as $warehouseDetail) {
            $stokNew[$warehouseDetail['id']] = [
                'name' => $warehouseDetail['name'],
                'balance' => $warehouseDetail['balance']
            ];
        }

        // Tambahkan logging untuk debugging data gudang yang difilter
        $salesOrderList = $this->fetchSalesOrderList($headers);
        $this->fetchSalesOrderDetailsBatch($salesOrderList, $headers, $itemIdUtama, $stokNew);

        // Fetch sales invoice list and matching invoices for further stock reduction
        Log::info('Semua data sudah selesai');
        return response()->json([
            'success' => true,
            'stokNew' => $stokNew,
        ]);
    }

    public function getImageFromApi(Request $request)
    {
        // fileName & session, bisa dari param request atau fixed
        $fileName = $request->query('fileName'); 
        $session = $request->query('session');   

        $baseUrl = 'https://public.accurate.id';
        $url = $baseUrl . $fileName . '?session=' . $session;

        // Headers
        $headers = [
            'Authorization' => 'Bearer ' . env('ACCURATE_API_TOKEN'),
            'X-Session-ID' => env('ACCURATE_SESSION'),
        ];

        // Request gambar ke API external
        $response = Http::withHeaders($headers)->get($url);

        if ($response->successful()) {
            // Kirim langsung gambar ke browser
            return Response::make($response->body(), 200, [
                'Content-Type' => $response->header('Content-Type', 'image/jpeg'),
                'Cache-Control' => 'max-age=3600, public',
            ]);
        }
        // Kalau gagal, bisa kasih placeholder text atau image
        return response('Gambar tidak ditemukan', 404);
    }

public function getAdjustedPriceAjax(Request $request)
{
    $branchName = $request->input('branch_name');
    $itemId = $request->input('item_id');

    if (!$branchName || !$itemId) {
        return response()->json([
            'success' => false,
            'message' => 'Branch name dan Item ID harus diisi.'
        ], 400);
    }

    $token = env('ACCURATE_API_TOKEN');
    $session = env('ACCURATE_SESSION');

    $headers = [
        'Authorization' => 'Bearer ' . $token,
        'X-Session-ID' => $session
    ];

    Log::info("Memanggil Accurate get-selling-price untuk itemId: $itemId dan branch: $branchName");

    // Ambil nomor item (no) dari itemId
    $itemDetailsResponse = Http::withHeaders($headers)
        ->get("https://public.accurate.id/accurate/api/item/detail.do", ['id' => $itemId]);

    if (!$itemDetailsResponse->successful()) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil detail item.'
        ], 500);
    }

    $itemDetails = $itemDetailsResponse->json()['d'] ?? null;
    if (!$itemDetails || !isset($itemDetails['no'])) {
        return response()->json([
            'success' => false,
            'message' => 'Nomor item tidak ditemukan.'
        ], 500);
    }

    $itemNo = $itemDetails['no'];

    $params = [
        'no' => $itemNo,
        'branchName' => $branchName,
    ];

    Log::info('Params get-selling-price:', $params);

    try {
        $response = Http::withHeaders($headers)
            ->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", $params);

    if ($response->successful()) {
        $data = $response->json();

    $d = $data['d'] ?? null;

    $adjustedPrice = null;
        if ($d && isset($d['unitPrice'])) {
            $unitPrice = $d['unitPrice'];
            $discPercent = isset($d['itemDiscPercent']) ? floatval($d['itemDiscPercent']) : 0;
            if ($discPercent > 0) {
                $adjustedPrice = $unitPrice - ($unitPrice * $discPercent / 100);
            } else {
                $adjustedPrice = $unitPrice;
            }
        }

        return response()->json([
            'success' => true,
            'adjustedPrice' => $adjustedPrice,
            'discItem' => $d['itemDiscPercent'] ?? null,
        ]);

        } else {
            Log::warning('Gagal ambil harga dari Accurate', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dari Accurate.'
            ], 500);
        }
        } catch (\Exception $e) {
            Log::error('Exception saat mengambil harga dari Accurate', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungi Accurate.'
            ], 500);
        }
    }

    public function searchItemsAjax(Request $request)
    {
        $query = $request->input('q');
        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Query pencarian tidak boleh kosong.'
            ], 400);
        }

        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        $params = [
            'sp.page' => 1,
            'sp.pageSize' => 20,
            'fields' => 'id,name,no,availableToSell,itemCategoryId,detailSellingPrice',
            'filter.suspended' => 'false',
            'filter.keywords.op' => 'CONTAIN',
            'filter.keywords.val[0]' => $query,
        ];

        $response = Http::withHeaders($headers)->get('https://public.accurate.id/accurate/api/item/list.do', $params);

        if ($response->successful()) {
            $data = $response->json();
            $items = $data['d'] ?? [];

            return response()->json([
                'success' => true,
                'items' => $items,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dari API.',
            ], 500);
        }
    }

    protected function applyStockReductions(&$stokNew, $itemIdUtama, $headers)
    {
        $salesOrderList = $this->fetchSalesOrderList($headers);
        $this->fetchSalesOrderDetailsBatch($salesOrderList, $headers, $itemIdUtama, $stokNew);

        
    }

    // Jika ingin menghindari proses pengurangan stok ulang saat export PDF, Anda bisa menyimpan hasil stok yang sudah dikurangi (misal di session, cache, atau dikirim dari frontend) saat user membuka halaman detail item.
    // Kemudian, pada exportPdf, gunakan data stok yang sudah dikurangi tersebut jika tersedia, tanpa memanggil applyStockReductions lagi.

    // Contoh sederhana: ambil stok dari request jika dikirim dari frontend (misal via AJAX sebelum export PDF)
    public function exportPdf(Request $request, $id)
    {
        $user = Auth::user();
        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        // Ambil data item detail
        $item = $this->fetchItemDetails($id, $headers);
        if (!$item) {
            return back()->withErrors('Gagal Mengambil Data Item');
        }

        // Ambil filter dari request
        $selectedBranchId = $request->input('branch_id');
        $filterGudang = $request->input('filterGudang', 'semua');
        $filterHargaGaransi = $request->input('filterHargaGaransi', 'semua');

        // Ambil stok hasil pengurangan dari frontend (hasil AJAX di blade)
        $stokNew = $request->input('stokNew');
        if (is_string($stokNew)) {
            // Jika dikirim sebagai JSON string, decode dulu
            $stokNew = json_decode($stokNew, true);
        }

        // Jika stokNew tidak valid, fallback ke stok awal tanpa pengurangan
        if (!is_array($stokNew) || count($stokNew) === 0) {
            $detailWarehouse = $item['detailWarehouseData'];

            $stokNew = [];
            foreach ($detailWarehouse as $warehouseDetail) {
                $stokNew[$warehouseDetail['id']] = [
                    'name' => $warehouseDetail['name'],
                    'balance' => $warehouseDetail['balance'] ?? 0
                ];
            }
            $stokNew = array_filter($stokNew, fn($w) => ($w['balance'] ?? 0) > 0);
        }

        // Apply filterGudang to stokNew
        if ($filterGudang && $filterGudang !== 'semua') {
            $stokNew = array_filter($stokNew, function ($stock) use ($filterGudang) {
                $nameLower = strtolower($stock['name']);
                $descLower = strtolower($stock['description'] ?? '');

                if ($filterGudang === 'non') {
                    return is_null($stock['description'] ?? null) &&
                        !str_contains($nameLower, 'reseller') &&
                        !str_contains($nameLower, 'tsc') &&
                        !str_contains($nameLower, 'twintos') &&
                        !str_contains($nameLower, 'twinmart') &&
                        !str_contains($nameLower, 'marketing') &&
                        !str_contains($nameLower, 'asp') &&
                        !str_contains($nameLower, 'bazar') &&
                        !str_contains($nameLower, 'bina') &&
                        !str_contains($nameLower, 'dkv') &&
                        !str_contains($nameLower, 'af') &&
                        !str_contains($nameLower, 'barang rusak') &&
                        !str_contains($nameLower, 'sc landasan ulin') &&
                        !str_contains($nameLower, 'panda store landasan ulin') &&
                        !str_contains($nameLower, 'sc banjarbaru');
                } elseif ($filterGudang === 'konsinyasi') {
                    return str_contains($descLower, 'konsinyasi');
                } elseif ($filterGudang === 'tsc') {
                    return str_contains($nameLower, 'tsc') || str_contains($nameLower, 'panda sc banjarbaru');
                } elseif ($filterGudang === 'resel') {
                    return str_contains($nameLower, 'reseller');
                } elseif ($filterGudang === 'trans') {
                    return str_contains($nameLower, 'transit');
                }
                return true;
            });
        }

        // Filter out stocks with zero or less balance
        $stokNew = array_filter($stokNew, fn($stock) => ($stock['balance'] ?? 0) > 0);

        // Kategorikan stokNew ke dalam grup berdasarkan nama/deskripsi gudang
        $tscStock = [];
        $nonKonsinyasiStock = [];
        $resellerStock = [];
        $konsinyasiStock = [];

        foreach ($stokNew as $warehouseId => $stock) {
            $nameLower = strtolower($stock['name']);
            $descLower = '';
            // Try to get description from item detail warehouses if available
            $desc = collect($item['detailWarehouseData'])->firstWhere('id', $warehouseId)['description'] ?? '';
            $descLower = strtolower($desc);

            if (str_contains($nameLower, 'tsc') || str_contains($nameLower, 'panda sc banjarbaru')) {
                $tscStock[$warehouseId] = $stock;
            } elseif (is_null($desc) || $desc === '') {
                // Non Konsinyasi: description null and name not containing reseller, tsc, etc.
                if (!str_contains($nameLower, 'reseller') &&
                    !str_contains($nameLower, 'tsc') &&
                    !str_contains($nameLower, 'twintos') &&
                    !str_contains($nameLower, 'twinmart') &&
                    !str_contains($nameLower, 'marketing') &&
                    !str_contains($nameLower, 'asp') &&
                    !str_contains($nameLower, 'bazar') &&
                    !str_contains($nameLower, 'bina') &&
                    !str_contains($nameLower, 'dkv') &&
                    !str_contains($nameLower, 'af') &&
                    !str_contains($nameLower, 'barang rusak') &&
                    !str_contains($nameLower, 'sc landasan ulin') &&
                    !str_contains($nameLower, 'panda store landasan ulin') &&
                    !str_contains($nameLower, 'sc banjarbaru')) {
                    $nonKonsinyasiStock[$warehouseId] = $stock;
                }
            } elseif (str_contains($descLower, 'konsinyasi')) {
                $konsinyasiStock[$warehouseId] = $stock;
            } elseif (str_contains($nameLower, 'reseller')) {
                $resellerStock[$warehouseId] = $stock;
            }
        }

        // Filter out zero or less balance stocks in each group
        $tscStock = array_filter($tscStock, fn($stock) => ($stock['balance'] ?? 0) > 0);
        $nonKonsinyasiStock = array_filter($nonKonsinyasiStock, fn($stock) => ($stock['balance'] ?? 0) > 0);
        $resellerStock = array_filter($resellerStock, fn($stock) => ($stock['balance'] ?? 0) > 0);
        $konsinyasiStock = array_filter($konsinyasiStock, fn($stock) => ($stock['balance'] ?? 0) > 0);

        // Sort each group by warehouse name ascending
        $sortByName = function(&$array) {
            uasort($array, function($a, $b) {
                return strcmp(strtolower($a['name']), strtolower($b['name']));
            });
        };

        $sortByName($tscStock);
        $sortByName($nonKonsinyasiStock);
        $sortByName($resellerStock);
        $sortByName($konsinyasiStock);

        // Ambil harga disesuaikan jika ada
        list($unitPrice, $discItem) = $this->fetchAdjustedPrice($headers, $selectedBranchId, $id);

        Log::info("Export PDF - selectedBranchId: $selectedBranchId, unitPrice: $unitPrice, discItem: $discItem");

        $sellingPrices = collect($item['detailSellingPrice']);
        $resellerPrice = $sellingPrices
            ->first(fn($p) => strtolower($p['priceCategory']['name']) === 'reseller')['price'] ?? 0;
        $userPrice = $sellingPrices
            ->first(fn($p) => strtolower($p['priceCategory']['name']) === 'user')['price'] ?? 0;

        $finalUserPrice = $userPrice;
        $finalResellerPrice = $resellerPrice;

        if ($selectedBranchId) {
            if ($filterHargaGaransi === 'user' && $unitPrice !== null) {
                $discPercent = floatval($discItem ?? 0);
                $finalUserPrice = $unitPrice - ($unitPrice * $discPercent / 100);
                $finalResellerPrice = 0;
            } elseif ($filterHargaGaransi === 'reseller' && $unitPrice !== null) {
                $discPercent = floatval($discItem ?? 0);
                $finalResellerPrice = $unitPrice - ($unitPrice * $discPercent / 100);
                $finalUserPrice = 0;
            } elseif ($filterHargaGaransi === 'semua' && $unitPrice !== null) {
                $discPercent = floatval($discItem ?? 0);
                $finalUserPrice = $unitPrice - ($unitPrice * $discPercent / 100);
                $finalResellerPrice = $unitPrice - ($unitPrice * $discPercent / 100);
            }
        }

        Log::info("Export PDF - finalUserPrice: $finalUserPrice, finalResellerPrice: $finalResellerPrice");

        $pdf = Pdf::loadView('items.detail-pdf', [
            'item' => $item,
            'stokNew' => $stokNew,
            'tscStock' => $tscStock,
            'nonKonsinyasiStock' => $nonKonsinyasiStock,
            'resellerStock' => $resellerStock,
            'konsinyasiStock' => $konsinyasiStock,
            'finalUserPrice' => $finalUserPrice,
            'finalResellerPrice' => $finalResellerPrice,
            'filterGudang' => $filterGudang,
            'filterHargaGaransi' => $filterHargaGaransi,
            'selectedBranchId' => $selectedBranchId,
            'discItem' => $discItem,
        ]);

        return $pdf->stream('laporan-item-' . Str::slug($item['name']) . '.pdf');
    }


    public function getAdjustedPriceResellerAjax(Request $request)
    {
        $no = $request->input('no');
        $priceCategoryName = $request->input('priceCategoryName');
        $discountCategoryName = $request->input('discountCategoryName');
        $branchName = $request->input('branchName');

        if (!$no || !$priceCategoryName || !$discountCategoryName) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter no, priceCategoryName, dan discountCategoryName harus diisi.'
            ], 400);
        }

        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        $params = [
            'no' => $no,
            'priceCategoryName' => $priceCategoryName,
            'discountCategoryName' => $discountCategoryName,
        ];

        if ($branchName) {
            $params['branchName'] = $branchName;
        }

        Log::info('Memanggil Accurate get-selling-price dengan parameter:', $params);

        try {
            $response = Http::withHeaders($headers)
                ->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", $params);

            if ($response->successful()) {
                $data = $response->json();
                $d = $data['d'] ?? null;

                $adjustedPrice = null;
                if ($d && isset($d['unitPrice'])) {
                    $unitPrice = $d['unitPrice'];
                    $discPercent = isset($d['itemDiscPercent']) ? floatval($d['itemDiscPercent']) : 0;
                    if ($discPercent > 0) {
                        $adjustedPrice = $unitPrice - ($unitPrice * $discPercent / 100);
                    } else {
                        $adjustedPrice = $unitPrice;
                    }
                }

                return response()->json([
                    'success' => true,
                    'adjustedPrice' => $adjustedPrice,
                    'discItem' => $d['itemDiscPercent'] ?? null,
                ]);
            } else {
                Log::warning('Gagal ambil harga dari Accurate', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data dari Accurate.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception saat mengambil harga dari Accurate', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungi Accurate.'
            ], 500);
        }
    }


}