<?php

namespace App\Http\Controllers\Api;

use App\Events\LaporanNeracaEventPusher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanNeracaRequest;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanNeraca;

class LaporanNeracaController extends Controller
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

        $report = LaporanNeraca::getReport($sampai, $eksport);
        // sleep(5);

        return response([
            'data' => $report
        ]);

        // return response([
        //     'data' => 'asdf',
        // ]);
         }
    }

    /**
     * @ClassName
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

            $export = LaporanNeraca::getReport($sampai, $eksport);

            return response([
                'data' => $export
            ]);
        }
    }
}
