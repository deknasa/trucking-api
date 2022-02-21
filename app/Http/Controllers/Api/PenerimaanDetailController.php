<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanDetail;
use App\Http\Requests\StorePenerimaanDetailRequest;
use App\Http\Requests\UpdatePenerimaanDetailRequest;

class PenerimaanDetailController extends Controller
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
     * @param  \App\Http\Requests\StorePenerimaDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePenerimaanDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PenerimaDetail  $penerimaDetail
     * @return \Illuminate\Http\Response
     */
    public function show(PenerimaanDetail $penerimaanDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PenerimaDetail  $penerimaDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(PenerimaanDetail $penerimaanDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePenerimaDetailRequest  $request
     * @param  \App\Models\PenerimaDetail  $penerimaDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePenerimaanDetailRequest $request, PenerimaanDetail $penerimaanDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PenerimaDetail  $penerimaDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(PenerimaanDetail $penerimaanDetail)
    {
        //
    }
}
