<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengembalianKasGantungDetailRequest;
use App\Models\KasGantungHeader;
use App\Models\KasGantungDetail;
use App\Models\PengembalianKasGantungHeader;
use App\Models\PengembalianKasGantungDetail;
use App\Http\Requests\StorePengembalianKasGantungHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasGantungHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengembalianKasGantungHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $pengembalianKasGantungHeader = new PengembalianKasGantungHeader();
        return response([
            'data' => $pengembalianKasGantungHeader->get(),
            'attributes' => [
                'totalRows' => $pengembalianKasGantungHeader->totalRows,
                'totalPages' => $pengembalianKasGantungHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StorePengembalianKasGantungHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'PENGEMBALIAN KAS GANTUNG BUKTI';
            $subgroup = 'PENGEMBALIAN KAS GANTUNG BUKTI';


            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group ;
            $content['subgroup'] = $subgroup ;
            $content['table'] = 'PengembalianKasGantungHeader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasGantungHeader = new PengembalianKasGantungHeader();
            
            $pengembalianKasGantungHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasGantungHeader->pelanggan_id = $request->pelanggan_id;
            $pengembalianKasGantungHeader->keterangan = $request->keterangan;
            $pengembalianKasGantungHeader->bank_id = $request->bank_id;
            $pengembalianKasGantungHeader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $pengembalianKasGantungHeader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $pengembalianKasGantungHeader->penerimaan_nobukti = $request->penerimaan_nobukti;
            $pengembalianKasGantungHeader->coakasmasuk = $request->coa;
            $pengembalianKasGantungHeader->postingdari = $request->postingdari ?? '';
            $pengembalianKasGantungHeader->tglkasmasuk = date('Y-m-d', strtotime($request->tglkasmasuk));
            $pengembalianKasGantungHeader->statusformat = $format->id;
            $pengembalianKasGantungHeader->modifiedby = auth('api')->user()->name;
            
            
            TOP:
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $pengembalianKasGantungHeader->nobukti = $nobukti;

            
            if ($pengembalianKasGantungHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengembalianKasGantungHeader->getTable()),
                    'postingdari' => 'ENTRY PENGEMBALIAN KAS GANTUNG HEADER',
                    'idtrans' => $pengembalianKasGantungHeader->id,
                    'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengembalianKasGantungHeader->toArray(),
                    'modifiedby' => $pengembalianKasGantungHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                /* Store detail */
                if ($request->kasgantungdetail_id) {
                    $pengeluaranStokDetail = PengembalianKasGantungDetail::where('pengembaliankasgantung_id',$pengembalianKasGantungHeader->id)->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->kasgantungdetail_id); $i++) {
                        $idKasgantungDetail = $request->kasgantungdetail_id[$i];
                        $kasgantung = KasGantungDetail::where('id',$idKasgantungDetail)->first();
        
                        $datadetail = [
                            "pengembaliankasgantung_id" => $pengembalianKasGantungHeader->id,
                            "nobukti" => $pengembalianKasGantungHeader->nobukti,
                            "nominal" => $kasgantung->nominal,
                            "coadetail" => $request->coadetail[$i],
                            "keterangandetail" => $request->keterangandetail[$i],
                            "kasgantung_nobukti" => $kasgantung->nobukti,
                        ];
                        $detaillog []=$datadetail;
                        $data = new StorePengembalianKasGantungDetailRequest($datadetail);
                        $pengembalianKasGantungDetail = app(PengembalianKasGantungDetailController::class)->store($data);
    
                        if ($pengembalianKasGantungDetail['error']) {
                            return response($pengembalianKasGantungDetail, 422);
                        } else {
                            $iddetail = $pengembalianKasGantungDetail['id'];
                            $tabeldetail = $pengembalianKasGantungDetail['tabel'];
                        }

                        $datalogtrail = [
                            'namatabel' => $tabeldetail,
                            'postingdari' => 'EDIT PENGEMBALIAN KAS GANTUNG HEADER',
                            'idtrans' =>  $iddetail,
                            'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                            'aksi' => 'EDIT',
                            'datajson' => $detaillog,
                            'modifiedby' => auth('api')->user()->name,
                        ];
                        $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                        
                        DB::commit();
                    }
                    /* Set position and page */
                    $selected = $this->getPosition($pengembalianKasGantungHeader, $pengembalianKasGantungHeader->getTable());
                    $pengembalianKasGantungHeader->position = $selected->position;
                    $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / ($request->limit ?? 10));
                    
                    if (isset($request->limit)) {
                        $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / $request->limit);
                    }
                    
                    return response([
                        'message' => 'Berhasil disimpan',
                        'data' => $pengembalianKasGantungHeader
                    ], 201);
                } 
            }
                
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }   
    }
    /**
     * @ClassName 
     */
    public function show(PengembalianKasGantungHeader $pengembalianKasGantungHeader,$id)
    {
        return response([
            'status' => true,
            'data' => $pengembalianKasGantungHeader->find($id),
            'detail' => PengembalianKasGantungDetail::getAll($id),
        ]);
    }
    /**
     * @ClassName 
     */
    public function update(UpdatePengembalianKasGantungHeaderRequest $request, PengembalianKasGantungHeader $pengembalianKasGantungHeader,$id)
    {
        try {
            /* Store header */
            $pengembalianKasGantungHeader = PengembalianKasGantungHeader::findOrFail($id);
            DB::beginTransaction();
        
            $pengembalianKasGantungHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasGantungHeader->pelanggan_id = $request->pelanggan_id;
            $pengembalianKasGantungHeader->keterangan = $request->keterangan;
            $pengembalianKasGantungHeader->bank_id = $request->bank_id;
            $pengembalianKasGantungHeader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $pengembalianKasGantungHeader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $pengembalianKasGantungHeader->penerimaan_nobukti = $request->penerimaan_nobukti;
            $pengembalianKasGantungHeader->coakasmasuk = $request->coa;
            $pengembalianKasGantungHeader->postingdari = $request->postingdari ?? '';
            
            $pengembalianKasGantungHeader->modifiedby = auth('api')->user()->name;
            $pengembalianKasGantungHeader->tglkasmasuk = date('Y-m-d', strtotime($request->tglkasmasuk));

            if ($pengembalianKasGantungHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengembalianKasGantungHeader->getTable()),
                    'postingdari' => 'ENTRY PENGEMBALIAN KAS GANTUNG HEADER',
                    'idtrans' => $pengembalianKasGantungHeader->id,
                    'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengembalianKasGantungHeader->toArray(),
                    'modifiedby' => $pengembalianKasGantungHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                /* Store detail */
                if ($request->kasgantungdetail_id) {
                    $pengeluaranStokDetail = PengembalianKasGantungDetail::where('pengembaliankasgantung_id',$pengembalianKasGantungHeader->id)->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->kasgantungdetail_id); $i++) {
                        $idKasgantungDetail = $request->kasgantungdetail_id[$i];
                        $kasgantung = KasGantungDetail::where('id',$idKasgantungDetail)->first();
        
                        $datadetail = [
                            "pengembaliankasgantung_id" => $pengembalianKasGantungHeader->id,
                            "nobukti" => $pengembalianKasGantungHeader->nobukti,
                            "nominal" => $kasgantung->nominal,
                            "coadetail" => $request->coadetail[$i],
                            "keterangandetail" => $request->keterangandetail[$i],
                            "kasgantung_nobukti" => $kasgantung->nobukti,
                        ];
                        $detaillog []=$datadetail;
                        $data = new StorePengembalianKasGantungDetailRequest($datadetail);
                        $pengembalianKasGantungDetail = app(PengembalianKasGantungDetailController::class)->store($data);
    
                        if ($pengembalianKasGantungDetail['error']) {
                            return response($pengembalianKasGantungDetail, 422);
                        } else {
                            $iddetail = $pengembalianKasGantungDetail['id'];
                            $tabeldetail = $pengembalianKasGantungDetail['tabel'];
                        }

                        $datalogtrail = [
                            'namatabel' => $tabeldetail,
                            'postingdari' => 'ENTRY PENERIMAAN STOK HEADER',
                            'idtrans' =>  $iddetail,
                            'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                            'aksi' => 'ENTRY',
                            'datajson' => $detaillog,
                            'modifiedby' => auth('api')->user()->name,
                        ];
                        $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                        
                        DB::commit();
                    }
                    /* Set position and page */
                    $selected = $this->getPosition($pengembalianKasGantungHeader, $pengembalianKasGantungHeader->getTable());
                    $pengembalianKasGantungHeader->position = $selected->position;
                    $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / ($request->limit ?? 10));
                    
                    if (isset($request->limit)) {
                        $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / $request->limit);
                    }
                    
                    return response([
                        'message' => 'Berhasil disimpan',
                        'data' => $pengembalianKasGantungHeader
                    ], 201);
                } 
            }


         }catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }  
            
        return response([
            'message' => 'Berhasil disimpan',
            'data' => $id
        ], 422);
    }
    /**
     * @ClassName 
     */
    public function destroy(PengembalianKasGantungHeader $pengembalianKasGantungHeader,$id)
    {
        DB::beginTransaction();

        $pengembalianKasGantungHeader = PengembalianKasGantungHeader::where('id',$id)->first();
        $delete = $pengembalianKasGantungHeader->delete();

        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($pengembalianKasGantungHeader->getTable()),
                'postingdari' => 'DELETE Pengembalian Kas Gantung',
                'idtrans' => $pengembalianKasGantungHeader->id,
                'nobuktitrans' => $pengembalianKasGantungHeader->id,
                'aksi' => 'DELETE',
                'datajson' => $pengembalianKasGantungHeader->toArray(),
                'modifiedby' => $pengembalianKasGantungHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($pengembalianKasGantungHeader, $pengembalianKasGantungHeader->getTable(), true);
            $pengembalianKasGantungHeader->position = $selected->position;
            $pengembalianKasGantungHeader->id = $selected->id;
            $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengembalianKasGantungHeader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('PengembalianKasGantungHeader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    //untuk create
    public function getKasGantung()
    {
        $KasGantung = new KasGantungHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $KasGantung->getKasGantung(),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $KasGantung->totalRows,
                'totalPages' => $KasGantung->totalPages
            ]
        ]);
    }
    public function getPengembalian($id)
    {
        $Pengembalian = new PengembalianKasGantungHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $Pengembalian->getPengembalian($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $Pengembalian->totalRows,
                'totalPages' => $Pengembalian->totalPages
            ]
        ]);
    }
}
