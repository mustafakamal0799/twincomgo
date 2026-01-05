<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TestController;

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

Route::get('/branches', [BranchController::class, 'index']);
Route::get('/total', [ItemController::class, 'getTotalItems']);

Route::get('/test', [TestController::class, 'index']);
Route::get('/category', [TestController::class, 'getCategory']);
Route::get('/items', [ItemController::class, 'index']);
Route::get('/price', [ItemController::class, 'ajaxPrice']);