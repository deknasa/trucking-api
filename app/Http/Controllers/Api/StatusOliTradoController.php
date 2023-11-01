<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StatusOliTrado;

class StatusOliTradoController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $statusOli = new StatusOliTrado();
        return response([
            'data' => $statusOli->get(),
            'attributes' => [
                'totalRows' => $statusOli->totalRows,
                'totalPages' => $statusOli->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function export()
    {
    }
}
