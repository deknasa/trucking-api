<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKasBank extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluaranstokdetailfifo';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];



    public function getReport($dari, $sampai, $bank_id)
    {
        // data coba coba
        $query = DB::table('pengeluaranheader')->from(
            DB::raw("pengeluaranheader with (readuncommitted)")
        )->select(
            'pengeluaranheader.id',
            'pengeluaranheader.nobukti',
            'pengeluaranheader.keterangan',
            'bank.namabank',
            'cabang.namacabang',
        )
        ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
        ->leftJoin(DB::raw("cabang with (readuncommitted)"), 'pengeluaranheader.cabang_id', 'cabang.id')
        ->where('pengeluaranheader.bank_id', $bank_id);

        $data = $query->get();
        return $data;
    }
}
