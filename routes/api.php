<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccurateController;
use App\Http\Controllers\ApiLoginController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/accurate/customers', [AccurateController::class, 'getCustomers']);
Route::get('/accurate/items', [AccurateController::class, 'getItems']);

Route::middleware('auth:sanctum')->get('/accurate/items/detail/{id}', [AccurateController::class, 'getItemDetailsApi']);


Route::post('/login', [ApiLoginController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiLoginController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
// Route::get('/accurate/items/detail/{id}', [AccurateController::class, 'detailItems']);          
