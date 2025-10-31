<?php

// app/Helpers/AccurateGlobal.php
namespace App\Helpers;

use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class AccurateGlobal
{
    public static function token()
    {
        $account = DB::table('accurate_accounts')
            ->where('status', 'active')
            ->orderByDesc('expires_at')
            ->first();

        if (!$account) {
            throw new \Exception('Tidak ada token Accurate aktif di database.');
        }

        // kalau token terenkripsi, decrypt
        try {
            $token = Crypt::decryptString($account->access_token_enc);
        } catch (Throwable $e) {
            $token = $account->access_token_enc;
        }

        return [
            'access_token' => $token,
            'session_id'   => $account->session_id,
            'db_id'        => $account->company_db_id,
        ];
    }
}
