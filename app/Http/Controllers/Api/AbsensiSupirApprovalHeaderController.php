<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSupirApprovalHeader;
use App\Models\AbsensiSupirHeader;
use App\Models\AbsensiSupirApprovalDetail;

use App\Models\KasGantungHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;


use App\Http\Controllers\Controller;

use App\Http\Requests\StoreAbsensiSupirApprovalHeaderRequest;
use App\Http\Requests\UpdateAbsensiSupirApprovalHeaderRequest;
use App\Http\Requests\StoreAbsensiSupirApprovalDetailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class AbsensiSupirApprovalHeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();

        return response([
            'data' => $absensiSupirApprovalHeader->get(),
            'attributes' => [
                'totalRows' => $absensiSupirApprovalHeader->totalRows,
                'totalPages' => $absensiSupirApprovalHeader->totalPages
            ]
        ]);
    }

    public function store(StoreAbsensiSupirApprovalHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'ABSENSI SUPIR APPROVAL BUKTI';
            $subgroup = 'ABSENSI SUPIR APPROVAL BUKTI';
            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'absensisupirapprovalheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $group = 'COA APPROVAL ABSENSI SUPIR KREDIT';
            $subgroup = 'COA APPROVAL ABSENSI SUPIR KREDIT';
            $coaaproval = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            $group = 'COA APPROVAL ABSENSI SUPIR DEBET';
            $subgroup = 'COA APPROVAL ABSENSI SUPIR DEBET';
            $coadebet = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();
            
            $coakaskeluar = $coaaproval->text;
            $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
            $absensisupir = DB::table('absensisupirheader')->where('nobukti', $request->absensisupir )->first();
            
            /* Store header */
            $absensiSupirApprovalHeader->tglbukti =  date('Y-m-d',strtotime($request->tglbukti));
            $absensiSupirApprovalHeader->absensisupir_nobukti =  $request->absensisupir_nobukti;
            $absensiSupirApprovalHeader->keterangan =  $request->keterangan;
            $absensiSupirApprovalHeader->statusapproval=  4;
            $absensiSupirApprovalHeader->statusformat =  $format->id;
            $absensiSupirApprovalHeader->pengeluaran_nobukti= $kasgantung->pengeluaran_nobukti ?? '0';
            $absensiSupirApprovalHeader->coakaskeluar= $coakaskeluar;
            $absensiSupirApprovalHeader->postingdari =  "ABSENSI SUPIR APPROVAL";
            $absensiSupirApprovalHeader->modifiedby =  auth('api')->user()->name;
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $absensiSupirApprovalHeader->nobukti = $nobukti;
            
            if ($absensiSupirApprovalHeader->save()) {
                $bank = DB::table('bank')->where('coa', $coakaskeluar )->first();
                $kasgantung = DB::table('kasgantungheader')->where('nobukti', $request->kasgantung_nobukti )->first();
                $kasgantungdetail = DB::table('kasgantungdetail')->where('nobukti', $request->kasgantung_nobukti )->get();
                $details=[];
                $total = 0;
                foreach ($kasgantungdetail as $detail) {
                    $details['keterangan'][] = $detail->keterangan;
                    $details['nominal'][] = $detail->nominal;
                    $total+= $detail->nominal;
                }

                $dataKasgantung =[
                    "tglbukti" => $kasgantung->tglbukti,
                    "keterangan" => $absensiSupirApprovalHeader->keterangan,
                    "bank_id" => $bank->id,
                    "penerima_id" => $kasgantung->penerima_id,
                    "coakaskeluar" => $coakaskeluar,
                    "postingdari" => 'ENTRY ABSENSI SUPIR APPROVAL',
                    "tglkaskeluar" => $request->tglbukti,
                    'keterangan_detail' => $details['keterangan'],
                    'nominal' => $details['nominal'],
                ];

                $data = new StoreKasGantungHeaderRequest($dataKasgantung);
                $kasgantungStore = app(KasGantungHeaderController::class)->update($data,$kasgantung->id);
                $kasgantung = $kasgantungStore->original['data'];
                
                
                $absensiSupirApprovalHeader->pengeluaran_nobukti = $kasgantung->pengeluaran_nobukti;
                $absensiSupirApprovalHeader->save();


                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                    'postingdari' => 'ENTRY ABSENSI SUPIR APPROVAL HEADER',
                    'idtrans' => $absensiSupirApprovalHeader->id,
                    'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                    'aksi' => 'ADD',
                    'datajson' => $absensiSupirApprovalHeader->toArray(),
                    'modifiedby' => $absensiSupirApprovalHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                if ($request->trado_id) {
                    /* Store detail */
                    $detaillog = [];
                    $jurnalDetail = [];
                    for ($i=0; $i < count($request->trado_id); $i++) { 
                        $datadetail = [
                            "absensisupirapproval_id"=> $absensiSupirApprovalHeader->id,
                            "nobukti"=> $absensiSupirApprovalHeader->nobukti,
                            "trado_id"=> $request->trado_id[$i],
                            "supir_id"=> $request->supir_id[$i],
                            "modifiedby"=> auth('api')->user()->name
                        ];
                        $data = new StoreAbsensiSupirApprovalDetailRequest($datadetail);
                        $absensiSupirApprovalDetail = app(AbsensiSupirApprovalDetailController::class)->store($data);
                        
                        if ($absensiSupirApprovalDetail['error']) {
                            return response($absensiSupirApprovalDetail, 422);
                        } else {
                            $iddetail = $absensiSupirApprovalDetail['id'];
                            $tabeldetail = $absensiSupirApprovalDetail['tabel'];
                        }
                        $datadetaillog = [
                            "id"=> $iddetail,
                            "absensisupirapproval_id"=> $absensiSupirApprovalHeader->id,
                            "nobukti"=> $absensiSupirApprovalHeader->nobukti,
                            "trado_id"=> $request->trado_id[$i],
                            "supir_id"=> $request->supir_id[$i],
                            "modifiedby"=> auth('api')->user()->name,
                            'created_at' => date('d-m-Y H:i:s', strtotime($absensiSupirApprovalHeader->created_at)),
                            'updated_at' => date('d-m-Y H:i:s', strtotime($absensiSupirApprovalHeader->updated_at)),
                        ];
                        
                        
                        $detaillog[] = $datadetaillog;
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY ABSENSI SUPIR APPROVAL DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
        
                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);

                    $jurnalHeader = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $absensiSupirApprovalHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($absensiSupirApprovalHeader->tglbukti)),
                        'keterangan' => $absensiSupirApprovalHeader->keterangan,
                        'postingdari' => 'ENTRY ABSENSI SUPIR APPROVAL DETAIL',
                        'statusapproval' => $absensiSupirApprovalHeader->statusapproval,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'statusformat' => 0,
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $jurnalDetail = [
                        [
                            'nobukti' => $absensiSupirApprovalHeader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($absensiSupirApprovalHeader->tglbukti)),
                            'coa' =>  $coadebet->text,
                            'nominal' => $total,
                            'keterangan' => $request->keterangan,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ],[
                            'nobukti' => $absensiSupirApprovalHeader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($absensiSupirApprovalHeader->tglbukti)),
                            'coa' =>  $kasgantung->coakaskeluar,
                            'nominal' => -$total,
                            'keterangan' => $request->keterangan,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ]
                    ];

                }
                DB::commit();

            }
            /* Set position and page */

            $selected = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable());
            $absensiSupirApprovalHeader->position = $selected->position;
            $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirApprovalHeader
            ], 201);
        }catch (\Throwable $th){
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AbsensiSupirApprovalHeader  $absensiSupirApprovalHeader
     * @return \Illuminate\Http\Response
     */
    public function show(AbsensiSupirApprovalHeader $absensiSupirApprovalHeader,$id)
    {
        $data = $absensiSupirApprovalHeader->find($id);
        // $detail = NotaDebetHeaderDetail::findAll($id);
        
        return response([
            'status' => true,
            'data' => $data,
            // 'detail' => $detail
        ]);
    }

    public function update(UpdateAbsensiSupirApprovalHeaderRequest $request, AbsensiSupirApprovalHeader $absensiSupirApprovalHeader,$id)
    {
        DB::beginTransaction();

        try {

            $group = 'ABSENSI SUPIR APPROVAL BUKTI';
            $subgroup = 'ABSENSI SUPIR APPROVAL BUKTI';
            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            $group = 'COA APPROVAL ABSENSI SUPIR KREDIT';
            $subgroup = 'COA APPROVAL ABSENSI SUPIR KREDIT';
            $coaaproval = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();
            
            $group = 'COA APPROVAL ABSENSI SUPIR DEBET';
            $subgroup = 'COA APPROVAL ABSENSI SUPIR DEBET';
            $coadebet = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();
            
            $coakaskeluar = $coaaproval->text;

            /* Store header */
            $absensiSupirApprovalHeader = AbsensiSupirApprovalHeader::findOrFail($id);
            
            $kasgantung = DB::table('kasgantungheader')
            ->select('kasgantungheader.pengeluaran_nobukti','kasgantungheader.tglbukti','kasgantungdetail.coa')
            ->leftJoin('kasgantungdetail','kasgantungheader.id','kasgantungdetail.kasgantung_id')
            ->where('kasgantungheader.nobukti', $request->kasgantung_nobukti )
            ->first();

            /* Store header */
            $absensiSupirApprovalHeader->tglbukti =  date('Y-m-d',strtotime($request->tglbukti));
            $absensiSupirApprovalHeader->absensisupir_nobukti =  $request->absensisupir_nobukti;
            $absensiSupirApprovalHeader->keterangan =  $request->keterangan;
            $absensiSupirApprovalHeader->statusapproval=  4;
            $absensiSupirApprovalHeader->statusformat =  $format->id;
            $absensiSupirApprovalHeader->pengeluaran_nobukti= $kasgantung->pengeluaran_nobukti ?? '0';
            $absensiSupirApprovalHeader->coakaskeluar= $coakaskeluar;
            $absensiSupirApprovalHeader->postingdari =  "ABSENSI SUPIR APPROVAL";
            $absensiSupirApprovalHeader->modifiedby =  auth('api')->user()->name;
            if ($absensiSupirApprovalHeader->save()) {
                $bank = DB::table('bank')->where('coa', $coakaskeluar )->first();
                $kasgantung = DB::table('kasgantungheader')->where('nobukti', $request->kasgantung_nobukti )->first();
                $kasgantungdetail = DB::table('kasgantungdetail')->where('nobukti', $request->kasgantung_nobukti )->get();
                $details=[];
                $total = 0;
                foreach ($kasgantungdetail as $detail) {
                    $details['keterangan'][] = $detail->keterangan;
                    $details['nominal'][] = $detail->nominal;
                    $total+= $detail->nominal;
                }

                $dataKasgantung =[
                    "tglbukti" => $kasgantung->tglbukti,
                    "keterangan" => $absensiSupirApprovalHeader->keterangan,
                    "bank_id" => $bank->id,
                    "penerima_id" => $kasgantung->penerima_id,
                    "coakaskeluar" => $coakaskeluar,
                    "pengeluaran_nobukti"=>$absensiSupirApprovalHeader->pengeluaran_nobukti,
                    "postingdari" => 'ENTRY ABSENSI SUPIR APPROVAL',
                    "tglkaskeluar" => $request->tglbukti,
                    'keterangan_detail' => $details['keterangan'],
                    'nominal' => $details['nominal'],
                ];

                $data = new StoreKasGantungHeaderRequest($dataKasgantung);
                $kasgantungStore = app(KasGantungHeaderController::class)->update($data,$kasgantung->id);
                $kasgantung = $kasgantungStore->original['data'];
                
                
                $absensiSupirApprovalHeader->pengeluaran_nobukti = $kasgantung->pengeluaran_nobukti;
                $absensiSupirApprovalHeader->save();

                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                    'postingdari' => 'EDIT ABSENSI SUPIR APPROVAL HEADER',
                    'idtrans' => $absensiSupirApprovalHeader->id,
                    'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $absensiSupirApprovalHeader->toArray(),
                    'modifiedby' => $absensiSupirApprovalHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                if ($request->trado_id) {
                    
                    /* Store detail */
                    $detaillog = [];
                    AbsensiSupirApprovalDetail::where('absensisupirapproval_id',$id)->delete();
                    for ($i=0; $i < count($request->trado_id); $i++) { 
                        $datadetail = [
                            "absensisupirapproval_id"=> $absensiSupirApprovalHeader->id,
                            "nobukti"=> $absensiSupirApprovalHeader->nobukti,
                            "trado_id"=> $request->trado_id[$i],
                            "supir_id"=> $request->supir_id[$i],
                            "modifiedby"=> auth('api')->user()->name
                        ];
                        $data = new StoreAbsensiSupirApprovalDetailRequest($datadetail);
                        $absensiSupirApprovalDetail = app(AbsensiSupirApprovalDetailController::class)->store($data);
                        
                        if ($absensiSupirApprovalDetail['error']) {
                            return response($absensiSupirApprovalDetail, 422);
                        } else {
                            $iddetail = $absensiSupirApprovalDetail['id'];
                            $tabeldetail = $absensiSupirApprovalDetail['tabel'];
                        }
                        $datadetaillog = [
                            "id"=> $iddetail,
                            "absensisupirapproval_id"=> $absensiSupirApprovalHeader->id,
                            "nobukti"=> $absensiSupirApprovalHeader->nobukti,
                            "trado_id"=> $request->trado_id[$i],
                            "supir_id"=> $request->supir_id[$i],
                            "modifiedby"=> auth('api')->user()->name,
                            'created_at' => date('d-m-Y H:i:s', strtotime($absensiSupirApprovalHeader->created_at)),
                            'updated_at' => date('d-m-Y H:i:s', strtotime($absensiSupirApprovalHeader->updated_at)),
                        ];
                        $detaillog[] = $datadetaillog;
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY ABSENSI SUPIR APPROVAL DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                }
                DB::commit();
            }
            /* Set position and page */

            $selected = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable());
            $absensiSupirApprovalHeader->position = $selected->position;
            $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirApprovalHeader
            ], 201);
        }catch (\Throwable $th){
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
         
        return response($request->all(), 442); 
    }

   
    public function destroy(AbsensiSupirApprovalHeader $absensiSupirApprovalHeader,$id)
    {
        DB::beginTransaction();
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
        try{
            $get = AbsensiSupirApprovalHeader::findOrFail($id);
            $pengeluaran = $get->pengeluaran_nobukti;

            $kasGantung = KasGantungHeader::where('pengeluaran_nobukti',$pengeluaran)->first();
            $kasGantung->pengeluaran_nobukti = '';
            $kasGantung->coakaskeluar = '';
            $kasGantung->kasgantungDetail()->update(['coa'=>'']);
            // $kasgantungDetail = $kasGantung->kasgantungDetail()->get();
            // $kasgantungDetail->coa='';
            // ->update(['delayed' => 1]);
            $kasGantung->save();
            // dd($kasgantungDetail);
            $delete = PengeluaranDetail::where('nobukti',$get->pengeluaran_nobukti)->delete();
            $delete = PengeluaranHeader::where('nobukti',$get->pengeluaran_nobukti)->delete();
            $delete = JurnalUmumDetail::where('nobukti',$get->pengeluaran_nobukti)->delete();
            $delete = JurnalUmumHeader::where('nobukti',$get->pengeluaran_nobukti)->delete();
            $delete = AbsensiSupirApprovalDetail::where('absensisupirapproval_id',$id)->delete();
            $delete = $get->delete();
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                    'postingdari' => 'DELETE ABSENSI SUPIR APPROVAL',
                    'idtrans' => $id,
                    'nobuktitrans' => $get->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $absensiSupirApprovalHeader->toArray(),
                    'modifiedby' => $absensiSupirApprovalHeader->modifiedby
                ];
    
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
    
                DB::commit();
                
                $selected = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable(), true);
                $absensiSupirApprovalHeader->position = $selected->position;
                $absensiSupirApprovalHeader->id = $selected->id;
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));
    
                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $absensiSupirApprovalHeader
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

    public function approval($id)
    {
        DB::beginTransaction();
        $absensiSupirApprovalHeader = AbsensiSupirApprovalHeader::findOrFail($id);
        try {
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($absensiSupirApprovalHeader->statusapproval == $statusApproval->id) {
                $absensiSupirApprovalHeader->statusapproval = $statusNonApproval->id;
            } else {
                $absensiSupirApprovalHeader->statusapproval = $statusApproval->id;
            }

            $absensiSupirApprovalHeader->tglapproval = date('Y-m-d', time());
            $absensiSupirApprovalHeader->userapproval = auth('api')->user()->name;

            if ($absensiSupirApprovalHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                    'postingdari' => 'UN/APPROVE ABSENSI SUPIR APPROVAL',
                    'idtrans' => $absensiSupirApprovalHeader->id,
                    'nobuktitrans' => $absensiSupirApprovalHeader->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $absensiSupirApprovalHeader->toArray(),
                    'modifiedby' => $absensiSupirApprovalHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $absensiSupirApprovalHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function fieldLength(Type $var = null)
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('AbsensiSupirApprovalHeader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getAbsensi($absensi)
    {
        $absensiSupir = new AbsensiSupirHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $absensiSupir->getAbsensi($absensi),
            // 'data' => $absensi,
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $absensiSupir->totalRows,
                'totalPages' => $absensiSupir->totalPages
            ]
        ]);
    }
    public function getApproval($absensi)
    {
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
        $absensiSupir = $absensiSupirApprovalHeader->find($absensi);
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $absensiSupirApprovalHeader->getApproval($absensiSupir->absensisupir_nobukti),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $absensiSupirApprovalHeader->totalRows,
                'totalPages' => $absensiSupirApprovalHeader->totalPages
            ]
        ]);
    }
}
