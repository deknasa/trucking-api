<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\AbsensiSupirDetail;
use App\Models\MandorAbsensiSupir;
use App\Http\Controllers\Controller;

class MandorAbsensiSupirHistoryController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $getHeaderAbsensi = (new MandorAbsensiSupir())->getHeaderAbsensi();
        $absensiSupirDetail = new AbsensiSupirDetail();
        $request->merge([
            'absensi_id' =>$getHeaderAbsensi->id??'',
            'sortIndex' =>$request->sortIndex == 'kodetrado'?'trado':$request->sortIndex,
        ]);
        
        return response([
            'data' => $absensiSupirDetail->get(),
            'attributes' => [
                'total' => $absensiSupirDetail->totalPages,
                'records' => $absensiSupirDetail->totalRows,
                'tradosupir' => (new MandorAbsensiSupir())->isTradoMilikSupir(),
            ]
        ]);
    }
}
