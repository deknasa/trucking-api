<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\StokPusat;
use App\Http\Requests\StoreStokPusatRequest;
use App\Http\Requests\UpdateStokPusatRequest;

class StokPusatController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $stokPusat = new StokPusat();
        return response([
            'data' => $stokPusat->get(),
            'attributes' => [
                'totalRows' => $stokPusat->totalRows,
                'totalPages' => $stokPusat->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreStokPusatRequest $request)
    {
        //
    }


    public function show(StokPusat $stokPusat)
    {
        //
    }

    /**
     * @ClassName 
     */
    public function update(UpdateStokPusatRequest $request, StokPusat $stokPusat)
    {
        //
    }

    /**
     * @ClassName 
     */
    public function destroy(StokPusat $stokPusat)
    {
        //
    }
}
