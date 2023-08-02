<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LogAbsensiController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        $tgldari=date('Y-m-d', strtotime($request->tgldari));
        $tglsampai=date('Y-m-d', strtotime($request->tglsampai));
        $logAbsensi = new LogAbsensi();
        return response([
            'data' => $logAbsensi->get($tgldari,$tglsampai),
            'attributes' => [
                'totalRows' => $logAbsensi->totalRows,
                'totalPages' => $logAbsensi->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function export()
    {
    }

    /**
     * @ClassName 
     */
    public function report()
    {
    }
}
