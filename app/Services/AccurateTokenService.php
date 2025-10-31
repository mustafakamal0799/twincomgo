<?php

namespace App\Services;

use App\Models\AccurateAccount;
use Illuminate\Support\Facades\Http;

class AccurateTokenService
{
    /**
     * Ambil token valid untuk head tertentu
     */
    public static function getValidAccessToken(int $accountId): ?string
    {
        $acc = AccurateAccount::find($accountId);
        if (!$acc || !$acc->active) return null;

        // Jika token hampir habis (<=60 detik), refresh
        if (!$acc->expires_at || $acc->expires_at->lte(now()->addSeconds(60))) {
            if (!self::refresh($acc)) return null;
            $acc->refresh();
        }

        return $acc->access_token ?: null;
    }

    /**
     * Refresh token Accurate
     */
    public static function refresh(AccurateAccount $acc): bool
    {
        if (!$acc->refresh_token) return false;

        $resp = Http::asForm()
            ->withBasicAuth(env('ACCURATE_CLIENT_ID'), env('ACCURATE_CLIENT_SECRET'))
            ->post('https://account.accurate.id/oauth/token', [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $acc->refresh_token,
            ]);

        if (!$resp->successful()) return false;

        $data = $resp->json();

        $acc->access_token  = $data['access_token'] ?? $acc->access_token;
        if (!empty($data['refresh_token'])) {
            $acc->refresh_token = $data['refresh_token'];
        }
        $acc->expires_at = now()->addSeconds($data['expires_in'] ?? 3600);
        $acc->save();

        return true;
    }
}
