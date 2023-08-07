<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpSim;

class ExpSimController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $expSim = new ExpSim();
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => $expSim->totalRows,
                'totalPages' => $expSim->totalPages
            ]
        ]);
    }

}