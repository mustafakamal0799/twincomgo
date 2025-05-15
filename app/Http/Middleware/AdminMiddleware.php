<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->status == 'admin') {
            return $next($request); // Lanjutkan ke request berikutnya jika user adalah admin
        }

        // Jika bukan admin, redirect ke halaman home atau halaman lain
        session()->flash('loginGagal', 'Bukan Admin');
        return redirect('/');
    }
}
