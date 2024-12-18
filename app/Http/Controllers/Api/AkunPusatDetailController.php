<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\AkunPusatDetail;
use App\Http\Requests\StoreAkunPusatDetailRequest;
use App\Http\Requests\UpdateAkunPusatDetailRequest;

class AkunPusatDetailController extends Controller
{

    public function index()
    {
        //
    }

    public function importdatacabang()
    {
        // dd('test');
        $akunPusatDetail = new AkunPusatDetail();
        return response([
            'data' => $akunPusatDetail->getimportdatacabang(),
            'attributes' => [
                'totalRows' => $akunPusatDetail->totalRows,
                'totalPages' => $akunPusatDetail->totalPages
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
     * @param  \App\Http\Requests\StoreAkunPusatDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAkunPusatDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AkunPusatDetail  $akunPusatDetail
     * @return \Illuminate\Http\Response
     */
    public function show(AkunPusatDetail $akunPusatDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AkunPusatDetail  $akunPusatDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(AkunPusatDetail $akunPusatDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAkunPusatDetailRequest  $request
     * @param  \App\Models\AkunPusatDetail  $akunPusatDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAkunPusatDetailRequest $request, AkunPusatDetail $akunPusatDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AkunPusatDetail  $akunPusatDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(AkunPusatDetail $akunPusatDetail)
    {
        //
    }
}
