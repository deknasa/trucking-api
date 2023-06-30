<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanKasBankRequest;
use App\Models\LaporanKasBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKasBankController extends Controller
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
    public function report(ValidasiLaporanKasBankRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {
            $dari = $request->dari;
            $sampai = $request->sampai;
            $bank_id = $request->bank_id;

            $laporankasbank = new LaporanKasBank();
            return response([
                'data' => $laporankasbank->getReport($dari, $sampai, $bank_id)
            ]);
        }
    }

    /**
     * @ClassName
     */
    public function export(ValidasiLaporanKasBankRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {
            $dari = $request->dari;
            $sampai = $request->sampai;
            $bank_id = $request->bank_id;

            $laporankasbank = new LaporanKasBank();
            return response([
                'data' => $laporankasbank->getReport($dari, $sampai, $bank_id)
            ]);
        }
    }
}
