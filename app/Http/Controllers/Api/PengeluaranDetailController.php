<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranDetail;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\UpdatePengeluaranDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengeluaranDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePengeluaranDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePengeluaranDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'nobukti' => 'required',
        ], [
            'nobukti.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'nobukti' => 'NoBukti',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'messages' => $validator->messages()
            ];
        }

        try {
            $pengeluaranDetail = new PengeluaranDetail();

            $pengeluaranDetail->pengeluaran_id = $request->pengeluaran_id;
            $pengeluaranDetail->nobukti = $request->nobukti;
            $pengeluaranDetail->alatbayar_id = $request->alatbayar_id;
            $pengeluaranDetail->nowarkat = $request->nowarkat;
            $pengeluaranDetail->tgljatuhtempo = $request->tgljatuhtempo;
            $pengeluaranDetail->nominal = $request->nominal;
            $pengeluaranDetail->coadebet = $request->coadebet;
            $pengeluaranDetail->coakredit = $request->coakredit;
            $pengeluaranDetail->keterangan = $request->keterangan ?? '';
            $pengeluaranDetail->bulanbeban = $request->bulanbeban;
            $pengeluaranDetail->modifiedby = $request->modifiedby;
            
            $pengeluaranDetail->save();
            
           
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $pengeluaranDetail->id,
                    'tabel' => $pengeluaranDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PengeluaranDetail  $pengeluaranDetail
     * @return \Illuminate\Http\Response
     */
    public function show(PengeluaranDetail $pengeluaranDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PengeluaranDetail  $pengeluaranDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(PengeluaranDetail $pengeluaranDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePengeluaranDetailRequest  $request
     * @param  \App\Models\PengeluaranDetail  $pengeluaranDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePengeluaranDetailRequest $request, PengeluaranDetail $pengeluaranDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PengeluaranDetail  $pengeluaranDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(PengeluaranDetail $pengeluaranDetail)
    {
        //
    }
}
