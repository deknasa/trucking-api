<?php

namespace App\Http\Controllers;

use App\Models\SaldoPiutang;
use App\Http\Requests\StoreSaldoPiutangRequest;
use App\Http\Requests\UpdateSaldoPiutangRequest;

class SaldoPiutangController extends Controller
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
     * @param  \App\Http\Requests\StoreSaldoPiutangRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaldoPiutangRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaldoPiutang  $saldoPiutang
     * @return \Illuminate\Http\Response
     */
    public function show(SaldoPiutang $saldoPiutang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SaldoPiutang  $saldoPiutang
     * @return \Illuminate\Http\Response
     */
    public function edit(SaldoPiutang $saldoPiutang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaldoPiutangRequest  $request
     * @param  \App\Models\SaldoPiutang  $saldoPiutang
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaldoPiutangRequest $request, SaldoPiutang $saldoPiutang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaldoPiutang  $saldoPiutang
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaldoPiutang $saldoPiutang)
    {
        //
    }
}
