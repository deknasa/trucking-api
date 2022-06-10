<?php

namespace App\Http\Controllers;

use App\Models\ServiceOutDetail;
use App\Http\Requests\StoreServiceOutDetailRequest;
use App\Http\Requests\UpdateServiceOutDetailRequest;

class ServiceOutDetailController extends Controller
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
     * @param  \App\Http\Requests\StoreServiceOutDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServiceOutDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ServiceOutDetail  $serviceOutDetail
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceOutDetail $serviceOutDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ServiceOutDetail  $serviceOutDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceOutDetail $serviceOutDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateServiceOutDetailRequest  $request
     * @param  \App\Models\ServiceOutDetail  $serviceOutDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServiceOutDetailRequest $request, ServiceOutDetail $serviceOutDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ServiceOutDetail  $serviceOutDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceOutDetail $serviceOutDetail)
    {
        //
    }
}
