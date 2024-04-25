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
            $jenis = 1;//kas bank


            $export = ExportLaporanKasGantung::getExport($sampai, $jenis);

          
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            // foreach ($export[0] as $data) {
            //     $data->tgl = date('d-m-Y', strtotime($data->tgl));
            // }

            return response([
                'data' => $export[0],
                'dataDua' => $export[1],
                'namacabang' => 'CABANG ' . $getCabang->namacabang
            ]);
        }
    }
}
