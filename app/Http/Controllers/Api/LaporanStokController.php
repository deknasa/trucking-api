<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\LaporanStok;
use App\Http\Requests\StoreLaporanStokRequest;
use App\Http\Requests\UpdateLaporanStokRequest;
use App\Http\Requests\ValidasiLaporanStokRequest;
use Illuminate\Support\Facades\DB;

class LaporanStokController extends Controller
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
    public function report(ValidasiLaporanStokRequest $request)
    {
        $bulan = substr($request->sampai, 0, 2);
        $tahun = substr($request->sampai, 3, 4);

        $laporanstok = new LaporanStok();

        $laporan_stok = $laporanstok->getReport($bulan, $tahun);

        if (count($laporan_stok) == 0) {
            return response([
                'data' => $laporan_stok,
                'message' => 'tidak ada data'
            ], 500);
        }else{
            return response([
                'data' => $laporan_stok,
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

        $laporanstok = new Laporanstok();


        $laporan_stok = $laporanstok->getReport($bulan, $tahun);
        // foreach($laporan_stok as $item){
        //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        // }

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        return response([
            'data' => $laporan_stok,
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
     * @param  \App\Http\Requests\StoreLaporanStokRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLaporanStokRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LaporanStok  $laporanStok
     * @return \Illuminate\Http\Response
     */
    public function show(LaporanStok $laporanStok)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LaporanStok  $laporanStok
     * @return \Illuminate\Http\Response
     */
    public function edit(LaporanStok $laporanStok)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLaporanStokRequest  $request
     * @param  \App\Models\LaporanStok  $laporanStok
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLaporanStokRequest $request, LaporanStok $laporanStok)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LaporanStok  $laporanStok
     * @return \Illuminate\Http\Response
     */
    public function destroy(LaporanStok $laporanStok)
    {
        //
    }
}
