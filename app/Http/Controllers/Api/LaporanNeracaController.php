<?php

namespace App\Http\Controllers\Api;

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
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $sampai = $request->sampai;

            $report = LaporanNeraca::getReport($sampai);

            return response([
                'data' => $report
            ]);
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

            $export = LaporanNeraca::getReport($sampai);

            return response([
                'data' => $export
            ]);
        }
    }
}
