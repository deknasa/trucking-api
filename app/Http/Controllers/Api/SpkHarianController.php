<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpkHarian;

class SpkHarianController extends Controller
{
     /**
     * @ClassName 
     * SpkHarianController
     * @Detail1 SpkHarianDetailController
     */
    public function index()
    {
        $spkHarian = new SpkHarian();
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => $spkHarian->totalRows,
                'totalPages' => $spkHarian->totalPages
            ]
        ]);
    }

}
