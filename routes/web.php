<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FrontController;
use App\Http\Controllers\DashboardController;

Route::get('/', [FrontController::class, 'index'])->name('front');

Route::get('/home', function() {
    return redirect()->action([DashboardController::class, 'index']);
})->name('home');
