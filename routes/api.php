<?php

use App\Http\Controllers\Api\AbsensiSupirDetailController;
use App\Http\Controllers\Api\AbsensiSupirHeaderController;
use App\Http\Controllers\Api\AbsenTradoController;
use App\Http\Controllers\Api\CabangController;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\SupirController;
use App\Http\Controllers\Api\TradoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\RunningNumberController;
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

Route::prefix('auth')->group(function() {
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('parameter/field_length', [ParameterController::class, 'fieldLength']);
Route::resource('parameter', ParameterController::class);

Route::get('cabang/field_length', [CabangController::class, 'fieldLength']);
Route::get('cabang/combostatus', [CabangController::class, 'combostatus']);
Route::get('cabang/getPosition2', [CabangController::class, 'getPosition2']);
Route::resource('cabang', CabangController::class);

Route::get('absensi/no_bukti', [AbsensiSupirHeaderController::class, 'getNoBukti']);
Route::get('absensi/running_number', [AbsensiSupirHeaderController::class, 'getRunningNumber']);
Route::resource('absensi', AbsensiSupirHeaderController::class);
Route::resource('absensi_detail', AbsensiSupirDetailController::class);
Route::resource('trado', TradoController::class);
Route::resource('supir', SupirController::class);
Route::resource('absentrado', AbsenTradoController::class);

Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');