<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function getItemDetailsApi($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User belum login.'], 401);
        }

        // lanjutkan kalau user tidak null
        $status = $user->status;

        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        $poolResponses = Http::pool(fn ($pool) => [
            $pool->withHeaders($headers)->get("https://public.accurate.id/accurate/api/item/detail.do?id=$id"),
            $pool->withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-order/list.do?id="),
            $pool->withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-invoice/list.do?id="),
        ]);

        $itemResponse = $poolResponses[0];
        $salesOrderList = $poolResponses[1]->json()['d'] ?? [];
        $salesInvoiceList = $poolResponses[2]->json()['d'] ?? [];

        if (!$itemResponse->successful()) {
            return response()->json(['error' => 'Gagal Mengambil Data Item'], 500);
        }

        $item = $itemResponse->json()['d'];
        $detailWarehouse = $item['detailWarehouseData'];
        $garansiUser = $item['charField6'];
        $garansiReseller = $item['charField7'];
        $fileName = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray();

        $konsinyasiWarehouses = collect($detailWarehouse)->filter(fn($w) =>
            Str::contains(Str::lower($w['description'] ?? ''), 'konsinyasi'));

        $tscWarehouses = collect($detailWarehouse)->filter(fn($w) =>
            Str::contains(Str::lower($w['name'] ?? ''), ['tsc', 'panda sc banjarbaru']));

        $nonKonsinyasiWarehouses = collect($detailWarehouse)->filter(fn($w) =>
            is_null($w['description']) &&
            !Str::contains(Str::lower($w['name']), [
                'reseller','tsc','twintos','twinmart',
                'marketing','asp','bazar','bina',
                'dkv','af','barang rusak', 'sc landasan ulin', 'panda store landasan ulin', 'sc banjarbaru'
            ]));

        $filteredWarehouses = match ($user->status) {
            'karyawan', 'admin' => $konsinyasiWarehouses->merge($tscWarehouses)->merge($nonKonsinyasiWarehouses),
            'reseller' => $konsinyasiWarehouses->merge($nonKonsinyasiWarehouses),
            default => collect()
        };

        $stokNew = [];
        foreach ($filteredWarehouses as $warehouseDetail) {
            $stokNew[$warehouseDetail['id']] = [
                'name' => $warehouseDetail['name'],
                'balance' => $warehouseDetail['balance']
            ];
        }

        // Sales Order
        $salesOrderIds = collect($salesOrderList)->pluck('id');
        foreach ($salesOrderIds->chunk(20) as $batch) {
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
            usleep(500000);
        }

        // Invoice
        $matchingInvoices = [];
        foreach (collect($salesInvoiceList)->pluck('id')->chunk(20) as $batch) {
            $responses = Http::pool(fn ($pool) =>
                $batch->map(fn ($id) =>
                    $pool->withHeaders($headers)->timeout(60)->retry(3, 1000)
                        ->get("https://public.accurate.id/accurate/api/sales-invoice/detail.do?id=$id")
                )->all()
            );

            foreach ($responses as $index => $response) {
                if ($response->successful()) {
                    $invoiceDetail = $response->json()['d'];
                    $invoiceId = $batch->values()[$index];

                    if ($invoiceDetail['reverseInvoice'] ?? false) {
                        foreach ($invoiceDetail['detailItem'] as $itemInvoice) {
                            if (($itemInvoice['item']['id'] ?? null) == $id) {
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

        return response()->json([
            'item' => $item,
            'stokNew' => array_values($stokNew),
            'garansiReseller' => $garansiReseller,
            'garansiUser' => $garansiUser,
            'fileName' => $fileName,
            'session' => $session,
        ]);
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
