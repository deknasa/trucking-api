<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\LaporanPembelianBarang;
use App\Http\Requests\ValidasiLaporanPembelianBarangRequest;
use App\Http\Requests\StoreLaporanPembelianBarangRequest;
use App\Http\Requests\UpdateLaporanPembelianBarangRequest;
use Illuminate\Support\Facades\DB;

class LaporanPembelianBarangController extends Controller
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
    public function report(ValidasiLaporanPembelianBarangRequest $request)
    {
        $bulan = substr($request->sampai, 0, 2);
        $tahun = substr($request->sampai, 3, 4);

        $laporanpembelianbarang = new LaporanPembelianBarang();

        $laporan_pembelianbarang = $laporanpembelianbarang->getReport($bulan, $tahun);

        if (count($laporan_pembelianbarang) == 0) {
            return response([
                'data' => $laporan_pembelianbarang,
                'message' => 'tidak ada data'
            ], 500);
        }else{
            return response([
                'data' => $laporan_pembelianbarang,
                'message' => 'berhasil'
            ]);
        }
        
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $bulan = substr($request->sampai,0,2);
        $tahun = substr($request->sampai,-4);

        $laporanpembelianbarang = new LaporanPembelianBarang();


        $laporan_pembelianbarang = $laporanpembelianbarang->getReport($bulan, $tahun);
        // foreach($laporan_pembelianbarang as $item){
        //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        // }

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        return response([
            'data' => $laporan_pembelianbarang,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            // 'data' => $report
        ]);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreLaporanPembelianBarangRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLaporanPembelianBarangRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LaporanPembelianBarang  $laporanPembelianBarang
     * @return \Illuminate\Http\Response
     */
    public function show(LaporanPembelianBarang $laporanPembelianBarang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LaporanPembelianBarang  $laporanPembelianBarang
     * @return \Illuminate\Http\Response
     */
    public function edit(LaporanPembelianBarang $laporanPembelianBarang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLaporanPembelianBarangRequest  $request
     * @param  \App\Models\LaporanPembelianBarang  $laporanPembelianBarang
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLaporanPembelianBarangRequest $request, LaporanPembelianBarang $laporanPembelianBarang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LaporanPembelianBarang  $laporanPembelianBarang
     * @return \Illuminate\Http\Response
     */
    public function destroy(LaporanPembelianBarang $laporanPembelianBarang)
    {
        //
    }
}
