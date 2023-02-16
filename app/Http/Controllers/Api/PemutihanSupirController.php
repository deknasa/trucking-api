<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PemutihanSupir;
use App\Http\Requests\StorePemutihanSupirRequest;
use App\Http\Requests\UpdatePemutihanSupirRequest;
use Illuminate\Support\Facades\DB;

class PemutihanSupirController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $pemutihanSupir = new PemutihanSupir();
        return response([
            'data' => $pemutihanSupir->get(),
            'attributes' => [
                'totalRows' => $pemutihanSupir->totalRows,
                'totalPages' => $pemutihanSupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePemutihanSupirRequest $request)
    {
        //
    }


    public function show(PemutihanSupir $pemutihanSupir)
    {
        //
    }

    /**
     * @ClassName
     */
    public function update(UpdatePemutihanSupirRequest $request, PemutihanSupir $pemutihanSupir)
    {
        //
    }

    /**
     * @ClassName
     */
    public function destroy(PemutihanSupir $pemutihanSupir)
    {
        //
    }
    
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pemutihansupir')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
