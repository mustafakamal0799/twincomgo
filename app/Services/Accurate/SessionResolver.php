<?php

namespace App\Services\Accurate;

use App\Models\AccurateAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class SessionResolver
{
    public function __construct(protected TokenResolver $tokens) {}

    public function ensureSessionId(int $accountId): string
    {
        $cacheKey = "aa:session:{$accountId}";
        if ($sid = Cache::get($cacheKey)) return $sid;

        $acc = AccurateAccount::findOrFail($accountId);
        if (! $acc->company_db_id) {
            throw new Exception("company_db_id belum di-set untuk account {$accountId}");
        }

        if ($acc->session_id) {
            Cache::put($cacheKey, $acc->session_id, 3600);
            return $acc->session_id;
        }

        return $this->openDb($acc, $cacheKey);
    }

    public function reopenSession(int $accountId): string
    {
        $acc = AccurateAccount::findOrFail($accountId);
        return $this->openDb($acc, "aa:session:{$accountId}");
    }

    protected function openDb(AccurateAccount $acc, string $cacheKey): string
    {
        $lock = Cache::lock("aa:open-db:{$acc->id}", 10);
        if (! $lock->get()) {
            usleep(300 * 1000);
            return Cache::get($cacheKey) ?: (string)($acc->fresh()->session_id ?? '');
        }

        try {
            $token = $this->tokens->getValidAccessToken($acc->id);

            $resp = Http::asForm()
                ->withHeaders(['Authorization' => 'Bearer '.$token])
                ->post(config('services.accurate.base_api').'/open-db.do', [
                    'id' => $acc->company_db_id,
                ]);

            if (! $resp->successful()) {
                Log::error('open-db failed', [
                    'account_id' => $acc->id,
                    'status'     => $resp->status(),
                    'body'       => $resp->body()
                ]);
                throw new Exception('Gagal open-db Accurate');
            }

            $json = $resp->json();
            $sessionId = $json['session'] ?? $json['sessionId'] ?? null;
            if (! $sessionId) throw new Exception('Response open-db tidak mengandung session id');

            $acc->update(['session_id' => $sessionId]);
            Cache::put($cacheKey, $sessionId, 3600);
            return $sessionId;

        } finally {
            optional($lock)->release();
        }
    }
}
