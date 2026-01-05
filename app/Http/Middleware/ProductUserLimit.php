<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ProductUserLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // kalau belum lewat wait-page, wajib redirect dulu
        if (!$request->session()->has('wait_passed')) {
            return redirect()->route('queue.number');
        }

        return $next($request);
    }
}
