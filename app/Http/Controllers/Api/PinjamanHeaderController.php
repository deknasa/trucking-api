<?php

namespace App\Http\Controllers;

use App\Models\PinjamanHeader;
use App\Http\Requests\StorePinjamanHeaderRequest;
use App\Http\Requests\UpdatePinjamanHeaderRequest;

class PinjamanHeaderController extends Controller
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
     * @param  \App\Http\Requests\StorePinjamanHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePinjamanHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PinjamanHeader  $pinjamanHeader
     * @return \Illuminate\Http\Response
     */
    public function show(PinjamanHeader $pinjamanHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PinjamanHeader  $pinjamanHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(PinjamanHeader $pinjamanHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePinjamanHeaderRequest  $request
     * @param  \App\Models\PinjamanHeader  $pinjamanHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePinjamanHeaderRequest $request, PinjamanHeader $pinjamanHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PinjamanHeader  $pinjamanHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(PinjamanHeader $pinjamanHeader)
    {
        //
    }
}
