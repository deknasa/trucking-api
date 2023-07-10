<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\SaldoAwalBank;
use App\Http\Requests\StoreSaldoAwalBankRequest;
use App\Http\Requests\UpdateSaldoAwalBankRequest;

class SaldoAwalBankController extends Controller
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
     * @param  \App\Http\Requests\StoreSaldoAwalBankRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaldoAwalBankRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaldoAwalBank  $saldoAwalBank
     * @return \Illuminate\Http\Response
     */
    public function show(SaldoAwalBank $saldoAwalBank)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SaldoAwalBank  $saldoAwalBank
     * @return \Illuminate\Http\Response
     */
    public function edit(SaldoAwalBank $saldoAwalBank)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaldoAwalBankRequest  $request
     * @param  \App\Models\SaldoAwalBank  $saldoAwalBank
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaldoAwalBankRequest $request, SaldoAwalBank $saldoAwalBank)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaldoAwalBank  $saldoAwalBank
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaldoAwalBank $saldoAwalBank)
    {
        //
    }
}
