<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExportRic extends Model
{
    use HasFactory;

    public function getExport($periode, $statusric, $dari, $sampai, $trado_id, $kelompok_id)
    {
        
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $data = [
            0 => [
                'nobukti' => 'SPK 0073/IX/2023',
                'tglbukti' => '2023/9/6',
                'namabarang' => 'OLI MEDITRAN SXSAE 15 W-40',
                'qty' => '-2',
                'satuan' => 'liter',
                'statuskendaraan' => 'Penambahan Oli',
                'pergantianke' => '1',
                'kmke' => '7947',
                'selisihkm' => '7947',
                'keterangan' => 'NO UR 030585, KEPERLUAN PERBAIKAN KEBOCORAN OLI PADA OIL SEAL INJEKTOR DAN TAMBAH OLI MESIN KARENA KEBOXORAN OIL SEAL INJEKTOR/ BUSTAMI',
                'judul' => $getJudul->text,
                'judulLaporan' => 'Export RIC'
            ],
            1 => [
                'nobukti' => 'SPK 0077/IX/2023',
                'tglbukti' => '2023/9/12',
                'namabarang' => 'OLI SAE 15 (PERTAMINA)',
                'qty' => '-1',
                'satuan' => 'liter',
                'statuskendaraan' => 'Penambahan Oli',
                'pergantianke' => '1',
                'kmke' => '7654',
                'selisihkm' => '7654',
                'keterangan' => 'NO UR 0314578, KEPERLUAN PERBAIKAN KEBOCORAN OLI PADA OIL SEAL INJEKTOR DAN TAMBAH OLI MESIN KARENA KEBOXORAN OIL SEAL INJEKTOR/ BUSTAMI',
                'judul' => $getJudul->text,
                'judulLaporan' => 'Export RIC'
            ]
        ];

        return $data;
    }
}
