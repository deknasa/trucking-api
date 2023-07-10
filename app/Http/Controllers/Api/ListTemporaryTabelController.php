<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\ListTemporaryTabel;
use App\Http\Requests\StoreListTemporaryTabelRequest;
use App\Http\Requests\UpdateListTemporaryTabelRequest;

class ListTemporaryTabelController extends Controller
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
     * @param  \App\Http\Requests\StoreListTemporaryTabelRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreListTemporaryTabelRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ListTemporaryTabel  $listTemporaryTabel
     * @return \Illuminate\Http\Response
     */
    public function show(ListTemporaryTabel $listTemporaryTabel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ListTemporaryTabel  $listTemporaryTabel
     * @return \Illuminate\Http\Response
     */
    public function edit(ListTemporaryTabel $listTemporaryTabel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateListTemporaryTabelRequest  $request
     * @param  \App\Models\ListTemporaryTabel  $listTemporaryTabel
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateListTemporaryTabelRequest $request, ListTemporaryTabel $listTemporaryTabel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ListTemporaryTabel  $listTemporaryTabel
     * @return \Illuminate\Http\Response
     */
    public function destroy(ListTemporaryTabel $listTemporaryTabel)
    {
        //
    }
}
