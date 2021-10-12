<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::bind('id', function ($id) {
    if (!is_numeric($id)) {
        return \Vinkla\Hashids\Facades\Hashids::decode($id)[0];
    } else {
        return $id;
    }
});

Route::post('auth', [ApiAuthController::class, 'auth', 'middleware' => 'throttle:3,1'])->name('api.auth');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
