<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanRitasiGandengan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanRitasiGandenganController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => 0,
                'totalPages' => 0
            ]
        ]);
    }
    
    /**
     * @ClassName
     */
    public function export(Request $request)
    {
        $periode = $request->periode;
        
        $export = new LaporanRitasiGandengan ();
        return response([
            'data' => $export->Export($periode)
        ]);
    }

    public function header(Request $request)
    {
        $periode = $request->periode;
        
        $export = new LaporanRitasiGandengan ();
        return response([
            'header' => $export->getHeader($periode)
        ]);
    }

}
