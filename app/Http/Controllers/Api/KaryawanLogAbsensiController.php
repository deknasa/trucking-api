<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\KaryawanLogAbsensi;
use App\Http\Requests\StoreKaryawanLogAbsensiRequest;
use App\Http\Requests\UpdateKaryawanLogAbsensiRequest;

class KaryawanLogAbsensiController extends Controller
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
     * @param  \App\Http\Requests\StoreKaryawanLogAbsensiRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreKaryawanLogAbsensiRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\KaryawanLogAbsensi  $karyawanLogAbsensi
     * @return \Illuminate\Http\Response
     */
    public function show(KaryawanLogAbsensi $karyawanLogAbsensi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\KaryawanLogAbsensi  $karyawanLogAbsensi
     * @return \Illuminate\Http\Response
     */
    public function edit(KaryawanLogAbsensi $karyawanLogAbsensi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateKaryawanLogAbsensiRequest  $request
     * @param  \App\Models\KaryawanLogAbsensi  $karyawanLogAbsensi
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateKaryawanLogAbsensiRequest $request, KaryawanLogAbsensi $karyawanLogAbsensi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\KaryawanLogAbsensi  $karyawanLogAbsensi
     * @return \Illuminate\Http\Response
     */
    public function destroy(KaryawanLogAbsensi $karyawanLogAbsensi)
    {
        //
    }
}
