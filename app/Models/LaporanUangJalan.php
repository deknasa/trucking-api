<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanUangJalan extends MyModel
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
    public function get()
    {
        
        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'laporanuangjalan.id',
                'laporanuangjalan.namasupir',
                'laporanuangjalan.tglabsensi',
                'laporanuangjalan.nominalambil',
                'laporanuangjalan.tglric',
                'laporanuangjalan.nobuktiric',
                'laporanuangjalan.nominalkembali',
                'parameter.memo as statusaktif',
                'laporanuangjalan.modifiedby',
                'laporanuangjalan.created_at',
                'laporanuangjalan.updated_at',
                DB::raw("'Laporan laporanuangjalan' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'laporanuangjalan.statusaktif', 'parameter.id');

            
            $this->filter($query);
            

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('akuntansi.statusaktif', '=', $statusaktif->id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        

        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
        return $data;
       

    }


    public function getReport($tgldari, $tglsampai, $tglambil_jalandari, $tglambil_jalansampai, $supirdari, $supirsampai, $status)
    {
        //NOTE - Tempambil
        $Tempambil = '##Tempambil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempambil, function ($table) {
            $table->integer('supir_id');
            $table->date('tgl');
            $table->decimal('nominal', 10, 2);
        });
    

        $select_Tempambil = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader AS A WITH (READUNCOMMITTED)"))
        ->select([
            'b.supir_id',
            'A.tglbukti',
            'B.uangjalan',
        ])
        ->join(DB::raw('absensisupirdetail AS B WITH (READUNCOMMITTED)'), 'A.nobukti', '=', 'B.nobukti')
        ->where('A.tglbukti', '>=', $tglambil_jalandari)
        ->where('A.tglbukti', '<=', $tglambil_jalansampai)
        ->where('B.supir_id', '>=', $supirdari)
        ->where('B.supir_id', '<=', $supirsampai)
        ->whereRaw('ISNULL(B.uangjalan, 0) <> 0');
     
        
        DB::table($Tempambil)->insertUsing([
            'supir_id',
            'tgl',
            'nominal',
        ], $select_Tempambil);
        // dd($select_Tempambil->get());

        //NOTE - Listkembali
        $Listkembali = '##Listkembali' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Listkembali, function ($table) {
            $table->date('tglabsensi');
            $table->integer('supir_id');
            $table->string('buktiric', 50);
        });
        
        $select_Listkembali = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail AS A WITH (READUNCOMMITTED)"))
        ->select([
            'b.tglbukti as tglbukti', 
            DB::raw('MAX(c.supir_id) as supir_id'), 
            'c.nobukti'
        ])
      
        ->join(DB::raw('suratpengantar AS b WITH (READUNCOMMITTED)'), 'a.suratpengantar_nobukti', '=', 'b.nobukti')

        ->join(DB::raw('gajisupirheader AS c WITH (READUNCOMMITTED)'), 'a.nobukti', '=', 'c.nobukti')


        ->join(DB::raw($Tempambil . ' as d'), function ($join) {
            $join->on('b.tglbukti', '=', 'd.tgl')
                ->on('c.supir_id', '=', 'd.supir_id');
        })
      
        ->where('c.supir_id', '>=', $supirdari)
        ->where('c.supir_id', '<=', $supirsampai)
   
        ->where('c.tglbukti', '>=', $tgldari)
        
        ->where('c.tglbukti', '<=', $tglsampai)
    
        ->groupBy('c.nobukti', 'b.tglbukti');

        DB::table($Listkembali)->insertUsing([
            'tglabsensi',
            'supir_id',
            'buktiric',
        ], $select_Listkembali);
        


        //NOTE - Listkembaliurut
        $Listkembaliurut = '##Listkembaliurut' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Listkembaliurut, function ($table) {
            $table->date('tglabsensi');
            $table->integer('supir_id');
            $table->string('buktiric', 50);
            $table->integer('urut');
        });

        $select_Listkembaliurut = DB::table('Listkembali')->from(DB::raw($Listkembali))
        ->select([
            'tglabsensi',
            'supir_id',
            'buktiric',
            DB::raw('ROW_NUMBER() OVER (PARTITION BY buktiric ORDER BY tglabsensi) as urut')
        ]);

        DB::table($Listkembaliurut)->insertUsing([
            'tglabsensi',
            'supir_id',
            'buktiric',
            'urut',
        ], $select_Listkembaliurut);


        //NOTE - Tempkembali
        $Tempkembali = '##Tempkembali' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempkembali, function ($table) {
            $table->integer('supir_id');
            $table->date('tgl');
            $table->date('tglric');
            $table->string('nobuktiric', 50);
            $table->decimal('nominal', 10, 2);
        });
       

        $select_Tempkembali = DB::table('Listkembaliurut')->from(DB::raw($Listkembaliurut . " AS a"))
        ->select([
            'A.supir_id',
            'A.tglabsensi',
            'A.buktiric',
            'B.tglbukti',
            DB::raw('(CASE WHEN A.urut = 1 THEN ISNULL(B.uangjalan, 0) ELSE 0 END) AS nominal')
        ])
        ->leftJoin(DB::raw('gajisupirheader AS b WITH (READUNCOMMITTED)'), 'A.buktiric', '=', 'B.nobukti');

        DB::table($Tempkembali)->insertUsing([
            'supir_id',
            'tgl',
            'nobuktiric',
            'tglric',
            'nominal',
        ], $select_Tempkembali);


        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DISETUJUI')
        ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

    $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DIPERIKSA')
        ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();
        $select_Tempkembali2 = DB::table($Tempambil . ' AS a')
        ->select([
            'c.namasupir',
            'a.tgl AS tglabsensi',
            'a.nominal AS nominalambil',
            'b.tgl AS tglkembali',
            'b.nobuktiric',
            'b.nominal AS nominalkembali',
            db::raw("'" . $disetujui . "' as disetujui"),
            db::raw("'" . $diperiksa . "' as diperiksa"),
            DB::raw("'LAPORAN UANG JALAN' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        ])
        ->leftJoin($Tempkembali . ' AS b', function ($join) {
            $join->on('a.tgl', '=', 'b.tgl')
                ->on('a.supir_id', '=', 'b.supir_id');
        })
        ->join(DB::raw('supir AS c WITH (READUNCOMMITTED)'), 'a.supir_id', '=', 'c.id')
        ->orderBy('c.namasupir')
        ->orderBy('a.tgl');
        $data = $select_Tempkembali2->get();
    
    return $data;
    

    }
     

    public function getExport($tgldari, $tglsampai, $tglambil_jalandari, $tglambil_jalansampai, $supirdari, $supirsampai, $status)
    {
        //NOTE - Tempambil
        $Tempambil = '##Tempambil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempambil, function ($table) {
            $table->integer('supir_id');
            $table->date('tgl');
            $table->decimal('nominal', 10, 2);
        });
    

        $select_Tempambil = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader AS A WITH (READUNCOMMITTED)"))
        ->select([
            'b.supir_id',
            'A.tglbukti',
            'B.uangjalan',
        ])
        ->join(DB::raw('absensisupirdetail AS B WITH (READUNCOMMITTED)'), 'A.nobukti', '=', 'B.nobukti')
        ->where('A.tglbukti', '>=', $tglambil_jalandari)
        ->where('A.tglbukti', '<=', $tglambil_jalansampai)
        ->where('B.supir_id', '>=', $supirdari)
        ->where('B.supir_id', '<=', $supirsampai)
        ->whereRaw('ISNULL(B.uangjalan, 0) <> 0');
     
        
        DB::table($Tempambil)->insertUsing([
            'supir_id',
            'tgl',
            'nominal',
        ], $select_Tempambil);
        // dd($select_Tempambil->get());

        //NOTE - Listkembali
        $Listkembali = '##Listkembali' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Listkembali, function ($table) {
            $table->date('tglabsensi');
            $table->integer('supir_id');
            $table->string('buktiric', 50);
        });
        
        $select_Listkembali = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail AS A WITH (READUNCOMMITTED)"))
        ->select([
            'b.tglbukti as tglbukti', 
            DB::raw('MAX(c.supir_id) as supir_id'), 
            'c.nobukti'
        ])
      
        ->join(DB::raw('suratpengantar AS b WITH (READUNCOMMITTED)'), 'a.suratpengantar_nobukti', '=', 'b.nobukti')

        ->join(DB::raw('gajisupirheader AS c WITH (READUNCOMMITTED)'), 'a.nobukti', '=', 'c.nobukti')


        ->join(DB::raw($Tempambil . ' as d'), function ($join) {
            $join->on('b.tglbukti', '=', 'd.tgl')
                ->on('c.supir_id', '=', 'd.supir_id');
        })
      
        ->where('c.supir_id', '>=', $supirdari)
        ->where('c.supir_id', '<=', $supirsampai)
   
        ->where('c.tglbukti', '>=', $tgldari)
        
        ->where('c.tglbukti', '<=', $tglsampai)
    
        ->groupBy('c.nobukti', 'b.tglbukti');

        DB::table($Listkembali)->insertUsing([
            'tglabsensi',
            'supir_id',
            'buktiric',
        ], $select_Listkembali);
        


        //NOTE - Listkembaliurut
        $Listkembaliurut = '##Listkembaliurut' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Listkembaliurut, function ($table) {
            $table->date('tglabsensi');
            $table->integer('supir_id');
            $table->string('buktiric', 50);
            $table->integer('urut');
        });

        $select_Listkembaliurut = DB::table('Listkembali')->from(DB::raw($Listkembali))
        ->select([
            'tglabsensi',
            'supir_id',
            'buktiric',
            DB::raw('ROW_NUMBER() OVER (PARTITION BY buktiric ORDER BY tglabsensi) as urut')
        ]);

        DB::table($Listkembaliurut)->insertUsing([
            'tglabsensi',
            'supir_id',
            'buktiric',
            'urut',
        ], $select_Listkembaliurut);


        //NOTE - Tempkembali
        $Tempkembali = '##Tempkembali' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempkembali, function ($table) {
            $table->integer('supir_id');
            $table->date('tgl');
            $table->date('tglric');
            $table->string('nobuktiric', 50);
            $table->decimal('nominal', 10, 2);
        });
       

        $select_Tempkembali = DB::table('Listkembaliurut')->from(DB::raw($Listkembaliurut . " AS a"))
        ->select([
            'A.supir_id',
            'A.tglabsensi',
            'A.buktiric',
            'B.tglbukti',
            DB::raw('(CASE WHEN A.urut = 1 THEN ISNULL(B.uangjalan, 0) ELSE 0 END) AS nominal')
        ])
        ->leftJoin(DB::raw('gajisupirheader AS b WITH (READUNCOMMITTED)'), 'A.buktiric', '=', 'B.nobukti');

        DB::table($Tempkembali)->insertUsing([
            'supir_id',
            'tgl',
            'nobuktiric',
            'tglric',
            'nominal',
        ], $select_Tempkembali);


        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DISETUJUI')
        ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

    $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DIPERIKSA')
        ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();
        $select_Tempkembali2 = DB::table($Tempambil . ' AS a')
        ->select([
            'c.namasupir',
            'a.tgl AS tglabsensi',
            'a.nominal AS nominalambil',
            'b.tgl AS tglkembali',
            'b.nobuktiric',
            'b.nominal AS nominalkembali',
            db::raw("'" . $disetujui . "' as disetujui"),
            db::raw("'" . $diperiksa . "' as diperiksa"),
            DB::raw("'LAPORAN UANG JALAN' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")

        ])
        ->leftJoin($Tempkembali . ' AS b', function ($join) {
            $join->on('a.tgl', '=', 'b.tgl')
                ->on('a.supir_id', '=', 'b.supir_id');
        })
        ->join(DB::raw('supir AS c WITH (READUNCOMMITTED)'), 'a.supir_id', '=', 'c.id')
        ->orderBy('c.namasupir')
        ->orderBy('a.tgl');
        $data = $select_Tempkembali2->get();
    
    return $data;
    

    }

       

        

   
}
