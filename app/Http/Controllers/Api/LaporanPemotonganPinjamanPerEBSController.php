<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanPemotonganPinjamanPerEBS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPemotonganPinjamanPerEBSController extends Controller
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
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));

        $laporanpemotonganpinjamanperebs = new LaporanPemotonganPinjamanPerEBS();
        // $report = [
        //     [
        //         'nobukti' => 'EBS 0001/II/2023',
        //         'tanggal' => '21/2/2023',
        //         'nobk' => 'BK 2134 NMA',
        //         'supir' => 'HERMAN',
        //         'tgldari' => '22/2/2023',
        //         'tglsampai' => '22/2/2023',
        //         'pinjamansendiri' => '124124',
        //         'ketpinjamansendiri' => 'Charge supir Syaiful atas Ban Masak yg Rusak Jebol Samping pada B 9949 JH dgn no ban 1100 - 06316109 ketebalan 4mm sebesar Rp.420.666 + Rp.500.000 (Biaya Vul 2), total keseluruhan Rp. 920.666 Dibulatk (PJT 0002/XII/2022) Pinjaman Supir Syaiful B 9949 JH untuk Biaya Perdamaian, Kepolisian dan Ibu Sidabutar atas Laka di  Tebing Tinggi(PJT 0062/XI/2022)',
        //         'pinjamanbersama' => '124124',
        //         'ketpinjamanbersama' => 'Charge bersama semua supir atas Ban yang meledak dgn no ban 1100 - 04924112 pada Gandengan T- 07 Panjang sebesar Rp.740.000,- dibagi 17 supir(PJT 0017/III/2018)'
        //     ]
        // ];
        $laporan_pemotongan_pinjamanperebs = $laporanpemotonganpinjamanperebs->getReport($dari, $sampai,);

        foreach ($laporan_pemotongan_pinjamanperebs as $item) {
            $item->tgldari = date('d-m-Y', strtotime($item->tgldari));
            $item->tglsampai = date('d-m-Y', strtotime($item->tglsampai));

            $item->tglbukti = date('d-m-Y', strtotime(substr($item->tglbukti, 0, 10)));
            $item->tanggaldari = date('d-m-Y', strtotime($item->tanggaldari));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        return response([
            'data' => $laporan_pemotongan_pinjamanperebs,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));

        $laporanpemotonganpinjamanperebs = new LaporanPemotonganPinjamanPerEBS();
        // $report = [
        //     [
        //         'nobukti' => 'EBS 0001/II/2023',
        //         'tanggal' => '21/2/2023',
        //         'nobk' => 'BK 2134 NMA',
        //         'supir' => 'HERMAN',
        //         'tgldari' => '22/2/2023',
        //         'tglsampai' => '22/2/2023',
        //         'pinjamansendiri' => '124124',
        //         'ketpinjamansendiri' => 'Charge supir Syaiful atas Ban Masak yg Rusak Jebol Samping pada B 9949 JH dgn no ban 1100 - 06316109 ketebalan 4mm sebesar Rp.420.666 + Rp.500.000 (Biaya Vul 2), total keseluruhan Rp. 920.666 Dibulatk (PJT 0002/XII/2022) Pinjaman Supir Syaiful B 9949 JH untuk Biaya Perdamaian, Kepolisian dan Ibu Sidabutar atas Laka di  Tebing Tinggi(PJT 0062/XI/2022)',
        //         'pinjamanbersama' => '124124',
        //         'ketpinjamanbersama' => 'Charge bersama semua supir atas Ban yang meledak dgn no ban 1100 - 04924112 pada Gandengan T- 07 Panjang sebesar Rp.740.000,- dibagi 17 supir(PJT 0017/III/2018)'
        //     ]
        // ];
        $laporan_pemotongan_pinjamanperebs = $laporanpemotonganpinjamanperebs->getReport($dari, $sampai,);

        foreach ($laporan_pemotongan_pinjamanperebs as $item) {
            $item->tgldari = date('d-m-Y', strtotime($item->tgldari));
            $item->tglsampai = date('d-m-Y', strtotime($item->tglsampai));

            $item->tglbukti = date('d-m-Y', strtotime(substr($item->tglbukti, 0, 10)));
            $item->tanggaldari = date('d-m-Y', strtotime($item->tanggaldari));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        return response([
            'data' => $laporan_pemotongan_pinjamanperebs,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
    }
}
