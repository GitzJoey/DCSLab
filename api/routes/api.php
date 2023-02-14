<?php

use App\Http\Controllers\ApiAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('auth', [ApiAuthController::class, 'auth', 'middleware' => ['guest', 'throttle:3,1']])->name('api.auth');

Route::group(['prefix' => 'get', 'middleware' => ['auth', 'auth:sanctum', 'throttle:100,1'], 'as' => 'api.get'], function () {
    Route::group(['prefix' => 'dashboard', 'as' => '.db'], function () {
        
    });
});

Route::group(['prefix' => 'post', 'middleware' => ['auth', 'auth:sanctum', 'throttle:50,1'], 'as' => 'api.post'], function () {
    Route::group(['prefix' => 'dashboard', 'as' => '.db'], function () {
    });
});
