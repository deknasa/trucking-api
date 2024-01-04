<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExportLaporanKasGantung;
use App\Http\Requests\ValidasiExportKasHarianRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportLaporanKasGantungController extends Controller
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
    public function export(ValidasiExportKasHarianRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $sampai = $request->periode;
            $jenis = $request->bank_id;


            $export = ExportLaporanKasGantung::getExport($sampai, $jenis);

          

            // foreach ($export[0] as $data) {
            //     $data->tgl = date('d-m-Y', strtotime($data->tgl));
            // }

            return response([
                'data' => $export[0],
                'dataDua' => $export[1],
            ]);
        }
    }
}
