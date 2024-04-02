<?php

use Illuminate\Http\Request;
use App\Models\LaporanArusKas;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AclController;
use App\Http\Controllers\Api\AcosController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankController;

use App\Http\Controllers\Api\KotaController;
use App\Http\Controllers\Api\MenuController;

use App\Http\Controllers\Api\MerkController;
use App\Http\Controllers\Api\RoleController;

use App\Http\Controllers\Api\StokController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ZonaController;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\SupirController;
use App\Http\Controllers\Api\TarifController;
use App\Http\Controllers\Api\TradoController;
use App\Http\Controllers\Api\CabangController;

use App\Http\Controllers\Api\ExpSimController;
use App\Http\Controllers\Api\GudangController;
use App\Http\Controllers\Api\MandorController;

use App\Http\Controllers\Api\OtobonController;
use App\Http\Controllers\Api\RitasiController;
use App\Http\Controllers\Api\SatuanController;
use App\Http\Controllers\Api\CcEmailController;
use App\Http\Controllers\Api\ExpStnkController;
use App\Http\Controllers\Api\MekanikController;
use App\Http\Controllers\Api\ShipperController;
use App\Http\Controllers\Api\ToEmailController;
use App\Http\Controllers\Api\UserAclController;
use App\Http\Controllers\Api\BccEmailController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\KaryawanController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\KelompokController;
use App\Http\Controllers\Api\LapanganController;
use App\Http\Controllers\Api\ListTripController;
use App\Http\Controllers\Api\LogTrailController;
use App\Http\Controllers\Api\PenerimaController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TripInapController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\AkunPusatController;
use App\Http\Controllers\Api\AkuntansiController;
use App\Http\Controllers\Api\AlatBayarController;
use App\Http\Controllers\Api\ContainerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExportRicController;
use App\Http\Controllers\Api\GandenganController;
use App\Http\Controllers\Api\HariLiburController;
use App\Http\Controllers\Api\InputTripController;
use App\Http\Controllers\Api\JenisEmklController;
use App\Http\Controllers\Api\KartuStokController;
use App\Http\Controllers\Api\KartuStokLamaController;
use App\Http\Controllers\Api\KerusakanController;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ReportAllController;
use App\Http\Controllers\Api\SpkHarianController;
use App\Http\Controllers\Api\StokPusatController;
use App\Http\Controllers\Api\TutupBukuController;
use App\Http\Controllers\Api\UpahSupirController;
use App\Http\Controllers\Api\AbsenTradoController;
use App\Http\Controllers\Api\DataRitasiController;
use App\Http\Controllers\Api\JenisOrderController;
use App\Http\Controllers\Api\JenisTradoController;
use App\Http\Controllers\Api\LogAbsensiController;
use App\Http\Controllers\Api\MandorTripController;
use App\Http\Controllers\Api\PindahBukuController;
use App\Http\Controllers\Api\SupirSerapController;
use App\Http\Controllers\Api\UpahRitasiController;
use App\Http\Controllers\Api\BukaAbsensiController;
use App\Http\Controllers\Api\ExpAsuransiController;
use App\Http\Controllers\Api\HistoryTripController;
use App\Http\Controllers\Api\JobTruckingController;
use App\Http\Controllers\Api\LaporanStokController;
use App\Http\Controllers\Api\OrderanEmklController;
use App\Http\Controllers\Api\ReminderOliController;

use App\Http\Controllers\Api\ReminderSpkController;
use App\Http\Controllers\Api\SubKelompokController;

use App\Http\Controllers\Api\HutangDetailController;
use App\Http\Controllers\Api\HutangHeaderController;

use App\Http\Controllers\Api\OpnameDetailController;
use App\Http\Controllers\Api\OpnameHeaderController;

use App\Http\Controllers\Api\ReminderStokController;
use App\Http\Controllers\Api\ReportNeracaController;

use App\Http\Controllers\Api\SaldoUmurAkiController;
use App\Http\Controllers\Api\TarifRincianController;

use App\Http\Controllers\Api\UbahPasswordController;
use App\Http\Controllers\CustomValidationController;

use App\Http\Controllers\LaporanKasHarianController;
use App\Http\Controllers\Api\BankPelangganController;
use App\Http\Controllers\Api\InvoiceDetailController;
use App\Http\Controllers\Api\InvoiceHeaderController;
use App\Http\Controllers\Api\LaporanNeracaController;
use App\Http\Controllers\Api\MainAkunPusatController;
use App\Http\Controllers\Api\PiutangDetailController;
use App\Http\Controllers\Api\PiutangHeaderController;
use App\Http\Controllers\Api\ReminderEmailController;
use App\Http\Controllers\Api\TypeAkuntansiController;
use App\Http\Controllers\Api\ApprovalOpnameController;
use App\Http\Controllers\Api\BlackListSupirController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\LaporanArusKasController;
use App\Http\Controllers\Api\LaporanKasBankController;
use App\Http\Controllers\Api\PemutihanSupirController;
use App\Http\Controllers\Api\PenerimaanStokController;
use App\Http\Controllers\Api\StatusOliTradoController;
use App\Http\Controllers\Api\StokPersediaanController;
use App\Http\Controllers\Api\SuratPengantarController;
use App\Http\Controllers\Api\AkunPusatDetailController;
use App\Http\Controllers\Api\ChargeGandenganController;
use App\Http\Controllers\Api\GajiSupirDetailController;
use App\Http\Controllers\Api\GajiSupirHeaderController;
use App\Http\Controllers\Api\LaporanLabaRugiController;
use App\Http\Controllers\Api\NotaDebetDetailController;
use App\Http\Controllers\Api\NotaDebetHeaderController;
use App\Http\Controllers\Api\OrderanTruckingController;
use App\Http\Controllers\Api\PengeluaranStokController;
use App\Http\Controllers\Api\ReminderServiceController;
use App\Http\Controllers\Api\ServiceInDetailController;
use App\Http\Controllers\Api\ServiceInHeaderController;
use App\Http\Controllers\Api\SpkHarianDetailController;
use App\Http\Controllers\Api\StatusContainerController;
use App\Http\Controllers\Api\ImportDataCabangController;
use App\Http\Controllers\Api\JurnalUmumDetailController;
use App\Http\Controllers\Api\JurnalUmumHeaderController;
use App\Http\Controllers\Api\KasGantungDetailController;
use App\Http\Controllers\Api\KasGantungHeaderController;
use App\Http\Controllers\Api\LaporanBukuBesarController;
use App\Http\Controllers\Api\LaporanHutangBBMController;
use App\Http\Controllers\Api\LaporanKartuStokController;
use App\Http\Controllers\Api\LaporanPembelianController;
use App\Http\Controllers\Api\LaporanTripTradoController;
use App\Http\Controllers\Api\LaporanUangJalanController;
use App\Http\Controllers\Api\NotaKreditDetailController;
use App\Http\Controllers\Api\NotaKreditHeaderController;
use App\Http\Controllers\Api\PenerimaanDetailController;
use App\Http\Controllers\Api\PenerimaanHeaderController;
use App\Http\Controllers\Api\ServiceOutDetailController;
use App\Http\Controllers\Api\ServiceOutHeaderController;
use App\Http\Controllers\Api\TarikDataAbsensiController;
use App\Http\Controllers\Api\UpahSupirRincianController;
use App\Http\Controllers\Api\ApprovalBukaCetakController;
use App\Http\Controllers\api\ApprovalStokReuseController;
use App\Http\Controllers\Api\ExportLaporanStokController;
use App\Http\Controllers\Api\HutangBayarDetailController;
use App\Http\Controllers\Api\HutangBayarHeaderController;
use App\Http\Controllers\Api\HutangExtraDetailController;
use App\Http\Controllers\Api\HutangExtraHeaderController;
use App\Http\Controllers\Api\LaporanBiayaSupirController;
use App\Http\Controllers\Api\LaporanDataJurnalController;
use App\Http\Controllers\Api\LaporanHutangGiroController;
use App\Http\Controllers\Api\LaporanJurnalUmumController;
use App\Http\Controllers\Api\LaporanKasGantungController;
use App\Http\Controllers\Api\MainTypeAkuntansiController;
use App\Http\Controllers\Api\PengajuanTripInapController;
use App\Http\Controllers\Api\PengeluaranDetailController;
use App\Http\Controllers\Api\PengeluaranHeaderController;
use App\Http\Controllers\Api\ReminderSpkDetailController;
use App\Http\Controllers\Api\UpahRitasiRincianController;
use App\Http\Controllers\Api\AbsensiSupirDetailController;
use App\Http\Controllers\Api\AbsensiSupirHeaderController;
use App\Http\Controllers\Api\ApprovalNotaHeaderController;
use App\Http\Controllers\Api\BukaPenerimaanStokController;
use App\Http\Controllers\Api\InvoiceExtraDetailController;
use App\Http\Controllers\Api\InvoiceExtraHeaderController;
use App\Http\Controllers\Api\KaryawanLogAbsensiController;
use App\Http\Controllers\Api\LaporanPiutangGiroController;
use App\Http\Controllers\Api\LaporanRitasiTradoController;
use App\Http\Controllers\Api\LaporanTitipanEmklController;
use App\Http\Controllers\Api\MandorAbsensiSupirController;
use App\Http\Controllers\Api\PenerimaanTruckingController;
use App\Http\Controllers\Api\ProsesAbsensiSupirController;
use App\Http\Controllers\Api\SaldoAwalBukuBesarController;
use App\Http\Controllers\Api\TarifDiscountHargaController;
use App\Http\Controllers\Api\ApprovalHutangBayarController;
use App\Http\Controllers\Api\ApprovalSupirGambarController;
use App\Http\Controllers\Api\ApprovalTradoGambarController;
use App\Http\Controllers\Api\BukaPengeluaranStokController;
use App\Http\Controllers\Api\InvoiceLunasKePusatController;
use App\Http\Controllers\Api\LaporanPemakaianBanController;
use App\Http\Controllers\Api\PengeluaranTruckingController;
use App\Http\Controllers\Api\LaporanDepositoSupirController;
use App\Http\Controllers\Api\LaporanKlaimPJTSupirController;
use App\Http\Controllers\Api\LaporanMingguanSupirController;
use App\Http\Controllers\Api\LaporanMutasiKasBankController;
use App\Http\Controllers\Api\LaporanPemakaianStokController;
use App\Http\Controllers\Api\LaporanPembelianStokController;
use App\Http\Controllers\Api\LaporanPinjamanSupirController;
use App\Http\Controllers\Api\PemutihanSupirDetailController;
use App\Http\Controllers\Api\PenerimaanGiroDetailController;
use App\Http\Controllers\Api\PenerimaanGiroHeaderController;
use App\Http\Controllers\Api\PenerimaanStokDetailController;
use App\Http\Controllers\Api\PenerimaanStokHeaderController;
use App\Http\Controllers\Api\SaldoAkunPusatDetailController;
use App\Http\Controllers\Api\StatusGandenganTruckController;
use App\Http\Controllers\Api\ApprovalInvoiceHeaderController;
use App\Http\Controllers\Api\ExportPemakaianBarangController;
use App\Http\Controllers\Api\ExportPembelianBarangController;
use App\Http\Controllers\Api\ExportRincianMingguanController;
use App\Http\Controllers\Api\HistoriPenerimaanStokController;
use App\Http\Controllers\Api\JurnalUmumPusatDetailController;
use App\Http\Controllers\Api\JurnalUmumPusatHeaderController;
use App\Http\Controllers\Api\LaporanOrderPembelianController;
use App\Http\Controllers\Api\LaporanRekapSumbanganController;
use App\Http\Controllers\Api\LaporanSaldoInventoryController;
use App\Http\Controllers\Api\LaporanSaldoInventoryLamaController;
use App\Http\Controllers\Api\PelunasanHutangDetailController;
use App\Http\Controllers\Api\PelunasanHutangHeaderController;
use App\Http\Controllers\Api\PendapatanSupirDetailController;
use App\Http\Controllers\Api\PendapatanSupirHeaderController;
use App\Http\Controllers\Api\PengeluaranStokDetailController;
use App\Http\Controllers\Api\PengeluaranStokHeaderController;
use App\Http\Controllers\Api\ProsesGajiSupirDetailController;
use App\Http\Controllers\Api\ProsesGajiSupirHeaderController;
use App\Http\Controllers\Api\RekapPenerimaanDetailController;
use App\Http\Controllers\Api\RekapPenerimaanHeaderController;
use App\Http\Controllers\Api\TradoSupirMilikMandorController;
use App\Http\Controllers\Api\ExportLaporanKasHarianController;
use App\Http\Controllers\Api\ExportPerhitunganBonusController;
use App\Http\Controllers\Api\HistoriPengeluaranStokController;
use App\Http\Controllers\Api\LaporanHistoryDepositoController;
use App\Http\Controllers\Api\LaporanHistoryPinjamanController;
use App\Http\Controllers\Api\LaporanPembelianBarangController;
use App\Http\Controllers\Api\LaporanRitasiGandenganController;
use App\Http\Controllers\Api\LaporanTransaksiHarianController;
use App\Http\Controllers\Api\LaporanWarkatBelumCairController;
use App\Http\Controllers\Api\PelunasanPiutangDetailController;
use App\Http\Controllers\Api\PelunasanPiutangHeaderController;
use App\Http\Controllers\Api\RekapPengeluaranDetailController;
use App\Http\Controllers\Api\RekapPengeluaranHeaderController;
use App\Http\Controllers\Api\ApprovalPendapatanSupirController;
use App\Http\Controllers\Api\ApprovalSupirKeteranganController;
use App\Http\Controllers\Api\ApprovalTradoKeteranganController;
use App\Http\Controllers\Api\ApprovalTransaksiHeaderController;
use App\Http\Controllers\Api\ExportLaporanKasGantungController;
use App\Http\Controllers\Api\ExportPengeluaranBarangController;
use App\Http\Controllers\Api\LaporanDepositoKaryawanController;
use App\Http\Controllers\Api\LaporanRekapTitipanEmklController;
use App\Http\Controllers\Api\LaporanApprovalStokReuseController;
use App\Http\Controllers\Api\LaporanPenyesuaianBarangController;
use App\Http\Controllers\Api\PenerimaanTruckingDetailController;
use App\Http\Controllers\Api\PenerimaanTruckingHeaderController;
use App\Http\Controllers\Api\LaporanBanGudangSementaraController;
use App\Http\Controllers\Api\LaporanEstimasiKasGantungController;
use App\Http\Controllers\Api\PengeluaranStokDetailFifoController;
use App\Http\Controllers\Api\PengeluaranTruckingDetailController;
use App\Http\Controllers\Api\PengeluaranTruckingHeaderController;
use App\Http\Controllers\Api\PengembalianKasBankDetailController;
use App\Http\Controllers\Api\PengembalianKasBankHeaderController;
use App\Http\Controllers\Api\AbsensiSupirApprovalDetailController;
use App\Http\Controllers\Api\AbsensiSupirApprovalHeaderController;
use App\Http\Controllers\Api\ExportLaporanMingguanSupirController;
use App\Http\Controllers\Api\LaporanKartuHutangPrediksiController;
use App\Http\Controllers\Api\LaporanKartuPiutangPerAgenController;
use App\Http\Controllers\Api\LaporanKartuPanjarController;
use App\Http\Controllers\Api\LaporanSupirLebihDariTradoController;
use App\Http\Controllers\Api\LaporanTripGandenganDetailController;
use App\Http\Controllers\Api\ProsesUangJalanSupirDetailController;
use App\Http\Controllers\Api\ProsesUangJalanSupirHeaderController;
use App\Http\Controllers\Api\LaporanKartuHutangPerVendorController;
use App\Http\Controllers\Api\LaporanPinjamanPerUnitTradoController;
use App\Http\Controllers\Api\SuratPengantarBiayaTambahanController;
use App\Http\Controllers\Api\InvoiceChargeGandenganDetailController;
use App\Http\Controllers\Api\InvoiceChargeGandenganHeaderController;
use App\Http\Controllers\Api\LaporanPinjamanSupirKaryawanController;
use App\Http\Controllers\Api\PengembalianKasGantungDetailController;
use App\Http\Controllers\Api\PengembalianKasGantungHeaderController;
use App\Http\Controllers\Api\LapKartuHutangPerVendorDetailController;
use App\Http\Controllers\Api\LaporanHistoryTradoMilikSupirController;
use App\Http\Controllers\Api\LaporanKartuHutangPerSupplierController;
use App\Http\Controllers\Api\LaporanPemotonganPinjamanDepoController;
use App\Http\Controllers\Api\LaporanHistorySupirMilikMandorController;
use App\Http\Controllers\Api\LaporanHistoryTradoMilikMandorController;
use App\Http\Controllers\Api\LaporanKeteranganPinjamanSupirController;
use App\Http\Controllers\Api\LaporanMingguanSupirBedaMandorController;
use App\Http\Controllers\Api\PencairanGiroPengeluaranDetailController;
use App\Http\Controllers\Api\PencairanGiroPengeluaranHeaderController;
use App\Http\Controllers\Api\LaporanKartuPiutangPerPelangganController;
use App\Http\Controllers\Api\LaporanKartuPiutangPerPlgDetailController;
use App\Http\Controllers\Api\LaporanPemotonganPinjamanPerEBSController;
use App\Http\Controllers\Api\SuratPengantarApprovalInputTripController;
use App\Http\Controllers\Api\ApprovalBukaTanggalSuratPengantarController;
use App\Http\Controllers\Api\LaporanPemotonganPinjamanDepositoController;
use App\Http\Controllers\Api\ExportRincianMingguanPendapatanSupirController;
use App\Http\Controllers\Api\TarifHargaTertentuController;

// use App\Http\Controllers\Api\LaporanTransaksiHarianController;

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
Route::get('cekIp', [AuthController::class, 'cekIp']);
// Route::post('oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');

Route::get('supir/image/{field}/{filename}/{type}/{aksi}', [SupirController::class, 'getImage']);
Route::get('supir/pdf/{field}/{filename}', [SupirController::class, 'getPdf']);
Route::get('trado/image/{field}/{filename}/{type}/{aksi}', [TradoController::class, 'getImage']);
Route::get('stok/{filename}/{type}', [StokController::class, 'getImage']);
Route::get('stokpusat/{cabang}/{filename}/{type}', [StokPusatController::class, 'getImage']);
Route::get('upahsupir/{filename}/{type}', [UpahSupirController::class, 'getImage']);
Route::get('parameter/getparamrequest', [ParameterController::class, 'getparamrequest']);
Route::get('importdatacabang/testkoneksi', [ImportDataCabangController::class, 'testkoneksi']);
Route::get('stok/getGambar', [StokController::class, 'getGambar']);


route::middleware(['auth:api'])->group(function () {
    Route::resource('dashboard', DashboardController::class)->whereNumber('dashboard');
    Route::get('remainderfinalabsensi', [AuthController::class,'remainderFinalAbsensi']);
    Route::get('error/geterrors', [ErrorController::class, 'errorUrl']);
});

route::middleware(['auth:api'])->group(function () {
    Route::post('bataledit', [Controller::class, 'batalEditingBy']);
    Route::get('jurnalumumpusatheader/importdatacabang', [JurnalUmumPusatHeaderController::class, 'importdatacabang']);
    Route::get('saldoakunpusatdetail/importdatacabang', [SaldoAkunPusatDetailController::class, 'importdatacabang']);
    Route::get('saldoakunpusatdetail/importdatacabangtahun', [SaldoAkunPusatDetailController::class, 'importdatacabangtahun']);
    Route::get('saldoakunpusatdetail/importdatacabangbulan', [SaldoAkunPusatDetailController::class, 'importdatacabangbulan']);
    Route::get('saldoawalbukubesar/importdatacabang', [SaldoAwalBukuBesarController::class, 'importdatacabang']);
    Route::get('saldoawalbukubesar/importdatacabangtahun', [SaldoAwalBukuBesarController::class, 'importdatacabangtahun']);
    Route::get('saldoawalbukubesar/importdatacabangbulan', [SaldoAwalBukuBesarController::class, 'importdatacabangbulan']);
    Route::get('akunpusatdetail/importdatacabang', [AkunPusatDetailController::class, 'importdatacabang']);
    Route::post('jurnalumumpusatheader/storeimportdatacabang', [JurnalUmumPusatHeaderController::class, 'storeimportdatacabang']);
    Route::get('parameter', [ParameterController::class, 'index']);
    Route::get('user/combostatus', [UserController::class, 'combostatus']);
    Route::get('mandor/default', [MandorController::class, 'default']);
    Route::get('mandor/field_length', [MandorController::class, 'fieldLength']);
    Route::resource('supir', SupirController::class)->whereNumber('supir');
    Route::resource('absentrado', AbsenTradoController::class)->whereNumber('absentrado');
    Route::get('parameter/getparambytext', [ParameterController::class, 'getParamByText']);
    Route::get('parameter/combolist', [ParameterController::class, 'combolist']);
    Route::get('parameter/comboapproval', [ParameterController::class, 'comboapproval']);
    Route::get('suratpengantar/field_length', [SuratPengantarController::class, 'fieldLength']);
    Route::resource('harilibur', HariLiburController::class)->whereNumber('harilibur');
    Route::get('suratpengantarapprovalinputtrip/cektanggal', [SuratPengantarApprovalInputTripController::class, 'isTanggalAvaillable']);
    Route::get('suratpengantar/default', [SuratPengantarController::class, 'default']);
    Route::get('saldoumuraki/getUmurAki', [SaldoUmurAkiController::class, 'getUmurAki']);
    Route::get('saldoumuraki/getUmurAkiAll', [SaldoUmurAkiController::class, 'getUmurAkiAll']);
    Route::resource('saldoumuraki', SaldoUmurAkiController::class)->whereNumber('saldoumuraki');

    Route::resource('customer', CustomerController::class)->whereNumber('customer');
    Route::resource('jenisorder', JenisOrderController::class)->whereNumber('jenisorder');
    Route::resource('statuscontainer', StatusContainerController::class)->parameters(['statuscontainer' => 'statusContainer'])->whereNumber('statusContainer');
    Route::resource('container', ContainerController::class)->whereNumber('container');
    Route::resource('tarifdiscountharga', TarifDiscountHargaController::class)->whereNumber('tarifdiscountharga');
    Route::resource('tarifhargatertentu', TarifHargaTertentuController::class)->whereNumber('tarifhargatertentu');
    Route::resource('shipper', ShipperController::class)->whereNumber('shipper');
    Route::get('upahsupirrincian/get', [UpahSupirRincianController::class, 'get']);
    Route::get('absensisupirdetail/get', [AbsensiSupirDetailController::class, 'getDetailAbsensi']);
    Route::resource('kota', KotaController::class)->whereNumber('kotum');
    Route::resource('gudang', GudangController::class)->whereNumber('gudang');
    Route::resource('kategori', KategoriController::class)->whereNumber('kategori');
    Route::resource('kelompok', KelompokController::class)->whereNumber('kelompok');
    Route::resource('kerusakan', KerusakanController::class)->whereNumber('kerusakan');
    Route::resource('mandor', MandorController::class)->whereNumber('mandor');
    Route::resource('merk', MerkController::class)->whereNumber('merk');
    Route::resource('satuan', SatuanController::class)->whereNumber('satuan');
    Route::resource('zona', ZonaController::class)->whereNumber('zona');
    Route::resource('tarif', TarifController::class)->whereNumber('tarif');
    Route::resource('tarifrincian', TarifRincianController::class)->whereNumber('tarifrincian');
    Route::resource('orderantrucking', OrderanTruckingController::class)->whereNumber('orderantrucking');
    Route::resource('chargegandengan', ChargeGandenganController::class)->whereNumber('chargegandengan');
    Route::resource('prosesabsensisupir', ProsesAbsensiSupirController::class)->whereNumber('prosesabsensisupirs');
    Route::resource('mandorabsensisupir', MandorAbsensiSupirController::class)->whereNumber('mandorabsensisupir');
    Route::get('historytrip', [HistoryTripController::class, 'index']);
    Route::resource('listtrip', ListTripController::class)->whereNumber('listtrip');
    Route::post('listtrip/{id}/cekValidasi', [ListTripController::class, 'cekValidasi'])->name('listtrip.cekvalidasi')->whereNumber('id');
    Route::resource('mekanik', MekanikController::class)->whereNumber('mekanik');
    Route::resource('upahsupir', UpahSupirController::class)->whereNumber('upahsupir');
    Route::resource('upahsupirrincian', UpahSupirRincianController::class)->whereNumber('upahsupirrincian');
    Route::post('cabang/{cabang}/approvalkonensi', [CabangController::class, 'approvalKonensi']);
    Route::resource('cabang', CabangController::class)->whereNumber('cabang');
    Route::resource('gandengan', GandenganController::class)->whereNumber('gandengan');
    Route::resource('trado', TradoController::class)->whereNumber('trado');
    Route::resource('bank', BankController::class)->whereNumber('bank');
    Route::resource('bankpelanggan', BankPelangganController::class)->whereNumber('bankpelanggan');
    Route::resource('jenisemkl', JenisEmklController::class)->whereNumber('jenisemkl');
    Route::resource('jenistrado', JenisTradoController::class)->whereNumber('jenistrado');
    Route::resource('akunpusat', AkunPusatController::class)->parameters(['akunpusat' => 'akunPusat'])->whereNumber('akunPusat');
    Route::resource('mainakunpusat', MainAkunPusatController::class)->whereNumber('mainakunpusat');
    Route::resource('error', ErrorController::class)->whereNumber('error');
    Route::resource('user', UserController::class)->whereNumber('user');
    Route::resource('menu', MenuController::class)->whereNumber('menu')->whereNumber('menu');
    Route::resource('userrole', UserRoleController::class)->whereNumber('userrole');
    Route::resource('acl', AclController::class)->whereNumber('acl');
    Route::resource('logtrail', LogTrailController::class)->whereNumber('logtrail');
    Route::resource('trado', TradoController::class)->whereNumber('trado');
    Route::resource('subkelompok', SubKelompokController::class)->parameters(['subkelompok' => 'subKelompok'])->whereNumber('subKelompok');
    Route::resource('supplier', SupplierController::class)->whereNumber('supplier');
    Route::apiResource('stok', StokController::class)->whereNumber('stok');
    Route::resource('penerima', PenerimaController::class)->whereNumber('penerima');
    Route::resource('penerimaantrucking', PenerimaanTruckingController::class)->parameters(['penerimaantrucking' => 'penerimaanTrucking'])->whereNumber('penerimaanTrucking');
    Route::resource('pengeluarantrucking', PengeluaranTruckingController::class)->parameters(['pengeluarantrucking' => 'pengeluaranTrucking'])->whereNumber('pengeluaranTrucking');
    Route::get('bukapenerimaanstok/{id}/cektanggal', [BukaPenerimaanStokController::class, 'isTanggalAvaillable']);
    Route::get('bukapengeluaranstok/{id}/cektanggal', [BukaPengeluaranStokController::class, 'isTanggalAvaillable']);
    Route::get('jurnalumumdetail/jurnal', [JurnalUmumDetailController::class, 'jurnal']);
    Route::get('parameter/combo', [ParameterController::class, 'combo']);
    Route::resource('pengeluarantruckingdetail', PengeluaranTruckingDetailController::class)->whereNumber('pengeluarantruckingdetail');
    Route::resource('penerimaantruckingheader', PenerimaanTruckingHeaderController::class)->whereNumber('penerimaantruckingheader');
    Route::apiResource('pengeluaranstokheader', PengeluaranStokHeaderController::class)->whereNumber('pengeluaranstokheader');
    Route::apiResource('pengeluaranstokdetail', PengeluaranStokDetailController::class)->whereNumber('pengeluaranstokdetail');
    Route::get('pengeluaranstokdetail/hutangbayar', [PengeluaranStokDetailController::class, 'hutangbayar']);
    Route::get('pengeluaranstokdetail/pengeluaran', [PengeluaranStokDetailController::class, 'pengeluaran']);
    Route::get('pengeluaranstokdetail/jurnal', [PengeluaranStokDetailController::class, 'jurnal']);
    Route::get('suratpengantar/gettripinap',  [SuratPengantarController::class, 'getTripInap']);
    Route::resource('suratpengantar', SuratPengantarController::class)->whereNumber('suratpengantar');
    Route::get('invoicedetail/piutang', [InvoiceDetailController::class, 'piutang']);
    Route::resource('invoicedetail', InvoiceDetailController::class)->whereNumber('invoicedetail');
    Route::get('jurnalumumheader/field_length', [JurnalUmumHeaderController::class, 'fieldLength']);
    Route::resource('alatbayar', AlatBayarController::class)->whereNumber('alatbayar');
    Route::get('parameter/getparamfirst', [ParameterController::class, 'getparamfirst']);
    Route::get('cabang/combostatus', [CabangController::class, 'combostatus']);
    Route::apiResource('penerimaanstok', PenerimaanStokController::class)->whereNumber('penerimaanstok');
    Route::apiResource('pengeluaranstok', PengeluaranStokController::class)->whereNumber('pengeluaranstok');

    Route::resource('karyawan', KaryawanController::class)->whereNumber('karyawan');
    Route::resource('akuntansi', AkuntansiController::class)->whereNumber('akuntansi');
    Route::resource('typeakuntansi', TypeAkuntansiController::class)->whereNumber('typeakuntansi');
    Route::resource('maintypeakuntansi', MainTypeAkuntansiController::class)->whereNumber('maintypeakuntansi');
    Route::apiResource('absensisupirheader', AbsensiSupirHeaderController::class)->parameter('absensisupirheader', 'absensiSupirHeader')->whereNumber('absensisupirheader');
    Route::get('suratpengantar/{id}/getpelabuhan', [SuratPengantarController::class, 'getpelabuhan'])->whereNumber('id');
    Route::get('upahritasi/comboluarkota', [UpahRitasiController::class, 'comboluarkota']);
    Route::resource('approvaltradogambar', ApprovalTradoGambarController::class)->whereNumber('approvaltradogambar');
    Route::resource('approvaltradoketerangan', ApprovalTradoKeteranganController::class)->whereNumber('approvaltradoketerangan');
    Route::resource('approvalsupirgambar', ApprovalSupirGambarController::class)->whereNumber('approvalsupirgambar');
    Route::resource('approvalsupirketerangan', ApprovalSupirKeteranganController::class)->whereNumber('approvalsupirketerangan');
    Route::get('invoiceheader/comboapproval', [InvoiceHeaderController::class, 'comboapproval']);
    Route::get('hutangbayarheader/comboapproval', [HutangBayarHeaderController::class, 'comboapproval']);
    Route::get('user/combocabang', [UserController::class, 'combocabang']);
    Route::resource('jobtrucking', JobTruckingController::class);
    Route::get('menu/combomenuparent', [MenuController::class, 'combomenuparent']);
    Route::get('gandengan/combostatus', [GandenganController::class, 'combostatus']);
    Route::get('jenisemkl/combo', [JenisEmklController::class, 'combo']);
    Route::get('shipper/combostatus', [ShipperController::class, 'combostatus']);
    Route::get('penerimaandetail/getPenerimaan', [PenerimaanDetailController::class, 'getPenerimaan']);
    Route::get('penerimaandetail/getDetail', [PenerimaanDetailController::class, 'getDetail']);
    Route::get('rekappenerimaanheader/getpenerimaan', [RekapPenerimaanHeaderController::class, 'getPenerimaan']);
    Route::get('pengeluarandetail/getPengeluaran', [PengeluaranDetailController::class, 'getPengeluaran']);
    Route::get('rekappengeluaranheader/getpengeluaran', [RekapPengeluaranHeaderController::class, 'getPengeluaran']);

    Route::get('gajisupirdetail/bbm', [GajiSupirDetailController::class, 'bbm']);
    Route::get('gajisupirdetail/absensi', [GajiSupirDetailController::class, 'absensi']);
    Route::get('gajisupirdetail/deposito', [GajiSupirDetailController::class, 'deposito']);
    Route::get('gajisupirdetail/potpribadi', [GajiSupirDetailController::class, 'potPribadi']);
    Route::get('gajisupirdetail/potsemua', [GajiSupirDetailController::class, 'potSemua']);
    Route::get('prosesgajisupirdetail/getjurnal', [ProsesGajiSupirDetailController::class, 'getJurnal']);
    Route::post('suratpengantar/{id}/cekValidasi', [SuratPengantarController::class, 'cekValidasi'])->whereNumber('id');
    Route::get('kasgantungdetail/getKasgantung', [KasGantungDetailController::class, 'getKasgantung']);
    Route::get('absensisupirheader/{id}/cekabsensi', [AbsensiSupirHeaderController::class, 'cekabsensi'])->name('absensi.cekabsensi')->whereNumber('id');
    Route::post('absensisupirheader/{id}/cekValidasiAksi', [AbsensiSupirHeaderController::class, 'cekValidasiAksi'])->name('absensisupirheader.cekValidasiAksi')->whereNumber('id');

    Route::post('absensisupirheader/{id}/cekvalidasi', [AbsensiSupirHeaderController::class, 'cekvalidasi'])->name('absensisupirheader.cekvalidasi')->whereNumber('id');
    Route::post('absensisupirheader/{id}/cekvalidasidelete', [AbsensiSupirHeaderController::class, 'cekvalidasidelete'])->name('absensisupirheader.cekvalidasidelete')->whereNumber('id');
    Route::post('supir/approvalblacklist', [SupirController::class, 'approvalBlackListSupir'])->whereNumber('id');
    Route::post('supir/approvalluarkota', [SupirController::class, 'approvalSupirLuarKota'])->whereNumber('id');
    Route::post('supir/approvalnonaktif', [SupirController::class, 'approvalnonaktif']);
    Route::post('supir/approvalhistorysupirmilikmandor', [SupirController::class, 'approvalhistorysupirmilikmandor']);
    Route::post('supir/{id}/cekvalidasihistory', [SupirController::class, 'cekvalidasihistory'])->name('supir.cekvalidasihistory')->whereNumber('id');



    Route::post('prosesuangjalansupirheader/approval', [ProsesUangJalanSupirHeaderController::class, 'approval']);
    Route::post('getidtnl', [Controller::class, 'getIdTnl'])->name('getidtnl');
    Route::post('karyawan/approvalnonaktif', [KaryawanController::class, 'approvalnonaktif']);
    Route::post('reminderemail/approvalnonaktif', [ReminderEmailController::class, 'approvalnonaktif']);
    Route::post('toemail/approvalnonaktif', [ToEmailController::class, 'approvalnonaktif']);
    Route::post('jenistrado/approvalnonaktif', [JenisTradoController::class, 'approvalnonaktif']);
    Route::post('kerusakan/approvalnonaktif', [KerusakanController::class, 'approvalnonaktif']);
    Route::post('mandor/approvalnonaktif', [MandorController::class, 'approvalnonaktif']);
    Route::post('container/approvalnonaktif', [ContainerController::class, 'approvalnonaktif']);
    Route::post('statuscontainer/approvalnonaktif', [StatusContainerController::class, 'approvalnonaktif']);
    Route::post('kota/approvalnonaktif', [KotaController::class, 'approvalnonaktif']);
    Route::post('zona/approvalnonaktif', [ZonaController::class, 'approvalnonaktif']);
    Route::post('ccemail/approvalnonaktif', [CcEmailController::class, 'approvalnonaktif']);
    Route::post('bccemail/approvalnonaktif', [BccEmailController::class, 'approvalnonaktif']);
    Route::post('upahritasi/approvalnonaktif', [UpahRitasiController::class, 'approvalnonaktif']);
    Route::post('cabang/approvalnonaktif', [CabangController::class, 'approvalnonaktif']);
    Route::get('suratpengantarbiayatambahan/approval', [SuratPengantarBiayaTambahanController::class, 'approval']);
    Route::post('suratpengantar/deleterow', [SuratPengantarBiayaTambahanController::class, 'deleteRow']);
    Route::resource('suratpengantarbiayatambahan', SuratPengantarBiayaTambahanController::class)->whereNumber('suratpengantarbiayatambahan');
    Route::post('gajisupirheader/{id}/cekvalidasi', [GajiSupirHeaderController::class, 'cekvalidasi'])->name('gajisupirheader.cekvalidasi')->whereNumber('id');
    Route::post('gajisupirheader/{id}/cekValidasiAksi', [GajiSupirHeaderController::class, 'cekValidasiAksi'])->name('gajisupirheader.cekValidasiAksi')->whereNumber('id');
    Route::post('absensisupirapprovalheader/{id}/cekvalidasi', [AbsensiSupirApprovalHeaderController::class, 'cekvalidasi'])->name('absensisupirapprovalheader.cekvalidasi')->whereNumber('id');
    Route::post('absensisupirapprovalheader/{id}/cekvalidasiaksi', [AbsensiSupirApprovalHeaderController::class, 'cekvalidasiaksi'])->whereNumber('id');
    Route::post('penerimaantruckingheader/{id}/cekValidasiAksi', [PenerimaanTruckingHeaderController::class, 'cekValidasiAksi'])->name('penerimaantruckingheader.cekValidasiAksi')->whereNumber('id');
    Route::post('penerimaantruckingheader/{id}/cekvalidasi', [PenerimaanTruckingHeaderController::class, 'cekvalidasi'])->name('penerimaantruckingheader.cekvalidasi')->whereNumber('id');
    Route::post('pengeluarantruckingheader/{id}/cekValidasiAksi', [PengeluaranTruckingHeaderController::class, 'cekValidasiAksi'])->name('pengeluarantruckingheader.cekValidasiAksi')->whereNumber('id');
    Route::post('pengeluarantruckingheader/{id}/cekvalidasi', [PengeluaranTruckingHeaderController::class, 'cekvalidasi'])->name('pengeluarantruckingheader.cekvalidasi')->whereNumber('id');
    Route::post('invoiceextraheader/{id}/cekvalidasiAksi', [InvoiceExtraHeaderController::class, 'cekvalidasiAksi'])->name('invoiceextraheader.cekvalidasiAksi')->whereNumber('id');
    Route::post('invoiceextraheader/{id}/cekvalidasi', [InvoiceExtraHeaderController::class, 'cekvalidasi'])->name('invoiceextraheader.cekvalidasi')->whereNumber('id');
    Route::post('piutangheader/{id}/cekvalidasi', [PiutangHeaderController::class, 'cekvalidasi'])->name('piutangheader.cekvalidasi')->whereNumber('id');
    Route::post('piutangheader/{id}/cekValidasiAksi', [PiutangHeaderController::class, 'cekValidasiAksi'])->name('piutangheader.cekValidasiAksi')->whereNumber('id');
    Route::post('hutangheader/{id}/cekvalidasi', [HutangHeaderController::class, 'cekvalidasi'])->name('hutangheader.cekvalidasi')->whereNumber('id');
    Route::post('hutangheader/{id}/cekValidasiAksi', [HutangHeaderController::class, 'cekValidasiAksi'])->name('hutangheader.cekValidasiAksi')->whereNumber('id');
    Route::post('pelunasanpiutangheader/{id}/cekValidasiAksi', [PelunasanPiutangHeaderController::class, 'cekValidasiAksi'])->name('pelunasanpiutangheader.cekValidasiAksi')->whereNumber('id');
    Route::post('pelunasanpiutangheader/{id}/cekvalidasi', [PelunasanPiutangHeaderController::class, 'cekvalidasi'])->name('pelunasanpiutangheader.cekvalidasi')->whereNumber('id');
    Route::post('hutangbayarheader/{id}/cekValidasiAksi', [HutangBayarHeaderController::class, 'cekValidasiAksi'])->name('hutangbayarheader.cekValidasiAksi')->whereNumber('id');
    Route::post('hutangbayarheader/{id}/cekvalidasi', [HutangBayarHeaderController::class, 'cekvalidasi'])->name('hutangbayarheader.cekvalidasi')->whereNumber('id');
    Route::post('serviceinheader/{id}/cekvalidasi', [ServiceInHeaderController::class, 'cekvalidasi'])->name('serviceinheader.cekvalidasi')->whereNumber('id');
    Route::post('serviceoutheader/{id}/cekvalidasi', [ServiceOutHeaderController::class, 'cekvalidasi'])->name('serviceoutheader.cekvalidasi')->whereNumber('id');
    Route::post('kasgantungheader/{id}/cekValidasiAksi', [KasGantungHeaderController::class, 'cekValidasiAksi'])->name('kasgantungheader.cekValidasiAksi')->whereNumber('id');
    Route::post('kasgantungheader/{id}/cekvalidasi', [KasGantungHeaderController::class, 'cekvalidasi'])->name('kasgantungheader.cekvalidasi')->whereNumber('id');
    Route::post('notakreditheader/{id}/cekValidasiAksi', [NotaKreditHeaderController::class, 'cekValidasiAksi'])->name('notakreditheader.cekValidasiAksi')->whereNumber('id');
    Route::post('notakreditheader/{id}/cekvalidasi', [NotaKreditHeaderController::class, 'cekvalidasi'])->name('notakreditheader.cekvalidasi')->whereNumber('id');
    Route::post('notadebetheader/{id}/cekvalidasi', [NotaDebetHeaderController::class, 'cekvalidasi'])->name('notadebetheader.cekvalidasi')->whereNumber('id');
    Route::post('notadebetheader/{id}/cekValidasiAksi', [NotaDebetHeaderController::class, 'cekValidasiAksi'])->name('notadebetheader.cekValidasiAksi')->whereNumber('id');
    Route::post('rekappengeluaranheader/{id}/cekvalidasi', [RekapPengeluaranHeaderController::class, 'cekvalidasi'])->name('rekappengeluaranheader.cekvalidasi')->whereNumber('id');
    Route::post('rekappenerimaanheader/{id}/cekvalidasi', [RekapPenerimaanHeaderController::class, 'cekvalidasi'])->name('rekappenerimaanheader.cekvalidasi')->whereNumber('id');
    Route::post('pengembaliankasgantungheader/{id}/cekValidasiAksi', [PengembalianKasGantungHeaderController::class, 'cekValidasiAksi'])->name('pengembaliankasgantungheader.cekValidasiAksi')->whereNumber('id');
    Route::post('pengembaliankasgantungheader/{id}/cekvalidasi', [PengembalianKasGantungHeaderController::class, 'cekvalidasi'])->name('pengembaliankasgantungheader.cekvalidasi')->whereNumber('id');
    Route::post('prosesgajisupirheader/{id}/cekvalidasi', [ProsesGajiSupirHeaderController::class, 'cekvalidasi'])->name('prosesgajisupirheader.cekvalidasi')->whereNumber('id');
    Route::post('prosesgajisupirheader/{id}/cekValidasiAksi', [ProsesGajiSupirHeaderController::class, 'cekValidasiAksi'])->name('prosesgajisupirheader.cekValidasiAksi')->whereNumber('id');
    Route::post('invoiceheader/{id}/cekvalidasiAksi', [InvoiceHeaderController::class, 'cekvalidasiAksi'])->name('invoiceheader.cekvalidasiAksi')->whereNumber('id');
    Route::post('invoiceheader/{id}/cekvalidasi', [InvoiceHeaderController::class, 'cekvalidasi'])->name('invoiceheader.cekvalidasi')->whereNumber('id');
    Route::post('penerimaanheader/{id}/cekvalidasi', [PenerimaanHeaderController::class, 'cekvalidasi'])->name('penerimaanheader.cekvalidasi')->whereNumber('id');
    Route::post('penerimaanheader/{id}/cekValidasiAksi', [PenerimaanHeaderController::class, 'cekValidasiAksi'])->name('penerimaanheader.cekValidasiAksi')->whereNumber('id');
    Route::post('pengeluaranheader/{id}/cekValidasiAksi', [PengeluaranHeaderController::class, 'cekValidasiAksi'])->name('pengeluaranheader.cekValidasiAksi')->whereNumber('id');
    Route::post('pengeluaranheader/{id}/cekvalidasi', [PengeluaranHeaderController::class, 'cekvalidasi'])->name('pengeluaranheader.cekvalidasi')->whereNumber('id');
    Route::post('penerimaangiroheader/{id}/cekvalidasi', [PenerimaanGiroHeaderController::class, 'cekvalidasi'])->name('penerimaangiroheader.cekvalidasi')->whereNumber('id');
    Route::post('penerimaangiroheader/{id}/cekValidasiAksi', [PenerimaanGiroHeaderController::class, 'cekValidasiAksi'])->name('penerimaangiroheader.cekValidasiAksi')->whereNumber('id');
    Route::post('pendapatansupirheader/{id}/cekvalidasi', [PendapatanSupirHeaderController::class, 'cekvalidasi'])->name('pendapatansupirheader.cekvalidasi')->whereNumber('id');
    Route::post('pendapatansupirheader/{id}/cekValidasiAksi', [PendapatanSupirHeaderController::class, 'cekValidasiAksi'])->name('pendapatansupirheader.cekValidasiAksi')->whereNumber('id');
    Route::post('prosesuangjalansupirheader/{id}/cekvalidasi', [ProsesUangJalanSupirHeaderController::class, 'cekvalidasi'])->name('prosesuangjalansupirheader.cekvalidasi')->whereNumber('id');
    Route::post('prosesuangjalansupirheader/{id}/cekValidasiAksi', [ProsesUangJalanSupirHeaderController::class, 'cekValidasiAksi'])->name('prosesuangjalansupirheader.cekValidasiAksi')->whereNumber('id');
    Route::post('tarifdiscountharga/approvalnonaktif', [TarifDiscountHargaController::class, 'approvalnonaktif']);
    Route::post('tarifhargatertentu/approvalnonaktif', [TarifHargaTertentuController::class, 'approvalnonaktif']);
    Route::post('pengeluarantrucking/approvalnonaktif', [PengeluaranTruckingController::class, 'approvalnonaktif']);
    Route::post('penerimaantrucking/approvalnonaktif', [PenerimaanTruckingController::class, 'approvalnonaktif']);
    Route::post('tarif/approvalnonaktif', [TarifController::class, 'approvalnonaktif']);
    Route::post('upahsupir/approvalnonaktif', [UpahSupirController::class, 'approvalnonaktif']);
    Route::post('upahsupir/getrincian', [UpahSupirController::class, 'getRincian']);
    Route::post('customer/approvalnonaktif', [CustomerController::class, 'approvalnonaktif']);
    Route::post('gandengan/approvalnonaktif', [GandenganController::class, 'approvalnonaktif']);
    Route::post('trado/approvalnonaktif', [TradoController::class, 'approvalnonaktif']);
    Route::post('supplier/approvalnonaktif', [SupplierController::class, 'approvalnonaktif']);
    Route::post('shipper/approvalnonaktif', [ShipperController::class, 'approvalnonaktif']);
    Route::post('orderantrucking/{id}/{aksi}/cekValidasi', [OrderanTruckingController::class, 'cekValidasi'])->name('orderantrucking.cekValidasi')->whereNumber('id');
    Route::post('gudang/approvalnonaktif', [GudangController::class, 'approvalnonaktif']);
    Route::post('kategori/approvalnonaktif', [KategoriController::class, 'approvalnonaktif']);
    Route::post('kelompok/approvalnonaktif', [KelompokController::class, 'approvalnonaktif']);
    Route::post('merk/approvalnonaktif', [MerkController::class, 'approvalnonaktif']);
    Route::post('satuan/approvalnonaktif', [SatuanController::class, 'approvalnonaktif']);
    Route::post('subkelompok/approvalnonaktif', [SubKelompokController::class, 'approvalnonaktif']);
    Route::post('stok/approvalnonaktif', [StokController::class, 'approvalnonaktif']);
    Route::post('penerimaanstok/approvalnonaktif', [PenerimaanStokController::class, 'approvalnonaktif']);
    Route::post('pengeluaranstok/approvalnonaktif', [PengeluaranStokController::class, 'approvalnonaktif']);
    Route::post('dataritasi/approvalnonaktif', [DataRitasiController::class, 'approvalnonaktif']);
    Route::post('akuntansi/approvalnonaktif', [AkuntansiController::class, 'approvalnonaktif']);
    Route::post('typeakuntansi/approvalnonaktif', [TypeAkuntansiController::class, 'approvalnonaktif']);
    Route::post('maintypeakuntansi/approvalnonaktif', [MainTypeAkuntansiController::class, 'approvalnonaktif']);
    Route::post('mainakunpusat/approvalnonaktif', [MainAkunPusatController::class, 'approvalnonaktif']);
    Route::post('akunpusat/approvalnonaktif', [AkunPusatController::class, 'approvalnonaktif']);
    Route::post('alatbayar/approvalnonaktif', [AlatBayarController::class, 'approvalnonaktif']);
    Route::post('bank/approvalnonaktif', [BankController::class, 'approvalnonaktif']);
    Route::post('bankpelanggan/approvalnonaktif', [BankPelangganController::class, 'approvalnonaktif']);
    Route::post('penerima/approvalnonaktif', [PenerimaController::class, 'approvalnonaktif']);
    Route::post('jenisorder/approvalnonaktif', [JenisOrderController::class, 'approvalnonaktif']);
    Route::post('absentrado/approvalnonaktif', [AbsenTradoController::class, 'approvalnonaktif']);
    Route::get('supir/approvalsupirtanpa', [SupirController::class, 'approvalSupirTanpa']);
    Route::get('trado/approvaltradotanpa', [TradoController::class, 'approvalTradoTanpa']);
    Route::post('jurnalumumheader/approval', [JurnalUmumHeaderController::class, 'approval']);
    Route::post('suratpengantar/addrow', [SuratPengantarController::class, 'addrow']);
    Route::get('mandorabsensisupir/{tradoId}/getabsentrado', [MandorAbsensiSupirController::class, 'getabsentrado'])->whereNumber('tradoId');
    Route::get('supir/approvalluarkota', [SupirController::class, 'approvalLuarKota']);
    Route::post('pencairangiropengeluaranheader/updateTgl', [PencairanGiroPengeluaranHeaderController::class, 'updateTglJatuhTempo']);
});

route::middleware(['auth:api', 'authorized'])->group(function () {
    Route::post('penerimaanstok/approvaltidakcabang', [PenerimaanStokController::class, 'approvalTidakCabang']);
    Route::post('penerimaanstok/approvalberlakucabang', [PenerimaanStokController::class, 'approvalBerlakuCabang']);

    Route::post('importdatacabang', [ImportDataCabangController::class, 'store']);
    Route::resource('toemail', ToEmailController::class);
    Route::resource('ccemail', CcEmailController::class);
    Route::resource('bccemail', BccEmailController::class);
    Route::resource('reminderemail', ReminderEmailController::class);
    // Route::resource('dashboard', DashboardController::class)->whereNumber('dashboard');
    Route::get('kota/combo', [KotaController::class, 'combo']);
    Route::get('kota/field_length', [KotaController::class, 'fieldLength']);
    Route::get('kota/default', [KotaController::class, 'default']);
    Route::post('kota/{id}/cekValidasi', [KotaController::class, 'cekValidasi'])->name('kota.cekValidasi')->whereNumber('id');
    Route::get('kota/export', [KotaController::class, 'export']);
    Route::get('kota/report', [KotaController::class, 'report']);


    Route::get('gudang/combo', [GudangController::class, 'combo']);
    Route::get('gudang/field_length', [GudangController::class, 'fieldLength']);
    Route::get('gudang/default', [GudangController::class, 'default']);
    Route::post('gudang/{id}/cekValidasi', [GudangController::class, 'cekValidasi'])->name('gudang.cekValidasi')->whereNumber('id');
    Route::get('gudang/export', [GudangController::class, 'export']);
    Route::get('gudang/report', [GudangController::class, 'report']);


    Route::get('kategori/combo', [KategoriController::class, 'combo']);
    Route::get('kategori/field_length', [KategoriController::class, 'fieldLength']);
    Route::get('kategori/default', [KategoriController::class, 'default']);
    Route::post('kategori/{id}/cekValidasi', [KategoriController::class, 'cekValidasi'])->name('kategori.cekValidasi')->whereNumber('id');
    Route::get('kategori/export', [KategoriController::class, 'export']);
    Route::get('kategori/report', [KategoriController::class, 'report']);


    Route::get('kelompok/combo', [KelompokController::class, 'combo']);
    Route::get('kelompok/field_length', [KelompokController::class, 'fieldLength']);
    Route::get('kelompok/default', [KelompokController::class, 'default']);
    Route::post('kelompok/{id}/cekValidasi', [KelompokController::class, 'cekValidasi'])->name('kelompok.cekValidasi')->whereNumber('id');
    Route::get('kelompok/export', [KelompokController::class, 'export']);
    Route::get('kelompok/report', [KelompokController::class, 'report']);

    Route::get('kerusakan/combo', [KerusakanController::class, 'combo']);
    Route::get('kerusakan/field_length', [KerusakanController::class, 'fieldLength']);
    Route::get('kerusakan/default', [KerusakanController::class, 'default']);
    Route::post('kerusakan/{id}/cekValidasi', [KerusakanController::class, 'cekValidasi'])->name('kerusakan.cekValidasi')->whereNumber('id');
    Route::get('kerusakan/export', [KerusakanController::class, 'export']);
    Route::get('kerusakan/report', [KerusakanController::class, 'report']);


    Route::get('mandor/combo', [MandorController::class, 'combo']);
    Route::post('mandor/{id}/cekValidasi', [MandorController::class, 'cekValidasi'])->name('mandor.cekValidasi')->whereNumber('id');
    Route::get('mandor/export', [MandorController::class, 'export']);
    Route::get('mandor/report', [MandorController::class, 'report']);


    Route::get('merk/combo', [MerkController::class, 'combo']);
    Route::get('merk/field_length', [MerkController::class, 'fieldLength']);
    Route::get('merk/default', [MerkController::class, 'default']);
    Route::post('merk/{id}/cekValidasi', [MerkController::class, 'cekValidasi'])->name('merk.cekValidasi')->whereNumber('id');
    Route::get('merk/export', [MerkController::class, 'export']);
    Route::get('merk/report', [MerkController::class, 'report']);


    Route::get('satuan/combo', [SatuanController::class, 'combo']);
    Route::get('satuan/field_length', [SatuanController::class, 'fieldLength']);
    Route::get('satuan/default', [SatuanController::class, 'default']);
    Route::get('satuan/export', [SatuanController::class, 'export']);
    Route::get('satuan/report', [SatuanController::class, 'report']);

    Route::get('zona/combo', [ZonaController::class, 'combo']);
    Route::get('zona/field_length', [ZonaController::class, 'fieldLength']);
    Route::get('zona/default', [ZonaController::class, 'default']);
    Route::post('zona/{id}/cekValidasi', [ZonaController::class, 'cekValidasi'])->name('zona.cekValidasi')->whereNumber('id');
    Route::get('zona/export', [ZonaController::class, 'export']);
    Route::get('zona/report', [ZonaController::class, 'report']);


    Route::get('tarif/combo', [TarifController::class, 'combo']);
    Route::get('tarif/field_length', [TarifController::class, 'fieldLength']);
    Route::get('tarif/default', [TarifController::class, 'default']);
    Route::get('tarif/listpivot', [TarifController::class, 'listpivot']);
    Route::post('tarif/import', [TarifController::class, 'import']);
    Route::get('tarif/export', [TarifController::class, 'export']);
    Route::get('tarif/report', [TarifController::class, 'report']);
    Route::post('tarif/{id}/cekValidasi', [TarifController::class, 'cekValidasi'])->name('tarif.cekValidasi')->whereNumber('id');

    Route::get('tarifrincian/setuprow', [TarifRincianController::class, 'setUpRow']);
    Route::get('tarifrincian/get', [TarifRincianController::class, 'get']);
    Route::get('tarifrincian/setuprowshow/{id}', [TarifRincianController::class, 'setUpRowExcept'])->whereNumber('id');


    Route::get('orderantrucking/combo', [OrderanTruckingController::class, 'combo']);
    Route::get('orderantrucking/field_length', [OrderanTruckingController::class, 'fieldLength']);
    Route::get('orderantrucking/default', [OrderanTruckingController::class, 'default']);
    Route::get('orderantrucking/{id}/getagentas', [OrderanTruckingController::class, 'getagentas'])->whereNumber('id');
    Route::get('orderantrucking/{id}/getcont', [OrderanTruckingController::class, 'getcont'])->whereNumber('id');
    Route::get('orderantrucking/export', [OrderanTruckingController::class, 'export']);
    Route::get('orderantrucking/getorderantrip', [OrderanTruckingController::class, 'getOrderanTrip']);
    Route::post('orderantrucking/approvaledit', [OrderanTruckingController::class, 'approvaledit']);
    Route::post('orderantrucking/approvaltanpajob', [OrderanTruckingController::class, 'approvaltanpajobemkl']);
    Route::post('orderantrucking/approval', [OrderanTruckingController::class, 'approval']);
    Route::patch('orderantrucking/{orderantrucking}/updatenocontainer', [OrderanTruckingController::class, 'updateNoContainer']);

    Route::get('chargegandengan/export', [ChargeGandenganController::class, 'export']);

    Route::get('prosesabsensisupir/combo', [ProsesAbsensiSupirController::class, 'combo']);
    Route::get('prosesabsensisupir/field_length', [ProsesAbsensiSupirController::class, 'fieldLength']);

    Route::get('mandorabsensisupir/{tradoId}/cekvalidasi', [MandorAbsensiSupirController::class, 'cekValidasi'])->whereNumber('tradoId');
    Route::get('mandorabsensisupir/{tradoId}/cekvalidasiadd', [MandorAbsensiSupirController::class, 'cekValidasiAdd'])->whereNumber('tradoId');
    Route::patch('mandorabsensisupir/{id}/update', [MandorAbsensiSupirController::class, 'update'])->whereNumber('id');
    Route::delete('mandorabsensisupir/{id}/delete', [MandorAbsensiSupirController::class, 'destroy'])->whereNumber('id');

    Route::get('invoicelunaskepusat/report', [InvoiceLunasKePusatController::class, 'report']);
    Route::get('invoicelunaskepusat/export', [InvoiceLunasKePusatController::class, 'export']);
    Route::get('invoicelunaskepusat/{invoiceheader_id}/cekvalidasiadd', [InvoiceLunasKePusatController::class, 'cekValidasiAdd'])->whereNumber('invoiceheader_id');
    Route::get('invoicelunaskepusat/{invoiceheader_id}/cekvalidasi', [InvoiceLunasKePusatController::class, 'cekValidasi'])->whereNumber('invoiceheader_id');
    Route::resource('invoicelunaskepusat', InvoiceLunasKePusatController::class)->whereNumber('invoicelunaskepusat');

    Route::post('inputtrip', [InputTripController::class, 'store']);
    Route::get('inputtrip/getinfo', [InputTripController::class, 'getInfoTrado']);
    Route::get('inputtrip/getKotaRitasi', [InputTripController::class, 'getKotaRitasi']);


    Route::get('mekanik/combo', [MekanikController::class, 'combo']);
    Route::get('mekanik/field_length', [MekanikController::class, 'fieldLength']);
    Route::get('mekanik/default', [MekanikController::class, 'default']);
    Route::post('mekanik/{id}/cekValidasi', [MekanikController::class, 'cekValidasi'])->name('mekanik.cekValidasi')->whereNumber('id');

    Route::get('upahsupir/combo', [UpahSupirController::class, 'combo']);
    Route::get('upahsupir/field_length', [UpahSupirController::class, 'fieldLength']);
    Route::get('upahsupir/default', [UpahSupirController::class, 'default']);
    Route::get('upahsupir/export', [UpahSupirController::class, 'export']);
    Route::post('upahsupir/import', [UpahSupirController::class, 'import']);
    Route::post('upahsupir/{id}/cekValidasi', [UpahSupirController::class, 'cekValidasi'])->name('upahsupir.cekValidasi')->whereNumber('id');

    Route::get('upahsupirrincian/setuprow', [UpahSupirRincianController::class, 'setUpRow']);
    Route::get('upahsupirrincian/setuprowshow/{id}', [UpahSupirRincianController::class, 'setUpRowExcept'])->whereNumber('id');

    Route::get('parameter/export', [ParameterController::class, 'export']);
    Route::get('parameter/detail', [ParameterController::class, 'detail']);
    Route::get('parameter/default', [ParameterController::class, 'default']);
    Route::get('parameter/field_length', [ParameterController::class, 'fieldLength']);
    Route::get('parameter/getcoa', [ParameterController::class, 'getcoa']);
    Route::get('parameter/{id}', [ParameterController::class, 'show']);
    Route::post('parameter/addrow', [ParameterController::class, 'addrow']);
    Route::post('parameter', [ParameterController::class, 'store']);
    Route::patch('parameter/{id}', [ParameterController::class, 'update']);
    Route::delete('parameter/{id}', [ParameterController::class, 'destroy']);

    // Route::resource('parameter', ParameterController::class)->whereNumber('parameter');

    Route::get('absensisupirheader/{id}/detail', [AbsensiSupirHeaderController::class, 'detail'])->name('absensi.detail')->whereNumber('id');
    Route::get('absensisupirheader/no_bukti', [AbsensiSupirHeaderController::class, 'getNoBukti']);
    Route::get('absensisupirheader/running_number', [AbsensiSupirHeaderController::class, 'getRunningNumber']);
    Route::get('absensisupirheader/grid', [AbsensiSupirHeaderController::class, 'grid']);
    Route::get('absensisupirheader/field_length', [AbsensiSupirHeaderController::class, 'fieldLength']);
    Route::get('absensisupirheader/default', [AbsensiSupirHeaderController::class, 'default']);
    Route::get('absensisupirheader/{id}/printreport', [AbsensiSupirHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('absensisupirheader/{id}/export', [AbsensiSupirHeaderController::class, 'export'])->name('absensisupirheader.export')->whereNumber('id');
    Route::post('absensisupirheader/{id}/approval', [AbsensiSupirHeaderController::class, 'approval'])->name('absensisupirheader.approval')->whereNumber('id');
    Route::post('absensisupirheader/{id}/approvalEditAbsensi', [AbsensiSupirHeaderController::class, 'approvalEditAbsensi'])->whereNumber('id');
    Route::post('absensisupirheader/approvaltripinap', [AbsensiSupirHeaderController::class, 'approvalTripInap']);
    Route::post('absensisupirheader/approvalfinalabsensi', [AbsensiSupirHeaderController::class, 'approvalfinalabsensi']);
    

    Route::resource('absensisupirdetail', AbsensiSupirDetailController::class);
    Route::post('bukaabsensi/updatetanggalbatas', [BukaAbsensiController::class, 'updateTanggalBatas']);
    Route::resource('bukaabsensi', BukaAbsensiController::class)->whereNumber('bukaabsensi');

    Route::get('approvalsupirgambar/default', [ApprovalSupirGambarController::class, 'default']);

    Route::get('approvalsupirketerangan/default', [ApprovalSupirKeteranganController::class, 'default']);

    Route::get('blacklistsupir/default', [BlackListSupirController::class, 'default']);
    Route::resource('blacklistsupir', BlackListSupirController::class)->whereNumber('blacklistsupir');

    Route::get('tradosupirmilikmandor/default', [TradoSupirMilikMandorController::class, 'default']);

    Route::resource('tradosupirmilikmandor', TradoSupirMilikMandorController::class)->whereNumber('tradosupirmilikmandor');

    Route::get('suratpengantarapprovalinputtrip/default', [SuratPengantarApprovalInputTripController::class, 'default']);
    Route::post('suratpengantarapprovalinputtrip/{id}/cekvalidasi', [SuratPengantarApprovalInputTripController::class, 'cekvalidasi'])->name('suratpengantarapprovalinputtrip.cekvalidasi')->whereNumber('id');
    Route::get('suratpengantarapprovalinputtrip/field_length', [SuratPengantarApprovalInputTripController::class, 'fieldLength']);
    Route::resource('suratpengantarapprovalinputtrip', SuratPengantarApprovalInputTripController::class)->whereNumber('suratpengantarapprovalinputtrip');

    Route::get('approvaltransaksiheader/combo', [ApprovalTransaksiHeaderController::class, 'combo']);
    Route::get('approvaltransaksiheader/default', [ApprovalTransaksiHeaderController::class, 'default']);
    Route::apiResource('approvaltransaksiheader', ApprovalTransaksiHeaderController::class)->whereNumber('approvaltransaksiheader');

    Route::get('approvalinvoiceheader/combo', [ApprovalInvoiceHeaderController::class, 'combo']);
    Route::get('approvalinvoiceheader/default', [ApprovalInvoiceHeaderController::class, 'default']);
    Route::resource('approvalinvoiceheader', ApprovalInvoiceHeaderController::class)->whereNumber('approvalinvoiceheader');

    Route::get('approvalbukacetak/combo', [ApprovalBukaCetakController::class, 'combo']);
    Route::resource('approvalbukacetak', ApprovalBukaCetakController::class)->whereNumber('approvalbukacetak');

    Route::get('absensisupirapprovalheader/running_number', [AbsensiSupirApprovalHeaderController::class, 'getRunningNumber']);
    Route::get('absensisupirapprovalheader/grid', [AbsensiSupirApprovalHeaderController::class, 'grid']);
    Route::get('absensisupirapprovalheader/field_length', [AbsensiSupirApprovalHeaderController::class, 'fieldLength']);
    Route::get('absensisupirapprovalheader/{id}/printreport', [AbsensiSupirApprovalHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('absensisupirapprovalheader/{id}/export', [AbsensiSupirApprovalHeaderController::class, 'export'])->whereNumber('id');
    Route::get('absensisupirapprovalheader/{absensi}/getabsensi', [AbsensiSupirApprovalHeaderController::class, 'getAbsensi'])->whereNumber('absensi');
    Route::get('absensisupirapprovalheader/{absensi}/getapproval', [AbsensiSupirApprovalHeaderController::class, 'getApproval'])->whereNumber('absensi');
    Route::post('absensisupirapprovalheader/{id}/approval', [AbsensiSupirApprovalHeaderController::class, 'approval'])->whereNumber('id');
    Route::apiResource('absensisupirapprovalheader', AbsensiSupirApprovalHeaderController::class)->whereNumber('absensisupirapprovalheader');
    Route::apiResource('absensisupirapprovaldetail', AbsensiSupirApprovalDetailController::class)->whereNumber('absensisupirapprovaldetail');

    Route::get('customer/field_length', [CustomerController::class, 'fieldLength']);
    Route::get('customer/export', [CustomerController::class, 'export'])->name('export');
    Route::get('customer/default', [CustomerController::class, 'default']);
    // Route::post('customer/{customer}/approval', [CustomerController::class, 'approval'])->name('customer.approval')->whereNumber('customer');
    Route::post('customer/{id}/cekValidasi', [CustomerController::class, 'cekValidasi'])->name('customer.cekValidasi')->whereNumber('id');
    Route::get('customer/export', [CustomerController::class, 'export']);
    Route::get('customer/report', [CustomerController::class, 'report']);
    Route::post('customer/approval', [CustomerController::class, 'approval']);


    Route::get('cabang/field_length', [CabangController::class, 'fieldLength']);
    Route::get('cabang/default', [CabangController::class, 'default']);
    Route::get('cabang/report', [CabangController::class, 'report']);
    Route::get('cabang/export', [CabangController::class, 'export']);
    Route::get('cabang/getPosition2', [CabangController::class, 'getPosition2']);

    Route::get('gandengan/field_length', [GandenganController::class, 'fieldLength']);
    Route::get('gandengan/getPosition2', [GandenganController::class, 'getPosition2']);
    Route::get('gandengan/default', [GandenganController::class, 'default']);
    Route::get('gandengan/report', [GandenganController::class, 'report']);
    Route::get('gandengan/export', [GandenganController::class, 'export']);
    Route::post('gandengan/{id}/cekValidasi', [GandenganController::class, 'cekValidasi'])->name('gandengan.cekValidasi')->whereNumber('id');


    Route::get('acos/getuseracl', [AcosController::class, 'getUserAcl']);
    Route::get('acos/field_length', [AcosController::class, 'fieldLength']);
    Route::resource('acos', AcosController::class)->whereNumber('acos');

    Route::get('logtrail/detail', [LogTrailController::class, 'detail']);
    Route::get('logtrail/header', [LogTrailController::class, 'header']);
    Route::resource('logtrail', LogTrailController::class)->whereNumber('logtrail');

    Route::post('trado/historymandor', [TradoController::class, 'historyTradoMandor']);
    Route::get('trado/{id}/gethistorymandor', [TradoController::class, 'getHistoryMandor']);
    Route::get('trado/{id}/getlisthistorymandor', [TradoController::class, 'getListHistoryMandor']);
    Route::post('trado/historysupir', [TradoController::class, 'historyTradoSupir']);
    Route::get('trado/{id}/gethistorysupir', [TradoController::class, 'getHistorySupir']);
    Route::get('trado/{id}/getlisthistorysupir', [TradoController::class, 'getListHistorySupir']);
    Route::get('trado/combo', [TradoController::class, 'combo']);
    Route::get('trado/field_length', [TradoController::class, 'fieldLength']);
    Route::post('trado/upload_image/{id}', [TradoController::class, 'uploadImage'])->whereNumber('id');
    Route::get('trado/default', [TradoController::class, 'default']);
    Route::post('trado/{id}/cekValidasi', [TradoController::class, 'cekValidasi'])->name('trado.cekValidasi')->whereNumber('id');
    Route::get('trado/export', [TradoController::class, 'export']);
    Route::get('trado/report', [TradoController::class, 'report']);
    Route::post('trado/approvalmesin', [TradoController::class, 'approvalmesin']);
    Route::post('trado/approvalpersneling', [TradoController::class, 'approvalpersneling']);
    Route::post('trado/approvalgardan', [TradoController::class, 'approvalgardan']);
    Route::post('trado/approvalsaringanhawa', [TradoController::class, 'approvalsaringanhawa']);
    Route::post('trado/approvalhistorytradomilikmandor', [TradoController::class, 'approvalhistorytradomilikmandor']);
    Route::post('trado/approvalhistorytradomiliksupir', [TradoController::class, 'approvalhistorytradomiliksupir']);
    Route::post('trado/approvaltradotanpa', [TradoController::class, 'StoreApprovalTradoTanpa']);
    Route::post('trado/{id}/cekvalidasihistory', [TradoController::class, 'cekvalidasihistory'])->name('trado.cekvalidasihistory')->whereNumber('id');




    Route::get('absentrado/field_length', [AbsenTradoController::class, 'fieldLength']);
    Route::get('absentrado/rekapabsentrado', [AbsenTradoController::class, 'rekapabsentrado']);
    Route::post('absentrado/addrow', [AbsenTradoController::class, 'addrow']);
    Route::get('absentrado/default', [AbsenTradoController::class, 'default']);
    Route::post('absentrado/{id}/cekValidasi', [AbsenTradoController::class, 'cekValidasi'])->name('absentrado.cekValidasi')->whereNumber('id');
    Route::get('absentrado/export', [AbsenTradoController::class, 'export']);
    Route::get('absentrado/report', [AbsenTradoController::class, 'report']);
    Route::get('absentrado/detail', [AbsenTradoController::class, 'detail']);

    Route::get('container/field_length', [ContainerController::class, 'fieldLength']);
    Route::get('container/combostatus', [ContainerController::class, 'combostatus']);
    Route::get('container/getPosition2', [ContainerController::class, 'getPosition2']);
    Route::get('container/default', [ContainerController::class, 'default']);
    Route::post('container/{id}/cekValidasi', [ContainerController::class, 'cekValidasi'])->name('container.cekValidasi')->whereNumber('id');
    Route::get('container/export', [ContainerController::class, 'export']);
    Route::get('container/report', [ContainerController::class, 'report']);

    Route::get('tarifdiscountharga/field_length', [TarifDiscountHargaController::class, 'fieldLength']);
    Route::get('tarifdiscountharga/combostatus', [TarifDiscountHargaController::class, 'combostatus']);
    Route::get('tarifdiscountharga/getPosition2', [TarifDiscountHargaController::class, 'getPosition2']);
    Route::get('tarifdiscountharga/default', [TarifDiscountHargaController::class, 'default']);
    Route::post('tarifdiscountharga/{id}/cekValidasi', [TarifDiscountHargaController::class, 'cekValidasi'])->name('tarifdiscountharga.cekValidasi')->whereNumber('id');
    Route::get('tarifdiscountharga/combo', [TarifDiscountHargaController::class, 'combo']);
    Route::get('tarifdiscountharga/export', [TarifDiscountHargaController::class, 'export']);
    Route::get('tarifdiscountharga/report', [TarifDiscountHargaController::class, 'report']);

    Route::get('tarifhargatertentu/field_length', [TarifHargaTertentuController::class, 'fieldLength']);
    Route::get('tarifhargatertentu/combostatus', [TarifHargaTertentuController::class, 'combostatus']);
    Route::get('tarifhargatertentu/getPosition2', [TarifHargaTertentuController::class, 'getPosition2']);
    Route::get('tarifhargatertentu/default', [TarifHargaTertentuController::class, 'default']);
    Route::post('tarifhargatertentu/{id}/cekValidasi', [TarifHargaTertentuController::class, 'cekValidasi'])->name('tarifhargatertentu.cekValidasi')->whereNumber('id');
    Route::get('tarifhargatertentu/combo', [TarifHargaTertentuController::class, 'combo']);
    Route::get('tarifhargatertentu/export', [TarifHargaTertentuController::class, 'export']);
    Route::get('tarifhargatertentu/report', [TarifHargaTertentuController::class, 'report']);

    Route::get('bank/combo', [BankController::class, 'combo']);
    Route::get('bank/field_length', [BankController::class, 'fieldLength']);
    Route::get('bank/default', [BankController::class, 'default']);
    Route::post('bank/{id}/cekValidasi', [BankController::class, 'cekValidasi'])->name('bank.cekValidasi')->whereNumber('id');
    Route::get('bank/export', [BankController::class, 'export']);
    Route::get('bank/report', [BankController::class, 'report']);

    Route::get('alatbayar/combo', [AlatBayarController::class, 'combo']);
    Route::get('alatbayar/field_length', [AlatBayarController::class, 'fieldLength']);
    Route::get('alatbayar/default', [AlatBayarController::class, 'default']);
    Route::post('alatbayar/{id}/cekValidasi', [AlatBayarController::class, 'cekValidasi'])->name('alatbayar.cekValidasi')->whereNumber('id');
    Route::get('alatbayar/export', [AlatBayarController::class, 'export']);
    Route::get('alatbayar/report', [AlatBayarController::class, 'report']);

    Route::get('bankpelanggan/combo', [BankPelangganController::class, 'combo']);
    Route::get('bankpelanggan/field_length', [BankPelangganController::class, 'fieldLength']);
    Route::get('bankpelanggan/default', [BankPelangganController::class, 'default']);
    Route::post('bankpelanggan/{id}/cekValidasi', [BankPelangganController::class, 'cekValidasi'])->name('bankpelanggan.cekValidasi')->whereNumber('id');
    Route::get('bankpelanggan/export', [BankPelangganController::class, 'export']);
    Route::get('bankpelanggan/report', [BankPelangganController::class, 'report']);

    Route::get('jenisemkl/field_length', [JenisEmklController::class, 'fieldLength']);
    Route::get('jenisemkl/default', [JenisEmklController::class, 'default']);
    Route::post('jenisemkl/{id}/cekValidasi', [JenisEmklController::class, 'cekValidasi'])->name('jenisemkl.cekValidasi')->whereNumber('id');
    Route::get('jenisemkl/export', [JenisEmklController::class, 'export']);
    Route::get('jenisemkl/report', [JenisEmklController::class, 'report']);

    Route::get('jenisorder/combo', [JenisOrderController::class, 'combo']);
    Route::get('jenisorder/field_length', [JenisOrderController::class, 'fieldLength']);
    Route::get('jenisorder/default', [JenisOrderController::class, 'default']);
    Route::post('jenisorder/{id}/cekValidasi', [JenisOrderController::class, 'cekValidasi'])->name('jenisorder.cekValidasi')->whereNumber('id');
    Route::get('jenisorder/export', [JenisOrderController::class, 'export']);
    Route::get('jenisorder/report', [JenisOrderController::class, 'report']);

    Route::get('jenistrado/combo', [JenisTradoController::class, 'combo']);
    Route::get('jenistrado/field_length', [JenisTradoController::class, 'fieldLength']);
    Route::get('jenistrado/default', [JenisTradoController::class, 'default']);
    Route::post('jenistrado/{id}/cekValidasi', [JenisTradoController::class, 'cekValidasi'])->name('jenistrado.cekValidasi')->whereNumber('id');
    Route::get('jenistrado/export', [JenisTradoController::class, 'export']);
    Route::get('jenistrado/report', [JenisTradoController::class, 'report']);

    Route::get('akunpusat/field_length', [AkunPusatController::class, 'fieldLength']);
    Route::get('akunpusat/default', [AkunPusatController::class, 'default']);
    Route::get('akunpusat/export', [AkunPusatController::class, 'export']);
    Route::get('akunpusat/report', [AkunPusatController::class, 'report']);
    Route::post('akunpusat/transfer', [AkunPusatController::class, 'transfer']);
    Route::delete('akunpusat/deleteCoa', [AkunPusatController::class, 'deleteCoa']);
    Route::get('akunpusat/checkCoa', [AkunPusatController::class, 'checkCoa']);
    Route::get('akunpusat/{id}/cekValidasi', [AkunPusatController::class, 'cekValidasi'])->name('akunpusat.cekValidasi')->whereNumber('id');

    Route::get('mainakunpusat/field_length', [MainAkunPusatController::class, 'fieldLength']);
    Route::get('mainakunpusat/default', [MainAkunPusatController::class, 'default']);
    Route::get('mainakunpusat/export', [MainAkunPusatController::class, 'export']);
    Route::get('mainakunpusat/report', [MainAkunPusatController::class, 'report']);
    Route::get('mainakunpusat/{id}/cekValidasi', [MainAkunPusatController::class, 'cekValidasi'])->whereNumber('id');

    Route::get('error/field_length', [ErrorController::class, 'fieldLength']);

    Route::get('error/geterror', [ErrorController::class, 'geterror']);
    Route::get('error/export', [ErrorController::class, 'export'])->name('error.export');

    Route::get('role/getroleid', [RoleController::class, 'getroleid']);
    Route::get('role/field_length', [RoleController::class, 'fieldLength']);
    Route::get('role/export', [RoleController::class, 'export'])->name('role.export');
    Route::get('role/{role}/acl', [AclController::class, 'RoleAcl'])->whereNumber('role');
    Route::post('role/{role}/acl', [UserRoleController::class, 'store'])->whereNumber('role');
    Route::resource('role', RoleController::class)->whereNumber('role');

    Route::get('cabang/field_length', [CabangController::class, 'fieldLength']);
    Route::get('cabang/getPosition2', [CabangController::class, 'getPosition2']);
    Route::get('cabang/export', [CabangController::class, 'export'])->name('cabang.export');

    Route::get('acos/field_length', [AcosController::class, 'fieldLength']);
    Route::resource('acos', AcosController::class);

    Route::get('user/field_length', [UserController::class, 'fieldLength']);
    Route::get('user/export', [UserController::class, 'export'])->name('user.export');
    Route::get('user/getuserid', [UserController::class, 'getuserid']);
    Route::get('user/default', [UserController::class, 'default']);
    Route::get('user/{user}/role', [UserRoleController::class, 'index'])->whereNumber('user');
    Route::post('user/{user}/role', [UserController::class, 'storeRoles'])->whereNumber('user');
    Route::get('user/{user}/acl', [UserAclController::class, 'index'])->whereNumber('user');
    Route::post('user/{user}/acl', [UserAclController::class, 'store'])->whereNumber('user');

    Route::get('menu/field_length', [MenuController::class, 'fieldLength']);
    Route::get('menu/controller', [MenuController::class, 'listclassall']);
    Route::get('menu/getdatanamaacos', [MenuController::class, 'getdatanamaacos']);
    Route::get('menu/export', [MenuController::class, 'export'])->name('menu.export');

    Route::get('userrole/field_length', [UserRoleController::class, 'fieldLength']);
    Route::get('userrole/detail', [UserRoleController::class, 'detail']);
    Route::get('userrole/detaillist', [UserRoleController::class, 'detaillist']);
    Route::get('userrole/combostatus', [UserRoleController::class, 'combostatus']);
    Route::get('userrole/export', [UserRoleController::class, 'export'])->name('userrole.export');

    Route::get('acl/field_length', [AclController::class, 'fieldLength']);
    Route::get('acl/detail/{roleId}', [AclController::class, 'detail'])->whereNumber('roleId');
    Route::get('acl/detaillist', [AclController::class, 'detaillist']);
    Route::get('acl/combostatus', [AclController::class, 'combostatus']);
    Route::get('acl/export', [AclController::class, 'export'])->name('acl.export');

    Route::get('logtrail/detail', [LogTrailController::class, 'detail']);
    Route::get('logtrail/header', [LogTrailController::class, 'header']);

    Route::get('trado/combo', [TradoController::class, 'combo']);
    Route::get('trado/field_length', [TradoController::class, 'fieldLength']);
    Route::get('trado/getImage/{id}/{field}', [TradoController::class, 'getImage'])->whereNumber('id');
    Route::post('trado/upload_image/{id}', [TradoController::class, 'uploadImage'])->whereNumber('id');


    Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');

    Route::post('supir/historymandor', [SupirController::class, 'historySupirMandor']);
    Route::post('supir/approvalsupirtanpa', [SupirController::class, 'StoreApprovalSupirTanpa']);
    Route::get('supir/{id}/gethistorymandor', [SupirController::class, 'getHistoryMandor']);
    Route::get('supir/{id}/getlisthistorymandor', [SupirController::class, 'getListHistoryMandor']);
    Route::get('supir/combo', [SupirController::class, 'combo']);
    Route::get('supir/field_length', [SupirController::class, 'fieldLength']);
    Route::get('supir/getsupirresign', [SupirController::class, 'getSupirResign']);
    Route::get('supir/getImage/{id}/{field}', [SupirController::class, 'getImage'])->whereNumber('id');
    Route::post('supir/upload_image/{id}', [SupirController::class, 'uploadImage'])->whereNumber('id');
    Route::get('supir/default', [SupirController::class, 'default']);
    Route::post('supir/{id}/approvalresign', [SupirController::class, 'approvalSupirResign'])->whereNumber('id');
    Route::post('supir/{id}/cekValidasi', [SupirController::class, 'cekValidasi'])->name('supir.cekValidasi')->whereNumber('id');

    Route::get('supir/export', [SupirController::class, 'export']);
    Route::get('supir/report', [SupirController::class, 'report']);


    Route::get('subkelompok/export', [SubKelompokController::class, 'export']);
    Route::get('subkelompok/field_length', [SubKelompokController::class, 'fieldLength']);
    Route::get('subkelompok/default', [SubKelompokController::class, 'default']);
    Route::post('subkelompok/{id}/cekValidasi', [SubKelompokController::class, 'cekValidasi'])->name('subkelompok.cekValidasi')->whereNumber('id');

    Route::get('supplier/export', [SupplierController::class, 'export']);
    Route::get('supplier/field_length', [SupplierController::class, 'fieldLength']);
    Route::get('supplier/default', [SupplierController::class, 'default']);
    Route::post('supplier/{id}/cekValidasi', [SupplierController::class, 'cekValidasi'])->name('supplier.cekValidasi')->whereNumber('id');
    Route::get('supplier/export', [SupplierController::class, 'export']);
    Route::post('supplier/approval', [SupplierController::class, 'approval']);
    Route::post('supplier/approvalTNL', [SupplierController::class, 'approvalTNL']);
    Route::get('supplier/report', [SupplierController::class, 'report']);


    Route::get('stok/default', [StokController::class, 'default']);
    Route::get('stok/field_length', [StokController::class, 'fieldLength']);
    Route::post('stok/approvalklaim', [StokController::class, 'approvalklaim']);
    Route::post('stok/approvalreuse', [StokController::class, 'approvalReuse']);
    Route::post('stok/{stok}/getvulkan', [StokController::class, 'getvulkan']);
    // Route::post('stok/{stok}/approvalklaim', [StokController::class, 'approvalklaim']);
    Route::post('stok/{id}/cekValidasi', [StokController::class, 'cekValidasi'])->name('stok.cekValidasi')->whereNumber('id');
    Route::get('stok/export', [StokController::class, 'export']);
    Route::get('stok/report', [StokController::class, 'report']);
    Route::post('stok/updatekonsolidasi', [StokController::class, 'updatekonsolidasi']);


    Route::get('penerima/export', [PenerimaController::class, 'export']);
    Route::get('penerima/field_length', [PenerimaController::class, 'fieldLength']);
    Route::get('penerima/default', [PenerimaController::class, 'default']);
    Route::post('penerima/{id}/cekValidasi', [PenerimaController::class, 'cekValidasi'])->name('penerima.cekValidasi')->whereNumber('id');

    Route::get('shipper/export', [ShipperController::class, 'export']);
    Route::get('shipper/field_length', [ShipperController::class, 'fieldLength']);
    Route::get('shipper/default', [ShipperController::class, 'default']);
    Route::post('shipper/{id}/cekValidasi', [ShipperController::class, 'cekValidasi'])->name('shipper.cekValidasi')->whereNumber('id');


    Route::get('statuscontainer/export', [StatusContainerController::class, 'export']);
    Route::get('statuscontainer/field_length', [StatusContainerController::class, 'fieldLength']);
    Route::get('statuscontainer/default', [StatusContainerController::class, 'default']);
    Route::post('statuscontainer/{id}/cekValidasi', [StatusContainerController::class, 'cekValidasi'])->name('statuscontainer.cekValidasi')->whereNumber('id');

    Route::get('penerimaantrucking/export', [PenerimaanTruckingController::class, 'export']);
    Route::get('penerimaantrucking/field_length', [PenerimaanTruckingController::class, 'fieldLength']);
    Route::get('penerimaantrucking/default', [PenerimaanTruckingController::class, 'default']);
    Route::post('penerimaantrucking/{id}/cekValidasi', [PenerimaanTruckingController::class, 'cekValidasi'])->name('penerimaantrucking.cekValidasi')->whereNumber('id');
    Route::get('penerimaantrucking/export', [PenerimaanTruckingController::class, 'export']);
    Route::get('penerimaantrucking/report', [PenerimaanTruckingController::class, 'report']);

    Route::get('pengeluarantrucking/export', [PengeluaranTruckingController::class, 'export']);
    Route::get('pengeluarantrucking/field_length', [PengeluaranTruckingController::class, 'fieldLength']);
    Route::get('pengeluarantrucking/default', [PengeluaranTruckingController::class, 'default']);
    Route::post('pengeluarantrucking/{id}/cekValidasi', [PengeluaranTruckingController::class, 'cekValidasi'])->name('pengeluarantrucking.cekValidasi')->whereNumber('id');
    Route::get('pengeluarantrucking/export', [PengeluaranTruckingController::class, 'export']);
    Route::get('pengeluarantrucking/report', [PengeluaranTruckingController::class, 'report']);


    Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    Route::get('jurnalumumheader/no_bukti', [JurnalUmumHeaderController::class, 'getNoBukti']);
    Route::get('jurnalumumheader/{id}/printreport', [JurnalUmumHeaderController::class, 'printReport'])->whereNumber('id');
    Route::post('jurnalumumheader/{id}/approval', [JurnalUmumHeaderController::class, 'approval'])->name('jurnalumumheader.approval')->whereNumber('id');
    Route::get('jurnalumumheader/combo', [JurnalUmumHeaderController::class, 'combo']);
    Route::post('jurnalumumheader/{id}/cekapproval', [JurnalUmumHeaderController::class, 'cekapproval'])->name('jurnalumumheader.cekapproval')->whereNumber('id');
    Route::get('jurnalumumheader/grid', [JurnalUmumHeaderController::class, 'grid']);
    Route::get('jurnalumumheader/{id}/export', [JurnalUmumHeaderController::class, 'export'])->name('jurnalumumheader.export')->whereNumber('id');
    Route::post('jurnalumumheader/{id}/cekvalidasiaksi', [JurnalUmumHeaderController::class, 'cekvalidasiaksi'])->whereNumber('id');
    Route::post('jurnalumumheader/copy', [JurnalUmumHeaderController::class, 'copy']);
    Route::post('jurnalumumheader/addrow', [JurnalUmumDetailController::class, 'addrow']);
    Route::resource('jurnalumumheader', JurnalUmumHeaderController::class)->whereNumber('jurnalumumheader');
    Route::get('jurnalumumdetail/getDetail', [JurnalUmumDetailController::class, 'getDetail']);
    Route::resource('jurnalumumdetail', JurnalUmumDetailController::class)->whereNumber('jurnalumumdetail');

    Route::get('penerimaantruckingheader/getpengembaliantitipan', [PenerimaanTruckingHeaderController::class, 'getDataPengembalianTitipan']);
    Route::get('penerimaantruckingheader/getpengembaliantitipan/{id}', [PenerimaanTruckingHeaderController::class, 'getDataPengembalianTitipanShow']);
    Route::get('penerimaantruckingheader/{id}/printreport', [PenerimaanTruckingHeaderController::class, 'printReport'])->whereNumber('id');
    Route::post('penerimaantruckingheader/addrow', [PenerimaanTruckingDetailController::class, 'addrow']);
    Route::get('penerimaantruckingheader/{id}/{aksi}/getpengembalianpinjaman', [PenerimaanTruckingHeaderController::class, 'getPengembalianPinjaman'])->name('pengeluarantruckingheader.getPengembalianPinjaman')->whereNumber('id');
    Route::get('penerimaantruckingheader/{id}/{aksi}/getpengembalianpinjamankaryawan', [PenerimaanTruckingHeaderController::class, 'getPengembalianPinjamanKaryawan'])->name('pengeluarantruckingheader.getPengembalianPinjamanKaryawan')->whereNumber('id');
    Route::get('penerimaantruckingheader/no_bukti', [PenerimaanTruckingHeaderController::class, 'getNoBukti']);
    Route::get('penerimaantruckingheader/{id}/export', [PenerimaanTruckingHeaderController::class, 'export'])->name('penerimaantruckingheader.export')->whereNumber('id');
    Route::get('penerimaantruckingheader/{supirId}/getpinjaman', [PenerimaanTruckingHeaderController::class, 'getPinjaman'])->whereNumber('supirId');
    Route::get('penerimaantruckingheader/{supirId}/getpinjamankaryawan', [PenerimaanTruckingHeaderController::class, 'getPinjamanKaryawan'])->whereNumber('karyawanId');
    Route::get('penerimaantruckingheader/combo', [PenerimaanTruckingHeaderController::class, 'combo']);
    Route::get('penerimaantruckingheader/grid', [PenerimaanTruckingHeaderController::class, 'grid']);
    Route::get('penerimaantruckingheader/field_length', [PenerimaanTruckingHeaderController::class, 'fieldLength']);
    Route::resource('penerimaantruckingdetail', PenerimaanTruckingDetailController::class)->whereNumber('penerimaantruckingdetail');


    Route::get('pengeluarantruckingheader/{id}/geteditotol', [PengeluaranTruckingHeaderController::class, 'getEditOtol'])->whereNumber('id');
    Route::get('pengeluarantruckingheader/getotol', [PengeluaranTruckingHeaderController::class, 'getOtol']);
    Route::get('pengeluarantruckingheader/{id}/geteditotok', [PengeluaranTruckingHeaderController::class, 'getEditOtok'])->whereNumber('id');
    Route::get('pengeluarantruckingheader/getotok', [PengeluaranTruckingHeaderController::class, 'getOtok']);
    Route::get('pengeluarantruckingheader/getinvoice', [PengeluaranTruckingHeaderController::class, 'getInvoice']);
    Route::get('pengeluarantruckingheader/{id}/geteditinvoice', [PengeluaranTruckingHeaderController::class, 'getEditInvoice'])->whereNumber('id');
    Route::get('pengeluarantruckingheader/{id}/printreport', [PengeluaranTruckingHeaderController::class, 'printReport'])->whereNumber('id');
    Route::post('pengeluarantruckingheader/addrow', [PengeluaranTruckingDetailController::class, 'addrow']);
    Route::get('pengeluarantruckingheader/getdeposito', [PengeluaranTruckingHeaderController::class, 'getdeposito'])->name('pengeluarantruckingheader.getdeposito');
    Route::get('pengeluarantruckingheader/getdepositokaryawan', [PengeluaranTruckingHeaderController::class, 'getDepositoKaryawan']);
    Route::get('pengeluarantruckingheader/{id}/{aksi}/gettarikdeposito', [PengeluaranTruckingHeaderController::class, 'getTarikDeposito'])->name('pengeluarantruckingheader.gettarikdeposito')->whereNumber('id');
    Route::get('pengeluarantruckingheader/{id}/{aksi}/gettarikdepositokaryawan', [PengeluaranTruckingHeaderController::class, 'getTarikDepositoKaryawan'])->whereNumber('id');
    Route::get('pengeluarantruckingheader/getbiayalapangan', [PengeluaranTruckingHeaderController::class, 'getbiayalapangan'])->name('pengeluarantruckingheader.getbiayalapangan');
    Route::get('pengeluarantruckingheader/getpelunasan', [PengeluaranTruckingHeaderController::class, 'getpelunasan'])->name('pengeluarantruckingheader.getpelunasan');
    Route::get('pengeluarantruckingheader/{id}/{aksi}/geteditpelunasan', [PengeluaranTruckingHeaderController::class, 'getEditPelunasan'])->name('pengeluarantruckingheader.geteditpelunasan')->whereNumber('id');
    Route::get('pengeluarantruckingheader/no_bukti', [PengeluaranTruckingHeaderController::class, 'getNoBukti']);
    Route::get('pengeluarantruckingheader/combo', [PengeluaranTruckingHeaderController::class, 'combo']);
    Route::get('pengeluarantruckingheader/grid', [PengeluaranTruckingHeaderController::class, 'grid']);
    Route::get('pengeluarantruckingheader/field_length', [PengeluaranTruckingHeaderController::class, 'fieldLength']);
    Route::get('pengeluarantruckingheader/{id}/export', [PengeluaranTruckingHeaderController::class, 'export'])->name('pengeluarantruckingheader.export')->whereNumber('id');
    Route::resource('pengeluarantruckingheader', PengeluaranTruckingHeaderController::class)->whereNumber('pengeluarantruckingheader');


    // Route::post('bukapenerimaanstok/{id}/updatetanggalbatas', [BukaPenerimaanStokController::class, 'updateTanggalBatas']);
    Route::post('bukapenerimaanstok/updatetanggalbatas', [BukaPenerimaanStokController::class, 'updateTanggalBatas']);
    Route::apiResource('bukapenerimaanstok', BukaPenerimaanStokController::class)->whereNumber('bukapenerimaanstok');


    // Route::post('bukapengeluaranstok/{id}/updatetanggalbatas', [BukaPengeluaranStokController::class, 'updateTanggalBatas']);
    Route::post('bukapengeluaranstok/updatetanggalbatas', [BukaPengeluaranStokController::class, 'updateTanggalBatas']);
    Route::apiResource('bukapengeluaranstok', BukaPengeluaranStokController::class)->whereNumber('bukapengeluaranstok');


    Route::get('penerimaanstok/field_length', [PenerimaanStokController::class, 'fieldLength']);
    Route::get('penerimaanstok/export', [PenerimaanStokController::class, 'export']);
    Route::get('penerimaanstok/default', [PenerimaanStokController::class, 'default']);
    Route::post('penerimaanstok/{id}/cekValidasi', [PenerimaanStokController::class, 'cekValidasi'])->name('penerimaanstok.cekValidasi')->whereNumber('id');


    Route::post('penerimaanstokheader/addrow', [PenerimaanStokDetailController::class, 'addrow']);
    Route::post('penerimaanstokheader/deleterow', [PenerimaanStokDetailController::class, 'deleterow']);
    Route::get('penerimaanstokheader/field_length', [PenerimaanStokHeaderController::class, 'fieldLength']);
    Route::get('penerimaanstokheader/{id}/printreport', [PenerimaanStokHeaderController::class, 'printReport'])->whereNumber('id');
    Route::post('penerimaanstokheader/{id}/cekvalidasi', [PenerimaanStokHeaderController::class, 'cekValidasi'])->name('penerimaanstokheader.cekValidasi')->whereNumber('id');
    Route::post('penerimaanstokheader/{id}/approvaledit', [PenerimaanStokHeaderController::class, 'approvalEdit']);
    Route::post('penerimaanstokheader/{id}/approvaleditketerangan', [PenerimaanStokHeaderController::class, 'approvalEditKeterangan']);
    Route::get('penerimaanstokheader/{id}/pengeluaranstoknobukti', [PenerimaanStokHeaderController::class, 'getPengeluaranStok'])->name('penerimaanstokheader.pengeluaranstoknobukti')->whereNumber('id');
    Route::get('penerimaanstokheader/{id}/detailspbp', [PenerimaanStokHeaderController::class, 'getDetailSPBP']);
    Route::apiResource('penerimaanstokheader', PenerimaanStokHeaderController::class)->whereNumber('penerimaanstokheader');
    Route::get('penerimaanstokdetail/hutang', [PenerimaanStokDetailController::class, 'hutang']);
    Route::apiResource('penerimaanstokdetail', PenerimaanStokDetailController::class)->whereNumber('penerimaanstokdetail');


    Route::get('pengeluaranstok/field_length', [PengeluaranStokController::class, 'fieldLength']);
    Route::get('pengeluaranstok/export', [PengeluaranStokController::class, 'export']);
    Route::get('pengeluaranstok/default', [PengeluaranStokController::class, 'default']);
    Route::post('pengeluaranstok/{id}/cekValidasi', [PengeluaranStokController::class, 'cekValidasi'])->name('pengeluaranstok.cekValidasi')->whereNumber('id');


    Route::get('pengeluaranstokheader/{id}/printreport', [PengeluaranStokHeaderController::class, 'printReport'])->whereNumber('id');
    Route::post('pengeluaranstokheader/{id}/cekvalidasi', [PengeluaranStokHeaderController::class, 'cekValidasi'])->name('pengeluaranstokheader.cekValidasi')->whereNumber('id');
    Route::post('pengeluaranstokheader/{id}/approvaledit', [PengeluaranStokHeaderController::class, 'approvalEdit']);
    Route::post('pengeluaranstokheader/{id}/approvaleditketerangan', [PengeluaranStokHeaderController::class, 'approvalEditKeterangan']);
    Route::post('pengeluaranstokheader/addrow', [PengeluaranStokDetailController::class, 'addrow']);

    Route::get('reminderservice/index', [ReminderServiceController::class, 'index']);
    Route::get('reminderservice', [ReminderServiceController::class, 'index']);

    Route::get('pengeluaranstok/field_length', [PengeluaranStokController::class, 'fieldLength']);
    Route::post('invoiceextraheader/addrow', [InvoiceExtraDetailController::class, 'addrow']);
    Route::post('invoiceextraheader/{id}/approval', [InvoiceExtraHeaderController::class, 'approval'])->whereNumber('id');
    Route::get('invoiceextraheader/{id}/printreport', [InvoiceExtraHeaderController::class, 'printReport'])->whereNumber('id');
    Route::post('invoiceextraheader/approval', [InvoiceExtraHeaderController::class, 'approval']);
    Route::get('invoiceextraheader/{id}/export', [InvoiceExtraHeaderController::class, 'export'])->name('invoiceextraheader.export')->whereNumber('id');
    Route::resource('invoiceextraheader', InvoiceExtraHeaderController::class)->whereNumber('invoiceextraheader');
    Route::resource('invoiceextradetail', InvoiceExtraDetailController::class)->whereNumber('invoiceextradetail');

    Route::post('invoicechargegandenganheader/{id}/cekvalidasi', [InvoiceChargeGandenganHeaderController::class, 'cekvalidasi'])->name('invoicechargegandenganheader.cekvalidasi')->whereNumber('id');
    Route::post('invoicechargegandenganheader/{id}/cekvalidasiAksi', [InvoiceChargeGandenganHeaderController::class, 'cekvalidasiAksi'])->name('invoicechargegandenganheader.cekvalidasiAksi')->whereNumber('id');
    Route::get('invoicechargegandenganheader/{id}/getinvoicegandengan', [InvoiceChargeGandenganHeaderController::class, 'getinvoicegandengan'])->name('invoicechargegandenganheader.getinvoicegandengan')->whereNumber('id');
    Route::get('invoicechargegandenganheader/{id}/export', [InvoiceChargeGandenganHeaderController::class, 'export'])->name('invoicechargegandenganheader.export')->whereNumber('id');
    Route::get('invoicechargegandenganheader/{id}/printreport', [InvoiceChargeGandenganHeaderController::class, 'printReport'])->whereNumber('id');
    Route::resource('invoicechargegandenganheader', InvoiceChargeGandenganHeaderController::class)->whereNumber('invoicechargegandenganheader');
    Route::resource('invoicechargegandengandetail', InvoiceChargeGandenganDetailController::class)->whereNumber('invoicechargegandengandetail');

    Route::get('piutangheader/{id}/printreport', [PiutangHeaderController::class, 'printReport'])->whereNumber('id');
    Route::post('piutangheader/addrow', [PiutangDetailController::class, 'addrow']);
    Route::get('piutangheader/no_bukti', [PiutangHeaderController::class, 'getNoBukti']);
    Route::get('piutangheader/grid', [PiutangHeaderController::class, 'grid']);
    Route::get('piutangheader/field_length', [PiutangHeaderController::class, 'fieldLength']);
    Route::get('piutangheader/{id}/export', [PiutangHeaderController::class, 'export'])->name('piutangheader.export')->whereNumber('id');
    Route::apiResource('piutangheader', PiutangHeaderController::class)->parameters(['piutangheader' => 'piutangHeader'])->whereNumber('piutangHeader');
    Route::get('piutangdetail/history', [PiutangDetailController::class, 'history']);
    Route::apiResource('piutangdetail', PiutangDetailController::class)->whereNumber('piutangdetail');

    Route::get('hutangheader/{id}/printreport', [HutangHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('hutangheader/{id}/export', [HutangHeaderController::class, 'export'])->name('hutangheader.export')->whereNumber('id');
    Route::get('hutangheader/no_bukti', [HutangHeaderController::class, 'getNoBukti']);
    Route::post('hutangheader/addrow', [HutangDetailController::class, 'addrow']);
    Route::post('hutangheader/approval', [HutangHeaderController::class, 'approval']);
    Route::get('hutangheader/combo', [HutangHeaderController::class, 'combo']);
    Route::get('hutangheader/grid', [HutangHeaderController::class, 'grid']);
    Route::get('hutangheader/field_length', [HutangHeaderController::class, 'fieldLength']);
    Route::resource('hutangheader', HutangHeaderController::class)->whereNumber('hutangheader');
    Route::get('hutangdetail/history', [HutangDetailController::class, 'history']);
    Route::resource('hutangdetail', HutangDetailController::class)->whereNumber('hutangdetail');

    Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    Route::get('pelunasanpiutangheader/no_bukti', [PelunasanPiutangHeaderController::class, 'getNoBukti']);
    Route::get('pelunasanpiutangheader/default', [PelunasanPiutangHeaderController::class, 'default']);
    Route::get('pelunasanpiutangheader/combo', [PelunasanPiutangHeaderController::class, 'combo']);
    Route::get('pelunasanpiutangheader/{id}/printreport', [PelunasanPiutangHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('pelunasanpiutangheader/{id}/export', [PelunasanPiutangHeaderController::class, 'export'])->name('pelunasanpiutangheader.export')->whereNumber('id');
    Route::get('pelunasanpiutangheader/{id}/getpiutang', [PelunasanPiutangHeaderController::class, 'getpiutang'])->name('pelunasanpiutangheader.getpiutang')->whereNumber('id');
    Route::get('pelunasanpiutangheader/{id}/{agenid}/getPelunasanPiutang', [PelunasanPiutangHeaderController::class, 'getPelunasanPiutang'])->whereNumber('id');
    Route::get('pelunasanpiutangheader/{id}/{agenid}/getDeletePelunasanPiutang', [PelunasanPiutangHeaderController::class, 'getDeletePelunasanPiutang'])->whereNumber('id');
    Route::get('pelunasanpiutangheader/grid', [PelunasanPiutangHeaderController::class, 'grid']);
    Route::get('pelunasanpiutangheader/field_length', [PelunasanPiutangHeaderController::class, 'fieldLength']);
    Route::resource('pelunasanpiutangheader', PelunasanPiutangHeaderController::class)->whereNumber('pelunasanpiutangheader');
    Route::get('pelunasanpiutangdetail/getPelunasan', [PelunasanPiutangDetailController::class, 'getPelunasan']);
    Route::resource('pelunasanpiutangdetail', PelunasanPiutangDetailController::class)->whereNumber('pelunasanpiutangdetail');

    Route::get('hutangbayarheader/{id}/printreport', [HutangBayarHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('hutangbayarheader/no_bukti', [HutangBayarHeaderController::class, 'getNoBukti']);
    Route::get('hutangbayarheader/field_length', [HutangBayarHeaderController::class, 'fieldLength']);
    Route::get('hutangbayarheader/combo', [HutangBayarHeaderController::class, 'combo']);
    Route::get('hutangbayarheader/{id}/getHutang', [HutangBayarHeaderController::class, 'getHutang'])->name('hutangbayarheader.getHutang')->whereNumber('id');
    Route::post('hutangbayarheader/approval', [HutangBayarHeaderController::class, 'approval']);
    Route::get('hutangbayarheader/{id}/export', [HutangBayarHeaderController::class, 'export'])->name('hutangbayarheader.export')->whereNumber('id');
    Route::post('hutangbayarheader/{id}/cekapproval', [HutangBayarHeaderController::class, 'cekapproval'])->name('hutangbayarheader.cekapproval')->whereNumber('id');
    Route::get('hutangbayarheader/{id}/{fieldid}/getPembayaran', [HutangBayarHeaderController::class, 'getPembayaran'])->whereNumber('id');
    Route::get('hutangbayarheader/grid', [HutangBayarHeaderController::class, 'grid']);
    Route::resource('hutangbayarheader', HutangBayarHeaderController::class)->whereNumber('hutangbayarheader');
    Route::resource('hutangbayardetail', HutangBayarDetailController::class)->whereNumber('hutangbayardetail');

    Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    Route::get('serviceinheader/no_bukti', [ServiceInHeaderController::class, 'getNoBukti']);
    Route::get('serviceinheader/combo', [ServiceInHeaderController::class, 'combo']);
    Route::get('serviceinheader/grid', [ServiceInHeaderController::class, 'grid']);
    Route::get('serviceinheader/field_length', [ServiceInHeaderController::class, 'fieldLength']);
    Route::get('serviceinheader/default', [ServiceInHeaderController::class, 'default']);

    Route::get('serviceinheader/{id}/printreport', [ServiceInHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('serviceinheader/{id}/export', [ServiceInHeaderController::class, 'export'])->name('serviceinheader.export')->whereNumber('id');
    Route::post('serviceinheader/addrow', [ServiceInDetailController::class, 'addrow']);
    Route::resource('serviceinheader', ServiceInHeaderController::class)->parameter('serviceinheader', 'serviceInHeader')->whereNumber('serviceinheader');
    Route::resource('serviceindetail', ServiceInDetailController::class)->whereNumber('serviceindetail');


    Route::get('serviceoutheader/combo', [ServiceOutHeaderController::class, 'combo']);
    Route::get('serviceoutheader/field_length', [ServiceOutHeaderController::class, 'fieldLength']);
    Route::get('serviceoutheader/{id}/printreport', [ServiceOutHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('serviceoutheader/{id}/export', [ServiceOutHeaderController::class, 'export'])->name('serviceoutheader.export')->whereNumber('id');
    Route::post('serviceoutheader/addrow', [ServiceOutDetailController::class, 'addrow']);
    Route::resource('serviceoutheader', ServiceOutHeaderController::class)->whereNumber('serviceoutheader');
    Route::resource('serviceoutdetail', ServiceOutDetailController::class)->whereNumber('serviceoutdetail');

    Route::post('kasgantungheader/addrow', [KasGantungDetailController::class, 'addrow']);
    Route::get('kasgantungheader/{id}/printreport', [KasGantungHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('kasgantungheader/combo', [KasGantungHeaderController::class, 'combo']);
    Route::get('kasgantungheader/grid', [KasGantungHeaderController::class, 'grid']);
    Route::get('kasgantungheader/default', [KasGantungHeaderController::class, 'default']);
    Route::get('kasgantungheader/{id}/export', [KasGantungHeaderController::class, 'export'])->name('kasgantungheader.export')->whereNumber('id');
    Route::get('kasgantungheader/field_length', [KasGantungHeaderController::class, 'fieldLength']);
    Route::resource('kasgantungheader', KasGantungHeaderController::class)->whereNumber('kasgantungheader');
    Route::resource('kasgantungdetail', KasGantungDetailController::class)->whereNumber('kasgantungdetail');

    Route::get('gajisupirheader/{id}/printreport', [GajiSupirHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('gajisupirheader/no_bukti', [GajiSupirHeaderController::class, 'getNoBukti']);
    Route::get('gajisupirheader/grid', [GajiSupirHeaderController::class, 'grid']);
    Route::get('gajisupirheader/field_length', [GajiSupirHeaderController::class, 'fieldLength']);
    Route::get('gajisupirheader/getAbsensi', [GajiSupirHeaderController::class, 'getAbsensi']);
    Route::get('gajisupirheader/getTrip', [GajiSupirHeaderController::class, 'getTrip']);
    Route::get('gajisupirheader/getpinjsemua', [GajiSupirHeaderController::class, 'getPinjSemua']);
    Route::get('gajisupirheader/{id}/{aksi}/editpinjsemua', [GajiSupirHeaderController::class, 'getEditPinjSemua'])->whereNumber('id');
    Route::get('gajisupirheader/{supirId}/getpinjpribadi', [GajiSupirHeaderController::class, 'getPinjPribadi'])->whereNumber('supirId');
    Route::get('gajisupirheader/{id}/{supirId}/{aksi}/editpinjpribadi', [GajiSupirHeaderController::class, 'getEditPinjPribadi'])->whereNumber('id')->whereNumber('supirId');
    Route::post('gajisupirheader/noEdit', [GajiSupirHeaderController::class, 'noEdit']);
    Route::post('gajisupirheader/getuangjalan', [GajiSupirHeaderController::class, 'getUangJalan']);
    Route::get('gajisupirheader/{gajiId}/getEditTrip', [GajiSupirHeaderController::class, 'getEditTrip'])->whereNumber('gajiId');
    Route::get('gajisupirheader/{gajiId}/getEditAbsensi', [GajiSupirHeaderController::class, 'getEditAbsensi'])->whereNumber('gajiId');
    Route::get('gajisupirheader/{id}/export', [GajiSupirHeaderController::class, 'export'])->whereNumber('id');
    Route::resource('gajisupirheader', GajiSupirHeaderController::class)->whereNumber('gajisupirheader');
    Route::resource('gajisupirdetail', GajiSupirDetailController::class)->whereNumber('gajisupirdetail');

    Route::post('notakreditheader/addrow', [NotaKreditDetailController::class, 'addrow']);
    Route::get('notakreditheader/default', [NotaKreditHeaderController::class, 'default']);
    Route::get('notakreditheader/{id}/printreport', [NotaKreditHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('notakreditheader/field_length', [NotaKreditHeaderController::class, 'fieldLength']);
    Route::get('notakreditheader/{id}/export', [NotaKreditHeaderController::class, 'export'])->name('notakreditheader.export')->whereNumber('id');
    Route::get('notakreditheader/{id}/getpelunasan', [NotaKreditHeaderController::class, 'getPelunasan'])->whereNumber('id');
    Route::get('notakreditheader/{id}/getnotakredit', [NotaKreditHeaderController::class, 'getNotaKredit'])->whereNumber('id');
    Route::post('notakreditheader/approval', [NotaKreditHeaderController::class, 'approval']);
    Route::get('notakreditheader/export', [NotaKreditHeaderController::class, 'export']);
    Route::resource('notakreditheader', NotaKreditHeaderController::class)->whereNumber('notakreditheader');
    Route::resource('notakreditdetail', NotaKreditDetailController::class)->whereNumber('notakreditdetail');

    Route::post('notadebetheader/addrow', [NotaDebetDetailController::class, 'addrow']);
    Route::get('notadebetheader/{id}/printreport', [NotaDebetHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('notadebetheader/{id}/export', [NotaDebetHeaderController::class, 'export'])->name('notadebetheader.export')->whereNumber('id');
    Route::get('notadebetheader/field_length', [NotaDebetHeaderController::class, 'fieldLength']);
    Route::get('notadebetheader/default', [NotaDebetHeaderController::class, 'default']);
    Route::get('notadebetheader/{id}/getpelunasan', [NotaDebetHeaderController::class, 'getPelunasan'])->whereNumber('id');
    Route::get('notadebetheader/{id}/getnotadebet', [NotaDebetHeaderController::class, 'getNotaDebet'])->whereNumber('id');
    Route::post('notadebetheader/approval', [NotaDebetHeaderController::class, 'approval']);
    Route::get('notadebetheader/export', [NotaDebetHeaderController::class, 'export']);
    Route::resource('notadebetheader', NotaDebetHeaderController::class)->whereNumber('notadebetheader');
    Route::resource('notadebetdetail', NotaDebetDetailController::class)->whereNumber('notadebet_detail');

    Route::get('rekappengeluaranheader/field_length', [RekapPengeluaranHeaderController::class, 'fieldLength']);
    Route::get('rekappengeluaranheader/{id}/printreport', [RekapPengeluaranHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('rekappengeluaranheader/{id}/export', [RekapPengeluaranHeaderController::class, 'export'])->name('rekappengeluaranheader.export')->whereNumber('id');
    Route::get('rekappengeluaranheader/{id}/getrekappengeluaran', [RekapPengeluaranHeaderController::class, 'getRekapPengeluaran'])->whereNumber('id');
    Route::post('rekappengeluaranheader/approval', [RekapPengeluaranHeaderController::class, 'approval'])->whereNumber('id');
    Route::get('gandengan/default', [GandenganController::class, 'default']);
    Route::resource('rekappengeluaranheader', RekapPengeluaranHeaderController::class)->whereNumber('rekappengeluaranheader');
    Route::resource('rekappengeluarandetail', RekapPengeluaranDetailController::class)->whereNumber('rekappengeluarandetail');

    Route::get('rekappenerimaanheader/field_length', [RekapPenerimaanHeaderController::class, 'fieldLength']);
    Route::get('rekappenerimaanheader/{id}/printreport', [RekapPenerimaanHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('rekappenerimaanheader/{id}/export', [RekapPenerimaanHeaderController::class, 'export'])->name('rekappenerimaanheader.export')->whereNumber('id');
    Route::get('rekappenerimaanheader/{id}/getrekappenerimaan', [RekapPenerimaanHeaderController::class, 'getRekapPenerimaan'])->whereNumber('id');
    Route::post('rekappenerimaanheader/approval', [RekapPenerimaanHeaderController::class, 'approval'])->whereNumber('id');
    Route::resource('rekappenerimaanheader', RekapPenerimaanHeaderController::class)->whereNumber('rekappenerimaanheader');
    Route::resource('rekappenerimaandetail', RekapPenerimaanDetailController::class)->whereNumber('rekappenerimaandetail');

    Route::get('pengembaliankasgantungheader/field_length', [PengembalianKasGantungHeaderController::class, 'fieldLength']);
    Route::get('pengembaliankasgantungheader/getkasgantung', [PengembalianKasGantungHeaderController::class, 'getKasGantung']);
    Route::get('pengembaliankasgantungheader/{id}/{aksi}/getpengembalian', [PengembalianKasGantungHeaderController::class, 'getPengembalian'])->whereNumber('id');
    Route::get('pengembaliankasgantungheader/default', [PengembalianKasGantungHeaderController::class, 'default']);
    Route::get('pengembaliankasgantungheader/{id}/printreport', [PengembalianKasGantungHeaderController::class, 'printReport'])->whereNumber('id');
    Route::post('pengembaliankasgantungheader/addrow', [PengembalianKasGantungDetailController::class, 'addrow']);
    Route::get('pengembaliankasgantungheader/{id}/export', [PengembalianKasGantungHeaderController::class, 'export'])->name('pengembaliankasgantungheader.export')->whereNumber('id');
    Route::resource('pengembaliankasgantungheader', PengembalianKasGantungHeaderController::class)->whereNumber('pengembaliankasgantungheader');
    Route::resource('pengembaliankasgantungdetail', PengembalianKasGantungDetailController::class)->whereNumber('pengembaliankasgantungdetail');

    Route::post('pengembaliankasbankheader/{id}/approval', [PengembalianKasBankHeaderController::class, 'approval'])->name('pengembaliankasbankheader.approval')->whereNumber('id');
    Route::get('pengembaliankasbankheader/no_bukti', [PengembalianKasBankHeaderController::class, 'getNoBukti']);
    Route::get('pengembaliankasbankheader/field_length', [PengembalianKasBankHeaderController::class, 'fieldLength']);
    Route::get('pengembaliankasbankheader/combo', [PengembalianKasBankHeaderController::class, 'combo']);
    Route::get('pengembaliankasbankheader/default', [PengembalianKasBankHeaderController::class, 'default']);
    Route::post('pengembaliankasbankheader/addrow', [PengembalianKasBankDetailController::class, 'addrow']);
    Route::post('pengembaliankasbankheader/{id}/approval', [PengembalianKasBankHeaderController::class, 'approval'])->whereNumber('id');
    Route::post('pengembaliankasbankheader/{id}/cekvalidasi', [PengembalianKasBankHeaderController::class, 'cekvalidasi'])->whereNumber('id');
    Route::get('pengembaliankasbankheader/grid', [PengembalianKasBankHeaderController::class, 'grid']);
    Route::resource('pengembaliankasbankheader', PengembalianKasBankHeaderController::class)->whereNumber('pengembaliankasbankheader');
    Route::resource('pengembaliankasbankdetail', PengembalianKasBankDetailController::class)->whereNumber('pengembaliankasbankdetail');

    Route::get('prosesgajisupirheader/default', [ProsesGajiSupirHeaderController::class, 'default']);
    Route::get('prosesgajisupirheader/no_bukti', [ProsesGajiSupirHeaderController::class, 'getNoBukti']);
    Route::get('prosesgajisupirheader/grid', [ProsesGajiSupirHeaderController::class, 'grid']);
    Route::get('prosesgajisupirheader/field_length', [ProsesGajiSupirHeaderController::class, 'fieldLength']);
    Route::get('prosesgajisupirheader/getRic', [ProsesGajiSupirHeaderController::class, 'getRic']);
    Route::post('prosesgajisupirheader/hitungNominal', [ProsesGajiSupirHeaderController::class, 'hitungNominal']);
    Route::post('prosesgajisupirheader/noEdit', [ProsesGajiSupirHeaderController::class, 'noEdit']);
    Route::get('prosesgajisupirheader/{id}/export', [ProsesGajiSupirHeaderController::class, 'export'])->name('prosesgajisupirheader.export')->whereNumber('id');
    Route::get('prosesgajisupirheader/{id}/getEdit', [ProsesGajiSupirHeaderController::class, 'getEdit'])->whereNumber('id');
    Route::get('prosesgajisupirheader/{id}/printreport', [ProsesGajiSupirHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('prosesgajisupirheader/{dari}/{sampai}/getAllData', [ProsesGajiSupirHeaderController::class, 'getAllData']);
    Route::resource('prosesgajisupirheader', ProsesGajiSupirHeaderController::class)->whereNumber('prosesgajisupirheader');
    Route::resource('prosesgajisupirdetail', ProsesGajiSupirDetailController::class)->whereNumber('prosesgajisupirdetail');

    Route::get('invoiceheader/{id}/printreport', [InvoiceHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('invoiceheader/grid', [InvoiceHeaderController::class, 'grid']);
    Route::get('invoiceheader/field_length', [InvoiceHeaderController::class, 'fieldLength']);
    Route::get('invoiceheader/{id}/getEdit', [InvoiceHeaderController::class, 'getEdit'])->whereNumber('id');
    Route::get('invoiceheader/{id}/getAllEdit', [InvoiceHeaderController::class, 'getAllEdit'])->whereNumber('id');
    Route::get('invoiceheader/getSP', [InvoiceHeaderController::class, 'getSP']);
    Route::post('invoiceheader/approval', [InvoiceHeaderController::class, 'approval']);
    Route::get('invoiceheader/{id}/export', [InvoiceHeaderController::class, 'export'])->name('invoiceheader.export')->whereNumber('id');
    Route::resource('invoiceheader', InvoiceHeaderController::class)->whereNumber('invoiceheader');

    Route::resource('tutupbuku', TutupBukuController::class)->whereNumber('tutupbuku');
    Route::resource('approvalopname', ApprovalOpnameController::class)->whereNumber('approvalopname');


    Route::get('suratpengantar/rekapcustomer', [SuratPengantarController::class, 'rekapcustomer']);
    Route::get('absentrado/rekapabsentrado', [AbsenTradoController::class, 'rekapabsentrado']);
    Route::get('suratpengantar/combo', [SuratPengantarController::class, 'combo']);
    Route::post('suratpengantar/cekUpahSupir', [SuratPengantarController::class, 'cekUpahSupir']);
    Route::get('suratpengantar/export', [SuratPengantarController::class, 'export']);
    Route::get('suratpengantar/{id}/getTarifOmset', [SuratPengantarController::class, 'getTarifOmset'])->whereNumber('id');
    Route::post('suratpengantar/batalmuat', [SuratPengantarController::class, 'approvalBatalMuat'])->whereNumber('id');
    Route::post('suratpengantar/edittujuan', [SuratPengantarController::class, 'approvalEditTujuan'])->whereNumber('id');
    Route::post('suratpengantar/titipanemkl', [SuratPengantarController::class, 'approvalTitipanEmkl']);
    Route::get('suratpengantar/{id}/getOrderanTrucking', [SuratPengantarController::class, 'getOrderanTrucking'])->whereNumber('id');
    Route::get('suratpengantar/getGaji/{dari}/{sampai}/{container}/{statuscontainer}', [SuratPengantarController::class, 'getGaji']);

    Route::get('penerimaanheader/{id}/printreport', [PenerimaanHeaderController::class, 'printReport'])->whereNumber('id');
    Route::post('penerimaanheader/{id}/editcoa', [PenerimaanHeaderController::class, 'editCoa']);
    Route::post('penerimaanheader/addrow', [PenerimaanDetailController::class, 'addrow']);
    Route::post('penerimaanheader/{id}/approval', [PenerimaanHeaderController::class, 'approval'])->name('penerimaanheader.approval')->whereNumber('id');
    Route::get('penerimaanheader/no_bukti', [PenerimaanHeaderController::class, 'getNoBukti']);
    Route::get('penerimaanheader/combo', [PenerimaanHeaderController::class, 'combo']);
    Route::get('penerimaanheader/{id}/export', [PenerimaanHeaderController::class, 'export'])->name('penerimaanheader.export')->whereNumber('id');
    Route::get('penerimaanheader/{id}/tarikPelunasan', [PenerimaanHeaderController::class, 'tarikPelunasan'])->whereNumber('id');
    Route::post('penerimaanheader/approval', [PenerimaanHeaderController::class, 'approval']);
    Route::get('penerimaanheader/{id}/{table}/getPelunasan', [PenerimaanHeaderController::class, 'getPelunasan'])->whereNumber('id');
    Route::get('penerimaanheader/grid', [PenerimaanHeaderController::class, 'grid']);
    Route::get('penerimaanheader/default', [PenerimaanHeaderController::class, 'default']);
    Route::resource('penerimaanheader', PenerimaanHeaderController::class)->whereNumber('penerimaanheader');
    Route::resource('penerimaandetail', PenerimaanDetailController::class)->whereNumber('penerimaandetail');

    // Route::get('running_number', [Controller::class, 'getRunningNumber'])->name('running_number');
    // Route::post('penerimaan/{id}/approval', [PenerimaanHeaderController::class, 'approval'])->name('penerimaan.approval');
    // Route::get('penerimaan/no_bukti', [PenerimaanHeaderController::class, 'getNoBukti']);
    // Route::get('penerimaan/combo', [PenerimaanHeaderController::class, 'combo']);
    // Route::get('penerimaan/grid', [PenerimaanHeaderController::class, 'grid']);
    // Route::resource('penerimaan', PenerimaanHeaderController::class);

    Route::get('upahritasi/combo', [UpahRitasiController::class, 'combo']);
    Route::get('upahritasi/default', [UpahRitasiController::class, 'default']);
    Route::get('upahritasi/field_length', [UpahRitasiController::class, 'fieldLength']);
    Route::get('upahritasi/export', [UpahRitasiController::class, 'export']);
    Route::post('upahritasi/import', [UpahRitasiController::class, 'import']);
    Route::post('upahritasi/{id}/cekValidasi', [UpahRitasiController::class, 'cekValidasi'])->name('upahritasi.cekValidasi')->whereNumber('id');
    Route::resource('upahritasi', UpahRitasiController::class)->whereNumber('upahritasi');

    Route::get('upahritasirincian/setuprow', [UpahRitasiRincianController::class, 'setUpRow']);
    Route::get('upahritasirincian/setuprowshow/{id}', [UpahRitasiRincianController::class, 'setUpRowExcept'])->whereNumber('id');
    Route::resource('upahritasirincian', UpahRitasiRincianController::class)->whereNumber('upahritasirincian');

    Route::get('ritasi/combo', [RitasiController::class, 'combo']);
    Route::post('ritasi/{id}/cekvalidasi', [RitasiController::class, 'cekvalidasi'])->name('ritasi.cekValidasi')->whereNumber('id');
    Route::get('ritasi/field_length', [RitasiController::class, 'fieldLength']);
    Route::get('ritasi/default', [RitasiController::class, 'default']);
    Route::get('ritasi/export', [RitasiController::class, 'export']);
    Route::resource('ritasi', RitasiController::class)->whereNumber('ritasi');

    //pengeluaran
    Route::get('pengeluaranheader/{id}/printreport', [PengeluaranHeaderController::class, 'printReport'])->whereNumber('id');
    Route::post('pengeluaranheader/addrow', [PengeluaranDetailController::class, 'addrow']);
    Route::post('pengeluaranheader/{id}/editcoa', [PengeluaranHeaderController::class, 'editCoa']);
    Route::post('pengeluaranheader/{id}/approval', [PengeluaranHeaderController::class, 'approval'])->name('pengeluaranheader.approval')->whereNumber('id');
    Route::get('pengeluaranheader/no_bukti', [PengeluaranHeaderController::class, 'getNoBukti']);
    Route::get('pengeluaranheader/field_length', [PengeluaranHeaderController::class, 'fieldLength']);
    Route::get('pengeluaranheader/combo', [PengeluaranHeaderController::class, 'combo']);
    Route::get('pengeluaranheader/grid', [PengeluaranHeaderController::class, 'grid']);
    Route::get('pengeluaranheader/default', [PengeluaranHeaderController::class, 'default']);
    Route::post('pengeluaranheader/approval', [PengeluaranHeaderController::class, 'approval']);
    Route::post('pengeluaranheader/editingat', [PengeluaranHeaderController::class, 'editingat']);
    Route::get('pengeluaranheader/{id}/export', [PengeluaranHeaderController::class, 'export'])->name('pengeluaranheader.export')->whereNumber('id');
    Route::resource('pengeluaranheader', PengeluaranHeaderController::class)->whereNumber('pengeluaranheader');
    Route::resource('pengeluarandetail', PengeluaranDetailController::class)->whereNumber('pengeluarandetail');

    Route::post('penerimaangiroheader/approvalkacab', [PenerimaanGiroHeaderController::class, 'approvalKacab']);
    Route::post('penerimaangiroheader/editingat', [PenerimaanGiroHeaderController::class, 'editingat']);
    Route::post('penerimaangiroheader/addrow', [PenerimaanGiroDetailController::class, 'addrow']);
    Route::post('penerimaangiroheader/{id}/approval', [PenerimaanGiroHeaderController::class, 'approval'])->name('penerimaangiroheader.approval')->whereNumber('id');
    Route::get('penerimaangiroheader/{id}/printreport', [PenerimaanGiroHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('penerimaangiroheader/field_length', [PenerimaanGiroHeaderController::class, 'fieldLength']);
    Route::get('penerimaangiroheader/combo', [PenerimaanGiroHeaderController::class, 'combo']);
    Route::get('penerimaangiroheader/{id}/export', [PenerimaanGiroHeaderController::class, 'export'])->name('penerimaangiroheader.export')->whereNumber('id');
    Route::get('penerimaangiroheader/grid', [PenerimaanGiroHeaderController::class, 'grid']);
    Route::post('penerimaangiroheader/approval', [PenerimaanGiroHeaderController::class, 'approval']);
    Route::get('penerimaangiroheader/get', [PenerimaanGiroHeaderController::class, 'get']);
    Route::get('penerimaangiroheader/{id}/tarikPelunasan', [PenerimaanGiroHeaderController::class, 'tarikPelunasan'])->whereNumber('id');
    Route::get('penerimaangiroheader/{id}/getPelunasan', [PenerimaanGiroHeaderController::class, 'getPelunasan'])->whereNumber('id');
    Route::resource('penerimaangiroheader', PenerimaanGiroHeaderController::class)->whereNumber('penerimaangiroheader');
    Route::get('penerimaangirodetail/getDetail', [PenerimaanGiroDetailController::class, 'getDetail']);
    Route::resource('penerimaangirodetail', PenerimaanGiroDetailController::class)->whereNumber('penerimaangirodetail');

    Route::get('harilibur/field_length', [HariLiburController::class, 'fieldLength']);
    Route::get('harilibur/default', [HariLiburController::class, 'default']);
    Route::get('harilibur/export', [HariLiburController::class, 'export']);
    Route::get('harilibur/report', [HariLiburController::class, 'report']);

    Route::get('jurnalumumpusatheader/grid', [JurnalUmumPusatHeaderController::class, 'grid']);
    Route::get('jurnalumumpusatheader/field_length', [JurnalUmumPusatHeaderController::class, 'fieldLength']);
    Route::resource('jurnalumumpusatheader', JurnalUmumPusatHeaderController::class)->whereNumber('jurnalumumpusatheader');
    Route::resource('jurnalumumpusatdetail', JurnalUmumPusatDetailController::class)->whereNumber('jurnalumumpusatdetail');

    Route::get('reportall/report', [ReportAllController::class, 'report'])->name('reportall.report');
    Route::resource('reportall', ReportAllController::class)->whereNumber('reportall');

    Route::get('reportneraca/report', [ReportNeracaController::class, 'report'])->name('reportneraca.report');
    Route::resource('reportneraca', ReportNeracaController::class)->whereNumber('reportneraca');

    Route::get('pencairangiropengeluaranheader/grid', [PencairanGiroPengeluaranHeaderController::class, 'grid']);
    Route::get('pencairangiropengeluaranheader/field_length', [PencairanGiroPengeluaranHeaderController::class, 'fieldLength']);
    Route::delete('pencairangiropengeluaranheader', [PencairanGiroPengeluaranHeaderController::class, 'destroy']);
    Route::resource('pencairangiropengeluaranheader', PencairanGiroPengeluaranHeaderController::class)->whereNumber('pencairangiropengeluaranheader');
    Route::resource('pencairangiropengeluarandetail', PencairanGiroPengeluaranDetailController::class)->whereNumber('pencairangiropengeluarandetail');

    Route::get('approvalnotaheader/combo', [ApprovalNotaHeaderController::class, 'combo']);
    Route::get('approvalnotaheader/default', [ApprovalNotaHeaderController::class, 'default']);
    Route::resource('approvalnotaheader', ApprovalNotaHeaderController::class)->whereNumber('approvalnotaheader');
    Route::get('approvalhutangbayar/default', [ApprovalHutangBayarController::class, 'default']);
    Route::resource('approvalhutangbayar', ApprovalHutangBayarController::class)->whereNumber('approvalhutangbayar');


    Route::get('pendapatansupirheader/{id}/printreport', [PendapatanSupirHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('pendapatansupirheader/{id}/export', [PendapatanSupirHeaderController::class, 'export'])->name('pendapatansupirheader.export')->whereNumber('id');
    Route::get('pendapatansupirheader/{id}/exportsupir', [PendapatanSupirHeaderController::class, 'exportsupir'])->name('pendapatansupirheader.exportsupir')->whereNumber('id');
    Route::post('pendapatansupirheader/approval', [PendapatanSupirHeaderController::class, 'approval']);
    Route::get('pendapatansupirheader/getDataDeposito', [PendapatanSupirHeaderController::class, 'getDataDeposito']);
    Route::get('pendapatansupirheader/{supirId}/getPinjaman', [PendapatanSupirHeaderController::class, 'getPinjaman']);
    Route::get('pendapatansupirheader/gettrip', [PendapatanSupirHeaderController::class, 'gettrip']);
    Route::get('pendapatansupirheader/default', [PendapatanSupirHeaderController::class, 'default']);
    Route::resource('pendapatansupirheader', PendapatanSupirHeaderController::class)->parameters(['pendapatansupirheader' => 'pendapatanSupirHeader'])->whereNumber('pendapatanSupirHeader');
    Route::get('pendapatansupirdetail/jurnal', [PendapatanSupirDetailController::class, 'jurnal']);
    Route::get('pendapatansupirdetail/detailsupir', [PendapatanSupirDetailController::class, 'detailsupir']);
    Route::resource('pendapatansupirdetail', PendapatanSupirDetailController::class)->whereNumber('pendapatansupirdetail');

    Route::get('approvalpendapatansupir/default', [ApprovalPendapatanSupirController::class, 'default']);
    Route::resource('approvalpendapatansupir', ApprovalPendapatanSupirController::class)->whereNumber('approvalpendapatansupir');
    Route::get('stokpersediaan/default', [StokPersediaanController::class, 'default']);
    Route::get('stokpersediaan/report', [StokPersediaanController::class, 'report'])->name('stokpersediaan.report');
    Route::get('stokpersediaan/export', [StokPersediaanController::class, 'export'])->name('stokpersediaan.export');
    Route::resource('stokpersediaan', StokPersediaanController::class)->whereNumber('stokpersediaan');
    Route::get('kartustok/report', [KartuStokController::class, 'report'])->name('kartustok.report');
    Route::get('kartustok/export', [KartuStokController::class, 'export'])->name('kartustok.export');
    Route::get('kartustok/default', [KartuStokController::class, 'default']);
    Route::resource('kartustok', KartuStokController::class)->whereNumber('kartustok');
    Route::get('kartustoklama/report', [KartuStokLamaController::class, 'report'])->name('kartustoklama.report');
    Route::get('kartustoklama/export', [KartuStokLamaController::class, 'export'])->name('kartustoklama.export');
    Route::get('kartustoklama/default', [KartuStokLamaController::class, 'default']);
    Route::resource('kartustoklama', KartuStokLamaController::class)->whereNumber('kartustoklama');


    Route::get('historipenerimaanstok/report', [HistoriPenerimaanStokController::class, 'report'])->name('historipenerimaanstok.report');
    Route::get('historipenerimaanstok/default', [HistoriPenerimaanStokController::class, 'default']);
    Route::resource('historipenerimaanstok', HistoriPenerimaanStokController::class)->whereNumber('historipenerimaanstok');

    Route::get('historipengeluaranstok/report', [HistoriPengeluaranStokController::class, 'report'])->name('historipengeluaranstok.report');
    Route::get('historipengeluaranstok/default', [HistoriPengeluaranStokController::class, 'default']);
    Route::resource('historipengeluaranstok', HistoriPengeluaranStokController::class)->whereNumber('historipengeluaranstok');

    Route::get('laporankasbank/report', [LaporanKasBankController::class, 'report'])->name('laporankasbank.report');
    Route::get('laporankasbank/export', [LaporanKasBankController::class, 'export'])->name('laporankasbank.export');
    Route::resource('laporankasbank', LaporanKasBankController::class)->whereNumber('laporankasbank');

    Route::get('laporanbukubesar/report', [LaporanBukuBesarController::class, 'report'])->name('laporanbukubesar.report');
    Route::get('laporanbukubesar/export', [LaporanBukuBesarController::class, 'export'])->name('laporanbukubesar.export');
    Route::resource('laporanbukubesar', LaporanBukuBesarController::class)->whereNumber('laporanbukubesar');

    Route::get('laporandatajurnal/report', [LaporanDataJurnalController::class, 'report'])->name('laporandatajurnal.report');
    Route::get('laporandatajurnal/export', [LaporanDataJurnalController::class, 'export'])->name('laporandatajurnal.export');
    Route::get('laporandatajurnal/index', [LaporanDataJurnalController::class, 'index'])->name('laporandatajurnal.index');
    // Route::resource('laporandatajurnal', LaporanDataJurnalController::class)->whereNumber('laporandatajurnal');

    Route::get('tarikdataabsensi/report', [TarikDataAbsensiController::class, 'report'])->name('tarikdataabsensi.report');
    Route::get('tarikdataabsensi/export', [TarikDataAbsensiController::class, 'export'])->name('tarikdataabsensi.export');
    Route::get('tarikdataabsensi/index', [TarikDataAbsensiController::class, 'index'])->name('tarikdataabsensi.index');



    Route::post('prosesuangjalansupirheader/addrowtransfer', [ProsesUangJalanSupirDetailController::class, 'addrowtransfer']);
    Route::post('prosesuangjalansupirheader/{id}/approval', [ProsesUangJalanSupirHeaderController::class, 'approval'])->name('prosesuangjalansupirheader.approval')->whereNumber('id');
    Route::get('prosesuangjalansupirheader/{id}/printreport', [ProsesUangJalanSupirHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('prosesuangjalansupirheader/field_length', [ProsesUangJalanSupirHeaderController::class, 'fieldLength']);
    Route::get('prosesuangjalansupirheader/combo', [ProsesUangJalanSupirHeaderController::class, 'combo']);
    Route::get('prosesuangjalansupirheader/grid', [ProsesUangJalanSupirHeaderController::class, 'grid']);
    Route::get('prosesuangjalansupirheader/{id}/export', [ProsesUangJalanSupirHeaderController::class, 'export'])->name('prosesuangjalansupirheader.export')->whereNumber('id');
    Route::get('prosesuangjalansupirheader/{id}/tarikPelunasan', [ProsesUangJalanSupirHeaderController::class, 'tarikPelunasan'])->whereNumber('id');
    Route::get('prosesuangjalansupirheader/{id}/getPinjaman', [ProsesUangJalanSupirHeaderController::class, 'getPinjaman'])->whereNumber('id');
    Route::get('prosesuangjalansupirheader/{id}/getPengembalian', [ProsesUangJalanSupirHeaderController::class, 'getPengembalian'])->whereNumber('id');
    Route::resource('prosesuangjalansupirheader', ProsesUangJalanSupirHeaderController::class)->whereNumber('prosesuangjalansupirheader');
    Route::get('prosesuangjalansupirdetail/transfer', [ProsesUangJalanSupirDetailController::class, 'transfer']);
    Route::resource('prosesuangjalansupirdetail', ProsesUangJalanSupirDetailController::class)->whereNumber('prosesuangjalansupirdetail');

    Route::get('/orderanemkl', [OrderanEmklController::class, 'index'])->middleware('handle-token');

    Route::get('laporandepositosupir/report', [LaporanDepositoSupirController::class, 'report'])->name('laporandepositosupir.report');
    Route::get('laporandepositosupir/export', [LaporanDepositoSupirController::class, 'export'])->name('laporandepositosupir.export');
    Route::resource('laporandepositosupir', LaporanDepositoSupirController::class);


    Route::get('laporandepositokaryawan/report', [LaporanDepositoKaryawanController::class, 'report'])->name('laporandepositokaryawan.report');
    Route::get('laporandepositokaryawan/export', [LaporanDepositoKaryawanController::class, 'export'])->name('laporandepositokaryawan.export');
    Route::resource('laporandepositokaryawan', LaporanDepositoKaryawanController::class);

    Route::get('laporanpinjamansupir/export', [LaporanPinjamanSupirController::class, 'export'])->name('laporanpinjamansupir.export');
    Route::get('laporanpinjamansupir/report', [LaporanPinjamanSupirController::class, 'report'])->name('laporanpinjamansupir.report');
    Route::resource('laporanpinjamansupir', LaporanPinjamanSupirController::class);

    Route::get('exportlaporanmingguansupir/export', [ExportLaporanMingguanSupirController::class, 'export'])->name('exportlaporanmingguansupir.export');
    Route::resource('exportlaporanmingguansupir', ExportLaporanMingguanSupirController::class);

    Route::get('laporanketeranganpinjamansupir/export', [LaporanKeteranganPinjamanSupirController::class, 'export'])->name('laporanketeranganpinjamansupir.export');
    Route::get('laporanketeranganpinjamansupir/report', [LaporanKeteranganPinjamanSupirController::class, 'report'])->name('laporanketeranganpinjamansupir.report');
    Route::resource('laporanketeranganpinjamansupir', LaporanKeteranganPinjamanSupirController::class);

    Route::get('laporankasharian/report', [LaporanKasHarianController::class, 'report'])->name('laporankasharian.report');
    Route::get('laporankasharian/export', [LaporanKasHarianController::class, 'export'])->name('laporankasharian.export');
    Route::resource('laporankasharian', LaporanKasHarianController::class);

    Route::get('laporankasgantung/report', [LaporanKasGantungController::class, 'report'])->name('laporankasgantung.report');
    Route::get('laporankasgantung/export', [LaporanKasGantungController::class, 'export'])->name('laporankasgantung.export');
    Route::resource('laporankasgantung', LaporanKasGantungController::class)->whereNumber('laporankasgantung');

    Route::get('laporanjurnalumum/report', [LaporanJurnalUmumController::class, 'report'])->name('laporanjurnalumum.report');
    Route::get('laporanjurnalumum/export', [LaporanJurnalUmumController::class, 'export'])->name('laporanjurnalumum.export');
    Route::resource('laporanjurnalumum', LaporanJurnalUmumController::class)->whereNumber('laporanjurnalumum');


    Route::get('laporanpembelian/report', [LaporanPembelianController::class, 'report'])->name('laporanpembelian.report');
    Route::get('laporanpembelian/export', [LaporanPembelianController::class, 'export'])->name('laporanpembelian.export');
    Route::resource('laporanpembelian', LaporanPembelianController::class)->whereNumber('laporanpembelian');

    Route::get('laporanpembelianstok/report', [LaporanPembelianStokController::class, 'report'])->name('laporanpembelianstok.report');
    Route::get('laporanpembelianstok/export', [LaporanPembelianStokController::class, 'export'])->name('laporanpembelianstok.export');
    Route::resource('laporanpembelianstok', LaporanPembelianStokController::class)->whereNumber('laporanpembelianstok');

    Route::get('laporanhutanggiro/report', [LaporanHutangGiroController::class, 'report'])->name('laporanhutanggiro.report');
    Route::get('laporanhutanggiro/export', [LaporanHutangGiroController::class, 'export'])->name('laporanhutanggiro.export');
    Route::resource('laporanhutanggiro', LaporanHutangGiroController::class)->whereNumber('laporanhutanggiro');

    Route::get('laporankartuhutangpersupplier/report', [LaporanKartuHutangPerSupplierController::class, 'report'])->name('laporankartuhutangpersupplier.report');
    Route::get('laporankartuhutangpersupplier/export', [LaporanKartuHutangPerSupplierController::class, 'export'])->name('laporankartuhutangpersupplier.export');
    Route::resource('laporankartuhutangpersupplier', LaporanKartuHutangPerSupplierController::class)->whereNumber('laporankartuhutangpersupplier');

    Route::get('laporankartupiutangperagen/report', [LaporanKartuPiutangPerAgenController::class, 'report'])->name('laporankartupiutangperagen.report');
    Route::get('laporankartupiutangperagen/export', [LaporanKartuPiutangPerAgenController::class, 'export'])->name('laporankartupiutangperagen.export');
    Route::resource('laporankartupiutangperagen', LaporanKartuPiutangPerAgenController::class)->whereNumber('laporankartupiutangperagen');

    Route::get('laporankartupanjar/report', [LaporanKartuPanjarController::class, 'report'])->name('laporankartupanjar.report');
    Route::get('laporankartupanjar/export', [LaporanKartuPanjarController::class, 'export'])->name('laporankartupanjar.export');
    Route::resource('laporankartupanjar', LaporanKartuPanjarController::class)->whereNumber('laporankartupanjar');

    Route::get('laporanhistorydeposito/report', [LaporanHistoryDepositoController::class, 'report'])->name('laporanhistorydeposito.report');
    Route::get('laporanhistorydeposito/export', [LaporanHistoryDepositoController::class, 'export'])->name('laporanhistorydeposito.export');
    Route::resource('laporanhistorydeposito', LaporanHistoryDepositoController::class)->whereNumber('laporanhistorydeposito');

    Route::get('laporanhutangbbm/report', [LaporanHutangBBMController::class, 'report'])->name('laporanhutangbbm.report');
    Route::get('laporanhutangbbm/export', [LaporanHutangBBMController::class, 'export'])->name('laporanhutangbbm.export');
    Route::resource('laporanhutangbbm', LaporanHutangBBMController::class)->whereNumber('laporanhutangbbm');
    Route::get('laporanestimasikasgantung/report', [LaporanEstimasiKasGantungController::class, 'report'])->name('laporanestimasikasgantung.report');
    Route::get('lapkartuhutangpervendordetail/report', [LapKartuHutangPerVendorDetailController::class, 'report'])->name('lapkartuhutangpervendordetail.report');
    Route::resource('lapkartuhutangpervendordetail', LapKartuHutangPerVendorDetailController::class)->whereNumber('lapkartuhutangpervendordetail');
    Route::get('laporanwarkatbelumcair/report', [LaporanWarkatBelumCairController::class, 'report'])->name('laporanwarkatbelumcair.report');
    Route::resource('laporanwarkatbelumcair', LaporanWarkatBelumCairController::class)->whereNumber('laporanwarkatbelumcair');

    Route::get('laporanpiutanggiro/report', [LaporanPiutangGiroController::class, 'report'])->name('laporanpiutanggiro.report');
    Route::get('laporanpiutanggiro/export', [LaporanPiutangGiroController::class, 'export'])->name('laporanpiutanggiro.export');
    Route::resource('laporanpiutanggiro', LaporanPiutangGiroController::class)->whereNumber('laporanpiutanggiro');

    Route::get('laporanlabarugi/report', [LaporanLabaRugiController::class, 'report'])->name('laporanlabarugi.report');
    Route::get('laporanlabarugi/export', [LaporanLabaRugiController::class, 'export'])->name('laporanlabarugi.export');
    Route::resource('laporanlabarugi', LaporanLabaRugiController::class)->whereNumber('laporanlabarugi');

    Route::get('laporanpemakaianstok/report', [LaporanPemakaianStokController::class, 'report'])->name('laporanpemakaianstok.report');
    Route::get('laporanpemakaianstok/export', [LaporanPemakaianStokController::class, 'export'])->name('laporanpemakaianstok.export');
    Route::resource('laporanpemakaianstok', LaporanPemakaianStokController::class)->whereNumber('laporanpemakaianstok');

    Route::get('laporanpembelianbarang/report', [LaporanPembelianBarangController::class, 'report'])->name('laporanpembelianbarang.report');
    Route::get('laporanpembelianbarang/export', [LaporanPembelianBarangController::class, 'export'])->name('laporanpembelianbarang.export');
    Route::resource('laporanpembelianbarang', LaporanPembelianBarangController::class)->whereNumber('laporanpembelianbarang');

    Route::get('laporanstok/report', [LaporanStokController::class, 'report'])->name('laporanstok.report');
    Route::get('laporanstok/export', [LaporanStokController::class, 'export'])->name('laporanstok.export');
    Route::resource('laporanstok', LaporanStokController::class)->whereNumber('laporanstok');

    Route::get('laporanneraca/report', [LaporanNeracaController::class, 'report'])->name('laporanneraca.report');
    Route::get('laporanneraca/export', [LaporanNeracaController::class, 'export'])->name('laporanneraca.export');
    Route::resource('laporanneraca', LaporanNeracaController::class)->whereNumber('laporanneraca');


    Route::get('laporanpenyesuaianbarang/report', [LaporanPenyesuaianBarangController::class, 'report'])->name('laporanpenyesuaianbarang.report');
    Route::get('laporanpenyesuaianbarang/export', [LaporanPenyesuaianBarangController::class, 'export'])->name('laporanpenyesuaianbarang.export');
    Route::resource('laporanpenyesuaianbarang', LaporanPenyesuaianBarangController::class)->whereNumber('laporanpenyesuaianbarang');

    Route::get('laporanpemakaianban/report', [LaporanPemakaianBanController::class, 'report'])->name('laporanpemakaianban.report');
    Route::get('laporanpemakaianban/export', [LaporanPemakaianBanController::class, 'export'])->name('laporanpemakaianban.export');
    Route::resource('laporanpemakaianban', LaporanPemakaianBanController::class)->whereNumber('laporanpemakaianban');

    Route::get('laporantransaksiharian/report', [LaporanTransaksiHarianController::class, 'report'])->name('laporantransaksiharian.report');
    Route::get('laporantransaksiharian/export', [LaporanTransaksiHarianController::class, 'export'])->name('laporantransaksiharian.export');
    Route::resource('laporantransaksiharian', LaporanTransaksiHarianController::class)->whereNumber('laporantransaksiharian');

    Route::get('laporantitipanemkl/report', [LaporanTitipanEmklController::class, 'report'])->name('laporantitipanemkl.report');
    Route::get('laporantitipanemkl/export', [LaporanTitipanEmklController::class, 'export'])->name('laporantitipanemkl.export');
    Route::resource('laporantitipanemkl', LaporanTitipanEmklController::class)->whereNumber('laporantitipanemkl');

    Route::get('laporanrekaptitipanemkl/report', [LaporanRekapTitipanEmklController::class, 'report'])->name('laporanrekaptitipanemkl.report');
    Route::get('laporanrekaptitipanemkl/export', [LaporanRekapTitipanEmklController::class, 'export'])->name('laporanrekaptitipanemkl.export');
    Route::resource('laporanrekaptitipanemkl', LaporanRekapTitipanEmklController::class)->whereNumber('laporanrekaptitipanemkl');

    Route::resource('laporanestimasikasgantung', LaporanEstimasiKasGantungController::class)->whereNumber('laporanestimasikasgantung');
    Route::get('laporantriptrado/report', [LaporanTripTradoController::class, 'report'])->name('laporantriptrado.report');
    Route::get('laporantriptrado/export', [LaporanTripTradoController::class, 'export'])->name('laporantriptrado.export');
    Route::resource('laporantriptrado', LaporanTripTradoController::class)->whereNumber('laporantriptrado');
    Route::get('laporankartuhutangprediksi/report', [LaporanKartuHutangPrediksiController::class, 'report'])->name('laporankartuhutangprediksi.report');
    Route::get('laporankartuhutangprediksi/export', [LaporanKartuHutangPrediksiController::class, 'export'])->name('laporankartuhutangprediksi.export');
    Route::resource('laporankartuhutangprediksi', LaporanKartuHutangPrediksiController::class)->whereNumber('laporankartuhutangprediksi');
    Route::get('laporantripgandengandetail/report', [LaporanTripGandenganDetailController::class, 'report'])->name('laporantripgandengandetail.report');
    Route::get('laporantripgandengandetail/export', [LaporanTripGandenganDetailController::class, 'export'])->name('laporantripgandengandetail.export');
    Route::resource('laporantripgandengandetail', LaporanTripGandenganDetailController::class)->whereNumber('laporantripgandengandetail');
    Route::get('laporanuangjalan/report', [LaporanUangJalanController::class, 'report'])->name('laporanuangjalan.report');
    Route::get('laporanuangjalan/export', [LaporanUangJalanController::class, 'export'])->name('laporanuangjalan.export');
    Route::resource('laporanuangjalan', LaporanUangJalanController::class)->whereNumber('laporanuangjalan');
    Route::get('laporanpinjamansupirkaryawan/export', [LaporanPinjamanSupirKaryawanController::class, 'export'])->name('laporanpinjamansupirkaryawan.export');
    Route::get('laporanpinjamansupirkaryawan/report', [LaporanPinjamanSupirKaryawanController::class, 'report'])->name('laporanpinjamansupirkaryawan.report');
    Route::resource('laporanpinjamansupirkaryawan', LaporanPinjamanSupirKaryawanController::class)->whereNumber('laporanpinjamansupirkaryawan');
    Route::get('laporanpemotonganpinjamanperebs/report', [LaporanPemotonganPinjamanPerEBSController::class, 'report'])->name('laporanpemotonganpinjamanperebs.report');
    Route::get('laporanpemotonganpinjamanperebs/export', [LaporanPemotonganPinjamanPerEBSController::class, 'export'])->name('laporanpemotonganpinjamanperebs.export');
    Route::resource('laporanpemotonganpinjamanperebs', LaporanPemotonganPinjamanPerEBSController::class)->whereNumber('laporanpemotonganpinjamanperebs');
    Route::get('laporansupirlebihdaritrado/report', [LaporanSupirLebihDariTradoController::class, 'report'])->name('laporansupirlebihdaritrado.report');
    Route::get('laporansupirlebihdaritrado/export', [LaporanSupirLebihDariTradoController::class, 'export'])->name('laporansupirlebihdaritrado.export');
    Route::resource('laporansupirlebihdaritrado', LaporanSupirLebihDariTradoController::class)->whereNumber('ilaporansupirlebihdaritradod');
    Route::get('laporanpemotonganpinjamandepo/report', [LaporanPemotonganPinjamanDepoController::class, 'report'])->name('laporanpemotonganpinjamandepo.report');
    Route::resource('laporanpemotonganpinjamandepo', LaporanPemotonganPinjamanDepoController::class)->whereNumber('laporanpemotonganpinjamandepo');
    Route::get('laporanrekapsumbangan/report', [LaporanRekapSumbanganController::class, 'report'])->name('laporanrekapsumbangan.report');
    Route::get('laporanrekapsumbangan/export', [LaporanRekapSumbanganController::class, 'export'])->name('laporanrekapsumbangan.export');
    Route::resource('laporanrekapsumbangan', LaporanRekapSumbanganController::class)->whereNumber('laporanrekapsumbangan');
    Route::get('laporanklaimpjtsupir/report', [LaporanKlaimPJTSupirController::class, 'report'])->name('laporanklaimpjtsupir.report');
    Route::get('laporanklaimpjtsupir/export', [LaporanKlaimPJTSupirController::class, 'export'])->name('laporanklaimpjtsupir.export');
    Route::resource('laporanklaimpjtsupir', LaporanKlaimPJTSupirController::class)->whereNumber('laporanklaimpjtsupir');
    Route::get('laporankartuhutangpervendor/report', [LaporanKartuHutangPerVendorController::class, 'report'])->name('laporankartuhutangpervendor.report');
    Route::resource('laporankartuhutangpervendor', LaporanKartuHutangPerVendorController::class)->whereNumber('laporankartuhutangpervendor');
    Route::get('laporanmutasikasbank/report', [LaporanMutasiKasBankController::class, 'report'])->name('laporanmutasikasbank.report');
    Route::resource('laporanmutasikasbank', LaporanMutasiKasBankController::class)->whereNumber('laporanmutasikasbank');
    Route::get('laporankartustok/report', [LaporanKartuStokController::class, 'report'])->name('laporankartustok.report');
    Route::resource('laporankartustok', LaporanKartuStokController::class)->whereNumber('laporankartustok');
    Route::get('laporanaruskas/report', [LaporanArusKasController::class, 'report'])->name('laporanaruskas.report');
    Route::resource('laporanaruskas', LaporanArusKasController::class)->whereNumber('laporanaruskas');

    Route::get('laporankartupiutangperpelanggan/report', [LaporanKartuPiutangPerPelangganController::class, 'report'])->name('laporankartupiutangperpelanggan.report');
    Route::resource('laporankartupiutangperpelanggan', LaporanKartuPiutangPerPelangganController::class)->whereNumber('laporankartupiutangperpelanggan');
    Route::get('laporankartupiutangperplgdetail/report', [LaporanKartuPiutangPerPlgDetailController::class, 'report'])->name('laporankartupiutangperplgdetail.report');
    Route::resource('laporankartupiutangperplgdetail', LaporanKartuPiutangPerPlgDetailController::class)->whereNumber('laporankartupiutangperplgdetail');
    Route::get('laporanorderpembelian/report', [LaporanOrderPembelianController::class, 'report'])->name('laporanorderpembelian.report');
    Route::resource('laporanorderpembelian', LaporanOrderPembelianController::class)->whereNumber('laporanorderpembelian');

    Route::get('exportpengeluaranbarang/export', [ExportPengeluaranBarangController::class, 'export'])->name('exportpengeluaranbarang.export');
    Route::resource('exportpengeluaranbarang', ExportPengeluaranBarangController::class)->whereNumber('exportpengeluaranbarang');
    Route::get('exportpembelianbarang/export', [ExportPembelianBarangController::class, 'export'])->name('exportpembelianbarang.export');
    Route::resource('exportpembelianbarang', ExportPembelianBarangController::class)->whereNumber('exportpembelianbarang');
    // Route::get('exportlaporandeposito/export', [ExportLaporanDepositoController::class, 'export'])->name('exportlaporandeposito.export');
    // Route::resource('exportlaporandeposito', ExportLaporanDepositoController::class);
    Route::get('exportlaporankasgantung/export', [ExportLaporanKasGantungController::class, 'export'])->name('exportlaporankasgantung.export');
    Route::resource('exportlaporankasgantung', ExportLaporanKasGantungController::class)->whereNumber('exportlaporankasgantung');


    Route::get('laporansaldoinventory/export', [LaporanSaldoInventoryController::class, 'export'])->name('laporansaldoinventory.export');
    Route::get('laporansaldoinventory/report', [LaporanSaldoInventoryController::class, 'report'])->name('laporansaldoinventory.report');
    Route::resource('laporansaldoinventory', LaporanSaldoInventoryController::class)->whereNumber('laporansaldoinventory');

    Route::get('laporansaldoinventorylama/export', [LaporanSaldoInventoryLamaController::class, 'export'])->name('laporansaldoinventorylama.export');
    Route::get('laporansaldoinventorylama/report', [LaporanSaldoInventoryLamaController::class, 'report'])->name('laporansaldoinventorylama.report');
    Route::resource('laporansaldoinventorylama', LaporanSaldoInventoryLamaController::class)->whereNumber('laporansaldoinventorylama');

    Route::get('exportlaporanstok/export', [ExportLaporanStokController::class, 'export'])->name('exportlaporanstok.export');
    Route::resource('exportlaporanstok', ExportLaporanStokController::class)->whereNumber('exportlaporanstok');
    Route::get('laporanritasitrado/export', [LaporanRitasiTradoController::class, 'export'])->name('laporanritasitrado.export');
    Route::resource('laporanritasitrado', LaporanRitasiTradoController::class)->whereNumber('laporanritasitrado');
    Route::get('laporanritasigandengan/header', [LaporanRitasiGandenganController::class, 'header'])->name('laporanritasigandengan.header');
    Route::get('laporanritasigandengan/export', [LaporanRitasiGandenganController::class, 'export'])->name('laporanritasigandengan.export');
    Route::resource('laporanritasigandengan', LaporanRitasiGandenganController::class)->whereNumber('laporanritasigandengan');
    Route::get('laporanhistorypinjaman/report', [LaporanHistoryPinjamanController::class, 'report'])->name('laporanhistorypinjaman.report');
    Route::get('laporanhistorypinjaman/export', [LaporanHistoryPinjamanController::class, 'export'])->name('laporanhistorypinjaman.export');
    Route::resource('laporanhistorypinjaman', LaporanHistoryPinjamanController::class)->whereNumber('laporanhistorypinjaman');
    Route::get('exportpemakaianbarang/export', [ExportPemakaianBarangController::class, 'export'])->name('exportpemakaianbarang.export');
    Route::resource('exportpemakaianbarang', ExportPemakaianBarangController::class)->whereNumber('exportpemakaianbarang');

    Route::get('/orderanemkl/getTglJob', [OrderanEmklController::class, 'getTglJob'])->middleware('handle-token');

    Route::get('pemutihansupir/getPost', [PemutihanSupirController::class, 'getPost']);
    Route::get('pemutihansupir/getNonPost', [PemutihanSupirController::class, 'getNonPost']);
    Route::get('pemutihansupir/{id}/printreport', [PemutihanSupirController::class, 'printreport'])->whereNumber('id');
    Route::get('pemutihansupir/{id}/export', [PemutihanSupirController::class, 'export'])->name('pemutihansupir.export')->whereNumber('id');
    Route::get('pemutihansupir/{pemutihanId}/getEditPost', [PemutihanSupirController::class, 'getEditPost'])->whereNumber('pemutihanId');
    Route::get('pemutihansupir/{pemutihanId}/getEditNonPost', [PemutihanSupirController::class, 'getEditNonPost'])->whereNumber('pemutihanId');
    Route::get('pemutihansupir/{pemutihanId}/getDeletePost', [PemutihanSupirController::class, 'getDeletePost'])->whereNumber('pemutihanId');
    Route::get('pemutihansupir/{pemutihanId}/getDeleteNonPost', [PemutihanSupirController::class, 'getDeleteNonPost'])->whereNumber('pemutihanId');
    Route::post('pemutihansupir/{id}/cekvalidasi', [PemutihanSupirController::class, 'cekvalidasi'])->name('pemutihansupir.cekvalidasi')->whereNumber('id');
    Route::get('pemutihansupir/field_length', [PemutihanSupirController::class, 'fieldLength']);
    Route::resource('pemutihansupir', PemutihanSupirController::class)->whereNumber('pemutihansupir');
    Route::resource('pemutihansupirdetail', PemutihanSupirDetailController::class)->whereNumber('pemutihansupirdetail');


    Route::get('exportrincianmingguanpendapatan/export', [ExportRincianMingguanPendapatanSupirController::class, 'export'])->name('exportrincianmingguanpendapatan.export');
    Route::resource('exportrincianmingguanpendapatan', ExportRincianMingguanPendapatanSupirController::class)->whereNumber('exportrincianmingguanpendapatan');;
    Route::get('laporanbangudangsementara/export', [LaporanBanGudangSementaraController::class, 'export'])->name('laporanbangudangsementara.export');
    Route::get('laporanbangudangsementara/report', [LaporanBanGudangSementaraController::class, 'report'])->name('laporanbangudangsementara.report');
    Route::resource('laporanbangudangsementara', LaporanBanGudangSementaraController::class)->whereNumber('laporanbangudangsementara');;
    Route::get('exportrincianmingguan/export', [ExportRincianMingguanController::class, 'export'])->name('exportrincianmingguan.export');
    Route::resource('exportrincianmingguan', ExportRincianMingguanController::class)->whereNumber('exportrincianmingguan');;
    Route::get('exportlaporankasharian/export', [ExportLaporanKasHarianController::class, 'export'])->name('exportlaporankasharian.export');
    Route::resource('exportlaporankasharian', ExportLaporanKasHarianController::class)->whereNumber('exportlaporankasharian');

    Route::get('pindahbuku/{id}/printreport', [PindahBukuController::class, 'printReport'])->whereNumber('id');
    Route::get('pindahbuku/{id}/export', [PindahBukuController::class, 'export'])->name('pindahbuku.export')->whereNumber('id');
    Route::get('pindahbuku/default', [PindahBukuController::class, 'default']);
    Route::post('pindahbuku/{id}/cekvalidasi', [PindahBukuController::class, 'cekvalidasi'])->name('pindahbuku.cekvalidasi')->whereNumber('id');
    Route::resource('pindahbuku', PindahBukuController::class)->whereNumber('pindahbuku');

    Route::get('karyawan/field_length', [KaryawanController::class, 'fieldLength']);
    Route::get('karyawan/default', [KaryawanController::class, 'default']);
    Route::post('karyawan/{id}/cekValidasi', [KaryawanController::class, 'cekValidasi'])->name('karyawan.cekValidasi')->whereNumber('id');
    Route::get('karyawan/export', [KaryawanController::class, 'export']);
    Route::get('karyawan/report', [KaryawanController::class, 'report']);

    Route::get('cabang/field_length', [CabangController::class, 'fieldLength']);
    Route::get('cabang/default', [CabangController::class, 'default']);
    Route::get('cabang/report', [CabangController::class, 'report']);
    Route::get('cabang/export', [CabangController::class, 'export']);
    Route::get('cabang/getPosition2', [CabangController::class, 'getPosition2']);


    Route::get('dataritasi/field_length', [DataRitasiController::class, 'fieldLength']);

    Route::get('dataritasi/combostatus', [DataRitasiController::class, 'combostatus']);
    Route::get('dataritasi/default', [DataRitasiController::class, 'default']);
    Route::get('dataritasi/report', [DataRitasiController::class, 'report']);
    Route::get('dataritasi/export', [DataRitasiController::class, 'export']);
    Route::get('dataritasi/getPosition2', [DataRitasiController::class, 'getPosition2']);
    Route::resource('dataritasi', DataRitasiController::class)->whereNumber('dataritasi');

    Route::get('akuntansi/field_length', [AkuntansiController::class, 'fieldLength']);
    Route::get('akuntansi/combostatus', [AkuntansiController::class, 'combostatus']);
    Route::get('akuntansi/default', [AkuntansiController::class, 'default']);
    Route::get('akuntansi/report', [AkuntansiController::class, 'report']);
    Route::get('akuntansi/export', [AkuntansiController::class, 'export']);
    Route::get('akuntansi/getPosition2', [AkuntansiController::class, 'getPosition2']);

    Route::get('typeakuntansi/field_length', [TypeAkuntansiController::class, 'fieldLength']);
    Route::get('typeakuntansi/combostatus', [TypeAkuntansiController::class, 'combostatus']);
    Route::get('typeakuntansi/default', [TypeAkuntansiController::class, 'default']);
    Route::get('typeakuntansi/report', [TypeAkuntansiController::class, 'report']);
    Route::get('typeakuntansi/export', [TypeAkuntansiController::class, 'export']);
    Route::get('typeakuntansi/getPosition2', [TypeAkuntansiController::class, 'getPosition2']);

    Route::get('maintypeakuntansi/field_length', [MainTypeAkuntansiController::class, 'fieldLength']);
    Route::get('maintypeakuntansi/combostatus', [MainTypeAkuntansiController::class, 'combostatus']);
    Route::get('maintypeakuntansi/default', [MainTypeAkuntansiController::class, 'default']);
    Route::get('maintypeakuntansi/report', [MainTypeAkuntansiController::class, 'report']);
    Route::get('maintypeakuntansi/export', [MainTypeAkuntansiController::class, 'export']);
    Route::get('maintypeakuntansi/getPosition2', [MainTypeAkuntansiController::class, 'getPosition2']);

    Route::get('approvaltradogambar/field_length', [ApprovalTradoGambarController::class, 'fieldLength']);

    Route::get('approvaltradoketerangan/field_length', [ApprovalTradoKeteranganController::class, 'fieldLength']);

    Route::get('ubahpassword/field_length', [UbahPasswordController::class, 'fieldLength']);
    Route::resource('ubahpassword', UbahPasswordController::class)->whereNumber('ubahpassword');

    Route::get('laporanpinjamanperunittrado/report', [LaporanPinjamanPerUnitTradoController::class, 'report'])->name('laporanpinjamanperunittrado.report');
    Route::get('laporanpinjamanperunittrado/export', [LaporanPinjamanPerUnitTradoController::class, 'export'])->name('laporanpinjamanperunittrado.export');
    Route::resource('laporanpinjamanperunittrado', LaporanPinjamanPerUnitTradoController::class)->whereNumber('laporanpinjamanperunittrado');

    Route::get('stokpusat/getdata', [StokPusatController::class, 'getData']);
    Route::get('stokpusat/datajkttnl', [StokPusatController::class, 'dataJktTnl']);
    Route::get('stokpusat/datamks', [StokPusatController::class, 'dataMks']);
    Route::get('stokpusat/datamnd', [StokPusatController::class, 'dataMnd']);
    Route::get('stokpusat/datajkt', [StokPusatController::class, 'dataJkt']);
    Route::get('stokpusat/datamdn', [StokPusatController::class, 'dataMdn']);
    Route::get('stokpusat/datasby', [StokPusatController::class, 'dataSby']);
    Route::resource('stokpusat', StokPusatController::class)->whereNumber('stokpusat');

    Route::get('hutangextraheader/{id}/printreport', [HutangExtraHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('hutangextraheader/{id}/export', [HutangExtraHeaderController::class, 'export'])->name('hutangextraheader.export')->whereNumber('id');
    Route::post('hutangextraheader/addrow', [HutangExtraDetailController::class, 'addrow']);
    Route::post('hutangextraheader/{id}/cekvalidasi', [HutangExtraHeaderController::class, 'cekvalidasi'])->name('hutangextraheader.cekvalidasi')->whereNumber('id');
    Route::post('hutangextraheader/{id}/cekValidasiAksi', [HutangExtraHeaderController::class, 'cekValidasiAksi'])->name('hutangextraheader.cekValidasiAksi')->whereNumber('id');
    Route::post('hutangextraheader/approval', [HutangExtraHeaderController::class, 'approval']);
    Route::get('hutangextraheader/combo', [HutangExtraHeaderController::class, 'combo']);
    Route::get('hutangextraheader/grid', [HutangExtraHeaderController::class, 'grid']);
    Route::post('hutangextraheader/{id}/cekValidasiAksi', [HutangExtraHeaderController::class, 'cekValidasiAksi'])->name('hutangextraheader.cekValidasiAksi')->whereNumber('id');
    Route::get('hutangextraheader/field_length', [HutangExtraHeaderController::class, 'fieldLength']);
    Route::resource('hutangextraheader', HutangExtraHeaderController::class)->whereNumber('hutangextraheader');
    Route::get('hutangextradetail/hutang', [HutangExtraDetailController::class, 'hutang']);
    Route::resource('hutangextradetail', HutangExtraDetailController::class)->whereNumber('hutangextradetail');

    Route::get('logabsensi/report', [LogAbsensiController::class, 'report']);
    Route::get('logabsensi/export', [LogAbsensiController::class, 'export']);
    Route::resource('logabsensi', LogAbsensiController::class)->whereNumber('logabsensi');

    Route::resource('karyawanlogabsensi', KaryawanLogAbsensiController::class)->whereNumber('karyawanlogabsensi');
    Route::resource('reminderoli', ReminderOliController::class)->whereNumber('reminderoli');
    Route::resource('expsim', ExpSimController::class)->whereNumber('expsim');
    Route::resource('expstnk', ExpStnkController::class)->whereNumber('expstnk');
    Route::resource('expasuransi', ExpAsuransiController::class)->whereNumber('expasuransi');
    Route::resource('reminderstok', ReminderStokController::class)->whereNumber('reminderstok');
    Route::resource('statusolitrado', StatusOliTradoController::class)->whereNumber('statusolitrado');
    Route::resource('reminderspk', ReminderSpkController::class)->whereNumber('reminderspk');
    Route::resource('reminderspkdetail', ReminderSpkDetailController::class)->whereNumber('reminderspkdetail');
    Route::get('reminderspkdetail/export', [ReminderSpkDetailController::class, 'export']);

    Route::resource('spkharian', SpkHarianController::class)->whereNumber('spkharian');
    Route::resource('spkhariandetail', SpkHarianDetailController::class)->whereNumber('spkhariandetail');
    Route::resource('statusgandengantruck', StatusGandenganTruckController::class)->whereNumber('statusgandengantruck');

    Route::get('opnameheader/{id}/printreport', [OpnameHeaderController::class, 'printReport'])->whereNumber('id');
    Route::get('opnameheader/{id}/export', [OpnameHeaderController::class, 'export'])->name('opnameheader.export')->whereNumber('id');
    Route::get('opnameheader/getstok', [OpnameHeaderController::class, 'getStok']);
    Route::get('opnameheader/{id}/getEdit', [OpnameHeaderController::class, 'getEdit'])->whereNumber('id');
    Route::get('opnameheader/{id}/export', [OpnameHeaderController::class, 'export'])->name('opnameheader.export')->whereNumber('id');
    Route::post('opnameheader/approval', [OpnameHeaderController::class, 'approval']);
    Route::post('opnameheader/{id}/cekvalidasi', [OpnameHeaderController::class, 'cekvalidasi'])->name('opnameheader.cekvalidasi')->whereNumber('id');
    // Route::post('opnameheader/{id}/approval', [OpnameHeaderController::class, 'approval'])->name('opnameheader.approval')->whereNumber('id');
    Route::resource('opnameheader', OpnameHeaderController::class)->whereNumber('opnameheader');
    Route::resource('opnamedetail', OpnameDetailController::class)->whereNumber('opnamedetail');

    Route::get('pelunasanhutangheader/{id}/printreport', [PelunasanHutangHeaderController::class, 'printReport'])->whereNumber('id');
    Route::post('pelunasanhutangheader/{id}/cekValidasiAksi', [PelunasanHutangHeaderController::class, 'cekValidasiAksi'])->name('pelunasanhutangheader.cekValidasiAksi')->whereNumber('id');
    Route::post('pelunasanhutangheader/{id}/cekvalidasi', [PelunasanHutangHeaderController::class, 'cekvalidasi'])->name('pelunasanhutangheader.cekvalidasi')->whereNumber('id');
    Route::get('pelunasanhutangheader/no_bukti', [PelunasanHutangHeaderController::class, 'getNoBukti']);
    Route::get('pelunasanhutangheader/field_length', [PelunasanHutangHeaderController::class, 'fieldLength']);
    Route::get('pelunasanhutangheader/combo', [PelunasanHutangHeaderController::class, 'combo']);
    Route::get('pelunasanhutangheader/{id}/getHutang', [PelunasanHutangHeaderController::class, 'getHutang'])->name('pelunasanhutangheader.getHutang')->whereNumber('id');
    Route::get('pelunasanhutangheader/comboapproval', [PelunasanHutangHeaderController::class, 'comboapproval']);
    Route::post('pelunasanhutangheader/approval', [PelunasanHutangHeaderController::class, 'approval']);
    Route::get('pelunasanhutangheader/{id}/export', [PelunasanHutangHeaderController::class, 'export'])->name('pelunasanhutangheader.export')->whereNumber('id');
    Route::post('pelunasanhutangheader/{id}/cekapproval', [PelunasanHutangHeaderController::class, 'cekapproval'])->name('pelunasanhutangheader.cekapproval')->whereNumber('id');
    Route::get('pelunasanhutangheader/{id}/{fieldid}/getPembayaran', [PelunasanHutangHeaderController::class, 'getPembayaran'])->whereNumber('id');
    Route::get('pelunasanhutangheader/grid', [PelunasanHutangHeaderController::class, 'grid']);
    Route::resource('pelunasanhutangheader', PelunasanHutangHeaderController::class)->whereNumber('pelunasanhutangheader');
    Route::resource('pelunasanhutangdetail', PelunasanHutangDetailController::class)->whereNumber('pelunasanhutangdetail');

    Route::get('tripinap/export', [TripInapController::class, 'export']);
    Route::get('tripinap/report', [TripInapController::class, 'report']);
    Route::post('tripinap/approval', [TripInapController::class, 'approval']);
    Route::post('tripinap/{id}/approval', [TripInapController::class, 'approval'])->name('tripinap.approval')->whereNumber('tripinap');
    Route::post('tripinap/{id}/cekValidasi', [TripInapController::class, 'cekValidasi'])->name('tripinap.cekValidasi')->whereNumber('id');
    Route::resource('tripinap', TripInapController::class)->whereNumber('tripinap');

    Route::get('pengajuantripinap/export', [PengajuanTripInapController::class, 'export']);
    Route::get('pengajuantripinap/report', [PengajuanTripInapController::class, 'report']);
    Route::post('pengajuantripinap/approval', [PengajuanTripInapController::class, 'approval']);
    Route::post('pengajuantripinap/approvalbataspengajuan', [PengajuanTripInapController::class, 'approvalbataspengajuan']);
    Route::post('pengajuantripinap/{id}/approval', [PengajuanTripInapController::class, 'approval'])->name('pengajuantripinap.approval')->whereNumber('pengajuantripinap');
    Route::post('pengajuantripinap/{id}/cekValidasi', [PengajuanTripInapController::class, 'cekValidasi'])->name('pengajuantripinap.cekValidasi')->whereNumber('id');
    Route::resource('pengajuantripinap', PengajuanTripInapController::class)->whereNumber('pengajuantripinap');

    Route::get('exportric/export', [ExportRicController::class, 'export'])->name('exportric.export');
    Route::resource('exportric', ExportRicController::class);

    Route::get('laporanmingguansupirbedamandor/export', [LaporanMingguanSupirBedaMandorController::class, 'export'])->name('laporanmingguansupirbedamandor.export');
    Route::resource('laporanmingguansupirbedamandor', LaporanMingguanSupirBedaMandorController::class);

    Route::post('supirserap/{id}/cekvalidasi', [SupirSerapController::class, 'cekvalidasi'])->whereNumber('id');
    Route::get('supirserap/export', [SupirSerapController::class, 'export'])->whereNumber('id');
    Route::post('supirserap/approval', [SupirSerapController::class, 'approval']);
    Route::get('supirserap/field_length', [SupirSerapController::class, 'fieldLength']);
    Route::resource('supirserap', SupirSerapController::class)->whereNumber('supirserap');

    Route::get('laporanbiayasupir/export', [LaporanBiayaSupirController::class, 'export'])->name('laporanbiayasupir.export');
    Route::resource('laporanbiayasupir', LaporanBiayaSupirController::class)->whereNumber('laporanbiayasupir');

    Route::get('otobon/field_length', [OtobonController::class, 'fieldLength']);
    Route::get('otobon/export', [OtobonController::class, 'export']);
    Route::resource('otobon', OtobonController::class)->whereNumber('otobon');

    Route::get('lapangan/field_length', [LapanganController::class, 'fieldLength']);
    Route::get('lapangan/export', [LapanganController::class, 'export']);
    Route::resource('lapangan', LapanganController::class)->whereNumber('lapangan');

    Route::get('laporanhistorysupirmilikmandor/report', [LaporanHistorySupirMilikMandorController::class, 'report'])->name('laporanhistorysupirmilikmandor.report');
    Route::get('laporanhistorysupirmilikmandor/export', [LaporanHistorySupirMilikMandorController::class, 'export'])->name('laporanhistorysupirmilikmandor.export');
    Route::resource('laporanhistorysupirmilikmandor', LaporanHistorySupirMilikMandorController::class)->whereNumber('laporanhistorysupirmilikmandor');

    Route::get('laporanhistorytradomilikmandor/report', [LaporanHistoryTradoMilikMandorController::class, 'report'])->name('laporanhistorytradomilikmandor.report');
    Route::get('laporanhistorytradomilikmandor/export', [LaporanHistoryTradoMilikMandorController::class, 'export'])->name('laporanhistorytradomilikmandor.export');
    Route::resource('laporanhistorytradomilikmandor', LaporanHistoryTradoMilikMandorController::class)->whereNumber('laporanhistorytradomilikmandor');

    Route::get('laporanhistorytradomiliksupir/report', [LaporanHistoryTradoMilikSupirController::class, 'report'])->name('laporanhistorytradomiliksupir.report');
    Route::get('laporanhistorytradomiliksupir/export', [LaporanHistoryTradoMilikSupirController::class, 'export'])->name('laporanhistorytradomiliksupir.export');
    Route::resource('laporanhistorytradomiliksupir', LaporanHistoryTradoMilikSupirController::class)->whereNumber('laporanhistorytradomiliksupir');

    Route::get('laporanapprovalstokreuse/report', [LaporanApprovalStokReuseController::class, 'report'])->name('laporanapprovalstokreuse.report');
    Route::get('laporanapprovalstokreuse/export', [LaporanApprovalStokReuseController::class, 'export'])->name('laporanapprovalstokreuse.export');
    Route::resource('laporanapprovalstokreuse', LaporanApprovalStokReuseController::class);

    Route::get('exportperhitunganbonus/report', [ExportPerhitunganBonusController::class, 'report'])->name('exportperhitunganbonus.report');
    Route::get('exportperhitunganbonus/export', [ExportPerhitunganBonusController::class, 'export'])->name('exportperhitunganbonus.export');
    Route::resource('exportperhitunganbonus', ExportPerhitunganBonusController::class);
});
Route::get('suratpengantarapprovalinputtrip/updateapproval', [SuratPengantarApprovalInputTripController::class, 'updateApproval']);

Route::get('parameter/select/{grp}/{subgrp}/{text}', [ParameterController::class, 'getparameterid']);

Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('reset-password/{token}', [ForgotPasswordController::class, 'resetPassword'])->name('resetPassword');

Route::get('reminder-expstnk', [ExpStnkController::class, 'sendEmailReminder']);
Route::get('reminder-spk', [ReminderSpkController::class, 'sendEmailReminder']);
Route::get('/reminder-olimesin', [ReminderOliController::class, 'sendEmailReminder_olimesin']);
Route::get('/reminder-saringanhawa', [ReminderOliController::class, 'sendEmailReminder_saringanhawa']);
Route::get('/reminder-perseneling', [ReminderOliController::class, 'sendEmailReminder_perseneling']);
Route::get('/reminder-oligardan', [ReminderOliController::class, 'sendEmailReminder_oligardan']);
Route::get('/reminder-servicerutin', [ReminderOliController::class, 'sendEmailReminder_ServiceRutin']);
