<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\OpnameHeader;
use App\Http\Requests\StoreOpnameHeaderRequest;
use App\Http\Requests\UpdateOpnameHeaderRequest;

class OpnameHeaderController extends Controller
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
     * @param  \App\Http\Requests\StoreOpnameHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOpnameHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OpnameHeader  $opnameHeader
     * @return \Illuminate\Http\Response
     */
    public function show(OpnameHeader $opnameHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OpnameHeader  $opnameHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(OpnameHeader $opnameHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOpnameHeaderRequest  $request
     * @param  \App\Models\OpnameHeader  $opnameHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOpnameHeaderRequest $request, OpnameHeader $opnameHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OpnameHeader  $opnameHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(OpnameHeader $opnameHeader)
    {
        //
    }
}
