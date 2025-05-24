<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SyncAdjustedStock extends Command
{
    protected $signature = 'sync:adjusted-stock';

    protected $description = 'Sync adjusted stock balances from sales orders and invoices and cache the result';

    public function handle()
    {
        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        $this->info('Fetching sales order list...');
        $salesOrderListResponse = Http::withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-order/list.do?id=");
        if (! $salesOrderListResponse->successful()) {
            $this->error('Failed to fetch sales order list');
            return 1;
        }
        $salesOrderList = $salesOrderListResponse->json()['d'] ?? [];
        $salesOrderIds = collect($salesOrderList)->pluck('id');
        $batches = $salesOrderIds->chunk(50);

        $stokNew = [];

        foreach ($batches as $batch) {
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
                            $warehouseId = $items['warehouseId'];
                            $quantity = (float) $items['availableQuantity'];
                            if (isset($stokNew[$warehouseId])) {
                                $stokNew[$warehouseId] -= $quantity;
                            } else {
                                $stokNew[$warehouseId] = -$quantity;
                            }
                        }
                    }
                }
            }
            usleep(500000);
        }

        $this->info('Fetching sales invoice list...');
        $salesInvoiceListResponse = Http::withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-invoice/list.do?id=");
        if (! $salesInvoiceListResponse->successful()) {
            $this->error('Failed to fetch sales invoice list');
            return 1;
        }
        $salesInvoiceList = $salesInvoiceListResponse->json()['d'] ?? [];
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
                    if (isset($invoiceDetail['reverseInvoice']) && $invoiceDetail['reverseInvoice'] === true) {
                        foreach ($invoiceDetail['detailItem'] as $itemInvoice) {
                            $warehouseId = $itemInvoice['warehouse']['id'] ?? null;
                            $quantity = (float) $itemInvoice['quantity'];
                            if ($warehouseId !== null) {
                                if (isset($stokNew[$warehouseId])) {
                                    $stokNew[$warehouseId] -= $quantity;
                                } else {
                                    $stokNew[$warehouseId] = -$quantity;
                                }
                            }
                        }
                    }
                }
            }
            usleep(500000);
        }

        Cache::put('adjusted_stock_balances', $stokNew, 3600); // cache for 1 hour

        $this->info('Adjusted stock balances cached successfully.');

        return 0;
    }
}
