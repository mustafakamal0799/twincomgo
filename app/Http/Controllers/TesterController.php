<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TesterController extends Controller
{

    public function test () {

        return view('items.test-table');
    }

    public function invoice () {
        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ];

        $saleInvoiceList = [];
        $page = 1;

        do {
            Log::info("Mengambil halaman sales invoice ke-$page dengan filter reverseInvoice=true");
            $response = Http::timeout(100)->withHeaders($headers)->get("https://public.accurate.id/accurate/api/sales-invoice/list.do", [
                'sp.page' => $page,
                'sp.pageSize' => 100,
                'fields' => 'id,number,reverseInvoice',
                'filter.reverseInvoice' => 'true',
            ]);

            Log::info("Request URL: " . $response->effectiveUri());
            Log::info("Response status: " . $response->status());
            Log::info("Response body: " . json_encode($response->json()));

            if (!$response->successful()) {
                Log::warning("Gagal mengambil halaman sales invoice ke-$page");
                break;
            }

            $data = $response->json();
            $saleInvoiceList = array_merge($saleInvoiceList, $data['d'] ?? []);
            $hasNext = ($page * 100) < ($data['sp']['rowCount'] ?? 0);
            $page++;

        } while ($hasNext);

        // ✅ Tambahkan pengecekan agar tidak error
        if (empty($saleInvoiceList)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data Sales Invoice ditemukan.',
            ]);
        }

        $batchSize = 8;
        $salesInvoiceChunks = array_chunk($saleInvoiceList, $batchSize);

        $invoiceDetail = [];

        foreach ($salesInvoiceChunks as $batch) {
            $responses = Http::pool(fn ($pool) =>
                collect($batch)->map(fn ($invoice) =>
                    $pool->timeout(100)->withHeaders($headers)
                        ->get("https://public.accurate.id/accurate/api/sales-invoice/detail.do?id=" . $invoice['id'])
                )->all()
            );

            foreach ($responses as $response) {
                if ($response->successful()) {
                    $invoiceDetail[] = $response->json()['d'];
                }
            }
        }

        dd($invoiceDetail);
    }

}
