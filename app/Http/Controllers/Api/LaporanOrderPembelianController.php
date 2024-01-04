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
    public function report(Request $request)
    {
        $sampai = $request->sampai;
        $dari = $request->dari;
        $supplierdari_id = $request->supplierdari_id;
        $suppliersampai_id = $request->suppliersampai_id;
        $text_id = $request->text_id;

        $report = [
            [
                'judul' => 'PT. Transporindo Agung Sejahtera',
                'noob' => 'PO 0001/IV/2023',
                'tanggal' => '03/04/2023',
                'kodevendor' => 'HOKI JAYA',
                'namavendor' => 'HOKI JAYA',
                'memo' => 'ORDER SPAREPART DI HOKI JAYA',
                'kodebarang' => 'Karet Voring',
                'namabarang' => 'SPAREPART - KARET VORING',
                'qty' => '1.00',
                'satuan' => 'Buah',
                'keterangan' => 'ORDER SPAREPART DI HOKI JAYA'
            ], 
        ];
        return response([
            'data' => $report
        ]);
    }
}
