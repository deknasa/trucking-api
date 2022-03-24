<?php

namespace App\Http\Controllers;

use App\Models\PinjamanDetail;
use App\Http\Requests\StorePinjamanDetailRequest;
use App\Http\Requests\UpdatePinjamanDetailRequest;

class PinjamanDetailController extends Controller
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
     * @param  \App\Http\Requests\StorePinjamanDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePinjamanDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PinjamanDetail  $pinjamanDetail
     * @return \Illuminate\Http\Response
     */
    public function show(PinjamanDetail $pinjamanDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PinjamanDetail  $pinjamanDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(PinjamanDetail $pinjamanDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePinjamanDetailRequest  $request
     * @param  \App\Models\PinjamanDetail  $pinjamanDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePinjamanDetailRequest $request, PinjamanDetail $pinjamanDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PinjamanDetail  $pinjamanDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(PinjamanDetail $pinjamanDetail)
    {
        //
    }
}
