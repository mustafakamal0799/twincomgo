<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\AccurateGlobal;
use App\Helpers\AccurateHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpParser\Node\Stmt\Foreach_;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
use App\Services\Accurate\AccurateClient;

class ItemController extends Controller
{
    private function fetchItemsForList(Request $request)
    {
        $user   = Auth::user();
        $status = $user->status;

        $acc     = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $perPage    = 11;
        $page       = max(1, (int) $request->query('page', 1));
        $offset     = ($page - 1) * $perPage;

        $search     = trim($request->query('search', ''));
        $categoryId = $request->query('category_id');
        $priceMode  = $request->query('price_mode', 'default');
        $stokAda    = $request->query('stok_ada', '1');
        $minPrice   = $request->filled('min_price') ? floatval(str_replace(['.', ','], ['', '.'], $request->input('min_price'))) : null;
        $maxPrice   = $request->filled('max_price') ? floatval(str_replace(['.', ','], ['', '.'], $request->input('max_price'))) : null;
        $usePriceFilter = ($minPrice !== null || $maxPrice !== null);

        // =========================================================
        // 🧩 1️⃣ Ambil total global sesuai filter aktif
        // =========================================================
        $baseQuery = [
            'sp.page'      => 1,
            'sp.pageSize'  => 100, // ambil 100 data untuk sampling stok ready
            'fields'       => 'id,availableToSell',
            'filter.suspended' => false,
        ];

        if ($search !== '') {
            $baseQuery['filter.keywords.op'] = 'CONTAIN';
            $baseQuery['filter.keywords.val[0]'] = $search;
        }
        if (!empty($categoryId)) {
            $baseQuery['filter.itemCategoryId'] = $categoryId;
        }

        // === Ambil data sample + total global ===
        $baseResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("{$baseUrl}/item/list.do", $baseQuery);

        $totalItemsAccurate = 0;
        $totalStokReadyEstimate = 0;

        if ($baseResp->successful()) {
            $jsonBase = $baseResp->json();
            $totalItemsAccurate = $jsonBase['sp']['rowCount'] ?? 0;

            $dataSample = collect($jsonBase['d'] ?? []);
            $stokReadyCount = $dataSample->filter(fn($i) => ($i['availableToSell'] ?? 0) > 0)->count();
            $ratioReady = $dataSample->count() > 0 ? $stokReadyCount / $dataSample->count() : 1;

            // kalau filter harga aktif, hitung ulang di sisi app
            if ($usePriceFilter) {
                $dataSample = $dataSample->map(function ($it) use ($token, $session) {
                    $it['price'] = $this->getPriceGlobal($it['id'], $token, $session);
                    return $it;
                })->filter(function ($it) use ($minPrice, $maxPrice) {
                    if ($minPrice !== null && $it['price'] < $minPrice) return false;
                    if ($maxPrice !== null && $it['price'] > $maxPrice) return false;
                    return true;
                })->values();

                // update rasio berdasarkan sample terfilter harga
                $stokReadyCount = $dataSample->filter(fn($i) => ($i['availableToSell'] ?? 0) > 0)->count();
                $ratioReady = $dataSample->count() > 0 ? $stokReadyCount / $dataSample->count() : 1;
            }

            $totalStokReadyEstimate = round($totalItemsAccurate * $ratioReady);
        }

        // ===============================================
        // Perulangan untuk mengambil items dari accurate
        // ===============================================
        $items = collect();
        $currentPageAccurate = 1;
        $pageCountAccurate = 1;
        $skipped = 0;
        $limitNeed = $perPage + 3;
        $totalFilteredAll = 0;

        do {
            $query = [
                'sp.page'      => $currentPageAccurate,
                'sp.pageSize'  => 100,
                'fields'       => 'id,name,no,availableToSell,itemCategory.name,availableToSellInAllUnit,detailItemImage',
                'filter.suspended' => false,
            ];

            if ($search !== '') {
                $query['filter.keywords.op'] = 'CONTAIN';
                $query['filter.keywords.val[0]'] = $search;
            }
            if (!empty($categoryId)) {
                $query['filter.itemCategoryId'] = $categoryId;
            }

            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->get("{$baseUrl}/item/list.do", $query);

            if (!$resp->successful()) break;
            $json = $resp->json();
            $pageCountAccurate = $json['sp']['pageCount'] ?? 1;
            $totalItemsAccurate = $json['sp']['rowCount'] ?? 0;
            $data = collect($json['d'] ?? []);

            $filtered = ($stokAda == '1')
                ? $data->filter(fn($i) => ($i['availableToSell'] ?? 0) > 0)->values()
                : $data->values();

            if ($usePriceFilter) {
                $filtered = $filtered->map(function ($it) use ($token, $session) {
                    $it['price'] = $this->getPriceGlobal($it['id'], $token, $session);
                    return $it;
                })->filter(function ($it) use ($minPrice, $maxPrice) {
                    if ($minPrice !== null && $it['price'] < $minPrice) return false;
                    if ($maxPrice !== null && $it['price'] > $maxPrice) return false;
                    return true;
                })->values();
            }

            if ($skipped < $offset) {
                $canSkip = min($filtered->count(), $offset - $skipped);
                $skipped += $canSkip;
                $filtered = $filtered->slice($canSkip)->values();
            }

            if ($filtered->isNotEmpty()) {
                $need = $limitNeed - $items->count();
                $items = $items->merge($filtered->take($need));
            }

            // Break untuk item jika sudah mencukupi dari limit yang sudah diatur
            if ($items->count() >= $limitNeed) break;

            $currentPageAccurate++;
        } while ($currentPageAccurate <= $pageCountAccurate);

        $hasMore = $items->count() > $perPage;

        // =========================================================
        // 🧩 3️⃣ Tentukan totalItemsFiltered (akurat + aman)
        // =========================================================
        if ($usePriceFilter || $stokAda == '1') {
            $totalItemsFiltered = $items->count(); // tampil sesuai filter yang berlaku
        } else {
            $totalItemsFiltered = $totalItemsAccurate;
        }
        
        $items = $items->take($perPage)->values();

        if (!$usePriceFilter) {
            $items = $items->map(function ($it) use ($token, $session, $priceMode) {
                $priceCategory = $priceMode === 'reseller' ? 'RESELLER' : 'USER';
                $it['price'] = $this->getPriceGlobal($it['id'], $token, $session, $priceCategory);
                return $it;
            });
        }

        $items = $items->map(function ($item) {
            $item['fileName'] = collect($item['detailItemImage'] ?? [])
                ->pluck('fileName')->filter()->values()->toArray();
            return $item;
        });

        return [
            'items' => $items,
            'page'  => $page,
            'pageCount' => $hasMore ? $page + 1 : $page,
            'session' => $session,
            'filters' => compact('search','categoryId','stokAda','minPrice','maxPrice','priceMode'),
            // 💡 ganti bagian ini
            'totalItems' => ($stokAda == '1')
                ? $totalStokReadyEstimate   // stok ready (estimasi)
                : $totalItemsAccurate,      // total semua data (default)

            'estimasi' => $totalStokReadyEstimate,
        ];
    }

    public function index(Request $request)
    {
        $data = $this->fetchItemsForList($request);

        $categories = Cache::remember("accurate:categories:global", 1800, function () use ($data) {
            $acc = AccurateGlobal::token();
            $token = $acc['access_token'];
            $session = $acc['session_id'];
            $baseUrl = rtrim(config('services.accurate.base_api'), '/');
            $cats = collect();
            $page = 1;
            do {
                $resp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Session-ID'  => $session,
                ])->get("{$baseUrl}/item-category/list.do", [
                    'sp.page' => $page,
                    'sp.pageSize' => 100,
                    'fields' => 'id,name,parent',
                ]);
                if (!$resp->successful()) break;
                $json = $resp->json();
                $cats = $cats->merge($json['d'] ?? []);
                $page++;
            } while (($json['sp']['pageCount'] ?? 1) >= $page);
            return $cats->values();
        });

        $viewData = array_merge($data, ['categories' => $categories, 'totalItems' => $data['totalItems'] ?? 0,]);

        if ($request->ajax()) {
            return response()->view('items.index3', $viewData);
        }

        return view('items.index3', $viewData);
    }


    public function exportPdf1(Request $request)
    {
        $data = $this->fetchItemsForList($request);

        $pdf = Pdf::loadView('items.pdf', [
            'items'   => $data['items'],
            'filters' => $data['filters'],
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Daftar Produk.pdf');
    }

    private function getPriceGlobal($itemId, $token, $session, $priceCategory = 'USER')
    {
        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
            'id' => $itemId,
            'priceCategoryName' => $priceCategory,
        ]);

        $json = $resp->json();
        $data = $json['d'] ?? [];

        // 🔹 Ini yang penting — ambil dari field yang benar
        return $data['unitPrice']
            ?? ($data['unitPriceRule'][0]['price'] ?? 0);
    }
}