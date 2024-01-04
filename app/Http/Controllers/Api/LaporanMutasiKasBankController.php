<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanMutasiKasBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanMutasiKasBankController extends Controller
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
        $dari = $request->dari;

        $laporanmutasikasbank = new LaporanMutasiKasBank();

        $report = [
            [
                'nobukti' => 'PBT 0001/V/2023',
                'tanggal' => '01-02-2022',
                'keterangan' => 'Tarik tunai untuk isi Kas Divisi Trucking(Cek BCA No ES 947511)',
                'dari' => 'BANK TRUCKING3',
                'ke' => 'KAS TRUCKING',
                'nominal' => '23000000',

            ],
            [
                'nobukti' => 'PBT 0002/V/2023',
                'tanggal' => '02-02-2022',
                'keterangan' => 'Tarik tunai untuk isi Kas Divisi Trucking(Cek BCA No ES 947512)',
                'dari' => 'BANK TRUCKING3',
                'ke' => 'KAS TRUCKING',
                'nominal' => '12000000',
            ],
            [
                'nobukti' => 'PBT 0003/V/2023',
                'tanggal' => '03-02-2022',
                'keterangan' => 'Tarik tunai untuk isi Kas Divisi Trucking(Cek BCA No ES 947513)',
                'dari' => 'BANK TRUCKING3',
                'ke' => 'KAS TRUCKING',
                'nominal' => '9000000',
            ],
            [
                'nobukti' => 'PBT 0004/V/2023',
                'tanggal' => '04-02-2022',
                'keterangan' => 'Tarik tunai untuk isi Kas Divisi Trucking(Cek BCA No ES 947514)',
                'dari' => 'BANK TRUCKING3',
                'ke' => 'KAS TRUCKING',
                'nominal' => '31000000',
            ],
            
        ];
        return response([
            'data' => $report
        ]);
    }
}
