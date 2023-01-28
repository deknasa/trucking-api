<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\MandorAbsensiSupir;
use App\Http\Requests\StoreMandorAbsensiSupirRequest;
use App\Http\Requests\UpdateMandorAbsensiSupirRequest;

class MandorAbsensiSupirController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        //
        dd('test');
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
     * @ClassName 
     */
    public function store(StoreMandorAbsensiSupirRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MandorAbsensiSupir  $mandorAbsensiSupir
     * @return \Illuminate\Http\Response
     */
    public function show(MandorAbsensiSupir $mandorAbsensiSupir)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MandorAbsensiSupir  $mandorAbsensiSupir
     * @return \Illuminate\Http\Response
     */
    public function edit(MandorAbsensiSupir $mandorAbsensiSupir)
    {
        //
    }

    /**
     * @ClassName 
     */
    public function update(UpdateMandorAbsensiSupirRequest $request, MandorAbsensiSupir $mandorAbsensiSupir)
    {
        //
    }

    /**
     * @ClassName 
     */
    public function destroy(MandorAbsensiSupir $mandorAbsensiSupir)
    {
        //
    }
}
