<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BiayaExtraSupirDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BiayaExtraSupirDetailController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $biayaExtraSupirDetail = new BiayaExtraSupirDetail();


        return response([
            'data' => $biayaExtraSupirDetail->get(),
            'attributes' => [
                'totalRows' => $biayaExtraSupirDetail->totalRows,
                'totalPages' => $biayaExtraSupirDetail->totalPages,
                'totalNominal' => $biayaExtraSupirDetail->totalNominal,
            ]
        ]);
    }


}