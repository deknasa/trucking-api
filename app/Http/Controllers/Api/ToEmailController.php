<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\ToEmail;
use App\Http\Requests\StoreToEmailRequest;
use App\Http\Requests\UpdateToEmailRequest;

class ToEmailController extends Controller
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
     * @param  \App\Http\Requests\StoreToEmailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreToEmailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ToEmail  $toEmail
     * @return \Illuminate\Http\Response
     */
    public function show(ToEmail $toEmail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ToEmail  $toEmail
     * @return \Illuminate\Http\Response
     */
    public function edit(ToEmail $toEmail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateToEmailRequest  $request
     * @param  \App\Models\ToEmail  $toEmail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateToEmailRequest $request, ToEmail $toEmail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ToEmail  $toEmail
     * @return \Illuminate\Http\Response
     */
    public function destroy(ToEmail $toEmail)
    {
        //
    }
}
