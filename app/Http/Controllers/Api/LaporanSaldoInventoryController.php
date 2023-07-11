<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\LaporanSaldoInventory;
use App\Http\Requests\StoreLaporanSaldoInventoryRequest;
use App\Http\Requests\UpdateLaporanSaldoInventoryRequest;

class LaporanSaldoInventoryController extends Controller
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
     * @param  \App\Http\Requests\StoreLaporanSaldoInventoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLaporanSaldoInventoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LaporanSaldoInventory  $laporanSaldoInventory
     * @return \Illuminate\Http\Response
     */
    public function show(LaporanSaldoInventory $laporanSaldoInventory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LaporanSaldoInventory  $laporanSaldoInventory
     * @return \Illuminate\Http\Response
     */
    public function edit(LaporanSaldoInventory $laporanSaldoInventory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLaporanSaldoInventoryRequest  $request
     * @param  \App\Models\LaporanSaldoInventory  $laporanSaldoInventory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLaporanSaldoInventoryRequest $request, LaporanSaldoInventory $laporanSaldoInventory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LaporanSaldoInventory  $laporanSaldoInventory
     * @return \Illuminate\Http\Response
     */
    public function destroy(LaporanSaldoInventory $laporanSaldoInventory)
    {
        //
    }
}
