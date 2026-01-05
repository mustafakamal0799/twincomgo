<?php

namespace App\Http\Controllers;

use App\Helpers\AccurateGlobal;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $acc     = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $page = $request->query('page', 1);
        $search = $request->query('search', "");
        $categoryId = $request->query('categoryId');

        // PARAMETER
        $query = [
            "fields" => "id,name,availableToSell",
            'filter.suspended' => false,
            'filter.keywords.op' => 'CONTAIN',
            'filter.keywords.val[0]' => $search,
            'filter.itemCategoryId.op' => 'EQUAL',
            'filter.itemCategoryId.val[0]' => $categoryId,
            'sp.page' => $page,
            'sp.pageSize' => 100,
        ];        

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("$baseUrl/item/list.do", $query);

        $json = $resp->json();

        return response()->json($json['d']);        
    }

    public function getCategory() {
        $acc     = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $cats = collect();
        $page = 1;

        do {
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->timeout(10)->get("$baseUrl/item-category/list.do", [
                'sp.page'     => $page,
                'sp.pageSize' => 100,
                'fields'      => 'id,name,parent',
            ]);

            if (!$resp->successful()) break;

            $json = $resp->json();
            $cats = $cats->merge($json['d'] ?? []);

            $page++;
            $pageCount = $json['sp']['pageCount'] ?? 1;

        } while ($page <= $pageCount);

        return response()->json([
            'category' => $cats->values()
        ]);
    }
}
