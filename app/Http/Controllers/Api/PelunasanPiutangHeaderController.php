<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PelunasanPiutangHeader;
use App\Models\PelunasanPiutangDetail;

use App\Http\Requests\StorePelunasanPiutangHeaderRequest;
use App\Http\Requests\UpdatePelunasanPiutangHeaderRequest;
use App\Http\Requests\StorePelunasanPiutangDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\LogTrail;
use App\Models\Agen;
use App\Models\Cabang;
use App\Models\Bank;
use App\Models\Pelanggan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class PelunasanPiutangHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $pengeluarantruckingheader = new PelunasanPiutangHeader();
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
    public function store(StorePelunasanPiutangHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'PELUNASAN PIUTANG BUKTI';
            $subgroup = 'PELUNASAN PIUTANG BUKTI';


            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group ;
            $content['subgroup'] = $subgroup ;
            $content['table'] = 'pelunasanpiutangheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $pelunasanpiutangheader = new PelunasanPiutangHeader();
          
            $pelunasanpiutangheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pelunasanpiutangheader->keterangan = $request->keterangan;
            $pelunasanpiutangheader->bank_id = $request->bank;
            $pelunasanpiutangheader->agen_id = $request->agen;
            $pelunasanpiutangheader->cabang_id = $request->cabang;
            $pelunasanpiutangheader->statusformat = $format->id;
            $pelunasanpiutangheader->modifiedby = auth('api')->user()->name;
            
            TOP:
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $pelunasanpiutangheader->nobukti = $nobukti;
    

            try {
                $pelunasanpiutangheader->save();
                DB::commit();
            } catch (\Exception $e) {
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

            $logTrail = [
                'namatabel' => strtoupper($pelunasanpiutangheader->getTable()),
                'postingdari' => 'ENTRY PELUNASAN PIUTANG HEADER',
                'idtrans' => $pelunasanpiutangheader->id,
                'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $pelunasanpiutangheader->toArray(),
                'modifiedby' => $pelunasanpiutangheader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            
            /* Store detail */
                      
            $detaillog = [];

            for ($i = 0; $i < count($request->nominal); $i++) {
                    
                $datadetail = [
                    'pelunasanpiutang_id' => $pelunasanpiutangheader->id,
                    'nobukti' => $pelunasanpiutangheader->nobukti,
                    'tgl' => $pelunasanpiutangheader->tglbukti,
                    'pelanggan_id' => $request->pelanggan_id,
                    'agen_id' => $request->agen_detail,
                    'nominal' => str_replace(',', '',$request->nominal_detail),
                    'piutang_nobukti' => $request->piutang_nobukti,
                    'cicilan' => $request->cicilan,
                    'tglcair' => $request->tglcair,
                    'keterangan' => $request->keterangan_detail,
                    'tgljt' => $request->tgljt,
                    'penyesuaian' => $request->penyesuaian,
                    'coapenyesuaian' => $request->coapenyesuaian,
                    'invoice_nobukti' => $request->invoice_nobukti,
                    'keteranganpenyesuaian' => $request->keteranganpenyesuaian,
                    'nominallebihbayar' => $request->nominallebihbayar,
                    'coalebihbayar' => $request->coalebihbayar,
                    'modifiedby' => $pelunasanpiutangheader->modifiedby,
                ];

                //STORE 
                $data = new StorePelunasanPiutangDetailRequest($datadetail);
                
                $datadetails = app(PelunasanPiutangDetailController::class)->store($data);
                // dd('tes');
                
                
                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }
                
                
                $datadetaillog = [
                    'id' => $iddetail,
                    'pelunasanpiutangheader_id' => $pelunasanpiutangheader->id,
                    'nobukti' => $pelunasanpiutangheader->nobukti,
                    'tgl' => $pelunasanpiutangheader->tglbukti,
                    'pelanggan_id' => $request->pelanggan_id,
                    'agen_id' => $request->agen_id,
                    'nominal' => $request->nominal,
                    'piutang_nobukti' => $request->piutang_nobukti,
                    'cicilan' => $request->cicilan,
                    'piutang_nobukti' => $request->piutang_nobukti,
                    'piutang_nobukti' => $request->piutang_nobukti,
                    'piutang_nobukti' => $request->piutang_nobukti,
                    'piutang_nobukti' => $request->piutang_nobukti,
                    'modifiedby' => $pelunasanpiutangheader->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($pelunasanpiutangheader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($pelunasanpiutangheader->updated_at)),
                    
                ];
                
                $detaillog[] = $datadetaillog;

                
                $dataid = LogTrail::select('id')
                ->where('idtrans', '=', $pelunasanpiutangheader->id)
                ->where('namatabel', '=', $pelunasanpiutangheader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY PELUNASAN PIUTANG DETAIL',
                    'idtrans' =>  $dataid->id,
                    'nobuktitrans' => $pelunasanpiutangheader->nobukti,
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
        

            $selected = $this->getPosition($pelunasanpiutangheader, $pelunasanpiutangheader->getTable());
            $pelunasanpiutangheader->position = $selected->position;
            $pelunasanpiutangheader->page = ceil($pelunasanpiutangheader->position / ($request->limit ?? 10));
            
            // dd($pengeluarantruckingheader->page);

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pelunasanpiutangheader 
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }   
    }

    
    public function show($id)
    {
        // $data = PelunasanPiutangHeader::with(
        //     'pelunasanpiutangdetail',
        // )->find($id);

        $data = PelunasanPiutangHeader::findAll($id);
        $detail = PelunasanPiutangDetail::findAll($id);
        
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName
     */
    public function update(StorePelunasanPiutangHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {

            $pelunasanpiutangheader = PelunasanPiutangHeader::findOrFail($id);
            
            $pelunasanpiutangheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pelunasanpiutangheader->keterangan = $request->keterangan;
            $pelunasanpiutangheader->bank_id = $request->bank_id;
            $pelunasanpiutangheader->agen_id = $request->agen_id;
            $pelunasanpiutangheader->cabang_id = $request->cabang_id;
            $pelunasanpiutangheader->modifiedby = auth('api')->user()->name;

            
            if($pelunasanpiutangheader->save()){
                $logTrail = [
                    'namatabel' => strtoupper($pelunasanpiutangheader->getTable()),
                    'postingdari' => 'EDIT PELUNASAN PIUTANG HEADER',
                    'idtrans' => $id,
                    'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $pelunasanpiutangheader->toArray(),
                    'modifiedby' => $pelunasanpiutangheader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                PelunasanPiutangDetail::where('pelunasanpiutang_id', $id)->delete();

                /* Store detail */
                
                $detaillog = [];
                for($i = 0; $i < count($request->nominal_detail); $i++){
                     $nominal = str_replace('.00','', $request->nominal_detail[$i]);

                    $datadetail = [
                        'pelunasanpiutang_id' => $pelunasanpiutangheader->id,
                        'nobukti' => $pelunasanpiutangheader->nobukti,
                        'tgl' => $pelunasanpiutangheader->tglbukti,
                        'pelanggan_id' => $request->pelanggan_id,
                        'agen_id' => $request->agen_detail,
                        'nominal' => str_replace(',', '',$request->nominal_detail),
                        'piutang_nobukti' => $request->piutang_nobukti,
                        'cicilan' => $request->cicilan,
                        'tglcair' => $request->tglcair,
                        'keterangan' => $request->keterangan_detail,
                        'tgljt' => $request->tgljt,
                        'penyesuaian' => $request->penyesuaian,
                        'coapenyesuaian' => $request->coapenyesuaian,
                        'invoice_nobukti' => $request->invoice_nobukti,
                        'keteranganpenyesuaian' => $request->keteranganpenyesuaian,
                        'nominallebihbayar' => $request->nominallebihbayar,
                        'coalebihbayar' => $request->coalebihbayar,
                        'modifiedby' => $pelunasanpiutangheader->modifiedby,
                    ];

                    //STORE
                    
                    $data = new StorePelunasanPiutangDetailRequest($datadetail);
                    $datadetails = app(PiutangDetailController::class)->store($data);
                    
                    if($datadetails['error']){
                        return response($datadetails, 422);
                    }else{
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    
                    $datadetaillog = [
                        'id' => $iddetail,
                        'pelunasanpiutangheader_id' => $pelunasanpiutangheader->id,
                        'nobukti' => $pelunasanpiutangheader->nobukti,
                        'tgl' => $pelunasanpiutangheader->tglbukti,
                        'pelanggan_id' => $request->pelanggan_id,
                        'agen_id' => $request->agen_id,
                        'nominal' => $request->nominal,
                        'piutang_nobukti' => $request->piutang_nobukti,
                        'cicilan' => $request->cicilan,
                        'piutang_nobukti' => $request->piutang_nobukti,
                        'piutang_nobukti' => $request->piutang_nobukti,
                        'piutang_nobukti' => $request->piutang_nobukti,
                        'piutang_nobukti' => $request->piutang_nobukti,
                        'modifiedby' => $pelunasanpiutangheader->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($pelunasanpiutangheader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($pelunasanpiutangheader->updated_at)),
                    
                    ];

                    $detaillog[] = $datadetaillog;

                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT PELUNASAN PIUTANG DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $pelunasanpiutangheader->nobukti,
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
             $selected = $this->getPosition($pelunasanpiutangheader, $pelunasanpiutangheader->getTable(), true);
                $pelunasanpiutangheader->position = $selected->position;
                $pelunasanpiutangheader->id = $selected->id;
                $pelunasanpiutangheader->page = ceil($pelunasanpiutangheader->position / ($request->limit ?? 10));

            

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pelunasanpiutangheader
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
        $pelunasanpiutangheader = new PelunasanPiutangDetail();
        try {
            
            $delete = PelunasanPiutangDetail::where('pelunasanpiutang_id',$id)->delete();
            $delete = PelunasanPiutangHeader::destroy($id);
            
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($pelunasanpiutangheader->getTable()),
                    'postingdari' => 'DELETE PELUNASAN PIUTANG HEADER',
                    'idtrans' => $id,
                    'nobuktitrans' => '',
                    'aksi' => 'DELETE',
                    'datajson' => $pelunasanpiutangheader->toArray(),
                    'modifiedby' => $pelunasanpiutangheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($pelunasanpiutangheader, $pelunasanpiutangheader->getTable(), true);
                $pelunasanpiutangheader->position = $selected->position;
                $pelunasanpiutangheader->id = $selected->id;
                $pelunasanpiutangheader->page = ceil($pelunasanpiutangheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $pelunasanpiutangheader
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
            'agen' => Agen::all(),
            'cabang' => Cabang::all(),
            'bank' => Bank::all(),
            'pelanggan' => Pelanggan::all(),
            
        ];

        return response([
            'data' => $data
        ]);
    }
}
