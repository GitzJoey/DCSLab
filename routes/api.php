<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RoleController;

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

Route::bind('id', function ($id) {
    if (!is_numeric($id)) {
        return \Vinkla\Hashids\Facades\Hashids::decode($id)[0];
    } else {
        return $id;
    }
});

Route::group(['prefix' => 'get', 'middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'role'], function () {
        Route::get('read', [RoleController::class, 'read'])->name('api.get.role.read');
        Route::get('permissions/read', [RoleController::class, 'getAllPermissions'])->name('api.get.role.permissions.read');
    });
});

Route::group(['prefix' => 'post', 'middleware' => 'auth:sanctum'], function () {

});
