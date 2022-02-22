<?php

namespace App\Http\Controllers;

use App\Models\StatusContainer;
use App\Http\Requests\StoreStatusContainerRequest;
use App\Http\Requests\UpdateStatusContainerRequest;

class StatusContainerController extends Controller
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
     * @param  \App\Http\Requests\StoreStatusContainerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStatusContainerRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StatusContainer  $statusContainer
     * @return \Illuminate\Http\Response
     */
    public function show(StatusContainer $statusContainer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StatusContainer  $statusContainer
     * @return \Illuminate\Http\Response
     */
    public function edit(StatusContainer $statusContainer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStatusContainerRequest  $request
     * @param  \App\Models\StatusContainer  $statusContainer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStatusContainerRequest $request, StatusContainer $statusContainer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StatusContainer  $statusContainer
     * @return \Illuminate\Http\Response
     */
    public function destroy(StatusContainer $statusContainer)
    {
        //
    }
}
