<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\AccurateGlobal;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Http;

class KaryawanController extends Controller
{
    public function show($encrypted, Request $request)
    {
            // 🔹 Decode ID dari Hashid
        $decoded = Hashids::decode($encrypted);
        $id = $decoded[0] ?? null;

        if (!$id) {
            abort(404, 'ID item tidak valid');
        }

        // ============================================================
        // 🔹 Ambil token & session dari AccurateGlobal
        // ============================================================
        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];
        $branchName = $request->input('branchName');

        $baseUrl = 'https://public.accurate.id/accurate/api';

        // 🔹 Ambil detail item
        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("{$baseUrl}/item/detail.do", ['id' => $id]);

        $item = $resp->json()['d'] ?? null;
        if (!$item) {
            return back()->with('error', 'Gagal mengambil data item dari Accurate.');
        }

        $fileName = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray();

        // ============================================================
        // 🔹 Ambil dua harga: USER & RESELLER
        // ============================================================
        $prices = [];

        // Harga USER
        $defaultResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
            'id' => $id,
            'branchName' => $branchName, // 🔹 tambahan penting
        ]);

        $prices['user'] = $defaultResp['d']['unitPrice']
            ?? ($defaultResp['d']['unitPriceRule'][0]['price'] ?? 0);

        // Harga RESELLER
        $resellerResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
            'id' => $id,
            'priceCategoryName' => 'RESELLER',
            'discountCategoryName' => 'RESELLER',
            'branchName' => $branchName, // 🔹 tambahan penting
        ]);

        $resellerPrice = $resellerResp['d']['unitPrice']
            ?? ($resellerResp['d']['unitPriceRule'][0]['price'] ?? 0);

        // Diskon kalau ada
        $discountRule = $resellerResp['d']['discountRule'][0] ?? null;
        if ($discountRule && isset($discountRule['discount']) && is_numeric($discountRule['discount'])) {
            $discountPercent = floatval($discountRule['discount']);
            $resellerPrice -= ($resellerPrice * $discountPercent / 100);
        }
        $prices['reseller'] = $resellerPrice;

        $item['price'] = $prices['user'];

        // ============================================================
        // 🔹 Ambil gudang & update stok real-time (pakai pool)
        // ============================================================
        $warehouses = collect($item['detailWarehouseData'] ?? [])->map(function ($wh) {
            $unitParts = explode(' ', $wh['balanceUnit']);
            $wh['unit'] = $unitParts[1] ?? null;
            return $wh;
        });

        $warehouseNames = $warehouses->pluck('name')->filter()->values();
        $updatedStocks = [];

        $chunks = $warehouseNames->chunk(8);
        foreach ($chunks as $batch) {
            $responses = Http::pool(fn($pool) =>
                $batch->map(fn($name) =>
                    $pool->as($name)->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'X-Session-ID'  => $session,
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
                }
            }
            usleep(500000);
        }

        // Update stok realtime
        $warehouses = $warehouses->map(function ($wh) use ($updatedStocks) {
            $name = $wh['name'] ?? null;
            if ($name && isset($updatedStocks[$name])) {
                $wh['balance'] = $updatedStocks[$name];
            }
            return $wh;
        })->filter(fn($wh) => isset($wh['balance']) && $wh['balance'] > 0)->values();

        // ============================================================
        // 🔹 Pisah berdasarkan tipe gudang
        // ============================================================
        $warehousesKonsinyasi = $warehouses->filter(fn($wh) =>
            isset($wh['description']) && Str::contains(strtolower($wh['description']), 'konsinyasi')
        )->values();

        $storeNames = [
            'TSTORE KAYUTANGI', 'TSTORE BANJARBARU A. YANI', 'TSTORE BANJARBARU P. BATUR',
            'TSTORE BELITUNG', 'TSTORE MARTAPURA', 'TDC',
            'STORE PALANGKARAYA', 'LANDASAN ULIN', 'PANDA STORE BANJARBARU',
        ];
        $warehousesStore = $warehouses->filter(fn($wh) =>
            in_array(strtoupper($wh['name'] ?? ''), $storeNames)
        )->values();

        $resellerNames = ['RESELLER ZAKI', 'RESELLER MARDANI'];
        $warehousesReseller = $warehouses->filter(fn($wh) =>
            in_array(strtoupper($wh['name'] ?? ''), $resellerNames)
        )->values();

        $tscNames = [
            'TSC BANJARBARU A. YANI', 'TSC BANJARBARU P. BATUR', 'TSC BELITUNG',
            'TSC KAYUTANGI', 'TSC LANDASAN ULIN', 'TSC MARTAPURA', 'TSC PALANGKARAYA',
        ];
        $warehousesTsc = $warehouses->filter(fn($wh) =>
            in_array(strtoupper($wh['name'] ?? ''), $tscNames)
        )->values();

        return view('reseller.karyawan.detail', [
            'item'                => $item,
            'warehouses'          => $warehouses,
            'warehousesKonsinyasi'=> $warehousesKonsinyasi,
            'warehousesStore'     => $warehousesStore,
            'warehousesReseller'  => $warehousesReseller,
            'warehousesTsc'       => $warehousesTsc,
            'totalKonsinyasi'     => $warehousesKonsinyasi->sum('balance'),
            'totalStore'          => $warehousesStore->sum('balance'),
            'totalReseller'       => $warehousesReseller->sum('balance'),
            'totalTsc'            => $warehousesTsc->sum('balance'),
            'images'              => $fileName,
            'session'             => $session,
            'prices'              => $prices,
        ]);
    }

    public function getPrice(Request $request, $id)
    {
        $branchName = $request->input('branchName');

        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        $baseUrl = 'https://public.accurate.id/accurate/api';

        // Harga USER
        $defaultResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("{$baseUrl}/item/get-selling-price.do", [
            'id' => $id,
            'branchName' => $branchName,
        ]);

        $userPrice = $defaultResp['d']['unitPrice']
            ?? ($defaultResp['d']['unitPriceRule'][0]['price'] ?? 0);

        // Harga RESELLER
        $resellerResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("{$baseUrl}/item/get-selling-price.do", [
            'id' => $id,
            'priceCategoryName' => 'RESELLER',
            'discountCategoryName' => 'RESELLER',
            'branchName' => $branchName,
        ]);

        $resellerPrice = $resellerResp['d']['unitPrice']
            ?? ($resellerResp['d']['unitPriceRule'][0]['price'] ?? 0);

        $discountRule = $resellerResp['d']['discountRule'][0] ?? null;
        if ($discountRule && isset($discountRule['discount'])) {
            $disc = floatval($discountRule['discount']);
            $resellerPrice -= ($resellerPrice * $disc / 100);
        }

        return response()->json([
            'user' => $userPrice,
            'reseller' => $resellerPrice,
        ]);
    }
}
