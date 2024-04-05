<?php

namespace App\Http\Controllers\Api;

use App\Events\LaporanNeracaEventPusher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanNeracaRequest;
use App\Models\Cabang;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanNeraca;

class LaporanNeracaController extends Controller
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
    public function report(ValidasiLaporanNeracaRequest $request)
    {
        // event(new LaporanNeracaEventPusher(json_encode([
        //     'id' => auth('api')->user()->id,
        // ])));
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $sampai = $request->sampai;
            $eksport = 0;
            $cabang_id = $request->cabang_id ?? 0;
            $cabang = Cabang::find($request->cabang_id);
            $dataHeader = [
                'cabang' => ($cabang == '') ? '' : 'CABANG ' . $cabang->namacabang
            ];
            $report = LaporanNeraca::getReport($sampai, $eksport, $cabang_id);
            // sleep(5);

            return response([
                'data' => $report,
                'dataheader' => $dataHeader
            ]);

            // return response([
            //     'data' => 'asdf',
            // ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(ValidasiLaporanNeracaRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $sampai = $request->sampai;
            $eksport = 0;
            $cabang_id = $request->cabang_id ?? 0;
            $cabang = Cabang::find($request->cabang_id);
            $dataHeader = [
                'cabang' => ($cabang == '') ? '' : 'CABANG ' . $cabang->namacabang
            ];

            $export = LaporanNeraca::getReport($sampai, $eksport, $cabang_id);

            return response([
                'data' => $export,
                'dataheader' => $dataHeader
            ]);
        }
    }
}
