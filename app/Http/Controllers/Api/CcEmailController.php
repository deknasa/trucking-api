<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\CcEmail;
use App\Http\Requests\StoreCcEmailRequest;
use App\Http\Requests\UpdateCcEmailRequest;

class CcEmailController extends Controller
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
     * @param  \App\Http\Requests\StoreCcEmailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCcEmailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CcEmail  $ccEmail
     * @return \Illuminate\Http\Response
     */
    public function show(CcEmail $ccEmail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CcEmail  $ccEmail
     * @return \Illuminate\Http\Response
     */
    public function edit(CcEmail $ccEmail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCcEmailRequest  $request
     * @param  \App\Models\CcEmail  $ccEmail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCcEmailRequest $request, CcEmail $ccEmail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CcEmail  $ccEmail
     * @return \Illuminate\Http\Response
     */
    public function destroy(CcEmail $ccEmail)
    {
        //
    }
}
