<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGajiSupirDetailRequest;
use App\Models\GajiSupirHeader;
use App\Http\Requests\StoreGajiSupirHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateGajiSupirHeaderRequest;
use App\Models\GajiSupirDetail;
use App\Models\LogTrail;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GajiSupirHeaderController extends Controller
{
     /**
     * @ClassName 
     */
    public function index()
    {
        $gajiSupirHeader = new GajiSupirHeader();
        return response([
            'data' => $gajiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $gajiSupirHeader->totalRows,
                'totalPages' => $gajiSupirHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreGajiSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            if($request->sp_id != ''){            
                $group = 'RINCIAN GAJI SUPIR BUKTI';
                $subgroup = 'RINCIAN GAJI SUPIR BUKTI';


                $format = DB::table('parameter')
                    ->where('grp', $group )
                    ->where('subgrp', $subgroup)
                    ->first();

                $content = new Request();
                $content['group'] = $group ;
                $content['subgroup'] = $subgroup ;
                $content['table'] = 'gajisupirheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $gajisupirheader = new GajiSupirHeader();
                $gajisupirheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $gajisupirheader->supir_id = $request->supir_id;
                $gajisupirheader->nominal = '';
                $gajisupirheader->keterangan = $request->keterangan;
                $gajisupirheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
                $gajisupirheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
                $gajisupirheader->total = '';
                $gajisupirheader->uangjalan = $request->uangjalan ?? '';
                $gajisupirheader->bbm = $request->bbm ?? '';
                $gajisupirheader->potonganpinjaman = $request->potonganpinjaman ?? '';
                $gajisupirheader->deposito = $request->deposito ?? '';
                $gajisupirheader->potonganpinjamansemua = $request->potonganpinjamansemua ?? '';
                $gajisupirheader->komisisupir = $request->komisisupir ?? '';
                $gajisupirheader->tolsupir = $request->tolsupir ?? '';
                $gajisupirheader->voucher = $request->voucher ?? '';
                $gajisupirheader->uangmakanharian = $request->uangmakanharian ?? '';
                $gajisupirheader->pinjamanpribadi = $request->pinjamanpribadi ?? '';
                $gajisupirheader->gajiminus = $request->gajiminus ?? '';
                $gajisupirheader->uangJalantidakterhitung = $request->uangjalantidakterhitung ?? '';
                $gajisupirheader->statusformat = $format->id;
                $gajisupirheader->modifiedby = auth('api')->user()->name;
            
                TOP:
                    $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                    $gajisupirheader->nobukti = $nobukti;
    

                try {
                    $gajisupirheader->save();
                    DB::commit();
                } catch (\Exception $e) {
                    $errorCode = @$e->errorInfo[1];
                    if ($errorCode == 2601) {
                        goto TOP;
                    }
                }

                $logTrail = [
                    'namatabel' => strtoupper($gajisupirheader->getTable()),
                    'postingdari' => 'ENTRY GAJI SUPIR HEADER',
                    'idtrans' => $gajisupirheader->id,
                    'nobuktitrans' => $gajisupirheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $gajisupirheader->toArray(),
                    'modifiedby' => $gajisupirheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                /* Store detail */
                        
                $detaillog = [];
                
                $total = 0;
                $urut = 1;
                for ($i = 0; $i < count($request->sp_id); $i++) {
                
                    $sp = DB::table('suratpengantar')->where('id',$request->sp_id[$i])->first();
                    
                    $total = $total + $sp->gajisupir + $sp->gajikenek;
                    $datadetail = [
                        'gajisupir_id' => $gajisupirheader->id,
                        'nobukti' => $gajisupirheader->nobukti,
                        'nominaldeposito' => $request->nominaldeposito[$i] ?? 0,
                        'nourut' => $urut,
                        'suratpengantar_nobukti' => $sp->nobukti,
                        'komisisupir' => $sp->komisisupir,
                        'tolsupir' => $sp->tolsupir,
                        'voucher' => $request->voucher[$i] ?? 0,
                        'novoucher' => $request->novoucher[$i]  ?? 0,
                        'gajisupir' => $sp->gajisupir,
                        'gajikenek' => $sp->gajikenek,
                        'gajiritasi' => $request->gajiritasi[$i] ?? 0,
                        'nominalpengembalianpinjaman' => $request->nominalpengembalianpinjaman[$i] ?? 0,
                        'modifiedby' => $gajisupirheader->modifiedby,
                    ];

                    //STORE 
                    $data = new StoreGajiSupirDetailRequest($datadetail);
                    
                    $datadetails = app(GajiSupirDetailController::class)->store($data);
                    
                    
                    
                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    
                    
                    $datadetaillog = [
                        'id' => $iddetail,
                        'gajisupir_id' => $gajisupirheader->id,
                        'nobukti' => $gajisupirheader->nobukti,
                        'nominaldeposito' => $request->nominaldeposito[$i] ?? 0,
                        'nourut' => $urut,
                        'suratpengantar_nobukti' => $sp->nobukti,
                        'komisisupir' => $sp->komisisupir,
                        'tolsupir' => $sp->tolsupir,
                        'voucher' => $request->voucher[$i] ?? 0,
                        'novoucher' => $request->novoucher[$i]  ?? 0,
                        'gajisupir' => $sp->gajisupir,
                        'gajikenek' => $sp->gajikenek,
                        'gajiritasi' => $request->gajiritasi[$i] ?? 0,
                        'nominalpengembalianpinjaman' => $request->nominalpengembalianpinjaman[$i] ?? 0,
                        'modifiedby' => $gajisupirheader->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($gajisupirheader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($gajisupirheader->updated_at)),
                        
                    ];
                    
                    $detaillog[] = $datadetaillog;

                    
                    $dataid = LogTrail::select('id')
                    ->where('idtrans', '=', $gajisupirheader->id)
                    ->where('namatabel', '=', $gajisupirheader->getTable())
                    ->orderBy('id', 'DESC')
                    ->first();

                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY GAJI SUPIR DETAIL',
                        'idtrans' =>  $dataid->id,
                        'nobuktitrans' => $gajisupirheader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => $request->modifiedby,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                    
                    $urut++;
                }

                $gajisupirheader->nominal = $total;
                $gajisupirheader->total = $total;
                $gajisupirheader->save();
        
            
                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';
                DB::commit();
                
                /* Set position and page */
            

                $selected = $this->getPosition($gajisupirheader, $gajisupirheader->getTable());
                $gajisupirheader->position = $selected->position;
                $gajisupirheader->page = ceil($gajisupirheader->position / ($request->limit ?? 10));
                

                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $gajisupirheader 
                ], 201);
            }else{
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'WP')
                ->first();
                return response([
                    'errors' => [
                        'sp' => "SP $query->keterangan"
                    ],
                    'message' => "SP $query->keterangan",
                ], 422);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }   
    }

   
    public function show($id)
    {
        $data = GajiSupirHeader::findAll($id);
        // $detail = GajiSupirDetail::findAll($id);
        
        return response([
            'status' => true,
            'data' => $data,
            // 'detail' => $detail
        ]);
    }

     /**
     * @ClassName 
     */
    public function update(UpdateGajiSupirHeaderRequest $request, GajiSupirHeader $gajisupirheader)
    {
        DB::beginTransaction();
        
        try {

            $gajisupirheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $gajisupirheader->supir_id = $request->supir_id;
            $gajisupirheader->nominal = '';
            $gajisupirheader->keterangan = $request->keterangan;
            $gajisupirheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $gajisupirheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $gajisupirheader->total = '';
            $gajisupirheader->uangjalan = $request->uangjalan ?? '';
            $gajisupirheader->bbm = $request->bbm ?? '';
            $gajisupirheader->potonganpinjaman = $request->potonganpinjaman ?? '';
            $gajisupirheader->deposito = $request->deposito ?? '';
            $gajisupirheader->potonganpinjamansemua = $request->potonganpinjamansemua ?? '';
            $gajisupirheader->komisisupir = $request->komisisupir ?? '';
            $gajisupirheader->tolsupir = $request->tolsupir ?? '';
            $gajisupirheader->voucher = $request->voucher ?? '';
            $gajisupirheader->uangmakanharian = $request->uangmakanharian ?? '';
            $gajisupirheader->pinjamanpribadi = $request->pinjamanpribadi ?? '';
            $gajisupirheader->gajiminus = $request->gajiminus ?? '';
            $gajisupirheader->uangJalantidakterhitung = $request->uangjalantidakterhitung ?? '';
            $gajisupirheader->modifiedby = auth('api')->user()->name;

            
            if($gajisupirheader->save()){
                $logTrail = [
                    'namatabel' => strtoupper($gajisupirheader->getTable()),
                    'postingdari' => 'EDIT GAJI SUPIR HEADER',
                    'idtrans' => $gajisupirheader->id,
                    'nobuktitrans' => $gajisupirheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $gajisupirheader->toArray(),
                    'modifiedby' => $gajisupirheader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                GajiSupirDetail::where('gajisupir_id', $gajisupirheader->id)->lockForUpdate()->delete();

                /* Store detail */
                
                
                $detaillog = [];
                $total = 0;
                $urut = 1;

                for($i = 0; $i < count($request->sp_id); $i++){
                    $sp = DB::table('suratpengantar')->where('id',$request->sp_id[$i])->first();
                
                    $total = $total + $sp->gajisupir + $sp->gajikenek;

                    $datadetail = [
                        'gajisupir_id' => $gajisupirheader->id,
                        'nobukti' => $gajisupirheader->nobukti,
                        'nominaldeposito' => $request->nominaldeposito[$i] ?? 0,
                        'nourut' => $urut,
                        'suratpengantar_nobukti' => $sp->nobukti,
                        'komisisupir' => $sp->komisisupir,
                        'tolsupir' => $sp->tolsupir,
                        'voucher' => $request->voucher[$i] ?? 0,
                        'novoucher' => $request->novoucher[$i]  ?? 0,
                        'gajisupir' => $sp->gajisupir,
                        'gajikenek' => $sp->gajikenek,
                        'gajiritasi' => $request->gajiritasi[$i] ?? 0,
                        'nominalpengembalianpinjaman' => $request->nominalpengembalianpinjaman[$i] ?? 0,
                        'modifiedby' => $gajisupirheader->modifiedby,
                    ];

                    //STORE
                    
                    $data = new StoreGajiSupirDetailRequest($datadetail);
                    $datadetails = app(GajiSupirDetailController::class)->store($data);
                    
                    if($datadetails['error']){
                        return response($datadetails, 422);
                    }else{
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    
                    $datadetaillog = [
                        'id' => $iddetail,
                        'gajisupir_id' => $gajisupirheader->id,
                        'nobukti' => $gajisupirheader->nobukti,
                        'nominaldeposito' => $request->nominaldeposito[$i] ?? 0,
                        'nourut' => $urut,
                        'suratpengantar_nobukti' => $sp->nobukti,
                        'komisisupir' => $sp->komisisupir,
                        'tolsupir' => $sp->tolsupir,
                        'voucher' => $request->voucher[$i] ?? 0,
                        'novoucher' => $request->novoucher[$i]  ?? 0,
                        'gajisupir' => $sp->gajisupir,
                        'gajikenek' => $sp->gajikenek,
                        'gajiritasi' => $request->gajiritasi[$i] ?? 0,
                        'nominalpengembalianpinjaman' => $request->nominalpengembalianpinjaman[$i] ?? 0,
                        'modifiedby' => $gajisupirheader->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($gajisupirheader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($gajisupirheader->updated_at)),
                        
                    ];

                    $detaillog[] = $datadetaillog;

                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT GAJI SUPIR DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $gajisupirheader->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $detaillog,
                        'modifiedby' => $request->modifiedby,
                    ];
                    
                    $data = new StoreLogTrailRequest($datalogtrail);
                    
                    app(LogTrailController::class)->store($data);
                    $urut++;
                }
                
            }

            $gajisupirheader->nominal = $total;
            $gajisupirheader->total = $total;
            $gajisupirheader->save();

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
      

            DB::commit();

             /* Set position and page */
            $selected = $this->getPosition($gajisupirheader, $gajisupirheader->getTable());
            $gajisupirheader->position = $selected->position;
            $gajisupirheader->page = ceil($gajisupirheader->position / ($request->limit ?? 10));

            
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $gajisupirheader
            ]);
        }catch (\Throwable $th){
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(GajiSupirHeader $gajisupirheader, Request $request)
    {
        DB::beginTransaction();
        try {
            
            $delete = GajiSupirDetail::where('gajisupir_id',$gajisupirheader->id)->lockForUpdate()->delete();
            $delete = GajiSupirHeader::destroy($gajisupirheader->id);
            
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($gajisupirheader->getTable()),
                    'postingdari' => 'DELETE GAJI SUPIR HEADER',
                    'idtrans' => $gajisupirheader->id,
                    'nobuktitrans' => $gajisupirheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $gajisupirheader->toArray(),
                    'modifiedby' => $gajisupirheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($gajisupirheader, $gajisupirheader->getTable(), true);
                $gajisupirheader->position = $selected->position;
                $gajisupirheader->id = $selected->id;
                $gajisupirheader->page = ceil($gajisupirheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $gajisupirheader
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

    public function getTrip($supir_id,$dari,$sampai) {
        $gajisupir = new GajiSupirHeader();
        $tglDari = date('Y-m-d', strtotime($dari));
        $tglSampai = date('Y-m-d', strtotime($sampai));


        $cekSP = DB::table('suratpengantar')
            ->where('tglbukti','>=',$tglDari)
            ->where('tglbukti','<=',$tglSampai)
            ->where('supir_id',$supir_id)->first();

        if($cekSP){
            $nobukti = $cekSP->nobukti;
            $cekTrip = DB::table('gajisupirdetail')->where('suratpengantar_nobukti',$nobukti)->first();
            
            if($cekTrip) {
                $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SPSD')
                ->first();
                $data = [
                    'message' => $query->keterangan,
                    'errors' => true
                ];
    
                return response($data);
                
            }else{
                return response([
                    'errors' => false,
                    'data' => $gajisupir->getTrip($supir_id,$tglDari,$tglSampai),
                    'attributes' => [
                        'totalRows' => $gajisupir->totalRows,
                        'totalPages' => $gajisupir->totalPages
                    ]
                ]);
            }
        }else{
            $query = DB::table('error')
            ->select('keterangan')
            ->where('kodeerror', '=', 'NT')
            ->first();
            $data = [
                'message' => $query->keterangan,
                'errors' => true
            ];

            return response($data);
            
        }
        
    }

    public function getEditTrip($gajiId) {
        $gajisupir = new GajiSupirHeader();

        return response([
            'data' => $gajisupir->getEditTrip($gajiId)
        ]);
    }

    public function noEdit() {
        $query = DB::table('error')
        ->select('keterangan')
        ->where('kodeerror', '=', 'RICX')
        ->first();
        $data = [
            'message' => $query->keterangan,
            'errors' => 'noEdit'
        ];

        return response($data);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('gajisupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

}
