<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPendapatanSupirHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePendapatanSupirDetailRequest;
use App\Models\PendapatanSupirHeader;
use App\Http\Requests\StorePendapatanSupirHeaderRequest;
use App\Http\Requests\UpdatePendapatanSupirHeaderRequest;
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
     * @Detail1 PendapatanSupirDetailController
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
     */
    public function store(StorePendapatanSupirHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            $data = [
                "tgldari" => $request->tgldari,
                "tglsampai" => $request->tglsampai,
                "tglbukti" => $request->tglbukti,
                "bank_id" => $request->bank_id,
                "supir_id" => $request->supir_id,                
                "supir" => $request->supir,                
                "id_detail" => $request->id_detail,
                "nobukti_trip" => $request->nobukti_trip,
                "nobukti_ric" => $request->nobukti_ric,
                "dari_id" => $request->dari_id,
                "sampai_id" => $request->sampai_id,
                "nominal_detail" => $request->nominal_detail,
            ];

            $pendapatanSupirHeader = (new PendapatanSupirHeader())->processStore($data);
            $pendapatanSupirHeader->position = $this->getPosition($pendapatanSupirHeader, $pendapatanSupirHeader->getTable())->position;
            if ($request->limit==0) {
                $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / (10));
            } else {
                $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / ($request->limit ?? 10));
            }
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

        $detail = (new PendapatanSupirHeader())->getTrip($data->tgldari, $data->tglsampai,$data->supir_id,$id, 'show');

        return response([
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdatePendapatanSupirHeaderRequest $request, PendapatanSupirHeader $pendapatanSupirHeader): JsonResponse
    {
        try {
            $data = [
                "tgldari" => $request->tgldari,
                "tglsampai" => $request->tglsampai,
                "tglbukti" => $request->tglbukti,
                "bank_id" => $request->bank_id,
                "supir_id" => $request->supir_id,                
                "supir" => $request->supir,                
                "id_detail" => $request->id_detail,
                "nobukti_trip" => $request->nobukti_trip,
                "nobukti_ric" => $request->nobukti_ric,
                "dari_id" => $request->dari_id,
                "sampai_id" => $request->sampai_id,
                "nominal_detail" => $request->nominal_detail,
            ];



            $pendapatanSupirHeader = (new PendapatanSupirHeader())->processUpdate($pendapatanSupirHeader, $data);
            $pendapatanSupirHeader->position = $this->getPosition($pendapatanSupirHeader, $pendapatanSupirHeader->getTable())->position;
            if ($request->limit==0) {
                $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / (10));
            } else {
                $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / ($request->limit ?? 10));
            }
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
    public function destroy(DestroyPendapatanSupirHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pendapatanSupir = (new PendapatanSupirHeader())->processDestroy($id, 'DELETE PENDAPATAN SUPIR');
            $selected = $this->getPosition($pendapatanSupir, $pendapatanSupir->getTable(), true);
            $pendapatanSupir->position = $selected->position;
            $pendapatanSupir->id = $selected->id;
            if ($request->limit==0) {
                $pendapatanSupir->page = ceil($pendapatanSupir->position / (10));
            } else {
                $pendapatanSupir->page = ceil($pendapatanSupir->position / ($request->limit ?? 10));
            }
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
        $status = $pendapatan->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pendapatan->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->first();
            $data = [
                'error' => true,
                'message' =>  'No Bukti ' . $pendapatan->nobukti . ' ' . $query->keterangan,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];
            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->first();
                $data = [
                    'error' => true,
                    'message' =>  'No Bukti ' . $pendapatan->nobukti . ' ' . $query->keterangan,
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
                'message' => $query->keterangan,
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
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $pendapatanSupirHeader = new PendapatanSupirHeader();
        return response([
            'data' => $pendapatanSupirHeader->getExport($id)
        ]);
    }

    public function gettrip(Request $request)
    {
        $tgldari  = date('Y-m-d', strtotime($request->tgldari));
        $tglsampai  = date('Y-m-d', strtotime($request->tglsampai));
        $supir_id  = $request->supir_id;
        $id  = $request->idPendapatan;
        // dd('test');
        $pendapatanSupir = new PendapatanSupirHeader();
        return response([
            'data' => $pendapatanSupir->getTrip($tgldari, $tglsampai,$supir_id,$id),
            'attributes' => [
                'totalRows' => $pendapatanSupir->totalRows,
                'totalPages' => $pendapatanSupir->totalPages,
                'totalNominal' => $pendapatanSupir->totalNominal,
            ]
        ]);
    }
}
