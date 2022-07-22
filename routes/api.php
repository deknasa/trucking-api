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
use App\Http\Controllers\Api\JenisEmklController;
use App\Http\Controllers\Api\JenisOrderController;
use App\Http\Controllers\Api\JenisTradoController;
use App\Http\Controllers\Api\KasGantungDetailController;
use App\Http\Controllers\Api\KasGantungHeaderController;
use App\Http\Controllers\Api\GudangController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\PenerimaController;
use App\Http\Controllers\Api\StatusContainerController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\KelompokController;
use App\Http\Controllers\Api\KerusakanController;
use App\Http\Controllers\Api\SubKelompokController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\KotaController;
use App\Http\Controllers\Api\MandorController;
use App\Http\Controllers\Api\MerkController;
use App\Http\Controllers\Api\PenerimaanTruckingController;
use App\Http\Controllers\Api\SatuanController;
use App\Http\Controllers\Api\ZonaController;
use App\Http\Controllers\Api\TarifController;
use App\Http\Controllers\Api\PengeluaranTruckingController;
use App\Http\Controllers\Api\OrderanTruckingController;
use App\Http\Controllers\Api\ProsesAbsensiSupirController;
use App\Http\Controllers\Api\MekanikController;
use App\Http\Controllers\Api\SuratPengantarController;
use App\Http\Controllers\Api\UpahSupirController;
use App\Http\Controllers\Api\UpahSupirRincianController;
use App\Http\Controllers\Api\UpahRitasiController;
use App\Http\Controllers\Api\UpahRitasiRincianController;
use App\Http\Controllers\Api\RitasiController;

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
    Route::get('parameter/combo', [ParameterController::class, 'combo']);
    Route::resource('parameter', ParameterController::class);

    Route::get('absensi/no_bukti', [AbsensiSupirHeaderController::class, 'getNoBukti']);
    Route::get('absensi/running_number', [AbsensiSupirHeaderController::class, 'getRunningNumber']);
    Route::get('absensi/grid', [AbsensiSupirHeaderController::class, 'grid']);
    Route::resource('absensi', AbsensiSupirHeaderController::class);

    Route::get('absen_trado/field_length', [AbsenTradoController::class, 'fieldLength']);
    Route::resource('absen_trado', AbsenTradoController::class);

    Route::get('agen/field_length', [AgenController::class, 'fieldLength']);
    Route::get('agen/export', [AgenController::class, 'export'])->name('export');
    Route::post('agen/{agen}/approval', [AgenController::class, 'approval'])->name('agen.approval');
    Route::resource('agen', AgenController::class);

    Route::get('error/field_length', [ErrorController::class, 'fieldLength']);
    Route::resource('error', ErrorController::class);

    Route::get('cabang/field_length', [CabangController::class, 'fieldLength']);
    Route::get('cabang/combostatus', [CabangController::class, 'combostatus']);
    Route::get('cabang/getPosition2', [CabangController::class, 'getPosition2']);
    Route::resource('cabang', CabangController::class);

    Route::get('error/field_length', [ErrorController::class, 'fieldLength']);
    Route::get('error/geterror', [ErrorController::class, 'geterror']);
    Route::resource('error', ErrorController::class);

    Route::get('role/getroleid', [RoleController::class, 'getroleid']);
    Route::get('role/field_length', [RoleController::class, 'fieldLength']);
    Route::resource('role', RoleController::class);

    Route::get('acos/field_length', [AcosController::class, 'fieldLength']);
    Route::resource('acos', AcosController::class);

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

    Route::get('jenisemkl/combo', [JenisEmklController::class, 'combo']);
    Route::get('jenisemkl/field_length', [JenisEmklController::class, 'fieldLength']);
    Route::resource('jenisemkl', JenisEmklController::class);

    Route::get('jenisorder/combo', [JenisOrderController::class, 'combo']);
    Route::get('jenisorder/field_length', [JenisOrderController::class, 'fieldLength']);
    Route::resource('jenisorder', JenisOrderController::class);

    Route::get('jenistrado/combo', [JenisTradoController::class, 'combo']);
    Route::get('jenistrado/field_length', [JenisTradoController::class, 'fieldLength']);
    Route::resource('jenistrado', JenisTradoController::class);

    Route::get('akunpusat/field_length', [AkunPusatController::class, 'fieldLength']);
    Route::resource('akunpusat', AkunPusatController::class)->parameters(['akunpusat' => 'akunPusat']);

    Route::get('error/field_length', [ErrorController::class, 'fieldLength']);
    Route::get('error/geterror', [ErrorController::class, 'geterror']);
    Route::get('error/export', [ErrorController::class, 'export'])->name('error.export');
    Route::resource('error', ErrorController::class);

    Route::get('role/getroleid', [RoleController::class, 'getroleid']);
    Route::get('role/field_length', [RoleController::class, 'fieldLength']);
    Route::get('role/export', [RoleController::class, 'export'])->name('role.export');
    Route::resource('role', RoleController::class);

    Route::get('cabang/field_length', [CabangController::class, 'fieldLength']);
    Route::get('cabang/combostatus', [CabangController::class, 'combostatus']);
    Route::get('cabang/getPosition2', [CabangController::class, 'getPosition2']);
    Route::get('cabang/export', [CabangController::class, 'export'])->name('cabang.export');
    Route::resource('cabang', CabangController::class);

    Route::get('acos/field_length', [AcosController::class, 'fieldLength']);
    Route::resource('acos', AcosController::class);

    Route::get('user/field_length', [UserController::class, 'fieldLength']);
    Route::get('user/export', [UserController::class, 'export'])->name('user.export');
    Route::get('user/combostatus', [UserController::class, 'combostatus']);
    Route::get('user/combocabang', [UserController::class, 'combocabang']);
    Route::get('user/getuserid', [UserController::class, 'getuserid']);
    Route::resource('user', UserController::class);

    Route::get('menu/field_length', [MenuController::class, 'fieldLength']);
    Route::get('menu/combomenuparent', [MenuController::class, 'combomenuparent']);
    Route::get('menu/getdatanamaacos', [MenuController::class, 'getdatanamaacos']);
    Route::get('menu/export', [MenuController::class, 'export'])->name('menu.export');
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
    Route::get('acl/export', [AclController::class, 'export'])->name('acl.export');
    Route::resource('acl', AclController::class);

    Route::get('useracl/field_length', [UserAclController::class, 'fieldLength']);
    Route::get('useracl/detail', [UserAclController::class, 'detail']);
    Route::get('useracl/detaillist', [UserAclController::class, 'detaillist']);
    Route::get('useracl/combostatus', [UserAclController::class, 'combostatus']);
    Route::get('useracl/export', [UserAclController::class, 'export'])->name('useracl.export');
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

    Route::get('subkelompok/export', [SubKelompokController::class, 'export']);
    Route::get('subkelompok/field_length', [SubKelompokController::class, 'fieldLength']);
    Route::resource('subkelompok', SubKelompokController::class)->parameters(['subkelompok' => 'subKelompok']);

    Route::get('supplier/export', [SupplierController::class, 'export']);
    Route::get('supplier/field_length', [SupplierController::class, 'fieldLength']);
    Route::resource('supplier', SupplierController::class);

    Route::get('penerima/export', [PenerimaController::class, 'export']);
    Route::get('penerima/field_length', [PenerimaController::class, 'fieldLength']);
    Route::resource('penerima', PenerimaController::class);

    Route::get('pelanggan/export', [PelangganController::class, 'export']);
    Route::get('pelanggan/field_length', [PelangganController::class, 'fieldLength']);
    Route::resource('pelanggan', PelangganController::class);

    Route::get('statuscontainer/export', [StatusContainerController::class, 'export']);
    Route::get('statuscontainer/field_length', [StatusContainerController::class, 'fieldLength']);
    Route::resource('statuscontainer', StatusContainerController::class)->parameters(['statuscontainer' => 'statusContainer']);

    Route::get('penerimaantrucking/export', [PenerimaanTruckingController::class, 'export']);
    Route::get('penerimaantrucking/field_length', [PenerimaanTruckingController::class, 'fieldLength']);
    Route::resource('penerimaantrucking', PenerimaanTruckingController::class)->parameters(['penerimaantrucking' => 'penerimaanTrucking']);

    Route::get('pengeluaran_trucking/export', [PengeluaranTruckingController::class, 'export']);
    Route::get('pengeluaran_trucking/field_length', [PengeluaranTruckingController::class, 'fieldLength']);
    Route::resource('pengeluaran_trucking', PengeluaranTruckingController::class);
});

Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
Route::get('kasgantung/no_bukti', [KasGantungHeaderController::class, 'getNoBukti']);
Route::get('kasgantung/combo', [KasGantungHeaderController::class, 'combo']);
Route::get('kasgantung/grid', [KasGantungHeaderController::class, 'grid']);
Route::resource('kasgantung', KasGantungHeaderController::class);

Route::resource('kasgantung_detail', KasGantungDetailController::class);

Route::get('gudang/combo', [GudangController::class, 'combo']);
Route::get('gudang/field_length', [GudangController::class, 'fieldLength']);
Route::resource('gudang', GudangController::class);

Route::get('kategori/combo', [KategoriController::class, 'combo']);
Route::get('kategori/field_length', [KategoriController::class, 'fieldLength']);
Route::resource('kategori', KategoriController::class);

Route::get('kelompok/combo', [KelompokController::class, 'combo']);
Route::get('kelompok/field_length', [KelompokController::class, 'fieldLength']);
Route::resource('kelompok', KelompokController::class);

Route::get('kerusakan/combo', [KerusakanController::class, 'combo']);
Route::get('kerusakan/field_length', [KerusakanController::class, 'fieldLength']);
Route::resource('kerusakan', KerusakanController::class);

Route::get('kota/combo', [KotaController::class, 'combo']);
Route::get('kota/field_length', [KotaController::class, 'fieldLength']);
Route::resource('kota', KotaController::class)->parameters(['kota' => 'kota']);

Route::get('mandor/combo', [MandorController::class, 'combo']);
Route::get('mandor/field_length', [MandorController::class, 'fieldLength']);
Route::resource('mandor', MandorController::class);

Route::get('merk/combo', [MerkController::class, 'combo']);
Route::get('merk/field_length', [MerkController::class, 'fieldLength']);
Route::resource('merk', MerkController::class);

Route::get('satuan/combo', [SatuanController::class, 'combo']);
Route::get('satuan/field_length', [SatuanController::class, 'fieldLength']);
Route::resource('satuan', SatuanController::class);

Route::get('zona/combo', [ZonaController::class, 'combo']);
Route::get('zona/field_length', [ZonaController::class, 'fieldLength']);
Route::resource('zona', ZonaController::class);

Route::get('tarif/combo', [TarifController::class, 'combo']);
Route::get('tarif/field_length', [TarifController::class, 'fieldLength']);
Route::resource('tarif', TarifController::class);

Route::get('orderantrucking/combo', [OrderanTruckingController::class, 'combo']);
Route::get('orderantrucking/field_length', [OrderanTruckingController::class, 'fieldLength']);
Route::resource('orderantrucking', OrderanTruckingController::class);

Route::get('prosesabsensisupir/combo', [ProsesAbsensiSupirController::class, 'combo']);
Route::get('prosesabsensisupir/field_length', [ProsesAbsensiSupirController::class, 'fieldLength']);
Route::resource('prosesabsensisupir', ProsesAbsensiSupirController::class);

Route::get('mekanik/combo', [MekanikController::class, 'combo']);
Route::get('mekanik/field_length', [MekanikController::class, 'fieldLength']);
Route::resource('mekanik', MekanikController::class);

Route::get('suratpengantar/combo', [SuratPengantarController::class, 'combo']);
Route::get('suratpengantar/field_length', [SuratPengantarController::class, 'fieldLength']);
Route::get('suratpengantar/get_gaji', [SuratPengantarController::class, 'getGaji']);
Route::resource('suratpengantar', SuratPengantarController::class);

Route::get('upahsupir/combo', [UpahSupirController::class, 'combo']);
Route::resource('upahsupir', UpahSupirController::class);

Route::resource('upahsupirrincian', UpahSupirRincianController::class);

Route::get('upahritasi/combo', [UpahRitasiController::class, 'combo']);
Route::resource('upahritasi', UpahRitasiController::class);

Route::resource('upahritasirincian', UpahRitasiRincianController::class);

Route::get('ritasi/combo', [RitasiController::class, 'combo']);
Route::get('ritasi/field_length', [RitasiController::class, 'fieldLength']);
Route::resource('ritasi', RitasiController::class);
