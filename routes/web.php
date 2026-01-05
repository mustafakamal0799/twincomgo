<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthinticationController;
use App\Http\Controllers\AccurateAccountController;
use App\Http\Controllers\Auth\ResetPasswordController;



// =================================================================================================================================
//                                                       ROUTE LOGIN 
// =================================================================================================================================
    Route::get('/',         [AuthinticationController::class, 'index'])->middleware('guest')->name('auth.login');
    Route::post('/login',   [AuthinticationController::class, 'login'])->name('auth.login.post');
    Route::post('/logout',  [AuthinticationController::class, 'logout'])->name('logout');
// =================================================================================================================================


// =================================================================================================================================
//                                                    ROUTE RESET PASSWORD
// =================================================================================================================================
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => 'required|email']); 
        $status = Password::sendResetLink(
            $request->only('email')
        );
        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    })->name('password.email');
    Route::get('/reset-password/{token}', function (string $token) {
            $email = request('email');
            return view('auth.reset-password', [
                'token' => $token,
                'email' => $email,
        ]);
    })->name('password.reset');
    Route::post('/reset-password', [ResetController::class, 'reset'])->name('password.update');
// =================================================================================================================================



// =================================================================================================================================
//                                                      ROUTE KARYAWAN
// =================================================================================================================================
    Route::middleware(['auth', 'karyawan', 'product.limit'])->group(function () {
        Route::get('/item',             [ItemController::class, 'index'])->name('items.index');
        Route::get('/item/{encrypted}', [KaryawanController::class, 'show'])->name('karyawan.show');
    });

    Route::get('/proxy/image',                      [KaryawanController::class, 'proxyImage'])->name('proxy.image');
    Route::get('/karyawan/{encrypted}/export-pdf',  [KaryawanController::class, 'exportPdf'])->name('karyawan.exportPdf');
    Route::get('/karyawan/{id}/price',              [KaryawanController::class, 'getPrice']);
    Route::get('/branches',                         [KaryawanController::class, 'getBranches']);
    Route::get('/items/export-pdf',                 [ItemController::class, 'exportPdf1'])->name('items.exportPdf');

    // AJAX-KARYAWAN
    Route::get('/ajax/warehouse-stock',             [KaryawanController::class, 'getWarehouseStock'])->name('ajax.warehouse.stock');
    Route::get('/ajax/item-image',                  [KaryawanController::class, 'getItemImage'])->name('ajax.item.image');
    Route::get('/ajax/price',                       [ItemController::class, 'ajaxPrice'])->name('ajax.price');
// =================================================================================================================================


// =================================================================================================================================
//                                                        ROUTE ADMIN
// =================================================================================================================================
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/admin-dashboard',       [AdminController::class, 'index'])->name('admin.index');
        Route::get('/admin-user',            [AdminController::class, 'viewUser'])->name('admin.user');
        Route::get('/admin-log',             [AdminController::class, 'logActivity'])->name('admin.log');    
        Route::get('/admin/log/user-search', [AdminController::class, 'searchUser'])->name('admin.log.user-search');
        Route::post('/auto-logout',          [AdminController::class, 'autoLogout'])->name('auto.logout');
        
        Route::get('/admin/users',           [UserController::class, 'index'])->name('users2.index');
        Route::get('/admin/users/create',    [UserController::class, 'create'])->name('users2.create');
        Route::post('/admin/users',          [UserController::class, 'store'])->name('users2.store');
        Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('users2.edit');
        Route::put('/admin/users/{id}',      [UserController::class, 'update'])->name('users2.update');
        Route::delete('/admin/users/{id}',   [UserController::class, 'destroy'])->name('users2.destroy');
        
        // (Sudah ada) Accurate Accounts & mapping — biarkan seperti sebelumnya
        Route::get('/admin/accurate-accounts',              [AccurateAccountController::class, 'index'])->name('aa.index');
        Route::get('/admin/accurate-accounts/create',       [AccurateAccountController::class, 'create'])->name('aa.create');
        Route::post('/admin/accurate-accounts',             [AccurateAccountController::class, 'store'])->name('aa.store');
        Route::get('/admin/accurate-accounts/{id}/edit',    [AccurateAccountController::class, 'edit'])->name('aa.edit');
        Route::put('/admin/accurate-accounts/{id}',         [AccurateAccountController::class, 'update'])->name('aa.update');
        Route::delete('/admin/accurate-accounts/{id}',      [AccurateAccountController::class, 'destroy'])->name('aa.destroy');    
        
        Route::get('/users/create', [AdminController::class, 'create'])->name('users.create');
        Route::post('/users',       [AdminController::class, 'store'])->name('users.post');
        Route::get('/users/{id}',   [AdminController::class, 'show'])->name('users.show');
    });
// =================================================================================================================================


// =================================================================================================================================
//                                                       ROUTE RESELLER
// =================================================================================================================================
    Route::get('/reseller',             [ResellerController::class, 'index2'])->name('reseller.index');
    Route::get('/reseller/test',        [ResellerController::class, 'index'])->name('reseller.test');
    Route::get('/reseller/{encrypted}', [ResellerController::class, 'show'])->name('reseller.detail');

    // AJAX-RESELLER
    Route::get('/ajax/priceReseller',   [ResellerController::class, 'ajaxPriceReseller'])->name('ajax.price.reseller');
// =================================================================================================================================



// =================================================================================================================================
//                                                        SISTEM ANTRIAN
// =================================================================================================================================
    Route::get('/queue-number', function () {
        
        // Ambil nomor terakhir + 1
        $queue = Cache::get('login_queue', 0) + 1;
        
        // Simpan kembali, reset tiap 60 detik
        Cache::put('login_queue', $queue, 60);
        
        // Simpan di session user
        session(['queue_number' => $queue]);
        
        return redirect()->route('wait.page');
    })->name('queue.number');

    Route::get('/wait', function () {
        return view('wait-page');
    })->name('wait.page');

    // setelah selesai wait
    Route::get('/wait/continue', function () {
        session(['wait_passed' => true]);
        if (Auth::user()->status === 'KARYAWAN') {
            return redirect()->intended('/item');
        }
        if (Auth::user()->status === 'RESELLER') {
            return redirect()->intended('/reseller');
        }
    })->name('wait.continue');
// =================================================================================================================================



Route::get('/forgot-password', [ResetPasswordController::class, 'showForgotForm'])->name('showForgotForm');
Route::post('/forgot-password', [ResetPasswordController::class, 'sendOtp']);

Route::post('/resend-otp', [ResetPasswordController::class, 'resendOtp']);
Route::get('/verify-otp', [ResetPasswordController::class, 'showVerifyForm']);
Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOtp']);

Route::get('/reset-password', [ResetPasswordController::class, 'showResetForm']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);

