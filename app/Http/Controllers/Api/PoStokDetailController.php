<?php

namespace App\Http\Controllers;

use App\Models\PoStokDetail;
use App\Http\Requests\StorePoStokDetailRequest;
use App\Http\Requests\UpdatePoStokDetailRequest;

class PoStokDetailController extends Controller
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
     * @param  \App\Http\Requests\StorePoStokDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePoStokDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PoStokDetail  $poStokDetail
     * @return \Illuminate\Http\Response
     */
    public function show(PoStokDetail $poStokDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PoStokDetail  $poStokDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(PoStokDetail $poStokDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePoStokDetailRequest  $request
     * @param  \App\Models\PoStokDetail  $poStokDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePoStokDetailRequest $request, PoStokDetail $poStokDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PoStokDetail  $poStokDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(PoStokDetail $poStokDetail)
    {
        //
    }
}
