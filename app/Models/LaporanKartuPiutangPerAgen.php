<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKartuPiutangPerAgen extends MyModel
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

    public function getReport($dari, $sampai, $agenDari, $agenSampai)
    {

        if ($agenDari == 0) {
            $agenDari = db::table('agen')->from(db::raw("agen with (readuncommitted)"))
                ->select('id')->orderby('id', 'asc')->first()->id ?? 0;
        }

        if ($agenSampai == 0) {
            $agenSampai = db::table('agen')->from(db::raw("agen with (readuncommitted)"))
                ->select('id')->orderby('id', 'desc')->first()->id ?? 0;
        }

        if ($agenDari > $agenSampai) {
            $agenDari1 = $agenSampai;
            $agenSampai1 = $agenDari;
            $agenDari = $agenDari1;
            $agenSampai = $agenSampai1;
        }

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        // START TEMPPIUTANG
        $Temppiutang = '##Temppiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutang, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        $select_Temppiutang = DB::table('piutangheader')->from(DB::raw("piutangheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                DB::raw('SUM(B.nominal) as nominal')
            ])
            ->join('piutangdetail AS B', 'A.nobukti', '=', 'B.NoBukti')
            ->where('A.agen_id', '>=', $agenDari)
            ->where('A.agen_id', '<=', $agenSampai)
            ->groupBy('A.nobukti');

        DB::table($Temppiutang)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal',
        ], $select_Temppiutang);
        // dd($select_Temppiutang->get());
        // END TEMPPIUTANG

        // START TEMPPIUTANG BAYAR
        $Temppiutangbyr = '##Temppiutangbyr' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyr, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangbyr = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader as A with (readuncommitted)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                'B.piutang_nobukti',
                DB::raw('SUM(B.nominal) as nominal')
            ])
            ->join(DB::raw("pelunasanPiutangdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw($Temppiutang . " AS C with (readuncommitted)"), 'B.piutang_nobukti', 'C.nobukti')
            ->groupBy('A.nobukti', 'B.piutang_nobukti');


        DB::table($Temppiutangbyr)->insertUsing([
            'tglbukti',
            'nobukti',
            'piutang_nobukti',
            'nominal',
        ], $select_Temppiutangbyr);
        //   dd($select_Temppiutangbyr->get());
        // END TEMPPIUTANG BAYAR

        //NOTE - Temppiutangsaldo
        $Temppiutangsaldo = '##Temppiutangsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangsaldo = DB::table($Temppiutang)->from(DB::raw($Temppiutang . " AS A"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.nominal'
            ])
            ->where('A.tglbukti', '<', $dari);

        DB::table($Temppiutangsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal'
        ], $select_Temppiutangsaldo);
        // dd($select_Temppiutangsaldo->get());




        //NOTE - Temppiutangbyrsaldo
        $Temppiutangbyrsaldo = '##Temppiutangbyrsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyrsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangbyrsaldo = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS a"))
            ->select([
                DB::raw('MAX(a.tglbukti) as tglbukti'),
                DB::raw('MAX(a.nobukti) as nobukti'),
                'a.piutang_nobukti',
                DB::raw('SUM(a.nominal) as nominal'),

            ])

            ->where('a.tglbukti', '<', $dari)
            ->groupBy('a.piutang_nobukti');

        DB::table($Temppiutangbyrsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'piutang_nobukti',
            'nominal'
        ], $select_Temppiutangbyrsaldo);
        //datanya tidak ada
        // dd($select_Temppiutangbyrsaldo->get());

        //NOTE - TemppiutangbyrsaldoCicil
        $TemppiutangbyrsaldoCicil = '##TemppiutangbyrsaldoCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemppiutangbyrsaldoCicil, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
            $table->integer('urut');
        });

        $select_TemppiutangbyrsaldoCicil = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS A"))
            ->select([
                'A.tglbukti as tglbukti',
                'A.nobukti',
                'A.piutang_nobukti',
                'A.nominal as nominal',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY A.piutang_nobukti ORDER BY A.tglbukti) as urut')
            ])
            ->where('A.tglbukti', '<', $dari);
        //datanya tidak ada
        // dd("ASdas");
        // dd($select_TemppiutangbyrsaldoCicil->get());

        $Temppiutangberjalan = '##Temppiutangberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangberjalan, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        // $select_Temppiutangberjalan = DB::table($Temppiutang)->from(DB::raw($Temppiutang . " AS A"))
        //     ->select([
        //         'A.tglbukti',
        //         'A.nobukti',
        //         'A.nominal',
        //     ])
        //     ->where('A.tglbukti', '>', $dari)
        //     ->where('A.tglbukti', '<=', $sampai);

        // DB::table($Temppiutangberjalan)->insertUsing([
        //     'tglbukti',
        //     'nobukti',
        //     'nominal'
        // ], $select_Temppiutangberjalan);

        $Temppiutangbyrberjalan = '##Temppiutangbyrberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyrberjalan, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        // $select_Temppiutangbyrberjalan = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS A"))
        //     ->select([
        //         DB::raw('MAX(A.tglbukti) as tglbukti'),
        //         DB::raw('MAX(A.nobukti) as nobukti'),
        //         'A.piutang_nobukti',
        //         DB::raw('SUM(A.nominal) as nominal')
        //     ])
        //     ->where('A.tglbukti', '>', $dari)
        //     ->where('A.tglbukti', '<=', $sampai)
        //     ->groupBy('A.piutang_nobukti');

        // DB::table($Temppiutangbyrberjalan)->insertUsing([
        //     'tglbukti',
        //     'nobukti',
        //     'piutang_nobukti',
        //     'nominal',
        // ], $select_Temppiutangbyrberjalan);

        $TemppiutangbyrberjalanCicil = '##TemppiutangbyrberjalanCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemppiutangbyrberjalanCicil, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
            $table->integer('urut');
        });

        // $select_TemppiutangbyrberjalanCicil = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS A"))
        //     ->select([
        //         DB::raw('A.tglbukti as tglbukti'),
        //         DB::raw('A.nobukti as nobukti'),
        //         'A.piutang_nobukti',
        //         DB::raw('A.nominal as nominal'),
        //         DB::raw("row_number() Over(partition BY A.piutang_nobukti Order By A.tglbukti) as urut")
        //     ])
        //     ->where('A.tglbukti', '>', $dari)
        //     ->where('A.tglbukti', '<=', $sampai);

        // DB::table($TemppiutangbyrberjalanCicil)->insertUsing([
        //     'tglbukti',
        //     'nobukti',
        //     'piutang_nobukti',
        //     'nominal',
        //     'urut',
        // ], $select_TemppiutangbyrberjalanCicil);

        $TempCicil = '##TempCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempCicil, function ($table) {
            $table->string('piutang_nobukti', 50);
            $table->integer('urut');
        });

        $select_TempCicil = DB::table($TemppiutangbyrsaldoCicil)->from(DB::raw($TemppiutangbyrsaldoCicil))
            ->select([
                'piutang_nobukti',
                DB::raw('MAX(urut) as urut'),
            ])
            ->groupBy('piutang_nobukti');

        DB::table($TempCicil)->insertUsing([
            'piutang_nobukti',
            'urut',
        ], $select_TempCicil);

        $select_TempCicil2 = DB::table($TemppiutangbyrberjalanCicil)->from(DB::raw($TemppiutangbyrberjalanCicil))
            ->select([
                'piutang_nobukti',
                DB::raw('MAX(urut) as urut'),
            ])
            ->groupBy('piutang_nobukti');

        DB::table($TempCicil)->insertUsing([
            'piutang_nobukti',
            'urut',
        ], $select_TempCicil2);
        // dd($select_TempCicil->get());


        $TempCicilRekap = '##TempCicilRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempCicilRekap, function ($table) {
            $table->string('piutang_nobukti', 50);
            $table->integer('urut');
        });

        $select_TempCicilRekap = DB::table($TempCicil)->from(DB::raw($TempCicil))
            ->select([
                'piutang_nobukti',
                DB::raw('SUM(urut) as urut'),
            ])
            ->groupBy('piutang_nobukti');

        DB::table($TempCicilRekap)->insertUsing([
            'piutang_nobukti',
            'urut',
        ], $select_TempCicilRekap);
        //    dd($select_TempCicilRekap->get());


        $TempRekappiutang = '##TempRekappiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempRekappiutang, function ($table) {
            $table->bigIncrements('id');
            $table->string('nobukti', 50);
            $table->double('nominal');
            $table->double('bayar');
        });



        $select_TempRekappiutang = DB::table($Temppiutangsaldo . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0)) as saldo"),
                DB::raw("ISNULL(C.nominal, 0) as bayar")
            ])
            ->leftJoin($Temppiutangbyrsaldo . ' AS B', 'A.nobukti', '=', 'B.piutang_nobukti')
            ->leftJoin($Temppiutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.piutang_nobukti')
            ->join(DB::raw("piutangheader AS D with (readuncommitted)"), 'A.nobukti', '=', 'D.nobukti')
            ->where(DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0))"), '<>', 0)
            ->orderBy('D.agen_id')
            ->orderBy('D.tglbukti')
            ->orderBy('A.nobukti');
        // dd($select_TempRekappiutang->get()); sampai sini datanya ada


        DB::table($TempRekappiutang)->insertUsing([
            'nobukti',
            'nominal',
            'bayar',
        ], $select_TempRekappiutang);

        // dd($select_TempRekappiutang->get());

        $select_TempRekappiutang2 = DB::table($Temppiutangberjalan . ' AS A')
            ->select([
                'A.nobukti',
                'A.nominal',
                DB::raw('ISNULL(C.nominal, 0) as bayar')
            ])

            ->leftJoin($Temppiutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.piutang_nobukti');

        // dd($select_TempRekappiutang2->get());

        DB::table($TempRekappiutang)->insertUsing([
            'nobukti',
            'nominal',
            'bayar',
        ], $select_TempRekappiutang2);

        $Tempketerangan = '##Tempketerangan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempketerangan, function ($table) {
            $table->string('nobukti', 50);
            $table->LongText('keterangan');
        });

        $select_Tempketerangan = DB::table($TempRekappiutang . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw('MAX(B.keterangan) as keterangan'),

            ])
            ->join('piutangdetail as b', 'A.nobukti', 'b.nobukti')
            ->groupBy('A.nobukti');


        DB::table($Tempketerangan)->insertUsing([
            'nobukti',
            'keterangan'
        ], $select_Tempketerangan);

        // dd($select_TempRekappiutang2->get());
        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $select_data = DB::table($TempRekappiutang . ' AS A')
            ->select([
                'D.namaagen',
                db::raw("(case when isnull(C.keterangan,'')='' then isnull(e.keterangan,'') else isnull(C.keterangan,'') end) as keterangan"),
                'A.nobukti',
                'C.tglbukti',
                DB::raw("dateadd(d,isnull(d.[top],0),c.tglbukti) as tgljatuhtempo"),
                DB::raw('ISNULL(B.urut, 0) + 1 as cicil'),
                'A.nominal',
                'A.bayar',
                DB::raw('SUM((ISNULL(A.nominal, 0) - A.bayar)) OVER (PARTITION BY D.namaagen ORDER BY A.id ASC) as Saldo'),
                DB::raw("'$getJudul->text' AS text"),
                DB::raw("'$dari' AS dari"),
                DB::raw("'$sampai' AS sampai"),
                DB::raw("'Laporan Kartu Piutang Per Agen' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
            ])
            ->leftJoin($TempCicilRekap . ' AS B', 'A.nobukti', '=', 'B.piutang_nobukti')
            ->join(DB::raw("piutangheader AS C with (readuncommitted)"), 'A.nobukti', '=', 'C.nobukti')
            ->join(DB::raw("agen AS D with (readuncommitted)"), 'C.agen_id', '=', 'D.id')
            ->leftJoin($Tempketerangan . ' AS e', 'e.nobukti', '=', 'a.nobukti')

            
            ->orderBy('D.namaagen')
            ->orderBy('C.tglbukti')
            ->orderBy('C.nobukti');
        // dd($select_data->get());
        $data = $select_data->get();
        return $data;
    }





    public function getExport($dari, $sampai, $agenDari, $agenSampai)
    {

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $Temppiutang = '##Temppiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutang, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        $select_Temppiutang = DB::table('piutangheader')->from(DB::raw("piutangheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                DB::raw('SUM(B.nominal) as nominal')
            ])
            ->join('piutangdetail AS B', 'A.nobukti', '=', 'B.NoBukti')
            ->where('A.agen_id', '>=', $agenDari)
            ->where('A.agen_id', '<=', $agenSampai)
            ->groupBy('A.nobukti');

        DB::table($Temppiutang)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal',
        ], $select_Temppiutang);
        // dd($select_Temppiutang->get());



        $Temppiutangbyr = '##Temppiutangbyr' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyr, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangbyr = DB::table('pelunasanPiutangheader')->from(DB::raw("pelunasanPiutangheader as A with (readuncommitted)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                'B.piutang_nobukti',
                DB::raw('SUM(B.nominal) as nominal')
            ])
            ->join(DB::raw("pelunasanPiutangdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw($Temppiutang . " AS C with (readuncommitted)"), 'B.piutang_nobukti', 'C.nobukti')
            ->groupBy('A.nobukti', 'B.piutang_nobukti');


        DB::table($Temppiutangbyr)->insertUsing([
            'tglbukti',
            'nobukti',
            'piutang_nobukti',
            'nominal',
        ], $select_Temppiutangbyr);
        //   dd($select_Temppiutangbyr->get());


        //NOTE - Temppiutangsaldo
        $Temppiutangsaldo = '##Temppiutangsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangsaldo = DB::table($Temppiutang)->from(DB::raw($Temppiutang . " AS a"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.nominal'
            ])
            ->where('A.tglbukti', '>', $dari);

        DB::table($Temppiutangsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal'
        ], $select_Temppiutangsaldo);
        // dd($select_Temppiutangsaldo->get());




        //NOTE - Temphutangbyrsaldo
        $Temppiutangbyrsaldo = '##Temppiutangbyrsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyrsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangbyrsaldo = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS a"))
            ->select([
                DB::raw('MAX(a.tglbukti) as tglbukti'),
                DB::raw('MAX(a.nobukti) as nobukti'),
                'a.piutang_nobukti',
                DB::raw('SUM(a.nominal) as nominal'),

            ])

            ->where('a.tglbukti', '<', $dari)
            ->groupBy('a.piutang_nobukti');

        DB::table($Temppiutangbyrsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'piutang_nobukti',
            'nominal'
        ], $select_Temppiutangbyrsaldo);
        //datanya tidak ada
        // dd($select_Temppiutangbyrsaldo->get());

        //NOTE - TemppiutangbyrsaldoCicil
        $TemppiutangbyrsaldoCicil = '##TemppiutangbyrsaldoCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemppiutangbyrsaldoCicil, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
            $table->integer('urut');
        });

        $select_TemppiutangbyrsaldoCicil = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS a"))
            ->select([
                'A.tglbukti as tglbukti',
                'A.nobukti',
                'A.piutang_nobukti',
                'A.nominal as nominal',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY A.piutang_nobukti ORDER BY A.tglbukti) as urut')
            ])
            ->where('A.tglbukti', '<', $dari);
        //datanya tidak ada
        // dd("ASdas");
        // dd($select_TemppiutangbyrsaldoCicil->get());

        $Temppiutangberjalan = '##Temppiutangberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangberjalan, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });


        $Temppiutangbyrberjalan = '##Temppiutangbyrberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyrberjalan, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        $TemppiutangbyrberjalanCicil = '##TemppiutangbyrberjalanCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemppiutangbyrberjalanCicil, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
            $table->integer('urut');
        });

        $select_Temppiutangberjalan = DB::table($Temppiutang)->from(DB::raw($Temppiutang . " AS a"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.nominal',
            ])
            ->where('A.tglbukti', '>', $dari)
            ->where('A.tglbukti', '<=', $sampai);

        DB::table($Temppiutangberjalan)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal'
        ], $select_Temppiutangberjalan);
        // dd($select_Temppiutangberjalan->get());

        $select_Temppiutangbyrberjalan = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS a"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                DB::raw('MAX(A.nobukti) as nobukti'),
                'A.piutang_nobukti',
                DB::raw('SUM(A.nominal) as nominal')
            ])
            ->where('A.tglbukti', '>', $dari)
            ->where('A.tglbukti', '<=', $sampai)
            ->groupBy('A.piutang_nobukti');

        DB::table($Temppiutangbyrberjalan)->insertUsing([
            'tglbukti',
            'nobukti',
            'piutang_nobukti',
            'nominal',
        ], $select_Temppiutangbyrberjalan);
        // dd($select_Temppiutangbyrberjalan->get());


        $TempCicil = '##TempCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempCicil, function ($table) {
            $table->string('piutang_nobukti', 50);
            $table->integer('urut');
        });

        $select_TempCicil = DB::table($TemppiutangbyrsaldoCicil)->from(DB::raw($TemppiutangbyrsaldoCicil))
            ->select([
                'piutang_nobukti',
                DB::raw('MAX(urut) as urut'),
            ])
            ->groupBy('piutang_nobukti');

        DB::table($TempCicil)->insertUsing([
            'piutang_nobukti',
            'urut',
        ], $select_TempCicil);
        // dd($select_TempCicil->get());

        $TempCicilRekap = '##TempCicilRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempCicilRekap, function ($table) {
            $table->string('piutang_nobukti', 50);
            $table->integer('urut');
        });

        $select_TempCicilRekap = DB::table($TempCicil)->from(DB::raw($TempCicil))
            ->select([
                'piutang_nobukti',
                DB::raw('SUM(urut) as urut'),
            ])
            ->groupBy('piutang_nobukti');

        DB::table($TempCicilRekap)->insertUsing([
            'piutang_nobukti',
            'urut',
        ], $select_TempCicilRekap);
        //    dd($select_TempCicilRekap->get());


        $TempRekappiutang = '##TempRekappiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempRekappiutang, function ($table) {
            $table->bigIncrements('id');
            $table->string('nobukti', 50);
            $table->double('nominal');
            $table->double('bayar');
        });



        $select_TempRekappiutang = DB::table($Temppiutangsaldo . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0)) as saldo"),
                DB::raw("ISNULL(C.nominal, 0) as bayar")
            ])
            ->leftJoin($Temppiutangbyrsaldo . ' AS B', 'A.nobukti', '=', 'B.piutang_nobukti')
            ->leftJoin($Temppiutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.piutang_nobukti')
            ->join('piutangheader AS D', 'A.nobukti', '=', 'D.nobukti')
            ->where(DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0))"), '<>', 0)
            ->orderBy('D.agen_id')
            ->orderBy('A.nobukti');
        // dd($select_TempRekappiutang->get()); sampai sini datanya ada


        DB::table($TempRekappiutang)->insertUsing([
            'nobukti',
            'nominal',
            'bayar',
        ], $select_TempRekappiutang);

        // dd($select_TempRekappiutang->get());

        $select_TempRekappiutang2 = DB::table($Temppiutangberjalan . ' AS A')
            ->select([
                'A.nobukti',
                'A.nominal',
                DB::raw('ISNULL(C.nominal, 0) as bayar')
            ])

            ->leftJoin($Temppiutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.piutang_nobukti');

        // dd($select_TempRekappiutang2->get());

        DB::table($TempRekappiutang)->insertUsing([
            'nobukti',
            'nominal',
            'bayar',
        ], $select_TempRekappiutang2);
        // dd($select_TempRekappiutang2->get());

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $select_data = DB::table($TempRekappiutang . ' AS A')
            ->select([
                'D.namaagen',
                'C.keterangan',
                'A.nobukti',
                'C.tglbukti',
                'C.tgljatuhtempo',
                DB::raw('ISNULL(B.urut, 0) + 1 as cicil'),
                'A.nominal',
                'A.bayar',
                DB::raw('SUM((ISNULL(A.nominal, 0) - A.bayar)) OVER (PARTITION BY D.namaagen ORDER BY A.id ASC) as Saldo'),
                DB::raw("'$getJudul->text' AS text"),
                DB::raw("'$dari' AS dari"),
                DB::raw("'$sampai' AS sampai"),
                DB::raw("'Laporan Kartu Piutang Per Agen' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
            ])
            ->leftJoin($TempCicilRekap . ' AS B', 'A.nobukti', '=', 'B.piutang_nobukti')
            ->join('piutangheader AS C', 'A.nobukti', '=', 'C.nobukti')
            ->join('agen AS D', 'C.agen_id', '=', 'D.id')
            ->orderBy('D.namaagen')
            ->orderBy('C.tglbukti')
            ->orderBy('C.nobukti');
        // dd($select_data->get());
        $data = $select_data->get();
        return $data;
    }
}
