<?php

use Illuminate\Http\Request;
use App\Models\AccurateToken;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\TesterController;
use App\Http\Controllers\AccurateController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\UserHeadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\AccurateAuthController;
use App\Http\Controllers\AccurateSyncController;
use App\Http\Controllers\AccurateTokenController;
use App\Http\Controllers\AuthinticationController;
use App\Http\Controllers\AccurateAccountController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


//Authintication
Route::get('/', [AuthinticationController::class, 'index'])->middleware('guest')->name('auth.login');
Route::post('/', [AuthinticationController::class, 'login'])->name('auth.login.post');
Route::post('/logout', [AuthinticationController::class, 'logout'])->name('logout');

Route::get('/categories/search', [ItemController::class, 'searchCategories'])->name('categories.search');

// routes/web.php
Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
Route::get('/item/{id}/price', [KaryawanController::class, 'getPrice'])->name('item.price');





//Reset Password
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

//item
Route::middleware(['auth'])->group(function () {
    Route::get('/item', [ItemController::class, 'index'])->name('items.index');
    Route::get('/item-detail/{encrypted}', [ItemController::class, 'getItemDetails'])->name('items.detail');
    Route::get('/item-detail/{encrypted}/export-pdf', [ItemController::class, 'exportPdf'])->name('items.export-pdf');
    Route::post('/item-detail/{encrypted}/export-pdf', [ItemController::class, 'exportPdf'])->name('items.export-pdf.post');
});

//Admin

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin-dashboard', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin-user', [AdminController::class, 'viewUser'])->name('admin.user');
    Route::get('/admin-log', [AdminController::class, 'logActivity'])->name('admin.log');    
    Route::get('/admin/log/user-search', [AdminController::class, 'searchUser'])->name('admin.log.user-search');
    Route::post('/auto-logout', [AdminController::class, 'autoLogout'])->name('auto.logout');

    
});

// web.php
Route::get('/items/export-pdf', [ItemController::class, 'exportPdf1'])->name('items.export.pdf');



Route::post('/sync-accurate-users', function () {
    if (auth()->user()->status !== 'admin') {
        abort(403);
    }

    Artisan::call('sync:accurate-users');
    return redirect()->back()->with('success', 'Sinkronisasi Accurate berhasil dijalankan!');
})->name('sync.accurate')->middleware('auth');


Route::get('/uji', function () {
    return view('uji-coba');
});

Route::get('/image/{filename}', [ItemController::class, 'getAccurateImage'])->where('filename', '.*');
Route::get('/accurate-image/{filename}', [ItemController::class, 'getAccurateImage'])
    ->where('filename', '.*') // ini penting agar path panjang bisa dibaca
    ->name('accurate.image');
Route::get('/proxy-image', [ItemController::class, 'getImageFromApi'])->name('proxy.image');
Route::post('/sync/employees', [AccurateSyncController::class, 'syncEmployees'])->name('sync.employees');
Route::post('/sync/customers', [AccurateSyncController::class, 'syncCustomers'])->name('sync.customers');

Route::get('/selling-price', [ItemController::class, 'getSellingPrice']);

Route::post('/items/adjusted-price-ajax', [ItemController::class, 'getAdjustedPriceAjax'])->name('items.adjusted-price-ajax');
Route::post('/items/salesorder-stock-ajax', [ItemController::class, 'getSalesOrderStockAjax'])->name('items.salesorder-stock-ajax');
Route::get('/items/search-items-ajax', [ItemController::class, 'searchItemsAjax'])->name('items.search-items-ajax');
Route::post('/items/matching-invoices-ajax', [ItemController::class, 'getMatchingInvoicesAjax'])->name('items.matching-invoices-ajax');
Route::post('/items/adjusted-price-reseller-ajax', [ItemController::class, 'getAdjustedPriceResellerAjax'])->name('items.adjusted-price-reseller-ajax');


Route::middleware(['auth','can:manage-users'])->group(function () {

    Route::get('/admin/accurate/{account}/connect', [AccurateAuthController::class,'connect'])
        ->name('admin.accurate.connect');
    Route::get('/admin/accurate/callback', [AccurateAuthController::class,'callback'])
    ->name('admin.accurate.callback');
});


Route::get('/users/create', [AdminController::class, 'create'])->name('users.create');
Route::post('/users', [AdminController::class, 'store'])->name('users.post');
Route::get('/users/{id}', [AdminController::class, 'show'])->name('users.show');

Route::get('/reseller', [ResellerController::class, 'index2'])->name('reseller.index');
Route::get('/reseller/test', [ResellerController::class, 'index'])->name('reseller.test');
Route::get('/reseller/{encrypted}/detail', [ResellerController::class, 'getItemDetails'])->name('reseller.detail');

Route::get('/admin/users', [UserController::class, 'index'])->name('users2.index');
Route::get('/admin/users/create', [UserController::class, 'create'])->name('users2.create');
Route::post('/admin/users', [UserController::class, 'store'])->name('users2.store');
Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('users2.edit');
Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('users2.update');
Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('users2.destroy');

// (Sudah ada) Accurate Accounts & mapping — biarkan seperti sebelumnya
Route::get('/admin/accurate-accounts', [AccurateAccountController::class, 'index'])->name('aa.index');
Route::get('/admin/accurate-accounts/create', [AccurateAccountController::class, 'create'])->name('aa.create');
Route::post('/admin/accurate-accounts', [AccurateAccountController::class, 'store'])->name('aa.store');
Route::get('/admin/accurate-accounts/{id}/edit', [AccurateAccountController::class, 'edit'])->name('aa.edit');
Route::put('/admin/accurate-accounts/{id}', [AccurateAccountController::class, 'update'])->name('aa.update');
Route::delete('/admin/accurate-accounts/{id}', [AccurateAccountController::class, 'destroy'])->name('aa.destroy');

//Karyawan
Route::get('/detail/{encrypted}', [KaryawanController::class, 'show'])->name('karyawan.show');




