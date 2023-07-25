<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanTripGandenganDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanTripGandenganDetailController extends Controller
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
    public function report(Request $request)
    {
        $dari = $request->dari;
        $sampai = $request->sampai;
        $gandengandari = $request->gandengandari;
        $gandengansampai = $request->gandengansampai;
        $gandengandari_id = $request->gandengandari_id;
        $gandengansampai_id = $request->gandengansampai_id;

        $laporantripgandengandetail = new LaporanTripGandenganDetail();
        // $report = LaporanTripGandenganDetail::getReport($sampai, $jenis);
        $report = [
            [
                'gandengan' => 'T2',
                'tanggal' => '23-2-2023',
                'nosp' => '2414215412',
                'supir' => 'HERMAN',
                'nocont' => '124512',
                'noplat' => 'BK 2159 ABS',
                'rute' => 'MEDAN-BELAWAN',
                'cont' => '20',
                'keterangan' => 'TES KETERANGAN RITASI'
            ]
        ];
        return response([
            // date('d-m-Y', strtotime($item->tanggal));
            // 'data' => $report
            'data' => $laporantripgandengandetail->getReport($gandengandari_id, $gandengansampai_id, date('Y-m-d', strtotime($dari)), date('Y-m-d', strtotime($sampai)))
        ]);
    }
     /**
     * @ClassName
     */
    public function export(Request $request)
    {
        $dari = $request->dari;
        $sampai = $request->sampai;
        $gandengandari = $request->gandengandari;
        $gandengansampai = $request->gandengansampai;
        $gandengandari_id = $request->gandengandari_id;
        $gandengansampai_id = $request->gandengansampai_id;

        $laporantripgandengandetail = new LaporanTripGandenganDetail();

        $dataTripGandengan = $laporantripgandengandetail->getReport($gandengandari_id, $gandengansampai_id, date('Y-m-d', strtotime($dari)), date('Y-m-d', strtotime($sampai)));


        return response([
            'data' => $dataTripGandengan
        ]);
    }
}
