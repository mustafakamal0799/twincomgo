<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if ($user->status === 'admin') {
                    return redirect()->route('admin.index');
                } elseif ($user->status === 'RESELLER') {
                    return redirect()->route('items.index');
                } elseif ($user->status === 'KARYAWAN') {
                    return redirect()->route('items.index');
                }

                return redirect('/');
            }
        }

        return $next($request);
    }
}
