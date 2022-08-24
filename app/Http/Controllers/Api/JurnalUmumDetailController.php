<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmumDetail;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateJurnalUmumDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JurnalUmumDetailController extends Controller
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
     * @param  \App\Http\Requests\StoreJurnalUmumDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJurnalUmumDetailRequest $request)
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
            $jurnalumumDetail = new JurnalUmumDetail();
            
            $jurnalumumDetail->jurnalumum_id = $request->jurnalumum_id;
            $jurnalumumDetail->nobukti = $request->nobukti;
            $jurnalumumDetail->tglbukti = $request->tglbukti;
            $jurnalumumDetail->coa = $request->coa;
            $jurnalumumDetail->nominal = $request->nominal;
            $jurnalumumDetail->keterangan = $request->keterangan ?? '';
            $jurnalumumDetail->modifiedby = $request->modifiedby;
            
            $jurnalumumDetail->save();
           
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $jurnalumumDetail->id,
                    'tabel' => $jurnalumumDetail->getTable(),
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
     * @param  \App\Models\JurnalUmumDetail  $jurnalUmumDetail
     * @return \Illuminate\Http\Response
     */
    public function show(JurnalUmumDetail $jurnalUmumDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JurnalUmumDetail  $jurnalUmumDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(JurnalUmumDetail $jurnalUmumDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJurnalUmumDetailRequest  $request
     * @param  \App\Models\JurnalUmumDetail  $jurnalUmumDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJurnalUmumDetailRequest $request, JurnalUmumDetail $jurnalUmumDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JurnalUmumDetail  $jurnalUmumDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(JurnalUmumDetail $jurnalUmumDetail)
    {
        //
    }
}
