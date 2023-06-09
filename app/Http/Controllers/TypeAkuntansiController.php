<?php

namespace App\Http\Controllers;

use App\Models\TypeAkuntansi;
use App\Http\Requests\StoreTypeAkuntansiRequest;
use App\Http\Requests\UpdateTypeAkuntansiRequest;

class TypeAkuntansiController extends Controller
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
     * @param  \App\Http\Requests\StoreTypeAkuntansiRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTypeAkuntansiRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TypeAkuntansi  $typeAkuntansi
     * @return \Illuminate\Http\Response
     */
    public function show(TypeAkuntansi $typeAkuntansi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TypeAkuntansi  $typeAkuntansi
     * @return \Illuminate\Http\Response
     */
    public function edit(TypeAkuntansi $typeAkuntansi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTypeAkuntansiRequest  $request
     * @param  \App\Models\TypeAkuntansi  $typeAkuntansi
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTypeAkuntansiRequest $request, TypeAkuntansi $typeAkuntansi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TypeAkuntansi  $typeAkuntansi
     * @return \Illuminate\Http\Response
     */
    public function destroy(TypeAkuntansi $typeAkuntansi)
    {
        //
    }
}
