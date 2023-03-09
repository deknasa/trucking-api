<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\RekapPengeluaranDetail;
use App\Http\Requests\StoreRekapPengeluaranDetailRequest;
use App\Http\Requests\UpdateRekapPengeluaranDetailRequest;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RekapPengeluaranDetailController extends Controller
{
    public function index(Request $request)
    {

        $rekapPengeluaranDetail = new RekapPengeluaranDetail ();

        return response([
            'data' => $rekapPengeluaranDetail->get(),
            'attributes' => [
                'totalRows' => $rekapPengeluaranDetail->totalRows ,
                'totalPages' => $rekapPengeluaranDetail->totalPages ,
                'totalNominal' => $rekapPengeluaranDetail->totalNominal ,
            ]
        ]);
    }

    
    
    public function store(StoreRekapPengeluaranDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            "rekappengeluaran_id" => 'required',
            "nobukti" => 'required',
            "tgltransaksi" => 'required',
            "pengeluaran_nobukti" => 'required',
            "nominal" => 'required',
            // "keterangandetail" => 'required',
            "modifiedby" => 'required',
         ], [
             "rekappengeluaran_id.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             "nobukti.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             "tgltransaksi.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             "pengeluaran_nobukti.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
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

            $rekeapPengeluaranDetail = new RekapPengeluaranDetail();
            $rekeapPengeluaranDetail->rekappengeluaran_id = $request->rekappengeluaran_id;
            $rekeapPengeluaranDetail->nobukti = $request->nobukti;
            $rekeapPengeluaranDetail->tgltransaksi =  date('Y-m-d',strtotime($request->tgltransaksi));
            $rekeapPengeluaranDetail->pengeluaran_nobukti = $request->pengeluaran_nobukti;
            $rekeapPengeluaranDetail->nominal = $request->nominal;
            $rekeapPengeluaranDetail->keterangan = $request->keterangan;
            $rekeapPengeluaranDetail->modifiedby = $request->modifiedby;
            
            DB::commit();
            if ($rekeapPengeluaranDetail->save()) {
                return [
                    'error' => false,
                    'id' => $rekeapPengeluaranDetail->id,
                    'data' => $rekeapPengeluaranDetail,
                    'tabel' => $rekeapPengeluaranDetail->getTable(),
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
     * @param  \App\Models\RekapPengeluaranDetail  $rekapPengeluaranDetail
     * @return \Illuminate\Http\Response
     */
    public function show(RekapPengeluaranDetail $rekapPengeluaranDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RekapPengeluaranDetail  $rekapPengeluaranDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(RekapPengeluaranDetail $rekapPengeluaranDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRekapPengeluaranDetailRequest  $request
     * @param  \App\Models\RekapPengeluaranDetail  $rekapPengeluaranDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRekapPengeluaranDetailRequest $request, RekapPengeluaranDetail $rekapPengeluaranDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RekapPengeluaranDetail  $rekapPengeluaranDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(RekapPengeluaranDetail $rekapPengeluaranDetail)
    {
        //
    }
}
