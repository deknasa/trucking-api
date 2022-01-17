<?php

use App\Http\Controllers\AbsensiSupirHeaderController;
use App\Http\Controllers\AbsenTradoController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\SupirController;
use App\Http\Controllers\TradoController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('parameter/field_length', [ParameterController::class, 'fieldLength']);
Route::resource('parameter', ParameterController::class);

Route::resource('absensi', AbsensiSupirHeaderController::class);
Route::resource('trado', TradoController::class);
Route::resource('supir', SupirController::class);
Route::resource('absentrado', AbsenTradoController::class);