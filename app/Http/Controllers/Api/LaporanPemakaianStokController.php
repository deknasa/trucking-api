<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\LaporanPemakaianStok;
use App\Http\Requests\ValidasiLaporanPemakaianStokRequest;

use App\Http\Requests\StoreLaporanPemakaianStokRequest;
use App\Http\Requests\UpdateLaporanPemakaianStokRequest;
use Illuminate\Support\Facades\DB;

class LaporanPemakaianStokController extends Controller
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
    public function report(ValidasiLaporanPemakaianStokRequest $request)
    {
        $bulan = substr($request->sampai, 0, 2);
        $tahun = substr($request->sampai, 3, 4);

        $laporanpemakaianstok = new LaporanPemakaianStok();

        $laporan_pemakaianstok = $laporanpemakaianstok->getReport($bulan, $tahun);

        if (count($laporan_pemakaianstok) == 0) {
            return response([
                'data' => $laporan_pemakaianstok,
                'message' => 'tidak ada data'
            ], 500);
        }else{
            return response([
                'data' => $laporan_pemakaianstok,
                'message' => 'berhasil'
            ]);
        }
        
    }

    /**
     * @ClassName
     */
    public function export(Request $request)
    {
        $bulan = substr($request->sampai,0,2);
        $tahun = substr($request->sampai,-4);


        $laporanpemakaianstok = new LaporanPemakaianStok();


        $laporan_pemakaianstok = $laporanpemakaianstok->getReport($bulan, $tahun);
        // foreach($laporan_pemakaianstok as $item){
        //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        // }

        return response([
            'data' => $laporan_pemakaianstok
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
     * @param  \App\Http\Requests\StoreLaporanPemakaianStokRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLaporanPemakaianStokRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LaporanPemakaianStok  $laporanPemakaianStok
     * @return \Illuminate\Http\Response
     */
    public function show(LaporanPemakaianStok $laporanPemakaianStok)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LaporanPemakaianStok  $laporanPemakaianStok
     * @return \Illuminate\Http\Response
     */
    public function edit(LaporanPemakaianStok $laporanPemakaianStok)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLaporanPemakaianStokRequest  $request
     * @param  \App\Models\LaporanPemakaianStok  $laporanPemakaianStok
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLaporanPemakaianStokRequest $request, LaporanPemakaianStok $laporanPemakaianStok)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LaporanPemakaianStok  $laporanPemakaianStok
     * @return \Illuminate\Http\Response
     */
    public function destroy(LaporanPemakaianStok $laporanPemakaianStok)
    {
        //
    }
}
