<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KartuStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuStokController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {

            $kartuStok = new KartuStok();

            return response([
                'data' => $kartuStok->get(),
                'attributes' => [
                    'totalRows' => $kartuStok->totalRows,
                    'totalPages' => $kartuStok->totalPages
                ]
            ]);
    }
    
    public function report(Request $request)
    {
        $stokdari_id = $request->stokdari_id;
        $stoksampai_id = $request->stoksampai_id;
        $dari = $request->dari;
        $sampai = $request->sampai;
        $filter = $request->filter;
        $datafilter = $request->datafilter;
        
        $report = KartuStok::getReport($stokdari_id, $stoksampai_id, $dari,$sampai, $filter, $datafilter);
        return response([
            'data' => $report
        ]);
    }
}
