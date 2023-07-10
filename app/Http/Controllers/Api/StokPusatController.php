<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\StokPusat;
use App\Http\Requests\StoreStokPusatRequest;
use App\Http\Requests\UpdateStokPusatRequest;

class StokPusatController extends Controller
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
     * @param  \App\Http\Requests\StoreStokPusatRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStokPusatRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StokPusat  $stokPusat
     * @return \Illuminate\Http\Response
     */
    public function show(StokPusat $stokPusat)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StokPusat  $stokPusat
     * @return \Illuminate\Http\Response
     */
    public function edit(StokPusat $stokPusat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStokPusatRequest  $request
     * @param  \App\Models\StokPusat  $stokPusat
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStokPusatRequest $request, StokPusat $stokPusat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StokPusat  $stokPusat
     * @return \Illuminate\Http\Response
     */
    public function destroy(StokPusat $stokPusat)
    {
        //
    }
}
