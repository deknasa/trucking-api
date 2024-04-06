<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Http\Requests\LaporanTitipanEmklRequest;
use App\Models\LaporanTitipanEmkl;

class LaporanTitipanEmklController extends Controller
{

   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $laporanTitipanEmkl = new LaporanTitipanEmkl();
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
    public function report(LaporanTitipanEmklRequest $request)
    {
        $tanggal = date('Y-m-d', strtotime($request->periode));
        $tgldari = date('Y-m-d', strtotime($request->tgldari));
        $tglsampai = date('Y-m-d', strtotime($request->tglsampai));
        $jenisorder = $request->jenisorder ?? 0;
        $laporanTitipanEmkl = new LaporanTitipanEmkl();

        $laporan_titipanemkl = $laporanTitipanEmkl->getData($tanggal,$tgldari,$tglsampai,$jenisorder);
        
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
        ->select('cabang.namacabang')
        ->join("parameter", 'parameter.text', 'cabang.id')
        ->where('parameter.grp', 'ID CABANG')
        ->first();
        // foreach ($laporan_titipanemkl as $item) {
        //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        // }
        return response([
            'data' => $laporan_titipanemkl,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            // 'data' => $report
        ]);
    }

     /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(LaporanTitipanEmklRequest $request)
    {

        $tanggal = date('Y-m-d', strtotime($request->periode));
        $tgldari = date('Y-m-d', strtotime($request->tgldari));
        $tglsampai = date('Y-m-d', strtotime($request->tglsampai));
        $jenisorder = $request->jenisorder ?? 0;
        $keteranganjenis = '';
        if($jenisorder != 0 || $jenisorder != ''){
            $getJenis = DB::table("jenisorder")->from(DB::raw("jenisorder with (readuncommitted)"))->where('id', $jenisorder)->first();
            $keteranganjenis = $getJenis->keterangan;
        }
        $laporanTitipanEmkl = new LaporanTitipanEmkl();

        $laporan_titipanemkl = $laporanTitipanEmkl->getData($tanggal,$tgldari,$tglsampai,$jenisorder);
        
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
        ->select('cabang.namacabang')
        ->join("parameter", 'parameter.text', 'cabang.id')
        ->where('parameter.grp', 'ID CABANG')
        ->first();
        // foreach ($laporan_titipanemkl as $item) {
        //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        // }
        return response([
            'data' => $laporan_titipanemkl,
            'jenisorder' => $keteranganjenis,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            // 'data' => $report
        ]);
    }

}
