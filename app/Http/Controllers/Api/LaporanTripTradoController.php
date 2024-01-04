<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanTripTrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanTripTradoController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $sampai = $request->sampai;
        $dari = $request->dari;

        $report = LaporanTripTrado::getReport($sampai, $dari);
        // $report = [
        //     [
        //         'nopol' => 'BK 2141 PK',
        //         'tripfull' => '6',
        //         'tripempty' => '4',
        //         'supir' => 'HERMAN',
        //         'full' => '2',
        //         'empty' => '1',
        //         'keterangan' => 'TES KETERANGAN'
        //     ],
            
        //     [
        //         'nopol' => 'BK 2415 ABS',
        //         'tripfull' => '4',
        //         'tripempty' => '3',
        //         'supir' => 'ANDIKA',
        //         'full' => '1',
        //         'empty' => '2',
        //         'keterangan' => 'TES KETERANGAN 2'
        //     ]
        // ];
        return response([
            'data' => $report
        ]);

    }

      /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
        {
            $dari = $request->dari;
            $sampai = $request->sampai;
    
            $export = LaporanTripTrado::getReport($dari,$sampai);
    
    
            return response([
                'data' => $export
            ]);
        }
}
