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


            $Templaporan = '##Templaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($Templaporan, function ($table) {
                $table->string('nobukti', 500);
                $table->date('tglbukti');
                $table->string('kodetrado', 500);
                $table->string('namastok', 500);
                $table->double('qty');
                $table->double('nominal');
                $table->double('harga');
                $table->string('satuan', 300);
                $table->longText('Keterangan');
            });


        $query = DB::table('pengeluaranstokdetail')->from(DB::raw("pengeluaranstokdetail  AS a WITH (READUNCOMMITTED)"))

            ->select(
                'b.nobukti',
                'b.tglbukti',
                db::raw("
                (case when isnull(c.kodetrado,'')<>'' then isnull(c.kodetrado,'')
                        when isnull(c1.kodegandengan,'')<>'' then isnull(c1.kodegandengan,'')
                        when isnull(c2.gudang,'')<>'' then isnull(c2.gudang,'')
                        else  '' end)
                as kodetrado
                "),
                'd.namastok',
                'a.qty',
                db::raw("isnull(a.total,0) as nominal"),
                db::raw("isnull(a.harga,0) as harga"),
                db::raw("isnull(e.satuan,'') as satuan"),
                'a.keterangan'

            )

            ->join(DB::raw("pengeluaranstokheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->leftjoin(db::raw("trado as c with (readuncommitted)"), 'b.trado_id', 'c.id')
            ->leftjoin(db::raw("gandengan as c1 with (readuncommitted)"), 'b.gandengan_id', 'c1.id')
            ->leftjoin(db::raw("gudang as c2 with (readuncommitted)"), 'b.gudang_id', 'c2.id')
            ->leftjoin(db::raw("stok as d with (readuncommitted)"), 'a.stok_id', 'd.id')
            ->leftjoin(db::raw("satuan as e with (readuncommitted)"), 'd.satuan_id', 'e.id')
            ->whereRaw("MONTH(b.tglbukti) = " . $bulan . " AND YEAR(b.tglbukti) = " . $tahun)
            ->whereRaw("b.pengeluaranstok_id in (1,3)")
            ->OrderBy('b.tglbukti', 'asc');


            DB::table($Templaporan)->insertUsing([
                'nobukti',
                'tglbukti',
                'kodetrado',
                'namastok',
                'qty',
                'nominal',
                'harga',
                'satuan',
                'Keterangan',
            ], $query);

            $query = DB::table($Templaporan)->from(DB::raw($Templaporan ."  AS a"))

            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.kodetrado',
                'a.namastok',
                'a.qty',
                'a.nominal',
                'a.harga',
                'a.satuan',
                'a.keterangan'

            )
            ->OrderBy('a.kodetrado', 'asc')
            ->OrderBy('a.tglbukti', 'asc')
            ->get();

        // dd($query->tosql());



        // return [$data1, $data2];
        return $query;
    }
}
