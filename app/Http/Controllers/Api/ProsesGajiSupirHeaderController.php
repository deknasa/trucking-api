<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ProsesGajiSupirDetailController as ApiProsesGajiSupirDetailController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProsesGajiSupirDetailController;
use App\Http\Requests\DestroyJurnalUmumRequest;
use App\Http\Requests\DestroyPenerimaanHeaderRequest;
use App\Http\Requests\DestroyPengeluaranHeaderRequest;
use App\Http\Requests\DestroyPengembalianKasGantungHeaderRequest;
use App\Http\Requests\DestroyProsesGajiSupirHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\GetRicEditRequest;
use App\Http\Requests\GetRicRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengembalianKasGantungHeaderRequest;
use App\Http\Requests\StoreProsesGajiSupirDetailRequest;
use App\Models\ProsesGajiSupirHeader;
use App\Http\Requests\StoreProsesGajiSupirHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasGantungHeaderRequest;
use App\Http\Requests\UpdateProsesGajiSupirHeaderRequest;
use App\Models\Bank;
use App\Models\Error;
use App\Models\GajiSupirBBM;
use App\Models\GajiSupirDeposito;
use App\Models\GajiSupirHeader;
use App\Models\GajiSupirPelunasanPinjaman;
use App\Models\GajiSupirPinjaman;
use App\Models\GajisUpirUangJalan;
use App\Models\JurnalUmumHeader;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingDetail;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranTruckingDetail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\PengembalianKasGantungHeader;
use App\Models\ProsesGajiSupirDetail;
use App\Models\Supir;
use App\Models\SuratPengantar;
use App\Rules\GetRicEditRequst;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProsesGajiSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     * ProsesGajiSupirHeader
     * @Detail1 ProsesGajiSupirDetailController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $prosesGajiSupirHeader = new ProsesGajiSupirHeader();
        return response([
            'data' => $prosesGajiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $prosesGajiSupirHeader->totalRows,
                'totalPages' => $prosesGajiSupirHeader->totalPages
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
     */
    public function store(StoreProsesGajiSupirHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'tgldari' => date('Y-m-d', strtotime($request->tgldari)),
                'tglsampai' => date('Y-m-d', strtotime($request->tglsampai)),
                'bank_id' => $request->bank_id,
                'rincianId' => $request->rincianId,
                'nobuktiRIC' => $request->nobuktiRIC,
                'supir_id' => $request->supir_id,
                'totalborongan' => $request->totalborongan,
                'nomPS' => $request->nomPS,
                'nomPP' => $request->nomPP,
                'nomDeposito' => $request->nomDeposito,
                'nomBBM' => $request->nomBBM,
                'nomUangjalan' => $request->nomUangjalan
            ];

            $prosesGajiSupirHeader = (new ProsesGajiSupirHeader())->processStore($data);
            $prosesGajiSupirHeader->position = $this->getPosition($prosesGajiSupirHeader, $prosesGajiSupirHeader->getTable())->position;
            if ($request->limit==0) {
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
        $getTrip = $proses->getEdit($id, '');

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
     */
    public function update(UpdateProsesGajiSupirHeaderRequest $request, ProsesGajiSupirHeader $prosesgajisupirheader): JsonResponse
    {
        DB::beginTransaction();

        try {

            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'tgldari' => date('Y-m-d', strtotime($request->tgldari)),
                'tglsampai' => date('Y-m-d', strtotime($request->tglsampai)),
                'bank_id' => $request->bank_id,
                'rincianId' => $request->rincianId,
                'nobuktiRIC' => $request->nobuktiRIC,
                'supir_id' => $request->supir_id,
                'totalborongan' => $request->totalborongan,
                'nomPS' => $request->nomPS,
                'nomPP' => $request->nomPP,
                'nomDeposito' => $request->nomDeposito,
                'nomBBM' => $request->nomBBM,
                'nomUangjalan' => $request->nomUangjalan,
                'nobuktiUangjalan' => $request->nobuktiUangjalan
            ];

            $prosesGajiSupirHeader = (new ProsesGajiSupirHeader())->processUpdate($prosesgajisupirheader, $data);
            $prosesGajiSupirHeader->position = $this->getPosition($prosesGajiSupirHeader, $prosesGajiSupirHeader->getTable())->position;
            if ($request->limit==0) {
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
            if ($request->limit==0) {
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
        $gajiSupir = new ProsesGajiSupirHeader();
        $dari = date('Y-m-d', strtotime(request()->tgldari));
        $sampai = date('Y-m-d', strtotime(request()->tglsampai));

        $cekRic = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->whereRaw("tglbukti >= '$dari'")
            ->whereRaw("tglbukti <= '$sampai'")
            ->first();

        //CEK APAKAH ADA RIC
        if ($cekRic) {
            $nobukti = $cekRic->nobukti;
            $cekEBS = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))
                ->whereRaw("gajisupir_nobukti = '$nobukti'")->first();

            return response([
                'errors' => false,
                'data' => $gajiSupir->getRic($dari, $sampai),
                'attributes' => [
                    'totalRows' => $gajiSupir->totalRows,
                    'totalPages' => $gajiSupir->totalPages,
                    'totalBorongan' => $gajiSupir->totalBorongan,
                    'totalUangJalan' => $gajiSupir->totalUangJalan,
                    'totalUangBBM' => $gajiSupir->totalUangBBM,
                    'totalUangMakan' => $gajiSupir->totalUangMakan,
                    'totalUangMakanBerjenjang' => $gajiSupir->totalUangMakanBerjenjang,
                    'totalPotPinjaman' => $gajiSupir->totalPotPinjaman,
                    'totalPotPinjSemua' => $gajiSupir->totalPotPinjSemua,
                    'totalDeposito' => $gajiSupir->totalDeposito,
                    'totalKomisi' => $gajiSupir->totalKomisi,
                    'totalTol' => $gajiSupir->totalTol
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
    public function getEdit(GetRicEditRequest $request, $gajiId)
    {
        $prosesgajisupir = new ProsesGajiSupirHeader();
        $aksi = $request->aksi;
        if ($aksi == 'edit') {
            $dari = date('Y-m-d', strtotime(request()->tgldari));
            $sampai = date('Y-m-d', strtotime(request()->tglsampai));
            $data = $prosesgajisupir->getAllEdit($gajiId, $dari, $sampai, $aksi);
        } else {
            $data = $prosesgajisupir->getEdit($gajiId, $aksi);
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
        $status = $prosesgaji->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $prosesgaji->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
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
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'kodeerror' => 'SDC',
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
                'message' => $query->keterangan,
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
     */
    public function report()
    {
    }


    /**
     * @ClassName 
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
                $prosesGajiSupirHeader->tglbukacetak = date('Y-m-d H:i:s');
                $prosesGajiSupirHeader->userbukacetak = auth('api')->user()->name;
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
