<?php

namespace App\Http\Controllers;

use App\Models\PindahBuku;
use App\Http\Requests\StorePindahBukuRequest;
use App\Http\Requests\UpdatePindahBukuRequest;

class PindahBukuController extends Controller
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
     * @param  \App\Http\Requests\StorePindahBukuRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePindahBukuRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PindahBuku  $pindahBuku
     * @return \Illuminate\Http\Response
     */
    public function show(PindahBuku $pindahBuku)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PindahBuku  $pindahBuku
     * @return \Illuminate\Http\Response
     */
    public function edit(PindahBuku $pindahBuku)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePindahBukuRequest  $request
     * @param  \App\Models\PindahBuku  $pindahBuku
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePindahBukuRequest $request, PindahBuku $pindahBuku)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PindahBuku  $pindahBuku
     * @return \Illuminate\Http\Response
     */
    public function destroy(PindahBuku $pindahBuku)
    {
        //
    }
}
