<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Http\Requests\LaporanRekapTitipanEmklRequest;
use App\Models\LaporanRekapTitipanEmkl;

class LaporanRekapTitipanEmklController extends Controller
{
     /**
     * @ClassName
     */
    public function index()
    {
        $laporanRekapTitipanEmkl = new LaporanRekapTitipanEmkl();
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
    public function report(LaporanRekapTitipanEmklRequest $request)
    {

        $laporanRekapTitipanEmkl = new LaporanRekapTitipanEmkl();

        $laporan_titipanemkl = $laporanRekapTitipanEmkl->getData();
        
        // foreach ($laporan_titipanemkl as $item) {
        //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        // }
        return response([
            'data' => $laporan_titipanemkl
            // 'data' => $report
        ]);
    }

     /**
     * @ClassName
     */
    public function export(LaporanRekapTitipanEmklRequest $request)
    {

        $laporanRekapTitipanEmkl = new LaporanRekapTitipanEmkl();

        $laporan_titipanemkl = $laporanRekapTitipanEmkl->getData();
        
        // foreach ($laporan_titipanemkl as $item) {
        //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        // }
        return response([
            'data' => $laporan_titipanemkl
            // 'data' => $report
        ]);
    }
}
