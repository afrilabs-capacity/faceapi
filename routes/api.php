<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SubscriberController;

header('Access-Control-Allow-Origin:  *');
header('Access-Control-Allow-Credentials', ' true');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');

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
Route::get('/logout', [LoginController::class, 'logout'])->name('website.user.logout');
Route::get('/get-stats', [\App\Http\Controllers\WebsiteController::class, 'count']);


// Route::middleware('auth')->group(function () {
//     Route::get('/get-stats', [\App\Http\Controllers\WebsiteController::class, 'count']);
//     Route::post('/create-website', [\App\Http\Controllers\WebsiteController::class, 'create']);
//     Route::get('/user', [\App\Http\Controllers\UserController::class, 'index']);
//     Route::get('/load-websites', [\App\Http\Controllers\WebsiteController::class, 'index']);
//     Route::get('/load-websites-users', [\App\Http\Controllers\WebsiteUserController::class, 'index']);
//     Route::delete('/websites-users/{uuid}', [\App\Http\Controllers\WebsiteUserController::class, 'destroy']);
//     Route::delete('/websites/{uuid}', [\App\Http\Controllers\WebsiteController::class, 'destroy']);
// });

Route::get('/validate-site-key/{id}', [\App\Http\Controllers\WebsiteController::class, 'validateSiteKey']);
Route::get('/get-stats', [\App\Http\Controllers\WebsiteController::class, 'count']);
Route::post('/create-website', [\App\Http\Controllers\WebsiteController::class, 'create']);
Route::get('/user', [\App\Http\Controllers\UserController::class, 'index']);
Route::get('/load-websites', [\App\Http\Controllers\WebsiteController::class, 'index']);
Route::get('/load-websites-users', [\App\Http\Controllers\WebsiteUserController::class, 'index']);
Route::delete('/websites-users/{uuid}', [\App\Http\Controllers\WebsiteUserController::class, 'destroy']);
Route::delete('/websites/{uuid}', [\App\Http\Controllers\WebsiteController::class, 'destroy']);

Route::post('/verify', [SubscriberController::class, 'verify']);
Route::post('/verification', [\App\Http\Controllers\Api\SubscriberController::class, 'verify']);
Route::post('/verify-no-ui', [SubscriberController::class, 'verify']);

Route::get('/website/{website_id}/list/users', [\App\Http\Controllers\WebsiteUserController::class, 'getWebsiteUsersWebsiteId']);
Route::get('/website/{website_id}/destroy/user/{user_id}', [\App\Http\Controllers\WebsiteUserController::class, 'destroyWebsiteUserWebsiteIdUserId']);
