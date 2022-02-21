<?php

namespace App\Http\Controllers;

use App\Models\JenisOrder;
use App\Http\Requests\StoreJenisOrderRequest;
use App\Http\Requests\UpdateJenisOrderRequest;

class JenisOrderController extends Controller
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
     * @param  \App\Http\Requests\StoreJenisOrderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJenisOrderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
    public function show(JenisOrder $jenisOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(JenisOrder $jenisOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJenisOrderRequest  $request
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJenisOrderRequest $request, JenisOrder $jenisOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(JenisOrder $jenisOrder)
    {
        //
    }
}
