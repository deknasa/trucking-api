<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProsesGajiSupirDetailController;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreProsesGajiSupirDetailRequest;
use App\Models\ProsesGajiSupirHeader;
use App\Http\Requests\StoreProsesGajiSupirHeaderRequest;
use App\Http\Requests\UpdateProsesGajiSupirHeaderRequest;

use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\ProsesGajiSupirDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProsesGajiSupirHeaderController extends Controller
{
     /**
     * @ClassName 
     */
    public function index()
    {
        $prosesGajiSupirHeader = new ProsesGajiSupirHeader();
        return response([
            'data' => $prosesGajiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $prosesGajiSupirHeader->totalRows,
                'totalPages' => $prosesGajiSupirHeader->totalPages
            ]
        ]);
    }

     /**
     * @ClassName 
     */
    public function store(StoreProsesGajiSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'RINCIAN GAJI SUPIR BUKTI';
            $subgroup = 'RINCIAN GAJI SUPIR BUKTI';


            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group ;
            $content['subgroup'] = $subgroup ;
            $content['table'] = 'prosesgajisupirheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $prosesgajisupirheader = new ProsesGajiSupirHeader();
            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            $prosesgajisupirheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $prosesgajisupirheader->keterangan = $request->keterangan;
            $prosesgajisupirheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $prosesgajisupirheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $prosesgajisupirheader->statusapproval = $statusApproval->id ?? $request->statusapproval;;
            $prosesgajisupirheader->userapproval = '';
            $prosesgajisupirheader->tglapproval = '';
            $prosesgajisupirheader->periode = date('Y-m-d', strtotime($request->periode));
            $prosesgajisupirheader->statusformat = $format->id;
            $prosesgajisupirheader->modifiedby = auth('api')->user()->name;
            
            TOP:
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $prosesgajisupirheader->nobukti = $nobukti;
    

            try {
                $prosesgajisupirheader->save();
                DB::commit();
            } catch (\Exception $e) {
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

            $logTrail = [
                'namatabel' => strtoupper($prosesgajisupirheader->getTable()),
                'postingdari' => 'ENTRY PROSES GAJI SUPIR HEADER',
                'idtrans' => $prosesgajisupirheader->id,
                'nobuktitrans' => $prosesgajisupirheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $prosesgajisupirheader->toArray(),
                'modifiedby' => $prosesgajisupirheader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            
            /* Store detail */
                      
            $detaillog = [];
            
            $urut = 1;
            for ($i = 0; $i < count($request->ric_id); $i++) {
               
                $ric = DB::table('gajisupirheader')->where('id',$request->ric_id[$i])->first();
                $sp = DB::table('suratpengantar')->where('supir_id', $ric->supir_id)->first();
                $datadetail = [
                    'prosesgajisupir_id' => $prosesgajisupirheader->id,
                    'nobukti' => $prosesgajisupirheader->nobukti,
                    'gajisupir_nobukti' => $ric->nobukti,
                    'supir_id' => $ric->supir_id,
                    'trado_id' => $sp->trado_id,
                    'nominal' => $ric->nominal,
                    'keterangan' => $ric->keterangan,
                    'modifiedby' => $prosesgajisupirheader->modifiedby,
                ];

                //STORE 
                $data = new StoreProsesGajiSupirDetailRequest($datadetail);
                
                $datadetails = app(ProsesGajiSupirDetailController::class)->store($data);
                
                
                
                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }
                
                
                $datadetaillog = [
                    'id' => $iddetail,
                    'prosesgajisupir_id' => $prosesgajisupirheader->id,
                    'nobukti' => $prosesgajisupirheader->nobukti,
                    'gajisupir_nobukti' => $ric->nobukti,
                    'supir_id' => $ric->supir_id,
                    'trado_id' => $sp->trado_id,
                    'nominal' => $ric->nominal,
                    'keterangan' => $ric->keterangan,
                    'modifiedby' => $prosesgajisupirheader->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($prosesgajisupirheader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($prosesgajisupirheader->updated_at)),
                    
                ];
                
                $detaillog[] = $datadetaillog;

                
                $dataid = LogTrail::select('id')
                ->where('idtrans', '=', $prosesgajisupirheader->id)
                ->where('namatabel', '=', $prosesgajisupirheader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY PROSES GAJI SUPIR DETAIL',
                    'idtrans' =>  $dataid->id,
                    'nobuktitrans' => $prosesgajisupirheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
                
                $urut++;
            }
             
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();
            
            /* Set position and page */
        

            $selected = $this->getPosition($prosesgajisupirheader, $prosesgajisupirheader->getTable());
            $prosesgajisupirheader->position = $selected->position;
            $prosesgajisupirheader->page = ceil($prosesgajisupirheader->position / ($request->limit ?? 10));
            

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $prosesgajisupirheader 
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        } 
    }

    public function show(ProsesGajiSupirHeader $prosesGajiSupirHeader)
    {
        return response([
            'status' => true,
            'data' => $prosesGajiSupirHeader
        ]);
    }


     /**
     * @ClassName 
     */
    public function update(UpdateProsesGajiSupirHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {

            $prosesgajisupirheader = ProsesGajiSupirHeader::findOrFail($id);
            
            $prosesgajisupirheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $prosesgajisupirheader->keterangan = $request->keterangan;
            $prosesgajisupirheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $prosesgajisupirheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $prosesgajisupirheader->periode = date('Y-m-d', strtotime($request->periode));
            $prosesgajisupirheader->modifiedby = auth('api')->user()->name;

            
            if($prosesgajisupirheader->save()){
                $logTrail = [
                    'namatabel' => strtoupper($prosesgajisupirheader->getTable()),
                    'postingdari' => 'EDIT PROSES GAJI SUPIR HEADER',
                    'idtrans' => $id,
                    'nobuktitrans' => $prosesgajisupirheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $prosesgajisupirheader->toArray(),
                    'modifiedby' => $prosesgajisupirheader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                ProsesGajiSupirDetail::where('prosesgajisupir_id', $id)->delete();

                /* Store detail */
                
                
                $detaillog = [];
                $urut = 1;

                for($i = 0; $i < count($request->ric_id); $i++){
                    $ric = DB::table('gajisupirheader')->where('id',$request->ric_id[$i])->first();
                    $sp = DB::table('suratpengantar')->where('supir_id', $ric->supir_id)->first();
                    $datadetail = [
                        'prosesgajisupir_id' => $prosesgajisupirheader->id,
                        'nobukti' => $prosesgajisupirheader->nobukti,
                        'gajisupir_nobukti' => $ric->nobukti,
                        'supir_id' => $ric->supir_id,
                        'trado_id' => $sp->trado_id,
                        'nominal' => $ric->nominal,
                        'keterangan' => $ric->keterangan,
                        'modifiedby' => $prosesgajisupirheader->modifiedby,
                    ];

                    //STORE
                    
                    $data = new StoreProsesGajiSupirDetailRequest($datadetail);
                    $datadetails = app(ProsesGajiSupirDetailController::class)->store($data);
                    
                    if($datadetails['error']){
                        return response($datadetails, 422);
                    }else{
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    
                    $datadetaillog = [
                        'id' => $iddetail,
                        'prosesgajisupir_id' => $prosesgajisupirheader->id,
                        'nobukti' => $prosesgajisupirheader->nobukti,
                        'gajisupir_nobukti' => $ric->nobukti,
                        'supir_id' => $ric->supir_id,
                        'trado_id' => $sp->trado_id,
                        'nominal' => $ric->nominal,
                        'keterangan' => $ric->keterangan,
                        'modifiedby' => $prosesgajisupirheader->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($prosesgajisupirheader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($prosesgajisupirheader->updated_at)),
                        
                    ];

                    $detaillog[] = $datadetaillog;

                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT PROSES GAJI SUPIR DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $prosesgajisupirheader->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $detaillog,
                        'modifiedby' => $request->modifiedby,
                    ];
                    
                    $data = new StoreLogTrailRequest($datalogtrail);
                    
                    app(LogTrailController::class)->store($data);
                    $urut++;
                }
                
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
      

            DB::commit();

             /* Set position and page */
            $selected = $this->getPosition($prosesgajisupirheader, $prosesgajisupirheader->getTable());
            $prosesgajisupirheader->position = $selected->position;
            $prosesgajisupirheader->page = ceil($prosesgajisupirheader->position / ($request->limit ?? 10));

            
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $prosesgajisupirheader
            ]);
        }catch (\Throwable $th){
            DB::rollBack();
            return response($th->getMessage());
        }
    }

     /**
     * @ClassName 
     */
    public function destroy($id, Request $request)
    {
        DB::beginTransaction();
        $prosesgajisupirheader = new ProsesGajiSupirHeader();
        try {
            
            $delete = ProsesGajiSupirDetail::where('prosesgajisupir_id',$id)->delete();
            $delete = ProsesGajiSupirHeader::destroy($id);
            
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($prosesgajisupirheader->getTable()),
                    'postingdari' => 'DELETE PROSES GAJI SUPIR HEADER',
                    'idtrans' => $id,
                    'nobuktitrans' => '',
                    'aksi' => 'DELETE',
                    'datajson' => $prosesgajisupirheader->toArray(),
                    'modifiedby' => $prosesgajisupirheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($prosesgajisupirheader, $prosesgajisupirheader->getTable(), true);
                $prosesgajisupirheader->position = $selected->position;
                $prosesgajisupirheader->id = $selected->id;
                $prosesgajisupirheader->page = ceil($prosesgajisupirheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $prosesgajisupirheader
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

    public function getRic($dari, $sampai) {
        $prosesgajisupir = new ProsesGajiSupirHeader();
        $dari = date('Y-m-d', strtotime($dari));
        $sampai = date('Y-m-d', strtotime($sampai));

        return response([
            'errors' => false,
            'data' => $prosesgajisupir->getRic($dari, $sampai)
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('prosesgajisupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

}
