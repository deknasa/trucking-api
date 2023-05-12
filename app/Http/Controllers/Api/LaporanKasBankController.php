<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKasBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKasBankController extends Controller
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
    public function report(Request $request)
    {
        $dari = $request->dari;
        $sampai = $request->sampai;
        $bank_id = $request->bankid;

        $laporankasbank=new LaporanKasBank();
        return response([
            'data' => $laporankasbank->getReport($dari,$sampai, $bank_id) 
        ]);
    }

    /**
     * @ClassName
     */
    public function export(Request $request)
    {
        $dari = $request->dari;
        $sampai = $request->sampai;
        $bank_id = $request->bankid;

        $laporankasbank=new LaporanKasBank();                
        return response([
            'data' => $laporankasbank->getReport($dari,$sampai, $bank_id)  
        ]);
    }
}
