<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanMingguanSupirBedaMandor;
use Illuminate\Http\Request;

class LaporanMingguanSupirBedaMandorController extends Controller
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
    public function export(Request $request)
    {

        $dari = $request->dari;
        $sampai = $request->sampai;
        $tradodari = $request->tradodari_id;
        $tradosampai = $request->tradosampai_id;


        $export = LaporanMingguanSupirBedaMandor::getExport($dari, $sampai, $tradodari, $tradosampai);


        foreach ($export as $data) {
            $data->tglbukti = date('d-m-Y', strtotime($data->tglbukti));
        }

        return response([
            'data' => $export,

        ]);
    }
}
