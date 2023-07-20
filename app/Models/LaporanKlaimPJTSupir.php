<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKlaimPJTSupir extends MyModel
{
    use HasFactory;

    protected $table = '';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];



    public function getReport($sampai, $dari,$kelompok)
    {
       

        $pidpengeluarantrucking=7;

        $query = DB::table("pengeluarantruckingheader")->from(
            DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'b.nominal',
                'b.keterangan',
                'd.namasupir',
                'c.namastok'

            )
            ->join(DB::raw("pengeluarantruckingdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("stok as c with (readuncommitted)"), 'b.stok_id', 'c.id')
            ->join(DB::raw("supir as d with (readuncommitted)"), 'a.supir_id', 'd.id')
            ->where('a.pengeluarantrucking_id', '=', $pidpengeluarantrucking)
            ->whereRaw("a.tglbukti >='".  date('Y/m/d', strtotime($dari)). "'")
            ->whereRaw("a.tglbukti <='".  date('Y/m/d', strtotime($sampai)). "'")
            ->where('c.kelompok_id', '=', $kelompok);


            // dd($query->get());
        $data = $query->get();
        return $data;
    }
}
