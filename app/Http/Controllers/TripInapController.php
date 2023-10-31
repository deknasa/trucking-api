<?php

namespace App\Http\Controllers;

use App\Models\TripInap;
use App\Http\Requests\StoreTripInapRequest;
use App\Http\Requests\UpdateTripInapRequest;

class TripInapController extends Controller
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
     * @param  \App\Http\Requests\StoreTripInapRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTripInapRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripInap  $tripInap
     * @return \Illuminate\Http\Response
     */
    public function show(TripInap $tripInap)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TripInap  $tripInap
     * @return \Illuminate\Http\Response
     */
    public function edit(TripInap $tripInap)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTripInapRequest  $request
     * @param  \App\Models\TripInap  $tripInap
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTripInapRequest $request, TripInap $tripInap)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripInap  $tripInap
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripInap $tripInap)
    {
        //
    }
}
