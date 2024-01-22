<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\TarifHargaTertentu;
use App\Http\Requests\StoreTarifHargaTertentuRequest;
use App\Http\Requests\UpdateTarifHargaTertentuRequest;

class TarifHargaTertentuController extends Controller
{
      /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
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
     * @param  \App\Http\Requests\StoreTarifHargaTertentuRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTarifHargaTertentuRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TarifHargaTertentu  $tarifHargaTertentu
     * @return \Illuminate\Http\Response
     */
    public function show(TarifHargaTertentu $tarifHargaTertentu)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TarifHargaTertentu  $tarifHargaTertentu
     * @return \Illuminate\Http\Response
     */
    public function edit(TarifHargaTertentu $tarifHargaTertentu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTarifHargaTertentuRequest  $request
     * @param  \App\Models\TarifHargaTertentu  $tarifHargaTertentu
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTarifHargaTertentuRequest $request, TarifHargaTertentu $tarifHargaTertentu)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TarifHargaTertentu  $tarifHargaTertentu
     * @return \Illuminate\Http\Response
     */
    public function destroy(TarifHargaTertentu $tarifHargaTertentu)
    {
        //
    }
}
