<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpkHarian;

class SpkHarianController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $statusOli = new SpkHarian();
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => $statusOli->totalRows,
                'totalPages' => $statusOli->totalPages
            ]
        ]);
    }

}
