<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanOrderPembelian;

class LaporanOrderPembelianController extends Controller
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
        $kategori = $request->kategori;

        $report = [
            [
                'noob' => 'PO 0001/IV/2023',
                'tanggal' => '03/04/2023',
                'kodevendor' => 'HOKI JAYA',
                'namavendor' => 'HOKI JAYA',
                'memo' => 'ORDER SPAREPART DI HOKI JAYA',
                'kodebarang' => 'Karet Voring',
                'namabarang' => 'SPAREPART - KARET VORING',
                'qty' => '1.00',
                'satuan' => 'Buah',
                'keterangan' => 'ORDER SPAREPART DI HOKI JAYA',
                'vendordari' => '{SEMUA}',
                'vendorsampai' => '{SEMUA}'
            ], 
        ];
        return response([
            'data' => $report
        ]);
    }
}
