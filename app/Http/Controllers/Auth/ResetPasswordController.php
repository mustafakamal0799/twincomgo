<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Email tidak ditemukan.');
        }

        $otp = rand(100000, 999999);
        
        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'otp' => $otp,
            'expired_at' => Carbon::now()->addMinutes(5),
            'created_at' => Carbon::now(),
        ]);

        Mail::to($request->email)->send(new OtpMail($otp));

        return redirect('/verify-otp?email=' . $request->email);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak ditemukan.'
            ], 404);
        }

        // Hapus OTP lama
        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        $otp = rand(100000, 999999);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'otp' => $otp,
            'expired_at' => Carbon::now()->addMinutes(5),
            'created_at' => Carbon::now(),
        ]);

        Mail::to($request->email)->send(new OtpMail($otp));

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP baru telah dikirim.'
        ]);
    }

    public function showVerifyForm(Request $request)
    {
        return view('auth.verify-otp', ['email' => $request->email]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $check = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('used', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$check) {
            return back()->with('error', 'OTP salah.');
        }

        if (Carbon::now()->greaterThan($check->expired_at)) {
            return back()->with('error', 'OTP expired.');
        }

        DB::table('password_resets')->where('id', $check->id)->update([
            'used' => true
        ]);

        session()->put('validOtp', true);

        return redirect('/reset-password?email=' . $request->email);
    }

    public function showResetForm(Request $request)
    {
        if (!$request->session()->has('validOtp')) {
            return redirect('/forgot-password');
        }

        return view('auth.reset-password', ['email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        return redirect('/')->with('success', 'Password berhasil direset.');
    }
}
