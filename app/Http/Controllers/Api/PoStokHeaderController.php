<?php

namespace App\Http\Controllers;

use App\Models\PoStokHeader;
use App\Http\Requests\StorePoStokHeaderRequest;
use App\Http\Requests\UpdatePoStokHeaderRequest;

class PoStokHeaderController extends Controller
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
     * @param  \App\Http\Requests\StorePoStokHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePoStokHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PoStokHeader  $poStokHeader
     * @return \Illuminate\Http\Response
     */
    public function show(PoStokHeader $poStokHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PoStokHeader  $poStokHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(PoStokHeader $poStokHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePoStokHeaderRequest  $request
     * @param  \App\Models\PoStokHeader  $poStokHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePoStokHeaderRequest $request, PoStokHeader $poStokHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PoStokHeader  $poStokHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(PoStokHeader $poStokHeader)
    {
        //
    }
}
