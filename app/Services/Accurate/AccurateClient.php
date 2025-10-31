<?php

namespace App\Services\Accurate;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AccurateClient
{
    public function __construct(
        protected TokenResolver $tokens,
        protected SessionResolver $sessions,
    ) {}

    public function request(
        int $accountId,
        string $method,
        string $path,       // ex: '/item/list.do'
        array $query = [],
        array $headers = [],
        int $collapseWindowMs = 300
    ) {
        $endpoint = rtrim(config('services.accurate.base_api'), '/').'/'.ltrim($path, '/');

        // Single-flight: gabungkan request identik dalam window kecil
        $qHash = md5(json_encode($query));
        $sfKey = "sf:aa:{$accountId}:{$path}:{$qHash}";
        $sfPayloadKey = "{$sfKey}:payload";

        $locked = Cache::add("{$sfKey}:lock", 1, $collapseWindowMs / 1000.0);
        if (! $locked) {
            usleep($collapseWindowMs * 1000);
            if ($payload = Cache::get($sfPayloadKey)) return $payload;
        }

        // Token + Session
        $token = $this->tokens->getValidAccessToken($accountId);
        $sid   = $this->sessions->ensureSessionId($accountId);

        // Retry + auto re-open session jika invalid
        $attempts = 0; $max = 4; $backoff = 1.5;
        $resp = null; $reopened = false;

        while (true) {
            $attempts++;
            try {
                $http = Http::timeout(15)->withHeaders(array_merge([
                    'Authorization' => 'Bearer '.$token,
                    'X-Session-ID'  => $sid,
                ], $headers));

                $resp = match (strtoupper($method)) {
                    'GET'    => $http->get($endpoint, $query),
                    'POST'   => $http->asForm()->post($endpoint, $query),
                    'PUT'    => $http->asForm()->put($endpoint, $query),
                    'DELETE' => $http->delete($endpoint, $query),
                    default  => $http->get($endpoint, $query),
                };

                $status = $resp->status();

                // Session invalid → open-db sekali lalu ulang
                if (in_array($status, [401, 419, 440], true) && ! $reopened) {
                    $sid = $this->sessions->reopenSession($accountId);
                    $reopened = true;
                    continue;
                }

                // Kalau 429/5xx, coba retry dengan backoff
                if ($status === 429 || $resp->serverError()) {
                    if ($attempts < $max) {
                        $sleep = $this->jitter($backoff);
                        usleep((int)($sleep * 1_000_000));
                        $backoff *= 2;
                        continue;
                    }
                }

                break;

            } catch (Throwable $e) {
                if ($attempts < $max) {
                    $sleep = $this->jitter($backoff);
                    usleep((int)($sleep * 1_000_000));
                    $backoff *= 2;
                    continue;
                }
                throw $e;
            }
        }

        $payload = [
            'status' => $resp->status(),
            'headers'=> $resp->headers(),
            'json'   => $this->safeJson($resp),
            'body'   => $resp->body(),
        ];
        Cache::put($sfPayloadKey, $payload, 2);

        return $payload;
    }

    protected function jitter(float $base): float
    {
        $r = mt_rand() / mt_getrandmax();
        return $base * (0.8 + 0.4 * $r);
    }

    protected function safeJson($resp)
    {
        try { return $resp->json(); } catch (\Throwable) { return null; }
    }
}
