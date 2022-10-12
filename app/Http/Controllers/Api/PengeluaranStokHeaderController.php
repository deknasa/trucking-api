<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranStokHeader;

use App\Http\Requests\StorePengeluaranStokHeaderRequest;
use App\Http\Requests\UpdatePengeluaranStokHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengeluaranStokHeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pengeluaranStokHeader = new PengeluaranStokHeader();
        return response([
            'data' => $pengeluaranStokHeader->get(),
            'attributes' => [
                'totalRows' => $pengeluaranStokHeader->totalRows,
                'totalPages' => $pengeluaranStokHeader->totalPages
            ]
        ]);
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
     * @param  \App\Http\Requests\StorePengeluaranStokHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePengeluaranStokHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PengeluaranStokHeader  $pengeluaranStokHeader
     * @return \Illuminate\Http\Response
     */
    public function show(PengeluaranStokHeader $pengeluaranStokHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PengeluaranStokHeader  $pengeluaranStokHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(PengeluaranStokHeader $pengeluaranStokHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePengeluaranStokHeaderRequest  $request
     * @param  \App\Models\PengeluaranStokHeader  $pengeluaranStokHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePengeluaranStokHeaderRequest $request, PengeluaranStokHeader $pengeluaranStokHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PengeluaranStokHeader  $pengeluaranStokHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(PengeluaranStokHeader $pengeluaranStokHeader)
    {
        //
    }
}
