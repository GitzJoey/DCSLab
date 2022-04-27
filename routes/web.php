<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FrontController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StoreFrontController;

use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/', [FrontController::class, 'index'])->name('front');

Route::controller(StoreFrontController::class)->group(function () {
    Route::get('/storefront/home', 'index');
});

Route::get('/home', function() {
    return redirect()->route('db');
})->name('home');

Route::group(['prefix' => 'dashboard'], function () {
    Route::get('', [DashboardController::class, 'index'])->name('db');
    Route::any('/{all}', function($page) { return redirect()->route('db'); })->where(['all' => '.*']);
});

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');
