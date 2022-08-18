<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrderDetail;
use App\Http\Requests\StoreDeliveryOrderDetailRequest;
use App\Http\Requests\UpdateDeliveryOrderDetailRequest;

class DeliveryOrderDetailController extends Controller
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
     * @param  \App\Http\Requests\StoreDeliveryOrderDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDeliveryOrderDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeliveryOrderDetail  $deliveryOrderDetail
     * @return \Illuminate\Http\Response
     */
    public function show(DeliveryOrderDetail $deliveryOrderDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeliveryOrderDetail  $deliveryOrderDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(DeliveryOrderDetail $deliveryOrderDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDeliveryOrderDetailRequest  $request
     * @param  \App\Models\DeliveryOrderDetail  $deliveryOrderDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDeliveryOrderDetailRequest $request, DeliveryOrderDetail $deliveryOrderDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeliveryOrderDetail  $deliveryOrderDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeliveryOrderDetail $deliveryOrderDetail)
    {
        //
    }
}
