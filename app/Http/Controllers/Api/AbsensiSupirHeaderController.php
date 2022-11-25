<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiSupirHeaderRequest;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Models\AbsensiSupirHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAbsensiSupirHeaderRequest;
use App\Models\AbsensiSupirDetail;
use App\Models\KasGantungDetail;
use App\Models\KasGantungHeader;

use Illuminate\Database\QueryException;

class AbsensiSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $absensiSupirHeader = new AbsensiSupirHeader();

        return response([
            'data' => $absensiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $absensiSupirHeader->totalRows,
                'totalPages' => $absensiSupirHeader->totalPages
            ]
        ]);
    }

    public function show($id) {
        $data = AbsensiSupirHeader::find($id);
        $detail = AbsensiSupirDetail::find($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    public function detail($id)
    {
        return response([
            'data' => AbsensiSupirDetail::with('trado', 'supir', 'absenTrado')->where('absensi_id', $id)->get()
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreAbsensiSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'ABSENSI';
            $subgroup = 'ABSENSI';
            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'absensisupirheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            
            /* Store header */
            $absensiSupirHeader = new AbsensiSupirHeader();
            

            $absensiSupirHeader->nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $absensiSupirHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $absensiSupirHeader->keterangan = $request->keterangan ?? '';
            $absensiSupirHeader->kasgantung_nobukti = $request->kasgantung_nobukti ?? '';
            $absensiSupirHeader->nominal = array_sum($request->uangjalan);
            $absensiSupirHeader->statusformat = $format->id;
            $absensiSupirHeader->modifiedby = auth('api')->user()->name;

            TOP:
            
            if ($absensiSupirHeader->save()) {
                /* Store Header LogTrail */
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                    'postingdari' => 'ENTRY ABSENSI SUPIR HEADER',
                    'idtrans' => $absensiSupirHeader->id,
                    'nobuktitrans' => $absensiSupirHeader->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $absensiSupirHeader->toArray(),
                    'modifiedby' => $absensiSupirHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                for ($i = 0; $i < count($request->trado_id); $i++) {
                    /* Store Detail */
                    $absensiSupirDetail = $absensiSupirHeader->absensiSupirDetail()->create([
                        'absensi_id' => $absensiSupirHeader->id,
                        'nobukti' => $absensiSupirHeader->nobukti,
                        'trado_id' => $request->trado_id[$i],
                        'supir_id' => $request->supir_id[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'uangjalan' => $request->uangjalan[$i],
                        'absen_id' => $request->absen_id[$i] ?? '',
                        'jam' => $request->jam[$i],
                        'modifiedby' => $absensiSupirHeader->modifiedby,
                    ]);

                    if ($absensiSupirDetail) {
                        /* Store Detail LogTrail */
                        $detailLogTrail = [
                            'namatabel' => strtoupper($absensiSupirDetail->getTable()),
                            'postingdari' => 'ENTRY ABSENSI SUPIR DETAIL',
                            'idtrans' => $absensiSupirDetail->id,
                            'nobuktitrans' => $absensiSupirDetail->id,
                            'aksi' => 'ENTRY',
                            'datajson' => $absensiSupirDetail->toArray(),
                            'modifiedby' => $absensiSupirDetail->modifiedby
                        ];

                        $validatedLogTrail = new StoreLogTrailRequest($detailLogTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    }
                }

                $group = 'KAS GANTUNG';
                $subgroup = 'NOMOR KAS GANTUNG';
                $format = DB::table('parameter')
                    ->where('grp', $group )
                    ->where('subgrp', $subgroup)
                    ->first();

                $noBuktiKasgantungRequest = new Request();
                $noBuktiKasgantungRequest['group'] = 'KAS GANTUNG';
                $noBuktiKasgantungRequest['subgroup'] = 'NOMOR KAS GANTUNG';
                $noBuktiKasgantungRequest['table'] = 'kasgantungheader';
                $noBuktiKasgantungRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $nobuktiKasGantung= app(Controller::class)->getRunningNumber($noBuktiKasgantungRequest)->original['data'];
                    
                $absensiSupirHeader->kasgantung_nobukti = $nobuktiKasGantung;
                $absensiSupirHeader->save();


                $kasGantungHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $nobuktiKasGantung,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'penerima_id' => '',
                    'keterangan' => $request->keterangan,
                    'bank_id' => '',
                    'pengeluaran_nobukti' => '',
                    'coakaskeluar' => '',
                    'postingdari' => 'ENTRY ABSENSI SUPIR',
                    'tglkaskeluar' => '1900/1/1',
                    'statusformat' => $format->id,
                    'modifiedby' => auth('api')->user()->name
                ];

                $kasGantungDetail = [];
                    for ($i = 0; $i < count($request->uangjalan); $i++) {
                        $detail = [];
                        
                        $detail = [
                            'entriluar' => 1,
                            'nobukti' => $nobuktiKasGantung,
                            'nominal' => $request->uangjalan[$i],
                            'coa' => '',
                            'keterangan_detail' => $request->keterangan_detail[$i],
                            'modifiedby' =>  auth('api')->user()->name
                        ];
                        $kasGantungDetail[] = $detail;
                    }
                    
                    $kasGantung = $this->storeKasGantung($kasGantungHeader,$kasGantungDetail);
                   
                   
                    // if (!$kasGantung['status'] AND @$kasGantung['errorCode'] == 2601) {
                    //     goto ATAS;
                    // }
                    if (!$kasGantung['status']) {
                        throw new \Throwable($kasGantung['message']);
                    }

            }

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable());
            $absensiSupirHeader->position = $selected->position;
            $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirHeader
            ], 201);
        } catch (QueryException $queryException) {
            if (isset($queryException->errorInfo[1]) && is_array($queryException->errorInfo)) {
                // Check if deadlock
                if ($queryException->errorInfo[1] === 1205) {
                    goto TOP;
                }
            }

            throw $queryException;

        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        return response($absensiSupirHeader->absensiSupirDetail);
    }

    /**
     * @ClassName 
     */
    public function update(StoreAbsensiSupirHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $group = 'ABSENSI';
            $subgroup = 'ABSENSI';
            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            /* Store header */
            $absensiSupirHeader = AbsensiSupirHeader::lockForUpdate()->findOrFail($id);
            $absensiSupirHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $absensiSupirHeader->keterangan = $request->keterangan ?? '';
            $absensiSupirHeader->nominal = array_sum($request->uangjalan);
            $absensiSupirHeader->modifiedby = auth('api')->user()->name;
            $absensiSupirHeader->statusformat = $format->id;

            if ($absensiSupirHeader->save()) {
                /* Store Header LogTrail */
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                    'postingdari' => 'EDIT ABSENSI SUPIR HEADER',
                    'idtrans' => $absensiSupirHeader->id,
                    'nobuktitrans' => $absensiSupirHeader->id,
                    'aksi' => 'EDIT',
                    'datajson' => $absensiSupirHeader->toArray(),
                    'modifiedby' => $absensiSupirHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                KasGantungDetail::where('nobukti',$request->kasgantung_nobukti)->lockForUpdate()->delete();
                KasGantungHeader::where('nobukti',$request->kasgantung_nobukti)->lockForUpdate()->delete();
                AbsensiSupirDetail::where('absensi_id',$id)->lockForUpdate()->delete();

                for ($i = 0; $i < count($request->trado_id); $i++) {
                    /* Store Detail */
                    $absensiSupirDetail = $absensiSupirHeader->absensiSupirDetail()->create([
                        'absensi_id' => $absensiSupirHeader->id,
                        'nobukti' => $absensiSupirHeader->nobukti,
                        'trado_id' => $request->trado_id[$i],
                        'supir_id' => $request->supir_id[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'uangjalan' => $request->uangjalan[$i],
                        'absen_id' => $request->absen_id[$i],
                        'jam' => $request->jam[$i],
                        'modifiedby' => $absensiSupirHeader->modifiedby,
                    ]);

                    if ($absensiSupirDetail) {
                        /* Store Detail LogTrail */
                        $detailLogTrail = [
                            'namatabel' => strtoupper($absensiSupirDetail->getTable()),
                            'postingdari' => 'EDIT ABSENSI SUPIR DETAIL',
                            'idtrans' => $absensiSupirDetail->id,
                            'nobuktitrans' => $absensiSupirDetail->id,
                            'aksi' => 'EDIT',
                            'datajson' => $absensiSupirDetail->toArray(),
                            'modifiedby' => $absensiSupirDetail->modifiedby
                        ];

                        $validatedLogTrail = new StoreLogTrailRequest($detailLogTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    }
                }

                $group = 'KAS GANTUNG';
                $subgroup = 'NOMOR KAS GANTUNG';
                $format = DB::table('parameter')
                    ->where('grp', $group )
                    ->where('subgrp', $subgroup)
                    ->first();

                $noBuktiKasgantungRequest = new Request();
                $noBuktiKasgantungRequest['group'] = 'KAS GANTUNG';
                $noBuktiKasgantungRequest['subgroup'] = 'NOMOR KAS GANTUNG';
                $noBuktiKasgantungRequest['table'] = 'kasgantungheader';
                $noBuktiKasgantungRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $nobuktiKasGantung= app(Controller::class)->getRunningNumber($noBuktiKasgantungRequest)->original['data'];
                    
                $absensiSupirHeader->kasgantung_nobukti = $nobuktiKasGantung;
                $absensiSupirHeader->save();


                $kasGantungHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $nobuktiKasGantung,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'penerima_id' => '',
                    'keterangan' => $request->keterangan,
                    'bank_id' => '',
                    'pengeluaran_nobukti' => '',
                    'coakaskeluar' => '',
                    'postingdari' => 'ENTRY ABSENSI SUPIR',
                    'tglkaskeluar' => '1900/1/1',
                    'statusformat' => $format->id,
                    'modifiedby' => auth('api')->user()->name
                ];

                $kasGantungDetail = [];
                    for ($i = 0; $i < count($request->uangjalan); $i++) {
                        $detail = [];
                        
                        $detail = [
                            'entriluar' => 1,
                            'nobukti' => $nobuktiKasGantung,
                            'nominal' => $request->uangjalan[$i],
                            'coa' => '',
                            'keterangan_detail' => $request->keterangan_detail[$i],
                            'modifiedby' =>  auth('api')->user()->name
                        ];
                        $kasGantungDetail[] = $detail;
                    }

                    $kasGantung = $this->storeKasGantung($kasGantungHeader,$kasGantungDetail);
                   
                   
                    // if (!$kasGantung['status'] AND @$kasGantung['errorCode'] == 2601) {
                    //     goto ATAS;
                    // }
                    if (!$kasGantung['status']) {
                        throw new \Throwable($kasGantung['message']);
                    }
            }

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable());
            $absensiSupirHeader->position = $selected->position;
            $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));

            return response([
                'message' => 'Berhasil diubah',
                'data' => $absensiSupirHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    /**
     * @ClassName 
     */
    public function destroy($id, Request $request)
    {
        DB::beginTransaction();
        $absensiSupirHeader = new AbsensiSupirHeader();
        try{
            $get = AbsensiSupirHeader::lockForUpdate()->findOrFail($id);
            $delete = AbsensiSupirDetail::where('absensi_id',$id)->lockForUpdate()->delete();
            $delete = KasGantungDetail::where('nobukti',$get->kasgantung_nobukti)->lockForUpdate()->delete();
            $delete = KasGantungHeader::where('nobukti',$get->kasgantung_nobukti)->lockForUpdate()->delete();
            $delete = AbsensiSupirHeader::destroy($id);
            
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                    'postingdari' => 'DELETE ABSENSI SUPIR',
                    'idtrans' => $id,
                    'nobuktitrans' => $get->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $absensiSupirHeader->toArray(),
                    'modifiedby' => $absensiSupirHeader->modifiedby
                ];
    
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
    
                DB::commit();
                
                $selected = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable(), true);
                $absensiSupirHeader->position = $selected->position;
                $absensiSupirHeader->id = $selected->id;
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));
    
                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $absensiSupirHeader
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

    public function storeKasGantung($kasGantungHeader,$kasGantungDetail)
    {
        try {

          
            $kasGantung = new StoreKasGantungHeaderRequest($kasGantungHeader);
            $header = app(KasGantungHeaderController::class)->store($kasGantung);
           
            $nobukti = $kasGantungHeader['nobukti'];
            $fetchId = KasGantungHeader::select('id','pengeluaran_nobukti')
                ->whereRaw("nobukti = '$nobukti'")
                ->first();
            $id = $fetchId->id;
            $pengeluaranNoBukti = $fetchId->pengeluaran_nobukti;
            foreach ($kasGantungDetail as $value) {
                
                $value['kasgantung_id'] = $id;
                $value['pengeluaran_nobukti'] = $pengeluaranNoBukti;
                $kasGantungDetail = new StoreKasGantungDetailRequest($value);
                $tes = app(KasGantungDetailController::class)->store($kasGantungDetail);
                
            }

            
            return [
                'status' => true
            ];

        } catch (\Throwable $th) {
            throw $th;
            
        }
    }

    
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('absensisupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
