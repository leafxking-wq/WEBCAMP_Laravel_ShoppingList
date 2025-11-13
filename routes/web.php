<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShoppingListController;

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

//Route::get('/', function () {
//    return view('welcome');
//});

//買い物リスト
Route::get('/', [AuthController::class, 'index'])->name('front.index');;
Route::post('/login', [AuthController::class, 'login']);
//認可処理
Route::middleware(['auth'])->group(function () {
    Route::get('/shopping/list', [ShoppingListController::class, 'list']);
    Route::get('/logout', [AuthController::class, 'logout']);
});

//テスト
Route::get('/welcome', [WelcomeController::class, 'index']);
