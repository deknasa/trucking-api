<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ApprovalStokReuse;
use App\Http\Controllers\Controller;
use App\Models\ExportPerhitunganBonus;
use Illuminate\Support\Facades\DB;

class ExportPerhitunganBonusController extends Controller
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $periode = $request->periode ?? 0;
        $tahun = $request->tahun ?? 0;
        $cabang_id = $request->cabang_id ?? 0;

        if ($periode==1) {
            $bulan1='Januari';
            $bulan2='Februari';
            $bulan3='Maret';
        }

        if ($periode==2) {
            $bulan1='April';
            $bulan2='Mei';
            $bulan3='Juni';
        }
        if ($periode==3) {
            $bulan1='Juli';
            $bulan2='Agustus';
            $bulan3='September';
        }
        if ($periode==4) {
            $bulan1='Oktober';
            $bulan2='November';
            $bulan3='Desember';
        }

        $cabang=db::table("cabang")->from(db::raw("cabang a with (readuncommitted)"))
        ->select(
            'a.namacabang'
        )
        ->where('a.id',$cabang_id)
        ->first()->namacabang ?? '';

        $laporan = new ExportPerhitunganBonus();
        return response([
            'data' => $laporan->getReport($periode,$tahun,$cabang_id),
            'dataheader' =>[
                'perkiraan' => 'Perkiraan',
                'bulankesatu' => $bulan1,
                'bulankedua' => $bulan2,
                'bulanketiga' => $bulan3,
            ],
            
            'judul' => "Bonus Karyawan ".$cabang." Periode ". $periode ." ( ".$bulan1." s.d ". $bulan3 ." ". $tahun ." )",
        ]);
    }
}
