<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpSim;

class ExpSimController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $expSim = new ExpSim();
        return response([
            'data' => $expSim->get(),
            'attributes' => [
                'totalRows' => $expSim->totalRows,
                'totalPages' => $expSim->totalPages
            ]
        ]);
    }

}