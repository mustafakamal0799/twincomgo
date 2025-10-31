<?php

// app/Http/Controllers/BranchController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\AccurateGlobal;
use Illuminate\Support\Facades\Http;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('page', 1);

        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get('https://public.accurate.id/accurate/api/branch/list.do', [
            'sp.page' => $page,
            'sp.pageSize' => 100,
            'fields' => 'id,name',
        ]);

        $json = $response->json();

        return response()->json([
            'data' => $json['d'] ?? [],
            'totalPage' => $json['totalPage'] ?? 1,
            'page' => $page,
        ]);
    }
}

