<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\Supir;
use App\Models\Ritasi;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\GajiSupirBBM;
use Illuminate\Http\Request;
use App\Models\SuratPengantar;
use App\Models\GajiSupirDetail;
use App\Models\GajiSupirHeader;
use App\Models\JurnalUmumHeader;
use App\Models\GajiSupirDeposito;
use App\Models\GajiSupirPinjaman;
use App\Models\GajisUpirUangJalan;
use App\Models\PenerimaanTrucking;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranTruckingDetail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\GajiSupirPelunasanPinjaman;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\GetTripGajiSupirRequest;
use App\Http\Requests\StoreGajiSupirBBMRequest;
use App\Http\Requests\UpdateGajiSupirBBMRequest;
use App\Http\Requests\StoreGajiSupirDetailRequest;
use App\Http\Requests\StoreGajiSupirHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\UpdateGajiSupirHeaderRequest;
use App\Http\Requests\DestroyGajiSupirHeaderRequest;
use App\Http\Requests\StoreGajiSupirDepositoRequest;
use App\Http\Requests\StoreGajiSupirPinjamanRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\StoreGajisUpirUangJalanRequest;
use App\Http\Requests\UpdateGajiSupirDepositoRequest;
use App\Http\Requests\UpdateGajiSupirPinjamanRequest;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\StorePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;
use App\Http\Requests\StoreGajiSupirPelunasanPinjamanRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdateGajiSupirPelunasanPinjamanRequest;
use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GajiSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     * GajiSupirHeader
     * @Detail GajiSupirDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $gajiSupirHeader = new GajiSupirHeader();
        return response([
            'data' => $gajiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $gajiSupirHeader->totalRows,
                'totalPages' => $gajiSupirHeader->totalPages,
                'totalAll' => $gajiSupirHeader->totalAll,
                'totalUangJalan' => $gajiSupirHeader->totalUangJalan,
                'totalKomisiSupir' => $gajiSupirHeader->totalKomisiSupir,
                'totalGajiKenek' => $gajiSupirHeader->totalGajiKenek,
                'totalBiayaExtra' => $gajiSupirHeader->totalBiayaExtra,
                'totalBiayaExtraHeader' => $gajiSupirHeader->totalBiayaExtraHeader,
                'totalBbm' => $gajiSupirHeader->totalBbm,
                'totalDeposito' => $gajiSupirHeader->totalDeposito,
                'totalPotPinj' => $gajiSupirHeader->totalPotPinj,
                'totalPotSemua' => $gajiSupirHeader->totalPotSemua,
                'totalJenjang' => $gajiSupirHeader->totalJenjang,
                'totalMakan' => $gajiSupirHeader->totalMakan,
                'totalNominal' => $gajiSupirHeader->totalNominal,
                'totalGajiSupir' => $gajiSupirHeader->totalGajiSupir,
                'totalRitasiSupir' => $gajiSupirHeader->totalRitasiSupir,
            ]
        ]);
    }

    public function default()
    {
        $gajiSupir = new GajiSupirHeader();
        return response([
            'status' => true,
            'data' => $gajiSupir->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreGajiSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'supir_id' => $request->supir_id,
                'supir' => $request->supir,
                'tgldari' => $request->tgldari,
                'tglsampai' => $request->tglsampai,
                'uangjalan' => $request->uangjalan,
                'nomBBM' => $request->nomBBM,
                'nomDeposito' => $request->nomDeposito,
                'voucher' => $request->voucher,
                'biayaextra' => $request->biayaextraheader,
                'keteranganextra' => $request->keteranganextra,
                'uangmakanberjenjang' => $request->uangmakanberjenjang,
                'statusjeniskendaraan' => $request->statusjeniskendaraan,
                'uangmakanharian' => $request->uangmakanharian,
                'rincian_nobukti' => $request->rincian_nobukti,
                'rincian_ritasi' => $request->rincian_ritasi,
                'rincian_komisisupir' => $request->rincian_komisisupir,
                'rincian_tolsupir' => $request->rincian_tolsupir,
                'voucher' => $request->voucher,
                'novoucher' => $request->novoucher,
                'rincianId' => $request->rincianId,
                'rincian_gajisupir' => $request->rincian_gajisupir,
                'rincian_gajikenek' => $request->rincian_gajikenek,
                'rincian_upahritasi' => $request->rincian_upahritasi,
                'rincian_biayaextra' => $request->rincian_biayaextra,
                'rincian_keteranganbiaya' => $request->rincian_keteranganbiaya,
                'uangmakanjenjang' => $request->uangmakanjenjang,
                'pinjSemua' => $request->pinjSemua,
                'pinjSemua_nobukti' => $request->pinjSemua_nobukti,
                'nominalPS' => $request->nominalPS,
                'pinjSemua_keterangan' => $request->pinjSemua_keterangan,
                'pinjPribadi' => $request->pinjPribadi,
                'pinjPribadi_nobukti' => $request->pinjPribadi_nobukti,
                'nominalPP' => $request->nominalPP,
                'nomDeposito' => $request->nomDeposito,
                'pinjPribadi_keterangan' => $request->pinjPribadi_keterangan,
                'ketDeposito'   => $request->ketDeposito,
                'nomBBM' => $request->nomBBM,
                'ketBBM' => $request->ketBBM,
                'absensi_nobukti' => $request->absensi_nobukti,
                'absensi_uangjalan' => $request->absensi_uangjalan,
                'absensi_trado_id' => $request->absensi_trado_id,
                'rincian_biayaextrasupir_nobukti' => $request->rincian_biayaextrasupir_nobukti,
                'rincian_biayaextrasupir_nominal' => $request->rincian_biayaextrasupir_nominal,
                'rincian_biayaextrasupir_keterangan' => $request->rincian_biayaextrasupir_keterangan,
            ];
            $gajiSupirHeader = (new GajiSupirHeader())->processStore($data);

            if ($request->button == 'btnSubmit') {
                $gajiSupirHeader->position = $this->getPosition($gajiSupirHeader, $gajiSupirHeader->getTable())->position;

                if ($request->limit == 0) {
                    $gajiSupirHeader->page = ceil($gajiSupirHeader->position / (10));
                } else {
                    $gajiSupirHeader->page = ceil($gajiSupirHeader->position / ($request->limit ?? 10));
                }
            }
            $gajiSupirHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $gajiSupirHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $gajiSupirHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $gajisupir = new GajiSupirHeader();
        $data = (new GajiSupirHeader)->findAll($id);
        $deposito = (new GajiSupirDeposito)->findAll($data->nobukti);
        $BBM = (new GajiSupirBBM)->findAll($data->nobukti);
        $getTrip = $gajisupir->getEditTrip($id);
        $getUangjalan = $gajisupir->getEditAbsensi($id);
        return response([
            'status' => true,
            'data' => $data,
            'deposito' => $deposito,
            'bbm' => $BBM,
            'getTrip' => $getTrip,
            'getUangjalan' => $getUangjalan
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateGajiSupirHeaderRequest $request, GajiSupirHeader $gajisupirheader)
    {
        DB::beginTransaction();

        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'supir_id' => $request->supir_id,
                'supir' => $request->supir,
                'tgldari' => $request->tgldari,
                'tglsampai' => $request->tglsampai,
                'uangjalan' => $request->uangjalan,
                'nomBBM' => $request->nomBBM,
                'biayaextra' => $request->biayaextraheader,
                'keteranganextra' => $request->keteranganextra,
                'nomDeposito' => $request->nomDeposito,
                'voucher' => $request->voucher,
                'uangmakanharian' => $request->uangmakanharian,
                'uangmakanberjenjang' => $request->uangmakanberjenjang,
                'statusjeniskendaraan' => $request->statusjeniskendaraan,
                'rincian_nobukti' => $request->rincian_nobukti,
                'rincian_ritasi' => $request->rincian_ritasi,
                'rincian_komisisupir' => $request->rincian_komisisupir,
                'rincian_tolsupir' => $request->rincian_tolsupir,
                'voucher' => $request->voucher,
                'novoucher' => $request->novoucher,
                'rincianId' => $request->rincianId,
                'rincian_gajisupir' => $request->rincian_gajisupir,
                'rincian_gajikenek' => $request->rincian_gajikenek,
                'rincian_upahritasi' => $request->rincian_upahritasi,
                'rincian_biayaextra' => $request->rincian_biayaextra,
                'rincian_keteranganbiaya' => $request->rincian_keteranganbiaya,
                'uangmakanjenjang' => $request->uangmakanjenjang,
                'pinjSemua' => $request->pinjSemua,
                'pinjSemua_nobukti' => $request->pinjSemua_nobukti,
                'nominalPS' => $request->nominalPS,
                'pinjSemua_keterangan' => $request->pinjSemua_keterangan,
                'pinjPribadi' => $request->pinjPribadi,
                'pinjPribadi_nobukti' => $request->pinjPribadi_nobukti,
                'nominalPP' => $request->nominalPP,
                'nomDeposito' => $request->nomDeposito,
                'pinjPribadi_keterangan' => $request->pinjPribadi_keterangan,
                'ketDeposito'   => $request->ketDeposito,
                'nomBBM' => $request->nomBBM,
                'ketBBM' => $request->ketBBM,
                'absensi_nobukti' => $request->absensi_nobukti,
                'absensi_uangjalan' => $request->absensi_uangjalan,
                'absensi_trado_id' => $request->absensi_trado_id,
                'rincian_biayaextrasupir_nobukti' => $request->rincian_biayaextrasupir_nobukti,
                'rincian_biayaextrasupir_nominal' => $request->rincian_biayaextrasupir_nominal,
                'rincian_biayaextrasupir_keterangan' => $request->rincian_biayaextrasupir_keterangan,
            ];
            $gajiSupirHeader = (new GajiSupirHeader())->processUpdate($gajisupirheader, $data);

            $gajiSupirHeader->position = $this->getPosition($gajiSupirHeader, $gajiSupirHeader->getTable())->position;
            if ($request->limit == 0) {
                $gajiSupirHeader->page = ceil($gajiSupirHeader->position / (10));
            } else {
                $gajiSupirHeader->page = ceil($gajiSupirHeader->position / ($request->limit ?? 10));
            }
            $gajiSupirHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $gajiSupirHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $gajiSupirHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyGajiSupirHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $gajiSupirHeader = (new GajiSupirHeader())->processDestroy($id, 'DELETE GAJI SUPIR');
            $selected = $this->getPosition($gajiSupirHeader, $gajiSupirHeader->getTable(), true);

            $gajiSupirHeader->position = $selected->position;
            $gajiSupirHeader->id = $selected->id;
            if ($request->limit == 0) {
                $gajiSupirHeader->page = ceil($gajiSupirHeader->position / (10));
            } else {
                $gajiSupirHeader->page = ceil($gajiSupirHeader->position / ($request->limit ?? 10));
            }
            $gajiSupirHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $gajiSupirHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $gajiSupirHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getTrip(GetTripGajiSupirRequest $request)
    {
        $gajiSupir = new GajiSupirHeader();

        $dari = $request->tgldari;
        $sampai = $request->tglsampai;
        $supir_id = $request->supir_id;
        $statusjeniskendaraan = $request->statusjeniskendaraan;
        $tglDari = date('Y-m-d', strtotime($dari));
        $tglSampai = date('Y-m-d', strtotime($sampai));

        $cekSP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
            ->where('tglbukti', '>=', $tglDari)
            ->where('tglbukti', '<=', $tglSampai)
            ->where('supir_id', $supir_id)->first();

        // CEK APAKAH ADA SP UNTUK DATA TERSEBUT
        if ($cekSP) {
            $nobukti = $cekSP->nobukti;
            $cekTrip = GajiSupirDetail::from(DB::raw("gajisupirdetail with (readuncommitted)"))->where('suratpengantar_nobukti', $nobukti)->first();


            return response([
                'errors' => false,
                'data' => $gajiSupir->getTrip($supir_id, $tglDari, $tglSampai, $statusjeniskendaraan),
                'attributes' => [
                    'totalRows' => $gajiSupir->totalRows,
                    'totalPages' => $gajiSupir->totalPages,
                    'totalGajiSupir' => $gajiSupir->totalGajiSupir,
                    'totalGajiKenek' => $gajiSupir->totalGajiKenek,
                    'totalKomisiSupir' => $gajiSupir->totalKomisiSupir,
                    'totalUpahRitasi' => $gajiSupir->totalUpahRitasi,
                    'totalBiayaExtra' => $gajiSupir->totalBiayaExtra,
                    'totalTolSupir' => $gajiSupir->totalTolSupir,
                ]
            ]);
        } else {
            return response([
                'data' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function getPinjSemua()
    {
        $gajiSupir = new GajiSupirHeader();
        return response([
            'data' => $gajiSupir->getPinjSemua()
        ]);
    }

    public function getPinjPribadi($supir_id)
    {
        $gajiSupir = new GajiSupirHeader();
        return response([
            'data' => $gajiSupir->getPinjPribadi($supir_id)
        ]);
    }
    public function getEditTrip(GetTripGajiSupirRequest $request, $gajiId)
    {
        $gajisupir = new GajiSupirHeader();
        $aksi = request()->aksi;
        if ($aksi == 'edit') {
            $supir_id = request()->supir_id;
            $statusjeniskendaraan = request()->statusjeniskendaraan;
            $dari = date('Y-m-d', strtotime(request()->tgldari));
            $sampai = date('Y-m-d', strtotime(request()->tglsampai));
            $data = $gajisupir->getAllEditTrip($gajiId, $supir_id, $dari, $sampai, $statusjeniskendaraan);
        } else {
            $data = $gajisupir->getEditTrip($gajiId);
        }

        return response([
            'data' => $data,
            'attributes' => [
                'totalRows' => $gajisupir->totalRows,
                'totalPages' => $gajisupir->totalPages,
                'totalGajiSupir' => $gajisupir->totalGajiSupir,
                'totalGajiKenek' => $gajisupir->totalGajiKenek,
                'totalKomisiSupir' => $gajisupir->totalKomisiSupir,
                'totalUpahRitasi' => $gajisupir->totalUpahRitasi,
                'totalBiayaExtra' => $gajisupir->totalBiayaExtra,
                'totalTolSupir' => $gajisupir->totalTolSupir,
            ]
        ]);
    }

    public function getUangJalan()
    {
        $tglbukti = date('Y-m-d', strtotime(request()->tglbukti));
        $supir_id = request()->supir_id;
        $dari = date('Y-m-d', strtotime(request()->dari));
        $sampai = date('Y-m-d', strtotime(request()->sampai));

        $cekRic = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('tglbukti', $tglbukti)->where('supir_id', $supir_id)->first();

        if ($cekRic == null) {
            $gajisupir = new GajiSupirHeader();
            $uangjalan = $gajisupir->getUangJalan($supir_id, $dari, $sampai);
            return response([
                'data' => $uangjalan
            ]);
        }
    }

    public function noEdit()
    {
        $query = Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'RICX')
            ->first();
        return response([
            'message' => "$query->keterangan",
        ]);
    }

    public function cekvalidasi($id)
    {
        $gajisupir = GajiSupirHeader::find($id);
        $nobukti = $gajisupir->nobukti;
        $statusdatacetak = $gajisupir->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $cabang = (new Parameter())->cekText('CABANG', 'CABANG');

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));

        $aksi = request()->aksi ?? '';
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('gajisupirheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $gajisupir->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' ) <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($useredit != '' && $useredit != $user) {

            $waktu = (new Parameter())->cekBatasWaktuEdit('gaji supir header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->createLockEditing($id, 'gajisupirheader', $useredit);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $gajisupir->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }
        } else {
            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                if($aksi == 'PRINTER' && $cabang == 'MEDAN'){
                    $cekApproval = (new GajiSupirHeader())->cekApprovalMandor($gajisupir->nobukti);
                    if($cekApproval['kondisi'] == true) {
                        $data = [
                            'error' => true,
                            'message' => $cekApproval['keterangan'],
                            'kodeerror' => 'BAP',
                            'statuspesan' => 'warning',
                        ];
                        
                        return response($data);
                    }
                }
                (new MyModel())->createLockEditing($id, 'gajisupirheader', $useredit);
            }

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
    public function cekValidasiAksi($id)
    {
        $gajisupir = new GajiSupirHeader();
        $nobukti = GajiSupirHeader::from(DB::raw("gajisupirheader"))->where('id', $id)->first();
        $cekdata = $gajisupir->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->first();
            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'],
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {
            $getEditing = (new Locking())->getEditing('gajisupirheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'gajisupirheader', $useredit);

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];


            return response($data);
        }
    }
    private function storeJurnal($header, $detail)
    {

        try {
            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            $detailLog = [];
            foreach ($detail as $key => $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $jurnal = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($jurnal);

                $detailLog[] = $datadetails['detail']->toArray();
            }
            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => $header['postingdari'],
                'idtrans' => $jurnals->original['idlogtrail'],
                'nobuktitrans' => $header['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getEditPinjSemua($id, $aksi)
    {
        $data = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('id', $id)->first();
        $pinjamanSemua = new GajiSupirPelunasanPinjaman();

        if ($aksi == 'edit') {
            $data = $pinjamanSemua->getPinjamanSemua($data->nobukti);
        } else {
            $data = $pinjamanSemua->getDeletePinjSemua($data->nobukti);
        }
        return response([
            'data' => $data
        ]);
    }

    public function getEditPinjPribadi($id, $supirId, $aksi)
    {
        $data = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->where('id', $id)->first();
        $pinjamanPribadi = new GajiSupirPelunasanPinjaman();

        if ($aksi == 'edit') {
            $data = $pinjamanPribadi->getPinjamanPribadi($data->nobukti, $supirId);
        } else {
            $data = $pinjamanPribadi->getDeletePinjPribadi($data->nobukti, $supirId);
        }
        return response([
            'data' => $data
        ]);
    }

    public function getAbsensi()
    {
        $gajiSupir = new GajiSupirHeader();

        $statusjeniskendaraan = request()->statusjeniskendaraan;
        $supir_id = request()->supir_id;
        $tglDari = date('Y-m-d', strtotime(request()->tgldari));
        $tglSampai = date('Y-m-d', strtotime(request()->tglsampai));
        $data = $gajiSupir->getAbsensi($supir_id, $tglDari, $tglSampai, $statusjeniskendaraan);
        if ($data != null) {
            return response([
                'errors' => false,
                'data' => $data,
                'attributes' => [
                    'totalRows' => $gajiSupir->totalRows,
                    'totalPages' => $gajiSupir->totalPages,
                    'uangjalan' => $gajiSupir->totalUangJalan,
                ]
            ]);
        } else {
            return response([
                'errors' => false,
                'data' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                    'uangjalan' => 0,
                ]
            ]);
        }
    }

    public function getEditAbsensi($gajiId)
    {
        $gajisupir = new GajiSupirHeader();
        $aksi = request()->aksi;
        if ($aksi == 'edit') {
            $supir_id = request()->supir_id;
            $statusjeniskendaraan = request()->statusjeniskendaraan;
            $dari = date('Y-m-d', strtotime(request()->tgldari));
            $sampai = date('Y-m-d', strtotime(request()->tglsampai));
            $data = $gajisupir->getAllEditAbsensi($gajiId, $supir_id, $dari, $sampai, $statusjeniskendaraan);
        } else {
            $data = $gajisupir->getEditAbsensi($gajiId);
        }

        return response([
            'data' => $data,
            'attributes' => [
                'totalRows' => $gajisupir->totalRows,
                'totalPages' => $gajisupir->totalPages,
                'uangjalan' => $gajisupir->totalUangJalan,
            ]
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('gajisupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $gajisupir = GajiSupirHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($gajisupir->statuscetak != $statusSudahCetak->id) {
                $gajisupir->statuscetak = $statusSudahCetak->id;
                // $gajisupir->tglbukacetak = date('Y-m-d H:i:s');
                // $gajisupir->userbukacetak = auth('api')->user()->name;
                $gajisupir->jumlahcetak = $gajisupir->jumlahcetak + 1;
                if ($gajisupir->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($gajisupir->getTable()),
                        'postingdari' => 'PRINT GAJI SUPIR HEADER',
                        'idtrans' => $gajisupir->id,
                        'nobuktitrans' => $gajisupir->id,
                        'aksi' => 'PRINT',
                        'datajson' => $gajisupir->toArray(),
                        'modifiedby' => $gajisupir->modifiedby
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    DB::commit();
                }
            }
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report() {}



    /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak() {}
    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas() {}

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id, Request $request)
    {
        $gajiSupirHeader = new GajiSupirHeader();
        $gaji_SupirHeader = $gajiSupirHeader->getExport($id);

        if ($request->export == true) {
            $gajiSupirDetail = new GajiSupirDetail();
            $gaji_SupirDetail = $gajiSupirDetail->get();

            $tglBukti = $gaji_SupirHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $gaji_SupirHeader->tglbukti = $dateTglBukti;

            //PRINT TO EXCEL
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $gaji_SupirHeader->judul);
            $sheet->setCellValue('A2', $gaji_SupirHeader->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:N1');
            $sheet->mergeCells('A2:N2');

            $header_start_row = 4;
            $detail_table_header_row = 8;
            $detail_start_row = $detail_table_header_row + 1;

            $alphabets = range('A', 'Z');

            $header_columns = [
                [
                    'label' => 'No Bukti',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'Tanggal',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'Supir',
                    'index' => 'supir_id',
                ]
            ];
            if ($gaji_SupirHeader->formatcetak == 'FORMAT 3') {
                $header_down_columns = [
                    [
                        'label' => 'Sub Total',
                        'index' => 'total',
                    ],
                    [
                        'label' => 'Uang Jalan',
                        'index' => 'uangjalan',
                    ],
                    [
                        'label' => 'BBM',
                        'index' => 'bbm',
                    ],
                    [
                        'label' => 'Pinjaman Pribadi',
                        'index' => 'potonganpinjaman',
                    ],
                    [
                        'label' => 'FGajiMinus',
                        'index' => 'gajiminus',
                    ],
                    [
                        'label' => 'Pinjaman Bersama',
                        'index' => 'potonganpinjamansemua',
                    ],
                    [
                        'label' => 'Deposito',
                        'index' => 'deposito',
                    ],
                    [
                        'label' => 'Uang Makan',
                        'index' => 'uangmakanharian',
                    ],
                    [
                        'label' => 'Total Sebelum Uang Makan',
                        'index' => 'sebelumuangmakan',
                    ],
                    [
                        'label' => 'Total',
                        'index' => 'sisa',
                    ]
                ];
            } else {
                $header_down_columns = [
                    [
                        'label' => 'TOTAL UANG BORONGAN',
                        'index' => 'total',
                    ],
                    [
                        'label' => 'UANG MAKAN',
                        'index' => 'uangmakanharian',
                    ],
                    [
                        'label' => 'UANG MAKAN BERJENJANG',
                        'index' => 'uangmakanberjenjang',
                    ],
                    [
                        'label' => 'TOTAL POTONGAN UANG JALAN',
                        'index' => 'uangjalan',
                    ],
                    [
                        'label' => 'TOTAL POTONGAN PINJAMAN',
                        'index' => 'potonganpinjaman',
                    ],
                    [
                        'label' => 'TOTAL POTONGAN PINJAMAN SEMUA',
                        'index' => 'potonganpinjamansemua',
                    ],
                    [
                        'label' => 'TOTAL DEPOSITO',
                        'index' => 'deposito',
                    ],
                    [
                        'label' => 'TOTAL POTONGAN BBM',
                        'index' => 'bbm',
                    ],
                    [
                        'label' => 'SISA YANG DITERIMA SUPIR',
                        'index' => 'sisa',
                    ]
                ];
            }
            if ($gaji_SupirHeader->formatcetak == 'FORMAT 3') {
                $detail_columns = [
                    [
                        'label' => 'NO',
                    ],
                    [
                        'label' => 'TANGGAL',
                        'index' => 'tglsp',
                    ],
                    [
                        'label' => 'PLAT & TUJUAN',
                        'index' => 'tujuan',
                    ],
                    [
                        'label' => 'QTY',
                        'index' => 'qty',
                    ],
                    [
                        'label' => 'NO CONT & SEAL',
                        'index' => 'nocontseal',
                    ],
                    [
                        'label' => 'EMKL',
                        'index' => 'emkl',
                    ],
                    [
                        'label' => 'LITER',
                        'index' => 'liter',
                    ],
                    [
                        'label' => 'FULL',
                        'index' => 'spfull',
                    ],
                    [
                        'label' => 'EMPTY',
                        'index' => 'spempty',
                    ],
                    [
                        'label' => 'BORONGAN',
                        'index' => 'borongan',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'RITASI',
                        'index' => 'gajiritasi',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'BIAYA EXTRA',
                        'index' => 'biayaextra',
                        'format' => 'currency'
                    ]
                ];
            } else {
                $detail_columns = [
                    [
                        'label' => 'NO',
                    ],
                    [
                        'label' => 'TANGGAL',
                        'index' => 'tglsp',
                    ],
                    [
                        'label' => 'NO SP',
                        'index' => 'nosp',
                    ],
                    [
                        'label' => 'STATUS',
                        'index' => 'kodestatuscontainer',
                    ],
                    [
                        'label' => 'DARI',
                        'index' => 'dari',
                    ],
                    [
                        'label' => 'SAMPAI',
                        'index' => 'sampai',
                    ],
                    [
                        'label' => 'RITASI',
                        'index' => 'statusritasi',
                    ],
                    [
                        'label' => 'UK. CONT',
                        'index' => 'kodecontainer',
                    ],
                    [
                        'label' => 'LITER',
                        'index' => 'liter',
                    ],
                    [
                        'label' => 'NO CONT',
                        'index' => 'nocont',
                    ],
                    [
                        'label' => 'CUSTOMER',
                        'index' => 'agen',
                    ],
                    [
                        'label' => 'BORONGAN',
                        'index' => 'borongan',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'EXTRA',
                        'index' => 'biayaextra',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'RITASI',
                        'index' => 'upahritasi',
                        'format' => 'currency'
                    ]
                ];
            }

            //LOOPING HEADER   
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                if ($gaji_SupirHeader->formatcetak == 'FORMAT 3') {
                    if ($header_column['index'] == 'supir_id') {
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $gaji_SupirHeader->supir_id . ' (' . $gaji_SupirHeader->trado . ')');
                    } else {
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $gaji_SupirHeader->{$header_column['index']});
                    }
                } else {
                    $sheet->setCellValue('C' . $header_start_row++, ': ' . $gaji_SupirHeader->{$header_column['index']});
                }
            }
            foreach ($detail_columns as $detail_columns_index => $detail_column) {
                $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
            }
            $styleArray = array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ),
            );

            $style_number = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],

                'borders' => [
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ]
            ];

            $style_number_2 = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ]
            ];

            // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
            if ($gaji_SupirHeader->formatcetak == 'FORMAT 3') {
                $sheet->getStyle("A$detail_table_header_row:L$detail_table_header_row")->applyFromArray($styleArray);
            } else {
                $sheet->getStyle("A$detail_table_header_row:N$detail_table_header_row")->applyFromArray($styleArray);
            }

            // LOOPING DETAIL
            $liter = 0;
            $borongan = 0;
            $biayaextra = 0;
            $upahritasi = 0;
            foreach ($gaji_SupirDetail as $response_index => $response_detail) {

                // foreach ($detail_columns as $detail_columns_index => $detail_column) {
                //     $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                //     $sheet->getStyle("A$detail_table_header_row:N$detail_table_header_row")->getFont()->setBold(true);
                //     $sheet->getStyle("A$detail_table_header_row:N$detail_table_header_row")->getAlignment()->setHorizontal('center');
                // }
                if ($gaji_SupirHeader->formatcetak == 'FORMAT 3') {
                    $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $dateValue);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->tujuan);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->qty);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->nocontseal);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->emkl);
                    $sheet->setCellValue("G$detail_start_row", $response_detail->liter);
                    $sheet->setCellValue("H$detail_start_row", $response_detail->spfull);
                    $sheet->setCellValue("I$detail_start_row", $response_detail->spempty);
                    $sheet->setCellValue("J$detail_start_row", $response_detail->borongan);
                    $sheet->setCellValue("K$detail_start_row", $response_detail->gajiritasi);
                    $sheet->setCellValue("L$detail_start_row", $response_detail->biayaextra);

                    $sheet->getStyle("A$detail_start_row:L$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("J$detail_start_row:L$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $sheet->getStyle("B$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                } else {
                    $dateValue = ($response_detail->tglsp != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglsp))) : '';

                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $dateValue);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->nosp);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->kodestatuscontainer);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->dari);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->sampai);
                    $sheet->setCellValue("G$detail_start_row", $response_detail->statusritasi);
                    $sheet->setCellValue("H$detail_start_row", $response_detail->kodecontainer);
                    $sheet->setCellValue("I$detail_start_row", $response_detail->liter);
                    $sheet->setCellValue("J$detail_start_row", $response_detail->nocont);
                    $sheet->setCellValue("K$detail_start_row", $response_detail->agen);
                    $sheet->setCellValue("L$detail_start_row", $response_detail->borongan);
                    $sheet->setCellValue("M$detail_start_row", $response_detail->biayaextra);
                    $sheet->setCellValue("N$detail_start_row", $response_detail->upahritasi);

                    $sheet->getStyle("A$detail_start_row:N$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("L$detail_start_row:N$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $sheet->getStyle("B$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                }
                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            if ($gaji_SupirHeader->formatcetak == 'FORMAT 3') {
                $sheet->setCellValue("E$total_start_row", 'Total')->getStyle('E' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $liter = "=SUM(G" . ($detail_table_header_row + 1) . ":G" . ($detail_start_row - 1) . ")";
                $sheet->setCellValue("G$total_start_row", $liter)->getStyle("G$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

                $borongan = "=SUM(J" . ($detail_table_header_row + 1) . ":J" . ($detail_start_row - 1) . ")";
                $sheet->setCellValue("J$total_start_row", $borongan)->getStyle("J$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

                $biayaextra = "=SUM(L" . ($detail_table_header_row + 1) . ":L" . ($detail_start_row - 1) . ")";
                $sheet->setCellValue("L$total_start_row", $biayaextra)->getStyle("L$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

                $upahritasi = "=SUM(K" . ($detail_table_header_row + 1) . ":K" . ($detail_start_row - 1) . ")";
                $sheet->setCellValue("K$total_start_row", $upahritasi)->getStyle("K$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->getStyle("G$total_start_row:K$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            } else {

                $sheet->setCellValue("G$total_start_row", 'Tot Liter')->getStyle('G' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $liter = "=SUM(I" . ($detail_table_header_row + 1) . ":I" . ($detail_start_row - 1) . ")";
                $sheet->setCellValue("I$total_start_row", $liter)->getStyle("I$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $borongan = "=SUM(L" . ($detail_table_header_row + 1) . ":L" . ($detail_start_row - 1) . ")";
                $sheet->setCellValue("L$total_start_row", $borongan)->getStyle("L$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $biayaextra = "=SUM(M" . ($detail_table_header_row + 1) . ":M" . ($detail_start_row - 1) . ")";
                $sheet->setCellValue("M$total_start_row", $biayaextra)->getStyle("M$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $upahritasi = "=SUM(N" . ($detail_table_header_row + 1) . ":N" . ($detail_start_row - 1) . ")";
                $sheet->setCellValue("N$total_start_row", $upahritasi)->getStyle("N$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->getStyle("L$total_start_row:N$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            }

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            $sheet->getColumnDimension('J')->setAutoSize(true);
            $sheet->getColumnDimension('K')->setAutoSize(true);
            $sheet->getColumnDimension('L')->setAutoSize(true);
            $sheet->getColumnDimension('M')->setAutoSize(true);
            $sheet->getColumnDimension('N')->setAutoSize(true);

            $header_down_row = $total_start_row + 2;
            $header_down_value_row = $total_start_row + 2;
            $sisapinjaman_row = $total_start_row + 2;
            if ($gaji_SupirHeader->formatcetak == 'FORMAT 3') {

                foreach ($header_down_columns as $header_down_column) {
                    $sheet->setCellValue('H' . $header_down_row, $header_down_column['label']);
                    $header_down_row++;

                    $cellCoordinate = 'I' . $header_down_value_row++;
                    if ($header_down_column['index'] == 'gajiminus') {
                        $sheet->setCellValue($cellCoordinate, 0);
                    } else {
                        $sheet->setCellValue($cellCoordinate, $gaji_SupirHeader->{$header_down_column['index']});
                    }
                    $sheet->getStyle($cellCoordinate)->applyFromArray($style_number_2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                }
                $sheet->setCellValue('K' . $sisapinjaman_row, 'Sisa Pinjaman');
                $sheet->setCellValue('L' . $sisapinjaman_row, $gaji_SupirHeader->sisapinjaman);
                $sheet->getStyle('L' . $sisapinjaman_row)->applyFromArray($style_number_2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sisapinjaman_row++;
                $sheet->setCellValue('K' . $sisapinjaman_row, 'Saldo Deposito');
                $sheet->setCellValue('L' . $sisapinjaman_row, $gaji_SupirHeader->sisadeposito);
                $sheet->getStyle('L' . $sisapinjaman_row)->applyFromArray($style_number_2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            } else {

                foreach ($header_down_columns as $header_down_column) {
                    $sheet->setCellValue('L' . $header_down_row, $header_down_column['label']);
                    $sheet->setCellValue('M' . $header_down_row++, ':');

                    $cellCoordinate = 'N' . $header_down_value_row++;
                    $sheet->setCellValue($cellCoordinate, $gaji_SupirHeader->{$header_down_column['index']});
                    $sheet->getStyle($cellCoordinate)->applyFromArray($style_number_2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                }
            }
            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Rincian Gaji Supir' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $gaji_SupirHeader
            ]);
        }
    }
}
