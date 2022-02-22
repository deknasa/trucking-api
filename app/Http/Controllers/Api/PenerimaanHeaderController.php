<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanHeader;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;

class PenerimaanHeaderController extends Controller
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
     * @param  \App\Http\Requests\StorePenerimaanHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePenerimaanHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PenerimaHeader  $penerimaHeader
     * @return \Illuminate\Http\Response
     */
    public function show(PenerimaanHeader $penerimaanHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PenerimaHeader  $penerimaHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(PenerimaanHeader $penerimaanHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePenerimaHeaderRequest  $request
     * @param  \App\Models\PenerimaHeader  $penerimaHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePenerimaanHeaderRequest $request, PenerimaanHeader $penerimaanHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PenerimaHeader  $penerimaHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(PenerimaanHeader $penerimaanHeader)
    {
        //
    }
}
