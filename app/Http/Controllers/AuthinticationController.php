<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthinticationController extends Controller
{
    public function index() {
        return view('auth.login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => ['required'],
            'password' => ['required']
        ]);

        if(Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->status === 'admin') {
                activity()
                ->causedBy(Auth::user())
                ->inLog(auth()->user()->name)
                ->log('Admin dengan email ' . auth()->user()->email . ' sedang melakukan login');
                return redirect()->route('admin.index');
            }elseif($user->status === 'reseller') {
                activity()
                ->causedBy(Auth::user())
                ->inLog(auth()->user()->name)
                ->log('Reseller dengan email ' . auth()->user()->email . ' sedang melakukan login');
                return redirect()->route('items.index');
            }elseif($user->status === 'karyawan') {
                activity()
                ->causedBy(Auth::user())
                ->inLog(auth()->user()->name)
                ->log('Reseller dengan email ' . auth()->user()->email . ' sedang melakukan login');
                return redirect()->route('items.index');
            }else {
                return redirect()->route('auth.login');
            }            
        }
        return back()->with('loginError', 'Email atau password salah!');
    }

    public function logout(Request $request)
    {
        Auth::logout(); // Logout user

        $request->session()->invalidate(); // Invalidate session
        $request->session()->regenerateToken(); // Regenerate CSRF token

        return redirect('/')->with('status', 'Anda telah logout.');
    }
}
