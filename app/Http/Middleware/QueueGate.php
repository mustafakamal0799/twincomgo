<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class QueueGate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maxUsers = 5;
        $ttl = 10;

        $key = 'active_users';

        $current = Cache::get($key, 0);

        if ($current >= $maxUsers) {
            return response()->view('wait', [
                'position' => $current,
                'limit'    => $maxUsers,
            ], 429);
        }

        // tambahkan 1 user ke active
        Cache::put($key, $current + 1, $ttl);

        $response = $next($request);

        // setelah selesai request, kurangi
        Cache::decrement($key);

        return $response;
    }
}
