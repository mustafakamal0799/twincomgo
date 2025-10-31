<?php

namespace App\Services\Accurate;

use App\Models\AccurateAccount;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class TokenResolver
{
    public function getValidAccessToken(int $accountId): string
    {
        $cacheKey = "aa:token:{$accountId}";
        if ($token = Cache::get($cacheKey)) return $token;

        $acc = AccurateAccount::findOrFail($accountId);
        $access = Crypt::decryptString($acc->access_token_enc);

        if ($acc->expires_at && $acc->expires_at->isFuture()) {
            $ttl = now()->diffInSeconds($acc->expires_at);
            if ($ttl > 0) Cache::put($cacheKey, $access, $ttl);
            return $access;
        }

        // refresh dengan lock agar tidak balapan
        $lock = Cache::lock("aa:refresh:{$accountId}", 10);
        if (! $lock->get()) {
            usleep(300 * 1000);
            return Cache::get($cacheKey) ?: Crypt::decryptString($acc->fresh()->access_token_enc);
        }

        try {
            $refresh = $acc->refresh_token_enc ? Crypt::decryptString($acc->refresh_token_enc) : null;
            if (! $refresh) {
                $acc->update(['status' => 'expired']);
                throw new Exception("Missing refresh token for account {$accountId}");
            }

            $resp = Http::asForm()
                ->withBasicAuth(config('services.accurate.client_id'), config('services.accurate.client_secret'))
                ->post(config('services.accurate.base_auth').'/oauth/token', [
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $refresh,
                ]);

            if (! $resp->successful()) {
                Log::warning('Accurate refresh failed', [
                    'account_id' => $accountId,
                    'status'     => $resp->status(),
                    'body'       => $resp->body()
                ]);
                $acc->update(['status' => 'expired']);
                throw new Exception("Refresh failed for account {$accountId}");
            }

            $data = $resp->json();
            $newAccess = $data['access_token'] ?? null;
            $newRefresh= $data['refresh_token'] ?? null;
            $expiresIn = (int)($data['expires_in'] ?? 0);

            $acc->update([
                'access_token_enc'  => Crypt::encryptString($newAccess),
                'refresh_token_enc' => $newRefresh ? Crypt::encryptString($newRefresh) : $acc->refresh_token_enc,
                'expires_at'        => $expiresIn ? Carbon::now()->addSeconds($expiresIn) : null,
                'status'            => 'active',
            ]);

            $ttl = $expiresIn > 0 ? $expiresIn : 300;
            Cache::put($cacheKey, $newAccess, $ttl);
            return $newAccess;

        } finally {
            optional($lock)->release();
        }
    }
}
