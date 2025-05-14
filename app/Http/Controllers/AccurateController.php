<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AccurateController extends Controller
{
    public function getCustomers(Request $request) {
        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $page = 1;
        $pageSize = 100;

        $params = [
            'sp.page' => $page,
            'sp.pageSize' => $pageSize,
            'fields' => 'id,name,email',
            'filter.customerCategoryId' => 2650, // jika tetap dipakai
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session,
        ])->get('https://public.accurate.id/accurate/api/customer/list.do', $params);

        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            return response()->json([
                'message' => 'Gagal mengambil data dari Accurate API',
                'status' => $response->status(),
            ], $response->status());
        }
    }

    public function detailItems($id)
    {
        set_time_limit(7200);
        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $detailUrl = 'https://public.accurate.id/accurate/api/item/detail.do?id=' . $id;

        $respon = Http::timeout(3600)->retry(3, 2000)->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ])->get($detailUrl);

        if ($respon->successful()) {
            $item = $respon->json()['d'];
            $detailGudang = $item['detailWarehouseData'];
            $garansiReseller = $item['charField7'];

            $filteredWarehouses = collect($detailGudang)
                ->filter(function ($w) {
                    return 
                        (is_null($w['description']) ?? true) &&
                        (!Str::contains(Str::lower($w['name']), [
                            'reseller','tsc','twintos','twinmart',
                            'marketing','asp','bazar','bina',
                            'dkv','af','barang rusak', 'sc landasan ulin', 'panda store landasan ulin', 'sc banjarbaru'
                        ]));
                });

            $warehouseMap = [];
            foreach ($filteredWarehouses as $gudang) {
                $warehouseMap[$gudang['id']] = [
                    'name' => $gudang['name'],
                    'balance' => (float) $gudang['balance']
                ];
            }

            // Sales Order
            $salesOrderUrl = 'https://public.accurate.id/accurate/api/sales-order/list.do?id=';
            $salesOrderResponse = Http::timeout(3600)->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session
            ])->get($salesOrderUrl);

            $stokBaru = $warehouseMap;

            if ($salesOrderResponse->successful()) {
                $salesOrderList = $salesOrderResponse->json()['d'];
            
                foreach ($salesOrderList as $order) {
                    $detailUrl = 'https://public.accurate.id/accurate/api/sales-order/detail.do?id=' . $order['id'];
            
                    $detailResponse = Http::timeout(3600)->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'X-Session-ID' => $session
                    ])->get($detailUrl);
            
                    if ($detailResponse->successful()) {
                        $detail = $detailResponse->json()['d'];

                        if ($detail['statusName'] === 'Menunggu diproses' || $detail['statusName'] === 'Sebagian diproses') {
                            foreach ($detail['detailItem'] as $items) {
                                if ($items['itemId'] == $id) {
                                    $warehouseId = $items['warehouseId'];
                                    $quantity = (float) $items['availableQuantity'];
                                        
                                    if (isset($stokBaru[$warehouseId])) {
                                        $stokBaru[$warehouseId]['balance'] -= $quantity;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Sales Invoice
            $salesInvoiceUrl = 'https://public.accurate.id/accurate/api/sales-invoice/list.do?id=';
            $salesInvoiceResponse = Http::timeout(3600)->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session
            ])->get($salesInvoiceUrl);

            $matchingInvoices = [];

            if ($salesInvoiceResponse->successful()) {
                $salesInvoiceList = $salesInvoiceResponse->json()['d'];

                foreach ($salesInvoiceList as $invoice) {
                    $invoiceDetailUrl = 'https://public.accurate.id/accurate/api/sales-invoice/detail.do?id=' . $invoice['id'];

                    $invoiceDetailResponse = Http::timeout(3600)->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'X-Session-ID' => $session
                    ])->get($invoiceDetailUrl);

                    if ($invoiceDetailResponse->successful()) {
                        $invoiceDetail = $invoiceDetailResponse->json()['d'];

                        if (isset($invoiceDetail['reverseInvoice']) && $invoiceDetail['reverseInvoice'] === true) {
                            foreach ($invoiceDetail['detailItem'] as $itemInvoice) {
                                if (isset($itemInvoice['item']['id']) && $itemInvoice['item']['id'] == $id) {
                                    $matchingInvoices[] = [
                                        'invoice_id' => $invoice['id'],
                                        'quantity' => (float) $itemInvoice['quantity'],
                                        'warehouse' => $itemInvoice['warehouse']['id'] ?? null
                                    ];
                                    break;
                                }
                            }
                        }
                    }
                }

                foreach ($matchingInvoices as $invoiceMatch) {
                    $warehouseId = $invoiceMatch['warehouse'];
                    $quantity = $invoiceMatch['quantity'];

                    if (isset($stokBaru[$warehouseId])) {
                        $stokBaru[$warehouseId]['balance'] -= $quantity;
                    }
                }
            }

            return response()->json([
                'item' => $item,
                'stokBaru' => $stokBaru,
                'garansiReseller' => $garansiReseller
            ], 200);
        } else {
            return response()->json([
                'error' => 'Gagal Mengambil Data Item'
            ], 400);
        }
    }


    public function getItems(Request $request)
    {
        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $search = $request->input('q');
        $page = $request->get('page', 1);
        $stokAda = $request->input('stok_ada');
        $pageSize = 100;
        
        $params = [
            'sp.page' => $page,
            'sp.pageSize' => $pageSize,
            'fields' => 'id,name,no,availableToSell',
            'filter.suspended' => 'false',
            'filter.keywords.op' => 'CONTAIN',
            'filter.keywords.val[0]' => $search,
        ];

        $respon = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ])->get('https://public.accurate.id/accurate/api/item/list.do', $params);

        if ($respon->successful()) {
            $data = $respon->json();

            if (!isset($data['sp']) || !isset($data['d'])) {
                return response()->json([
                    'error' => 'Data tidak ditemukan atau format API tidak sesuai.'
                ], 400);
            }

            $items = $data['d'];
            $apiPagination = $data['sp'];

            if ($stokAda) {
                $items = array_filter($items, function ($item) {
                    return isset($item['availableToSell']) && $item['availableToSell'] > 0;
                });
            }

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

            return response()->json([
                'items' => $items,
                'pagination' => $pagination,
                'search' => $search
            ], 200);
        } else {
            return response()->json([
                'error' => 'Gagal Mengambil Data Item: ' . json_encode($respon->json())
            ], 400);
        }
    }


}
