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

    /**
     * @ClassName 
     */
    public function store(StorePendapatanSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {


            $data = [
                "tgldari" => $request->tgldari,
                "tglsampai" => $request->tglsampai,
                "periode" => $request->periode,
                "tglbukti" => $request->tglbukti,
                "nominal" => $request->nominal,
                "supir_id" => $request->supir_id,
                "nominal" => $request->nominal,
                "keterangan_detail" => $request->keterangan_detail,
                "postingdari" => $request->postingdari,
                "bank_id" => $request->bank_id,
            ];

            $pendapatanSupirHeader = (new PendapatanSupirHeader())->processStore($data);
            $pendapatanSupirHeader->position = $this->getPosition($pendapatanSupirHeader, $pendapatanSupirHeader->getTable())->position;
            $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / ($request->limit ?? 10));

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
        $data = PendapatanSupirHeader::findUpdate($id);
        $detail = PendapatanSupirDetail::findUpdate($id);

        return response([
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdatePendapatanSupirHeaderRequest $request, PendapatanSupirHeader $pendapatanSupirHeader)
    {
        try {
            $data = [
                "tgldari" => $request->tgldari,
                "tglsampai" => $request->tglsampai,
                "periode" => $request->periode,
                "tglbukti" => $request->tglbukti,
                "nominal" => $request->nominal,
                "supir_id" => $request->supir_id,
                "nominal" => $request->nominal,
                "keterangan_detail" => $request->keterangan_detail,
                "postingdari" => $request->postingdari,
                "bank_id" => $request->bank_id,
            ];


            $pendapatanSupirHeader = (new PendapatanSupirHeader())->processUpdate($pendapatanSupirHeader, $data);
            $pendapatanSupirHeader->position = $this->getPosition($pendapatanSupirHeader, $pendapatanSupirHeader->getTable())->position;
            $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / ($request->limit ?? 10));

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
    public function destroy(DestroyPendapatanSupirHeaderRequest $request, $id)
    {
        DB::beginTransaction();


        $getDetail = PendapatanSupirDetail::where('pendapatansupir_id', $id)->get();

        $pendapatanSupir = new PendapatanSupirHeader();
        $pendapatanSupir = $pendapatanSupir->lockAndDestroy($id);

        if ($pendapatanSupir) {
            $logTrail = [
                'namatabel' => strtoupper($pendapatanSupir->getTable()),
                'postingdari' => 'DELETE PENDAPATAN SUPIR HEADER',
                'idtrans' => $pendapatanSupir->id,
                'nobuktitrans' => $pendapatanSupir->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pendapatanSupir->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PENDAPATAN SUPIR DETAIL
            $logTrailPendapatanDetail = [
                'namatabel' => 'PENDAPATANSUPIRDETAIL',
                'postingdari' => 'DELETE PENDAPATAN SUPIR DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $pendapatanSupir->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPendapatanDetail = new StoreLogTrailRequest($logTrailPendapatanDetail);
            app(LogTrailController::class)->store($validatedLogTrailPendapatanDetail);

            DB::commit();

            $selected = $this->getPosition($pendapatanSupir, $pendapatanSupir->getTable(), true);
            $pendapatanSupir->position = $selected->position;
            $pendapatanSupir->id = $selected->id;
            $pendapatanSupir->page = ceil($pendapatanSupir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pendapatanSupir
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
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
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah approve',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah cetak',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else {

            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '0',
                'kodenobukti' => '1'
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
        $id  = $request->id;
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
