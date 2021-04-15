<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DevController;
use App\Http\Controllers\DashboardController;

use Vinkla\Hashids\Facades\Hashids;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function() {
    return view('home');
});

Route::bind('id', function ($id) {
    if (!is_numeric($id)) {
        return Hashids::decode($id)[0];
    } else {
        return $id;
    }
});

Route::group(['prefix' => LaravelLocalization::setLocale()], function() {
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('', [DashboardController::class, 'index'])->name('db');
        Route::get('profile', [DashboardController::class, 'profile'])->name('db.profile');
        Route::get('settings', [DashboardController::class, 'settings'])->name('db.settings');

        Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->name('db.logs');

    });
});
