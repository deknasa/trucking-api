<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\LaporanDataJurnal;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanDataJurnalRequest;
use Illuminate\Support\Facades\DB;

class LaporanDataJurnalController extends Controller
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
    public function report(ValidasiLaporanDataJurnalRequest $request)
    {
        if ($request->isCheck) {
            $laporanDataJurnal = new LaporanDataJurnal();

            if (count($laporanDataJurnal->getReport()) === 0) {
                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],

                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'data' => 'ok'
                ]);
            }
        } else {
          
            $laporanbukubesar = new LaporanDataJurnal();

            return response([
                'data' => $laporanbukubesar->getReport(),
            ]);
        }
    }
    
    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(ValidasiLaporanDataJurnalRequest $request)
    {
        $laporanDataJurnal = new LaporanDataJurnal();

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
        ->select('cabang.namacabang')
        ->join("parameter", 'parameter.text', 'cabang.id')
        ->where('parameter.grp', 'ID CABANG')
        ->first();
        return response([
            'data' => $laporanDataJurnal->getReport(),
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
        
        
    }
}
