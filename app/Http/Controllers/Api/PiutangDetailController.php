<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetPiutangDetailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\PiutangDetail;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Http\Requests\UpdatePiutangDetailRequest;
use App\Models\JurnalUmumHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PiutangDetailController extends Controller
{
    public function index(GetPiutangDetailRequest $request)
    {
        $piutangDetail = new PiutangDetail();

        return response([
            'data' => $piutangDetail->get($request->piutang_id),
            'attributes' => [
                'totalRows' => $piutangDetail->totalRows,
                'totalPages' => $piutangDetail->totalPages
            ]
        ]);
    }

    public function store(StorePiutangDetailRequest $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'nominal' => 'required',
            'keterangan' => 'required'
        ], [
            'nominal.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan
        ]);
        // dd($request->all());

        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }
        try {
            $piutangdetail = new PiutangDetail();
            $entriLuar = $request->entriluar ?? 0;

            $piutangdetail->piutang_id = $request->piutang_id;
            $piutangdetail->nobukti = $request->nobukti;
            $piutangdetail->nominal = $request->nominal;
            $piutangdetail->keterangan = $request->keterangan;
            $piutangdetail->invoice_nobukti = $request->invoice_nobukti;
            $piutangdetail->modifiedby = auth('api')->user()->name;

            $piutangdetail->save();

            if($entriLuar == 1) {
                $nobukti = $piutangdetail->nobukti;
                $fetchId = JurnalUmumHeader::select('id','tglbukti')
                ->where('nobukti','=',$nobukti)
                ->first();
                $id = $fetchId->id;

                $getBaris = DB::table('jurnalumumdetail')->select('baris')->where('nobukti', $nobukti)->orderByDesc('baris')->first();

                $getCOA = DB::table('parameter')->where("kelompok","COA INVOICE")->get();
                
                if(is_null($getBaris)) {
                    $baris = 0;
                }else{
                    $baris = $getBaris->baris+1;
                }
                
                for ($x = 0; $x <= 1; $x++) {
                    
                    if ($x == 1) {
                        $datadetail = [
                            'jurnalumum_id' => $id,
                            'nobukti' => $piutangdetail->nobukti,
                            'tglbukti' => $fetchId->tglbukti,
                            'coa' =>  $getCOA[$x]->text,
                            'nominal' => -$piutangdetail->nominal,
                            'keterangan' => $piutangdetail->keterangan,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    } else {
                        $datadetail = [
                            'jurnalumum_id' => $id,
                            'nobukti' => $piutangdetail->nobukti,
                            'tglbukti' => $fetchId->tglbukti,
                            'coa' =>  $getCOA[$x]->text,
                            'nominal' => $piutangdetail->nominal,
                            'keterangan' => $piutangdetail->keterangan,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    }
                    $detail = new StoreJurnalUmumDetailRequest($datadetail);
                    $tes = app(JurnalUmumDetailController::class)->store($detail); 
                }
            }
            DB::commit();

            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $piutangdetail->id,
                    'tabel' => $piutangdetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
