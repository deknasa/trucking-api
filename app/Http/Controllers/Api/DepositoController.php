<?php

namespace App\Http\Controllers;

use App\Models\Deposito;
use App\Http\Requests\StoreDepositoRequest;
use App\Http\Requests\UpdateDepositoRequest;

class DepositoController extends Controller
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
     * @param  \App\Http\Requests\StoreDepositoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDepositoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Deposito  $deposito
     * @return \Illuminate\Http\Response
     */
    public function show(Deposito $deposito)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Deposito  $deposito
     * @return \Illuminate\Http\Response
     */
    public function edit(Deposito $deposito)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDepositoRequest  $request
     * @param  \App\Models\Deposito  $deposito
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDepositoRequest $request, Deposito $deposito)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Deposito  $deposito
     * @return \Illuminate\Http\Response
     */
    public function destroy(Deposito $deposito)
    {
        //
    }
}
