<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\BccEmail;
use App\Http\Requests\StoreBccEmailRequest;
use App\Http\Requests\UpdateBccEmailRequest;

class BccEmailController extends Controller
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
     * @param  \App\Http\Requests\StoreBccEmailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBccEmailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BccEmail  $bccEmail
     * @return \Illuminate\Http\Response
     */
    public function show(BccEmail $bccEmail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BccEmail  $bccEmail
     * @return \Illuminate\Http\Response
     */
    public function edit(BccEmail $bccEmail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBccEmailRequest  $request
     * @param  \App\Models\BccEmail  $bccEmail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBccEmailRequest $request, BccEmail $bccEmail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BccEmail  $bccEmail
     * @return \Illuminate\Http\Response
     */
    public function destroy(BccEmail $bccEmail)
    {
        //
    }
}
