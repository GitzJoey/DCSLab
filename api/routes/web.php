<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    $dashboardUrl = env('FRONTEND_URL').'/dashboard/main'.'?'.http_build_query(request()->query());
    return redirect()->away($dashboardUrl);
})->name('home');

Route::get('/do-reset-password', function () {
    $resetUrl = env('FRONTEND_URL').'/auth/reset-password'.'?'.http_build_query(request()->query());
    return redirect()->away($resetUrl);
})->name('password.reset');
