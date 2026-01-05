<?php

namespace App\Http\Controllers;

use App\Helpers\AccurateGlobal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ListController extends Controller
{
    public function getList(Request $request)
    {
        $acc = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $code = $request->code;

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer' . $token,
            'X-Session-ID' => $session,
        ])->get("$baseUrl/item/detail.do", [
            'id' => $code,
        ]);

        if (!$resp->successful()) {
            return response()->json(['message', 'Data gagal diambil']);
        }

        $data = $resp->json()['d'];

        return response()->json([
            'no'  => $data['no'],
            'name'  => $data['name'],
            'stock' => $data['availableToSell'],
        ]);
    }
}
