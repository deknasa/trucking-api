<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreAcosRequest;
use App\Http\Requests\UpdateAcosRequest;
use App\Http\Requests\DestroyAcosRequest;

use App\Http\Controllers\Controller;

class AcosController extends Controller
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
     * @param  \App\Http\Requests\StoreAcosRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAcosRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Acos  $acos
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Acos  $acos
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAcosRequest  $request
     * @param  \App\Models\Acos  $acos
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAcosRequest $request, Acos $acos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Acos  $acos
     * @return \Illuminate\Http\Response
     */
    public function destroy(Acos $acos)
    {
        //
    }
}
