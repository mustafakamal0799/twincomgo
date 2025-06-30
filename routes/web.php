<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\TesterController;
use App\Http\Controllers\AccurateController;
use App\Http\Controllers\AccurateSyncController;
use App\Http\Controllers\AuthinticationController;

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
Route::post('/login-post', [AuthinticationController::class, 'login'])->name('auth.login-post');
Route::post('/logout', [AuthinticationController::class, 'logout'])->name('logout');


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


Route::get('/test', [TesterController::class, 'test']);

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

// Route::get('/item-test', [TesterController::class, 'index'])->name('item-test');


 
