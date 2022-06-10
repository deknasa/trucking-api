<?php

namespace App\Http\Controllers;

use App\Models\Mekanik;
use App\Http\Requests\StoreMekanikRequest;
use App\Http\Requests\UpdateMekanikRequest;

class MekanikController extends Controller
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
     * @param  \App\Http\Requests\StoreMekanikRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMekanikRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mekanik  $mekanik
     * @return \Illuminate\Http\Response
     */
    public function show(Mekanik $mekanik)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mekanik  $mekanik
     * @return \Illuminate\Http\Response
     */
    public function edit(Mekanik $mekanik)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMekanikRequest  $request
     * @param  \App\Models\Mekanik  $mekanik
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMekanikRequest $request, Mekanik $mekanik)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mekanik  $mekanik
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mekanik $mekanik)
    {
        //
    }
}
