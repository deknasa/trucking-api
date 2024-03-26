<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPendapatanSupirHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\getPinjamanPendapatanSupirRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePendapatanSupirDetailRequest;
use App\Models\PendapatanSupirHeader;
use App\Http\Requests\StorePendapatanSupirHeaderRequest;
use App\Http\Requests\UpdatePendapatanSupirHeaderRequest;
use App\Models\Error;
use App\Models\Parameter;
use App\Models\PendapatanSupirDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendapatanSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     * PendapatanSupirHeader
     * @Detail PendapatanSupirDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $pendapatanSupir = new PendapatanSupirHeader();

        return response([
            'data' => $pendapatanSupir->get(),
            'attributes' => [
                'totalRows' => $pendapatanSupir->totalRows,
                'totalPages' => $pendapatanSupir->totalPages
            ]
        ]);
    }
    public function default()
    {
        $pendapatanSupir = new PendapatanSupirHeader();
        return response([
            'status' => true,
            'data' => $pendapatanSupir->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePendapatanSupirHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            $requestData = json_decode($request->detail, true);
            $data = [
                "tgldari" => $request->tgldari,
                "tglsampai" => $request->tglsampai,
                "tglbukti" => $request->tglbukti,
                "bank_id" => $request->bank_id,
                "supir_id" => $request->supir_id,
                "supir" => $request->supir,
                'id_detail' => $requestData['id_detail'],
                'nobukti_trip' => $requestData['nobukti_trip'],
                'nobukti_ric' => $requestData['nobukti_ric'],
                'dari_id' => $requestData['dari_id'],
                'sampai_id' => $requestData['sampai_id'],
                'nominal_detail' => $requestData['nominal_detail'],
                'gajikenek' => $requestData['gajikenek'],
                'supirtrip' => $requestData['supirtrip'],
                "nominal_depo" => $request->nominal_depo,
                "keterangan_depo" => $request->keterangan_depo,
                "supir_depo" => $request->supir_depo,
                "pinj_supir" => $request->pinj_supir,
                "pinj_nominal" => $request->pinj_nominal,
                "pinj_keterangan" => $request->pinj_keterangan,
                "pinj_nobukti" => $request->pinj_nobukti,
                "pinj_id" => $request->pinj_id,
                // "periode" => $request->periode,
            ];

            $pendapatanSupirHeader = (new PendapatanSupirHeader())->processStore($data);
            $pendapatanSupirHeader->position = $this->getPosition($pendapatanSupirHeader, $pendapatanSupirHeader->getTable())->position;
            if ($request->limit == 0) {
                $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / (10));
            } else {
                $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / ($request->limit ?? 10));
            }
            $pendapatanSupirHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pendapatanSupirHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $pendapatanSupirHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function show($id)
    {
        // dd('test');

        $data = (new PendapatanSupirHeader())->findUpdate($id);

        $supir_id = ($data->supir_id == '') ? 0 : $data->supir_id;

        $formatTab = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'TAB KOMISI')
            ->first()->text;
        if ($formatTab == 'FORMAT 1') {
            $detail = (new PendapatanSupirHeader())->getTrip($data->tgldari, $data->tglsampai, $supir_id, $id, 'show');
        } else {
            $detail = (new PendapatanSupirHeader())->getTrip2($data->tgldari, $data->tglsampai, $supir_id, $id, 'show');
        }
        return response([
            'data' => $data,
            'detail' => $detail,
            'pjp' => (new PendapatanSupirHeader())->getNobuktiPJP($data->nobukti),
            'dpo' => (new PendapatanSupirHeader())->getNobuktiDPO($data->nobukti),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePendapatanSupirHeaderRequest $request, PendapatanSupirHeader $pendapatanSupirHeader): JsonResponse
    {
        try {
            $requestData = json_decode($request->detail, true);
            $data = [
                "tgldari" => $request->tgldari,
                "tglsampai" => $request->tglsampai,
                "tglbukti" => $request->tglbukti,
                "bank_id" => $request->bank_id,
                "supir_id" => $request->supir_id,
                "supir" => $request->supir,
                'id_detail' => $requestData['id_detail'],
                'nobukti_trip' => $requestData['nobukti_trip'],
                'nobukti_ric' => $requestData['nobukti_ric'],
                'dari_id' => $requestData['dari_id'],
                'sampai_id' => $requestData['sampai_id'],
                'nominal_detail' => $requestData['nominal_detail'],
                'gajikenek' => $requestData['gajikenek'],
                'supirtrip' => $requestData['supirtrip'],
                "nominal_depo" => $request->nominal_depo,
                "keterangan_depo" => $request->keterangan_depo,
                "supir_depo" => $request->supir_depo,
                "pinj_supir" => $request->pinj_supir,
                "pinj_nominal" => $request->pinj_nominal,
                "pinj_keterangan" => $request->pinj_keterangan,
                "pinj_nobukti" => $request->pinj_nobukti,
                "pinj_id" => $request->pinj_id,
                // "periode" => $request->periode,

            ];



            $pendapatanSupirHeader = (new PendapatanSupirHeader())->processUpdate($pendapatanSupirHeader, $data);
            $pendapatanSupirHeader->position = $this->getPosition($pendapatanSupirHeader, $pendapatanSupirHeader->getTable())->position;
            if ($request->limit == 0) {
                $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / (10));
            } else {
                $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / ($request->limit ?? 10));
            }
            $pendapatanSupirHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pendapatanSupirHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $pendapatanSupirHeader
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
    public function destroy(DestroyPendapatanSupirHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pendapatanSupir = (new PendapatanSupirHeader())->processDestroy($id, 'DELETE PENDAPATAN SUPIR');
            $selected = $this->getPosition($pendapatanSupir, $pendapatanSupir->getTable(), true);
            $pendapatanSupir->position = $selected->position;
            $pendapatanSupir->id = $selected->id;
            if ($request->limit == 0) {
                $pendapatanSupir->page = ceil($pendapatanSupir->position / (10));
            } else {
                $pendapatanSupir->page = ceil($pendapatanSupir->position / ($request->limit ?? 10));
            }
            $pendapatanSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pendapatanSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pendapatanSupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }



    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->pendapatanId != '') {

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

                for ($i = 0; $i < count($request->pendapatanId); $i++) {
                    $pendapatanSupir = PendapatanSupirHeader::find($request->pendapatanId[$i]);
                    if ($pendapatanSupir->statusapproval == $statusApproval->id) {
                        $pendapatanSupir->statusapproval = $statusNonApproval->id;
                        $aksi = $statusNonApproval->text;
                    } else {
                        $pendapatanSupir->statusapproval = $statusApproval->id;
                        $aksi = $statusApproval->text;
                    }

                    $pendapatanSupir->tglapproval = date('Y-m-d', time());
                    $pendapatanSupir->userapproval = auth('api')->user()->name;

                    if ($pendapatanSupir->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($pendapatanSupir->getTable()),
                            'postingdari' => 'APPROVAL PENDAPATAN SUPIR',
                            'idtrans' => $pendapatanSupir->id,
                            'nobuktitrans' => $pendapatanSupir->nobukti,
                            'aksi' => $aksi,
                            'datajson' => $pendapatanSupir->toArray(),
                            'modifiedby' => auth('api')->user()->name
                        ];

                        $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    }
                }
                DB::commit();
                return response([
                    'message' => 'Berhasil'
                ]);
            } else {
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'WP')
                    ->first();
                return response([
                    'errors' => [
                        'penerimaan' => "PENDAPATAN SUPIR $query->keterangan"
                    ],
                    'message' => "PENDAPATAN SUPIR $query->keterangan",
                ], 422);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pendapatanSupirHeader = PendapatanSupirHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pendapatanSupirHeader->statuscetak != $statusSudahCetak->id) {
                $pendapatanSupirHeader->statuscetak = $statusSudahCetak->id;
                $pendapatanSupirHeader->tglbukacetak = date('Y-m-d H:i:s');
                $pendapatanSupirHeader->userbukacetak = auth('api')->user()->name;
                $pendapatanSupirHeader->jumlahcetak = $pendapatanSupirHeader->jumlahcetak + 1;
                if ($pendapatanSupirHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pendapatanSupirHeader->getTable()),
                        'postingdari' => 'PRINT PENDAPATAN SUPIR HEADER',
                        'idtrans' => $pendapatanSupirHeader->id,
                        'nobuktitrans' => $pendapatanSupirHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pendapatanSupirHeader->toArray(),
                        'modifiedby' => $pendapatanSupirHeader->modifiedby
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

    public function cekvalidasi($id)
    {
        $pendapatan = PendapatanSupirHeader::find($id);
        $nobukti = $pendapatan->nobukti ?? '';
        $status = $pendapatan->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pendapatan->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';


        $pengeluaran = $pendapatan->pengeluaran_nobukti ?? '';
        // dd($pengeluaran);
        $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $pengeluaran)
            ->first()->id ?? 0;
        // $aksi = request()->aksi ?? '';

        if ($idpengeluaran != 0) {
            $validasipengeluaran = app(PengeluaranHeaderController::class)->cekvalidasi($idpengeluaran);
            $msg = json_decode(json_encode($validasipengeluaran), true)['original']['error'] ?? false;
            if ($msg == false) {
                goto lanjut;
            } else {
                return $validasipengeluaran;
            }
        }

        lanjut:

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));

        if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' =>  $keterror,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];
            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' =>  $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $pendapatan->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' )';
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
                'statuspesan' => 'warning',
            ];

            return response($data);
        }else {

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
        $pendapatanSupir = new PendapatanSupirHeader();
        $nobukti = PendapatanSupirHeader::from(DB::raw("pendapatansupirheader"))->where('id', $id)->first();
        $cekdata = $pendapatanSupir->cekvalidasiaksi($nobukti->nobukti);
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $pendapatanSupirHeader = new PendapatanSupirHeader();
        return response([
            'data' => $pendapatanSupirHeader->getExport($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function exportsupir($id)
    {
        $pendapatanSupirHeader = new PendapatanSupirHeader();
        return response([
            'data' => $pendapatanSupirHeader->getExportsupir($id)
        ]);
    }


    public function gettrip(Request $request)
    {
        $tgldari  = date('Y-m-d', strtotime($request->tgldari));
        $tglsampai  = date('Y-m-d', strtotime($request->tglsampai));
        $supir_id  = $request->supir_id;
        $id  = $request->idPendapatan;
        $aksi = $request->aksi;
        // dd('test');
        $pendapatanSupir = new PendapatanSupirHeader();
        $formatTab = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'TAB KOMISI')
            ->first()->text;
        if ($formatTab == 'FORMAT 1') {
            $data = $pendapatanSupir->getTrip($tgldari, $tglsampai, $supir_id, $id, $aksi);
        } else {
            $data = $pendapatanSupir->getTrip2($tgldari, $tglsampai, $supir_id, $id, $aksi);
        }
        return response([
            'data' => $data,
            'attributes' => [
                'totalRows' => $pendapatanSupir->totalRows,
                'totalPages' => $pendapatanSupir->totalPages,
                'totalNominal' => $pendapatanSupir->totalNominal,
                'totalGajiKenek' => $pendapatanSupir->totalGajiKenek,
            ]
        ]);
    }

    public function getDataDeposito(Request $request)
    {
        $pendapatanSupir = new PendapatanSupirHeader();
        return response([
            'data' => $pendapatanSupir->getDataDeposito(),
        ]);
    }
    public function getPinjaman($supir_id)
    {
        $pendapatanSupir = new PendapatanSupirHeader();
        return response([
            'data' => $pendapatanSupir->getPinjaman($supir_id),
        ]);
    }
}
