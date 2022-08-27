<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumDetail;

use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;


class JurnalUmumController extends Controller
{
     /**
     * @ClassName
     */
    public function index()
    {
        
        $jurnalumum = new JurnalUmumHeader();

        return response([
            'data' => $jurnalumum->get(),
            'attributes' => [
                'totalRows' => $jurnalumum->totalRows,
                'totalPages' => $jurnalumum->totalPages
            ]
        ]);
    }

     /**
     * @ClassName
     */
    public function store(StoreJurnalUmumHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $content = new Request();
            $content['group'] = 'JURNAL UMUM';
            $content['subgroup'] = 'JURNAL UMUM';
            $content['table'] = 'jurnalumumheader';

            
            $jurnalumum = new JurnalUmumHeader();

            
            $jurnalumum->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $jurnalumum->keterangan = $request->keterangan;
            $jurnalumum->postingdari = '';
            $jurnalumum->statusapproval = '4';
            $jurnalumum->userapproval = '';
            $jurnalumum->tglapproval = date('Y-m-d H:i:s', strtotime($request->tglapproval));
            
            $jurnalumum->modifiedby = auth('api')->user()->name;
            
            
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $jurnalumum->nobukti = $nobukti;
            
            try {
                $jurnalumum->save();
            } catch (\Exception $e) {
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

            
            $logTrail = [
                'namatabel' => strtoupper($jurnalumum->getTable()),
                'postingdari' => 'ENTRY JURNAL UMUM',
                'idtrans' => $jurnalumum->id,
                'nobuktitrans' => $jurnalumum->id,
                'aksi' => 'ENTRY',
                'datajson' => $jurnalumum->toArray(),
                'modifiedby' => $jurnalumum->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            
            /* Store detail */
           
            
            for ($i = 0; $i < count($request->nominal_detail); $i++) {
                $detaillog = [];
                for($x = 0; $x <= 1; $x++)
                {
                    if($x == 1)
                    {
                        $datadetail = [
                            'jurnalumum_id' => $jurnalumum->id,
                            'nobukti' => $jurnalumum->nobukti,
                            'tglbukti' => $jurnalumum->tglbukti,
                            'coa' => $request->coakredit_detail[$i],
                            'nominal' => '-'.str_replace(',', '',$request->nominal_detail[$i]),
                            'keterangan' => $request->keterangan_detail[$i],
                            'modifiedby' => $jurnalumum->modifiedby,
                        ];
                    }else{
                        $datadetail = [
                            'jurnalumum_id' => $jurnalumum->id,
                            'nobukti' => $jurnalumum->nobukti,
                            'tglbukti' => $jurnalumum->tglbukti,
                            'coa' => $request->coadebet_detail[$i],
                            'nominal' => str_replace(',', '',$request->nominal_detail[$i]),
                            'keterangan' => $request->keterangan_detail[$i],
                            'modifiedby' => $jurnalumum->modifiedby,
                        ];
                    }

                    
                    //STORE 
                    $data = new StoreJurnalUmumDetailRequest($datadetail);
                    
                    $datadetails = app(JurnalUmumDetailController::class)->store($data);
                    // dd('tes');
                    
                    
                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    
                   if($x==1)
                   {
                    $datadetaillog = [
                        'id' => $iddetail,
                        'jurnalumum_id' => $jurnalumum->id,
                        'nobukti' => $jurnalumum->nobukti,
                        'tglbukti' => $jurnalumum->tglbukti,
                        'coa' => $request->coakredit_detail[$i],
                        'nominal' => $request->nominal_detail[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => $jurnalumum->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->updated_at)),
                    ];
                   }else{
                    $datadetaillog = [
                        'id' => $iddetail,
                        'jurnalumum_id' => $jurnalumum->id,
                        'nobukti' => $jurnalumum->nobukti,
                        'tglbukti' => $jurnalumum->tglbukti,
                        'coa' => $request->coadebet_detail[$i],
                        'nominal' => $request->nominal_detail[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => $jurnalumum->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->updated_at)),
                    ];
                   }
                    
                    
                    
                    $detaillog[] = $datadetaillog;

                    
                    $dataid = LogTrail::select('id')
                    ->where('idtrans', '=', $jurnalumum->id)
                    ->where('namatabel', '=', $jurnalumum->getTable())
                    ->orderBy('id', 'DESC')
                    ->first();

                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY JURNAL UMUM',
                        'idtrans' =>  $dataid->id,
                        'nobuktitrans' => $jurnalumum->id,
                        'aksi' => 'ENTRY',
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
            $jurnalumum->position = DB::table((new JurnalUmumHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $jurnalumum->{$request->sortname})
                ->where('id', '<=', $jurnalumum->id)
                ->count();

            if (isset($request->limit)) {
                $jurnalumum->page = ceil($jurnalumum->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jurnalumum
            ]);
            
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
            // return response($th->getMessage());
        }        
    }

    /**
     * @ClassName
     */
    public function show($id)
    {
        $data = JurnalUmumHeader::with(
            'jurnalumumdetail',
        )->find($id);
        
       
        return response([
            'status' => true,
            'data' => $data
        ]);
    }
   
    public function getNominal($id)
    {

    }
     /**
     * @ClassName
     */
    public function update(StoreJurnalUmumHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $jurnalumum = JurnalUmumHeader::findOrFail($id);
            $jurnalumum->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $jurnalumum->keterangan = $request->keterangan;
            $jurnalumum->modifiedby = auth('api')->user()->name;

            if ($jurnalumum->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jurnalumum->getTable()),
                    'postingdari' => 'ENTRY JURNAL UMUM',
                    'idtrans' => $jurnalumum->id,
                    'nobuktitrans' => $jurnalumum->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $jurnalumum->toArray(),
                    'modifiedby' => $jurnalumum->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                $jurnalumum->jurnalumumdetail()->delete();


                /* Store detail */
                

                for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $detaillog = [];
                    for($x = 0; $x <= 1; $x++)
                    {
                        if($x == 1)
                        {
                            $datadetail = [
                                'jurnalumum_id' => $jurnalumum->id,
                                'nobukti' => $jurnalumum->nobukti,
                                'tglbukti' => $jurnalumum->tglbukti,
                                'coa' => '-'.$request->coakredit_detail[$i],
                                'nominal' => $request->nominal_detail[$i],
                                'keterangan' => $request->keterangan_detail[$i],
                                'modifiedby' => $jurnalumum->modifiedby,
                            ];
                        }else{
                            $datadetail = [
                                'jurnalumum_id' => $jurnalumum->id,
                                'nobukti' => $jurnalumum->nobukti,
                                'tglbukti' => $jurnalumum->tglbukti,
                                'coa' => $request->coadebet_detail[$i],
                                'nominal' => $request->nominal_detail[$i],
                                'keterangan' => $request->keterangan_detail[$i],
                                'modifiedby' => $jurnalumum->modifiedby,
                            ];
                        }
    
                        //STORE 
                        $data = new StoreJurnalUmumDetailRequest($datadetail);
                        $datadetails = app(JurnalUmumDetailController::class)->store($data);
    
                        
                        if ($datadetails['error']) {
                            return response($datadetails, 422);
                        } else {
                            $iddetail = $datadetails['id'];
                            $tabeldetail = $datadetails['tabel'];
                        }
                        
                       
                        
                        $datadetaillog = [
                            'id' => $iddetail,
                            'jurnalumum_id' => $jurnalumum->id,
                            'nobukti' => $jurnalumum->nobukti,
                            'tglbukti' => $jurnalumum->tglbukti,
                            'coa' => $request->coadebet_detail[$i],
                            'nominal' => $request->nominal_detail[$i],
                            'keterangan' => $request->keterangan_detail[$i],
                            'modifiedby' => $jurnalumum->modifiedby,
                            'created_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->created_at)),
                            'updated_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->updated_at)),
                        ];
                        
                        $detaillog[] = $datadetaillog;
    
                        
                        $dataid = LogTrail::select('id')
                        ->where('idtrans', '=', $jurnalumum->id)
                        ->where('namatabel', '=', $jurnalumum->getTable())
                        ->orderBy('id', 'DESC')
                        ->first();
    
                        $datalogtrail = [
                            'namatabel' => $tabeldetail,
                            'postingdari' => 'ENTRY JURNAL UMUM',
                            'idtrans' =>  $dataid->id,
                            'nobuktitrans' => $jurnalumum->id,
                            'aksi' => 'ENTRY',
                            'datajson' => $detaillog,
                            'modifiedby' => $request->modifiedby,
                        ];
    
                        $data = new StoreLogTrailRequest($datalogtrail);
                        app(LogTrailController::class)->store($data);
                        
                        $dataid = LogTrail::select('id')
                        ->where('idtrans', '=', $jurnalumum->id)
                        ->where('namatabel', '=', $jurnalumum->getTable())
                        ->orderBy('id', 'DESC')
                        ->first();

                        $datalogtrail = [
                            'namatabel' => $tabeldetail,
                            'postingdari' => 'EDIT JURNAL UMUM',
                            'idtrans' =>  $dataid->id,
                            'nobuktitrans' => $jurnalumum->id,
                            'aksi' => 'EDIT',
                            'datajson' => $detaillog,
                            'modifiedby' => $request->modifiedby,
                        ];

                        $data = new StoreLogTrailRequest($datalogtrail);
                        app(LogTrailController::class)->store($data);

                    }
                }

            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();


            /* Set position and page */
            $jurnalumum->position = DB::table((new JurnalUmumHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $jurnalumum->{$request->sortname})
                ->where('id', '<=', $jurnalumum->id)
                ->count();

            if (isset($request->limit)) {
                $jurnalumum->page = ceil($jurnalumum->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jurnalumum
            ]);
        } catch (\Throwable $th) {
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
        $jurnalumum = new JurnalUmumHeader();
        try {
            
            
            $delete = JurnalUmumDetail::where('jurnalumum_id',$id)->delete();
            $delete = JurnalUmumHeader::destroy($id);
            // $delete = $jurnalumum->delete($id);
            
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($jurnalumum->getTable()),
                    'postingdari' => 'DELETE JURNAL UMUM',
                    'idtrans' => $id,
                    'nobuktitrans' => '',
                    'aksi' => 'DELETE',
                    'datajson' => $jurnalumum->toArray(),
                    'modifiedby' => $jurnalumum->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($jurnalumum, $jurnalumum->getTable(), true);
                $jurnalumum->position = $selected->position;
                $jurnalumum->id = $selected->id;
                $jurnalumum->page = ceil($jurnalumum->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $jurnalumum
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
}
