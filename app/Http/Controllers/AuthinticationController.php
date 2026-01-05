<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthinticationController extends Controller
{
    public function index() {
        return view('auth.login');
    }

    public function login(Request $request) 
    {
        $credentials = $request->validate([
            'email' => ['required'],
            'password' => ['required']
        ]);

        if(Auth::attempt($credentials)) {
            // RESET antrian sebelum assign queue
            $request->session()->forget('wait_passed');
            $request->session()->forget('queue_number');
            
            // regenerate session baru setelah login sukses
            $request->session()->regenerate();


            $user = Auth::user();

            if ($user->status === 'admin') {
                activity()
                    ->causedBy($user)
                    ->inLog($user->name)
                    ->log('Admin dengan email ' . $user->email . ' sedang melakukan login');
                return redirect()->route('admin.index');

            } elseif ($user->status === 'RESELLER') {
                activity()
                    ->causedBy($user)
                    ->inLog($user->name)
                    ->log('Reseller dengan email ' . $user->email . ' sedang melakukan login');
                return redirect()->route('queue.number');

            } elseif ($user->status === 'KARYAWAN') {
                activity()
                    ->causedBy($user)
                    ->inLog($user->name)
                    ->log('Karyawan dengan email ' . $user->email . ' sedang melakukan login');
                return redirect()->route('queue.number');
            }

            return redirect()->route('auth.login');
        }

        return back()->with('loginError', 'Email atau password salah!');
    }


    public function logout(Request $request)
    {
        $user = Auth::user();

        // hapus session antrian
        $request->session()->forget('wait_passed');
        $request->session()->forget('queue_number');

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
        $request->session()->regenerate();

        return redirect('/')->with('logoutSuccess', 'Anda telah logout.');
    }
}
