<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Models\RekapPenerimaanDetail;
use App\Http\Requests\StoreRekapPenerimaanDetailRequest;
use App\Http\Requests\UpdateRekapPenerimaanDetailRequest;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RekapPenerimaanDetailController extends Controller
{
    
    public function index(Request $request)
    {
        
           
        $rekapPenerimaanDetail = new RekapPenerimaanDetail ();

        return response([
            'data' => $rekapPenerimaanDetail->get(),
            'attributes' => [
                'totalRows' => $rekapPenerimaanDetail->totalRows ,
                'totalPages' => $rekapPenerimaanDetail->totalPages ,
                'totalNominal' => $rekapPenerimaanDetail->totalNominal ,
            ]
        ]);
    }

    
    
    public function store(StoreRekapPenerimaanDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            "rekappenerimaan_id" => 'required',
            "nobukti" => 'required',
            "tgltransaksi" => 'required',
            "penerimaan_nobukti" => 'required',
            "nominal" => 'required',
            // "keterangandetail" => 'required',
            "modifiedby" => 'required',
         ], [
             "rekappenerimaan_id.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             "nobukti.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             "tgltransaksi.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             "penerimaan_nobukti.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             "nominal.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            //  "keterangandetail.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             "modifiedby.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
         ], [
            //  'keterangandetail' => 'keterangan Detail',
            ],
         );
         if (!$validator->passes()) {
             return [
                 'error' => true,
                 'errors' => $validator->messages()
             ];
         }
         try {

            $rekeapPenerimaanDetail = new RekapPenerimaanDetail();
            $rekeapPenerimaanDetail->rekappenerimaan_id = $request->rekappenerimaan_id;
            $rekeapPenerimaanDetail->nobukti = $request->nobukti;
            $rekeapPenerimaanDetail->tgltransaksi =  date('Y-m-d',strtotime($request->tgltransaksi));
            $rekeapPenerimaanDetail->penerimaan_nobukti = $request->penerimaan_nobukti;
            $rekeapPenerimaanDetail->nominal = $request->nominal;
            $rekeapPenerimaanDetail->keterangan = $request->keterangandetail;
            $rekeapPenerimaanDetail->modifiedby = $request->modifiedby;
            
            DB::commit();
            if ($rekeapPenerimaanDetail->save()) {
                return [
                    'error' => false,
                    'id' => $rekeapPenerimaanDetail->id,
                    'data' => $rekeapPenerimaanDetail,
                    'tabel' => $rekeapPenerimaanDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RekapPenerimaanDetail  $rekapPenerimaanDetail
     * @return \Illuminate\Http\Response
     */
    public function show(RekapPenerimaanDetail $rekapPenerimaanDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RekapPenerimaanDetail  $rekapPenerimaanDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(RekapPenerimaanDetail $rekapPenerimaanDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRekapPenerimaanDetailRequest  $request
     * @param  \App\Models\RekapPenerimaanDetail  $rekapPenerimaanDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRekapPenerimaanDetailRequest $request, RekapPenerimaanDetail $rekapPenerimaanDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RekapPenerimaanDetail  $rekapPenerimaanDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(RekapPenerimaanDetail $rekapPenerimaanDetail)
    {
        //
    }
}
