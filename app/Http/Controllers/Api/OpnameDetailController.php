<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\OpnameDetail;
use App\Http\Requests\StoreOpnameDetailRequest;
use App\Http\Requests\UpdateOpnameDetailRequest;

class OpnameDetailController extends Controller
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
     * @param  \App\Http\Requests\StoreOpnameDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOpnameDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OpnameDetail  $opnameDetail
     * @return \Illuminate\Http\Response
     */
    public function show(OpnameDetail $opnameDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OpnameDetail  $opnameDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(OpnameDetail $opnameDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOpnameDetailRequest  $request
     * @param  \App\Models\OpnameDetail  $opnameDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOpnameDetailRequest $request, OpnameDetail $opnameDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OpnameDetail  $opnameDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(OpnameDetail $opnameDetail)
    {
        //
    }
}
