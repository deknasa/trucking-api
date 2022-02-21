<?php

namespace App\Http\Controllers;

use App\Models\PiutangHeader;
use App\Http\Requests\StorePiutangHeaderRequest;
use App\Http\Requests\UpdatePiutangHeaderRequest;

class PiutangHeaderController extends Controller
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
     * @param  \App\Http\Requests\StorePiutangHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePiutangHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PiutangHeader  $piutangHeader
     * @return \Illuminate\Http\Response
     */
    public function show(PiutangHeader $piutangHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PiutangHeader  $piutangHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(PiutangHeader $piutangHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePiutangHeaderRequest  $request
     * @param  \App\Models\PiutangHeader  $piutangHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePiutangHeaderRequest $request, PiutangHeader $piutangHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PiutangHeader  $piutangHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(PiutangHeader $piutangHeader)
    {
        //
    }
}
