<?php

namespace App\Http\Controllers;

use App\Models\LaporanKasHarian;
use Illuminate\Http\Request;

class LaporanKasHarianController extends Controller
{
    public function index(){
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => 0,
                'totalPages' => 0
            ]
        ]);
    }

    public function export(Request $request)
    {
        
        $periode = $request->periode;
     
        $export = LaporanKasHarian::getReport($periode);

        foreach ($export as $data) {
           
            $data->tanggal = date('d-m-Y', strtotime($data->tanggal));
        }

        return response([
            'data' => $export
        ]);

       
    }
}
