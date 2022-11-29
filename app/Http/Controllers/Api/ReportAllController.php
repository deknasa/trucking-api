<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\LogTrail;
use App\Models\ReportAll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportAllController extends Controller
{

     /**
     * @ClassName
     */
    public function index()
    {
        $reportAll = new ReportAll();
   
    }
    public function report(Request $request)
    {
        
        $tgl = $request->tanggal;
        $table = $request->data;
        $report = ReportAll::getReport($tgl, $table);
        return response([
            'data' => $report
        ]);
    }
}