<?php

namespace App\Http\Controllers;

use App\Models\ServiceOutHeader;
use App\Http\Requests\StoreServiceOutHeaderRequest;
use App\Http\Requests\UpdateServiceOutHeaderRequest;

class ServiceOutHeaderController extends Controller
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
     * @param  \App\Http\Requests\StoreServiceOutHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServiceOutHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ServiceOutHeader  $serviceOutHeader
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceOutHeader $serviceOutHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ServiceOutHeader  $serviceOutHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceOutHeader $serviceOutHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateServiceOutHeaderRequest  $request
     * @param  \App\Models\ServiceOutHeader  $serviceOutHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServiceOutHeaderRequest $request, ServiceOutHeader $serviceOutHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ServiceOutHeader  $serviceOutHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceOutHeader $serviceOutHeader)
    {
        //
    }
}
