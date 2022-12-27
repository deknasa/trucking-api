<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanBukuBesar extends MyModel
{
    use HasFactory;

    protected $table = 'jurnalumumheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    
    public function getReport()
    {
        // data coba coba
        $query = DB::table('jurnalumumdetail AS A')
        ->select(['A.nominal as debet','b.nominal as kredit','A.nominal as saldo','A.keterangan', 'jurnalumumheader.nobukti', 'jurnalumumheader.tglbukti'])
        ->leftJoin(
            DB::raw("(SELECT baris,nobukti,nominal FROM jurnalumumdetail WHERE nominal<0) B"),
            function ($join) {
                $join->on('A.baris', '=', 'B.baris');
            }
        )
        ->leftJoin('jurnalumumheader','jurnalumumheader.nobukti','A.nobukti')
        ->whereRaw("A.nobukti = B.nobukti")
        ->whereRaw("A.nominal >= 0");

        $data = $query->get();
        return $data;
    }
}