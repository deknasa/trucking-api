<?php

namespace App\Http\Controllers;

use App\Models\ServiceInDetail;
use App\Http\Requests\StoreServiceInDetailRequest;
use App\Http\Requests\UpdateServiceInDetailRequest;

class ServiceInDetailController extends Controller
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
     * @param  \App\Http\Requests\StoreServiceInDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServiceInDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ServiceInDetail  $serviceInDetail
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceInDetail $serviceInDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ServiceInDetail  $serviceInDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceInDetail $serviceInDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateServiceInDetailRequest  $request
     * @param  \App\Models\ServiceInDetail  $serviceInDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServiceInDetailRequest $request, ServiceInDetail $serviceInDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ServiceInDetail  $serviceInDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceInDetail $serviceInDetail)
    {
        //
    }
}
