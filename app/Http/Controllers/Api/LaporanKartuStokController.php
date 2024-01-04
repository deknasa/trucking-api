<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKartuStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKartuStokController extends Controller
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

        $laporankartustok = new LaporanKartuStok();

        $report = [
            [
                'namagudang' => 'GUDANG KANTOR',
                'header' => 'Laporan Kartu Stok',
                'namabarang' => 'BAN DALAM SWALLOW 900',
                'tanggal' => '08-May-2023',
                'stokdari' => '0007255076',
                'stoksampai' => '0007255090',
                'kodebarang' => '04819203',
                'transaksi' => 'SPB 0006/V/2023',
                'kategori' => 'Ban',
                'qtymasuk' => '1',
                'qtykeluar' => '0',
                'hargaperkategori' => '8300000',
                'nominalmasuk' => '0',
                'nominalkeluar' => '8300000',
                'qtysaldo' => '0',
                'nominalsaldo' => '0'
            ],
            [
                'namagudang' => 'GUDANG KANTOR',
                'header' => 'Laporan Kartu Stok',
                'namabarang' => 'GANTUNGAN BAN SERAP',
                'tanggal' => '08-May-2023',
                'stokdari' => '0007255076',
                'stoksampai' => '0007255090',
                'kodebarang' => '021651515',
                'transaksi' => 'SPB 0006/V/2023',
                'kategori' => 'Ban',
                'qtymasuk' => '1',
                'qtykeluar' => '0',
                'hargaperkategori' => '8300000',
                'nominalmasuk' => '0',
                'nominalkeluar' => '8300000',
                'qtysaldo' => '0',
                'nominalsaldo' => '0'
            ],
            [
                'namagudang' => 'GUDANG KANTOR',
                'header' => 'Laporan Kartu Stok',
                'namabarang' => 'BAN LUAR 1000',
                'tanggal' => '08-May-2023',
                'stokdari' => '0007255076',
                'stoksampai' => '0007255090',
                'kodebarang' => '0216514858',
                'transaksi' => 'SPB 0006/V/2023',
                'kategori' => 'Ban',
                'qtymasuk' => '1',
                'qtykeluar' => '0',
                'hargaperkategori' => '8300000',
                'nominalmasuk' => '8300000',
                'nominalkeluar' => '0',
                'qtysaldo' => '0',
                'nominalsaldo' => '0'
            ],
            
        ];
        return response([
            'data' => $report
        ]);
    }
}
