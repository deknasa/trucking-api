<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanHutangGiro extends MyModel
{
    use HasFactory;

    protected $table = 'laporanhutanggiro';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    
    public function getReport($periode)
    {
        // dd($periode);
        $getJudul = DB::table('parameter')
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();

        $alatbayar = 3;
         //NOTE - TempPencairan
         $TempPencairan = '##TempPencairan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
         Schema::create($TempPencairan, function ($table) {
             $table->string('nobukti',300)->nullable();
             $table->string('nobuktipencairan',300)->nullable();
             $table->date('tglbukti')->nullable();
             $table->date('tglbuktipencairan')->nullable();
         });

         $select_TempPencairan = DB::table('pengeluaranheader')->from(DB::raw("pengeluaranheader AS A WITH (READUNCOMMITTED)"))
        ->select([
            'a.nobukti',
            'b.nobukti as nobuktipencairan',
            'a.tglbukti',
            'b.tglbukti as tglbuktipencairan',
        ])
        ->join('pencairangiropengeluaranheader as b', 'a.nobukti', 'b.pengeluaran_nobukti')
        ->where('A.alatbayar_id', $alatbayar)
        ->where('b.tglbukti', '<=', $periode);
        // dd($select_TempPencairan->get());
        
        DB::table($TempPencairan)->insertUsing([
            'nobukti',
            'nobuktipencairan',
            'tglbukti',
            'tglbuktipencairan',
        ], $select_TempPencairan);

        $select_TempPencairan = DB::table('saldopengeluaranheader')->from(DB::raw("saldopengeluaranheader AS A WITH (READUNCOMMITTED)"))
        ->select([
            'a.nobukti',
            'b.nobukti as nobuktipencairan',
            'a.tglbukti',
            'b.tglbukti as tglbuktipencairan',
        ])
        ->join('pencairangiropengeluaranheader as b', 'a.nobukti', 'b.pengeluaran_nobukti')
        ->where('A.alatbayar_id', $alatbayar)
        ->where('b.tglbukti', '<=', $periode);
 
        
        DB::table($TempPencairan)->insertUsing([
            'nobukti',
            'nobuktipencairan',
            'tglbukti',
            'tglbuktipencairan',
        ], $select_TempPencairan);     
        
        // dd(db::table($TempPencairan)->get());

        $TempPencairan2 = '##TempPencairan2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempPencairan2, function ($table) {
            $table->string('nobukti',300)->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('nowarkat',300)->nullable();
            $table->double('nominal')->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->longText('judullaporan')->nullable();
            $table->longText('judul')->nullable();
            $table->longText('tglcetak')->nullable();
            $table->longText('usercetak')->nullable();
        });


        $select_TempPencairan2 = DB::table('pengeluaranheader AS a')->from(DB::raw("pengeluaranheader AS a WITH (READUNCOMMITTED)"))
    ->select([
        'a.nobukti',
        'a.tglbukti',
        'c.keterangan',
        'c.nowarkat',
        'c.nominal',
        'c.tgljatuhtempo',
        DB::raw("'Laporan Hutang Giro' as judulLaporan"),
        DB::raw("'" . $getJudul->text . "' as judul"),
        DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
        DB::raw(" 'User :".auth('api')->user()->name."' as usercetak") 
    ])
    ->leftjoin(db::raw($TempPencairan ." AS b"), 'a.nobukti', 'b.nobukti')
    // ->leftJoin($TempPencairan . " AS b", function ($join) {
    //     $join->on('a.nobukti', '=', 'b.nobukti')
    //          ->whereNull('b.nobukti');
    // })
    ->join('pengeluarandetail AS c', 'a.nobukti', '=', 'c.nobukti')

    ->whereRaw("c.tgljatuhtempo<='".$periode."'")
    ->whereRaw("isnull(b.nobukti,'')=''")
    ->where('a.alatbayar_id', $alatbayar);



    DB::table($TempPencairan2)->insertUsing([
        'nobukti',
        'tglbukti',
        'keterangan',
        'nowarkat',
        'nominal',
        'tgljatuhtempo',
        'judullaporan',
        'judul',
        'tglcetak',
        'usercetak',
    ], $select_TempPencairan2);     
    
    $select_TempPencairan2 = DB::table('saldopengeluaranheader AS a')->from(DB::raw("saldopengeluaranheader AS a WITH (READUNCOMMITTED)"))
    ->select([
        'a.nobukti',
        'a.tglbukti',
        'c.keterangan',
        'c.nowarkat',
        'c.nominal',
        'c.tgljatuhtempo',
        DB::raw("'Laporan Hutang Giro' as judulLaporan"),
        DB::raw("'" . $getJudul->text . "' as judul"),
        DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
        DB::raw(" 'User :".auth('api')->user()->name."' as usercetak") 
    ])
    ->leftjoin(db::raw($TempPencairan ." AS b"), 'a.nobukti', 'b.nobukti')
    // ->leftJoin($TempPencairan . " AS b", function ($join) {
    //     $join->on('a.nobukti', '=', 'b.nobukti')
    //          ->whereNull('b.nobukti');
    // })
    ->join('saldopengeluarandetail AS c', 'a.nobukti', '=', 'c.nobukti')
    ->whereRaw("isnull(b.nobukti,'')=''")
    ->where('a.alatbayar_id', $alatbayar);

    DB::table($TempPencairan2)->insertUsing([
        'nobukti',
        'tglbukti',
        'keterangan',
        'nowarkat',
        'nominal',
        'tgljatuhtempo',
        'judullaporan',
        'judul',
        'tglcetak',
        'usercetak',
    ], $select_TempPencairan2);         


        // dd($select_TempPencairan2->get());


        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

    $query = DB::table($TempPencairan2)->from(DB::raw($TempPencairan2 ." AS a "))
    ->select([
        'a.nobukti',
        'a.tglbukti',
        'a.keterangan',
        'a.nowarkat',
        'a.nominal',
        'a.tgljatuhtempo',
        'a.judulLaporan',
        'a.judul',
        'a.tglcetak',
        'a.usercetak',
        db::raw("'" . $disetujui . "' as disetujui"),
        db::raw("'" . $diperiksa . "' as diperiksa"),
    ])
    ->OrderBy('a.tglbukti')
    ->OrderBy('a.nobukti');
    
    $data = $query->get();

    // $data = DB::table('user')->get();



    return $data;

    
    }
   




  

    public function getExport($periode)
    {
        $getJudul = DB::table('parameter')
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();

        $alatbayar = 3;
         //NOTE - TempPencairan
         $TempPencairan = '##TempPencairan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
         Schema::create($TempPencairan, function ($table) {
             $table->string('nobukti');
             $table->string('nobuktipencairan');
             $table->date('tglbukti');
             $table->date('tglbuktipencairan');
         });

         $select_TempPencairan = DB::table('pengeluaranheader')->from(DB::raw("pengeluaranheader AS A WITH (READUNCOMMITTED)"))
        ->select([
            'a.nobukti',
            'a.nobukti as nobuktipencairan',
            'a.tglbukti',
            'b.tglbukti as tglbuktipencairan',
            
        ])
        ->join('pencairangiropengeluaranheader as b', 'a.nobukti', 'b.pengeluaran_nobukti')
        ->where('A.alatbayar_id', $alatbayar)
        ->where('b.tglbukti', '<=', $periode);
        // dd($select_TempPencairan->get());
        
        DB::table($TempPencairan)->insertUsing([
            'nobukti',
            'nobuktipencairan',
            'tglbukti',
            'tglbuktipencairan',
        ], $select_TempPencairan);


        $select_TempPencairan2 = DB::table('pengeluaranheader AS a')->from(DB::raw("pengeluaranheader AS a WITH (READUNCOMMITTED)"))
    ->select([
        'a.nobukti',
        'a.tglbukti',
        'c.keterangan',
        'c.nowarkat',
        'c.nominal',
        'c.tgljatuhtempo',
        DB::raw("'Laporan Hutang Giro' as judulLaporan"),
        DB::raw("'" . $getJudul->text . "' as judul"),
        DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
        DB::raw(" 'User :".auth('api')->user()->name."' as usercetak") 
    ])
    ->leftJoin($TempPencairan . " AS b", function ($join) {
        $join->on('a.nobukti', '=', 'b.nobukti')
             ->whereNull('b.nobukti');
    })
    ->join('pengeluarandetail AS c', 'a.nobukti', '=', 'c.nobukti')
    ->whereNull('b.nobukti')
    ->where('a.alatbayar_id', $alatbayar);
    $data = $select_TempPencairan2->get();


    return $data;
        
}
}
