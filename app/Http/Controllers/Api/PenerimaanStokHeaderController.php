<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanStokHeader;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanStokHeaderRequest;
use App\Http\Requests\UpdatePenerimaanStokHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanStokHeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $penerimaanStokHeader = new PenerimaanStokHeader();
        return response([
            'data' => $penerimaanStokHeader->get(),
            'attributes' => [
                'totalRows' => $penerimaanStokHeader->totalRows,
                'totalPages' => $penerimaanStokHeader->totalPages
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
     * @param  \App\Http\Requests\StorePenerimaanStokHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePenerimaanStokHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PenerimaanStokHeader  $penerimaanStokHeader
     * @return \Illuminate\Http\Response
     */
    public function show(PenerimaanStokHeader $penerimaanStokHeader,$id)
    {
        return response([
            'status' => true,
            'data' => $penerimaanStokHeader->find($id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PenerimaanStokHeader  $penerimaanStokHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(PenerimaanStokHeader $penerimaanStokHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePenerimaanStokHeaderRequest  $request
     * @param  \App\Models\PenerimaanStokHeader  $penerimaanStokHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePenerimaanStokHeaderRequest $request, PenerimaanStokHeader $penerimaanStokHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PenerimaanStokHeader  $penerimaanStokHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(PenerimaanStokHeader $penerimaanStokHeader)
    {
        //
    }
}
