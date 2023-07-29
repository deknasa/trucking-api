<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\SaldoSuratPengantar;
use App\Http\Requests\StoreSaldoSuratPengantarRequest;
use App\Http\Requests\UpdateSaldoSuratPengantarRequest;

class SaldoSuratPengantarController extends Controller
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
     * @param  \App\Http\Requests\StoreSaldoSuratPengantarRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaldoSuratPengantarRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaldoSuratPengantar  $saldoSuratPengantar
     * @return \Illuminate\Http\Response
     */
    public function show(SaldoSuratPengantar $saldoSuratPengantar)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SaldoSuratPengantar  $saldoSuratPengantar
     * @return \Illuminate\Http\Response
     */
    public function edit(SaldoSuratPengantar $saldoSuratPengantar)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaldoSuratPengantarRequest  $request
     * @param  \App\Models\SaldoSuratPengantar  $saldoSuratPengantar
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaldoSuratPengantarRequest $request, SaldoSuratPengantar $saldoSuratPengantar)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaldoSuratPengantar  $saldoSuratPengantar
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaldoSuratPengantar $saldoSuratPengantar)
    {
        //
    }
}
