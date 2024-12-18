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
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {


        $rekapPenerimaanDetail = new RekapPenerimaanDetail();

        return response([
            'data' => $rekapPenerimaanDetail->get(),
            'attributes' => [
                'totalRows' => $rekapPenerimaanDetail->totalRows,
                'totalPages' => $rekapPenerimaanDetail->totalPages,
                'totalNominal' => $rekapPenerimaanDetail->totalNominal,
            ]
        ]);
    }



    public function store(StoreRekapPenerimaanDetailRequest $request)
    {
        DB::beginTransaction();
        try {


            $rekeapPenerimaanDetail = new RekapPenerimaanDetail();
            $rekeapPenerimaanDetail->rekappenerimaan_id = $request->rekappenerimaan_id;
            $rekeapPenerimaanDetail->nobukti = $request->nobukti;
            $rekeapPenerimaanDetail->tgltransaksi =  date('Y-m-d', strtotime($request->tgltransaksi));
            $rekeapPenerimaanDetail->penerimaan_nobukti = $request->penerimaan_nobukti;
            $rekeapPenerimaanDetail->nominal = $request->nominal;
            $rekeapPenerimaanDetail->keterangan = $request->keterangandetail;
            $rekeapPenerimaanDetail->modifiedby = $request->modifiedby;

            $rekeapPenerimaanDetail->save();
            DB::commit();
            return [
                'error' => false,
                'id' => $rekeapPenerimaanDetail->id,
                'data' => $rekeapPenerimaanDetail,
                'tabel' => $rekeapPenerimaanDetail->getTable(),
            ];
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
