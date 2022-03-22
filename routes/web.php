<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
    // \App\Models\User::create(['name' => 'admin', 'email' => 'admin@admin.com', 'password' => '$2y$10$bbmI9t352fXaOVs8QR4Hi.R723komh4rwi0D920HASdc69E508U9K']);
})->name('login');

Route::get('/#/auth/login', function () {
    return view('index');
})->name('login');

Route::middleware('auth')->get('/dashboard', function () {
    return view('welcome');
});

Route::post('/verify', [VerificationController::class, 'verify']);
