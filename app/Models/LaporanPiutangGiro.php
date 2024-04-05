<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPiutangGiro extends MyModel
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

    public function getExport($periode)
    {
       
        $getJudul = DB::table('parameter')
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();
        $alatbayar = 3;

        $TempPencairan = '##TempPencairan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
         Schema::create($TempPencairan, function ($table) {
             $table->string('nobukti', 50);
             $table->string('nobuktipencairan', 50);
             $table->date('tglbukti');
             $table->date('tglbuktipencairan');
         });
        //  dd("Sda");
         $select_TempPencairan = DB::table('penerimaangiroheader')->from(DB::raw("penerimaangiroheader AS A WITH (READUNCOMMITTED)"))
        ->select([
            'a.nobukti',
            'b.nobukti AS nobuktipencairan',
            'a.tglbukti',
            'c.tglbukti AS tglbuktipencairan',
            
        ])
        ->join(DB::raw("penerimaandetail AS b with (readuncommitted)"), 'a.nobukti', '=', 'b.penerimaangiro_nobukti')
        ->join(DB::raw("penerimaanheader AS c with (readuncommitted)"), 'b.nobukti', '=', 'c.nobukti')
        ->where('c.tglbukti', '<=', $periode);
        

        
        DB::table($TempPencairan)->insertUsing([
            'nobukti',
            'nobuktipencairan',
            'tglbukti',
            'tglbuktipencairan',
        ], $select_TempPencairan);

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DISETUJUI')
        ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

    $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DIPERIKSA')
        ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $select_TempPencairan2 = DB::table('penerimaangiroheader')->from(DB::raw("penerimaangiroheader AS A WITH (READUNCOMMITTED)"))
        ->select([
            'a.nobukti',
            'a.tglbukti',
            'c.nowarkat',
            'c.nominal',
            'c.tgljatuhtempo',
            DB::raw("'Laporan Piutang Giro' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :".auth('api')->user()->name."' as usercetak") ,
            db::raw("'" . $disetujui . "' as disetujui"),
            db::raw("'" . $diperiksa . "' as diperiksa"),

            
        ])
        ->leftJoin(DB::raw("{$TempPencairan} AS b"), function ($join) {
            $join->on('a.nobukti', '=', 'b.nobukti')
                ->whereNull('b.nobukti');
        })
        ->join(DB::raw("penerimaangirodetail AS c WITH (READUNCOMMITTED)"), 'a.nobukti', '=', 'c.nobukti')
        ->whereNull('b.nobukti');

        $data = $select_TempPencairan2->get();
        return $data;
        // dd($select_TempPencairan2->get());
    }

    public function getReport($periode)
    {
       
        $getJudul = DB::table('parameter')
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();
        $alatbayar = 3;

        $TempPencairan = '##TempPencairan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
         Schema::create($TempPencairan, function ($table) {
             $table->string('nobukti', 50);
             $table->string('nobuktipencairan', 50);
             $table->date('tglbukti');
             $table->date('tglbuktipencairan');
         });
        //  dd("Sda");
         $select_TempPencairan = DB::table('penerimaangiroheader')->from(DB::raw("penerimaangiroheader AS A WITH (READUNCOMMITTED)"))
        ->select([
            'a.nobukti',
            'b.nobukti AS nobuktipencairan',
            'a.tglbukti',
            'c.tglbukti AS tglbuktipencairan',
            
        ])
        ->join(DB::raw("penerimaandetail AS b with (readuncommitted)"), 'a.nobukti', '=', 'b.penerimaangiro_nobukti')
        ->join(DB::raw("penerimaanheader AS c with (readuncommitted)"), 'b.nobukti', '=', 'c.nobukti')
        ->where('c.tglbukti', '<=', $periode);
        

        
        DB::table($TempPencairan)->insertUsing([
            'nobukti',
            'nobuktipencairan',
            'tglbukti',
            'tglbuktipencairan',
        ], $select_TempPencairan);

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DISETUJUI')
        ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

    $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DIPERIKSA')
        ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $select_TempPencairan2 = DB::table('penerimaangiroheader')->from(DB::raw("penerimaangiroheader AS A WITH (READUNCOMMITTED)"))
        ->select([
            'a.nobukti',
            'a.tglbukti',
            'c.nowarkat',
            'c.nominal',
            'c.tgljatuhtempo',
            DB::raw("'Laporan Piutang Giro' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :".auth('api')->user()->name."' as usercetak") ,
            db::raw("'" . $disetujui . "' as disetujui"),
            db::raw("'" . $diperiksa . "' as diperiksa"),
            
        ])
        ->leftJoin(DB::raw("{$TempPencairan} AS b"), function ($join) {
            $join->on('a.nobukti', '=', 'b.nobukti')
                ->whereNull('b.nobukti');
        })
        ->join(DB::raw("penerimaangirodetail AS c WITH (READUNCOMMITTED)"), 'a.nobukti', '=', 'c.nobukti')
        ->whereNull('b.nobukti');

        $data = $select_TempPencairan2->get();
        return $data;
        // dd($select_TempPencairan2->get());
    }
}
