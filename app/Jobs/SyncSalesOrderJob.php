<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncSalesOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $itemIdUtama;
    protected $stokNew;

    public function __construct($itemIdUtama, &$stokNew)
    {
        $this->itemIdUtama = $itemIdUtama;
        $this->stokNew = &$stokNew;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = Auth::user();
        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        $page = 1;
        $allSalesOrders = collect();

        do {
            $paramses = [
                    'sp.page' => $page,
                    'fields' => 'id',
                    'sp.pageSize' => 100 // atau nilai maksimum
                ];
            
            $response = Http::withHeaders($headers)->get('https://public.accurate.id/accurate/api/sales-order/list.do', $paramses);

            $result = $response->json();
            $salesOrders = collect($result['d'] ?? []);

            $allSalesOrders = $allSalesOrders->merge($salesOrders);
            $totalPages = $result['sp']['pageCount'] ?? 1;

            $page++;
            } while ($page <= $totalPages);

            Log::info("Total SO Ditemukan", ['total' => count($allSalesOrders)]);

            $salesOrderIds = collect($allSalesOrders)->pluck('id');
            $batches = $salesOrderIds->chunk(10);

            foreach ($batches as $batch) {
                $responses = Http::pool(fn ($pool) =>
                    $batch->map(fn ($id) =>
                        $pool->withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-order/detail.do?id=$id")
                    )->all()
                );

                foreach ($responses as $detailResponse) {
                    if ($detailResponse->successful()) {
                        $detail = $detailResponse->json()['d'];

                        if ($detail['statusName'] === 'Menunggu diproses' || $detail['statusName'] === 'Sebagian diproses') {
                            foreach ($detail['detailItem'] as $itemDetail) {
                                Log::info("Cek ItemId SalesOrder", [
                                    'itemDetailItemId' => $itemDetail['itemId'],
                                    'idUtama' => $this->itemIdUtama
                                ]);
                                // Ini item dalam sales order, kita bandingkan dengan $id dari item utama
                                if ($itemDetail['itemId'] == $this->itemIdUtama) {
                                    $warehouseId = $itemDetail['warehouseId'];
                                    $quantity = (float) $itemDetail['availableQuantity'];

                                        
                                    Log::info("Kurangi Stok", [
                                        'warehouseId' => $warehouseId,
                                        'qty' => $quantity,
                                        'before' => $stokNew[$warehouseId]['balance'] ?? 'tidak ada',
                                        'after' => isset($stokNew[$warehouseId]) ? $stokNew[$warehouseId]['balance'] - $quantity : 'tidak ada',
                                    ]);

                                    if (isset($stokNew[$warehouseId])) {
                                        $stokNew[$warehouseId]['balance'] -= $quantity;
                                    }
                                }
                            } 
                        }
                    } elseif ($detailResponse->status() == 429) {
                         sleep(3);
                }
            }        
            usleep(1000000); // Jeda 1 detik
        }
    }
}
