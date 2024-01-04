<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\LaporanDepositoKaryawan;
use App\Http\Requests\StoreLaporanDepositoKaryawanRequest;
use App\Http\Requests\UpdateLaporanDepositoKaryawanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanDepositoKaryawanController extends Controller
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
        $jenis = $request->jenis;
        $prosesneraca=0;
        $laporandepositokaryawan=new LaporanDepositoKaryawan();
        
        return response([
            'data' => $laporandepositokaryawan->getReport($sampai, $jenis,$prosesneraca)
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $sampai = $request->sampai;
        $jenis = $request->jenis;
        $prosesneraca=0;

        $laporandepositokaryawan=new LaporanDepositoKaryawan();

        return response([
            'data' => $laporandepositokaryawan->getReport($sampai, $jenis,$prosesneraca)
        ]);
    }
}
