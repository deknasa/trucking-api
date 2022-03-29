<?php

use App\Http\Controllers\Api\AkunPusatController;
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
use App\Http\Controllers\Api\AgenController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserAclController;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\LogTrailController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomValidationController;
use App\Http\Controllers\Api\ContainerController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\AlatBayarController;
use App\Http\Controllers\Api\BankPelangganController;

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

route::middleware('auth:api')->group(function () {
    Route::get('parameter/export', [ParameterController::class, 'export']);
    Route::get('parameter/field_length', [ParameterController::class, 'fieldLength']);
    Route::resource('parameter', ParameterController::class);

    Route::get('absensi/no_bukti', [AbsensiSupirHeaderController::class, 'getNoBukti']);
    Route::get('absensi/running_number', [AbsensiSupirHeaderController::class, 'getRunningNumber']);
    Route::get('absensi/grid', [AbsensiSupirHeaderController::class, 'grid']);
    Route::resource('absensi', AbsensiSupirHeaderController::class);

    Route::get('absen_trado/field_length', [AbsenTradoController::class, 'fieldLength']);
    Route::resource('absen_trado', AbsenTradoController::class);

    Route::get('agen/field_length', [AgenController::class, 'fieldLength']);
    Route::resource('agen', AgenController::class);

    Route::get('akun_pusat/field_length', [AkunPusatController::class, 'fieldLength']);
    Route::resource('akun_pusat', AkunPusatController::class);

    Route::get('error/field_length', [ErrorController::class, 'fieldLength']);
    Route::resource('error', ErrorController::class);

    Route::get('error/field_length', [ErrorController::class, 'fieldLength']);
    Route::get('error/geterror', [ErrorController::class, 'geterror']);
    Route::resource('error', ErrorController::class);

    Route::get('role/getroleid', [RoleController::class, 'getroleid']);
    Route::get('role/field_length', [RoleController::class, 'fieldLength']);
    Route::resource('role', RoleController::class);

    Route::get('cabang/field_length', [CabangController::class, 'fieldLength']);
    Route::get('cabang/combostatus', [CabangController::class, 'combostatus']);
    Route::get('cabang/getPosition2', [CabangController::class, 'getPosition2']);
    Route::resource('cabang', CabangController::class);

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

    Route::get('logtrail/detail', [LogTrailController::class, 'detail']);
    Route::get('logtrail/header', [LogTrailController::class, 'header']);
    Route::resource('logtrail', LogTrailController::class);

    Route::get('trado/combo', [TradoController::class, 'combo']);
    Route::get('trado/field_length', [TradoController::class, 'fieldLength']);
    Route::post('trado/upload_image/{id}', [TradoController::class, 'uploadImage']);
    Route::resource('trado', TradoController::class);

    Route::resource('absensi', AbsensiSupirHeaderController::class);
    Route::resource('absensi_detail', AbsensiSupirDetailController::class);
    Route::resource('absentrado', AbsenTradoController::class);

    Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');

    Route::get('container/field_length', [ContainerController::class, 'fieldLength']);
    Route::get('container/combostatus', [ContainerController::class, 'combostatus']);
    Route::get('container/getPosition2', [ContainerController::class, 'getPosition2']);
    Route::resource('container', ContainerController::class);

    Route::get('supir/combo', [SupirController::class, 'combo']);
    Route::get('supir/field_length', [SupirController::class, 'fieldLength']);
    Route::post('supir/upload_image/{id}', [SupirController::class, 'uploadImage']);
    Route::resource('supir', SupirController::class);

    Route::get('bank/combo', [BankController::class, 'combo']);
    Route::get('bank/field_length', [BankController::class, 'fieldLength']);
    Route::resource('bank', BankController::class);

    Route::get('alatbayar/combo', [AlatBayarController::class, 'combo']);
    Route::get('alatbayar/field_length', [AlatBayarController::class, 'fieldLength']);
    Route::resource('alatbayar', AlatBayarController::class);

    Route::get('bankpelanggan/combo', [BankPelangganController::class, 'combo']);
    Route::get('bankpelanggan/field_length', [BankPelangganController::class, 'fieldLength']);
    Route::resource('bankpelanggan', BankPelangganController::class);
});
