<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\StatusOliTradoDetail;
use App\Http\Requests\StoreStatusOliTradoDetailRequest;
use App\Http\Requests\UpdateStatusOliTradoDetailRequest;

class StatusOliTradoDetailController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $trado_id=request()->trado_id ?? 0;


        $statusOli = new StatusOliTradoDetail();
        return response([
            'data' => $statusOli->get($trado_id),
            'attributes' => [
                'totalRows' => $statusOli->totalRows,
                'totalPages' => $statusOli->totalPages
            ]
        ]);
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
     * @param  \App\Http\Requests\StoreStatusOliTradoDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStatusOliTradoDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StatusOliTradoDetail  $statusOliTradoDetail
     * @return \Illuminate\Http\Response
     */
    public function show(StatusOliTradoDetail $statusOliTradoDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StatusOliTradoDetail  $statusOliTradoDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(StatusOliTradoDetail $statusOliTradoDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStatusOliTradoDetailRequest  $request
     * @param  \App\Models\StatusOliTradoDetail  $statusOliTradoDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStatusOliTradoDetailRequest $request, StatusOliTradoDetail $statusOliTradoDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StatusOliTradoDetail  $statusOliTradoDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(StatusOliTradoDetail $statusOliTradoDetail)
    {
        //
    }
}
