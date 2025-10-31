<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class ResetController extends Controller
{
    public function reset(Request $request) {
        $request->validate([
            'token' => 'required',    
            'email' => 'required|email',    
            'password' => 'required|min:8|confirmed',    
        ],
        [
            'token.required'           => 'Token reset tidak ditemukan.',
            'email.required'           => 'Email wajib diisi.',
            'email.email'              => 'Format email tidak valid.',
            'password.required'        => 'Kata sandi wajib diisi.',
            'password.min'             => 'Kata sandi minimal :min karakter.',
            'password.confirmed'       => 'Konfirmasi kata sandi tidak cocok.',
        ]);     
    
        $status = Password::reset(
    
            $request->only('email', 'password', 'password_confirmation', 'token'),    
            function (User $user, string $password) {
    
                $user->forceFill([    
                    'password' => Hash::make($password)    
                ])->setRememberToken(Str::random(60));     
    
                $user->save();       
                event(new PasswordReset($user));    
            }    
        );
    
        return $status === Password::PASSWORD_RESET    
                    ? redirect()->route('auth.login')->with('success', __($status))    
                    : back()->withErrors(['email' => [__($status)]]);
    }
}
