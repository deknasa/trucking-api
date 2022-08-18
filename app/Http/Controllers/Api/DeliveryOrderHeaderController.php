<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrderHeader;
use App\Http\Requests\StoreDeliveryOrderHeaderRequest;
use App\Http\Requests\UpdateDeliveryOrderHeaderRequest;

class DeliveryOrderHeaderController extends Controller
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
     * @param  \App\Http\Requests\StoreDeliveryOrderHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDeliveryOrderHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeliveryOrderHeader  $deliveryOrderHeader
     * @return \Illuminate\Http\Response
     */
    public function show(DeliveryOrderHeader $deliveryOrderHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeliveryOrderHeader  $deliveryOrderHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(DeliveryOrderHeader $deliveryOrderHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDeliveryOrderHeaderRequest  $request
     * @param  \App\Models\DeliveryOrderHeader  $deliveryOrderHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDeliveryOrderHeaderRequest $request, DeliveryOrderHeader $deliveryOrderHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeliveryOrderHeader  $deliveryOrderHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeliveryOrderHeader $deliveryOrderHeader)
    {
        //
    }
}
