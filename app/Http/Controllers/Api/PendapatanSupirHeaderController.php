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

            $group = 'PENDAPATAN SUPIR BUKTI';
            $subgroup = 'PENDAPATAN SUPIR BUKTI';
            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $group)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'pendapatansupirheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $pendapatanSupir = new PendapatanSupirHeader();

            $statusApp = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $pendapatanSupir->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pendapatanSupir->bank_id  = $request->bank_id;
            $pendapatanSupir->tgldari  = date('Y-m-d', strtotime($request->tgldari));
            $pendapatanSupir->tglsampai  = date('Y-m-d', strtotime($request->tglsampai));
            $pendapatanSupir->statusapproval  = $statusApp->id;
            $pendapatanSupir->userapproval  = '';
            $pendapatanSupir->tglapproval  = '';
            $pendapatanSupir->periode  = date('Y-m-d', strtotime($request->periode));
            $pendapatanSupir->statusformat = $format->id;
            $pendapatanSupir->statuscetak = $statusCetak->id;
            $pendapatanSupir->modifiedby = auth('api')->user()->name;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $pendapatanSupir->nobukti = $nobukti;

            if ($pendapatanSupir->save()) {

                $logTrail = [
                    'namatabel' => strtoupper($pendapatanSupir->getTable()),
                    'postingdari' => 'ENTRY PENDAPATAN SUPIR HEADER',
                    'idtrans' => $pendapatanSupir->id,
                    'nobuktitrans' => $pendapatanSupir->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pendapatanSupir->toArray(),
                    'modifiedby' => $pendapatanSupir->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                $detaillog = [];
                for ($i = 0; $i < count($request->nominal); $i++) {
                    $datadetail = [
                        'pendapatansupir_id' => $pendapatanSupir->id,
                        'nobukti' => $pendapatanSupir->nobukti,
                        'supir_id' => $request->supir_id[$i],
                        'nominal' => $request->nominal[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => $pendapatanSupir->modifiedby,
                    ];

                    // STORE 
                    $data = new StorePendapatanSupirDetailRequest($datadetail);

                    $datadetails = app(PendapatanSupirDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $detaillog[] = $datadetails['detail']->toArray();
                }

                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'ENTRY PENDAPATAN SUPIR DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $pendapatanSupir->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $pendapatanSupir->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($pendapatanSupir, $pendapatanSupir->getTable());
            $pendapatanSupir->position = $selected->position;
            $pendapatanSupir->page = ceil($pendapatanSupir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pendapatanSupir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


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
        DB::beginTransaction();

        try {
            $pendapatanSupirHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pendapatanSupirHeader->bank_id = $request->bank_id;
            $pendapatanSupirHeader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $pendapatanSupirHeader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $pendapatanSupirHeader->periode = date('Y-m-d', strtotime($request->periode));


            if ($pendapatanSupirHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pendapatanSupirHeader->getTable()),
                    'postingdari' => 'EDIT PENDAPATAN SUPIR HEADER',
                    'idtrans' => $pendapatanSupirHeader->id,
                    'nobuktitrans' => $pendapatanSupirHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $pendapatanSupirHeader->toArray(),
                    'modifiedby' => $pendapatanSupirHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                PendapatanSupirDetail::where('pendapatansupir_id', $pendapatanSupirHeader->id)->delete();

                for ($i = 0; $i < count($request->nominal); $i++) {
                    $datadetail = [
                        'pendapatansupir_id' => $pendapatanSupirHeader->id,
                        'nobukti' => $pendapatanSupirHeader->nobukti,
                        'supir_id' => $request->supir_id[$i],
                        'nominal' => $request->nominal[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => $pendapatanSupirHeader->modifiedby,
                    ];

                    // STORE 
                    $data = new StorePendapatanSupirDetailRequest($datadetail);

                    $datadetails = app(PendapatanSupirDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $detaillog[] = $datadetails['detail']->toArray();
                }
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'EDIT PENDAPATAN SUPIR DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $pendapatanSupirHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $pendapatanSupirHeader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($pendapatanSupirHeader, $pendapatanSupirHeader->getTable());
            $pendapatanSupirHeader->position = $selected->position;
            $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pendapatanSupirHeader
            ], 201);
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
            $pendapatan = PendapatanSupirHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pendapatan->statuscetak != $statusSudahCetak->id) {
                $pendapatan->statuscetak = $statusSudahCetak->id;
                $pendapatan->tglbukacetak = date('Y-m-d H:i:s');
                $pendapatan->userbukacetak = auth('api')->user()->name;
                $pendapatan->jumlahcetak = $pendapatan->jumlahcetak + 1;

                if ($pendapatan->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pendapatan->getTable()),
                        'postingdari' => 'PRINT PENDAPATAN SUPIR HEADER',
                        'idtrans' => $pendapatan->id,
                        'nobuktitrans' => $pendapatan->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $pendapatan->toArray(),
                        'modifiedby' => auth('api')->user()->name,
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

    public function export($id)
    {
        $pendapatanSupirHeader = new PendapatanSupirHeader();
        return response([
            'data' => $pendapatanSupirHeader->getExport($id)
        ]);
    }
}
