<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Models\PengembalianKasBankDetail;
use App\Http\Requests\StorePengembalianKasBankDetailRequest;
use App\Http\Requests\UpdatePengembalianKasBankDetailRequest;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PengembalianKasBankDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $pengembalianKasBankDetail = new PengembalianKasBankDetail();

        return response([
            'data' => $pengembalianKasBankDetail->get(),
            'attributes' => [
                'totalRows' => $pengembalianKasBankDetail->totalRows,
                'totalPages' => $pengembalianKasBankDetail->totalPages,
                'totalNominal' => $pengembalianKasBankDetail->totalNominal

            ]
        ]);
    
        
    }

    public function store(StorePengembalianKasBankDetailRequest $request)
    { 
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'alatbayar_id' => 'required',
            'tgljatuhtempo' => 'required',
            'nominal' => 'required',
            'keterangan' => 'required',
         ], [
             'alatbayar_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'tgljatuhtempo.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'nominal.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
              ], [
             'alatbayar_id' => 'coa detail',
             'tgljatuhtempo' => 'keterangandetail Detail',
             'nominal' => 'coa detail',
             'keterangan' => 'keterangandetail Detail',
            ],
         );         
         if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }

        try {
            $pengembalianKasBankDetail = new PengembalianKasBankDetail();
            $entriLuar = $request->entriluar ?? 0;

            $pengembalianKasBankDetail->pengembaliankasbank_id = $request->pengembaliankasbank_id;
            $pengembalianKasBankDetail->nobukti = $request->nobukti;
            $pengembalianKasBankDetail->alatbayar_id = $request->alatbayar_id ?? '';
            $pengembalianKasBankDetail->nowarkat = $request->nowarkat ?? '';
            $pengembalianKasBankDetail->tgljatuhtempo = $request->tgljatuhtempo ?? '';
            $pengembalianKasBankDetail->nominal = $request->nominal ?? '';
            $pengembalianKasBankDetail->coadebet = $request->coadebet ?? '';
            $pengembalianKasBankDetail->coakredit = $request->coakredit ?? '';
            $pengembalianKasBankDetail->keterangan = $request->keterangan ?? '';
            $pengembalianKasBankDetail->bulanbeban = $request->bulanbeban ?? '';
            $pengembalianKasBankDetail->modifiedby = $request->modifiedby;
            

            if ($pengembalianKasBankDetail->save()) {
                DB::commit();
                return [
                    'error' => false,
                    'id' => $pengembalianKasBankDetail->id,
                    'tabel' => $pengembalianKasBankDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PengembalianKasBankDetail  $pengembalianKasBankDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(PengembalianKasBankDetail $pengembalianKasBankDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePengembalianKasBankDetailRequest  $request
     * @param  \App\Models\PengembalianKasBankDetail  $pengembalianKasBankDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePengembalianKasBankDetailRequest $request, PengembalianKasBankDetail $pengembalianKasBankDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PengembalianKasBankDetail  $pengembalianKasBankDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(PengembalianKasBankDetail $pengembalianKasBankDetail)
    {
        //
    }
}
