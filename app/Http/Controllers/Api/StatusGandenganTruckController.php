<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\StatusGandenganTruck;
use App\Http\Requests\StoreStatusGandenganTruckRequest;
use App\Http\Requests\UpdateStatusGandenganTruckRequest;

class StatusGandenganTruckController extends Controller
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
     * @param  \App\Http\Requests\StoreStatusGandenganTruckRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStatusGandenganTruckRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StatusGandenganTruck  $statusGandenganTruck
     * @return \Illuminate\Http\Response
     */
    public function show(StatusGandenganTruck $statusGandenganTruck)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StatusGandenganTruck  $statusGandenganTruck
     * @return \Illuminate\Http\Response
     */
    public function edit(StatusGandenganTruck $statusGandenganTruck)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStatusGandenganTruckRequest  $request
     * @param  \App\Models\StatusGandenganTruck  $statusGandenganTruck
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStatusGandenganTruckRequest $request, StatusGandenganTruck $statusGandenganTruck)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StatusGandenganTruck  $statusGandenganTruck
     * @return \Illuminate\Http\Response
     */
    public function destroy(StatusGandenganTruck $statusGandenganTruck)
    {
        //
    }
}
