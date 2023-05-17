<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKartuHutangPerVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKartuHutangPerVendorController extends Controller
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
        $sampai = $request->sampai;
        $dari = $request->dari;

        $laporankartuhutangpervendor = new LaporanKartuHutangPerVendor();

        // $report = LaporanRekapSumbangan::getReport($sampai, $dari);
        $report = [
            [
              
                'suppdari_id' => 'PT. ACHUNG',
                'suppsampai_id' => 'PT. BAHAGIA BERSAMA',
                'namasupplier' => 'PT. ACHUNG',
                'nobukti' => 'INV 0001/II/2023',
                'tanggal' => '03-05-2023',
                'tgljt' => '01-10-2023',
                'cicilan' => '2',
                'nominal' => '2000000',
                'bayar' => '2000000',
                'saldo' => '3000000',
             

            ],
            [
                'suppdari_id' => 'PT. ATB',
                'suppsampai_id' => 'PT. BAHAGIA BERSAMA',
                'namasupplier' => 'PT. ATB',
                'nobukti' => 'INV 0002/II/2023',
                'tanggal' => '10-02-2023',
                'tgljt' => '11-03-2023',
                'cicilan' => '5',
                'nominal' => '1000000',
                'bayar' => '2000000',
                'saldo' => '3000000',
            ],
            [
                'suppdari_id' => 'PT. BAHAGIA BERSAMA',
                'suppsampai_id' => 'PT. BAHAGIA BERSAMA',
                'namasupplier' => 'PT. BAHAGIA BERSAMA',
                'nobukti' => 'INV 0003/II/2023',
                'tanggal' => '15-04-2023',
                'tgljt' => '16-05-2023',
                'cicilan' => '5',
                'nominal' => '2000000',
                'bayar' => '2000000',
                'saldo' => '3000000',
            ]
        ];
        return response([
            'data' => $report
        ]);
    }
}
