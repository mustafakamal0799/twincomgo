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
            }elseif($user->status === 'RESELLER') {
                activity()
                ->causedBy(Auth::user())
                ->inLog(auth()->user()->name)
                ->log('Reseller dengan email ' . auth()->user()->email . ' sedang melakukan login');
                return redirect()->route('items.index');
            }elseif($user->status === 'KARYAWAN') {
                activity()
                ->causedBy(Auth::user())
                ->inLog(auth()->user()->name)
                ->log('Karyawan dengan email ' . auth()->user()->email . ' sedang melakukan login');
                return redirect()->route('items.index');
            }else {
                return redirect()->route('auth.login');
            }            
        }
        return back()->with('loginError', 'Email atau password salah!');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        // Update logout_time in activity log
        $loginActivity = \Spatie\Activitylog\Models\Activity::where('log_name', $user->name)
            ->where('description', 'like', '%sedang melakukan login%')
            ->whereNull('logout_time')
            ->latest('created_at')
            ->first();

        if ($loginActivity) {
            $loginActivity->logout_time = now();
            $loginActivity->save();
        }
        
        Auth::logout(); // Logout user

        $request->session()->invalidate(); // Invalidate session
        $request->session()->regenerateToken(); // Regenerate CSRF token

        return redirect('/')->with('status', 'Anda telah logout.');
    }
}
