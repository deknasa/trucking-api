<?php

namespace App\Http\Controllers;

use App\Models\Kerusakan;
use App\Http\Requests\StoreKerusakanRequest;
use App\Http\Requests\UpdateKerusakanRequest;

class KerusakanController extends Controller
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
     * @param  \App\Http\Requests\StoreKerusakanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreKerusakanRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Kerusakan  $kerusakan
     * @return \Illuminate\Http\Response
     */
    public function show(Kerusakan $kerusakan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Kerusakan  $kerusakan
     * @return \Illuminate\Http\Response
     */
    public function edit(Kerusakan $kerusakan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateKerusakanRequest  $request
     * @param  \App\Models\Kerusakan  $kerusakan
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateKerusakanRequest $request, Kerusakan $kerusakan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Kerusakan  $kerusakan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Kerusakan $kerusakan)
    {
        //
    }
}
