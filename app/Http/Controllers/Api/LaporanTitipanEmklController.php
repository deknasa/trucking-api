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
     */
    public function report(LaporanTitipanEmklRequest $request)
    {

        $laporanTitipanEmkl = new LaporanTitipanEmkl();

        $laporan_titipanemkl = $laporanTitipanEmkl->getData();
        
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
    public function export(LaporanTitipanEmklRequest $request)
    {

        $laporanTitipanEmkl = new LaporanTitipanEmkl();

        $laporan_titipanemkl = $laporanTitipanEmkl->getData();
        
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
