<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranTruckingHeader;
use App\Http\Requests\StorePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranTruckingDetailRequest;
use App\Models\AkunPusat;
use App\Models\Parameter;
use App\Models\Bank;
use App\Models\Error;
use App\Models\LogTrail;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranTrucking;
use App\Models\PengeluaranTruckingDetail;
use App\Models\Supir;


class PengeluaranTruckingHeaderController extends Controller
{
     
    /**
     * @ClassName
     */
    public function index()
    {
        $pengeluarantruckingheader = new PengeluaranTruckingHeader();
        return response([
            'data' => $pengeluarantruckingheader->get(),
            'attributes' => [
                'totalRows' => $pengeluarantruckingheader->totalRows,
                'totalPages' => $pengeluarantruckingheader->totalPages
            ]
        ]);
    }

    
    /**
     * @ClassName
     */
    public function store(StorePengeluaranTruckingHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $idpengeluaran = $request->pengeluarantrucking_id;
            $formatBukti =  DB::table('pengeluarantrucking')
            ->where('id', $idpengeluaran)
            ->first();
            // echo $formatBukti->kodepengeluaran;
            // die();
            $kodepengeluaran = $formatBukti->kodepengeluaran;

            
            $group = 'PENGELUARAN TRUCKING HEADER';
            if($kodepengeluaran == 'PJT')
            {
                $subgroup = 'PINJAMAN SUPIR';
            }else{
                $subgroup = 'BIAYA LAIN SUPIR';
            }
           
            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();
    
            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'pengeluarantruckingheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $pengeluarantruckingheader = new PengeluaranTruckingHeader();
            $statusPosting = Parameter::where('grp', 'STATUS POSTING')->where('text', 'BUKAN POSTING')->first();

            $noBuktiPengeluaran = $request->pengeluaran_nobukti;
            $PengeluaranHeader =  DB::table('pengeluaranheader')
            ->where('nobukti', $noBuktiPengeluaran)
            ->first();

            $pengeluarantruckingheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluarantruckingheader->pengeluarantrucking_id = $idpengeluaran;
            $pengeluarantruckingheader->keterangan = $request->keterangan;
            $pengeluarantruckingheader->bank_id = $request->bank_id;
            $pengeluarantruckingheader->statusposting = $statusPosting->id ?? 0;
            $pengeluarantruckingheader->coa = $request->coa;
            $pengeluarantruckingheader->pengeluaran_nobukti = $noBuktiPengeluaran;
            $pengeluarantruckingheader->pengeluaran_tgl = $PengeluaranHeader->tglbukti;
            $pengeluarantruckingheader->proses_nobukti = '';
            $pengeluarantruckingheader->statusformat =  $format->id;
            $pengeluarantruckingheader->modifiedby = auth('api')->user()->name;
            
            TOP:
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $pengeluarantruckingheader->nobukti = $nobukti;
    

            try {

                $pengeluarantruckingheader->save();
                DB::commit();
            } catch (\Exception $e) {
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

        
            $logTrail = [
                'namatabel' => strtoupper($pengeluarantruckingheader->getTable()),
                'postingdari' => 'ENTRY PENGELUARAN TRUCKING HEADER',
                'idtrans' => $pengeluarantruckingheader->id,
                'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $pengeluarantruckingheader->toArray(),
                'modifiedby' => $pengeluarantruckingheader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            
            /* Store detail */
                      
            $detaillog = [];

            for ($i = 0; $i < count($request->nominal); $i++) {
                    
                $datadetail = [
                    'pengeluarantruckingheader_id' => $pengeluarantruckingheader->id,
                    'nobukti' => $pengeluarantruckingheader->nobukti,
                    'supir_id' => $request->supir_id[$i],
                    'penerimaantruckingheader_nobukti' => $request->penerimaantruckingheader_nobukti[$i],
                    'nominal' => str_replace(',', '',$request->nominal[$i]),
                    'modifiedby' => $pengeluarantruckingheader->modifiedby,
                ];

                //STORE 
                $data = new StorePengeluaranTruckingDetailRequest($datadetail);
                
                $datadetails = app(PengeluaranTruckingDetailController::class)->store($data);
                // dd('tes');
                
                
                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }
                
                
                $datadetaillog = [
                    'id' => $iddetail,
                    'pengeluarantruckingheader_id' => $pengeluarantruckingheader->id,
                    'nobukti' => $pengeluarantruckingheader->nobukti,
                    'supir_id' => $request->supir_id[$i],
                    'penerimaantruckingheader_nobukti' => $request->penerimaantruckingheader_nobukti[$i],
                    'nominal' => $request->nominal[$i],
                    'modifiedby' => $pengeluarantruckingheader->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($pengeluarantruckingheader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($pengeluarantruckingheader->updated_at)),
                    
                ];
                
                $detaillog[] = $datadetaillog;

                
                $dataid = LogTrail::select('id')
                ->where('idtrans', '=', $pengeluarantruckingheader->id)
                ->where('namatabel', '=', $pengeluarantruckingheader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY PENGELUARAN TRUCKING DETAIL',
                    'idtrans' =>  $dataid->id,
                    'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
                
            }
     
           
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();
            
            /* Set position and page */
        

            $selected = $this->getPosition($pengeluarantruckingheader, $pengeluarantruckingheader->getTable());
            $pengeluarantruckingheader->position = $selected->position;
            $pengeluarantruckingheader->page = ceil($pengeluarantruckingheader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $pengeluarantruckingheader->page = ceil($pengeluarantruckingheader->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengeluarantruckingheader 
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }        
    }

   
    public function show($id)
    {
        $data = PengeluaranTruckingHeader::with(
            'pengeluarantruckingdetail',
        )->find($id);
        
        $idpengeluaran = $data['pengeluarantrucking_id'];
        $formatBukti =  DB::table('pengeluarantrucking')
        ->where('id', $idpengeluaran)
        ->first();
        
        $kodepengeluaran = $formatBukti->kodepengeluaran;
        
            
        return response([
            'status' => true,
            'data' => $data,
            'kode' => $kodepengeluaran
        ]);
    }

     
    /**
     * @ClassName
     */
    public function update(StorePengeluaranTruckingHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $idpengeluaran = $request->pengeluarantrucking_id;
            $formatBukti =  DB::table('pengeluarantrucking')
            ->where('id', $idpengeluaran)
            ->first();
            $kodepengeluaran = $formatBukti->kodepengeluaran;

            if($kodepengeluaran == 'PJT')
            {
                $group = 'PINJAMAN SUPIR';
                $subgroup = 'PINJAMAN SUPIR';
            }else{
                $group = 'BIAYA LAIN SUPIR';
                $subgroup = 'BIAYA LAIN SUPIR';
            }
           
            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

           
            $noBuktiPengeluaran = $request->pengeluaran_nobukti;
            $PengeluaranHeader =  DB::table('pengeluaranheader')
            ->where('nobukti', $noBuktiPengeluaran)
            ->first();

            $pengeluarantruckingheader = PengeluaranTruckingHeader::findOrFail($id);

            $pengeluarantruckingheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluarantruckingheader->pengeluarantrucking_id = $idpengeluaran;
            $pengeluarantruckingheader->keterangan = $request->keterangan;
            $pengeluarantruckingheader->bank_id = $request->bank_id;
            $pengeluarantruckingheader->coa = $request->coa;
            $pengeluarantruckingheader->pengeluaran_nobukti = $noBuktiPengeluaran;
            $pengeluarantruckingheader->pengeluaran_tgl = $PengeluaranHeader->tglbukti;
            $pengeluarantruckingheader->proses_nobukti = '';
            $pengeluarantruckingheader->statusformat =  $format->id;
            $pengeluarantruckingheader->modifiedby = auth('api')->user()->name;
            
            
            if ($pengeluarantruckingheader->save()) {
            
                $logTrail = [
                    'namatabel' => strtoupper($pengeluarantruckingheader->getTable()),
                    'postingdari' => 'EDIT PENGELUARAN TRUCKING HEADER',
                    'idtrans' => $pengeluarantruckingheader->id,
                    'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $pengeluarantruckingheader->toArray(),
                    'modifiedby' => $pengeluarantruckingheader->modifiedby
                ];

                
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                
               PengeluaranTruckingDetail::where('pengeluarantruckingheader_id',$id)->delete();
               
                /* Store detail */
                
                $detaillog = [];

                for ($i = 0; $i < count($request->nominal); $i++) {
                    
                    
                    $datadetail = [
                        'pengeluarantruckingheader_id' => $pengeluarantruckingheader->id,
                        'nobukti' => $pengeluarantruckingheader->nobukti,
                        'supir_id' => $request->supir_id[$i],
                        'penerimaantruckingheader_nobukti' => $request->penerimaantruckingheader_nobukti[$i],
                        'nominal' => str_replace(',', '',$request->nominal[$i]),
                        'modifiedby' => $pengeluarantruckingheader->modifiedby,
                    ];
                    

                    //STORE 
                    $data = new StorePengeluaranTruckingDetailRequest($datadetail);
                    
                    $datadetails = app(PengeluaranTruckingDetailController::class)->store($data);
                    // dd('here');
                    
                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    
                   
                    $datadetaillog = [
                        'id' => $iddetail,
                        'pengeluarantruckingheader_id' => $pengeluarantruckingheader->id,
                        'nobukti' => $pengeluarantruckingheader->nobukti,
                        'supir_id' => $request->supir_id[$i],
                        'penerimaantruckingheader_nobukti' => $request->penerimaantruckingheader_nobukti[$i],
                        'nominal' => $request->nominal[$i],
                        'modifiedby' => $pengeluarantruckingheader->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($pengeluarantruckingheader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($pengeluarantruckingheader->updated_at)),
                        
                    ];
                    
                    $detaillog[] = $datadetaillog;

                    
                    
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT PENGELUARAN TRUCKING DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $pengeluarantruckingheader->nobukti,
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
            $selected = $this->getPosition($pengeluarantruckingheader, $pengeluarantruckingheader->getTable());
            $pengeluarantruckingheader->position = $selected->position;
            $pengeluarantruckingheader->page = ceil($pengeluarantruckingheader->position / ($request->limit ?? 10));


            // if (isset($request->limit)) {
            //     $pengeluarantruckingheader->page = ceil($pengeluarantruckingheader->position / $request->limit);
            // }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengeluarantruckingheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }

     
    /**
     * @ClassName
     */
    public function destroy($id, Request $request)
    {
        DB::beginTransaction();
        $pengeluarantruckingheader = new PengeluaranTruckingHeader();
        try {
            
            $delete = PengeluaranTruckingDetail::where('pengeluarantruckingheader_id',$id)->delete();
            $delete = PengeluaranTruckingHeader::destroy($id);
            
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluarantruckingheader->getTable()),
                    'postingdari' => 'DELETE PENGELUARAN TRUCKING HEADER',
                    'idtrans' => $id,
                    'nobuktitrans' => '',
                    'aksi' => 'DELETE',
                    'datajson' => $pengeluarantruckingheader->toArray(),
                    'modifiedby' => $pengeluarantruckingheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($pengeluarantruckingheader, $pengeluarantruckingheader->getTable(), true);
                $pengeluarantruckingheader->position = $selected->position;
                $pengeluarantruckingheader->id = $selected->id;
                $pengeluarantruckingheader->page = ceil($pengeluarantruckingheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $pengeluarantruckingheader
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

    public function combo(Request $request)
    {
        $data = [
            'pengeluarantrucking' => PengeluaranTrucking::all(),
            'pengeluaranheader' => PengeluaranHeader::all(),
            'bank' => Bank::all(),
            'coa' => AkunPusat::all(),
            'penerimaantruckingheader' => PenerimaanTruckingHeader::all(),
            'supir' => Supir::select('id','namasupir')->get()
        ];

        return response([
            'data' => $data
        ]);
    }

   
}

