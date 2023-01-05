<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\NotaDebetDetail;
use Illuminate\Http\Request;

use App\Models\NotaDebetHeader;
use App\Models\PelunasanPiutangHeader;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreNotaDebetDetailRequest;
use App\Http\Requests\StoreNotaDebetHeaderRequest;
use App\Http\Requests\UpdateNotaDebetHeaderRequest;
use App\Models\Parameter;

class NotaDebetHeaderController extends Controller
{

    /**
     * @ClassName
     */
    public function index()
    {
        $notaDebetHeader = new NotaDebetHeader();
        return response([
            'data' => $notaDebetHeader->get(),
            'attributes' => [
                'totalRows' => $notaDebetHeader->totalRows,
                'totalPages' => $notaDebetHeader->totalPages
            ]
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StoreNotaDebetHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $group = 'NOTA DEBET BUKTI';
            $subgroup = 'NOTA DEBET BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();
            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'notadebetheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $notaDebetHeader = new NotaDebetHeader();

            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            

            $notaDebetHeader->pelunasanpiutang_nobukti = $request->pelunasanpiutang_nobukti;
            $notaDebetHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $notaDebetHeader->keterangan = $request->keterangan;
            $notaDebetHeader->statusapproval = $statusApproval->id;
            $notaDebetHeader->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
            $notaDebetHeader->statusformat = $format->id;
            $notaDebetHeader->statuscetak = $statusCetak->id;
            $notaDebetHeader->modifiedby = auth('api')->user()->name;
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $notaDebetHeader->nobukti = $nobukti;


            if ($notaDebetHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($notaDebetHeader->getTable()),
                    'postingdari' => 'ENTRY NOTA DEBET HEADER',
                    'idtrans' => $notaDebetHeader->id,
                    'nobuktitrans' => $notaDebetHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $notaDebetHeader->toArray(),
                    'modifiedby' => $notaDebetHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /* Store detail */
                if ($request->pelunasanpiutangdetail_id) {
                    $notaDebetDetail = NotaDebetDetail::where('notadebet_id', $notaDebetHeader->id)->lockForUpdate()->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->pelunasanpiutangdetail_id); $i++) {
                        $datadetail = [
                            "notadebet_id" => $notaDebetHeader->id,
                            "nobukti" =>  $notaDebetHeader->nobukti,
                            "tglterima" => $request->deatail_tglcair_pelunasan[$i],
                            "invoice_nobukti" => "",
                            "nominal" => $request->deatail_nominal_pelunasan[$i],
                            "nominalbayar" => $request->deatail_nominalbayar_pelunasan[$i],
                            "lebihbayar" => $request->deatail_lebihbayar_pelunasan[$i],
                            "keterangandetail" => $request->keterangandetail[$i],
                            "coalebihbayar" => $request->deatail_coalebihbayar_pelunasan[$i],
                            "modifiedby" => $notaDebetHeader->modifiedby = auth('api')->user()->name
                        ];

                        $data = new StoreNotaDebetDetailRequest($datadetail);
                        $notaDebetDetail = app(NotaDebetDetailController::class)->store($data);

                        if ($notaDebetDetail['error']) {
                            return response($notaDebetDetail, 422);
                        } else {
                            $iddetail = $notaDebetDetail['id'];
                            $tabeldetail = $notaDebetDetail['tabel'];
                        }

                        $detaillog[] = $notaDebetDetail['data']->toArray();
                    }

                    $datalogtrail = [
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'ENTRY NOTA DEBET DETAIL',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $notaDebetHeader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($datalogtrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    DB::commit();
                }
            }

            /* Set position and page */
            $selected = $this->getPosition($notaDebetHeader, $notaDebetHeader->getTable());
            $notaDebetHeader->position = $selected->position;
            $notaDebetHeader->page = ceil($notaDebetHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $notaDebetHeader->page = ceil($notaDebetHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $notaDebetHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
        return response([
            'message' => 'Berhasil gagal disimpan',
            'data' => $notaDebetHeader
        ], 422);
    }

    public function show(NotaDebetHeader $notaDebetHeader, $id)
    {
        $data = $notaDebetHeader->findAll($id);
        // $detail = NotaDebetHeaderDetail::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            // 'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateNotaDebetHeaderRequest $request, NotaDebetHeader $notadebetheader)
    {
        try {

            $notadebetheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $notadebetheader->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
            $notadebetheader->pelunasanpiutang_nobukti = $request->pelunasanpiutang_nobukti;
            $notadebetheader->keterangan = $request->keterangan;
            $notadebetheader->postingdari = "NOTA DEBET HEADER";
            $notadebetheader->modifiedby = auth('api')->user()->name;

            if ($notadebetheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($notadebetheader->getTable()),
                    'postingdari' => 'EDIT NOTA DEBET HEADER',
                    'idtrans' => $notadebetheader->id,
                    'nobuktitrans' => $notadebetheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $notadebetheader->toArray(),
                    'modifiedby' => $notadebetheader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /* Store detail */
                if ($request->pelunasanpiutangdetail_id) {
                    $notaDebetDetail = NotaDebetDetail::where('notadebet_id', $notadebetheader->id)->lockForUpdate()->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->pelunasanpiutangdetail_id); $i++) {
                        $datadetail = [
                            "notadebet_id" => $notadebetheader->id,
                            "nobukti" =>  $notadebetheader->nobukti,
                            "tglterima" => $request->deatail_tglcair_pelunasan[$i],
                            "invoice_nobukti" => "",
                            "nominal" => $request->deatail_nominal_pelunasan[$i],
                            "nominalbayar" => $request->deatail_nominalbayar_pelunasan[$i],
                            "lebihbayar" => $request->deatail_lebihbayar_pelunasan[$i],
                            "keterangandetail" => $request->keterangandetail[$i],
                            "coalebihbayar" => $request->deatail_coalebihbayar_pelunasan[$i],
                            "modifiedby" => $notadebetheader->modifiedby = auth('api')->user()->name
                        ];


                        $data = new StoreNotaDebetDetailRequest($datadetail);
                        $notaDebetDetail = app(NotaDebetDetailController::class)->store($data);
                        // $detaillog []=$datadetail;
                        if ($notaDebetDetail['error']) {
                            return response($notaDebetDetail, 422);
                        } else {
                            $iddetail = $notaDebetDetail['id'];
                            $tabeldetail = $notaDebetDetail['tabel'];
                            $detaillog[] = $notaDebetDetail['data']->toArray();
                        }
                    }
                    $datalogtrail = [
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'EDIT NOTA DEBET DETAIL',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $notadebetheader->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($datalogtrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    DB::commit();
                }
            }

            /* Set position and page */
            $selected = $this->getPosition($notadebetheader, $notadebetheader->getTable());
            $notadebetheader->position = $selected->position;
            $notadebetheader->page = ceil($notadebetheader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $notadebetheader->page = ceil($notadebetheader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $notadebetheader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
        return response([
            'message' => 'Berhasil gagal disimpan',
            'data' => $notadebetheader
        ], 422);
    }

    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $getDetail = NotaDebetDetail::lockForUpdate()->where('notadebet_id', $id)->get();
        $notaDebetHeader = new NotaDebetHeader();
        $notaDebetHeader = $notaDebetHeader->lockAndDestroy($id);

        if ($notaDebetHeader) {
            $logTrail = [
                'namatabel' => strtoupper($notaDebetHeader->getTable()),
                'postingdari' => 'DELETE NOTA DEBET ',
                'idtrans' => $notaDebetHeader->id,
                'nobuktitrans' => $notaDebetHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $notaDebetHeader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE NOTA DEBET DETAIL
            $logTrailNotaDebetDetail = [
                'namatabel' => 'NOTADEBETDETAIL',
                'postingdari' => 'DELETE NOTA DEBET DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $notaDebetHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailNotaDebetDetail = new StoreLogTrailRequest($logTrailNotaDebetDetail);
            app(LogTrailController::class)->store($validatedLogTrailNotaDebetDetail);
            DB::commit();

            $selected = $this->getPosition($notaDebetHeader, $notaDebetHeader->getTable(), true);
            $notaDebetHeader->position = $selected->position;
            $notaDebetHeader->id = $selected->id;
            $notaDebetHeader->page = ceil($notaDebetHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $notaDebetHeader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function getPelunasan($id)
    {
        $pelunasanPiutang = new PelunasanPiutangHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $pelunasanPiutang->getPelunasanNotaDebet($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $pelunasanPiutang->totalRows,
                'totalPages' => $pelunasanPiutang->totalPages
            ]
        ]);
    }
    public function getNotaDebet($id)
    {
        $notaDebet = new NotaDebetHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $notaDebet->getNotaDebet($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $notaDebet->totalRows,
                'totalPages' => $notaDebet->totalPages
            ]
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('notadebetheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    public function cekvalidasi($id)
    {
        $notaDebet = NotaDebetHeader::find($id);
        $status = $notaDebet->statusapproval;
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $notaDebet->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

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
    
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $notadebet = NotaDebetHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($notadebet->statuscetak != $statusSudahCetak->id) {
                $notadebet->statuscetak = $statusSudahCetak->id;
                $notadebet->tglbukacetak = date('Y-m-d H:i:s');
                $notadebet->userbukacetak = auth('api')->user()->name;
                $notadebet->jumlahcetak = $notadebet->jumlahcetak+1;

                if ($notadebet->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($notadebet->getTable()),
                        'postingdari' => 'PRINT NOTA KREDIT HEADER',
                        'idtrans' => $notadebet->id,
                        'nobuktitrans' => $notadebet->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $notadebet->toArray(),
                        'modifiedby' => auth('api')->user()->name
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
