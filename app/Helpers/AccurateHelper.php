<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AccurateHelper
{
    public static function getToken()
    {
        if ($token = Cache::get('accurate_access_token')) {
            return $token;
        }

        if ($refreshToken = Cache::get('accurate_refresh_token')) {
            $response = Http::asForm()
                ->withBasicAuth(env('ACCURATE_CLIENT_ID'), env('ACCURATE_CLIENT_SECRET'))
                ->post('https://account.accurate.id/oauth/token', [
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Cache::put('accurate_access_token', $data['access_token'], $data['expires_in']); // detik
                Cache::put('accurate_refresh_token', $data['refresh_token'], now()->addDays(14));

                return $data['access_token'];
            }

            // refresh gagal → bersihkan supaya error naik
            Cache::forget('accurate_access_token');
            Cache::forget('accurate_refresh_token');
        }

        throw new \Exception("Tidak ada token/refresh token. Silakan login ulang Accurate.");
    }

    public static function getSession()
    {
        if ($session = Cache::get('accurate_session_id')) return $session;

        $dbId = env('ACCURATE_DB_ID');
        if (!$dbId) throw new \Exception('ACCURATE_DB_ID belum di-set');

        $token = self::getToken();

        $res = Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept'        => 'application/json',
        ])->get('https://account.accurate.id/api/open-db.do', ['id' => $dbId]);

        if (!$res->successful()) {
            // jika 401/403, bersihin token biar login ulang nanti
            if (in_array($res->status(), [401,403])) {
                Cache::forget('accurate_access_token');
                Cache::forget('accurate_refresh_token');
            }
            throw new \Exception('Gagal open-db: '.$res->status().' - '.$res->body());
        }

        $data    = $res->json();
        $session = $data['session'] ?? null;
        $host    = $data['host'] ?? null;

        if (!$session || !$host) {
            throw new \Exception('Response open-db tidak berisi session/host');
        }

        // simpan 6 jam (boleh disesuaikan)
        Cache::put('accurate_session_id', $session, now()->addHours(6));
        Cache::put('accurate_host', $host, now()->addHours(6));

        return $session;
    }

    public static function getHost()
    {
        if (!Cache::has('accurate_host')) self::getSession();
        $host = Cache::get('accurate_host');
        if (!$host) throw new \Exception('Host Accurate belum tersedia');
        return rtrim($host, '/');
    }
}