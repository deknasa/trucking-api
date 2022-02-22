<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Http\Requests\StoreAgenRequest;
use App\Http\Requests\UpdateAgenRequest;

class AgenController extends Controller
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
     * @param  \App\Http\Requests\StoreAgenRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAgenRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Agen  $agen
     * @return \Illuminate\Http\Response
     */
    public function show(Agen $agen)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Agen  $agen
     * @return \Illuminate\Http\Response
     */
    public function edit(Agen $agen)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAgenRequest  $request
     * @param  \App\Models\Agen  $agen
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAgenRequest $request, Agen $agen)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Agen  $agen
     * @return \Illuminate\Http\Response
     */
    public function destroy(Agen $agen)
    {
        //
    }
}
