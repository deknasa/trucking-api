<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\tujuan;
use App\Http\Requests\StoretujuanRequest;
use App\Http\Requests\UpdatetujuanRequest;

class TujuanController extends Controller
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
     * @param  \App\Http\Requests\StoretujuanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoretujuanRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\tujuan  $tujuan
     * @return \Illuminate\Http\Response
     */
    public function show(tujuan $tujuan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\tujuan  $tujuan
     * @return \Illuminate\Http\Response
     */
    public function edit(tujuan $tujuan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatetujuanRequest  $request
     * @param  \App\Models\tujuan  $tujuan
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatetujuanRequest $request, tujuan $tujuan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\tujuan  $tujuan
     * @return \Illuminate\Http\Response
     */
    public function destroy(tujuan $tujuan)
    {
        //
    }
}
