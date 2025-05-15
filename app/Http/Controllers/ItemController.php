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
            'fields' => 'id,name,no,availableToSell,itemCategoryId',
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

            // dd($allCategories);  

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

    
    // public function detailItems($id) {
    //     set_time_limit(7200);
    //     $token = env('ACCURATE_API_TOKEN');
    //     $session = env('ACCURATE_SESSION');

    //     $detailUrl = 'https://public.accurate.id/accurate/api/item/detail.do?id=' . $id;

    //     $respon = Http::timeout(3600)->retry(3, 2000)->withHeaders([
    //         'Authorization' => 'Bearer ' . $token,
    //         'X-Session-ID' => $session
    //     ])->get($detailUrl);

    //     if ($respon->successful()) {
            
    //         $item = $respon->json()['d'];
    //         $detailGudang = $item['detailWarehouseData'];
    //         $garansiReseller = $item['charField7'];

    //        // dd($item);

    //         $filteredWarehouses = collect($detailGudang)
    //             ->filter(function ($w) {
    //             return 
    //                 (is_null($w['description']) ?? true) &&
    //                 (!Str::contains(Str::lower($w['name']), [
    //                     'reseller','tsc','twintos','twinmart',
    //                         'marketing','asp','bazar','bina',
    //                         'dkv','af','barang rusak', 'sc landasan ulin', 'panda store landasan ulin', 'sc banjarbaru'
    //                 ]));
    //         });

    //         $warehouseMap = [];
    //         foreach ($filteredWarehouses as $gudang) {
    //             $warehouseMap[$gudang['id']] = [
    //                 'name' => $gudang['name'],
    //                 'balance' => (float) $gudang['balance']
    //             ];
    //         }        

    //         $salesOrderUrl = 'https://public.accurate.id/accurate/api/sales-order/list.do?id=';
    //         $salesOrderResponse = Http::timeout(3600)->withHeaders([
    //             'Authorization' => 'Bearer ' . $token,
    //             'X-Session-ID' => $session
    //         ])->get($salesOrderUrl);

    //         $stokBaru = $warehouseMap;

    //         if ($salesOrderResponse->successful()) {
    //             $salesOrderList = $salesOrderResponse->json()['d'];
            
    //             foreach ($salesOrderList as $order) {
    //                 // Ambil detail sales order satu per satu
    //                 $detailUrl = 'https://public.accurate.id/accurate/api/sales-order/detail.do?id=' . $order['id'];
            
    //                 $detailResponse = Http::timeout(3600)->withHeaders([
    //                     'Authorization' => 'Bearer ' . $token,
    //                     'X-Session-ID' => $session
    //                 ])->get($detailUrl);
            
    //                 if ($detailResponse->successful()) {
    //                     $detail = $detailResponse->json()['d'];
        
    //                     if ($detail['statusName'] === 'Menunggu diproses' || $detail['statusName'] === 'Sebagian diproses') {
    //                         foreach ($detail['detailItem'] as $items) {
    //                             if($items['itemId'] == $id){
    //                             $warehouseId = $items['warehouseId'];
    //                             $quantity = (float) $items['availableQuantity'];
                                    
    //                                 // Kurangi stok jika gudang cocok
    //                                 if (isset($stokBaru[$warehouseId])) {
    //                                     $stokBaru[$warehouseId]['balance'] -= $quantity;
    //                                 } 
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
               
    //         }
    //         $salesInvoiceUrl = 'https://public.accurate.id/accurate/api/sales-invoice/list.do?id=';

    //         $salesInvoiceResponse = Http::timeout(3600)->withHeaders([
    //             'Authorization' => 'Bearer ' . $token,
    //             'X-Session-ID' => $session
    //         ])->get($salesInvoiceUrl);

    //         $matchingInvoices = []; // Untuk menyimpan id + quantity

    //         if ($salesInvoiceResponse->successful()) {
    //             $salesInvoiceList = $salesInvoiceResponse->json()['d'];

    //             foreach ($salesInvoiceList as $invoice) {
    //                 $invoiceDetailUrl = 'https://public.accurate.id/accurate/api/sales-invoice/detail.do?id=' . $invoice['id'];

    //                 $invoiceDetailResponse = Http::timeout(3600)->withHeaders([
    //                     'Authorization' => 'Bearer ' . $token,
    //                     'X-Session-ID' => $session
    //                 ])->get($invoiceDetailUrl);

    //                 if ($invoiceDetailResponse->successful()) {
    //                     $invoiceDetail = $invoiceDetailResponse->json()['d'];

    //                     // Filter hanya jika reverseInvoice = true
    //                     if (isset($invoiceDetail['reverseInvoice']) && $invoiceDetail['reverseInvoice'] === true) {
    //                         foreach ($invoiceDetail['detailItem'] as $itemInvoice) {
    //                             if (isset($itemInvoice['item']['id']) && $itemInvoice['item']['id'] == $id) {
    //                                 $matchingInvoices[] = [
    //                                     'invoice_id' => $invoice['id'],
    //                                     'quantity' => (float) $itemInvoice['quantity'],
    //                                     'warehouse' => $itemInvoice['warehouse']['id'] ?? null
    //                                 ];
    //                                 break;
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         foreach ($matchingInvoices as $invoiceMatch) {
    //             $warehouseId = $invoiceMatch['warehouse'];
    //             $quantity = $invoiceMatch['quantity'];

    //             // dd($warehouseId, $quantity, $stokBaru);

    //             if (isset($stokBaru[$warehouseId])) {
    //                 $stokBaru[$warehouseId]['balance'] -= $quantity;
    //             }
    //         }

    //         // dd($stokBaru);
    //         return view('items.detail', compact('item', 'stokBaru', 'garansiReseller'));
    //     } else {
    //         return back()->withErrors('Gagal Mengambil Data Item');
    //     }
    // }

    public function getAccurateImage($filename)
    {
        Log::info('Request tanpa header ke: ' . $filename);

        $url = "https://public.accurate.id/{$filename}";

        $response = Http::get($url); // Tanpa headers

        if ($response->successful()) {
            return Response::make($response->body(), 200, [
                'Content-Type' => $response->header('Content-Type'),
            ]);
        }

        Log::warning("Gagal mengambil gambar TANPA header dari Accurate: $url");
        return abort(404, 'Image not found.');
    }


    public function getItemDetails ($id) {

        $user = Auth::user();
        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        $poolResponses = Http::pool(fn ($pool) => [
            'item' => $pool->withHeaders($headers)->get("https://public.accurate.id/accurate/api/item/detail.do?id=$id"),
            'salesOrderList' => $pool->withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-order/list.do?id="),
            'salesInvoiceList' => $pool->withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-invoice/list.do?id="),
        ]);

        $responses = [
            'item' => $poolResponses[0],
            'salesOrderList' => $poolResponses[1],
            'salesInvoiceList' => $poolResponses[2],
        ];

        if(! $responses['item']->successful()){
            return back()->withErrors('Gagal Mengambil Data Item');
        } 

         $item = $responses['item']->json()['d'];
         $salesOrderList = $responses['salesOrderList']->json()['d'] ?? [];
         $salesInvoiceList = $responses['salesInvoiceList']->json()['d'] ?? [];



         // Detail Gudang dan Garansi Reseller
         $detailWarehouse = $item['detailWarehouseData'];
         $garansiReseller = $item['charField7'];
         $image = $item['detailItemImage'][0] ?? null;
        $fileName = $image['fileName'] ?? null;

        if ($fileName) {
            $imageUrl = "https://public.accurate.id" . $fileName;
        } else {
            $imageUrl = null; // Jika tidak ada gambar
        }

        $konsinyasiWarehouses = collect($detailWarehouse)->filter(function ($w) {
            return Str::contains(Str::lower($w['description'] ?? ''), 'konsinyasi');
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
            $filteredWarehouses = $konsinyasiWarehouses->merge($nonKonsinyasiWarehouses);
        } elseif ($user->status === 'reseller') {
            $filteredWarehouses = $nonKonsinyasiWarehouses;
        } else {
            $filteredWarehouses = collect(); // Default jika status tidak dikenal
        }

        // Filter Gudang dengan Nama
        // $filteredWarehouses = collect($detailWarehouse)
        //     ->filter(function ($w) {
        //     return 
        //         (is_null($w['description']) ?? true) &&
        //         (!Str::contains(Str::lower($w['name']), [
        //             'reseller','tsc','twintos','twinmart',
        //                 'marketing','asp','bazar','bina',
        //                 'dkv','af','barang rusak', 'sc landasan ulin', 'panda store landasan ulin', 'sc banjarbaru'
        //         ]));
        // });

        $stokNew = [];

        foreach ($filteredWarehouses as $warehouseDetail) {
            $stokNew[$warehouseDetail['id']] = [
                'name' => $warehouseDetail['name'],
                'balance' => $warehouseDetail['balance']
            ];
        }

        $salesOrderIds = collect($salesOrderList)->pluck('id');

        $batches = $salesOrderIds->chunk(30);

        foreach ($batches as $batch) {
    // 4. Kirim semua request dalam batch secara paralel
            $responses = Http::pool(fn ($pool) =>
                $batch->map(fn ($id) =>
                    $pool->withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-order/detail.do?id=$id")
                )->all()
            );
                foreach ($responses as $detailResponse) {
                    if ($detailResponse->successful()) {
                        $detail = $detailResponse->json()['d'];

                        if (in_array($detail['statusName'], ['Menunggu diproses', 'Sebagian diproses'])) {
                            foreach ($detail['detailItem'] as $items) {
                                if ($items['itemId'] == $id) {
                                    $warehouseId = $items['warehouseId'];
                                    $quantity = (float) $items['availableQuantity'];

                                    if (isset($stokNew[$warehouseId])) {
                                        $stokNew[$warehouseId]['balance'] -= $quantity;
                                    }
                                }
                            }
                        }
                    }
                }
            usleep(500000); // jeda 0.5 detik
        }

        $matchingInvoices = [];

        $salesInvoiceIds = collect($salesInvoiceList)->pluck('id');
        $batches = $salesInvoiceIds->chunk(30);

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
                            if (isset($itemInvoice['item']['id']) && $itemInvoice['item']['id'] == $id) {
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

        foreach ($matchingInvoices as $invoiceMatch) {
        $warehouseId = $invoiceMatch['warehouse'];
        $quantity = $invoiceMatch['quantity'];

            if (isset($stokNew[$warehouseId])) {
                $stokNew[$warehouseId]['balance'] -= $quantity;
            }
        }
        // 6. Return view ke Blade
        return view('items.detail', [
            'item' => $item,
            'stokNew' => $stokNew,
            'garansiReseller' => $garansiReseller,
            'fileName' => $fileName,
            'imageUrl' => $imageUrl,
            'konsinyasiWarehouses' => $konsinyasiWarehouses,
            'nonKonsinyasiWarehouses' => $nonKonsinyasiWarehouses,
        ]);

    }
}
