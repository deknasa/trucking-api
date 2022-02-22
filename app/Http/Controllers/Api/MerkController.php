<?php

namespace App\Http\Controllers;

use App\Models\Merk;
use App\Http\Requests\StoreMerkRequest;
use App\Http\Requests\UpdateMerkRequest;

class MerkController extends Controller
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
     * @param  \App\Http\Requests\StoreMerkRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMerkRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Merk  $merk
     * @return \Illuminate\Http\Response
     */
    public function show(Merk $merk)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Merk  $merk
     * @return \Illuminate\Http\Response
     */
    public function edit(Merk $merk)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMerkRequest  $request
     * @param  \App\Models\Merk  $merk
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMerkRequest $request, Merk $merk)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Merk  $merk
     * @return \Illuminate\Http\Response
     */
    public function destroy(Merk $merk)
    {
        //
    }
}
