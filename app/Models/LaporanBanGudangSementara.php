<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class LaporanBanGudangSementara extends MyModel
{
    use HasFactory;

    protected $table = '';


    public function getReport()
    {

        // $data = $queryRekap->get();
        $data = [
            [
                "kodestok" => "BAUT 12",
                'namastok' => 'BAUT 12',
                'gudang' => 'GUDANG PIHAK KE-3',
                'nobukti' => 'PG 00035/II/2023',
                'tanggal' => '23/2/2023',
                'jlhhari' => '23'
            ]
        ];
        return $data;
    }
}
