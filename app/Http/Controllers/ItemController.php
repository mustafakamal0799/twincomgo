<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Foreach_;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Vinkla\Hashids\Facades\Hashids;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->exists('stok_ada')) {
            return redirect()->route('items.index', array_merge($request->all(), ['stok_ada' => 1]));
        }
        
        $page = $request->input('page', 1);
        $categoryId = $request->input('category_id');

        $stokAda = $request->input('stok_ada');
        $minPrice = $request->input('min_price') !== null ? floatval(str_replace(['.', ','], ['', '.'], $request->input('min_price'))) : null;
        $maxPrice = $request->input('max_price') !== null ? floatval(str_replace(['.', ','], ['', '.'], $request->input('max_price'))) : null;
        $status = Auth::user()->status;
        

        $params = [
            'sp.page' => $page,
            'sp.pageSize' => 100,
            'fields' => 'id,name,no,availableToSell,branchPrice,itemUnit,availableToSellInAllUnit',
            'filter.suspended' => 'false',
        ];

        if ($request->filled('search')) {
            $search = $request->input('search');
            $params['filter.keywords.op'] = 'CONTAIN';
            $params['filter.keywords.val[0]'] = $search;
        }

        if ($categoryId) {
            $params['filter.itemCategoryId'] = $categoryId;
        }
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('ACCURATE_API_TOKEN'),
            'X-Session-ID' => env('ACCURATE_SESSION'),
        ])->get('https://public.accurate.id/accurate/api/item/list.do', $params);

        $data = $response->json();
        $items = $data['d'] ?? [];

        if ($minPrice !== null || $maxPrice !== null) {
            $items = array_filter($items, function ($item) use ($minPrice, $maxPrice) {
                $price = $item['branchPrice'] ?? null;
                if ($price === null) {
                    return false;
                }
                if ($minPrice !== null && $price < floatval($minPrice)) {
                    return false;
                }
                if ($maxPrice !== null && $price > floatval($maxPrice)) {
                    return false;
                }
                return true;
            });
        }

        if ($stokAda == '1') {
            $items = array_filter($items, function ($item) {
                return isset($item['availableToSell']) && $item['availableToSell'] > 0;
            });
        }

        $allCategories = collect();
        $page = 1;

        do {
            $paramses = [
                'sp.page' => $page,
                'fields' => 'id,name,parent',
                'sp.pageSize' => 100
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('ACCURATE_API_TOKEN'),
                'X-Session-ID' => env('ACCURATE_SESSION'),
            ])->get('https://public.accurate.id/accurate/api/item-category/list.do', $paramses);

            $data = $response->json();

            $categories = collect($data['d'] ?? []);
            $allCategories = $allCategories->merge($categories);

            $totalPages = $data['sp']['pageCount'] ?? 1;
            $page++;
        } while ($page <= $totalPages);

        $categories = $allCategories->toArray();
        $categoryOptions = $this->buildCategoryOptions($categories);

        $currentPage = $data['sp']['page'] ?? 1;
        $totalPages = $data['sp']['pageSize'] ?? 1;

        if ($request->ajax()) {
            return view('partials.item-rows', compact('items', 'status'))->render();
        }

        return view('items.index', compact(
            'items',
            'allCategories',
            'status',
            'categoryOptions',
            'currentPage',
            'totalPages',
        ));
    }

    public function searchCategories(Request $request)
    {
        $query = $request->input('q');

        $params = [
            'fields' => 'id,name',
            'filter.keywords.op' => 'CONTAIN',
            'filter.keywords.val[0]' => $query,
            'sp.pageSize' => 100,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('ACCURATE_API_TOKEN'),
            'X-Session-ID' => env('ACCURATE_SESSION'),
        ])->get('https://public.accurate.id/accurate/api/item-category/list.do', $params);

        $data = $response->json();

        $categories = collect($data['d'] ?? [])->map(function ($item) {
            return [
                'id' => $item['id'],
                'text' => $item['name'],
            ];
        });

        return response()->json($categories);
    }

    private function buildCategoryOptions(array $categories, $parentId = null, $prefix = '')
    {
        $html = '';

        foreach ($categories as $category) {
            $catParentId = $category['parent']['id'] ?? null;

            if ($catParentId == $parentId) {
                $selected = request('category_id') == $category['id'] ? 'selected' : '';
                $html .= '<option value="' . $category['id'] . '" ' . $selected . '>' . $prefix . $category['name'] . '</option>';
                // Rekursif untuk anaknya
                $html .= $this->buildCategoryOptions($categories, $category['id'], $prefix . '-- ');
            }
        }

        return $html;
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
                'filter.reverseInvoiceStatus' => 'UNDELIVERED',
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
        $batchSize = 10;
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
            // usleep(1000000);
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
            usleep(500000); //delay 0,5 detik
            Log::info("Batch invoice selesai");
        }
    }

    public function getMatchingInvoicesAjax(Request $request)
    {
        $itemIdUtama = $request->input('item_id');
        $stokNew = $request->input('stok_awal');

        if (!$itemIdUtama) {
            return response()->json([
                'success' => false,
                'message' => 'Item ID harus diisi.'
            ], 400);
        }

        if (!is_array($stokNew)) {
            return response()->json([
                'success' => false,
                'message' => 'Data stok awal tidak valid.'
            ], 400);
        }

        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        $salesInvoiceList = $this->fetchSalesInvoiceList($headers);

        // Kurangi stok berdasarkan invoice
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

            $d = $data['d'] ?? null;

                if ($d && isset($d['unitPriceRule']) && is_array($d['unitPriceRule'])) {
                    $discPercent = isset($d['itemDiscPercent']) ? floatval($d['itemDiscPercent']) : 0;
                    $adjustedPrices = [];

                    $unitMap = [
                        52850 => "1",
                        53550 => "BATANG",
                        53950 => "BOX",
                        53200 => "BTL",
                        53450 => "CAM",
                        53300 => "DUS",
                        52950 => "HPP",
                        53400 => "IKAT",
                        53600 => "KALENG",
                        53700 => "KARUNG",
                        53900 => "KG",
                        53350 => "KLG",
                        52701 => "METER",
                        52750 => "MTR",
                        53000 => "PACK",
                        53750 => "PAJAK",
                        53100 => "PAKET",
                        53151 => "PCH",
                        50 => "PCS",
                        53500 => "POTONG",
                        53650 => "RIT",
                        52900 => "ROLL",
                        53150 => "SAK",
                        53800 => "SET",
                        53050 => "UNIT",
                        53850 => "rim",
                    ];

                    foreach ($d['unitPriceRule'] as $rule) {
                        $unitId = $rule['unitId'] ?? null;
                        $price = $rule['price'] ?? null;

                        if ($unitId && $price) {
                            $unitName = $unitMap[$unitId] ?? "UNIT $unitId";
                            $finalPrice = $discPercent > 0
                                ? $price - ($price * $discPercent / 100)
                                : $price;

                            $adjustedPrices[$unitName] = $finalPrice;
                        }
                    }
                }


            Log::info("fetchAdjustedPrice response data: " . json_encode($adjustedPrices));
            $unitPrice = $adjustedPrices ?? null;
            $discItem = $discPercent ?? null;
            return [$unitPrice, $discItem];
        }

        Log::warning("fetchAdjustedPrice: Gagal ambil harga dari Accurate, status: {$response->status()}");
        return [null, null];
    }

    public function getItemDetails($encrypted)
    {
        $decoded = Hashids::decode($encrypted);
        $id = $decoded[0] ?? null;

        if (!$id) {
            abort(404, 'ID tidak valid'); // atau handle jika ID tidak valid
        }
        
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
        $pricePack = $item['unit2Price'];
        $satuanItem = $item['balanceInUnit'];
        $ratio = $item['ratio2'] ?? null;

        $detailWarehouse = collect($detailWarehouse)->map(function ($item) {
            $unitParts = explode(' ', $item['balanceUnit']);
            $item['unit'] = $unitParts[1] ?? null;
            return $item;
        })->toArray();

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
            'pricePack' => $pricePack,
            'satuanItem' => $satuanItem,
            'ratio' => $ratio ?? 'ratio tidak ada',
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

        $filteredWarehouses = collect($detailWarehouse);

        $stokNew = [];

        foreach ($filteredWarehouses as $warehouseDetail) {
            $stokNew[$warehouseDetail['id']] = [
                'name' => $warehouseDetail['name'],
                'balance' => $warehouseDetail['balance']
            ];
        }

        $salesOrderList = $this->fetchSalesOrderList($headers);
        $this->fetchSalesOrderDetailsBatch($salesOrderList, $headers, $itemIdUtama, $stokNew);


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

                if ($d && isset($d['unitPriceRule']) && is_array($d['unitPriceRule'])) {
                    $discPercent = isset($d['itemDiscPercent']) ? floatval($d['itemDiscPercent']) : 0;
                    $adjustedPrices = [];

                    // Paste unitMap lengkap di sini
                    $unitMap = [
                        52850 => "1",
                        53550 => "BATANG",
                        53950 => "BOX",
                        53200 => "BTL",
                        53450 => "CAM",
                        53300 => "DUS",
                        52950 => "HPP",
                        53400 => "IKAT",
                        53600 => "KALENG",
                        53700 => "KARUNG",
                        53900 => "KG",
                        53350 => "KLG",
                        52701 => "METER",
                        52750 => "MTR",
                        53000 => "PACK",
                        53750 => "PAJAK",
                        53100 => "PAKET",
                        53151 => "PCH",
                        50 => "PCS",
                        53500 => "POTONG",
                        53650 => "RIT",
                        52900 => "ROLL",
                        53150 => "SAK",
                        53800 => "SET",
                        53050 => "UNIT",
                        53850 => "rim",
                    ];

                    foreach ($d['unitPriceRule'] as $rule) {
                        $unitId = $rule['unitId'] ?? null;
                        $price = $rule['price'] ?? null;

                        if ($unitId && $price) {
                            $unitName = $unitMap[$unitId] ?? "UNIT $unitId";
                            $finalPrice = $discPercent > 0
                                ? $price - ($price * $discPercent / 100)
                                : $price;

                            $adjustedPrices[$unitName] = $finalPrice;
                        }
                    }

                    return response()->json([
                        'success' => true,
                        'adjustedPrices' => $adjustedPrices,
                        'discItem' => $discPercent > 0 ? $discPercent : null,
                    ]);
                }

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

    public function exportPdf(Request $request, $encrypted)
    {
        Log::info('DATA REQUEST EXPORT PDF:', $request->all());

        
        $decoded = Hashids::decode($encrypted);
        $id = $decoded[0] ?? null;

        if (!$id) {
            abort(404, 'ID tidak valid'); // atau handle jika ID tidak valid
        }

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

        $satuanItem = $item['balanceInUnit'] ?? null;

        // Ambil nama file gambar pertama dari detailItemImage jika ada
        $fileName = null;
        if (!empty($item['detailItemImage']) && is_array($item['detailItemImage'])) {
            $images = array_filter($item['detailItemImage'], fn($img) => !empty($img['fileName']));
            if (count($images) > 0) {
                $fileName = array_values($images)[0]['fileName'];
            }
        }

        $imageBase64 = null;
        if ($fileName) {
            $baseUrl = 'https://public.accurate.id';
            $imageUrl = $baseUrl . $fileName . '?session=' . $session;

            try {
                $response = Http::withHeaders($headers)->get($imageUrl);
                if ($response->successful()) {
                    $imageBase64 = base64_encode($response->body());
                }
            } catch (\Exception $e) {
                // Log error but continue
                Log::error("Gagal mengambil gambar untuk PDF: " . $e->getMessage());
            }
        }

        // Ambil filter dari request
        $selectedBranchId = $request->input('branch_id');
        $filterHargaGaransi = $request->input('filterHargaGaransi', 'semua');

        // Ambil stok hasil pengurangan dari frontend (hasil AJAX di blade)
        $stokNew = $request->input('stokNew');
        if (is_string($stokNew)) {
            $stokNew = json_decode($stokNew, true);
        }

        if (!is_array($stokNew) || count($stokNew) === 0) {
            $detailWarehouse = $item['detailWarehouseData'];

            $stokNew = [];
            foreach ($detailWarehouse as $warehouseDetail) {
                $stokNew[$warehouseDetail['id']] = [
                    'name' => $warehouseDetail['name'],
                    'description' => $warehouseDetail['description'] ?? null,
                    'balance' => $warehouseDetail['balance'] ?? 0,
                    'balanceUnit' => $warehouseDetail['balanceUnit'] ?? null,
                ];
            }
            $stokNew = array_filter($stokNew, fn($w) => ($w['balance'] ?? 0) > 0);
        } else {
            $detailWarehouse = collect($item['detailWarehouseData'])->keyBy('id');
            foreach ($stokNew as $warehouseId => &$stock) {
                if (isset($detailWarehouse[$warehouseId])) {
                        $stock['description'] = $detailWarehouse[$warehouseId]['description'] ?? null;
                        $stock['balanceUnit'] = $detailWarehouse[$warehouseId]['balanceUnit'] ?? null; // ✅ tambahkan ini
                    } else {
                        $stock['description'] = null;
                        $stock['balanceUnit'] = null; // ✅ default null kalau tidak ketemu
                    }
            }
            unset($stock);
        }

        // Apply filterGudang to stokNew
        // Log::info("Filter Gudang: $filterGudang, stokNew sebelum filter: " . json_encode($stokNew));

        // Logging stok reseller sebelum filter
        $resellerBeforeFilter = array_filter($stokNew, fn($stock) => str_contains(strtolower($stock['name']), 'reseller'));
        Log::info("Stok reseller sebelum filter: " . json_encode($resellerBeforeFilter));

        $filterGudang = $request->input('filterGudang', []);

        if (in_array('semua', $filterGudang)) {
            $filterGudang = []; // reset kalau semua dipilih
        }

        // filter stokNew berdasarkan array tersebut
        if (!empty($filterGudang)) {
            $stokNew = array_filter($stokNew, function ($stock) use ($filterGudang) {
                $nameLower = strtolower($stock['name']);
                $descLower = strtolower($stock['description'] ?? '');

                foreach ($filterGudang as $filter) {
                    if ($filter === 'store') {
                        if (is_null($stock['description'] ?? null) &&
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
                            !str_contains($nameLower, 'sc banjarbaru')) {
                                return true;
                        }
                    } elseif ($filter === 'konsinyasi' && str_contains($descLower, 'konsinyasi')) {
                        return true;
                    } elseif ($filter === 'tsc' && (str_contains($nameLower, 'tsc') || str_contains($nameLower, 'panda sc banjarbaru'))) {
                        return true;
                    } elseif ($filter === 'resel' && str_contains($nameLower, 'reseller')) {
                        return true;
                    } elseif ($filter === 'trans' && str_contains($nameLower, 'transit')) {
                        return true;
                    }
                }

                return false;
            });
        }

        Log::info('DATA STOK SETELAH FILTER:', $stokNew);

        // Filter out stocks with zero or less balance
        $stokNew = array_filter($stokNew, fn($stock) => ($stock['balance'] ?? 0) > 0);

        // Kategorikan stokNew ke dalam grup berdasarkan nama/deskripsi gudang
        $tscStock = [];
        $nonKonsinyasiStock = [];
        $resellerStock = [];
        $konsinyasiStock = [];
        $transitStock = [];

        foreach ($stokNew as $warehouseId => $stock) {
            $nameLower = strtolower($stock['name']);
            $descLower = '';
            // Try to get description from item detail warehouses if available
            $desc = collect($item['detailWarehouseData'])->firstWhere('id', $warehouseId)['description'] ?? '';
            $descLower = strtolower($desc);

            Log::info("Memeriksa stok warehouseId: $warehouseId, name: {$stock['name']}, balance: {$stock['balance']}, description: $descLower");

            if (str_contains($nameLower, 'tsc') || str_contains($nameLower, 'panda sc banjarbaru')) {
                if (($stock['balance'] ?? 0) > 0) {
                    $tscStock[$warehouseId] = $stock;
                    Log::info("Masuk kategori TSC");
                }
                
            } // --- FIX: pindahkan pengecekan reseller sebelum non-konsinyasi ---
            elseif (str_contains($nameLower, 'reseller') || str_contains($descLower, 'reseller')) {
                if (($stock['balance'] ?? 0) > 0) {
                    $resellerStock[$warehouseId] = $stock;
                    Log::info("Masuk kategori Reseller: warehouseId $warehouseId, name {$stock['name']}, balance {$stock['balance']}");
                } else {
                    Log::info("Tidak masuk kategori Reseller walau mengandung 'reseller': warehouseId $warehouseId, name {$stock['name']}, balance {$stock['balance']}");
                }
            }
            // --- END FIX --- 
            elseif (is_null($desc) || $desc === '') {
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
                    if (($stock['balance'] ?? 0) > 0) {
                        $nonKonsinyasiStock[$warehouseId] = $stock;
                        Log::info("Masuk kategori Non Konsinyasi");
                    }
                }
            } elseif (str_contains($descLower, 'konsinyasi')) {
                if (($stock['balance'] ?? 0) > 0) {
                    $konsinyasiStock[$warehouseId] = $stock;
                    Log::info("Masuk kategori Konsinyasi");
                }
            } 
            elseif (str_contains($nameLower, 'transit')) {
                if (($stock['balance'] ?? 0) > 0) {
                    $transitStock[$warehouseId] = $stock;
                    Log::info("Masuk kategori Transit");
                }
            }
        }

        Log::info("Jumlah stok TSC: " . count($tscStock));
        Log::info("Jumlah stok Non Konsinyasi: " . count($nonKonsinyasiStock));
        Log::info("Jumlah stok Konsinyasi: " . count($konsinyasiStock));
        Log::info("Jumlah stok Reseller: " . count($resellerStock));
        Log::info("Jumlah stok Transit: " . count($transitStock));

        // Filter out zero or less balance stocks in each group
        $tscStock = array_filter($tscStock, fn($stock) => ($stock['balance'] ?? 0) > 0);
        $nonKonsinyasiStock = array_filter($nonKonsinyasiStock, fn($stock) => ($stock['balance'] ?? 0) > 0);
        $resellerStock = array_filter($resellerStock, fn($stock) => ($stock['balance'] ?? 0) > 0);
        $konsinyasiStock = array_filter($konsinyasiStock, fn($stock) => ($stock['balance'] ?? 0) > 0);

        $totalTsc = array_sum(array_column($tscStock, 'balance'));
        $totalNonKonsinyasi = array_sum(array_column($nonKonsinyasiStock, 'balance'));
        $totalReseller = array_sum(array_column($resellerStock, 'balance'));
        $totalKonsinyasi = array_sum(array_column($konsinyasiStock, 'balance'));

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

        Log::info("Export PDF" , [
            'selectedBranchId' => $selectedBranchId,
            'unitPrice' => $unitPrice,
            'discItem' => $discItem,
        ]);

        $sellingPrices = collect($item['detailSellingPrice']);
        

        // if ($selectedBranchId) {
        //     if ($filterHargaGaransi === 'user' && $unitPrice !== null) {
        //         $discPercent = floatval($discItem ?? 0);
        //         $finalUserPrice = $unitPrice - ($unitPrice * $discPercent / 100);
        //         $finalResellerPrice = 0;
        //     } elseif ($filterHargaGaransi === 'reseller' && $unitPrice !== null) {
        //         $discPercent = floatval($discItem ?? 0);
        //         $finalResellerPrice = $unitPrice - ($unitPrice * $discPercent / 100);
        //         $finalUserPrice = 0;
        //     } elseif ($filterHargaGaransi === 'semua' && $unitPrice !== null) {
        //         $discPercent = floatval($discItem ?? 0);
        //         $finalUserPrice = $unitPrice - ($unitPrice * $discPercent / 100);
        //         $finalResellerPrice = $unitPrice - ($unitPrice * $discPercent / 100);
        //     }
        // }

        $totalBalance = 0;

        foreach ($stokNew as $stok) {
            $totalBalance += $stok['balance'] ?? 0;
        }


        Log::info("Export PDF", [
            'unitPrice' => $unitPrice,
        ]);

        $pdf = Pdf::loadView('items.detail-pdf', [
            'item' => $item,
            'stokNew' => $stokNew,
            'tscStock' => $tscStock,
            'nonKonsinyasiStock' => $nonKonsinyasiStock,
            'transitStock' => $transitStock,
            'resellerStock' => $resellerStock,
            'konsinyasiStock' => $konsinyasiStock,
            // 'finalUserPrice' => $finalUserPrice,
            // 'finalResellerPrice' => $finalResellerPrice,
            'filterGudang' => $filterGudang,
            'filterHargaGaransi' => $filterHargaGaransi,
            'selectedBranchId' => $selectedBranchId,
            'discItem' => $discItem,
            'garansiReseller' => $item['charField7'] ?? null,
            'garansiUser' => $item['charField6'] ?? null,
            'imageBase64' => $imageBase64,
            'totalNonKonsinyasi' => $totalNonKonsinyasi,
            'totalTsc' => $totalTsc,
            'totalReseller' => $totalReseller,
            'totalKonsinyasi' => $totalKonsinyasi,
            'satuanItem' => $satuanItem,
            'sellingPrices' => $sellingPrices,
            'unitPrice' => $unitPrice,
            
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

                
                    if ($d && isset($d['unitPriceRule']) && is_array($d['unitPriceRule'])) {
                    $discPercent = isset($d['itemDiscPercent']) ? floatval($d['itemDiscPercent']) : 0;
                    $adjustedPrices = [];

                    // Paste unitMap lengkap di sini
                    $unitMap = [
                        52850 => "1",
                        53550 => "BATANG",
                        53950 => "BOX",
                        53200 => "BTL",
                        53450 => "CAM",
                        53300 => "DUS",
                        52950 => "HPP",
                        53400 => "IKAT",
                        53600 => "KALENG",
                        53700 => "KARUNG",
                        53900 => "KG",
                        53350 => "KLG",
                        52701 => "METER",
                        52750 => "MTR",
                        53000 => "PACK",
                        53750 => "PAJAK",
                        53100 => "PAKET",
                        53151 => "PCH",
                        50 => "PCS",
                        53500 => "POTONG",
                        53650 => "RIT",
                        52900 => "ROLL",
                        53150 => "SAK",
                        53800 => "SET",
                        53050 => "UNIT",
                        53850 => "rim",
                    ];

                    foreach ($d['unitPriceRule'] as $rule) {
                        $unitId = $rule['unitId'] ?? null;
                        $price = $rule['price'] ?? null;

                        if ($unitId && $price) {
                            $unitName = $unitMap[$unitId] ?? "UNIT $unitId";
                            $finalPrice = $discPercent > 0
                                ? $price - ($price * $discPercent / 100)
                                : $price;

                            $adjustedPrices[$unitName] = $finalPrice;
                        }
                    }

                    return response()->json([
                        'success' => true,
                        'adjustedPrices' => $adjustedPrices,
                        'discItem' => $discPercent > 0 ? $discPercent : null,
                    ]);
                }
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