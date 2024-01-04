<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\LaporanDataJurnal;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanDataJurnalRequest;

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

        return response([
            'data' => $laporanDataJurnal->getReport(),
        ]);
        
        
    }
}
