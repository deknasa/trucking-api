<?php

    use App\Http\Controllers\Api\AkunPusatController;
    use App\Http\Controllers\Api\AbsensiSupirDetailController;
    use App\Http\Controllers\Api\AbsensiSupirHeaderController;

    use App\Http\Controllers\Api\AbsensiSupirApprovalHeaderController;
    use App\Http\Controllers\Api\AbsensiSupirApprovalDetailController;
    
    use App\Http\Controllers\Api\ApprovalTransaksiHeaderController;
    use App\Http\Controllers\Api\ApprovalInvoiceHeaderController;
    
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
    use App\Http\Controllers\Api\ApprovalHutangBayarController;
    use App\Http\Controllers\Api\ApprovalNotaHeaderController;
use App\Http\Controllers\Api\ApprovalPendapatanSupirController;
use App\Http\Controllers\Api\BankPelangganController;
    use App\Http\Controllers\Api\GajiSupirDetailController;
    use App\Http\Controllers\Api\GajiSupirHeaderController;
    use App\Http\Controllers\Api\JenisEmklController;
    use App\Http\Controllers\Api\JenisOrderController;
    use App\Http\Controllers\Api\JenisTradoController;
    use App\Http\Controllers\Api\KasGantungDetailController;
    use App\Http\Controllers\Api\KasGantungHeaderController;
    
    use App\Http\Controllers\Api\NotaDebetDetailController;
    use App\Http\Controllers\Api\NotaDebetHeaderController;
    
    use App\Http\Controllers\Api\NotaKreditHeaderController;
    use App\Http\Controllers\Api\NotaKreditDetailController;

    use App\Http\Controllers\Api\PengembalianKasGantungHeaderController;
    use App\Http\Controllers\Api\PengembalianKasGantungDetailController;
    
    use App\Http\Controllers\Api\PengembalianKasBankHeaderController;
    use App\Http\Controllers\Api\PengembalianKasBankDetailController;
    
    use App\Http\Controllers\Api\RekapPengeluaranHeaderController;
    use App\Http\Controllers\Api\RekapPengeluaranDetailController;
    
    use App\Http\Controllers\Api\RekapPenerimaanHeaderController;
    use App\Http\Controllers\Api\RekapPenerimaanDetailController;
    
    use App\Http\Controllers\Api\GudangController;
    use App\Http\Controllers\Api\PelangganController;
    use App\Http\Controllers\Api\PenerimaController;
    use App\Http\Controllers\Api\StatusContainerController;
    use App\Http\Controllers\Api\KategoriController;
    use App\Http\Controllers\Api\KelompokController;
    use App\Http\Controllers\Api\KerusakanController;
    use App\Http\Controllers\Api\SubKelompokController;
    use App\Http\Controllers\Api\SupplierController;
    use App\Http\Controllers\Api\StokController;
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
    use App\Http\Controllers\Api\ServiceInHeaderController;
    use App\Http\Controllers\Api\ServiceInDetailController;
    use App\Http\Controllers\Api\ServiceOutHeaderController;
    use App\Http\Controllers\Api\ServiceOutDetailController;
    use App\Http\Controllers\Api\PenerimaanHeaderController;
    use App\Http\Controllers\Api\PenerimaanDetailController;
    use App\Http\Controllers\Api\PengeluaranHeaderController;
    use App\Http\Controllers\Api\PengeluaranDetailController;
    use App\Http\Controllers\Api\PenerimaanTruckingHeaderController;
    use App\Http\Controllers\Api\PenerimaanTruckingDetailController;
    use App\Http\Controllers\Api\PenerimaanStokController;
    use App\Http\Controllers\Api\PenerimaanStokHeaderController;
    use App\Http\Controllers\Api\PenerimaanStokDetailController;
    use App\Http\Controllers\Api\PengeluaranStokController;
    use App\Http\Controllers\Api\PengeluaranStokDetailController;
    use App\Http\Controllers\Api\PengeluaranStokHeaderController;
    use App\Http\Controllers\Api\JurnalUmumHeaderController;
    use App\Http\Controllers\Api\JurnalUmumDetailController;
    use App\Http\Controllers\Api\PengeluaranTruckingHeaderController;
    use App\Http\Controllers\Api\PengeluaranTruckingDetailController;
    use App\Http\Controllers\Api\PiutangHeaderController;
    use App\Http\Controllers\Api\PiutangDetailController;
    use App\Http\Controllers\Api\HutangHeaderController;
    use App\Http\Controllers\Api\HutangDetailController;
    use App\Http\Controllers\Api\PelunasanPiutangHeaderController;
    use App\Http\Controllers\Api\PelunasanPiutangDetailController;
    use App\Http\Controllers\Api\HutangBayarHeaderController;
    use App\Http\Controllers\Api\HutangBayarDetailController;
    use App\Http\Controllers\Api\InvoiceDetailController;
    use App\Http\Controllers\Api\InvoiceHeaderController;
    use App\Http\Controllers\Api\InvoiceExtraDetailController;
    use App\Http\Controllers\Api\InvoiceExtraHeaderController;
    use App\Http\Controllers\Api\ProsesGajiSupirHeaderController;
    use App\Http\Controllers\Api\ProsesGajiSupirDetailController;
    use App\Http\Controllers\Api\HariLiburController;
    use App\Http\Controllers\Api\PenerimaanGiroDetailController;
    use App\Http\Controllers\Api\PenerimaanGiroHeaderController;
    use App\Http\Controllers\Api\JurnalUmumPusatDetailController;
    use App\Http\Controllers\Api\JurnalUmumPusatHeaderController;
    use App\Http\Controllers\Api\ReportAllController;
    use App\Http\Controllers\Api\PencairanGiroPengeluaranDetailController;
    use App\Http\Controllers\Api\PencairanGiroPengeluaranHeaderController;
    use App\Http\Controllers\Api\PendapatanSupirDetailController;
    use App\Http\Controllers\Api\PendapatanSupirHeaderController;
    use App\Http\Controllers\Api\ReportNeracaController;

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

    Route::get('supir/image/{field}/{filename}/{type}', [SupirController::class, 'getImage']);
    Route::get('trado/image/{field}/{filename}/{type}', [TradoController::class, 'getImage']);

    route::middleware(['auth:api'])->group(function () {
        Route::get('parameter/export', [ParameterController::class, 'export']);
        Route::get('parameter/field_length', [ParameterController::class, 'fieldLength']);
        Route::get('parameter/combo', [ParameterController::class, 'combo']);
        Route::resource('parameter', ParameterController::class);

        Route::get('absensisupirheader/{id}/detail', [AbsensiSupirHeaderController::class, 'detail'])->name('absensi.detail');
        Route::get('absensisupirheader/no_bukti', [AbsensiSupirHeaderController::class, 'getNoBukti']);
        Route::get('absensisupirheader/running_number', [AbsensiSupirHeaderController::class, 'getRunningNumber']);
        Route::get('absensisupirheader/grid', [AbsensiSupirHeaderController::class, 'grid']);
        Route::get('absensisupirheader/field_length', [AbsensiSupirHeaderController::class, 'fieldLength']);
        Route::apiResource('absensisupirheader', AbsensiSupirHeaderController::class)->parameter('absensisupirheader', 'absensiSupirHeader');
        
        Route::resource('absensisupirdetail', AbsensiSupirDetailController::class);
        
        Route::get('approvaltransaksiheader/combo', [ApprovalTransaksiHeaderController::class, 'combo']);
        Route::apiResource('approvaltransaksiheader', ApprovalTransaksiHeaderController::class);
        
        Route::get('approvalinvoiceheader/combo', [ApprovalInvoiceHeaderController::class, 'combo']);
        Route::apiResource('approvalinvoiceheader', ApprovalInvoiceHeaderController::class);

        
        Route::get('absensisupirapprovalheader/running_number', [AbsensiSupirApprovalHeaderController::class, 'getRunningNumber']);
        Route::get('absensisupirapprovalheader/grid', [AbsensiSupirApprovalHeaderController::class, 'grid']);
        Route::get('absensisupirapprovalheader/field_length', [AbsensiSupirApprovalHeaderController::class, 'fieldLength']);
        Route::get('absensisupirapprovalheader/export', [AbsensiSupirApprovalHeaderController::class, 'export']);
        Route::get('absensisupirapprovalheader/{absensi}/getabsensi', [AbsensiSupirApprovalHeaderController::class, 'getAbsensi']);
        Route::get('absensisupirapprovalheader/{absensi}/getapproval', [AbsensiSupirApprovalHeaderController::class, 'getApproval']);
        Route::post('absensisupirapprovalheader/{id}/approval', [AbsensiSupirApprovalHeaderController::class,'approval']);
        Route::apiResource('absensisupirapprovalheader', AbsensiSupirApprovalHeaderController::class);
        Route::apiResource('absensisupirapprovaldetail', AbsensiSupirApprovalDetailController::class);

        Route::get('absen_trado/field_length', [AbsenTradoController::class, 'fieldLength']);
        Route::resource('absen_trado', AbsenTradoController::class);

        Route::get('agen/field_length', [AgenController::class, 'fieldLength']);
        Route::get('agen/export', [AgenController::class, 'export'])->name('export');
        Route::post('agen/{agen}/approval', [AgenController::class, 'approval'])->name('agen.approval');
        Route::resource('agen', AgenController::class);

        Route::get('cabang/field_length', [CabangController::class, 'fieldLength']);
        Route::get('cabang/combostatus', [CabangController::class, 'combostatus']);
        Route::get('cabang/getPosition2', [CabangController::class, 'getPosition2']);
        Route::resource('cabang', CabangController::class);

        Route::get('acos/field_length', [AcosController::class, 'fieldLength']);
        Route::resource('acos', AcosController::class);


        Route::get('logtrail/detail', [LogTrailController::class, 'detail']);
        Route::get('logtrail/header', [LogTrailController::class, 'header']);
        Route::resource('logtrail', LogTrailController::class);

        Route::get('trado/combo', [TradoController::class, 'combo']);
        Route::get('trado/field_length', [TradoController::class, 'fieldLength']);
        Route::post('trado/upload_image/{id}', [TradoController::class, 'uploadImage']);
        Route::resource('trado', TradoController::class);

        Route::resource('absentrado', AbsenTradoController::class);

        Route::get('container/field_length', [ContainerController::class, 'fieldLength']);
        Route::get('container/combostatus', [ContainerController::class, 'combostatus']);
        Route::get('container/getPosition2', [ContainerController::class, 'getPosition2']);
        Route::resource('container', ContainerController::class);

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
        Route::get('menu/controller', [MenuController::class, 'listclassall']);
        Route::get('menu/getdatanamaacos', [MenuController::class, 'getdatanamaacos']);
        Route::get('menu/export', [MenuController::class, 'export'])->name('menu.export');
        Route::resource('menu', MenuController::class);

        Route::get('userrole/field_length', [UserRoleController::class, 'fieldLength']);
        Route::get('userrole/detail', [UserRoleController::class, 'detail']);
        Route::get('userrole/detaillist', [UserRoleController::class, 'detaillist']);
        Route::get('userrole/combostatus', [UserRoleController::class, 'combostatus']);
        Route::get('userrole/export', [UserRoleController::class, 'export'])->name('userrole.export');
        Route::resource('userrole', UserRoleController::class);

        Route::get('acl/field_length', [AclController::class, 'fieldLength']);
        Route::get('acl/detail/{roleId}', [AclController::class, 'detail']);
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
        Route::get('trado/getImage/{id}/{field}', [TradoController::class, 'getImage']);
        Route::post('trado/upload_image/{id}', [TradoController::class, 'uploadImage']);
        Route::resource('trado', TradoController::class);

        Route::resource('absentrado', AbsenTradoController::class);

        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');

        Route::get('container/field_length', [ContainerController::class, 'fieldLength']);
        Route::get('container/combostatus', [ContainerController::class, 'combostatus']);
        Route::get('container/getPosition2', [ContainerController::class, 'getPosition2']);
        Route::resource('container', ContainerController::class);

        Route::get('supir/combo', [SupirController::class, 'combo']);
        Route::get('supir/field_length', [SupirController::class, 'fieldLength']);
        Route::get('supir/getImage/{id}/{field}', [SupirController::class, 'getImage']);
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
        
        // Route::get('supplier/export', [SupplierController::class, 'export']);
        // Route::get('supplier/field_length', [SupplierController::class, 'fieldLength']);
        Route::apiResource('stok', StokController::class);

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

        Route::get('pengeluarantrucking/export', [PengeluaranTruckingController::class, 'export']);
        Route::get('pengeluarantrucking/field_length', [PengeluaranTruckingController::class, 'fieldLength']);
        Route::resource('pengeluarantrucking', PengeluaranTruckingController::class)->parameters(['pengeluarantrucking' => 'pengeluaranTrucking']);


        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('jurnalumumheader/no_bukti', [JurnalUmumHeaderController::class, 'getNoBukti']);
        Route::post('jurnalumumheader/{id}/approval', [JurnalUmumHeaderController::class, 'approval'])->name('jurnalumumheader.approval');
        Route::get('jurnalumumheader/combo', [JurnalUmumHeaderController::class, 'combo']);
        Route::post('jurnalumumheader/{id}/cekapproval', [JurnalUmumHeaderController::class, 'cekapproval'])->name('jurnalumumheader.cekapproval');
        Route::get('jurnalumumheader/grid', [JurnalUmumHeaderController::class, 'grid']);
        Route::get('jurnalumumheader/field_length', [JurnalUmumHeaderController::class, 'fieldLength']);
        Route::resource('jurnalumumheader', JurnalUmumHeaderController::class);
        Route::resource('jurnalumumdetail', JurnalUmumDetailController::class);

        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('penerimaantruckingheader/no_bukti', [PenerimaanTruckingHeaderController::class, 'getNoBukti']);
        Route::get('penerimaantruckingheader/combo', [PenerimaanTruckingHeaderController::class, 'combo']);
        Route::get('penerimaantruckingheader/grid', [PenerimaanTruckingHeaderController::class, 'grid']);
        Route::get('penerimaantruckingheader/field_length', [PenerimaanTruckingHeaderController::class, 'fieldLength']);
        Route::resource('penerimaantruckingheader', PenerimaanTruckingHeaderController::class);
        Route::resource('penerimaantruckingdetail', PenerimaanTruckingDetailController::class);

        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('pengeluarantruckingheader/no_bukti', [PengeluaranTruckingHeaderController::class, 'getNoBukti']);
        Route::get('pengeluarantruckingheader/combo', [PengeluaranTruckingHeaderController::class, 'combo']);
        Route::get('pengeluarantruckingheader/grid', [PengeluaranTruckingHeaderController::class, 'grid']);
        Route::get('pengeluarantruckingheader/field_length', [PengeluaranTruckingHeaderController::class, 'fieldLength']);
        Route::resource('pengeluarantruckingheader', PengeluaranTruckingHeaderController::class);
        Route::resource('pengeluarantruckingdetail', PengeluaranTruckingDetailController::class);

        Route::get('penerimaanstok/field_length', [PenerimaanStokController::class,'fieldLength']);
        Route::get('penerimaanstok/export', [PenerimaanStokController::class,'export']);
        Route::apiResource('penerimaanstok', PenerimaanStokController::class);
        Route::apiResource('penerimaanstokheader', PenerimaanStokHeaderController::class);
        Route::apiResource('penerimaanstokdetail', PenerimaanStokDetailController::class);

        Route::get('pengeluaranstok/field_length', [PengeluaranStokController::class,'fieldLength']);
        // Route::get('pengeluaranstok/export', [PengeluaranStokController::class,'export']);
        Route::apiResource('pengeluaranstok', PengeluaranStokController::class);
        Route::apiResource('pengeluaranstokheader', PengeluaranStokHeaderController::class);
        Route::apiResource('pengeluaranstokdetail', PengeluaranStokDetailController::class);

                
        Route::get('pengeluaranstok/field_length', [PengeluaranStokController::class,'fieldLength']);
        // Route::get('pengeluaranstok/export', [PengeluaranStokController::class,'export']);
        Route::post('invoiceextraheader/{id}/approval', [InvoiceExtraHeaderController::class,'approval']);
        Route::get('invoiceextraheader/{id}/printreport', [InvoiceExtraHeaderController::class,'printReport']);
        Route::resource('invoiceextraheader', InvoiceExtraHeaderController::class);
        Route::resource('invoiceextradetail', InvoiceExtraDetailController::class);

        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('piutangheader/no_bukti', [PiutangHeaderController::class, 'getNoBukti']);
        Route::get('piutangheader/grid', [PiutangHeaderController::class, 'grid']);
        Route::get('piutangheader/field_length', [PiutangHeaderController::class, 'fieldLength']);
        Route::apiResource('piutangheader', PiutangHeaderController::class)->parameters(['piutangheader' => 'piutangHeader']);
        Route::apiResource('piutangdetail', PiutangDetailController::class);

        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('hutangheader/no_bukti', [HutangHeaderController::class, 'getNoBukti']);
        Route::get('hutangheader/combo', [HutangHeaderController::class, 'combo']);
        Route::get('hutangheader/grid', [HutangHeaderController::class, 'grid']);
        Route::get('hutangheader/field_length', [HutangHeaderController::class, 'fieldLength']);
        Route::resource('hutangheader', HutangHeaderController::class);
        Route::resource('hutangdetail', HutangDetailController::class);

        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('pelunasanpiutangheader/no_bukti', [PelunasanPiutangHeaderController::class, 'getNoBukti']);
        Route::get('pelunasanpiutangheader/combo', [PelunasanPiutangHeaderController::class, 'combo']);
        Route::get('pelunasanpiutangheader/{id}/getpiutang', [PelunasanPiutangHeaderController::class, 'getpiutang'])->name('pelunasanpiutangheader.getpiutang');
        Route::get('pelunasanpiutangheader/{id}/{agenid}/getPelunasanPiutang', [PelunasanPiutangHeaderController::class, 'getPelunasanPiutang']);
        Route::get('pelunasanpiutangheader/{id}/{agenid}/getDeletePelunasanPiutang', [PelunasanPiutangHeaderController::class, 'getDeletePelunasanPiutang']);
        Route::get('pelunasanpiutangheader/grid', [PelunasanPiutangHeaderController::class, 'grid']);
        Route::get('pelunasanpiutangheader/field_length', [PelunasanPiutangHeaderController::class, 'fieldLength']);
        Route::resource('pelunasanpiutangheader', PelunasanPiutangHeaderController::class);
        Route::resource('pelunasanpiutangdetail', PelunasanPiutangDetailController::class);

        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('hutangbayarheader/no_bukti', [HutangBayarHeaderController::class, 'getNoBukti']);
        Route::get('hutangbayarheader/field_length', [HutangBayarHeaderController::class, 'fieldLength']);
        Route::get('hutangbayarheader/combo', [HutangBayarHeaderController::class, 'combo']);
        Route::get('hutangbayarheader/{id}/getHutang', [HutangBayarHeaderController::class, 'getHutang'])->name('hutangbayarheader.getHutang'); 
        Route::get('hutangbayarheader/comboapproval', [HutangBayarHeaderController::class, 'comboapproval']);
        Route::post('hutangbayarheader/{id}/cekapproval', [HutangBayarHeaderController::class, 'cekapproval'])->name('hutangbayarheader.cekapproval');
        Route::get('hutangbayarheader/{id}/{supplierid}/getPembayaran', [HutangBayarHeaderController::class, 'getPembayaran']);
        Route::get('hutangbayarheader/grid', [HutangBayarHeaderController::class, 'grid']);
        Route::resource('hutangbayarheader', HutangBayarHeaderController::class);
        Route::resource('hutangbayardetail', HutangBayarDetailController::class);

        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('serviceinheader/no_bukti', [ServiceInHeaderController::class, 'getNoBukti']);
        Route::get('serviceinheader/combo', [ServiceInHeaderController::class, 'combo']);
        Route::get('serviceinheader/grid', [ServiceInHeaderController::class, 'grid']);
        Route::get('serviceinheader/field_length', [ServiceInHeaderController::class, 'fieldLength']);
        Route::resource('serviceinheader', ServiceInHeaderController::class);
        Route::resource('serviceindetail', ServiceInDetailController::class);

        
        Route::get('serviceoutheader/combo', [ServiceOutHeaderController::class, 'combo']);
        Route::get('serviceoutheader/field_length', [ServiceOutHeaderController::class, 'fieldLength']);
        Route::resource('serviceoutheader', ServiceOutHeaderController::class);
        Route::resource('serviceoutdetail', ServiceOutDetailController::class);
        
        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('kasgantungheader/no_bukti', [KasGantungHeaderController::class, 'getNoBukti']);
        Route::get('kasgantungheader/combo', [KasGantungHeaderController::class, 'combo']);
        Route::get('kasgantungheader/grid', [KasGantungHeaderController::class, 'grid']);
        Route::get('kasgantungheader/field_length', [KasGantungHeaderController::class, 'fieldLength']);
        Route::resource('kasgantungheader', KasGantungHeaderController::class);

        Route::resource('kasgantungdetail', KasGantungDetailController::class);

        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('gajisupirheader/no_bukti', [GajiSupirHeaderController::class, 'getNoBukti']);
        Route::get('gajisupirheader/grid', [GajiSupirHeaderController::class, 'grid']);
        Route::get('gajisupirheader/field_length', [GajiSupirHeaderController::class, 'fieldLength']);
        Route::get('gajisupirheader/getTrip/{supirId}/{dari}/{sampai}', [GajiSupirHeaderController::class, 'getTrip']);
        Route::post('gajisupirheader/noEdit', [GajiSupirHeaderController::class, 'noEdit']);
        Route::get('gajisupirheader/{gajiId}/getEditTrip', [GajiSupirHeaderController::class, 'getEditTrip']);
        Route::resource('gajisupirheader', GajiSupirHeaderController::class);
        Route::resource('gajisupirdetail', GajiSupirDetailController::class);

        
        Route::get('notakreditheader/field_length', [NotaKreditHeaderController::class,'fieldLength']);
        Route::get('notakreditheader/{id}/getpelunasan', [NotaKreditHeaderController::class,'getPelunasan']);
        Route::get('notakreditheader/{id}/getnotakredit', [NotaKreditHeaderController::class,'getNotaKredit']);
        Route::post('notakreditheader/{id}/approval', [NotaKreditHeaderController::class,'approval']);
        Route::get('notakreditheader/export', [NotaKreditHeaderController::class, 'export']);
        Route::resource('notakreditheader', NotaKreditHeaderController::class);
        Route::resource('notakredit_detail', NotaKreditDetailController::class);
        
        Route::get('notadebetheader/field_length', [NotaDebetHeaderController::class,'fieldLength']);
        Route::get('notadebetheader/{id}/getpelunasan', [NotaDebetHeaderController::class,'getPelunasan']);
        Route::get('notadebetheader/{id}/getnotadebet', [NotaDebetHeaderController::class,'getNotaDebet']);
        Route::post('notadebetheader/{id}/approval', [NotaDebetHeaderController::class,'approval']);
        Route::get('notadebetheader/export', [NotaDebetHeaderController::class, 'export']);
        Route::resource('notadebetheader',NotaDebetHeaderController::class);
        Route::resource('notadebet_detail', NotaDebetDetailController::class);
        
        Route::get('rekappengeluaranheader/field_length', [RekapPengeluaranHeaderController::class,'fieldLength']);
        Route::get('rekappengeluaranheader/getpengeluaran', [RekapPengeluaranHeaderController::class,'getPengeluaran']);
        Route::get('rekappengeluaranheader/export', [RekapPengeluaranHeaderController::class, 'export']);
        Route::get('rekappengeluaranheader/{id}/getrekappengeluaran', [RekapPengeluaranHeaderController::class,'getRekapPengeluaran']);
        Route::post('rekappengeluaranheader/{id}/approval', [RekapPengeluaranHeaderController::class,'approval']);
        Route::resource('rekappengeluaranheader',RekapPengeluaranHeaderController::class);
        Route::resource('rekappengeluarandetail', RekapPengeluaranDetailController::class);
        
        Route::get('rekappenerimaanheader/field_length', [RekapPenerimaanHeaderController::class,'fieldLength']);
        Route::get('rekappenerimaanheader/getpenerimaan', [RekapPenerimaanHeaderController::class,'getPenerimaan']);
        Route::get('rekappenerimaanheader/export', [RekapPenerimaanHeaderController::class, 'export']);
        Route::get('rekappenerimaanheader/{id}/getrekappenerimaan', [RekapPenerimaanHeaderController::class,'getRekapPenerimaan']);
        Route::post('rekappenerimaanheader/{id}/approval', [RekapPenerimaanHeaderController::class,'approval']);
        Route::resource('rekappenerimaanheader',RekapPenerimaanHeaderController::class);
        Route::resource('rekappenerimaandetail', RekapPenerimaanDetailController::class);
        
        Route::get('pengembaliankasgantungheader/field_length', [PengembalianKasGantungHeaderController::class,'fieldLength']);
        Route::get('pengembaliankasgantungheader/getkasgantung', [PengembalianKasGantungHeaderController::class,'getKasGantung']);
        Route::get('pengembaliankasgantungheader/getpengembalian/{id}', [PengembalianKasGantungHeaderController::class,'getPengembalian']);
        Route::resource('pengembaliankasgantungheader', PengembalianKasGantungHeaderController::class);
        
        Route::resource('pengembaliankasgantung_detail', PengembalianKasGantungDetailController::class);

        Route::post('pengembaliankasbankheader/{id}/approval', [PengembalianKasBankHeaderController::class, 'approval'])->name('pengembaliankasbankheader.approval');
        Route::get('pengembaliankasbankheader/no_bukti', [PengembalianKasBankHeaderController::class, 'getNoBukti']);
        Route::get('pengembaliankasbankheader/field_length', [PengembalianKasBankHeaderController::class, 'fieldLength']);
        Route::get('pengembaliankasbankheader/combo', [PengembalianKasBankHeaderController::class, 'combo']);
        Route::post('pengembaliankasbankheader/{id}/approval', [PengembalianKasBankHeaderController::class,'approval']);
        Route::get('pengembaliankasbankheader/grid', [PengembalianKasBankHeaderController::class, 'grid']);
        Route::resource('pengembaliankasbankheader', PengembalianKasBankHeaderController::class);

        Route::resource('pengembaliankasbankdetail', PengembalianKasBankDetailController::class);

        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('prosesgajisupirheader/no_bukti', [ProsesGajiSupirHeaderController::class, 'getNoBukti']);
        Route::get('prosesgajisupirheader/grid', [ProsesGajiSupirHeaderController::class, 'grid']);
        Route::get('prosesgajisupirheader/field_length', [ProsesGajiSupirHeaderController::class, 'fieldLength']);
        Route::get('prosesgajisupirheader/getRic/{dari}/{sampai}', [ProsesGajiSupirHeaderController::class, 'getRic']);
        Route::post('prosesgajisupirheader/noEdit', [ProsesGajiSupirHeaderController::class, 'noEdit']);
        Route::get('prosesgajisupirheader/{id}/getEdit', [ProsesGajiSupirHeaderController::class, 'getEdit']);
        Route::resource('prosesgajisupirheader', ProsesGajiSupirHeaderController::class);
        Route::resource('prosesgajisupirdetail', ProsesGajiSupirDetailController::class);

        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::get('invoiceheader/no_bukti', [InvoiceHeaderController::class, 'getNoBukti']);
        Route::get('invoiceheader/grid', [InvoiceHeaderController::class, 'grid']);
        Route::get('invoiceheader/field_length', [InvoiceHeaderController::class, 'fieldLength']);
        Route::get('invoiceheader/comboapproval', [InvoiceHeaderController::class, 'comboapproval']);
        Route::get('invoiceheader/{id}/getEdit', [InvoiceHeaderController::class, 'getEdit']);
        Route::get('invoiceheader/getSP', [InvoiceHeaderController::class, 'getSP']);
        Route::post('invoiceheader/{id}/approval', [InvoiceHeaderController::class, 'approval'])->name('invoiceheader.approval');
        Route::post('invoiceheader/{id}/cekapproval', [InvoiceHeaderController::class, 'cekapproval'])->name('invoiceheader.cekapproval');
        Route::resource('invoiceheader', InvoiceHeaderController::class);
        Route::resource('invoicedetail', InvoiceDetailController::class);

        Route::get('suratpengantar/combo', [SuratPengantarController::class, 'combo']);
        Route::get('suratpengantar/field_length', [SuratPengantarController::class, 'fieldLength']);
        Route::post('suratpengantar/cekUpahSupir', [SuratPengantarController::class, 'cekUpahSupir']);
        Route::get('suratpengantar/{id}/getTarifOmset', [SuratPengantarController::class, 'getTarifOmset']);
        Route::get('suratpengantar/{id}/getOrderanTrucking', [SuratPengantarController::class, 'getOrderanTrucking']);
        Route::get('suratpengantar/getGaji/{dari}/{sampai}/{container}/{statuscontainer}', [SuratPengantarController::class, 'getGaji']);
        Route::resource('suratpengantar', SuratPengantarController::class);
        
        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::post('penerimaanheader/{id}/approval', [PenerimaanHeaderController::class, 'approval'])->name('penerimaanheader.approval');
        Route::get('penerimaanheader/no_bukti', [PenerimaanHeaderController::class, 'getNoBukti']);
        Route::get('penerimaanheader/combo', [PenerimaanHeaderController::class, 'combo']);
        Route::get('penerimaanheader/{id}/tarikPelunasan', [PenerimaanHeaderController::class, 'tarikPelunasan']);
        Route::get('penerimaanheader/{id}/{table}/getPelunasan', [PenerimaanHeaderController::class, 'getPelunasan']);
        Route::get('penerimaanheader/grid', [PenerimaanHeaderController::class, 'grid']);
        Route::resource('penerimaanheader', PenerimaanHeaderController::class);

        Route::resource('penerimaandetail', PenerimaanDetailController::class);

        //pengeluaran
        Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
        Route::post('pengeluaranheader/{id}/approval', [PengeluaranHeaderController::class, 'approval'])->name('pengeluaranheader.approval');
        Route::get('pengeluaranheader/no_bukti', [PengeluaranHeaderController::class, 'getNoBukti']);
        Route::get('pengeluaranheader/field_length', [PengeluaranHeaderController::class, 'fieldLength']);
        Route::get('pengeluaranheader/combo', [PengeluaranHeaderController::class, 'combo']);
        Route::get('pengeluaranheader/grid', [PengeluaranHeaderController::class, 'grid']);
        Route::resource('pengeluaranheader', PengeluaranHeaderController::class);

        Route::resource('pengeluarandetail', PengeluaranDetailController::class);
        
        Route::post('penerimaangiroheader/{id}/approval', [PenerimaanGiroHeaderController::class, 'approval'])->name('penerimaangiroheader.approval');
        Route::get('penerimaangiroheader/no_bukti', [PenerimaanGiroHeaderController::class, 'getNoBukti']);
        Route::get('penerimaangiroheader/field_length', [PenerimaanGiroHeaderController::class, 'fieldLength']);
        Route::get('penerimaangiroheader/combo', [PenerimaanGiroHeaderController::class, 'combo']);
        Route::get('penerimaangiroheader/grid', [PenerimaanGiroHeaderController::class, 'grid']);
        Route::get('penerimaangiroheader/{id}/tarikPelunasan', [PenerimaanGiroHeaderController::class, 'tarikPelunasan']);
        Route::get('penerimaangiroheader/{id}/getPelunasan', [PenerimaanGiroHeaderController::class, 'getPelunasan']);
        Route::resource('penerimaangiroheader', PenerimaanGiroHeaderController::class);

        Route::resource('penerimaangirodetail', PenerimaanGiroDetailController::class);

        
        Route::get('harilibur/field_length', [HariLiburController::class, 'fieldLength']);
        Route::resource('harilibur', HariLiburController::class);

        Route::get('jurnalumumpusatheader/grid', [JurnalUmumPusatHeaderController::class, 'grid']);
        Route::get('jurnalumumpusatheader/field_length', [JurnalUmumPusatHeaderController::class, 'fieldLength']);
        Route::resource('jurnalumumpusatheader', JurnalUmumPusatHeaderController::class);
        Route::resource('jurnalumumpusatdetail', JurnalUmumPusatDetailController::class);
        
        Route::get('reportall/report', [ReportAllController::class, 'report'])->name('reportall.report');
        Route::resource('reportall', ReportAllController::class);

        Route::get('reportneraca/report', [ReportNeracaController::class, 'report'])->name('reportneraca.report');
        Route::resource('reportneraca', ReportNeracaController::class);
        
        Route::get('pencairangiropengeluaranheader/grid', [PencairanGiroPengeluaranHeaderController::class, 'grid']);
        Route::get('pencairangiropengeluaranheader/field_length', [PencairanGiroPengeluaranHeaderController::class, 'fieldLength']);
        Route::delete('pencairangiropengeluaranheader', [PencairanGiroPengeluaranHeaderController::class, 'destroy']);
        Route::resource('pencairangiropengeluaranheader', PencairanGiroPengeluaranHeaderController::class);
        Route::resource('pencairangiropengeluarandetail', PencairanGiroPengeluaranDetailController::class);

        Route::resource('approvalnotaheader', ApprovalNotaHeaderController::class);
        Route::resource('approvalhutangbayar', ApprovalHutangBayarController::class);
        
        
        Route::post('pendapatansupirheader/{id}/cekapproval', [PendapatanSupirHeaderController::class, 'cekapproval'])->name('pendapatansupirheader.cekapproval');
        Route::resource('pendapatansupirheader', PendapatanSupirHeaderController::class)->parameters(['pendapatansupirheader' => 'pendapatanSupirHeader']);
        Route::resource('pendapatansupirdetail', PendapatanSupirDetailController::class);
        
        Route::resource('approvalpendapatansupir', ApprovalPendapatanSupirController::class);

    });

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
    Route::resource('kota', KotaController::class);

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

    Route::get('upahsupir/combo', [UpahSupirController::class, 'combo']);
    Route::get('upahsupir/field_length', [UpahSupirController::class, 'fieldLength']);
    Route::resource('upahsupir', UpahSupirController::class);

    Route::resource('upahsupirrincian', UpahSupirRincianController::class);



    //Penerimaan trucking
    // Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    // Route::get('penerimaantrucking/no_bukti', [PenerimaanTruckingHeaderController::class, 'getNoBukti']);
    // Route::get('penerimaantrucking/combo', [PenerimaanTruckingHeaderController::class, 'combo']);
    // Route::get('penerimaantrucking/grid', [PenerimaanTruckingHeaderController::class, 'grid']);
    // Route::resource('penerimaantrucking', PenerimaanTruckingHeaderController::class);

    // Route::resource('penerimaantruckingdetail', PenerimaanTruckingDetailController::class);

    


    Route::get('suratpengantar/combo', [SuratPengantarController::class, 'combo']);
    Route::get('suratpengantar/field_length', [SuratPengantarController::class, 'fieldLength']);
    Route::get('suratpengantar/get_gaji', [SuratPengantarController::class, 'getGaji']);
    Route::resource('suratpengantar', SuratPengantarController::class);

    Route::get('upahritasi/combo', [UpahRitasiController::class, 'combo']);
    
    Route::get('upahritasi/comboluarkota', [UpahRitasiController::class, 'comboluarkota']);
    Route::get('upahritasi/field_length', [UpahRitasiController::class, 'fieldLength']);
    Route::resource('upahritasi', UpahRitasiController::class);

    Route::resource('upahritasirincian', UpahRitasiRincianController::class);

    Route::get('ritasi/combo', [RitasiController::class, 'combo']);
    Route::get('ritasi/field_length', [RitasiController::class, 'fieldLength']);
    Route::resource('ritasi', RitasiController::class);

    Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    Route::post('penerimaan/{id}/approval', [PenerimaanHeaderController::class, 'approval'])->name('penerimaan.approval');
    Route::get('penerimaan/no_bukti', [PenerimaanHeaderController::class, 'getNoBukti']);
    Route::get('penerimaan/combo', [PenerimaanHeaderController::class, 'combo']);
    Route::get('penerimaan/grid', [PenerimaanHeaderController::class, 'grid']);
    Route::resource('penerimaan', PenerimaanHeaderController::class);

    Route::resource('penerimaandetail', PenerimaanDetailController::class);

    //pengeluaran
    Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    Route::post('pengeluaran/{id}/approval', [PengeluaranHeaderController::class, 'approval'])->name('pengeluaran.approval');
    Route::get('pengeluaran/no_bukti', [PengeluaranHeaderController::class, 'getNoBukti']);
    Route::get('pengeluaran/combo', [PengeluaranHeaderController::class, 'combo']);
    Route::get('pengeluaran/grid', [PengeluaranHeaderController::class, 'grid']);
    Route::resource('pengeluaran', PengeluaranHeaderController::class);

    Route::resource('pengeluarandetail', PengeluaranDetailController::class);

    //Penerimaan trucking
    // Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    // Route::post('penerimaantrucking/{id}/approval', [PenerimaanTruckingHeaderController::class, 'approval'])->name('penerimaantrucking.approval');
    // Route::get('penerimaantrucking/no_bukti', [PenerimaanTruckingHeaderController::class, 'getNoBukti']);
    // Route::get('penerimaantrucking/combo', [PenerimaanTruckingHeaderController::class, 'combo']);
    // Route::get('penerimaantrucking/grid', [PenerimaanTruckingHeaderController::class, 'grid']);
    // Route::resource('penerimaantrucking', PenerimaanTruckingHeaderController::class);

    // Route::resource('penerimaantruckingdetail', PenerimaanTruckingDetailController::class);
