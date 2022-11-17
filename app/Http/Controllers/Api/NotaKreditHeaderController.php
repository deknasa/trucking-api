<?php

namespace App\Http\Controllers\Api;


use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\NotaKreditHeader;
use App\Models\NotaKreditDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PelunasanPiutangHeader;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreNotaKreditDetailRequest;
use App\Http\Requests\StoreNotaKreditHeaderRequest;
use App\Http\Requests\UpdateNotaKreditHeaderRequest;

class NotaKreditHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $notaKreditHeader = new NotaKreditHeader();
        return response([
            'data' => $notaKreditHeader->get(),
            'attributes' => [
                'totalRows' => $notaKreditHeader->totalRows,
                'totalPages' => $notaKreditHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreNotaKreditHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $group = 'NOTA KREDIT BUKTI';
            $subgroup = 'NOTA KREDIT BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();
            $content = new Request();
            $content['group'] = $group ;
            $content['subgroup'] = $subgroup ;
            $content['table'] = 'notakreditheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $notaKreditHeader = new NotaKreditHeader();
            
            $notaKreditHeader->pelunasanpiutang_nobukti = $request->pelunasanpiutang_nobukti;
            $notaKreditHeader->tglbukti = date('Y-m-d',strtotime($request->tglbukti));
            $notaKreditHeader->keterangan = $request->keterangan;
            $notaKreditHeader->statusapproval = $request->statusapproval;
            $notaKreditHeader->tgllunas = date('Y-m-d',strtotime($request->tgllunas));
            $notaKreditHeader->statusformat = $request->statusformat;
            $notaKreditHeader->statusformat = $format->id;
            $notaKreditHeader->modifiedby = auth('api')->user()->name;
            TOP:
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $notaKreditHeader->nobukti = $nobukti;


            if ($notaKreditHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($notaKreditHeader->getTable()),
                    'postingdari' => 'ENTRY NOTA KREDIT HEADER',
                    'idtrans' => $notaKreditHeader->id,
                    'nobuktitrans' => $notaKreditHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $notaKreditHeader->toArray(),
                    'modifiedby' => $notaKreditHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                /* Store detail */
                if ($request->pelunasanpiutangdetail_id) {
                    $notaKreditDetail = NotaKreditDetail::where('notakredit_id',$notaKreditHeader->id)->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->pelunasanpiutangdetail_id); $i++) {
                        $datadetail = [
                            "notakredit_id" => $notaKreditHeader->id,
                            "nobukti" =>  $notaKreditHeader->nobukti,
                            "tglterima" => $request->deatail_tglcair_pelunasan[$i],
                            "invoice_nobukti" => "",
                            "nominal" => $request->deatail_nominal_pelunasan[$i],
                            "nominalbayar" => $request->deatail_nominalbayar_pelunasan[$i],
                            "penyesuaian" => $request->deatail_penyesuaian_pelunasan[$i],
                            "keterangandetail" => $request->keterangandetail[$i],
                            "coaadjust" => $request->deatail_coapenyesuaian_pelunasan[$i],
                            "modifiedby" => $notaKreditHeader->modifiedby = auth('api')->user()->name
                        ];
                        
                        $detaillog []=$datadetail;
                        $data = new StoreNotaKreditDetailRequest($datadetail);
                        $notaKreditDetail = app(NotaKreditDetailController::class)->store($data);
    
                        if ($notaKreditDetail['error']) {
                            return response($notaKreditDetail, 422);
                        } else {
                            $iddetail = $notaKreditDetail['id'];
                            $tabeldetail = $notaKreditDetail['tabel'];
                        }
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY NOTA KREDIT DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $notaKreditHeader->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    
                    DB::commit();
                }
            }

            /* Set position and page */
            $selected = $this->getPosition($notaKreditHeader, $notaKreditHeader->getTable());
            $notaKreditHeader->position = $selected->position;
            $notaKreditHeader->page = ceil($notaKreditHeader->position / ($request->limit ?? 10));
            
            if (isset($request->limit)) {
                $notaKreditHeader->page = ceil($notaKreditHeader->position / $request->limit);
            }
            
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $notaKreditHeader
            ], 201);
                    

        }catch (\Throwable $th){
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
        return response([
            'message' => 'Berhasil gagal disimpan',
            'data' => $notaKreditHeader
        ], 422);
    }

    public function show(NotaKreditHeader $notaKreditHeader,$id)
    {
        $data = $notaKreditHeader->find($id);
        // $detail = NotaKreditHeaderDetail::findAll($id);
        
        return response([
            'status' => true,
            'data' => $data,
            // 'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateNotaKreditHeaderRequest $request, NotaKreditHeader $notaKreditHeader,$id)
    {
        try {
            
            $notaKreditHeader = NotaKreditHeader::findOrFail($id);
            $notaKreditHeader->tglbukti = date('Y-m-d',strtotime($request->tglbukti));
            $notaKreditHeader->tglapproval = date('Y-m-d',strtotime($request->tglapproval));
            $notaKreditHeader->statusapproval = $request->statusapproval;
            $notaKreditHeader->tgllunas = date('Y-m-d',strtotime($request->tgllunas));
            $notaKreditHeader->pelunasanpiutang_nobukti = $request->pelunasanpiutang_nobukti;
            $notaKreditHeader->keterangan = $request->keterangan;
            $notaKreditHeader->postingdari = "NOTA KREDIT HEADER";
            $notaKreditHeader->userapproval = auth('api')->user()->name;
            $notaKreditHeader->modifiedby = auth('api')->user()->name;
           
            if ($notaKreditHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($notaKreditHeader->getTable()),
                    'postingdari' => 'EDIT NOTA KREDIT HEADER',
                    'idtrans' => $notaKreditHeader->id,
                    'nobuktitrans' => $notaKreditHeader->nobukti,
                    'aksi' => 'EDII',
                    'datajson' => $notaKreditHeader->toArray(),
                    'modifiedby' => $notaKreditHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                /* Store detail */
                if ($request->pelunasanpiutangdetail_id) {
                    $notaKreditDetail = NotaKreditDetail::where('notakredit_id',$notaKreditHeader->id)->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->pelunasanpiutangdetail_id); $i++) {
                        $datadetail = [
                            "notakredit_id" => $notaKreditHeader->id,
                            "nobukti" =>  $notaKreditHeader->nobukti,
                            "tglterima" => $request->deatail_tglcair_pelunasan[$i],
                            "invoice_nobukti" => "",
                            "nominal" => $request->deatail_nominal_pelunasan[$i],
                            "nominalbayar" => $request->deatail_nominalbayar_pelunasan[$i],
                            "penyesuaian" => $request->deatail_penyesuaian_pelunasan[$i],
                            "keterangandetail" => $request->keterangandetail[$i],
                            "coaadjust" => $request->deatail_coapenyesuaian_pelunasan[$i],
                            "modifiedby" => $notaKreditHeader->modifiedby = auth('api')->user()->name
                        ];
                        
                        
                        $data = new StoreNotaKreditDetailRequest($datadetail);
                        $notaKreditDetail = app(NotaKreditDetailController::class)->store($data);
    
                        if ($notaKreditDetail['error']) {
                            return response($notaKreditDetail, 422);
                        } else {
                            $iddetail = $notaKreditDetail['id'];
                            $tabeldetail = $notaKreditDetail['tabel'];
                            $detaillog []=$notaKreditDetail['data'];
                        }
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY NOTA KREDIT DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $notaKreditHeader->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' =>$detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    
                    DB::commit();
                }
            }

            /* Set position and page */
            $selected = $this->getPosition($notaKreditHeader, $notaKreditHeader->getTable());
            $notaKreditHeader->position = $selected->position;
            $notaKreditHeader->page = ceil($notaKreditHeader->position / ($request->limit ?? 10));
            
            if (isset($request->limit)) {
                $notaKreditHeader->page = ceil($notaKreditHeader->position / $request->limit);
            }
            
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $notaKreditHeader
            ], 201);
                    

        }catch (\Throwable $th){
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
        return response([
            'message' => 'Berhasil gagal disimpan',
            'data' => $notaKreditHeader
        ], 422);
    }
    /**
     * @ClassName 
     */
    public function destroy(NotaKreditHeader $notaKreditHeader,$id)
    {
        DB::beginTransaction();

        $notaKreditHeader = NotaKreditHeader::where('id',$id)->first();
        $delete = $notaKreditHeader->delete();

        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($notaKreditHeader->getTable()),
                'postingdari' => 'DELETE NOTA KREDIT ',
                'idtrans' => $notaKreditHeader->id,
                'nobuktitrans' => $notaKreditHeader->id,
                'aksi' => 'DELETE',
                'datajson' => $notaKreditHeader->toArray(),
                'modifiedby' => $notaKreditHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($notaKreditHeader, $notaKreditHeader->getTable(), true);
            $notaKreditHeader->position = $selected->position;
            $notaKreditHeader->id = $selected->id;
            $notaKreditHeader->page = ceil($notaKreditHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $notaKreditHeader
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
            'data' => $pelunasanPiutang->getPelunasanNotaKredit($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $pelunasanPiutang->totalRows,
                'totalPages' => $pelunasanPiutang->totalPages
            ]
        ]);
    }
    public function getNotaKredit($id)
    {
        $notaKredit = new NotaKreditHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $notaKredit->getNotaKredit($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $notaKredit->totalRows,
                'totalPages' => $notaKredit->totalPages
            ]
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('notakreditheader')->getColumns();

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
    public function approval(NotaKreditHeader $notaKreditHeader,$id)
    {
        DB::beginTransaction();
        $notaKreditHeader = NotaKreditHeader::findOrFail($id);
        try {
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($notaKreditHeader->statusapproval == $statusApproval->id) {
                $notaKreditHeader->statusapproval = $statusNonApproval->id;
            } else {
                $notaKreditHeader->statusapproval = $statusApproval->id;
            }

            $notaKreditHeader->tglapproval = date('Y-m-d', time());
            $notaKreditHeader->userapproval = auth('api')->user()->name;

            if ($notaKreditHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($notaKreditHeader->getTable()),
                    'postingdari' => 'UN/APPROVE NOKTA KREDIT',
                    'idtrans' => $notaKreditHeader->id,
                    'nobuktitrans' => $notaKreditHeader->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $notaKreditHeader->toArray(),
                    'modifiedby' => $notaKreditHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $notaKreditHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


}
