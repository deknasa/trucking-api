<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\HutangExtraDetail;
use App\Http\Requests\StoreHutangExtraDetailRequest;
use App\Http\Requests\UpdateHutangExtraDetailRequest;

class HutangExtraDetailController extends Controller
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
     * @param  \App\Http\Requests\StoreHutangExtraDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreHutangExtraDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HutangExtraDetail  $hutangExtraDetail
     * @return \Illuminate\Http\Response
     */
    public function show(HutangExtraDetail $hutangExtraDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HutangExtraDetail  $hutangExtraDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(HutangExtraDetail $hutangExtraDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateHutangExtraDetailRequest  $request
     * @param  \App\Models\HutangExtraDetail  $hutangExtraDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateHutangExtraDetailRequest $request, HutangExtraDetail $hutangExtraDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HutangExtraDetail  $hutangExtraDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(HutangExtraDetail $hutangExtraDetail)
    {
        //
    }
}
