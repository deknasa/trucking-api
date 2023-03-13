<?php

namespace App\Http\Controllers;

use App\Models\SaldoHutang;
use App\Http\Requests\StoreSaldoHutangRequest;
use App\Http\Requests\UpdateSaldoHutangRequest;

class SaldoHutangController extends Controller
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
     * @param  \App\Http\Requests\StoreSaldoHutangRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaldoHutangRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaldoHutang  $saldoHutang
     * @return \Illuminate\Http\Response
     */
    public function show(SaldoHutang $saldoHutang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SaldoHutang  $saldoHutang
     * @return \Illuminate\Http\Response
     */
    public function edit(SaldoHutang $saldoHutang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaldoHutangRequest  $request
     * @param  \App\Models\SaldoHutang  $saldoHutang
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaldoHutangRequest $request, SaldoHutang $saldoHutang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaldoHutang  $saldoHutang
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaldoHutang $saldoHutang)
    {
        //
    }
}
