<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPemakaianStok extends MyModel
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

    public function getReport($bulan, $tahun)
    {

    

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $cmpy = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->value('text');


            $query = DB::table('pengeluaranstokdetail')->from(DB::raw("pengeluaranstokdetail  AS a WITH (READUNCOMMITTED)"))

            ->select(
                'b.nobukti',
                'b.tglbukti',
                db::raw("isnull(c.kodetrado,'') as kodetrado"),
                'd.namastok',
                'a.qty',
                db::raw("isnull(a.total,0) as nominal"),
                db::raw("isnull(a.harga,0) as harga"),
                db::raw("isnull(e.satuan,'') as satuan"),
                'a.harga',
                'a.keterangan'
                
            )

            ->join(DB::raw("pengeluaranstokheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->leftjoin(db::raw("trado as c with (readuncommitted)"), 'b.trado_id', 'c.id')
            ->leftjoin(db::raw("stok as d with (readuncommitted)"), 'a.stok_id', 'd.id')
            ->leftjoin(db::raw("satuan as e with (readuncommitted)"), 'd.satuan_id', 'e.id')
            ->whereRaw("MONTH(b.tglbukti) = " . $bulan . " AND YEAR(b.tglbukti) = " . $tahun)
            ->whereRaw("b.pengeluaranstok_id in (1,3)")
            ->OrderBy('c.kodetrado','asc')
            ->OrderBy('b.tglbukti','asc')
            ->get();
            // dd($query->tosql());



        // return [$data1, $data2];
        return $query;
    }

}
