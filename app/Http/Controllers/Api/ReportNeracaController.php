<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\LogTrail;
use App\Models\ReportNeraca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportNeracaController extends Controller
{
   /**
     * @ClassName
     */
    public function index()
    {
        $reportAll = new ReportNeraca();
   
    }
    public function report(Request $request)
    {
        $tgldr = $request->tgldr;
        $tglsd = $request->tglsd;
        $coadr = $request->coadr;
        $coasd = $request->coasd;
        $report = ReportNeraca::getReport($tgldr, $tglsd, $coadr,$coasd);
        return response([
            'data' => $report
        ]);
    }
}

