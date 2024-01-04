<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpAsuransi;

class ExpAsuransiController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $expAsuransi = new ExpAsuransi();
        return response([
            'data' => $expAsuransi->get(),
            'attributes' => [
                'totalRows' => $expAsuransi->totalRows,
                'totalPages' => $expAsuransi->totalPages
            ]
        ]);
    }

}