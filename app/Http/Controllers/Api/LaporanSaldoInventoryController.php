<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\LaporanSaldoInventory;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLaporanSaldoInventoryRequest;
use App\Http\Requests\UpdateLaporanSaldoInventoryRequest;

class LaporanSaldoInventoryController extends Controller
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

        $kelompok_id = $request->kelompok_id ?? 0;
        $statusreuse = $request->statusreuse ?? 0;
        $statusban = $request->statusban ?? 0;
        $filter = $request->filter;
        $jenistgltampil = $request->jenistgltampil ?? '';
        $priode = $request->priode;
        $stokdari_id = $request->stokdari_id ?? 0;
        $stoksampai_id = $request->stoksampai_id ?? 0;
        $dataFilter = $request->dataFilter;
        // dd($request->all());
        $laporanSaldoInventory = new LaporanSaldoInventory();
        $report = LaporanSaldoInventory::getReport($kelompok_id, $statusreuse, $statusban, $filter, $jenistgltampil, $priode, $stokdari_id, $stoksampai_id, $dataFilter);
        // $report = [
        //     [
        //         'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',
        //     ],
        //     [
        //         'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',
        //     ],[
        //         'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',
        //     ],[
        //         'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',
        //     ],[
        //         'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',
        //     ],
        // ];
        return response([
            'data' => $report
        ]);
    }
      /**
     * @ClassName
     */
    public function export(Request $request)
    {
        $sampai = $request->sampai;
        $dari = $request->dari;

        $laporanSaldoInventory = new LaporanSaldoInventory();

        $report = [
            [
                'header' => 'Laporan Saldo Inventory',
                'lokasi' => 'GUDANG',
                'namalokasi' => 'GUDANG KANTOR',
                'kategori' => 'sparepart',
                'tgldari' => '2023-07-20',
                'tglsampai' => '2023-07-20',
                'stokdari' => 'BAN DALAM SWALLOW 900',
                'stoksampai' => 'BAN DALAM SWALLOW 900',
                'vulkanisirke' => 'Vul Ke: 0',
                'kodebarang' => '04819203',
                'namabarang' => 'BAN DALAM SWALLOW 900',
                'tanggal' => '08-07-2023',
                'qty' => '200',
                'satuan' => 'buah',
                'nominal' => '8300000',
            ],
            [
                'header' => 'Laporan Saldo Inventory',
                'lokasi' => 'GUDANG',
                'namalokasi' => 'GUDANG KANTOR',
                'kategori' => 'sparepart',
                'tgldari' => '2023-07-20',
                'tglsampai' => '2023-07-20',
                'stokdari' => 'BAN DALAM SWALLOW 900',
                'stoksampai' => 'BAN DALAM SWALLOW 900',
                'vulkanisirke' => 'Vul Ke: 0',
                'kodebarang' => '04819203',
                'namabarang' => 'BAN DALAM SWALLOW 900',
                'tanggal' => '08-07-2023',
                'qty' => '200',
                'satuan' => 'buah',
                'nominal' => '8300000',
            ],[
                'header' => 'Laporan Saldo Inventory',
                'lokasi' => 'GUDANG',
                'namalokasi' => 'GUDANG KANTOR',
                'kategori' => 'sparepart',
                'tgldari' => '2023-07-20',
                'tglsampai' => '2023-07-20',
                'stokdari' => 'BAN DALAM SWALLOW 900',
                'stoksampai' => 'BAN DALAM SWALLOW 900',
                'vulkanisirke' => 'Vul Ke: 0',
                'kodebarang' => '04819203',
                'namabarang' => 'BAN DALAM SWALLOW 900',
                'tanggal' => '08-07-2023',
                'qty' => '200',
                'satuan' => 'buah',
                'nominal' => '8300000',
            ],[
                'header' => 'Laporan Saldo Inventory',
                'lokasi' => 'GUDANG',
                'namalokasi' => 'GUDANG KANTOR',
                'kategori' => 'sparepart',
                'tgldari' => '2023-07-20',
                'tglsampai' => '2023-07-20',
                'stokdari' => 'BAN DALAM SWALLOW 900',
                'stoksampai' => 'BAN DALAM SWALLOW 900',
                'vulkanisirke' => 'Vul Ke: 0',
                'kodebarang' => '04819203',
                'namabarang' => 'BAN DALAM SWALLOW 900',
                'tanggal' => '08-07-2023',
                'qty' => '200',
                'satuan' => 'buah',
                'nominal' => '8300000',
            ],[
                'header' => 'Laporan Saldo Inventory',
                'lokasi' => 'GUDANG',
                'namalokasi' => 'GUDANG KANTOR',
                'kategori' => 'sparepart',
                'tgldari' => '2023-07-20',
                'tglsampai' => '2023-07-20',
                'stokdari' => 'BAN DALAM SWALLOW 900',
                'stoksampai' => 'BAN DALAM SWALLOW 900',
                'vulkanisirke' => 'Vul Ke: 0',
                'kodebarang' => '04819203',
                'namabarang' => 'BAN DALAM SWALLOW 900',
                'tanggal' => '08-07-2023',
                'qty' => '200',
                'satuan' => 'buah',
                'nominal' => '8300000',
            ],
        
        ];
        return response([
            'data' => $report
        ]);
    }
}
