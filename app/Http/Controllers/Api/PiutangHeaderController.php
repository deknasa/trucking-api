<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PiutangHeader;
use App\Http\Requests\StorePiutangHeaderRequest;
use App\Http\Requests\UpdatePiutangHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;


use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Models\InvoiceHeader;
use App\Models\PiutangDetail;

use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;

class PiutangHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $piutang = new PiutangHeader();
        return response([
            'data' => $piutang->get(),
            'attributes' => [
                'totalRows' => $piutang->totalRows,
                'totalPages' => $piutang->totalPages
            ]
        ]);
    }

     /**
     * @ClassName
     */
    public function store(StorePiutangHeaderRequest $request)
    {
        

        DB::beginTransaction();

        
        try {

            $group = 'PIUTANG BUKTI';
            $subgroup = 'PIUTANG BUKTI';


            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();
            
            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'piutangheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $piutang = new PiutangHeader();
           
            $piutang->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $piutang->keterangan = $request->keterangan;
            $piutang->postingdari = $request->postingdari ?? '';
            $piutang->invoice_nobukti = $request->invoice_nobukti ?? '';
            $piutang->modifiedby = auth('api')->user()->name;
            $piutang->statusformat = $format->id;
            
        //    SUM NOMINAL
            $sum = 0;
            for ($i = 0; $i < count($request->nominal_detail); $i++) {
                $nominal_detail = str_replace('.00','', $request->nominal_detail[$i]);
                $nominal_detail = str_replace(',','',$nominal_detail);

                $sum += $nominal_detail;
            }

            $piutang->nominal = $sum;

            
            TOP:
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $piutang->nobukti = $nobukti;
            

            try {
                $piutang->save();   
            } catch (\Exception $e) {
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

            
            $logTrail = [
                'namatabel' => strtoupper($piutang->getTable()),
                'postingdari' => 'ENTRY PIUTANG HEADER',
                'idtrans' => $piutang->id,
                'nobuktitrans' => $piutang->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $piutang->toArray(),
                'modifiedby' => $piutang->modifiedby
            ];
            
            
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            /* Store detail */
                      
            $detaillog = [];
            for ($i = 0; $i < count($request->nominal_detail); $i++) {
            
                $nominal = str_replace('.00','', $request->nominal_detail[$i]);

                $datadetail = [
                    'piutang_id' => $piutang->id,
                    'nobukti' => $piutang->nobukti,
                    'nominal' => str_replace(',', '',$nominal),
                    'keterangan' => $request->keterangan_detail[$i],
                    'invoice_nobukti' => $request->invoice_nobukti ?? '',
                    'modifiedby' => $piutang->modifiedby,
                ];
                
                //STORE 
                $data = new StorePiutangDetailRequest($datadetail);
                
                $datadetails = app(PiutangDetailController::class)->store($data);
                
                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }
                
                
                $datadetaillog = [
                    'id' => $iddetail,
                    'piutang_id' => $piutang->id,
                    'nobukti' => $piutang->nobukti,
                    'nominal' => str_replace(',', '',$nominal),
                    'keterangan' => $request->keterangan_detail[$i],
                    'invoice_nobukti' => '',
                    'modifiedby' => $piutang->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($piutang->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($piutang->updated_at)),
                    
                ];
                             
                
                $detaillog[] = $datadetaillog;

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY PIUTANG DETAIL',
                    'idtrans' =>  $iddetail,
                    'nobuktitrans' => $piutang->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $piutang->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
            
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

           
            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');
            
            $coapiutang = DB::table('parameter')
            ->where('grp', 'COA PIUTANG MANUAL')->get();

            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $piutang->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'keterangan' => $request->keterangan,
                'postingdari' => "ENTRY PIUTANG",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
            ];

            $jurnaldetail = [];
            
            for ($i = 0; $i < count($request->nominal_detail); $i++) {
                $detail = [];
                
                foreach($coapiutang as $key => $coa){
                    $a = 0;
                    $getcoa = DB::table('akunpusat')
                    ->where('id', $coa->text)->first();
                    
                    $nominal = str_replace('.','', $request->nominal_detail[$i]);
                    
                    $jurnalDetail = [ 
                        [
                        'nobukti' => $piutang->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($piutang->tglbukti)),
                        'coa' =>  $getcoa->coa,
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                        ]
                    ];
                    if($coa->subgrp == 'DEBET'){
                        $jurnalDetail[$a]['nominal'] = str_replace(',', '',$nominal);
                    }else{
                        $jurnalDetail[$a]['nominal'] = '-'.str_replace(',', '',$nominal);
                    }
                
                    $detail = array_merge($detail, $jurnalDetail);
                    $a++;
                }
                    $jurnaldetail = array_merge($jurnaldetail, $detail);
            }

            $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);
        //    dd($jurnal['det']);
           
            
            if (!$jurnal['status']) {
                throw new \Throwable($jurnal['message']);
            }
            
            
            
            DB::commit();
        
        /* Set position and page */
    

            $piutang->position = DB::table((new PiutangHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $piutang->{$request->sortname})
                ->where('id', '<=', $piutang->id)
                ->count();

            if (isset($request->limit)) {
                $piutang->page = ceil($piutang->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $piutang 
            ]);
            
            
            
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }        
    }

    
    public function show($id)
    {
        $data = PiutangHeader::with(
            'piutangdetail',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * @ClassName
     */
    public function update(StorePiutangHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {

            $piutang = PiutangHeader::findOrFail($id);
            
            $piutang->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $piutang->keterangan = $request->keterangan;
            $piutang->postingdari = $request->postingdari ?? '';
            $piutang->invoice_nobukti = $request->invoice_nobukti ?? '';
            $piutang->modifiedby = auth('api')->user()->name;

            $sum = 0;
            for($i=0; $i < count($request->nominal_detail); $i++){
                $nominal = str_replace('.00','',$request->nominal_detail[$i]);
                $nominal = str_replace(',','',$nominal);
                $sum += $nominal;
            }
            $piutang->nominal = $sum;

            if($piutang->save()){
                $logTrail = [
                    'namatabel' => strtoupper($piutang->getTable()),
                    'postingdari' => 'EDIT PIUTANG HEADER',
                    'idtrans' => $id,
                    'nobuktitrans' => $piutang->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $piutang->toArray(),
                    'modifiedby' => $piutang->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                PiutangDetail::where('piutang_id', $id)->delete();

                /* Store detail */
                
                $detaillog = [];
                for($i = 0; $i < count($request->nominal_detail); $i++){
                     $nominal = str_replace('.00','', $request->nominal_detail[$i]);

                    $datadetail = [
                        'piutang_id' => $id,
                        'nobukti' => $piutang->nobukti,
                        'nominal' => str_replace(',','',$nominal),
                        'keterangan' => $request->keterangan_detail[$i],
                        'invoice_nobukti' => $request->invoice_nobukti ?? '',
                        'modifiedby' => $piutang->modifiedby,
                    ];

                    //STORE
                    
                    $data = new StorePiutangDetailRequest($datadetail);
                    $datadetails = app(PiutangDetailController::class)->store($data);
                    
                    if($datadetails['error']){
                        return response($datadetails, 422);
                    }else{
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    
                    $datadetaillog = [
                        'id' => $iddetail,
                        'piutang_id' => $piutang->$id,
                        'nobukti' => $piutang->nobukti,
                        'nominal' => str_replace(',','',$request->nominal_detail[$i]),
                        'keterangan' => $request->keterangan_detail[$i],
                        'invoice_nobukti' => $request->invoice_nobukti ?? '',
                        'modifiedby' => $piutang->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($piutang->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($piutang->updated_at)),
                        
                    ];

                    $detaillog[] = $datadetaillog;

                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT PIUTANG DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $piutang->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $detaillog,
                        'modifiedby' => $request->modifiedby,
                    ];
                    
                    $data = new StoreLogTrailRequest($datalogtrail);
                    
                    app(LogTrailController::class)->store($data);
                }
                
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            JurnalUmumHeader::where('nobukti', $piutang->nobukti)->delete();
            JurnalUmumDetail::where('nobukti', $piutang->nobukti)->delete();

            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');
            
            $coapiutang = DB::table('parameter')
            ->where('grp', 'COA PIUTANG MANUAL')->get();

            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $piutang->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'keterangan' => $request->keterangan,
                'postingdari' => "ENTRY PIUTANG",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
            ];

            $jurnaldetail = [];
            
            for ($i = 0; $i < count($request->nominal_detail); $i++) {
                $detail = [];
                
                foreach($coapiutang as $key => $coa){
                    $a = 0;
                    $getcoa = DB::table('akunpusat')
                    ->where('id', $coa->text)->first();

                    
                    $jurnalDetail = [ 
                        [
                        'nobukti' => $piutang->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($piutang->tglbukti)),
                        'coa' =>  $getcoa->coa,
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                        ]
                    ];
                    if($coa->subgrp == 'DEBET'){
                        $jurnalDetail[$a]['nominal'] = str_replace(',', '',$request->nominal_detail[$i]);
                    }else{
                        $jurnalDetail[$a]['nominal'] = '-'.str_replace(',', '',$request->nominal_detail[$i]);
                    }
                
                    $detail = array_merge($detail, $jurnalDetail);
                    $a++;
                }
                    $jurnaldetail = array_merge($jurnaldetail, $detail);
            }
            
            $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);

            if (!$jurnal['status']) {
                throw new \Throwable($jurnal['message']);
            }

            DB::commit();

             /* Set position and page */
             $piutang->position = DB::table((new PiutangHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
             ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $piutang->{$request->sortname})
             ->where('id', '<=', $piutang->id)
             ->count();

            if (isset($request->limit)) {
                $piutang->page = ceil($piutang->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $piutang
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
        $piutang = new PiutangHeader();
        $piutangs = PiutangHeader::findOrFail($id);

        $nobukti = $piutangs->nobukti;

        try{
            $delete = PiutangDetail::where('piutang_id',$id)->delete();
            $delete = PiutangHeader::destroy($id);

            JurnalUmumHeader::where('nobukti', $piutangs->nobukti)->delete();
            JurnalUmumDetail::where('nobukti', $piutangs->nobukti)->delete();

            if($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($piutang->getTable()),
                    'postingdari' => 'DELETE PIUTANG HEADER',
                    'idtrans' => $id,
                    'nobuktitrans' => $nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $piutang->toArray(),
                    'modifiedby' => $piutang->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($piutang, $piutang->getTable(), true);
                $piutang->position = $selected->position;
                $piutang->id = $selected->id;
                $piutang->page = ceil($piutang->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $piutang
                ]);
            }
        } catch(\Throwable $th){
            DB::rollBack();
            return response($th->getMessage()); 
        }
    }

    private function storeJurnal($header, $detail)
    {
        DB::beginTransaction();
        
        try {
            
            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);
            $nobukti = $header['nobukti'];
            $fetchId = JurnalUmumHeader::select('id')
            ->where('nobukti','=',$nobukti)
            ->first();
            $id = $fetchId->id;
            $details = [];

            foreach ($detail as $value) {
                $value['jurnalumum_id'] = $id;
                $detail = new StoreJurnalUmumDetailRequest($value);
                 app(JurnalUmumDetailController::class)->store($detail);
                $details = $detail;
            }
            // die;
            DB::commit();
            return [
                'status' => true,
                'head' => $jurnals,
                'det' => $details,
            ];
           
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage()); 
        }
    }
    
}
