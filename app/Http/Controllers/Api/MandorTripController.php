<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\MandorTrip;
use App\Http\Requests\StoreMandorTripRequest;
use App\Http\Requests\UpdateMandorTripRequest;

class MandorTripController extends Controller
{
    /**
     * @ClassName 
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
     * @ClassName 
     */
    public function store(StoreMandorTripRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MandorTrip  $mandorTrip
     * @return \Illuminate\Http\Response
     */
    public function show(MandorTrip $mandorTrip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MandorTrip  $mandorTrip
     * @return \Illuminate\Http\Response
     */
    public function edit(MandorTrip $mandorTrip)
    {
        //
    }

    /**
     * @ClassName 
     */
    public function update(UpdateMandorTripRequest $request, MandorTrip $mandorTrip)
    {
        //
    }

    /**
     * @ClassName 
     */
    public function destroy(MandorTrip $mandorTrip)
    {
        //
    }
}
