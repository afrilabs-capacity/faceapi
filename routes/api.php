<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [LoginController::class, 'login'])->name('website.user.login');

Route::middleware('auth')->group(function(){
        Route::get('/get-stats', [\App\Http\Controllers\WebsiteController::class, 'count']);
        Route::post('/create-website', [\App\Http\Controllers\WebsiteController::class, 'create']);
        Route::get('/user', [\App\Http\Controllers\UserController::class, 'index']);
        Route::get('/load-websites', [\App\Http\Controllers\WebsiteController::class, 'index']);
    });
