<?php

use App\Http\Controllers\Api\AbsensiSupirDetailController;
use App\Http\Controllers\Api\AbsensiSupirHeaderController;
use App\Http\Controllers\Api\AbsenTradoController;
use App\Http\Controllers\Api\CabangController;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\SupirController;
use App\Http\Controllers\Api\TradoController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\AuthController;
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

Route::get('role/field_length', [RoleController::class, 'fieldLength']);
Route::resource('role', RoleController::class);

Route::get('user/field_length', [UserController::class, 'fieldLength']);
Route::get('user/combostatus', [UserController::class, 'combostatus']);
Route::get('user/combocabang', [UserController::class, 'combocabang']);
Route::resource('user', UserController::class);

Route::get('menu/field_length', [MenuController::class, 'fieldLength']);
Route::get('menu/combomenuparent', [MenuController::class, 'combomenuparent']);
Route::resource('menu', MenuController::class);

Route::resource('absensi', AbsensiSupirHeaderController::class);
Route::resource('absensi_detail', AbsensiSupirDetailController::class);
Route::resource('trado', TradoController::class);
Route::resource('supir', SupirController::class);
Route::resource('absentrado', AbsenTradoController::class);