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

Route::post('/tokens/create', function (Request $request) {
    $token = \App\Models\User::where('id', 10)->first()->createToken('aa');

    return ['token' => $token->plainTextToken];
});


Route::group(['prefix' => 'get', 'middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'role'], function () {
        Route::get('read', [RoleController::class, 'read'])->name('api.get.role.read');
        Route::get('permission/read', [RoleController::class, 'getAllPermissions'])->name('api.get.role.permission.read');
    });
});

Route::group(['prefix' => 'post', 'middleware' => 'auth:api'], function () {

});
