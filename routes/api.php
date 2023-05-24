<?php

use App\Http\Controllers\Api\AkunPusatController;
use App\Http\Controllers\Api\AbsensiSupirDetailController;
use App\Http\Controllers\Api\AbsensiSupirHeaderController;

use App\Http\Controllers\Api\BukaAbsensiController;
use App\Http\Controllers\Api\SuratPengantarApprovalInputTripController;

use App\Http\Controllers\Api\AbsensiSupirApprovalHeaderController;
use App\Http\Controllers\Api\AbsensiSupirApprovalDetailController;

use App\Http\Controllers\Api\ApprovalTransaksiHeaderController;
use App\Http\Controllers\Api\ApprovalInvoiceHeaderController;
use App\Http\Controllers\Api\ApprovalBukaCetakController;

use App\Http\Controllers\Api\HistoryTripController;
use App\Http\Controllers\Api\ListTripController;
use App\Http\Controllers\Api\InputTripController;

use App\Http\Controllers\Api\AbsenTradoController;
use App\Http\Controllers\Api\CabangController;
use App\Http\Controllers\Api\GandenganController;
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
use App\Http\Controllers\Api\ExportLaporanKasGantungController;
use App\Http\Controllers\Api\ExportLaporanKasHarianController;
use App\Http\Controllers\Api\ExportLaporanStokController;
use App\Http\Controllers\Api\ExportPemakaianBarangController;
use App\Http\Controllers\Api\ExportPembelianBarangController;
use App\Http\Controllers\Api\ExportPengeluaranBarangController;
use App\Http\Controllers\Api\ExportRincianMingguanController;
use App\Http\Controllers\Api\ExportRincianMingguanPendapatanSupirController;
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
use App\Http\Controllers\Api\TarifRincianController;
use App\Http\Controllers\Api\PengeluaranTruckingController;
use App\Http\Controllers\Api\OrderanTruckingController;
use App\Http\Controllers\Api\JobTruckingController;
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
use App\Http\Controllers\Api\MandorAbsensiSupirController;
use App\Http\Controllers\Api\MandorTripController;
use App\Http\Controllers\Api\PenerimaanTruckingDetailController;
use App\Http\Controllers\Api\PenerimaanStokController;
use App\Http\Controllers\Api\PenerimaanStokHeaderController;
use App\Http\Controllers\Api\PenerimaanStokDetailController;
use App\Http\Controllers\Api\PengeluaranStokController;
use App\Http\Controllers\Api\PengeluaranStokDetailController;
use App\Http\Controllers\Api\PengeluaranStokDetailFifoController;
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
use App\Http\Controllers\Api\InvoiceChargeGandenganHeaderController;
use App\Http\Controllers\Api\InvoiceChargeGandenganDetailController;
use App\Http\Controllers\Api\ProsesGajiSupirHeaderController;
use App\Http\Controllers\Api\ProsesGajiSupirDetailController;
use App\Http\Controllers\Api\HariLiburController;
use App\Http\Controllers\Api\PenerimaanGiroDetailController;
use App\Http\Controllers\Api\PenerimaanGiroHeaderController;
use App\Http\Controllers\Api\JurnalUmumPusatDetailController;
use App\Http\Controllers\Api\JurnalUmumPusatHeaderController;
use App\Http\Controllers\Api\KartuStokController;
use App\Http\Controllers\Api\HistoriPenerimaanStokController;
use App\Http\Controllers\Api\HistoriPengeluaranStokController;
use App\Http\Controllers\Api\KaryawanController;
use App\Http\Controllers\Api\LaporanBanGudangSementaraController;
use App\Http\Controllers\Api\LaporanBukuBesarController;
use App\Http\Controllers\Api\LaporanDepositoSupirController;
use App\Http\Controllers\Api\LaporanEstimasiKasGantungController;
use App\Http\Controllers\Api\LaporanHistoryPinjamanController;
use App\Http\Controllers\Api\LaporanHutangBBMController;
use App\Http\Controllers\Api\LaporanKartuHutangPrediksiController;
use App\Http\Controllers\Api\LaporanKasBankController;
use App\Http\Controllers\Api\LaporanKasGantungController;
use App\Http\Controllers\Api\LaporanKeteranganPinjamanSupirController;
use App\Http\Controllers\Api\LaporanKlaimPJTSupirController;
use App\Http\Controllers\Api\LaporanKartuPiutangPerPelangganController;
use App\Http\Controllers\Api\LaporanKartuPiutangPerPlgDetailController;
use App\Http\Controllers\Api\LaporanPemotonganPinjamanDepoController;
use App\Http\Controllers\Api\LaporanPemotonganPinjamanDepositoController;
use App\Http\Controllers\Api\LaporanPemotonganPinjamanPerEBSController;
use App\Http\Controllers\Api\LaporanPinjamanSupirController;
use App\Http\Controllers\Api\LaporanPinjamanSupirKaryawanController;
use App\Http\Controllers\Api\LaporanRekapSumbanganController;
use App\Http\Controllers\Api\LaporanRitasiGandenganController;
use App\Http\Controllers\Api\LaporanRitasiTradoController;
use App\Http\Controllers\Api\LaporanSupirLebihDariTradoController;
use App\Http\Controllers\Api\LaporanTripGandenganDetailController;
use App\Http\Controllers\Api\LaporanTripTradoController;
use App\Http\Controllers\Api\LaporanUangJalanController;
use App\Http\Controllers\Api\OrderanEmklController;
use App\Http\Controllers\Api\PemutihanSupirController;
use App\Http\Controllers\Api\PemutihanSupirDetailController;
use App\Http\Controllers\Api\ReportAllController;
use App\Http\Controllers\Api\PencairanGiroPengeluaranDetailController;
use App\Http\Controllers\Api\PencairanGiroPengeluaranHeaderController;
use App\Http\Controllers\Api\PendapatanSupirDetailController;
use App\Http\Controllers\Api\PendapatanSupirHeaderController;
use App\Http\Controllers\Api\PindahBukuController;
use App\Http\Controllers\Api\ProsesUangJalanSupirDetailController;
use App\Http\Controllers\Api\ProsesUangJalanSupirHeaderController;
use App\Http\Controllers\Api\ReportNeracaController;
use App\Http\Controllers\Api\StokPersediaanController;
use App\Http\Controllers\Api\TutupBukuController;
use App\Http\Controllers\Api\LaporanKartuHutangPerVendorController;
use App\Http\Controllers\Api\LaporanMutasiKasBankController;
use App\Http\Controllers\Api\LaporanKartuStokController;
use App\Http\Controllers\Api\LaporanArusKasController;
use App\Models\LaporanArusKas;
use App\Http\Controllers\Api\LaporanOrderPembelianController;
use App\Http\Controllers\Api\LapKartuHutangPerVendorDetailController;
use App\Http\Controllers\Api\LaporanWarkatBelumCairController;
use App\Http\Controllers\Api\LaporanPiutangGiroController;
use App\Http\Controllers\Api\LaporanLabaRugiController;
use App\Http\Controllers\Api\LaporanNeracaController;
use App\Http\Controllers\Api\LaporanPenyesuaianBarangController;
use App\Http\Controllers\Api\LaporanPemakaianBanController;
use App\Http\Controllers\Api\LaporanTransaksiHarianController;

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
Route::get('supir/pdf/{field}/{filename}', [SupirController::class, 'getPdf']);
Route::get('trado/image/{field}/{filename}/{type}', [TradoController::class, 'getImage']);
Route::get('stok/{filename}/{type}', [StokController::class, 'getImage']);
Route::get('upahsupir/{filename}/{type}', [UpahSupirController::class, 'getImage']);

route::middleware(['auth:api'])->group(function () {

    Route::get('gudang/combo', [GudangController::class, 'combo']);
    Route::get('gudang/field_length', [GudangController::class, 'fieldLength']);
    Route::get('gudang/default', [GudangController::class, 'default']);
    Route::post('gudang/{id}/cekValidasi', [GudangController::class, 'cekValidasi'])->name('gudang.cekValidasi');
    Route::resource('gudang', GudangController::class);

    Route::get('kategori/combo', [KategoriController::class, 'combo']);
    Route::get('kategori/field_length', [KategoriController::class, 'fieldLength']);
    Route::get('kategori/default', [KategoriController::class, 'default']);
    Route::post('kategori/{id}/cekValidasi', [KategoriController::class, 'cekValidasi'])->name('kategori.cekValidasi');
    Route::resource('kategori', KategoriController::class);

    Route::get('kelompok/combo', [KelompokController::class, 'combo']);
    Route::get('kelompok/field_length', [KelompokController::class, 'fieldLength']);
    Route::get('kelompok/default', [KelompokController::class, 'default']);
    Route::post('kelompok/{id}/cekValidasi', [KelompokController::class, 'cekValidasi'])->name('kelompok.cekValidasi');
    Route::resource('kelompok', KelompokController::class);

    Route::get('kerusakan/combo', [KerusakanController::class, 'combo']);
    Route::get('kerusakan/field_length', [KerusakanController::class, 'fieldLength']);
    Route::get('kerusakan/default', [KerusakanController::class, 'default']);
    Route::post('kerusakan/{id}/cekValidasi', [KerusakanController::class, 'cekValidasi'])->name('kerusakan.cekValidasi');
    Route::resource('kerusakan', KerusakanController::class);


    Route::get('mandor/combo', [MandorController::class, 'combo']);
    Route::get('mandor/field_length', [MandorController::class, 'fieldLength']);
    Route::get('mandor/default', [MandorController::class, 'default']);
    Route::post('mandor/{id}/cekValidasi', [MandorController::class, 'cekValidasi'])->name('mandor.cekValidasi');
    Route::resource('mandor', MandorController::class);

    Route::get('merk/combo', [MerkController::class, 'combo']);
    Route::get('merk/field_length', [MerkController::class, 'fieldLength']);
    Route::get('merk/default', [MerkController::class, 'default']);
    Route::post('merk/{id}/cekValidasi', [MerkController::class, 'cekValidasi'])->name('merk.cekValidasi');
    Route::resource('merk', MerkController::class);

    Route::get('satuan/combo', [SatuanController::class, 'combo']);
    Route::get('satuan/field_length', [SatuanController::class, 'fieldLength']);
    Route::get('satuan/default', [SatuanController::class, 'default']);
    Route::resource('satuan', SatuanController::class);

    Route::get('zona/combo', [ZonaController::class, 'combo']);
    Route::get('zona/field_length', [ZonaController::class, 'fieldLength']);
    Route::get('zona/default', [ZonaController::class, 'default']);
    Route::post('zona/{id}/cekValidasi', [ZonaController::class, 'cekValidasi'])->name('zona.cekValidasi');
    Route::resource('zona', ZonaController::class);


    Route::get('tarif/combo', [TarifController::class, 'combo']);
    Route::get('tarif/field_length', [TarifController::class, 'fieldLength']);
    Route::get('tarif/default', [TarifController::class, 'default']);
    Route::get('tarif/listpivot', [TarifController::class, 'listpivot']);
    Route::post('tarif/import', [TarifController::class, 'import']);
    Route::post('tarif/{id}/cekValidasi', [TarifController::class, 'cekValidasi'])->name('tarif.cekValidasi');
    Route::resource('tarif', TarifController::class);

    Route::get('tarifrincian/setuprow', [TarifRincianController::class, 'setUpRow']);
    Route::get('tarifrincian/get', [TarifRincianController::class, 'get']);
    Route::get('tarifrincian/setuprowshow/{id}', [TarifRincianController::class, 'setUpRowExcept']);
    Route::resource('tarifrincian', TarifRincianController::class);


    Route::get('orderantrucking/combo', [OrderanTruckingController::class, 'combo']);
    Route::get('orderantrucking/field_length', [OrderanTruckingController::class, 'fieldLength']);
    Route::get('orderantrucking/default', [OrderanTruckingController::class, 'default']);
    Route::post('orderantrucking/{id}/cekValidasi', [OrderanTruckingController::class, 'cekValidasi'])->name('orderantrucking.cekValidasi');
    Route::get('orderantrucking/{id}/getagentas', [OrderanTruckingController::class, 'getagentas']);
    Route::get('orderantrucking/{id}/getcont', [OrderanTruckingController::class, 'getcont']);
    Route::get('orderantrucking/getorderantrip', [OrderanTruckingController::class, 'getOrderanTrip']);
    Route::resource('orderantrucking', OrderanTruckingController::class);

    Route::resource('jobtrucking', JobTruckingController::class);


    Route::get('prosesabsensisupir/combo', [ProsesAbsensiSupirController::class, 'combo']);
    Route::get('prosesabsensisupir/field_length', [ProsesAbsensiSupirController::class, 'fieldLength']);
    Route::resource('prosesabsensisupir', ProsesAbsensiSupirController::class);

    Route::get('mandorabsensisupir/{tradoId}/cekvalidasi', [MandorAbsensiSupirController::class, 'cekValidasi']);
    Route::get('mandorabsensisupir/{tradoId}/cekvalidasiadd', [MandorAbsensiSupirController::class, 'cekValidasiAdd']);
    Route::post('mandorabsensisupir/{id}/update', [MandorAbsensiSupirController::class, 'update']);
    Route::post('mandorabsensisupir/{id}/delete', [MandorAbsensiSupirController::class, 'destroy']);
    Route::resource('mandorabsensisupir', MandorAbsensiSupirController::class);

    Route::get('historytrip', [HistoryTripController::class, 'index']);
    Route::get('listtrip', [ListTripController::class, 'index']);
    Route::post('inputtrip', [InputTripController::class, 'store']);


    Route::get('mekanik/combo', [MekanikController::class, 'combo']);
    Route::get('mekanik/field_length', [MekanikController::class, 'fieldLength']);
    Route::get('mekanik/default', [MekanikController::class, 'default']);
    Route::post('mekanik/{id}/cekValidasi', [MekanikController::class, 'cekValidasi'])->name('mekanik.cekValidasi');
    Route::resource('mekanik', MekanikController::class);

    Route::get('upahsupir/combo', [UpahSupirController::class, 'combo']);
    Route::get('upahsupir/field_length', [UpahSupirController::class, 'fieldLength']);
    Route::get('upahsupir/default', [UpahSupirController::class, 'default']);
    Route::resource('upahsupir', UpahSupirController::class);

    Route::get('upahsupirrincian/setuprow', [UpahSupirRincianController::class, 'setUpRow']);
    Route::get('upahsupirrincian/setuprowshow/{id}', [UpahSupirRincianController::class, 'setUpRowExcept']);
    Route::resource('upahsupirrincian', UpahSupirRincianController::class);

    Route::get('parameter/export', [ParameterController::class, 'export']);
    Route::get('parameter/detail', [ParameterController::class, 'detail']);
    Route::get('parameter/field_length', [ParameterController::class, 'fieldLength']);
    Route::get('parameter/combo', [ParameterController::class, 'combo']);
    Route::get('parameter/comboapproval', [ParameterController::class, 'comboapproval']);
    Route::get('parameter/combolist', [ParameterController::class, 'combolist']);
    Route::get('parameter/getcoa', [ParameterController::class, 'getcoa']);
    Route::resource('parameter', ParameterController::class);

    Route::get('absensisupirheader/{id}/cekabsensi', [AbsensiSupirHeaderController::class, 'cekabsensi'])->name('absensi.cekabsensi');
    Route::get('absensisupirheader/{id}/detail', [AbsensiSupirHeaderController::class, 'detail'])->name('absensi.detail');
    Route::post('absensisupirheader/{id}/cekValidasiAksi', [AbsensiSupirHeaderController::class, 'cekValidasiAksi'])->name('absensisupirheader.cekValidasiAksi');
    Route::get('absensisupirheader/no_bukti', [AbsensiSupirHeaderController::class, 'getNoBukti']);
    Route::get('absensisupirheader/running_number', [AbsensiSupirHeaderController::class, 'getRunningNumber']);
    Route::get('absensisupirheader/grid', [AbsensiSupirHeaderController::class, 'grid']);
    Route::get('absensisupirheader/field_length', [AbsensiSupirHeaderController::class, 'fieldLength']);
    Route::get('absensisupirheader/default', [AbsensiSupirHeaderController::class, 'default']);
    Route::post('absensisupirheader/{id}/cekvalidasi', [AbsensiSupirHeaderController::class, 'cekvalidasi'])->name('absensisupirheader.cekvalidasi');
    Route::post('absensisupirheader/{id}/approval', [AbsensiSupirHeaderController::class, 'approval'])->name('absensisupirheader.approval');
    Route::post('absensisupirheader/{id}/approvalEditAbsensi', [AbsensiSupirHeaderController::class, 'approvalEditAbsensi']);

    Route::apiResource('absensisupirheader', AbsensiSupirHeaderController::class)->parameter('absensisupirheader', 'absensiSupirHeader');

    Route::get('absensisupirdetail/get', [AbsensiSupirDetailController::class, 'getDetailAbsensi']);
    Route::resource('absensisupirdetail', AbsensiSupirDetailController::class);
    Route::resource('bukaabsensi', BukaAbsensiController::class);

    Route::get('suratpengantarapprovalinputtrip/cektanggal', [SuratPengantarApprovalInputTripController::class, 'isTanggalAvaillable']);
    Route::resource('suratpengantarapprovalinputtrip', SuratPengantarApprovalInputTripController::class);

    Route::get('approvaltransaksiheader/combo', [ApprovalTransaksiHeaderController::class, 'combo']);
    Route::get('approvaltransaksiheader/default', [ApprovalTransaksiHeaderController::class, 'default']);
    Route::apiResource('approvaltransaksiheader', ApprovalTransaksiHeaderController::class);

    Route::get('approvalinvoiceheader/combo', [ApprovalInvoiceHeaderController::class, 'combo']);
    Route::get('approvalinvoiceheader/default', [ApprovalInvoiceHeaderController::class, 'default']);
    Route::resource('approvalinvoiceheader', ApprovalInvoiceHeaderController::class);

    Route::get('approvalbukacetak/combo', [ApprovalBukaCetakController::class, 'combo']);
    Route::resource('approvalbukacetak', ApprovalBukaCetakController::class);

    Route::get('absensisupirapprovalheader/running_number', [AbsensiSupirApprovalHeaderController::class, 'getRunningNumber']);
    Route::get('absensisupirapprovalheader/grid', [AbsensiSupirApprovalHeaderController::class, 'grid']);
    Route::get('absensisupirapprovalheader/field_length', [AbsensiSupirApprovalHeaderController::class, 'fieldLength']);
    Route::get('absensisupirapprovalheader/export', [AbsensiSupirApprovalHeaderController::class, 'export']);
    Route::get('absensisupirapprovalheader/{absensi}/getabsensi', [AbsensiSupirApprovalHeaderController::class, 'getAbsensi']);
    Route::get('absensisupirapprovalheader/{absensi}/getapproval', [AbsensiSupirApprovalHeaderController::class, 'getApproval']);
    Route::post('absensisupirapprovalheader/{id}/cekvalidasi', [AbsensiSupirApprovalHeaderController::class, 'cekvalidasi'])->name('absensisupirapprovalheader.cekvalidasi');
    Route::post('absensisupirapprovalheader/{id}/approval', [AbsensiSupirApprovalHeaderController::class, 'approval']);
    Route::apiResource('absensisupirapprovalheader', AbsensiSupirApprovalHeaderController::class);
    Route::apiResource('absensisupirapprovaldetail', AbsensiSupirApprovalDetailController::class);

    Route::get('agen/field_length', [AgenController::class, 'fieldLength']);
    Route::get('agen/export', [AgenController::class, 'export'])->name('export');
    Route::get('agen/default', [AgenController::class, 'default']);
    Route::post('agen/{agen}/approval', [AgenController::class, 'approval'])->name('agen.approval');
    Route::post('agen/{id}/cekValidasi', [AgenController::class, 'cekValidasi'])->name('agen.cekValidasi');
    Route::resource('agen', AgenController::class);

    Route::get('cabang/field_length', [CabangController::class, 'fieldLength']);
    Route::get('cabang/combostatus', [CabangController::class, 'combostatus']);
    Route::get('cabang/default', [CabangController::class, 'default']);
    Route::get('cabang/report', [CabangController::class, 'report']);
    Route::get('cabang/export', [CabangController::class, 'export']);
    Route::get('cabang/getPosition2', [CabangController::class, 'getPosition2']);
    Route::resource('cabang', CabangController::class);

    Route::get('gandengan/field_length', [GandenganController::class, 'fieldLength']);
    Route::get('gandengan/combostatus', [GandenganController::class, 'combostatus']);
    Route::get('gandengan/getPosition2', [GandenganController::class, 'getPosition2']);
    Route::get('gandengan/default', [GandenganController::class, 'default']);
    Route::get('gandengan/report', [GandenganController::class, 'report']);
    Route::get('gandengan/export', [GandenganController::class, 'export']);
    Route::post('gandengan/{id}/cekValidasi', [GandenganController::class, 'cekValidasi'])->name('gandengan.cekValidasi');
    Route::resource('gandengan', GandenganController::class);


    Route::get('acos/field_length', [AcosController::class, 'fieldLength']);
    Route::resource('acos', AcosController::class);

    Route::get('kota/combo', [KotaController::class, 'combo']);
    Route::get('kota/field_length', [KotaController::class, 'fieldLength']);
    Route::get('kota/default', [KotaController::class, 'default']);
    Route::post('kota/{id}/cekValidasi', [KotaController::class, 'cekValidasi'])->name('kota.cekValidasi');
    Route::resource('kota', KotaController::class);

    Route::get('logtrail/detail', [LogTrailController::class, 'detail']);
    Route::get('logtrail/header', [LogTrailController::class, 'header']);
    Route::resource('logtrail', LogTrailController::class);

    Route::get('trado/combo', [TradoController::class, 'combo']);
    Route::get('trado/field_length', [TradoController::class, 'fieldLength']);
    Route::post('trado/upload_image/{id}', [TradoController::class, 'uploadImage']);
    Route::get('trado/default', [TradoController::class, 'default']);
    Route::post('trado/{id}/cekValidasi', [TradoController::class, 'cekValidasi'])->name('trado.cekValidasi');
    Route::resource('trado', TradoController::class);

    Route::get('absentrado/field_length', [AbsenTradoController::class, 'fieldLength']);
    Route::get('absentrado/default', [AbsenTradoController::class, 'default']);
    Route::post('absentrado/{id}/cekValidasi', [AbsenTradoController::class, 'cekValidasi'])->name('absentrado.cekValidasi');
    Route::resource('absentrado', AbsenTradoController::class);
    Route::get('absentrado/detail', [AbsenTradoController::class, 'detail']);

    Route::get('container/field_length', [ContainerController::class, 'fieldLength']);
    Route::get('container/combostatus', [ContainerController::class, 'combostatus']);
    Route::get('container/getPosition2', [ContainerController::class, 'getPosition2']);
    Route::get('container/default', [ContainerController::class, 'default']);
    Route::post('container/{id}/cekValidasi', [ContainerController::class, 'cekValidasi'])->name('container.cekValidasi');
    Route::resource('container', ContainerController::class);

    Route::get('bank/combo', [BankController::class, 'combo']);
    Route::get('bank/field_length', [BankController::class, 'fieldLength']);
    Route::get('bank/default', [BankController::class, 'default']);
    Route::post('bank/{id}/cekValidasi', [BankController::class, 'cekValidasi'])->name('bank.cekValidasi');
    Route::resource('bank', BankController::class);

    Route::get('alatbayar/combo', [AlatBayarController::class, 'combo']);
    Route::get('alatbayar/field_length', [AlatBayarController::class, 'fieldLength']);
    Route::get('alatbayar/default', [AlatBayarController::class, 'default']);
    Route::post('alatbayar/{id}/cekValidasi', [AlatBayarController::class, 'cekValidasi'])->name('alatbayar.cekValidasi');
    Route::resource('alatbayar', AlatBayarController::class);

    Route::get('bankpelanggan/combo', [BankPelangganController::class, 'combo']);
    Route::get('bankpelanggan/field_length', [BankPelangganController::class, 'fieldLength']);
    Route::get('bankpelanggan/default', [BankPelangganController::class, 'default']);
    Route::post('bankpelanggan/{id}/cekValidasi', [BankPelangganController::class, 'cekValidasi'])->name('bankpelanggan.cekValidasi');
    Route::resource('bankpelanggan', BankPelangganController::class);

    Route::get('jenisemkl/combo', [JenisEmklController::class, 'combo']);
    Route::get('jenisemkl/field_length', [JenisEmklController::class, 'fieldLength']);
    Route::get('jenisemkl/default', [JenisEmklController::class, 'default']);
    Route::post('jenisemkl/{id}/cekValidasi', [JenisEmklController::class, 'cekValidasi'])->name('jenisemkl.cekValidasi');
    Route::resource('jenisemkl', JenisEmklController::class);

    Route::get('jenisorder/combo', [JenisOrderController::class, 'combo']);
    Route::get('jenisorder/field_length', [JenisOrderController::class, 'fieldLength']);
    Route::get('jenisorder/default', [JenisOrderController::class, 'default']);
    Route::post('jenisorder/{id}/cekValidasi', [JenisOrderController::class, 'cekValidasi'])->name('jenisorder.cekValidasi');
    Route::resource('jenisorder', JenisOrderController::class);

    Route::get('jenistrado/combo', [JenisTradoController::class, 'combo']);
    Route::get('jenistrado/field_length', [JenisTradoController::class, 'fieldLength']);
    Route::get('jenistrado/default', [JenisTradoController::class, 'default']);
    Route::post('jenistrado/{id}/cekValidasi', [JenisTradoController::class, 'cekValidasi'])->name('jenistrado.cekValidasi');
    Route::resource('jenistrado', JenisTradoController::class);

    Route::get('akunpusat/field_length', [AkunPusatController::class, 'fieldLength']);
    Route::get('akunpusat/default', [AkunPusatController::class, 'default']);
    Route::resource('akunpusat', AkunPusatController::class)->parameters(['akunpusat' => 'akunPusat']);

    Route::get('error/field_length', [ErrorController::class, 'fieldLength']);
    Route::get('error/geterror', [ErrorController::class, 'geterror']);
    Route::get('error/export', [ErrorController::class, 'export'])->name('error.export');
    Route::resource('error', ErrorController::class);

    Route::get('role/getroleid', [RoleController::class, 'getroleid']);
    Route::get('role/field_length', [RoleController::class, 'fieldLength']);
    Route::get('role/export', [RoleController::class, 'export'])->name('role.export');
    Route::get('role/{role}/acl', [UserRoleController::class, 'index']);
    Route::post('role/{role}/acl', [UserRoleController::class, 'store']);
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
    Route::get('user/default', [UserController::class, 'default']);
    Route::get('user/{user}/role', [RoleController::class, 'index']);
    Route::post('user/{user}/role', [UserController::class, 'storeRoles']);
    Route::get('user/{user}/acl', [UserAclController::class, 'index']);
    Route::post('user/{user}/acl', [UserAclController::class, 'store']);
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

    Route::get('logtrail/detail', [LogTrailController::class, 'detail']);
    Route::get('logtrail/header', [LogTrailController::class, 'header']);
    Route::resource('logtrail', LogTrailController::class);

    Route::get('trado/combo', [TradoController::class, 'combo']);
    Route::get('trado/field_length', [TradoController::class, 'fieldLength']);
    Route::get('trado/getImage/{id}/{field}', [TradoController::class, 'getImage']);
    Route::post('trado/upload_image/{id}', [TradoController::class, 'uploadImage']);
    Route::resource('trado', TradoController::class);


    Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');

    Route::get('container/field_length', [ContainerController::class, 'fieldLength']);
    Route::get('container/combostatus', [ContainerController::class, 'combostatus']);
    Route::get('container/getPosition2', [ContainerController::class, 'getPosition2']);
    Route::resource('container', ContainerController::class);

    Route::get('supir/combo', [SupirController::class, 'combo']);
    Route::get('supir/field_length', [SupirController::class, 'fieldLength']);
    Route::get('supir/getImage/{id}/{field}', [SupirController::class, 'getImage']);
    Route::post('supir/upload_image/{id}', [SupirController::class, 'uploadImage']);
    Route::get('supir/default', [SupirController::class, 'default']);
    Route::post('supir/{id}/approvalblacklist', [SupirController::class, 'approvalBlackListSupir']);
    Route::post('supir/{id}/approvalluarkota', [SupirController::class, 'approvalSupirLuarKota']);
    Route::post('supir/{id}/approvalresign', [SupirController::class, 'approvalSupirResign']);
    Route::post('supir/{id}/cekValidasi', [SupirController::class, 'cekValidasi'])->name('supir.cekValidasi');
    Route::resource('supir', SupirController::class);

    Route::get('subkelompok/export', [SubKelompokController::class, 'export']);
    Route::get('subkelompok/field_length', [SubKelompokController::class, 'fieldLength']);
    Route::get('subkelompok/default', [SubKelompokController::class, 'default']);
    Route::post('subkelompok/{id}/cekValidasi', [SubKelompokController::class, 'cekValidasi'])->name('subkelompok.cekValidasi');
    Route::resource('subkelompok', SubKelompokController::class)->parameters(['subkelompok' => 'subKelompok']);

    Route::get('supplier/export', [SupplierController::class, 'export']);
    Route::get('supplier/field_length', [SupplierController::class, 'fieldLength']);
    Route::get('supplier/default', [SupplierController::class, 'default']);
    Route::post('supplier/{id}/cekValidasi', [SupplierController::class, 'cekValidasi'])->name('supplier.cekValidasi');
    Route::resource('supplier', SupplierController::class);

    Route::get('stok/default', [StokController::class, 'default']);
    Route::post('stok/{id}/cekValidasi', [StokController::class, 'cekValidasi'])->name('stok.cekValidasi');
    Route::apiResource('stok', StokController::class);


    Route::get('penerima/export', [PenerimaController::class, 'export']);
    Route::get('penerima/field_length', [PenerimaController::class, 'fieldLength']);
    Route::get('penerima/default', [PenerimaController::class, 'default']);
    Route::post('penerima/{id}/cekValidasi', [PenerimaController::class, 'cekValidasi'])->name('penerima.cekValidasi');
    Route::resource('penerima', PenerimaController::class);

    Route::get('pelanggan/export', [PelangganController::class, 'export']);
    Route::get('pelanggan/field_length', [PelangganController::class, 'fieldLength']);
    Route::get('pelanggan/default', [PelangganController::class, 'default']);
    Route::post('pelanggan/{id}/cekValidasi', [PelangganController::class, 'cekValidasi'])->name('pelanggan.cekValidasi');
    Route::get('pelanggan/combostatus', [PelangganController::class, 'combostatus']);

    Route::resource('pelanggan', PelangganController::class);

    Route::get('statuscontainer/export', [StatusContainerController::class, 'export']);
    Route::get('statuscontainer/field_length', [StatusContainerController::class, 'fieldLength']);
    Route::get('statuscontainer/default', [StatusContainerController::class, 'default']);
    Route::post('statuscontainer/{id}/cekValidasi', [StatusContainerController::class, 'cekValidasi'])->name('statuscontainer.cekValidasi');
    Route::resource('statuscontainer', StatusContainerController::class)->parameters(['statuscontainer' => 'statusContainer']);

    Route::get('penerimaantrucking/export', [PenerimaanTruckingController::class, 'export']);
    Route::get('penerimaantrucking/field_length', [PenerimaanTruckingController::class, 'fieldLength']);
    Route::post('penerimaantrucking/{id}/cekValidasi', [PenerimaanTruckingController::class, 'cekValidasi'])->name('penerimaantrucking.cekValidasi');
    Route::resource('penerimaantrucking', PenerimaanTruckingController::class)->parameters(['penerimaantrucking' => 'penerimaanTrucking']);

    Route::get('pengeluarantrucking/export', [PengeluaranTruckingController::class, 'export']);
    Route::get('pengeluarantrucking/field_length', [PengeluaranTruckingController::class, 'fieldLength']);
    Route::post('pengeluarantrucking/{id}/cekValidasi', [PengeluaranTruckingController::class, 'cekValidasi'])->name('pengeluarantrucking.cekValidasi');
    Route::resource('pengeluarantrucking', PengeluaranTruckingController::class)->parameters(['pengeluarantrucking' => 'pengeluaranTrucking']);


    Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    Route::get('jurnalumumheader/no_bukti', [JurnalUmumHeaderController::class, 'getNoBukti']);
    Route::post('jurnalumumheader/{id}/approval', [JurnalUmumHeaderController::class, 'approval'])->name('jurnalumumheader.approval');
    Route::get('jurnalumumheader/combo', [JurnalUmumHeaderController::class, 'combo']);
    Route::post('jurnalumumheader/{id}/cekapproval', [JurnalUmumHeaderController::class, 'cekapproval'])->name('jurnalumumheader.cekapproval');
    Route::get('jurnalumumheader/grid', [JurnalUmumHeaderController::class, 'grid']);
    Route::post('jurnalumumheader/approval', [JurnalUmumHeaderController::class, 'approval']);
    Route::post('jurnalumumheader/copy', [JurnalUmumHeaderController::class, 'copy']);
    Route::get('jurnalumumheader/field_length', [JurnalUmumHeaderController::class, 'fieldLength']);
    Route::resource('jurnalumumheader', JurnalUmumHeaderController::class);
    Route::get('jurnalumumdetail/jurnal', [JurnalUmumDetailController::class, 'jurnal']);
    Route::resource('jurnalumumdetail', JurnalUmumDetailController::class);

    Route::get('penerimaantruckingheader/{id}/printreport', [PenerimaanTruckingHeaderController::class, 'printReport']);
    Route::post('penerimaantruckingheader/{id}/cekValidasiAksi', [PenerimaanTruckingHeaderController::class, 'cekValidasiAksi'])->name('penerimaantruckingheader.cekValidasiAksi');
    Route::post('penerimaantruckingheader/{id}/cekvalidasi', [PenerimaanTruckingHeaderController::class, 'cekvalidasi'])->name('penerimaantruckingheader.cekvalidasi');
    Route::get('penerimaantruckingheader/{id}/{aksi}/getpengembalianpinjaman', [PenerimaanTruckingHeaderController::class, 'getPengembalianPinjaman'])->name('pengeluarantruckingheader.getPengembalianPinjaman');
    Route::get('penerimaantruckingheader/no_bukti', [PenerimaanTruckingHeaderController::class, 'getNoBukti']);
    Route::get('penerimaantruckingheader/{supirId}/getpinjaman', [PenerimaanTruckingHeaderController::class, 'getPinjaman']);
    Route::get('penerimaantruckingheader/combo', [PenerimaanTruckingHeaderController::class, 'combo']);
    Route::get('penerimaantruckingheader/grid', [PenerimaanTruckingHeaderController::class, 'grid']);
    Route::get('penerimaantruckingheader/field_length', [PenerimaanTruckingHeaderController::class, 'fieldLength']);
    Route::resource('penerimaantruckingheader', PenerimaanTruckingHeaderController::class);
    Route::resource('penerimaantruckingdetail', PenerimaanTruckingDetailController::class);

    Route::get('pengeluarantruckingheader/getinvoice', [PengeluaranTruckingHeaderController::class, 'getInvoice']);
    Route::get('pengeluarantruckingheader/{id}/geteditinvoice', [PengeluaranTruckingHeaderController::class, 'getEditInvoice']);
    Route::get('pengeluarantruckingheader/{id}/printreport', [PengeluaranTruckingHeaderController::class, 'printReport']);
    Route::post('pengeluarantruckingheader/{id}/cekValidasiAksi', [PengeluaranTruckingHeaderController::class, 'cekValidasiAksi'])->name('pengeluarantruckingheader.cekValidasiAksi');
    Route::post('pengeluarantruckingheader/{id}/cekvalidasi', [PengeluaranTruckingHeaderController::class, 'cekvalidasi'])->name('pengeluarantruckingheader.cekvalidasi');
    Route::get('pengeluarantruckingheader/getdeposito', [PengeluaranTruckingHeaderController::class, 'getdeposito'])->name('pengeluarantruckingheader.getdeposito');
    Route::get('pengeluarantruckingheader/{id}/{aksi}/gettarikdeposito', [PengeluaranTruckingHeaderController::class, 'getTarikDeposito'])->name('pengeluarantruckingheader.gettarikdeposito');
    Route::get('pengeluarantruckingheader/getpelunasan', [PengeluaranTruckingHeaderController::class, 'getpelunasan'])->name('pengeluarantruckingheader.getpelunasan');
    Route::get('pengeluarantruckingheader/{id}/{aksi}/geteditpelunasan', [PengeluaranTruckingHeaderController::class, 'getEditPelunasan'])->name('pengeluarantruckingheader.geteditpelunasan');
    Route::get('pengeluarantruckingheader/no_bukti', [PengeluaranTruckingHeaderController::class, 'getNoBukti']);
    Route::get('pengeluarantruckingheader/combo', [PengeluaranTruckingHeaderController::class, 'combo']);
    Route::get('pengeluarantruckingheader/grid', [PengeluaranTruckingHeaderController::class, 'grid']);
    Route::get('pengeluarantruckingheader/field_length', [PengeluaranTruckingHeaderController::class, 'fieldLength']);
    Route::resource('pengeluarantruckingheader', PengeluaranTruckingHeaderController::class);
    Route::resource('pengeluarantruckingdetail', PengeluaranTruckingDetailController::class);

    Route::get('penerimaanstok/field_length', [PenerimaanStokController::class, 'fieldLength']);
    Route::get('penerimaanstok/export', [PenerimaanStokController::class, 'export']);
    Route::get('penerimaanstok/default', [PenerimaanStokController::class, 'default']);
    Route::post('penerimaanstok/{id}/cekValidasi', [PenerimaanStokController::class, 'cekValidasi'])->name('penerimaanstok.cekValidasi');
    Route::apiResource('penerimaanstok', PenerimaanStokController::class);

    Route::get('penerimaanstokheader/field_length', [PenerimaanStokHeaderController::class, 'fieldLength']);
    Route::get('penerimaanstokheader/{id}/printreport', [PenerimaanStokHeaderController::class, 'printReport']);
    Route::post('penerimaanstokheader/{id}/cekvalidasi', [PenerimaanStokHeaderController::class, 'cekValidasi'])->name('penerimaanstokheader.cekValidasi');
    Route::apiResource('penerimaanstokheader', PenerimaanStokHeaderController::class);
    Route::get('penerimaanstokdetail/hutang', [PenerimaanStokDetailController::class, 'hutang']);
    Route::apiResource('penerimaanstokdetail', PenerimaanStokDetailController::class);

    Route::get('pengeluaranstok/field_length', [PengeluaranStokController::class, 'fieldLength']);
    // Route::get('pengeluaranstok/export', [PengeluaranStokController::class,'export']);
    Route::get('pengeluaranstok/default', [PengeluaranStokController::class, 'default']);
    Route::post('pengeluaranstok/{id}/cekValidasi', [PengeluaranStokController::class, 'cekValidasi'])->name('pengeluaranstok.cekValidasi');
    Route::apiResource('pengeluaranstok', PengeluaranStokController::class);

    Route::get('pengeluaranstokheader/{id}/printreport', [PengeluaranStokHeaderController::class, 'printReport']);
    Route::post('pengeluaranstokheader/{id}/cekvalidasi', [PengeluaranStokHeaderController::class, 'cekValidasi'])->name('pengeluaranstokheader.cekValidasi');
    Route::apiResource('pengeluaranstokheader', PengeluaranStokHeaderController::class);
    Route::apiResource('pengeluaranstokdetail', PengeluaranStokDetailController::class);


    Route::get('pengeluaranstok/field_length', [PengeluaranStokController::class, 'fieldLength']);
    // Route::get('pengeluaranstok/export', [PengeluaranStokController::class,'export']);
    Route::post('invoiceextraheader/{id}/approval', [InvoiceExtraHeaderController::class, 'approval']);
    Route::get('invoiceextraheader/{id}/printreport', [InvoiceExtraHeaderController::class, 'printReport']);
    Route::post('invoiceextraheader/approval', [InvoiceExtraHeaderController::class, 'approval']);
    Route::post('invoiceextraheader/{id}/cekvalidasi', [InvoiceExtraHeaderController::class, 'cekvalidasi'])->name('invoiceextraheader.cekvalidasi');
    Route::resource('invoiceextraheader', InvoiceExtraHeaderController::class);
    Route::resource('invoiceextradetail', InvoiceExtraDetailController::class);

    Route::post('invoicechargegandenganheader/{id}/cekvalidasi', [InvoiceChargeGandenganHeaderController::class, 'cekvalidasi'])->name('invoicechargegandenganheader.cekvalidasi');
    Route::get('invoicechargegandenganheader/{id}/getinvoicegandengan', [InvoiceChargeGandenganHeaderController::class, 'getinvoicegandengan'])->name('invoicechargegandenganheader.getinvoicegandengan');
    Route::resource('invoicechargegandenganheader', InvoiceChargeGandenganHeaderController::class);
    Route::resource('invoicechargegandengandetail', InvoiceChargeGandenganDetailController::class);


    Route::get('piutangheader/{id}/printreport', [PiutangHeaderController::class, 'printReport']);
    Route::post('piutangheader/{id}/cekvalidasi', [PiutangHeaderController::class, 'cekvalidasi'])->name('piutangheader.cekvalidasi');
    Route::get('piutangheader/no_bukti', [PiutangHeaderController::class, 'getNoBukti']);
    Route::get('piutangheader/grid', [PiutangHeaderController::class, 'grid']);
    Route::get('piutangheader/field_length', [PiutangHeaderController::class, 'fieldLength']);
    Route::post('piutangheader/{id}/cekValidasiAksi', [PiutangHeaderController::class, 'cekValidasiAksi'])->name('piutangheader.cekValidasiAksi');
    Route::apiResource('piutangheader', PiutangHeaderController::class)->parameters(['piutangheader' => 'piutangHeader']);
    Route::get('piutangdetail/history', [PiutangDetailController::class, 'history']);
    Route::apiResource('piutangdetail', PiutangDetailController::class);

    Route::get('hutangheader/{id}/printreport', [HutangHeaderController::class, 'printReport']);
    Route::post('hutangheader/{id}/cekvalidasi', [HutangHeaderController::class, 'cekvalidasi'])->name('hutangheader.cekvalidasi');
    Route::get('hutangheader/no_bukti', [HutangHeaderController::class, 'getNoBukti']);
    Route::get('hutangheader/combo', [HutangHeaderController::class, 'combo']);
    Route::get('hutangheader/grid', [HutangHeaderController::class, 'grid']);
    Route::post('hutangheader/{id}/cekValidasiAksi', [HutangHeaderController::class, 'cekValidasiAksi'])->name('hutangheader.cekValidasiAksi');
    Route::get('hutangheader/field_length', [HutangHeaderController::class, 'fieldLength']);
    Route::resource('hutangheader', HutangHeaderController::class);
    Route::get('hutangdetail/history', [HutangDetailController::class, 'history']);
    Route::resource('hutangdetail', HutangDetailController::class);

    Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    Route::get('pelunasanpiutangheader/no_bukti', [PelunasanPiutangHeaderController::class, 'getNoBukti']);
    Route::get('pelunasanpiutangheader/default', [PelunasanPiutangHeaderController::class, 'default']);
    Route::get('pelunasanpiutangheader/combo', [PelunasanPiutangHeaderController::class, 'combo']);
    Route::get('pelunasanpiutangheader/{id}/getpiutang', [PelunasanPiutangHeaderController::class, 'getpiutang'])->name('pelunasanpiutangheader.getpiutang');
    Route::get('pelunasanpiutangheader/{id}/{agenid}/getPelunasanPiutang', [PelunasanPiutangHeaderController::class, 'getPelunasanPiutang']);
    Route::get('pelunasanpiutangheader/{id}/{agenid}/getDeletePelunasanPiutang', [PelunasanPiutangHeaderController::class, 'getDeletePelunasanPiutang']);
    Route::get('pelunasanpiutangheader/grid', [PelunasanPiutangHeaderController::class, 'grid']);
    Route::get('pelunasanpiutangheader/field_length', [PelunasanPiutangHeaderController::class, 'fieldLength']);
    Route::resource('pelunasanpiutangheader', PelunasanPiutangHeaderController::class);
    Route::get('pelunasanpiutangdetail/getPelunasan', [PelunasanPiutangDetailController::class, 'getPelunasan']);
    Route::resource('pelunasanpiutangdetail', PelunasanPiutangDetailController::class);

    Route::get('hutangbayarheader/{id}/printreport', [HutangBayarHeaderController::class, 'printReport']);
    Route::post('hutangbayarheader/{id}/cekvalidasi', [HutangBayarHeaderController::class, 'cekvalidasi'])->name('hutangbayarheader.cekvalidasi');
    Route::get('hutangbayarheader/no_bukti', [HutangBayarHeaderController::class, 'getNoBukti']);
    Route::get('hutangbayarheader/field_length', [HutangBayarHeaderController::class, 'fieldLength']);
    Route::get('hutangbayarheader/combo', [HutangBayarHeaderController::class, 'combo']);
    Route::get('hutangbayarheader/{id}/getHutang', [HutangBayarHeaderController::class, 'getHutang'])->name('hutangbayarheader.getHutang');
    Route::get('hutangbayarheader/comboapproval', [HutangBayarHeaderController::class, 'comboapproval']);
    Route::post('hutangbayarheader/approval', [HutangBayarHeaderController::class, 'approval']);
    Route::post('hutangbayarheader/{id}/cekapproval', [HutangBayarHeaderController::class, 'cekapproval'])->name('hutangbayarheader.cekapproval');
    Route::get('hutangbayarheader/{id}/{fieldid}/getPembayaran', [HutangBayarHeaderController::class, 'getPembayaran']);
    Route::get('hutangbayarheader/grid', [HutangBayarHeaderController::class, 'grid']);
    Route::resource('hutangbayarheader', HutangBayarHeaderController::class);
    Route::resource('hutangbayardetail', HutangBayarDetailController::class);

    Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    Route::get('serviceinheader/no_bukti', [ServiceInHeaderController::class, 'getNoBukti']);
    Route::get('serviceinheader/combo', [ServiceInHeaderController::class, 'combo']);
    Route::get('serviceinheader/grid', [ServiceInHeaderController::class, 'grid']);
    Route::get('serviceinheader/field_length', [ServiceInHeaderController::class, 'fieldLength']);
    Route::post('serviceinheader/{id}/cekvalidasi', [ServiceInHeaderController::class, 'cekvalidasi'])->name('serviceinheader.cekvalidasi');
    Route::resource('serviceinheader', ServiceInHeaderController::class);
    Route::resource('serviceindetail', ServiceInDetailController::class);


    Route::get('serviceoutheader/combo', [ServiceOutHeaderController::class, 'combo']);
    Route::get('serviceoutheader/field_length', [ServiceOutHeaderController::class, 'fieldLength']);
    Route::post('serviceoutheader/{id}/cekvalidasi', [ServiceOutHeaderController::class, 'cekvalidasi'])->name('serviceoutheader.cekvalidasi');
    Route::resource('serviceoutheader', ServiceOutHeaderController::class);
    Route::resource('serviceoutdetail', ServiceOutDetailController::class);

    Route::post('kasgantungheader/{id}/cekValidasiAksi', [KasGantungHeaderController::class, 'cekValidasiAksi'])->name('kasgantungheader.cekValidasiAksi');
    Route::get('kasgantungheader/{id}/printreport', [KasGantungHeaderController::class, 'printReport']);
    Route::get('kasgantungheader/combo', [KasGantungHeaderController::class, 'combo']);
    Route::get('kasgantungheader/grid', [KasGantungHeaderController::class, 'grid']);
    Route::get('kasgantungheader/default', [KasGantungHeaderController::class, 'default']);
    Route::post('kasgantungheader/{id}/cekvalidasi', [KasGantungHeaderController::class, 'cekvalidasi'])->name('kasgantungheader.cekvalidasi');
    Route::get('kasgantungheader/field_length', [KasGantungHeaderController::class, 'fieldLength']);
    Route::resource('kasgantungheader', KasGantungHeaderController::class);

    Route::get('kasgantungdetail/getKasgantung', [KasGantungDetailController::class, 'getKasgantung']);
    Route::resource('kasgantungdetail', KasGantungDetailController::class);

    Route::get('gajisupirheader/{id}/printreport', [GajiSupirHeaderController::class, 'printReport']);
    Route::post('gajisupirheader/{id}/cekvalidasi', [GajiSupirHeaderController::class, 'cekvalidasi'])->name('gajisupirheader.cekvalidasi');
    Route::post('gajisupirheader/{id}/cekValidasiAksi', [GajiSupirHeaderController::class, 'cekValidasiAksi'])->name('gajisupirheader.cekValidasiAksi');
    Route::get('gajisupirheader/no_bukti', [GajiSupirHeaderController::class, 'getNoBukti']);
    Route::get('gajisupirheader/grid', [GajiSupirHeaderController::class, 'grid']);
    Route::get('gajisupirheader/field_length', [GajiSupirHeaderController::class, 'fieldLength']);
    Route::get('gajisupirheader/getTrip', [GajiSupirHeaderController::class, 'getTrip']);
    Route::get('gajisupirheader/getpinjsemua', [GajiSupirHeaderController::class, 'getPinjSemua']);
    Route::get('gajisupirheader/{id}/{aksi}/editpinjsemua', [GajiSupirHeaderController::class, 'getEditPinjSemua']);
    Route::get('gajisupirheader/{supirId}/getpinjpribadi', [GajiSupirHeaderController::class, 'getPinjPribadi']);
    Route::get('gajisupirheader/{id}/{supirId}/{aksi}/editpinjpribadi', [GajiSupirHeaderController::class, 'getEditPinjPribadi']);
    Route::post('gajisupirheader/noEdit', [GajiSupirHeaderController::class, 'noEdit']);
    Route::post('gajisupirheader/getuangjalan', [GajiSupirHeaderController::class, 'getUangJalan']);
    Route::get('gajisupirheader/{gajiId}/getEditTrip', [GajiSupirHeaderController::class, 'getEditTrip']);
    Route::resource('gajisupirheader', GajiSupirHeaderController::class);

    Route::get('gajisupirdetail/jurnalbbm', [GajiSupirDetailController::class, 'jurnalBBM']);
    Route::get('gajisupirdetail/deposito', [GajiSupirDetailController::class, 'deposito']);
    Route::get('gajisupirdetail/potpribadi', [GajiSupirDetailController::class, 'potPribadi']);
    Route::get('gajisupirdetail/potsemua', [GajiSupirDetailController::class, 'potSemua']);
    Route::resource('gajisupirdetail', GajiSupirDetailController::class);

    Route::post('notakreditheader/{id}/cekvalidasi', [NotaKreditHeaderController::class, 'cekvalidasi'])->name('notakreditheader.cekvalidasi');
    Route::get('notakreditheader/{id}/printreport', [NotaKreditHeaderController::class, 'printReport']);
    Route::get('notakreditheader/field_length', [NotaKreditHeaderController::class, 'fieldLength']);
    Route::get('notakreditheader/{id}/getpelunasan', [NotaKreditHeaderController::class, 'getPelunasan']);
    Route::get('notakreditheader/{id}/getnotakredit', [NotaKreditHeaderController::class, 'getNotaKredit']);
    Route::post('notakreditheader/approval', [NotaKreditHeaderController::class, 'approval']);
    Route::get('notakreditheader/export', [NotaKreditHeaderController::class, 'export']);
    Route::resource('notakreditheader', NotaKreditHeaderController::class);
    Route::resource('notakredit_detail', NotaKreditDetailController::class);

    Route::post('notadebetheader/{id}/cekvalidasi', [NotaDebetHeaderController::class, 'cekvalidasi'])->name('notadebetheader.cekvalidasi');
    Route::get('notadebetheader/{id}/printreport', [NotaDebetHeaderController::class, 'printReport']);
    Route::get('notadebetheader/field_length', [NotaDebetHeaderController::class, 'fieldLength']);
    Route::get('notadebetheader/{id}/getpelunasan', [NotaDebetHeaderController::class, 'getPelunasan']);
    Route::get('notadebetheader/{id}/getnotadebet', [NotaDebetHeaderController::class, 'getNotaDebet']);
    Route::post('notadebetheader/approval', [NotaDebetHeaderController::class, 'approval']);
    Route::get('notadebetheader/export', [NotaDebetHeaderController::class, 'export']);
    Route::resource('notadebetheader', NotaDebetHeaderController::class);
    Route::resource('notadebet_detail', NotaDebetDetailController::class);

    Route::get('rekappengeluaranheader/field_length', [RekapPengeluaranHeaderController::class, 'fieldLength']);
    Route::get('rekappengeluaranheader/getpengeluaran', [RekapPengeluaranHeaderController::class, 'getPengeluaran']);
    Route::get('rekappengeluaranheader/export', [RekapPengeluaranHeaderController::class, 'export']);
    Route::get('rekappengeluaranheader/{id}/getrekappengeluaran', [RekapPengeluaranHeaderController::class, 'getRekapPengeluaran']);
    Route::post('rekappengeluaranheader/{id}/approval', [RekapPengeluaranHeaderController::class, 'approval']);
    Route::post('rekappengeluaranheader/{id}/cekvalidasi', [RekapPengeluaranHeaderController::class, 'cekvalidasi'])->name('rekappengeluaranheader.cekvalidasi');
    Route::get('gandengan/default', [GandenganController::class, 'default']);

    Route::resource('rekappengeluaranheader', RekapPengeluaranHeaderController::class);
    Route::resource('rekappengeluarandetail', RekapPengeluaranDetailController::class);

    Route::get('rekappenerimaanheader/field_length', [RekapPenerimaanHeaderController::class, 'fieldLength']);
    Route::get('rekappenerimaanheader/getpenerimaan', [RekapPenerimaanHeaderController::class, 'getPenerimaan']);
    Route::get('rekappenerimaanheader/export', [RekapPenerimaanHeaderController::class, 'export']);
    Route::get('rekappenerimaanheader/{id}/getrekappenerimaan', [RekapPenerimaanHeaderController::class, 'getRekapPenerimaan']);
    Route::post('rekappenerimaanheader/{id}/approval', [RekapPenerimaanHeaderController::class, 'approval']);
    Route::post('rekappenerimaanheader/{id}/cekvalidasi', [RekapPenerimaanHeaderController::class, 'cekvalidasi'])->name('rekappenerimaanheader.cekvalidasi');
    Route::resource('rekappenerimaanheader', RekapPenerimaanHeaderController::class);
    Route::resource('rekappenerimaandetail', RekapPenerimaanDetailController::class);

    Route::get('pengembaliankasgantungheader/field_length', [PengembalianKasGantungHeaderController::class, 'fieldLength']);
    Route::get('pengembaliankasgantungheader/getkasgantung', [PengembalianKasGantungHeaderController::class, 'getKasGantung']);
    Route::get('pengembaliankasgantungheader/getpengembalian/{id}', [PengembalianKasGantungHeaderController::class, 'getPengembalian']);
    Route::get('pengembaliankasgantungheader/default', [PengembalianKasGantungHeaderController::class, 'default']);
    Route::post('pengembaliankasgantungheader/{id}/cekValidasiAksi', [PengembalianKasGantungHeaderController::class, 'cekValidasiAksi'])->name('pengembaliankasgantungheader.cekValidasiAksi');
    Route::post('pengembaliankasgantungheader/{id}/cekvalidasi', [PengembalianKasGantungHeaderController::class, 'cekvalidasi'])->name('pengembaliankasgantungheader.cekvalidasi');
    Route::resource('pengembaliankasgantungheader', PengembalianKasGantungHeaderController::class);

    Route::resource('pengembaliankasgantung_detail', PengembalianKasGantungDetailController::class);

    Route::post('pengembaliankasbankheader/{id}/approval', [PengembalianKasBankHeaderController::class, 'approval'])->name('pengembaliankasbankheader.approval');
    Route::get('pengembaliankasbankheader/no_bukti', [PengembalianKasBankHeaderController::class, 'getNoBukti']);
    Route::get('pengembaliankasbankheader/field_length', [PengembalianKasBankHeaderController::class, 'fieldLength']);
    Route::get('pengembaliankasbankheader/combo', [PengembalianKasBankHeaderController::class, 'combo']);
    Route::get('pengembaliankasbankheader/default', [PengembalianKasBankHeaderController::class, 'default']);
    Route::post('pengembaliankasbankheader/{id}/approval', [PengembalianKasBankHeaderController::class, 'approval']);
    Route::post('pengembaliankasbankheader/{id}/cekvalidasi', [PengembalianKasBankHeaderController::class, 'cekvalidasi']);
    Route::get('pengembaliankasbankheader/grid', [PengembalianKasBankHeaderController::class, 'grid']);
    Route::resource('pengembaliankasbankheader', PengembalianKasBankHeaderController::class);

    Route::resource('pengembaliankasbankdetail', PengembalianKasBankDetailController::class);

    Route::get('prosesgajisupirheader/{id}/printreport', [ProsesGajiSupirHeaderController::class, 'printReport']);
    Route::get('prosesgajisupirheader/default', [ProsesGajiSupirHeaderController::class, 'default']);
    Route::post('prosesgajisupirheader/{id}/cekvalidasi', [ProsesGajiSupirHeaderController::class, 'cekvalidasi'])->name('prosesgajisupirheader.cekvalidasi');
    Route::get('prosesgajisupirheader/no_bukti', [ProsesGajiSupirHeaderController::class, 'getNoBukti']);
    Route::get('prosesgajisupirheader/grid', [ProsesGajiSupirHeaderController::class, 'grid']);
    Route::get('prosesgajisupirheader/field_length', [ProsesGajiSupirHeaderController::class, 'fieldLength']);
    Route::get('prosesgajisupirheader/getRic', [ProsesGajiSupirHeaderController::class, 'getRic']);
    Route::post('prosesgajisupirheader/hitungNominal', [ProsesGajiSupirHeaderController::class, 'hitungNominal']);
    Route::post('prosesgajisupirheader/noEdit', [ProsesGajiSupirHeaderController::class, 'noEdit']);
    Route::get('prosesgajisupirheader/{id}/getEdit', [ProsesGajiSupirHeaderController::class, 'getEdit']);
    Route::get('prosesgajisupirheader/{dari}/{sampai}/getAllData', [ProsesGajiSupirHeaderController::class, 'getAllData']);
    Route::resource('prosesgajisupirheader', ProsesGajiSupirHeaderController::class);

    Route::get('prosesgajisupirdetail/getjurnal', [ProsesGajiSupirDetailController::class, 'getJurnal']);
    Route::resource('prosesgajisupirdetail', ProsesGajiSupirDetailController::class);

    Route::get('invoiceheader/{id}/printreport', [InvoiceHeaderController::class, 'printReport']);
    Route::get('invoiceheader/grid', [InvoiceHeaderController::class, 'grid']);
    Route::get('invoiceheader/field_length', [InvoiceHeaderController::class, 'fieldLength']);
    Route::get('invoiceheader/comboapproval', [InvoiceHeaderController::class, 'comboapproval']);
    Route::get('invoiceheader/{id}/getEdit', [InvoiceHeaderController::class, 'getEdit']);
    Route::get('invoiceheader/{id}/getAllEdit', [InvoiceHeaderController::class, 'getAllEdit']);
    Route::get('invoiceheader/getSP', [InvoiceHeaderController::class, 'getSP']);
    Route::post('invoiceheader/approval', [InvoiceHeaderController::class, 'approval']);
    Route::post('invoiceheader/{id}/cekvalidasi', [InvoiceHeaderController::class, 'cekvalidasi'])->name('invoiceheader.cekvalidasi');
    Route::resource('invoiceheader', InvoiceHeaderController::class);
    Route::get('invoicedetail/piutang', [InvoiceDetailController::class, 'piutang']);
    Route::resource('invoicedetail', InvoiceDetailController::class);

    Route::resource('tutupbuku', TutupBukuController::class);


    Route::get('suratpengantar/combo', [SuratPengantarController::class, 'combo']);
    Route::get('suratpengantar/field_length', [SuratPengantarController::class, 'fieldLength']);
    Route::post('suratpengantar/cekUpahSupir', [SuratPengantarController::class, 'cekUpahSupir']);
    Route::post('suratpengantar/{id}/cekValidasi', [SuratPengantarController::class, 'cekValidasi']);
    Route::get('suratpengantar/{id}/getTarifOmset', [SuratPengantarController::class, 'getTarifOmset']);
    Route::get('suratpengantar/{id}/getpelabuhan', [SuratPengantarController::class, 'getpelabuhan']);
    Route::post('suratpengantar/{id}/batalmuat', [SuratPengantarController::class, 'approvalBatalMuat']);
    Route::post('suratpengantar/{id}/edittujuan', [SuratPengantarController::class, 'approvalEditTujuan']);
    Route::get('suratpengantar/{id}/getOrderanTrucking', [SuratPengantarController::class, 'getOrderanTrucking']);
    Route::get('suratpengantar/getGaji/{dari}/{sampai}/{container}/{statuscontainer}', [SuratPengantarController::class, 'getGaji']);
    Route::get('suratpengantar/default', [SuratPengantarController::class, 'default']);
    Route::resource('suratpengantar', SuratPengantarController::class);

    Route::get('penerimaanheader/{id}/printreport', [PenerimaanHeaderController::class, 'printReport']);
    Route::post('penerimaanheader/{id}/approval', [PenerimaanHeaderController::class, 'approval'])->name('penerimaanheader.approval');
    Route::post('penerimaanheader/{id}/cekvalidasi', [PenerimaanHeaderController::class, 'cekvalidasi'])->name('penerimaanheader.cekvalidasi');
    Route::post('penerimaanheader/{id}/cekValidasiAksi', [PenerimaanHeaderController::class, 'cekValidasiAksi'])->name('penerimaanheader.cekValidasiAksi');
    Route::get('penerimaanheader/no_bukti', [PenerimaanHeaderController::class, 'getNoBukti']);
    Route::get('penerimaanheader/combo', [PenerimaanHeaderController::class, 'combo']);
    Route::get('penerimaanheader/{id}/tarikPelunasan', [PenerimaanHeaderController::class, 'tarikPelunasan']);
    Route::post('penerimaanheader/approval', [PenerimaanHeaderController::class, 'approval']);
    Route::get('penerimaanheader/{id}/{table}/getPelunasan', [PenerimaanHeaderController::class, 'getPelunasan']);
    Route::get('penerimaanheader/grid', [PenerimaanHeaderController::class, 'grid']);
    Route::get('penerimaanheader/default', [PenerimaanHeaderController::class, 'default']);

    Route::resource('penerimaanheader', PenerimaanHeaderController::class);
    Route::get('penerimaandetail/getPenerimaan', [PenerimaanDetailController::class, 'getPenerimaan']);
    Route::resource('penerimaandetail', PenerimaanDetailController::class);

    // Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    // Route::post('penerimaan/{id}/approval', [PenerimaanHeaderController::class, 'approval'])->name('penerimaan.approval');
    // Route::get('penerimaan/no_bukti', [PenerimaanHeaderController::class, 'getNoBukti']);
    // Route::get('penerimaan/combo', [PenerimaanHeaderController::class, 'combo']);
    // Route::get('penerimaan/grid', [PenerimaanHeaderController::class, 'grid']);
    // Route::resource('penerimaan', PenerimaanHeaderController::class);

    Route::get('upahritasi/combo', [UpahRitasiController::class, 'combo']);
    Route::get('upahritasi/default', [UpahRitasiController::class, 'default']);
    Route::get('upahritasi/comboluarkota', [UpahRitasiController::class, 'comboluarkota']);
    Route::get('upahritasi/field_length', [UpahRitasiController::class, 'fieldLength']);
    Route::get('upahritasi/listpivot', [UpahRitasiController::class, 'listpivot']);
    Route::post('upahritasi/import', [UpahRitasiController::class, 'import']);
    Route::resource('upahritasi', UpahRitasiController::class);

    Route::get('upahritasirincian/setuprow', [UpahRitasiRincianController::class, 'setUpRow']);
    Route::get('upahritasirincian/setuprowshow/{id}', [UpahRitasiRincianController::class, 'setUpRowExcept']);
    Route::resource('upahritasirincian', UpahRitasiRincianController::class);

    Route::get('ritasi/combo', [RitasiController::class, 'combo']);
    Route::get('ritasi/field_length', [RitasiController::class, 'fieldLength']);
    Route::get('ritasi/default', [RitasiController::class, 'default']);
    Route::resource('ritasi', RitasiController::class);

    //pengeluaran
    Route::get('pengeluaranheader/{id}/printreport', [PengeluaranHeaderController::class, 'printReport']);
    Route::post('pengeluaranheader/{id}/approval', [PengeluaranHeaderController::class, 'approval'])->name('pengeluaranheader.approval');
    Route::get('pengeluaranheader/no_bukti', [PengeluaranHeaderController::class, 'getNoBukti']);
    Route::get('pengeluaranheader/field_length', [PengeluaranHeaderController::class, 'fieldLength']);
    Route::get('pengeluaranheader/combo', [PengeluaranHeaderController::class, 'combo']);
    Route::get('pengeluaranheader/grid', [PengeluaranHeaderController::class, 'grid']);
    Route::get('pengeluaranheader/default', [PengeluaranHeaderController::class, 'default']);
    Route::post('pengeluaranheader/approval', [PengeluaranHeaderController::class, 'approval']);
    Route::post('pengeluaranheader/{id}/cekValidasiAksi', [PengeluaranHeaderController::class, 'cekValidasiAksi'])->name('pengeluaranheader.cekValidasiAksi');
    Route::post('pengeluaranheader/{id}/cekvalidasi', [PengeluaranHeaderController::class, 'cekvalidasi'])->name('pengeluaranheader.cekvalidasi');
    Route::resource('pengeluaranheader', PengeluaranHeaderController::class);

    Route::get('pengeluarandetail/getPengeluaran', [PengeluaranDetailController::class, 'getPengeluaran']);
    Route::resource('pengeluarandetail', PengeluaranDetailController::class);

    Route::post('penerimaangiroheader/{id}/approval', [PenerimaanGiroHeaderController::class, 'approval'])->name('penerimaangiroheader.approval');
    Route::post('penerimaangiroheader/{id}/cekvalidasi', [PenerimaanGiroHeaderController::class, 'cekvalidasi'])->name('penerimaangiroheader.cekvalidasi');
    Route::post('penerimaangiroheader/{id}/cekValidasiAksi', [PenerimaanGiroHeaderController::class, 'cekValidasiAksi'])->name('penerimaangiroheader.cekValidasiAksi');
    Route::get('penerimaangiroheader/{id}/printreport', [PenerimaanGiroHeaderController::class, 'printReport']);
    Route::get('penerimaangiroheader/field_length', [PenerimaanGiroHeaderController::class, 'fieldLength']);
    Route::get('penerimaangiroheader/combo', [PenerimaanGiroHeaderController::class, 'combo']);
    Route::get('penerimaangiroheader/grid', [PenerimaanGiroHeaderController::class, 'grid']);
    Route::post('penerimaangiroheader/approval', [PenerimaanGiroHeaderController::class, 'approval']);
    Route::get('penerimaangiroheader/{id}/tarikPelunasan', [PenerimaanGiroHeaderController::class, 'tarikPelunasan']);
    Route::get('penerimaangiroheader/{id}/getPelunasan', [PenerimaanGiroHeaderController::class, 'getPelunasan']);
    Route::resource('penerimaangiroheader', PenerimaanGiroHeaderController::class);

    Route::resource('penerimaangirodetail', PenerimaanGiroDetailController::class);


    Route::get('harilibur/field_length', [HariLiburController::class, 'fieldLength']);
    Route::get('harilibur/default', [HariLiburController::class, 'default']);
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

    Route::get('approvalnotaheader/combo', [ApprovalNotaHeaderController::class, 'combo']);
    Route::get('approvalnotaheader/default', [ApprovalNotaHeaderController::class, 'default']);
    Route::resource('approvalnotaheader', ApprovalNotaHeaderController::class);
    Route::get('approvalhutangbayar/default', [ApprovalHutangBayarController::class, 'default']);
    Route::resource('approvalhutangbayar', ApprovalHutangBayarController::class);


    Route::get('pendapatansupirheader/{id}/printreport', [PendapatanSupirHeaderController::class, 'printReport']);
    Route::post('pendapatansupirheader/{id}/cekvalidasi', [PendapatanSupirHeaderController::class, 'cekvalidasi'])->name('pendapatansupirheader.cekvalidasi');
    Route::post('pendapatansupirheader/approval', [PendapatanSupirHeaderController::class, 'approval']);
    Route::resource('pendapatansupirheader', PendapatanSupirHeaderController::class)->parameters(['pendapatansupirheader' => 'pendapatanSupirHeader']);
    Route::resource('pendapatansupirdetail', PendapatanSupirDetailController::class);

    Route::get('approvalpendapatansupir/default', [ApprovalPendapatanSupirController::class, 'default']);
    Route::resource('approvalpendapatansupir', ApprovalPendapatanSupirController::class);
    Route::get('stokpersediaan/default', [StokPersediaanController::class, 'default']);
    Route::resource('stokpersediaan', StokPersediaanController::class);
    Route::get('kartustok/report', [KartuStokController::class, 'report'])->name('kartustok.report');
    Route::get('kartustok/export', [KartuStokController::class, 'export'])->name('kartustok.export');
    Route::get('kartustok/default', [KartuStokController::class, 'default']);
    Route::resource('kartustok', KartuStokController::class);

    Route::get('historipenerimaanstok/report', [HistoriPenerimaanStokController::class, 'report'])->name('historipenerimaanstok.report');
    Route::get('historipenerimaanstok/default', [HistoriPenerimaanStokController::class, 'default']);
    Route::resource('historipenerimaanstok', HistoriPenerimaanStokController::class);

    Route::get('historipengeluaranstok/report', [HistoriPengeluaranStokController::class, 'report'])->name('historipengeluaranstok.report');
    Route::get('historipengeluaranstok/default', [HistoriPengeluaranStokController::class, 'default']);
    Route::resource('historipengeluaranstok', HistoriPengeluaranStokController::class);

    Route::get('laporankasbank/report', [LaporanKasBankController::class, 'report'])->name('laporankasbank.report');
    Route::get('laporankasbank/export', [LaporanKasBankController::class, 'export'])->name('laporankasbank.export');
    Route::resource('laporankasbank', LaporanKasBankController::class);
    
    Route::get('laporanbukubesar/report', [LaporanBukuBesarController::class, 'report'])->name('laporanbukubesar.report');
    Route::resource('laporanbukubesar', LaporanBukuBesarController::class);

    Route::post('prosesuangjalansupirheader/{id}/approval', [ProsesUangJalanSupirHeaderController::class, 'approval'])->name('prosesuangjalansupirheader.approval');
    Route::post('prosesuangjalansupirheader/{id}/cekvalidasi', [ProsesUangJalanSupirHeaderController::class, 'cekvalidasi'])->name('prosesuangjalansupirheader.cekvalidasi');
    Route::get('prosesuangjalansupirheader/{id}/printreport', [ProsesUangJalanSupirHeaderController::class, 'printReport']);
    Route::get('prosesuangjalansupirheader/field_length', [ProsesUangJalanSupirHeaderController::class, 'fieldLength']);
    Route::get('prosesuangjalansupirheader/combo', [ProsesUangJalanSupirHeaderController::class, 'combo']);
    Route::get('prosesuangjalansupirheader/grid', [ProsesUangJalanSupirHeaderController::class, 'grid']);
    Route::get('prosesuangjalansupirheader/{id}/tarikPelunasan', [ProsesUangJalanSupirHeaderController::class, 'tarikPelunasan']);
    Route::get('prosesuangjalansupirheader/{id}/getPinjaman', [ProsesUangJalanSupirHeaderController::class, 'getPinjaman']);
    Route::get('prosesuangjalansupirheader/{id}/getPengembalian', [ProsesUangJalanSupirHeaderController::class, 'getPengembalian']);
    Route::resource('prosesuangjalansupirheader', ProsesUangJalanSupirHeaderController::class);

    Route::get('prosesuangjalansupirdetail/transfer', [ProsesUangJalanSupirDetailController::class, 'transfer']);
    Route::resource('prosesuangjalansupirdetail', ProsesUangJalanSupirDetailController::class);

    Route::get('/orderanemkl', [OrderanEmklController::class, 'index'])->middleware('handle-token');

    Route::get('laporandepositosupir/report', [LaporanDepositoSupirController::class, 'report'])->name('laporandepositosupir.report');
    Route::get('laporandepositosupir/export', [LaporanDepositoSupirController::class, 'export'])->name('laporandepositosupir.export');
    Route::resource('laporandepositosupir', LaporanDepositoSupirController::class);
    Route::get('laporanpinjamansupir/report', [LaporanPinjamanSupirController::class, 'report'])->name('laporanpinjamansupir.report');
    Route::resource('laporanpinjamansupir', LaporanPinjamanSupirController::class);
    Route::get('laporanketeranganpinjamansupir/report', [LaporanKeteranganPinjamanSupirController::class, 'report'])->name('laporanketeranganpinjamansupir.report');
    Route::get('laporanketeranganpinjamansupir/export', [LaporanKeteranganPinjamanSupirController::class, 'export'])->name('laporanketeranganpinjamansupir.export');
    Route::resource('laporanketeranganpinjamansupir', LaporanKeteranganPinjamanSupirController::class);
    Route::get('laporankasgantung/report', [LaporanKasGantungController::class, 'report'])->name('laporankasgantung.report');
    Route::get('laporankasgantung/export', [LaporanKasGantungController::class, 'export'])->name('laporankasgantung.export');
    Route::resource('laporankasgantung', LaporanKasGantungController::class);

    Route::get('laporanhutangbbm/report', [LaporanHutangBBMController::class, 'report'])->name('laporanhutangbbm.report');
    Route::resource('laporanhutangbbm', LaporanHutangBBMController::class);
    Route::get('laporanestimasikasgantung/report', [LaporanEstimasiKasGantungController::class, 'report'])->name('laporanestimasikasgantung.report');
    Route::get('lapkartuhutangpervendordetail/report', [LapKartuHutangPerVendorDetailController::class, 'report'])->name('lapkartuhutangpervendordetail.report');
    Route::resource('lapkartuhutangpervendordetail', LapKartuHutangPerVendorDetailController::class);
    Route::get('laporanwarkatbelumcair/report', [LaporanWarkatBelumCairController::class, 'report'])->name('laporanwarkatbelumcair.report');
    Route::resource('laporanwarkatbelumcair', LaporanWarkatBelumCairController::class);
    Route::get('laporanpiutanggiro/report', [LaporanPiutangGiroController::class, 'report'])->name('laporanpiutanggiro.report');
    Route::resource('laporanpiutanggiro', LaporanPiutangGiroController::class);
    Route::get('laporanlabarugi/report', [LaporanLabaRugiController::class, 'report'])->name('laporanlabarugi.report');
    Route::resource('laporanlabarugi', LaporanLabaRugiController::class);
    Route::get('laporanneraca/report', [LaporanNeracaController::class, 'report'])->name('laporanneraca.report');
    Route::resource('laporanneraca', LaporanNeracaController::class);
    Route::get('laporanpenyesuaianbarang/report', [LaporanPenyesuaianBarangController::class, 'report'])->name('laporanpenyesuaianbarang.report');
    Route::get('laporanpenyesuaianbarang/export', [LaporanPenyesuaianBarangController::class, 'export'])->name('laporanpenyesuaianbarang.export');
    Route::resource('laporanpenyesuaianbarang', LaporanPenyesuaianBarangController::class);

    Route::get('laporanpemakaianban/report', [LaporanPemakaianBanController::class, 'report'])->name('laporanpemakaianban.report');
    Route::resource('laporanpemakaianban', LaporanPemakaianBanController::class);

    Route::get('laporantransaksiharian/report', [LaporanTransaksiHarianController::class, 'report'])->name('laporantransaksiharian.report');
    Route::get('laporantransaksiharian/export', [LaporanTransaksiHarianController::class, 'export'])->name('laporantransaksiharian.export');
    Route::resource('laporantransaksiharian', LaporanTransaksiHarianController::class);

    Route::resource('laporanestimasikasgantung', LaporanEstimasiKasGantungController::class);
    Route::get('laporantriptrado/report', [LaporanTripTradoController::class, 'report'])->name('laporantriptrado.report');
    Route::resource('laporantriptrado', zLaporanTripTradoController::class);
    Route::get('laporankartuhutangprediksi/report', [LaporanKartuHutangPrediksiController::class, 'report'])->name('laporankartuhutangprediksi.report');
    Route::resource('laporankartuhutangprediksi', LaporanKartuHutangPrediksiController::class);
    Route::get('laporantripgandengandetail/report', [LaporanTripGandenganDetailController::class, 'report'])->name('laporantripgandengandetail.report');
    Route::resource('laporantripgandengandetail', LaporanTripGandenganDetailController::class);
    Route::get('laporanuangjalan/report', [LaporanUangJalanController::class, 'report'])->name('laporanuangjalan.report');
    Route::resource('laporanuangjalan', LaporanUangJalanController::class);
    Route::get('laporanpinjamansupirkaryawan/report', [LaporanPinjamanSupirKaryawanController::class, 'report'])->name('laporanpinjamansupirkaryawan.report');
    Route::resource('laporanpinjamansupirkaryawan', LaporanPinjamanSupirKaryawanController::class);
    Route::get('laporanpemotonganpinjamanperebs/report', [LaporanPemotonganPinjamanPerEBSController::class, 'report'])->name('laporanpemotonganpinjamanperebs.report');
    Route::resource('laporanpemotonganpinjamanperebs', LaporanPemotonganPinjamanPerEBSController::class);
    Route::get('laporansupirlebihdaritrado/report', [LaporanSupirLebihDariTradoController::class, 'report'])->name('laporansupirlebihdaritrado.report');
    Route::resource('laporansupirlebihdaritrado', LaporanSupirLebihDariTradoController::class);
    Route::get('laporanpemotonganpinjamandepo/report', [LaporanPemotonganPinjamanDepoController::class, 'report'])->name('laporanpemotonganpinjamandepo.report');
    Route::resource('laporanpemotonganpinjamandepo', LaporanPemotonganPinjamanDepoController::class);
    Route::get('laporanrekapsumbangan/report', [LaporanRekapSumbanganController::class, 'report'])->name('laporanrekapsumbangan.report');
    Route::get('laporanrekapsumbangan/export', [LaporanRekapSumbanganController::class, 'export'])->name('laporanrekapsumbangan.export');
    Route::resource('laporanrekapsumbangan', LaporanRekapSumbanganController::class);
    Route::get('laporanklaimpjtsupir/report', [LaporanKlaimPJTSupirController::class, 'report'])->name('laporanklaimpjtsupir.report');
    Route::resource('laporanklaimpjtsupir', LaporanKlaimPJTSupirController::class);
    Route::get('laporankartuhutangpervendor/report', [LaporanKartuHutangPerVendorController::class, 'report'])->name('laporankartuhutangpervendor.report');
    Route::resource('laporankartuhutangpervendor', LaporanKartuHutangPerVendorController::class);
    Route::get('laporanmutasikasbank/report', [LaporanMutasiKasBankController::class, 'report'])->name('laporanmutasikasbank.report');
    Route::resource('laporanmutasikasbank', LaporanMutasiKasBankController::class);
    Route::get('laporankartustok/report', [LaporanKartuStokController::class, 'report'])->name('laporankartustok.report');
    Route::resource('laporankartustok', LaporanKartuStokController::class);
    Route::get('laporanaruskas/report', [LaporanArusKasController::class, 'report'])->name('laporanaruskas.report');
    Route::resource('laporanaruskas', LaporanArusKasController::class);

    Route::get('laporankartupiutangperpelanggan/report', [LaporanKartuPiutangPerPelangganController::class, 'report'])->name('laporankartupiutangperpelanggan.report');
    Route::resource('laporankartupiutangperpelanggan', LaporanKartuPiutangPerPelangganController::class);
    Route::get('laporankartupiutangperplgdetail/report', [LaporanKartuPiutangPerPlgDetailController::class, 'report'])->name('laporankartupiutangperplgdetail.report');
    Route::resource('laporankartupiutangperplgdetail', LaporanKartuPiutangPerPlgDetailController::class);
    Route::get('laporanorderpembelian/report', [LaporanOrderPembelianController::class, 'report'])->name('laporanorderpembelian.report');
    Route::resource('laporanorderpembelian', LaporanOrderPembelianController::class);

    Route::get('exportpengeluaranbarang/export', [ExportPengeluaranBarangController::class, 'export'])->name('exportpengeluaranbarang.export');
    Route::resource('exportpengeluaranbarang', ExportPengeluaranBarangController::class);
    Route::get('exportpembelianbarang/export', [ExportPembelianBarangController::class, 'export'])->name('exportpembelianbarang.export');
    Route::resource('exportpembelianbarang', ExportPembelianBarangController::class);
    // Route::get('exportlaporandeposito/export', [ExportLaporanDepositoController::class, 'export'])->name('exportlaporandeposito.export');
    // Route::resource('exportlaporandeposito', ExportLaporanDepositoController::class);
    Route::get('exportlaporankasgantung/export', [ExportLaporanKasGantungController::class, 'export'])->name('exportlaporankasgantung.export');
    Route::resource('exportlaporankasgantung', ExportLaporanKasGantungController::class);
    Route::get('exportlaporanstok/export', [ExportLaporanStokController::class, 'export'])->name('exportlaporanstok.export');
    Route::resource('exportlaporanstok', ExportLaporanStokController::class);
    Route::get('laporanritasitrado/export', [LaporanRitasiTradoController::class, 'export'])->name('laporanritasitrado.export');
    Route::resource('laporanritasitrado', LaporanRitasiTradoController::class);
    Route::get('laporanritasigandengan/header', [LaporanRitasiGandenganController::class, 'header'])->name('laporanritasigandengan.header');
    Route::get('laporanritasigandengan/export', [LaporanRitasiGandenganController::class, 'export'])->name('laporanritasigandengan.export');
    Route::resource('laporanritasigandengan', LaporanRitasiGandenganController::class);
    Route::get('laporanhistorypinjaman/export', [LaporanHistoryPinjamanController::class, 'export'])->name('laporanhistorypinjaman.export');
    Route::resource('laporanhistorypinjaman', LaporanHistoryPinjamanController::class);
    Route::get('exportpemakaianbarang/export', [ExportPemakaianBarangController::class, 'export'])->name('exportpemakaianbarang.export');
    Route::resource('exportpemakaianbarang', ExportPemakaianBarangController::class);

    Route::get('/orderanemkl/getTglJob', [OrderanEmklController::class, 'getTglJob'])->middleware('handle-token');

    Route::get('pemutihansupir/getPost', [PemutihanSupirController::class, 'getPost']);
    Route::get('pemutihansupir/getNonPost', [PemutihanSupirController::class, 'getNonPost']);
    Route::get('pemutihansupir/{pemutihanId}/getEditPost', [PemutihanSupirController::class, 'getEditPost']);
    Route::get('pemutihansupir/{pemutihanId}/getEditNonPost', [PemutihanSupirController::class, 'getEditNonPost']);
    Route::get('pemutihansupir/{pemutihanId}/getDeletePost', [PemutihanSupirController::class, 'getDeletePost']);
    Route::get('pemutihansupir/{pemutihanId}/getDeleteNonPost', [PemutihanSupirController::class, 'getDeleteNonPost']);
    Route::post('pemutihansupir/{id}/cekvalidasi', [PemutihanSupirController::class, 'cekvalidasi'])->name('pemutihansupir.cekvalidasi');
    Route::get('pemutihansupir/field_length', [PemutihanSupirController::class, 'fieldLength']);
    Route::resource('pemutihansupir', PemutihanSupirController::class);
    Route::resource('pemutihansupirdetail', PemutihanSupirDetailController::class);


    Route::get('exportrincianmingguanpendapatan/export', [ExportRincianMingguanPendapatanSupirController::class, 'export'])->name('exportrincianmingguanpendapatan.export');
    Route::resource('exportrincianmingguanpendapatan', ExportRincianMingguanPendapatanSupirController::class);
    Route::get('laporanbangudangsementara/report', [LaporanBanGudangSementaraController::class, 'report'])->name('laporanbangudangsementara.report');
    Route::resource('laporanbangudangsementara', LaporanBanGudangSementaraController::class);
    Route::get('exportrincianmingguan/export', [ExportRincianMingguanController::class, 'export'])->name('exportrincianmingguan.export');
    Route::resource('exportrincianmingguan', ExportRincianMingguanController::class);
    Route::get('exportlaporankasharian/export', [ExportLaporanKasHarianController::class, 'export'])->name('exportlaporankasharian.export');
    Route::resource('exportlaporankasharian', ExportLaporanKasHarianController::class);

    Route::get('pindahbuku/default', [PindahBukuController::class, 'default']);
    Route::resource('pindahbuku', PindahBukuController::class);

    Route::get('karyawan/field_length', [KaryawanController::class, 'fieldLength']);
    Route::get('karyawan/default', [KaryawanController::class, 'default']);
    Route::post('karyawan/{id}/cekValidasi', [KaryawanController::class, 'cekValidasi'])->name('karyawan.cekValidasi');
    Route::resource('karyawan', KaryawanController::class);
});
