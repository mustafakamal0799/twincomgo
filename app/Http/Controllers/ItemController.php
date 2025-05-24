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
        $response = Http::withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-invoice/list.do?id=");
        if (!$response->successful()) {
            return [];
        }
        return $response->json()['d'] ?? [];
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
        $batchSize = 30;
        $salesOrderChunks = array_chunk($salesOrderList, $batchSize);

        foreach ($salesOrderChunks as $chunk) {
            $responses = Http::pool(fn ($pool) =>
                collect($chunk)->map(fn ($so) =>
                    $pool->timeout(100)->withHeaders($headers)
                        ->retry(3, 1000)
                        ->get("https://public.accurate.id/accurate/api/sales-order/detail.do?id=" . $so['id'])
                )->all()
            );

            foreach ($responses as $index => $detailResponse) {
                $so = $chunk[$index];

                if (!$detailResponse->successful()) {
                    Log::warning("Gagal ambil detail SO ID: {$so['id']} setelah 3 kali percobaan");
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

                    $warehouseId = $itemDetail['warehouseId'];
                    $quantity = (float) $itemDetail['availableQuantity'];

                    if (isset($stokNew[$warehouseId])) {
                        $stokNew[$warehouseId]['balance'] -= $quantity;
                        Log::info("✔️ Dikurangi dari Gudang ID: $warehouseId | Qty: $quantity | Sisa: {$stokNew[$warehouseId]['balance']} | SO#: {$detail['number']}");
                    } else {
                        Log::warning("❌ Gudang ID: $warehouseId tidak ditemukan di data stok | SO#: {$detail['number']}");
                    }
                }
            }
        }
    }

    protected function fetchMatchingInvoices($salesInvoiceList, $headers, $itemIdUtama)
    {
        $matchingInvoices = [];

        $salesInvoiceIds = collect($salesInvoiceList)->pluck('id');
        $batches = $salesInvoiceIds->chunk(50);

        foreach ($batches as $batch) {
            $responses = Http::pool(fn ($pool) =>
                $batch->map(fn ($id) =>
                    $pool->withHeaders($headers)
                        ->timeout(60)
                        ->retry(3, 1000)
                        ->get("https://public.accurate.id/accurate/api/sales-invoice/detail.do?id=$id")
                )->all()
            );

            foreach ($responses as $index => $response) {
                if ($response->successful()) {
                    $invoiceDetail = $response->json()['d'];
                    $invoiceId = $batch->values()[$index];

                    if (isset($invoiceDetail['reverseInvoice']) && $invoiceDetail['reverseInvoice'] === true) {
                        foreach ($invoiceDetail['detailItem'] as $itemInvoice) {
                            if (isset($itemInvoice['item']['id']) && $itemInvoice['item']['id'] == $itemIdUtama) {
                                $matchingInvoices[] = [
                                    'invoice_id' => $invoiceId,
                                    'quantity' => (float) $itemInvoice['quantity'],
                                    'warehouse' => $itemInvoice['warehouse']['id'] ?? null
                                ];
                                break;
                            }
                        }
                    }
                }
            }
            usleep(500000);
        }

        return $matchingInvoices;
    }

    protected function fetchAllBranches($headers)
    {
        return Cache::remember('allBranches', 600, function () use ($headers) {
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

                usleep(250000);
            } while (true);

            return $allBranches;
        });
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

        return Cache::remember("adjustedPrice_{$selectedBranchId}_{$itemId}", 600, function () use ($headers, $selectedBranchId, $itemId) {
            $sellingPriceListResponse = Http::withHeaders($headers)
                ->get("https://public.accurate.id/accurate/api/sellingprice-adjustment/list.do");

            if (!$sellingPriceListResponse->successful()) {
                return [null, null, null];
            }

            $adjustmentSellingPrices = $sellingPriceListResponse->json()['d'] ?? [];

            foreach ($adjustmentSellingPrices as $adjustment) {
                $adjustmentDetailResponse = Http::withHeaders($headers)
                    ->timeout(60)
                    ->get("https://public.accurate.id/accurate/api/sellingprice-adjustment/detail.do?id=" . $adjustment['id']);

                if (!$adjustmentDetailResponse->successful()) {
                    continue;
                }

                $detailAdjustment = $adjustmentDetailResponse->json()['d'] ?? [];

                if ($detailAdjustment['branchId'] == $selectedBranchId) {
                    $matchedItem = collect($detailAdjustment['detailItem'] ?? [])
                        ->firstWhere('itemId', $itemId);

                    if ($matchedItem) {
                        return [
                            $matchedItem['price'],
                            $detailAdjustment['priceCategory']['name'] ?? null,
                            $matchedItem['itemDiscPercent'] ?? null
                        ];
                    }
                }

                usleep(300000);
            }

            return [null, null, null];
        });
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

        $salesInvoiceList = $this->fetchSalesInvoiceList($headers);

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

        if ($user->status === 'karyawan' || $user->status === 'admin') {
            $filteredWarehouses = $konsinyasiWarehouses
                ->merge($tscWarehouses)
                ->merge($nonKonsinyasiWarehouses);
        } elseif ($user->status === 'reseller') {
             $filteredWarehouses = $konsinyasiWarehouses
                ->merge($nonKonsinyasiWarehouses);
        } else {
            $filteredWarehouses = collect();
        }
        $itemIdUtama = $id;
        $stokNew = [];

        foreach ($filteredWarehouses as $warehouseDetail) {
            $stokNew[$warehouseDetail['id']] = [
                'name' => $warehouseDetail['name'],
                'balance' => $warehouseDetail['balance']
            ];
        }

        $salesOrderList = $this->fetchSalesOrderList($headers);
        $this->fetchSalesOrderDetailsBatch($salesOrderList, $headers, $itemIdUtama, $stokNew);

        $matchingInvoices = $this->fetchMatchingInvoices($salesInvoiceList, $headers, $itemIdUtama);

        foreach ($matchingInvoices as $invoiceMatch) {
            $warehouseId = $invoiceMatch['warehouse'];
            $quantity = $invoiceMatch['quantity'];

            if (isset($stokNew[$warehouseId])) {
                $stokNew[$warehouseId]['balance'] -= $quantity;
            }
        }

        $allBranches = $this->fetchAllBranches($headers);

        $selectedBranchId = request('branch_id');

        list($adjustedPrice, $priceCategory, $discItem) = $this->fetchAdjustedPrice($headers, $selectedBranchId, $id);

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
            'adjustedPrice' => $adjustedPrice,
            'priceCategory' => $priceCategory,
            'discItem' => $discItem,
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

}
