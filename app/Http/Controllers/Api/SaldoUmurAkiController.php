<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\SaldoUmurAki;
use App\Http\Requests\StoreSaldoUmurAkiRequest;
use App\Http\Requests\UpdateSaldoUmurAkiRequest;

class SaldoUmurAkiController extends Controller
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
     * @param  \App\Http\Requests\StoreSaldoUmurAkiRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaldoUmurAkiRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaldoUmurAki  $saldoUmurAki
     * @return \Illuminate\Http\Response
     */
    public function show(SaldoUmurAki $saldoUmurAki)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SaldoUmurAki  $saldoUmurAki
     * @return \Illuminate\Http\Response
     */
    public function edit(SaldoUmurAki $saldoUmurAki)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaldoUmurAkiRequest  $request
     * @param  \App\Models\SaldoUmurAki  $saldoUmurAki
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaldoUmurAkiRequest $request, SaldoUmurAki $saldoUmurAki)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaldoUmurAki  $saldoUmurAki
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaldoUmurAki $saldoUmurAki)
    {
        //
    }
}
