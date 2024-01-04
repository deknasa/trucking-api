<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\TarikDataAbsensi;
use App\Models\LaporanDataJurnal;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanDataJurnalRequest;

class TarikDataAbsensiController extends Controller
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
            $tarikDataAbsensi = new TarikDataAbsensi();

            if (count($tarikDataAbsensi->getReport()) === 0) {
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
          
            $laporanbukubesar = new TarikDataAbsensi();

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
        $tarikDataAbsensi = new TarikDataAbsensi();

        return response([
            'data' => $tarikDataAbsensi->getReport(),
        ]);
        
        
    }
}
