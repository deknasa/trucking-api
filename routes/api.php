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
use App\Http\Controllers\Api\AcosController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\AclController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserAclController;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomValidationController;

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

Route::post('token', [AuthController::class, 'token']);

route::middleware('auth:api')->group(function() {
    Route::get('parameter/field_length', [ParameterController::class, 'fieldLength']);
    Route::resource('parameter', ParameterController::class);
});

Route::get('error/field_length', [ErrorController::class, 'fieldLength']);
Route::resource('error', ErrorController::class);

Route::get('cabang/field_length', [CabangController::class, 'fieldLength']);
Route::get('cabang/combostatus', [CabangController::class, 'combostatus']);
Route::get('cabang/getPosition2', [CabangController::class, 'getPosition2']);
Route::resource('cabang', CabangController::class);

Route::get('error/field_length', [ErrorController::class, 'fieldLength']);
Route::resource('error', ErrorController::class);

Route::get('absensi/no_bukti', [AbsensiSupirHeaderController::class, 'getNoBukti']);
Route::get('absensi/running_number', [AbsensiSupirHeaderController::class, 'getRunningNumber']);

Route::get('role/getroleid', [RoleController::class, 'getroleid']);
Route::get('role/field_length', [RoleController::class, 'fieldLength']);
Route::resource('role', RoleController::class);

Route::get('acos/field_length', [AcosController::class, 'fieldLength']);
Route::resource('acos', AcosController::class);


Route::get('user/field_length', [UserController::class, 'fieldLength']);
Route::get('user/combostatus', [UserController::class, 'combostatus']);
Route::get('user/combocabang', [UserController::class, 'combocabang']);
Route::get('user/getuserid', [UserController::class, 'getuserid']);
Route::resource('user', UserController::class);

Route::get('menu/field_length', [MenuController::class, 'fieldLength']);
Route::get('menu/combomenuparent', [MenuController::class, 'combomenuparent']);
Route::get('menu/getdatanamaacos', [MenuController::class, 'getdatanamaacos']);
Route::resource('menu', MenuController::class);

Route::resource('absensi', AbsensiSupirHeaderController::class);
Route::resource('absensi_detail', AbsensiSupirDetailController::class);

Route::get('userrole/field_length', [UserRoleController::class, 'fieldLength']);
Route::get('userrole/detail', [UserRoleController::class, 'detail']);
Route::get('userrole/detaillist', [UserRoleController::class, 'detaillist']);
Route::get('userrole/combostatus', [UserRoleController::class, 'combostatus']);
Route::resource('userrole', UserRoleController::class);

Route::get('acl/field_length', [AclController::class, 'fieldLength']);
Route::get('acl/detail', [AclController::class, 'detail']);
Route::get('acl/detaillist', [AclController::class, 'detaillist']);
Route::get('acl/combostatus', [AclController::class, 'combostatus']);
Route::resource('acl', AclController::class);

Route::get('useracl/field_length', [UserAclController::class, 'fieldLength']);
Route::get('useracl/detail', [UserAclController::class, 'detail']);
Route::get('useracl/detaillist', [UserAclController::class, 'detaillist']);
Route::get('useracl/combostatus', [UserAclController::class, 'combostatus']);
Route::resource('useracl', UserAclController::class);


Route::resource('trado', TradoController::class);
Route::resource('supir', SupirController::class);
Route::resource('absentrado', AbsenTradoController::class);

Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');

