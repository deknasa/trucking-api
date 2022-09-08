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

            $group = 'PIUTANG';
            $subgroup = 'PIUTANG';


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
            $piutang->postingdari = '';
            $piutang->nominal = str_replace(',','',$request->nominal);
            $piutang->invoice_nobukti = '';
            $piutang->modifiedby = auth('api')->user()->name;
            $piutang->statusformat = $format->id;
            
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
                $datadetail = [
                    'piutang_id' => $piutang->id,
                    'nobukti' => $piutang->nobukti,
                    'nominal' => str_replace(',', '',$request->nominal_detail[$i]),
                    'keterangan' => $request->keterangan_detail[$i],
                    'invoice_nobukti' => '',
                    'modifiedby' => $piutang->modifiedby,
                ];

                //STORE 
                $data = new StorePiutangDetailRequest($datadetail);
                
                $datadetails = app(PiutangDetailController::class)->store($data);
                // dd('tes');
                
                
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
                    'nominal' => str_replace(',', '',$request->nominal_detail[$i]),
                    'keterangan' => $request->keterangan_detail[$i],
                    'invoice_nobukti' => '',
                    'modifiedby' => $piutang->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($piutang->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($piutang->updated_at)),
                    
                ];
                
                
                
                $detaillog[] = $datadetaillog;

                $dataid = LogTrail::select('id')
                ->where('idtrans', '=', $piutang->id)
                ->where('namatabel', '=', $piutang->getTable())
                ->orderBy('id', 'DESC')
                ->first();
                    
                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY PIUTANG DETAIL',
                    'idtrans' =>  $dataid->id,
                    'nobuktitrans' => $piutang->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
                
            }
     
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            // if ($piutang->save() && $piutang->piutangdetail) {
                $parameterController = new ParameterController;
                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');
                
                $coadebet = DB::table('parameter')
                ->where('grp', 'JURNAL UMUM PIUTANG')
                ->first();

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
                ];

                $jurnaldetail = [];
                for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $jurnalDetail = [
                            'nobukti' => $piutang->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' =>  $coadebet->text,
                            'nominal' => str_replace(',', '',$request->nominal_detail[$i]),
                            'keterangan' => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                    ];
                    $jurnaldetail = array_merge($jurnaldetail, $jurnalDetail);
                }
                
                $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);

                

                // if (!$jurnal['status'] AND @$jurnal['errorCode'] == 2601) {
                //     goto ATAS;
                // }

                if (!$jurnal['status']) {
                    throw new \Throwable($jurnal['message']);
                }
                // dd('here');
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
    public function update(UpdatePiutangHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $piutang = PiutangHeader::findOrFail($id);

            $piutang->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $piutang->keterangan = $request->keterangan;
            $piutang->postingdari = '';
            $piutang->nominal = str_replace(',','',$request->nominal);
            $piutang->invoice_nobukti = '';
            $piutang->modifiedby = auth('api')->user()->name;

            if($piutang->save()){
                $logTrail = [
                    'namatabel' => strtoupper($piutang->getTable()),
                    'postingdari' => 'EDIT PIUTANG HEADER',
                    'idtrans' => $piutang->id,
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
                    $datadetail = [
                        'piutang_id' => $piutang->$id,
                        'nobukti' => $piutang->nobukti,
                        'nominal' => str_replace(',','',$request->nominal_detail[$i]),
                        'keterangan' => $request->keterangan_detail[$i],
                        'invoice_nobukti' => '',
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
                        'keterangan' => $request->keterangan_deteail[$i],
                        'invoice_nobukti' => '',
                        'modifiedby' => $piutang->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($piutang->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($piutang->updated_at)),
                        
                    ];

                    $detaillog[] = $datadetaillog;

                    $piutangdetail = PiutangDetail::findOrFail($id);
                    $dataid = LogTrail::select('id')
                    ->where('idtrans', '=', $piutangdetail->id)
                    ->where('namatabel', '=', $piutangdetail->getTable())
                    ->orderBy('id', 'DESC')
                    ->first();

                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT PIUTANG DETAIL',
                        'idtrans' =>  $dataid->id,
                        'nobuktitrans' => $piutangdetail->nobukti,
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
        try{
            $delete = PiutangDetail::where('piutang_id',$id)->delete();
            $delete = PiutangHeader::destroy($id);

            if($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($piutang->getTable()),
                    'postingdari' => 'DELETE PIUTANG HEADER',
                    'idtrans' => $id,
                    'nobuktitrans' => $piutang->nobukti,
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
        try {
            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);
            $nobukti = $header['nobukti'];
            $fetchId = JurnalUmumHeader::select('id')
            ->where('nobukti','=',$nobukti)
            ->first();

            $id = $fetchId->id;

            foreach ($detail as $key => $value) {
                $value['jurnalumum_id'] = $id;
            
                $detail = new StoreJurnalUmumDetailRequest($value);
                app(JurnalUmumDetailController::class)->store($detail);
            }

            
            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
}
