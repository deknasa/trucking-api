<?php

namespace App\Http\Controllers;

use App\Models\Akuntansi;
use App\Http\Requests\StoreAkuntansiRequest;
use App\Http\Requests\UpdateAkuntansiRequest;

class AkuntansiController extends Controller
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
     * @param  \App\Http\Requests\StoreAkuntansiRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAkuntansiRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Akuntansi  $akuntansi
     * @return \Illuminate\Http\Response
     */
    public function show(Akuntansi $akuntansi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Akuntansi  $akuntansi
     * @return \Illuminate\Http\Response
     */
    public function edit(Akuntansi $akuntansi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAkuntansiRequest  $request
     * @param  \App\Models\Akuntansi  $akuntansi
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAkuntansiRequest $request, Akuntansi $akuntansi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Akuntansi  $akuntansi
     * @return \Illuminate\Http\Response
     */
    public function destroy(Akuntansi $akuntansi)
    {
        //
    }
}
