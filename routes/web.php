<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\CompletedShoppingListController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

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
    Route::prefix('/shopping')->group(function () {
        Route::get('/list', [ShoppingListController::class, 'list']);
        Route::post('/register', [ShoppingListController::class, 'register']);
        Route::delete('/delete/{shopping_id}', [ShoppingListController::class, 'delete'])->whereNumber('shopping_id')->name('delete');
        Route::post('/complete/{shopping_id}', [ShoppingListController::class, 'complete'])->whereNumber('shopping_id')->name('complete');
    });
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/completed_shopping_list/list', [CompletedShoppingListController::class, 'list']);
});

// 管理画面
Route::prefix('/admin')->group(function () {
    Route::get('', [AdminAuthController::class, 'index'])->name('admin.index');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login');
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/top', [AdminHomeController::class, 'top'])->name('admin.top');
        Route::get('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/user/list', [AdminUserController::class, 'list'])->name('admin.user.list');
    });
});

//会員登録
// ページ表示
Route::get('/user/register', [UserController::class, 'index'])->name('register.index');
// 登録処理
Route::post('/user/register', [UserController::class, 'register'])->name('register');


