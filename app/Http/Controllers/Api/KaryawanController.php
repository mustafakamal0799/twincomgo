<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\AccurateGlobal;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class KaryawanController extends Controller
{
    public function apiDetail($encrypted, Request $request)
    {
        // 1️⃣ Decode hashid
        $decoded = Hashids::decode($encrypted);
        $id = $decoded[0] ?? null;

        if (!$id) {
            return response()->json(['error' => 'ID item tidak valid'], 400);
        }

        // 2️⃣ Ambil token Accurate
        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];
        $branchName = $request->input('branchName');
        $baseUrl = 'https://public.accurate.id/accurate/api';

        // 3️⃣ Ambil detail item
        $resp = Http::withHeaders([
            'Authorization' => "Bearer $token",
            'X-Session-ID'  => $session,
        ])->get("$baseUrl/item/detail.do", ['id' => $id]);

        $item = $resp->json()['d'] ?? null;
        if (!$item) {
            return response()->json(['error' => 'Item tidak ditemukan'], 404);
        }

        // 4️⃣ Ambil images
        $images = collect($item['detailItemImage'] ?? [])
            ->pluck('fileName')
            ->filter()
            ->map(fn($file) => "https://public.accurate.id/accurate/api/item/image.do?file={$file}&session={$session}")
            ->values();

        // 5️⃣ Harga user & reseller
        $prices = [
            'user'     => $this->getSellingPrice($id, $token, $session, $branchName),
            'reseller' => $this->getSellingPrice($id, $token, $session, $branchName, 'RESELLER'),
        ];

        // 6️⃣ Ambil semua gudang dari Accurate
        $warehouses = collect($item['detailWarehouseData'] ?? [])->map(function ($wh) {
            $unit = explode(' ', $wh['balanceUnit']);
            $wh['unit'] = $unit[1] ?? null;
            return $wh;
        });

        // 7️⃣ GET STOK REALTIME (pakai POOL)
        $warehouseNames = $warehouses->pluck('name')->filter()->values();
        $updatedStocks = [];

        foreach ($warehouseNames->chunk(8) as $chunk) {
            $responses = Http::pool(fn($pool) =>
                collect($chunk)->map(fn($name) =>
                    $pool->as($name)->withHeaders([
                        'Authorization' => "Bearer $token",
                        'X-Session-ID'  => $session,
                    ])->get("$baseUrl/item/get-on-sales.do", [
                        'id' => $id,
                        'warehouseName' => $name,
                    ])
                )
            );

            foreach ($responses as $name => $value) {
                if ($value->successful()) {
                    $updatedStocks[$name] = $value['d']['availableStock'] ?? 0;
                }
            }
        }

        $warehouses = $warehouses->map(function ($wh) use ($updatedStocks) {
            if (isset($updatedStocks[$wh['name']])) {
                $wh['balance'] = $updatedStocks[$wh['name']];
            }
            return $wh;
        })->filter(fn($wh) => ($wh['balance'] ?? 0) > 0)->values();

        // 8️⃣ Kelompokkan gudang
        $groups = [
            'konsinyasi' => fn($wh) => Str::contains(strtolower($wh['description'] ?? ''), 'konsinyasi'),
            'store' => fn($wh) => in_array(strtoupper($wh['name']), [
                'TSTORE KAYUTANGI', 'TSTORE BANJARBARU A. YANI',
                'TSTORE BANJARBARU P. BATUR', 'TSTORE BELITUNG',
                'TSTORE MARTAPURA', 'TDC', 'STORE PALANGKARAYA',
                'LANDASAN ULIN',
            ]),
            'panda' => fn($wh) => in_array(strtoupper($wh['name']), [
                'PANDA STORE BANJARBARU', 'PANDA SC BANJARBARU',
            ]),
            'reseller' => fn($wh) => in_array(strtoupper($wh['name']), [
                'RESELLER ZAKI', 'RESELLER MARDANI',
            ]),
            'tsc' => fn($wh) => in_array(strtoupper($wh['name']), [
                'TSC BANJARBARU A. YANI', 'TSC BANJARBARU P. BATUR',
                'TSC BELITUNG', 'TSC KAYUTANGI', 'TSC LANDASAN ULIN',
                'TSC MARTAPURA', 'TSC PALANGKARAYA',
            ]),
        ];

        $warehousesByGroup = [];
        foreach ($groups as $key => $fn) {
            $warehousesByGroup[$key] = $warehouses->filter($fn)->values();
        }

        // 9️⃣ Return JSON lengkap → siap dipakai FRONTEND
        return response()->json([
            'item'  => [
                'id'    => $id,
                'name'  => $item['name'] ?? null,
                'no'    => $item['no'] ?? null,
                'unit'  => $item['unitId']['name'] ?? null,
                'detail' => $item,
                'images' => $images,
            ],

            'prices' => $prices,

            'warehouses' => [
                'all'         => $warehouses,
                'konsinyasi'  => $warehousesByGroup['konsinyasi'],
                'store'       => $warehousesByGroup['store'],
                'tsc'         => $warehousesByGroup['tsc'],
                'panda'       => $warehousesByGroup['panda'],
                'reseller'    => $warehousesByGroup['reseller'],
            ],

            'totals' => [
                'konsinyasi' => $warehousesByGroup['konsinyasi']->sum('balance'),
                'store'      => $warehousesByGroup['store']->sum('balance'),
                'tsc'        => $warehousesByGroup['tsc']->sum('balance'),
                'panda'      => $warehousesByGroup['panda']->sum('balance'),
                'reseller'   => $warehousesByGroup['reseller']->sum('balance'),
            ],

            'session' => $session,
        ]);
    }

    private function getSellingPrice($id, $token, $session, $branchName, $category = null)
    {
        $params = [
            'id' => $id,
            'branchName' => $branchName,
        ];

        if ($category === 'RESELLER') {
            $params['priceCategoryName'] = 'RESELLER';
            $params['discountCategoryName'] = 'RESELLER';
        }

        $resp = Http::withHeaders([
            'Authorization' => "Bearer $token",
            'X-Session-ID'  => $session,
        ])->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", $params);

        $data = $resp->json()['d'] ?? [];

        $price = $data['unitPrice']
            ?? ($data['unitPriceRule'][0]['price'] ?? 0);

        $disc = $data['discountRule'][0]['discount'] ?? null;
        if ($disc) {
            $price -= ($price * floatval($disc) / 100);
        }

        return $price;
    }

    public function imageProxy(Request $request)
    {
        $file = $request->query('file');
        if (!$file) {
            return response()->file(public_path('images/noimage.jpg'));
        }

        // AMBIL TOKEN + SESSION Accurate
        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        // URL Accurate
        $url = "https://public.accurate.id/accurate/api/item/image.do?file=" . urlencode($file);

        // REQUEST pakai session + token
        $resp = Http::withHeaders([
            'Authorization' => "Bearer $token",
            'X-Session-ID'  => $session,
        ])->get($url);

        // Kalau gagal, pakai noimage bawaan
        if (!$resp->successful()) {
            return response()->file(public_path('images/noimage.jpg'));
        }

        // STREAM ke browser
        return response($resp->body(), 200)->header('Content-Type', 'image/jpeg');
    }

}
