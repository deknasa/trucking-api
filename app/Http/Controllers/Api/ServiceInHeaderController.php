<?php

namespace App\Http\Controllers;

use App\Models\ServiceInHeader;
use App\Http\Requests\StoreServiceInHeaderRequest;
use App\Http\Requests\UpdateServiceInHeaderRequest;

class ServiceInHeaderController extends Controller
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
     * @param  \App\Http\Requests\StoreServiceInHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServiceInHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ServiceInHeader  $serviceInHeader
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceInHeader $serviceInHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ServiceInHeader  $serviceInHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceInHeader $serviceInHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateServiceInHeaderRequest  $request
     * @param  \App\Models\ServiceInHeader  $serviceInHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServiceInHeaderRequest $request, ServiceInHeader $serviceInHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ServiceInHeader  $serviceInHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceInHeader $serviceInHeader)
    {
        //
    }
}
