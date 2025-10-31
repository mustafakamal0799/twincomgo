<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\AccurateAccount;

class AccurateAuthController extends Controller
{

    public function connect(AccurateAccount $account)
    {
        $redirect = route('admin.accurate.callback'); // fix, tanpa /{id}

        // bawa account_id di state
        $state = base64_encode(json_encode([
            'csrf'       => csrf_token(),
            'account_id' => $account->id,
        ]));

        $params = [
            'response_type' => 'code',
            'client_id'     => env('ACCURATE_CLIENT_ID'),
            'redirect_uri'  => $redirect, // HARUS identik dg yg didaftarkan
            'scope'         => implode(' ', [
                'item_view','customer_view','sales_order_view','sales_invoice_view',
                'employee_view','warehouse_view','unit_view','sales_invoice_save'
            ]),
            'state'         => $state,
        ];

        // (opsional) debug: dd('AUTH_URL', 'https://account.accurate.id/oauth/authorize?'.http_build_query($params));
        return redirect()->away('https://account.accurate.id/oauth/authorize?'.http_build_query($params));
    }

    public function callback(Request $request)
    {
        $code  = $request->query('code');
        $state = json_decode(base64_decode((string)$request->query('state')), true);

        if (!$code || !is_array($state) || empty($state['account_id'])) {
            return redirect()->route('admin.users.index')->with('error', 'Callback invalid (code/state).');
        }

        $redirect = route('admin.accurate.callback'); // sama persis seperti di connect()

        $resp = Http::asForm()
            ->withBasicAuth(env('ACCURATE_CLIENT_ID'), env('ACCURATE_CLIENT_SECRET'))
            ->post('https://account.accurate.id/oauth/token', [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => $redirect, // identik
            ]);

        if (!$resp->successful()) {
            return redirect()->route('admin.users.index')->with('error', 'Gagal tukar token: '.$resp->body());
        }

        $data    = $resp->json();
        $account = AccurateAccount::find($state['account_id']);
        if (!$account) {
            return redirect()->route('admin.users.index')->with('error', 'Account tidak ditemukan.');
        }

        $account->update([
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $account->refresh_token,
            'expires_at'    => now()->addSeconds($data['expires_in'] ?? 3600),
        ]);

        return redirect()->route('admin.users.index')->with('ok', "Token tersimpan untuk {$account->name}");
    }
}
