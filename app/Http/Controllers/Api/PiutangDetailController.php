<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetPiutangDetailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\PiutangDetail;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Http\Requests\UpdatePiutangDetailRequest;
use App\Models\JurnalUmumHeader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PiutangDetailController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'piutang_id' => $request->piutang_id,
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        try {
            $query = PiutangDetail::from('piutangdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['piutang_id'])) {
                $query->where('detail.piutang_id', $params['piutang_id']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.id as id_header',
                    'header.nobukti as nobukti_header',
                    'header.tglbukti as tgl_header',
                    'header.keterangan as keterangan_header',
                    'header.invoice_nobukti as invoice_nobukti',
                    'agen.namaagen as agen_id',
                    'detail.keterangan as keterangan_detail',
                    'detail.nominal',
                    'detail.invoice_nobukti as invoice_nobukti_detail'
                )
                    ->leftJoin('piutangheader as header', 'header.id', 'detail.piutang_id')
                    ->leftJoin('agen', 'header.agen_id','agen.id');

                $piutangDetail = $query->get();
            } else {
                // $piutangDetail = new PiutangDetail();

                // $piutangDetail = $piutangDetail->get($request->piutang_id);
                $query->select('detail.nobukti','detail.keterangan','detail.invoice_nobukti','detail.nominal');
                
                $piutangDetail = $query->get();
            }
            return response([
                'data' => $piutangDetail,
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
       
    }

    public function store(StorePiutangDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $piutangdetail = new PiutangDetail();

            $piutangdetail->piutang_id = $request->piutang_id;
            $piutangdetail->nobukti = $request->nobukti;
            $piutangdetail->nominal = $request->nominal;
            $piutangdetail->keterangan = $request->keterangan;
            $piutangdetail->invoice_nobukti = $request->invoice_nobukti;
            $piutangdetail->modifiedby = auth('api')->user()->name;

            $piutangdetail->save();

            // if($entriLuar == 1) {
            //     $nobukti = $piutangdetail->nobukti;
            //     $fetchId = JurnalUmumHeader::select('id','tglbukti')
            //     ->where('nobukti','=',$nobukti)
            //     ->first();
            //     $id = $fetchId->id;

            //     $getBaris = DB::table('jurnalumumdetail')->select('baris')->where('nobukti', $nobukti)->orderByDesc('baris')->first();

            //     $getCOA = DB::table('parameter')->where("kelompok","COA INVOICE")->get();
                
            //     if(is_null($getBaris)) {
            //         $baris = 0;
            //     }else{
            //         $baris = $getBaris->baris+1;
            //     }
                
            //     for ($x = 0; $x <= 1; $x++) {
                    
            //         if ($x == 1) {
            //             $datadetail = [
            //                 'jurnalumum_id' => $id,
            //                 'nobukti' => $piutangdetail->nobukti,
            //                 'tglbukti' => $fetchId->tglbukti,
            //                 'coa' =>  $getCOA[$x]->text,
            //                 'nominal' => -$piutangdetail->nominal,
            //                 'keterangan' => $piutangdetail->keterangan,
            //                 'modifiedby' => auth('api')->user()->name,
            //                 'baris' => $baris,
            //             ];
            //         } else {
            //             $datadetail = [
            //                 'jurnalumum_id' => $id,
            //                 'nobukti' => $piutangdetail->nobukti,
            //                 'tglbukti' => $fetchId->tglbukti,
            //                 'coa' =>  $getCOA[$x]->text,
            //                 'nominal' => $piutangdetail->nominal,
            //                 'keterangan' => $piutangdetail->keterangan,
            //                 'modifiedby' => auth('api')->user()->name,
            //                 'baris' => $baris,
            //             ];
            //         }
            //         $detail = new StoreJurnalUmumDetailRequest($datadetail);
            //         $tes = app(JurnalUmumDetailController::class)->store($detail); 
            //     }
            // }
            DB::commit();

           
                return [
                    'error' => false,
                    'detail' => $piutangdetail,
                    'id' => $piutangdetail->id,
                    'tabel' => $piutangdetail->getTable(),
                ];
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
