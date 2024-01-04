<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanPinjamanUnitTradoRequest;
use App\Models\LaporanPinjamanPerUnitTrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPinjamanPerUnitTradoController extends Controller
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
    public function report(ValidasiLaporanPinjamanUnitTradoRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {
            $trado_id = $request->trado_id;

            $laporanPinjaman = new LaporanPinjamanPerUnitTrado();
            return response([
                'data' => $laporanPinjaman->getReport($trado_id)
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(ValidasiLaporanPinjamanUnitTradoRequest $request)
    {

        $trado_id = $request->trado_id;

        $laporanPinjaman = new LaporanPinjamanPerUnitTrado();
        return response([
            'data' => $laporanPinjaman->getReport($trado_id)
        ]);
    }
}
