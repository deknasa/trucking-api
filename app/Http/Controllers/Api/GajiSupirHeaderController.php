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
            $gajiSupirHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $gajiSupirHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            if ($request->button == 'btnSubmit') {
                $gajiSupirHeader->position = $this->getPosition($gajiSupirHeader, $gajiSupirHeader->getTable())->position;

                if ($request->limit == 0) {
                    $gajiSupirHeader->page = ceil($gajiSupirHeader->position / (10));
                } else {
                    $gajiSupirHeader->page = ceil($gajiSupirHeader->position / ($request->limit ?? 10));
                }
            }
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
            $gajiSupirHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $gajiSupirHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            $gajiSupirHeader->position = $this->getPosition($gajiSupirHeader, $gajiSupirHeader->getTable())->position;
            if ($request->limit == 0) {
                $gajiSupirHeader->page = ceil($gajiSupirHeader->position / (10));
            } else {
                $gajiSupirHeader->page = ceil($gajiSupirHeader->position / ($request->limit ?? 10));
            }
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
            $gajiSupirHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $gajiSupirHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            $gajiSupirHeader->position = $selected->position;
            $gajiSupirHeader->id = $selected->id;
            if ($request->limit == 0) {
                $gajiSupirHeader->page = ceil($gajiSupirHeader->position / (10));
            } else {
                $gajiSupirHeader->page = ceil($gajiSupirHeader->position / ($request->limit ?? 10));
            }

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
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->createLockEditing($id, 'gajisupirheader',$useredit);
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
                (new MyModel())->createLockEditing($id, 'gajisupirheader',$useredit);
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
            (new MyModel())->createLockEditing($id, 'gajisupirheader',$useredit);

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
    public function report()
    {
    }



    /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak()
    {
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $gajiSupirHeader = new GajiSupirHeader();
        return response([
            'data' => $gajiSupirHeader->getExport($id),
        ]);
    }
}
