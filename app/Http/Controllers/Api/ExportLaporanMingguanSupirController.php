<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanMingguanSupirRequest;
use Illuminate\Http\Request;
use App\Models\ExportLaporanMingguanSupir;

class ExportLaporanMingguanSupirController extends Controller
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(ValidasiLaporanMingguanSupirRequest $request)
    {
        // if ($request->isCheck) {
        //     return response([
        //         'data' => 'ok'
        //     ]);
        // } else {

            $dari = $request->dari;
            $sampai = $request->sampai;
            $tradodari = $request->tradodari_id;
            $tradosampai = $request->tradosampai_id;


            $export = ExportLaporanMingguanSupir::getExport($dari,$sampai,$tradodari,$tradosampai);

           
            foreach ($export as $data) {
                $data->tglbukti = date('d-m-Y', strtotime($data->tglbukti));
            }

            return response([
                'data' => $export,
                
            ]);
        // }
       
    }

}
