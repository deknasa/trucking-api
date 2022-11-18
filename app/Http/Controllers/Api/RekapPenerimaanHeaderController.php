<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Models\RekapPenerimaanHeader;
use App\Http\Requests\StoreRekapPenerimaanHeaderRequest;
use App\Http\Requests\UpdateRekapPenerimaanHeaderRequest;

use App\Models\RekapPenerimaanDetail;
use App\Models\PenerimaanHeader;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreRekapPenerimaanDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;

class RekapPenerimaanHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $rekapPenerimaanHeader = new RekapPenerimaanHeader();
        return response([
            'data' => $rekapPenerimaanHeader->get(),
            'attributes' => [
                'totalRows' => $rekapPenerimaanHeader->totalRows,
                'totalPages' => $rekapPenerimaanHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreRekapPenerimaanHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $group = 'REKAP PENERIMAAN';
            $subgroup = 'REKAP PENERIMAAN';

            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();
            $content = new Request();
            $content['group'] = $group ;
            $content['subgroup'] = $subgroup ;
            $content['table'] = 'rekappenerimaanheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            $rekapPenerimaanHeader = new RekapPenerimaanHeader();
            
            $rekapPenerimaanHeader->tglbukti = date('Y-m-d',strtotime($request->tglbukti));
            $rekapPenerimaanHeader->keterangan = $request->keterangan;
            $rekapPenerimaanHeader->tgltransaksi  = date('Y-m-d',strtotime($request->tgltransaksi ));
            $rekapPenerimaanHeader->bank_id = $request->bank_id;
            $rekapPenerimaanHeader->statusapproval = $statusNonApproval->id;
            $rekapPenerimaanHeader->statusformat = $format->id;
            $rekapPenerimaanHeader->modifiedby = auth('api')->user()->name;
            TOP:
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $rekapPenerimaanHeader->nobukti = $nobukti;

            if ($rekapPenerimaanHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($rekapPenerimaanHeader->getTable()),
                    'postingdari' => 'ENTRY REKAP PENERIMAAN HEADER',
                    'idtrans' => $rekapPenerimaanHeader->id,
                    'nobuktitrans' => $rekapPenerimaanHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $rekapPenerimaanHeader->toArray(),
                    'modifiedby' => $rekapPenerimaanHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                /* Store detail */
                
                if ($request->penerimaan_nobukti) {
                    $rekapPenerimaanDetail = RekapPenerimaanDetail::where('rekappenerimaan_id',$rekapPenerimaanHeader->id)->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->penerimaan_nobukti); $i++) {
                        $datadetail = [
                            "rekappenerimaan_id" => $rekapPenerimaanHeader->id,
                            "nobukti" =>  $rekapPenerimaanHeader->nobukti,
                            "tgltransaksi" => $request->tgltransaksi_detail[$i],
                            "penerimaan_nobukti" => $request->penerimaan_nobukti[$i],
                            "nominal" => $request->nominal[$i],
                            "keterangandetail" => $request->keterangan_detail[$i],
                            "modifiedby" => $rekapPenerimaanHeader->modifiedby = auth('api')->user()->name
                        ];
                        
                        $detaillog []=$datadetail;
                        $data = new StoreRekapPenerimaanDetailRequest($datadetail);
                        $rekapPenerimaanDetail = app(RekapPenerimaanDetailController::class)->store($data);
    
                        if ($rekapPenerimaanDetail['error']) {
                            return response($rekapPenerimaanDetail, 422);
                        } else {
                            $iddetail = $rekapPenerimaanDetail['id'];
                            $tabeldetail = $rekapPenerimaanDetail['tabel'];
                        }
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY NOTA KREDIT DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $rekapPenerimaanHeader->nobukti,
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
            $selected = $this->getPosition($rekapPenerimaanHeader, $rekapPenerimaanHeader->getTable());
            $rekapPenerimaanHeader->position = $selected->position;
            $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));
            
            if (isset($request->limit)) {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / $request->limit);
            }
            
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $rekapPenerimaanHeader
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

    public function show(RekapPenerimaanHeader $rekapPenerimaanHeader,$id)
    {
        $data = $rekapPenerimaanHeader->find($id);
        
        return response([
            'status' => true,
            'data' => $data,
        ]);
    }
    /**
     * @ClassName 
     */
    public function update(UpdateRekapPenerimaanHeaderRequest $request, RekapPenerimaanHeader $rekapPenerimaanHeader,$id)
    {
        DB::beginTransaction();

        try {
            
            $rekapPenerimaanHeader = RekapPenerimaanHeader::findOrFail($id);

            $rekapPenerimaanHeader->tglbukti = date('Y-m-d',strtotime($request->tglbukti));
            $rekapPenerimaanHeader->keterangan = $request->keterangan;
            $rekapPenerimaanHeader->tgltransaksi  = date('Y-m-d',strtotime($request->tgltransaksi ));
            $rekapPenerimaanHeader->bank_id = $request->bank_id;
            $rekapPenerimaanHeader->modifiedby = auth('api')->user()->name;

            if ($rekapPenerimaanHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($rekapPenerimaanHeader->getTable()),
                    'postingdari' => 'ENTRY REKAP PENGELUARAN HEADER',
                    'idtrans' => $rekapPenerimaanHeader->id,
                    'nobuktitrans' => $rekapPenerimaanHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $rekapPenerimaanHeader->toArray(),
                    'modifiedby' => $rekapPenerimaanHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                /* Store detail */
                
                if ($request->penerimaan_nobukti) {
                    $rekapPenerimaanDetail = RekapPenerimaanDetail::where('rekappenerimaan_id',$rekapPenerimaanHeader->id)->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->penerimaan_nobukti); $i++) {
                        $datadetail = [
                            "rekappenerimaan_id" => $rekapPenerimaanHeader->id,
                            "nobukti" =>  $rekapPenerimaanHeader->nobukti,
                            "tgltransaksi" => $request->tgltransaksi_detail[$i],
                            "penerimaan_nobukti" => $request->penerimaan_nobukti[$i],
                            "nominal" => $request->nominal[$i],
                            "keterangandetail" => $request->keterangan_detail[$i],
                            "modifiedby" => $rekapPenerimaanHeader->modifiedby = auth('api')->user()->name
                        ];
                        
                        $detaillog []=$datadetail;
                        $data = new StoreRekapPenerimaanDetailRequest($datadetail);
                        $rekapPenerimaanDetail = app(RekapPenerimaanDetailController::class)->store($data);
    
                        if ($rekapPenerimaanDetail['error']) {
                            return response($rekapPenerimaanDetail, 422);
                        } else {
                            $iddetail = $rekapPenerimaanDetail['id'];
                            $tabeldetail = $rekapPenerimaanDetail['tabel'];
                        }
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT NOTA KREDIT DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $rekapPenerimaanHeader->nobukti,
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
            $selected = $this->getPosition($rekapPenerimaanHeader, $rekapPenerimaanHeader->getTable());
            $rekapPenerimaanHeader->position = $selected->position;
            $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));
            
            if (isset($request->limit)) {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / $request->limit);
            }
            
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $rekapPenerimaanHeader
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
    public function destroy(RekapPenerimaanHeader $rekapPenerimaanHeader,$id)
    {
        DB::beginTransaction();
        $rekapPenerimaanHeader = RekapPenerimaanHeader::findOrFail($id);

        try {
            
            $delete = RekapPenerimaanDetail::where('rekappenerimaan_id',$id)->delete();
            $delete = $rekapPenerimaanHeader->delete();
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($rekapPenerimaanHeader->getTable()),
                    'postingdari' => 'DELETE Rekap Penerimaan Header',
                    'idtrans' => $id,
                    'nobuktitrans' => '',
                    'aksi' => 'DELETE',
                    'datajson' => $rekapPenerimaanHeader->toArray(),
                    'modifiedby' => $rekapPenerimaanHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($rekapPenerimaanHeader, $rekapPenerimaanHeader->getTable(), true);
                $rekapPenerimaanHeader->position = $selected->position;
                $rekapPenerimaanHeader->id = $selected->id;
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $rekapPenerimaanHeader
                ]);
            } else {
                DB::rollBack();

                return response([
                    'status' => false,
                    'message' => 'Gagal dihapus'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName 
     */
    public function approval($id)
    {
        DB::beginTransaction();
        $rekapPenerimaanHeader = RekapPenerimaanHeader::findOrFail($id);
        try {
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($rekapPenerimaanHeader->statusapproval == $statusApproval->id) {
                $rekapPenerimaanHeader->statusapproval = $statusNonApproval->id;
            } else {
                $rekapPenerimaanHeader->statusapproval = $statusApproval->id;
            }

            $rekapPenerimaanHeader->tglapproval = date('Y-m-d', time());
            $rekapPenerimaanHeader->userapproval = auth('api')->user()->name;

            if ($rekapPenerimaanHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($rekapPenerimaanHeader->getTable()),
                    'postingdari' => 'UN/APPROVE ABSENSI SUPIR APPROVAL',
                    'idtrans' => $rekapPenerimaanHeader->id,
                    'nobuktitrans' => $rekapPenerimaanHeader->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $rekapPenerimaanHeader->toArray(),
                    'modifiedby' => $rekapPenerimaanHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $rekapPenerimaanHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    
    public function getPenerimaan(Request $request)
    {
        $penerimaan = new PenerimaanHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $penerimaan->getRekapPenerimaanHeader($request->bank,date('Y-m-d', strtotime($request->tglbukti))),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $penerimaan->totalRows,
                'totalPages' => $penerimaan->totalPages
            ]
        ]);
    }

    public function getRekapPenerimaan($id)
    {
        $rekapPenerimaan = new RekapPenerimaanHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $rekapPenerimaan->getRekapPenerimaanHeader($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $rekapPenerimaan->totalRows,
                'totalPages' => $rekapPenerimaan->totalPages
            ]
        ]);
    }
}
