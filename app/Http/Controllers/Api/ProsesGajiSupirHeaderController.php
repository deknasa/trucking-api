<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Exception;
use App\Models\Bank;
use App\Models\Error;
use App\Models\Supir;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\GajiSupirBBM;
use Illuminate\Http\Request;
use App\Models\SuratPengantar;
use App\Models\GajiSupirHeader;
use App\Rules\GetRicEditRequst;
use App\Models\JurnalUmumHeader;
use App\Models\PenerimaanHeader;
use App\Models\GajiSupirDeposito;
use App\Models\GajiSupirPinjaman;
use App\Models\PengeluaranHeader;
use Illuminate\Http\JsonResponse;
use App\Models\GajisUpirUangJalan;
use App\Models\PenerimaanTrucking;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetRicRequest;
use App\Models\ProsesGajiSupirDetail;
use App\Models\ProsesGajiSupirHeader;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetRicEditRequest;
use App\Models\PenerimaanTruckingDetail;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranTruckingDetail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\GajiSupirPelunasanPinjaman;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\PengembalianKasGantungHeader;
use App\Http\Requests\DestroyJurnalUmumRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Http\Requests\DestroyPenerimaanHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Http\Requests\DestroyPengeluaranHeaderRequest;
use App\Http\Requests\StoreProsesGajiSupirDetailRequest;
use App\Http\Requests\StoreProsesGajiSupirHeaderRequest;
use App\Http\Controllers\ProsesGajiSupirDetailController;
use App\Http\Requests\UpdateProsesGajiSupirHeaderRequest;
use App\Http\Requests\DestroyProsesGajiSupirHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;
use App\Http\Requests\StorePengembalianKasGantungHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasGantungHeaderRequest;
use App\Http\Requests\DestroyPengembalianKasGantungHeaderRequest;
use App\Http\Controllers\Api\ProsesGajiSupirDetailController as ApiProsesGajiSupirDetailController;

class ProsesGajiSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     * ProsesGajiSupirHeader
     * @Detail ProsesGajiSupirDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $prosesGajiSupirHeader = new ProsesGajiSupirHeader();
        return response([
            'data' => $prosesGajiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $prosesGajiSupirHeader->totalRows,
                'totalPages' => $prosesGajiSupirHeader->totalPages,
                'totalAll' => $prosesGajiSupirHeader->totalAll,
                'totalPosting' => $prosesGajiSupirHeader->totalPosting,
                'totalGajiSupir' => $prosesGajiSupirHeader->totalGajiSupir,
                'totalGajiKenek' => $prosesGajiSupirHeader->totalGajiKenek,
                'totalKomisiSupir' => $prosesGajiSupirHeader->totalKomisiSupir,
                'totalBiayaExtra' => $prosesGajiSupirHeader->totalBiayaExtra,
                'totalBiayaExtraHeader' => $prosesGajiSupirHeader->totalBiayaExtraHeader,
                'totalJalan' => $prosesGajiSupirHeader->totalJalan,
                'totalBbm' => $prosesGajiSupirHeader->totalBbm,
                'totalMakan' => $prosesGajiSupirHeader->totalMakan,
                'totalMakanBerjenjang' => $prosesGajiSupirHeader->totalMakanBerjenjang,
                'totalPotPinj' => $prosesGajiSupirHeader->totalPotPinj,
                'totalPotSemua' => $prosesGajiSupirHeader->totalPotSemua,
                'totalDeposito' => $prosesGajiSupirHeader->totalDeposito,
            ]
        ]);
    }

    public function default()
    {
        $prosesGaji = new ProsesGajiSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesGaji->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreProsesGajiSupirHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            $requestData = json_decode($request->dataric, true);
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'tgldari' => date('Y-m-d', strtotime($request->tgldari)),
                'tglsampai' => date('Y-m-d', strtotime($request->tglsampai)),
                'statusjeniskendaraan' => $request->statusjeniskendaraan,
                'bank_id' => $request->bank_id,
                'rincianId' => $requestData['rincianId'],
                'nobuktiRIC' => $requestData['nobuktiRIC'],
                'supir_id' => $requestData['supir_id'],
                'totalborongan' => $requestData['totalborongan'],
                'gajikenek' => $requestData['gajikenek'],
                'nomPS' => $request->nomPS,
                'nomPP' => $request->nomPP,
                'nomDeposito' => $request->nomDeposito,
                'nomBBM' => $request->nomBBM,
                'nomUangjalan' => $request->nomUangjalan
            ];

            $prosesGajiSupirHeader = (new ProsesGajiSupirHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $prosesGajiSupirHeader->position = $this->getPosition($prosesGajiSupirHeader, $prosesGajiSupirHeader->getTable())->position;
                if ($request->limit == 0) {
                    $prosesGajiSupirHeader->page = ceil($prosesGajiSupirHeader->position / (10));
                } else {
                    $prosesGajiSupirHeader->page = ceil($prosesGajiSupirHeader->position / ($request->limit ?? 10));
                }
                $prosesGajiSupirHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $prosesGajiSupirHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $prosesGajiSupirHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $proses = new ProsesGajiSupirHeader();
        $prosesGajiSupirHeader = $proses->findAll($id);
        $semua = $proses->showPotSemua($id);
        $pribadi = $proses->showPotPribadi($id);
        $deposito = $proses->showDeposito($id);
        $bbm = $proses->showBBM($id);
        $Uangjalan = $proses->showUangjalan($id);
        $getTrip = $proses->getEdit($id, '', $prosesGajiSupirHeader->statusjeniskendaraan);

        return response([
            'status' => true,
            'data' => $prosesGajiSupirHeader,
            'potsemua' => $semua,
            'potpribadi' => $pribadi,
            'deposito' => $deposito,
            'bbm' => $bbm,
            'uangjalan' => $Uangjalan,
            'getTrip' => $getTrip
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateProsesGajiSupirHeaderRequest $request, ProsesGajiSupirHeader $prosesgajisupirheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $requestData = json_decode($request->dataric, true);
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'tgldari' => date('Y-m-d', strtotime($request->tgldari)),
                'tglsampai' => date('Y-m-d', strtotime($request->tglsampai)),
                'statusjeniskendaraan' => $request->statusjeniskendaraan,
                'bank_id' => $request->bank_id,
                'rincianId' => $requestData['rincianId'],
                'nobuktiRIC' => $requestData['nobuktiRIC'],
                'supir_id' => $requestData['supir_id'],
                'totalborongan' => $requestData['totalborongan'],
                'gajikenek' => $requestData['gajikenek'],
                'nomPS' => $request->nomPS,
                'nomPP' => $request->nomPP,
                'nomDeposito' => $request->nomDeposito,
                'nomBBM' => $request->nomBBM,
                'nomUangjalan' => $request->nomUangjalan,
                'nobuktiUangjalan' => $request->nobuktiUangjalan
            ];

            $prosesGajiSupirHeader = (new ProsesGajiSupirHeader())->processUpdate($prosesgajisupirheader, $data);
            $prosesGajiSupirHeader->position = $this->getPosition($prosesGajiSupirHeader, $prosesGajiSupirHeader->getTable())->position;
            if ($request->limit == 0) {
                $prosesGajiSupirHeader->page = ceil($prosesGajiSupirHeader->position / (10));
            } else {
                $prosesGajiSupirHeader->page = ceil($prosesGajiSupirHeader->position / ($request->limit ?? 10));
            }
            $prosesGajiSupirHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $prosesGajiSupirHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $prosesGajiSupirHeader
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
    public function destroy(DestroyProsesGajiSupirHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $nobuktiUangJalan = $request->nobuktiUangjalan ?? '';
            $prosesGajiSupirHeader = (new ProsesGajiSupirHeader())->processDestroy($id, 'DELETE PROSES GAJI SUPIR', $nobuktiUangJalan);
            $selected = $this->getPosition($prosesGajiSupirHeader, $prosesGajiSupirHeader->getTable(), true);
            $prosesGajiSupirHeader->position = $selected->position;
            $prosesGajiSupirHeader->id = $selected->id;
            if ($request->limit == 0) {
                $prosesGajiSupirHeader->page = ceil($prosesGajiSupirHeader->position / (10));
            } else {
                $prosesGajiSupirHeader->page = ceil($prosesGajiSupirHeader->position / ($request->limit ?? 10));
            }
            $prosesGajiSupirHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $prosesGajiSupirHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $prosesGajiSupirHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getRic(GetRicRequest $request)
    {
        // dd('test');
        $gajiSupir = new ProsesGajiSupirHeader();
        $dari = date('Y-m-d', strtotime(request()->tgldari));
        $sampai = date('Y-m-d', strtotime(request()->tglsampai));
        $statusjeniskendaraan = request()->statusjeniskendaraan;

        $cekRic = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->whereRaw("tglbukti >= '$dari'")
            ->whereRaw("tglbukti <= '$sampai'")
            ->first();

        $cekRicsaldo = db::table("saldogajisupirheader")->from(DB::raw("saldogajisupirheader with (readuncommitted)"))
            ->whereRaw("tglbukti >= '$dari'")
            ->whereRaw("tglbukti <= '$sampai'")
            ->first();

        //CEK APAKAH ADA RIC
        if (isset($cekRic) || isset($cekRicsaldo)) {
            if (isset($cekRic)) {
                $nobukti = $cekRic->nobukti ?? '';
            } else {
                $nobukti = $cekRicsaldo->nobukti ?? '';
            }

            $cekEBS = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))
                ->whereRaw("gajisupir_nobukti = '$nobukti'")->first();

            return response([
                'errors' => false,
                'data' => $gajiSupir->getRic($dari, $sampai, $statusjeniskendaraan),
                'attributes' => [
                    'totalRows' => $gajiSupir->totalRows,
                    'totalPages' => $gajiSupir->totalPages,
                    'totalBorongan' => $gajiSupir->totalBorongan,
                    'totalUangJalan' => $gajiSupir->totalUangJalan,
                    'totalUangBBM' => $gajiSupir->totalUangBBM,
                    'totalUangMakan' => $gajiSupir->totalUangMakan,
                    'totalUangMakanBerjenjang' => $gajiSupir->totalUangMakanBerjenjang,
                    'totalBiayaExtraHeader' => $gajiSupir->totalBiayaExtraHeader,
                    'totalPotPinjaman' => $gajiSupir->totalPotPinjaman,
                    'totalPotPinjSemua' => $gajiSupir->totalPotPinjSemua,
                    'totalDeposito' => $gajiSupir->totalDeposito,
                    'totalKomisi' => $gajiSupir->totalKomisi,
                    'totalTol' => $gajiSupir->totalTol,
                    'totalGajiSupir' => $gajiSupir->totalGajiSupir,
                    'totalGajiKenek' => $gajiSupir->totalGajiKenek,
                ]
            ]);
        } else {


            // $cekRic = DB::table("saldogajisupirheader")->from(DB::raw("saldogajisupirheader with (readuncommitted)"))
            //     ->whereRaw("tglbukti >= '$dari'")
            //     ->whereRaw("tglbukti <= '$sampai'")
            //     ->first();
            // if ($cekRic) {

            //     $nobukti = $cekRic->nobukti;
            //     return response([
            //         'errors' => false,
            //         'data' => $gajiSupir->getRic($dari, $sampai),
            //         'attributes' => [
            //             'totalRows' => $gajiSupir->totalRows,
            //             'totalPages' => $gajiSupir->totalPages,
            //             'totalBorongan' => $gajiSupir->totalBorongan,
            //             'totalUangJalan' => $gajiSupir->totalUangJalan,
            //             'totalUangBBM' => $gajiSupir->totalUangBBM,
            //             'totalUangMakan' => $gajiSupir->totalUangMakan,
            //             'totalUangMakanBerjenjang' => $gajiSupir->totalUangMakanBerjenjang,
            //             'totalPotPinjaman' => $gajiSupir->totalPotPinjaman,
            //             'totalPotPinjSemua' => $gajiSupir->totalPotPinjSemua,
            //             'totalDeposito' => $gajiSupir->totalDeposito,
            //             'totalKomisi' => $gajiSupir->totalKomisi,
            //             'totalTol' => $gajiSupir->totalTol,
            //             'totalGajiSupir' => $gajiSupir->totalGajiSupir,
            //             'totalGajiKenek' => $gajiSupir->totalGajiKenek,
            //         ]
            //     ]);
            // } else {

            return response([
                'data' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
            // }
        }
    }
    public function getEdit(GetRicEditRequest $request, $gajiId)
    {
        $prosesgajisupir = new ProsesGajiSupirHeader();
        $aksi = $request->aksi;
        $statusjeniskendaraan = $request->statusjeniskendaraan;
        if ($aksi == 'edit') {
            $dari = date('Y-m-d', strtotime(request()->tgldari));
            $sampai = date('Y-m-d', strtotime(request()->tglsampai));
            $data = $prosesgajisupir->getAllEdit($gajiId, $dari, $sampai, $aksi, $statusjeniskendaraan);
        } else {
            $data = $prosesgajisupir->getEdit($gajiId, $aksi, $statusjeniskendaraan);
        }

        return response([
            'data' => $data,
            'attributes' => [
                'totalRows' => $prosesgajisupir->totalRows,
                'totalPages' => $prosesgajisupir->totalPages,
                'totalBorongan' => $prosesgajisupir->totalBorongan,
                'totalUangJalan' => $prosesgajisupir->totalUangJalan,
                'totalUangBBM' => $prosesgajisupir->totalUangBBM,
                'totalUangMakan' => $prosesgajisupir->totalUangMakan,
                'totalUangMakanBerjenjang' => $prosesgajisupir->totalUangMakanBerjenjang,
                'totalBiayaExtraHeader' => $prosesgajisupir->totalBiayaExtraHeader,
                'totalPotPinjaman' => $prosesgajisupir->totalPotPinjaman,
                'totalPotPinjSemua' => $prosesgajisupir->totalPotPinjSemua,
                'totalDeposito' => $prosesgajisupir->totalDeposito,
                'totalKomisi' => $prosesgajisupir->totalKomisi,
                'totalTol' => $prosesgajisupir->totalTol
            ]
        ]);
    }

    public function hitungNominal()
    {
        $ric = request()->rincianId;
    }
    public function noEdit()
    {
        $query = Error::from(DB::raw("error with (readuncommitted)"))
            ->select('keterangan')
            ->where('kodeerror', '=', 'EBSX')
            ->first();
        return response([
            'message' => "$query->keterangan",
        ]);
    }

    public function cekvalidasi($id)
    {
        $prosesgaji = ProsesGajiSupirHeader::find($id);
        $nobukti = $prosesgaji->nobukti ?? '';
        $status = $prosesgaji->statusapproval;

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $prosesgaji->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';
        $user = auth('api')->user()->name;
        $useredit = $prosesgaji->editing_by ?? '';

        $pengeluaran = $prosesgaji->pengeluaran_nobukti ?? '';
        $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $pengeluaran)
            ->first()->id ?? 0;
        $validasipengeluaran = app(PengeluaranHeaderController::class)->cekvalidasi($idpengeluaran);
        $msg = json_decode(json_encode($validasipengeluaran), true)['original']['error'] ?? false;
        // dd($msg);
        if ($msg == false) {
            goto lanjut;
        } else {
            return $validasipengeluaran;
        }

        lanjut:


        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));

        if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];
            return response($data);
        } else if ($tgltutup >= $prosesgaji->tglbukti) {
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

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($prosesgaji->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->updateEditingBy('prosesgajisupirheader', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $prosesgaji->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('prosesgajisupirheader', $id, $aksi);

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
        $prosesGaji = new ProsesGajiSupirHeader();
        $cekdata = $prosesGaji->cekvalidasiaksi($id);
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

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    public function getAllData($dari, $sampai)
    {
        $tglDari = date('Y-m-d', strtotime($dari));
        $tglSampai = date('Y-m-d', strtotime($sampai));

        $gajiSupir = new ProsesGajiSupirHeader();
        return response([
            'potsemua' => $gajiSupir->getPotSemua($tglDari, $tglSampai),
            'potpribadi' => $gajiSupir->getPotPribadi($tglDari, $tglSampai),
            'deposito' => $gajiSupir->getDeposito($tglDari, $tglSampai),
            'bbm' => $gajiSupir->getBBM($tglDari, $tglSampai),
            'pinjaman' => $gajiSupir->getPinjaman($tglDari, $tglSampai)
        ]);
    }

    private function storeJurnal($header, $detail)
    {

        try {
            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            foreach ($detail as $key => $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $jurnal = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($jurnal);

                $detailLog[] = $datadetails['detail']->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => 'ENTRY PROSES GAJI SUPIR',
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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('prosesgajisupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
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
        $prosesGajiSupirHeader = new ProsesGajiSupirHeader();
        return response([
            'data' => $prosesGajiSupirHeader->getExport($id),
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $prosesGajiSupirHeader = ProsesGajiSupirHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($prosesGajiSupirHeader->statuscetak != $statusSudahCetak->id) {
                $prosesGajiSupirHeader->statuscetak = $statusSudahCetak->id;
                // $prosesGajiSupirHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $prosesGajiSupirHeader->userbukacetak = auth('api')->user()->name;
                $prosesGajiSupirHeader->jumlahcetak = $prosesGajiSupirHeader->jumlahcetak + 1;
                if ($prosesGajiSupirHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($prosesGajiSupirHeader->getTable()),
                        'postingdari' => 'PRINT PROSES GAJI SUPIR HEADER',
                        'idtrans' => $prosesGajiSupirHeader->id,
                        'nobuktitrans' => $prosesGajiSupirHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $prosesGajiSupirHeader->toArray(),
                        'modifiedby' => $prosesGajiSupirHeader->modifiedby
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
}
