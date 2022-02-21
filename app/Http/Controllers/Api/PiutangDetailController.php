<?php

namespace App\Http\Controllers;

use App\Models\PiutangDetail;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Http\Requests\UpdatePiutangDetailRequest;

class PiutangDetailController extends Controller
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
     * @param  \App\Http\Requests\StorePiutangDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePiutangDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PiutangDetail  $piutangDetail
     * @return \Illuminate\Http\Response
     */
    public function show(PiutangDetail $piutangDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PiutangDetail  $piutangDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(PiutangDetail $piutangDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePiutangDetailRequest  $request
     * @param  \App\Models\PiutangDetail  $piutangDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePiutangDetailRequest $request, PiutangDetail $piutangDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PiutangDetail  $piutangDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(PiutangDetail $piutangDetail)
    {
        //
    }
}
