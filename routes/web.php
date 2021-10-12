<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FrontController;

Route::get('/', [FrontController::class, 'index'])->name('front');
