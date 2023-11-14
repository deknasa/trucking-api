<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\SaldoAkunPusatDetail;
use App\Http\Requests\StoreSaldoAkunPusatDetailRequest;
use App\Http\Requests\UpdateSaldoAkunPusatDetailRequest;

class SaldoAkunPusatDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        //
    }

    public function importdatacabang()
    {
        // dd('test');
        $saldoAkunPusatDetail = new SaldoAkunPusatDetail();
        return response([
            'data' => $saldoAkunPusatDetail->getimportdatacabang(),
            'attributes' => [
                'totalRows' => $saldoAkunPusatDetail->totalRows,
                'totalPages' => $saldoAkunPusatDetail->totalPages
            ]
        ]);
    }

    public function importdatacabangtahun()
    {
        // dd('test');
        $saldoAkunPusatDetail = new SaldoAkunPusatDetail();
        return response([
            'data' => $saldoAkunPusatDetail->getimportdatacabangtahun(),
            'attributes' => [
                'totalRows' => $saldoAkunPusatDetail->totalRows,
                'totalPages' => $saldoAkunPusatDetail->totalPages
            ]
        ]);
    }

    public function importdatacabangbulan()
    {
        // dd('test');
        $saldoAkunPusatDetail = new SaldoAkunPusatDetail();
        return response([
            'data' => $saldoAkunPusatDetail->getimportdatacabangbulan(),
            'attributes' => [
                'totalRows' => $saldoAkunPusatDetail->totalRows,
                'totalPages' => $saldoAkunPusatDetail->totalPages
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
     * @param  \App\Http\Requests\StoreSaldoAkunPusatDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaldoAkunPusatDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaldoAkunPusatDetail  $saldoAkunPusatDetail
     * @return \Illuminate\Http\Response
     */
    public function show(SaldoAkunPusatDetail $saldoAkunPusatDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SaldoAkunPusatDetail  $saldoAkunPusatDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(SaldoAkunPusatDetail $saldoAkunPusatDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaldoAkunPusatDetailRequest  $request
     * @param  \App\Models\SaldoAkunPusatDetail  $saldoAkunPusatDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaldoAkunPusatDetailRequest $request, SaldoAkunPusatDetail $saldoAkunPusatDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaldoAkunPusatDetail  $saldoAkunPusatDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaldoAkunPusatDetail $saldoAkunPusatDetail)
    {
        //
    }
}
