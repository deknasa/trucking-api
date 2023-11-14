<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\SaldoAwalBukuBesar;
use App\Http\Requests\StoreSaldoAwalBukuBesarRequest;
use App\Http\Requests\UpdateSaldoAwalBukuBesarRequest;

class SaldoAwalBukuBesarController extends Controller
{

    public function importdatacabang()
    {
        // dd('test');
        $saldoAwalBukuBesar = new SaldoAwalBukuBesar();
        return response([
            'data' => $saldoAwalBukuBesar->getimportdatacabang(),
            'attributes' => [
                'totalRows' => $saldoAwalBukuBesar->totalRows,
                'totalPages' => $saldoAwalBukuBesar->totalPages
            ]
        ]);
    }

    public function importdatacabangtahun()
    {
        // dd('test');
        $saldoAwalBukuBesar = new SaldoAwalBukuBesar();
        return response([
            'data' => $saldoAwalBukuBesar->getimportdatacabangtahun(),
            'attributes' => [
                'totalRows' => $saldoAwalBukuBesar->totalRows,
                'totalPages' => $saldoAwalBukuBesar->totalPages
            ]
        ]);
    }

    public function importdatacabangbulan()
    {
        // dd('test');
        $saldoAwalBukuBesar = new SaldoAwalBukuBesar();
        return response([
            'data' => $saldoAwalBukuBesar->getimportdatacabangbulan(),
            'attributes' => [
                'totalRows' => $saldoAwalBukuBesar->totalRows,
                'totalPages' => $saldoAwalBukuBesar->totalPages
            ]
        ]);
    }

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
     * @param  \App\Http\Requests\StoreSaldoAwalBukuBesarRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaldoAwalBukuBesarRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaldoAwalBukuBesar  $saldoAwalBukuBesar
     * @return \Illuminate\Http\Response
     */
    public function show(SaldoAwalBukuBesar $saldoAwalBukuBesar)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SaldoAwalBukuBesar  $saldoAwalBukuBesar
     * @return \Illuminate\Http\Response
     */
    public function edit(SaldoAwalBukuBesar $saldoAwalBukuBesar)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaldoAwalBukuBesarRequest  $request
     * @param  \App\Models\SaldoAwalBukuBesar  $saldoAwalBukuBesar
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaldoAwalBukuBesarRequest $request, SaldoAwalBukuBesar $saldoAwalBukuBesar)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaldoAwalBukuBesar  $saldoAwalBukuBesar
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaldoAwalBukuBesar $saldoAwalBukuBesar)
    {
        //
    }
}
