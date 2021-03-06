<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'App\Http\Controllers\StockController@index');
// Alternative: Route::get('/', [App\Http\Controllers\StockController::class, 'index']);

Route::resource('stock', App\Http\Controllers\StockController::class);
Route::resource('data-source', App\Http\Controllers\DataSourceController::class);

Route::resource(
    'stock-option',
    App\Http\Controllers\StockOptionController::class,
    [
        'only' => ['index']
    ]
);
