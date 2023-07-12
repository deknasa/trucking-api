<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\SaldoHutangPrediksi;
use App\Http\Requests\StoreSaldoHutangPrediksiRequest;
use App\Http\Requests\UpdateSaldoHutangPrediksiRequest;

class SaldoHutangPrediksiController extends Controller
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
     * @param  \App\Http\Requests\StoreSaldoHutangPrediksiRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaldoHutangPrediksiRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaldoHutangPrediksi  $saldoHutangPrediksi
     * @return \Illuminate\Http\Response
     */
    public function show(SaldoHutangPrediksi $saldoHutangPrediksi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SaldoHutangPrediksi  $saldoHutangPrediksi
     * @return \Illuminate\Http\Response
     */
    public function edit(SaldoHutangPrediksi $saldoHutangPrediksi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaldoHutangPrediksiRequest  $request
     * @param  \App\Models\SaldoHutangPrediksi  $saldoHutangPrediksi
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaldoHutangPrediksiRequest $request, SaldoHutangPrediksi $saldoHutangPrediksi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaldoHutangPrediksi  $saldoHutangPrediksi
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaldoHutangPrediksi $saldoHutangPrediksi)
    {
        //
    }
}
