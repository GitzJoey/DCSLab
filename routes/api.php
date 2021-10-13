<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ApiAuthController;

Route::bind('id', function ($id) {
    if (!is_numeric($id)) {
        return \Vinkla\Hashids\Facades\Hashids::decode($id)[0];
    } else {
        return $id;
    }
});

Route::post('/api/auth', [ApiAuthController::class, 'auth', 'middleware' => 'throttle:3,1'])->name('api.auth');

Route::group(['prefix' => 'get', 'middleware' => ['auth:sanctum', 'throttle:100,1']], function () {
    Route::group(['prefix' => 'dashboard'], function() {

    });
});

Route::group(['prefix' => 'post', 'middleware' => ['auth:sanctum','throttle:10,1']], function () {
    Route::group(['prefix' => 'dashboard'], function() {

    });
});
