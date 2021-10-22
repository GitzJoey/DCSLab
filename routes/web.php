<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FrontController;
use App\Http\Controllers\DashboardController;

Route::get('/', [FrontController::class, 'index'])->name('front');

Route::get('/home', function() {
    return redirect()->route('db');
})->name('home');

Route::group(['prefix' => 'dashboard'], function () {
    Route::get('', [DashboardController::class, 'index'])->name('db');
    Route::any('/{all}', function($page) { return redirect()->route('db'); })->where(['all' => '.*']);
});
