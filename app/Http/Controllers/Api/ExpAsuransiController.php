<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpAsuransi;

class ExpAsuransiController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $expAsuransi = new ExpAsuransi();
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => $expAsuransi->totalRows,
                'totalPages' => $expAsuransi->totalPages
            ]
        ]);
    }

}