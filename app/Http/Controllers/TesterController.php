<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TesterController extends Controller
{

    public function index (Request $request) {

        if (!$request->exists('stok_ada')) {
            return redirect()->route('item-test', array_merge($request->all(), ['stok_ada' => 1]));
        }
        
        $page = $request->input('page', 1);
        $categoryId = $request->input('category_id');

        $stokAda = $request->input('stok_ada');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        

        $params = [
            'sp.page' => $page,
            'sp.pageSize' => 100,
            'fields' => 'id,name,no,availableToSell,branchPrice',
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
                'fields' => 'id,name',
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

        if ($request->ajax()) {
            return view('partials.item-list', compact('items', 'allCategories'))->render();
        }

        return view('tester.index', compact('items', 'allCategories'));
    }
}
