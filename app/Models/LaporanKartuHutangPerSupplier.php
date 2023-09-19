<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKartuHutangPerSupplier extends MyModel
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

    public function getReport($dari, $sampai, $supplierdari, $suppliersampai)
    {

        $sampai=$dari;
        $tgl = '01-' . date('m', strtotime($dari)) . '-' . date('Y', strtotime($dari));
        $dari1 = date('Y-m-d', strtotime($tgl));

        if ($supplierdari==0) {
            $supplierdari=db::table('supplier')->from(db::raw("supplier with (readuncommitted)"))
                ->select('id')->orderby('id','asc')->first()->id ?? 0;
        }
     
        if ($suppliersampai==0) {
            $suppliersampai=db::table('supplier')->from(db::raw("supplier with (readuncommitted)"))
                ->select('id')->orderby('id','desc')->first()->id ?? 0;
        }

        if ($supplierdari>$suppliersampai) {
            $supplierdari1=$suppliersampai;
            $suppliersampai1=$supplierdari;
            $supplierdari=$supplierdari1;
            $suppliersampai=$suppliersampai1;
            
        }


        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $Temphutang = '##Temphutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutang, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        $select_Temphutang = DB::table('hutangheader')->from(DB::raw("hutangheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                DB::raw('SUM(B.total) as nominal')
            ])
            ->join('hutangdetail AS B', 'A.nobukti', '=', 'B.NoBukti')
            ->where('A.supplier_id', '>=', $supplierdari)
            ->where('A.supplier_id', '<=', $suppliersampai)
            ->groupBy('A.nobukti');

        DB::table($Temphutang)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal',
        ], $select_Temphutang);

        $Temphutangbyr = '##Temphutangbyr' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangbyr, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temphutangbyr = DB::table('hutangbayarheader')->from(DB::raw("hutangbayarheader as A with (readuncommitted)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                'B.hutang_nobukti',
                DB::raw('SUM(B.nominal) as nominal')
            ])
            ->join(DB::raw("hutangbayardetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw($Temphutang . " AS C with (readuncommitted)"), 'B.hutang_nobukti', 'C.nobukti')
            ->groupBy('A.nobukti', 'B.hutang_nobukti');


        DB::table($Temphutangbyr)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal',
        ], $select_Temphutangbyr);
        //   dd($select_Temphutangbyr->get());

        //NOTE - Temphutangsaldo
        $Temphutangsaldo = '##Temphutangsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->double('nominal');
        });

        $select_Temphutangsaldo = DB::table($Temphutang)->from(DB::raw($Temphutang . " AS a"))
        ->select([
            'A.tglbukti',
            'A.nobukti',
            'A.nominal'
        ])
        ->where('A.tglbukti', '<', $dari1);


        DB::table($Temphutangsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal'
        ], $select_Temphutangsaldo);        



        //NOTE - Temphutangbyrsaldo
        $Temphutangbyrsaldo = '##Temphutangbyrsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangbyrsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
        });
        // dd($Temphutangbyrsaldo);
        //NOTE - Temphutangbyrsaldo select insert
        $select_Temphutangbyrsaldo = DB::table($Temphutangbyr)->from(DB::raw($Temphutangbyr . " AS a"))
            ->select([
                DB::raw('MAX(a.tglbukti) as tglbukti'),
                DB::raw('MAX(a.nobukti) as nobukti'),
                'a.hutang_nobukti',
                DB::raw('SUM(a.nominal) as nominal'),

            ])

            ->where('a.tglbukti', '<', $dari1)
            ->groupBy('a.hutang_nobukti');

        DB::table($Temphutangbyrsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal'
        ], $select_Temphutangbyrsaldo);


        //NOTE - Temphutangbyrsaldo
        $TemphutangbyrsaldoCicil = '##TemphutangbyrsaldoCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemphutangbyrsaldoCicil, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
            $table->double('urut');
        });



        //NOTE - Temphutangbyrsaldo select insert
        $select_TemphutangbyrsaldoCicil = DB::table($Temphutangbyr)->from(DB::raw($Temphutangbyr . " AS a"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.hutang_nobukti',
                'A.nominal',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY A.hutang_nobukti ORDER BY A.tglbukti) as urut')
            ])
            ->where('A.tglbukti', '>=', $dari1)
            ->where('A.tglbukti', '<=', $sampai);
        // dd($select_TemphutangbyrsaldoCicil->get());



        DB::table($TemphutangbyrsaldoCicil)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal',
            'urut',
        ], $select_TemphutangbyrsaldoCicil);



        $Temphutangberjalan = '##Temphutangberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangberjalan, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        $select_Temphutangberjalan = DB::table($Temphutang)->from(DB::raw($Temphutang . " AS A"))
        ->select([
            'A.tglbukti',
            'A.nobukti',
            'A.nominal',
        ])
        ->where('A.tglbukti', '>=', $dari1)
        ->where('A.tglbukti', '<=', $sampai);

     
        // dd(db::table($Temppiutang)->get());
        // dd($select_Temphutangberjalan->get());

    DB::table($Temphutangberjalan)->insertUsing([
        'tglbukti',
        'nobukti',
        'nominal'
    ], $select_Temphutangberjalan);



        $Temphutangbyrberjalan = '##Temphutangbyrberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangbyrberjalan, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
        });

        

        $select_Temphutangbyrberjalan = DB::table($Temphutangbyr)->from(DB::raw($Temphutangbyr . " AS A"))
        ->select([
            DB::raw('MAX(A.tglbukti) as tglbukti'),
            DB::raw('MAX(A.nobukti) as nobukti'),
            'A.hutang_nobukti',
            DB::raw('SUM(A.nominal) as nominal')
        ])
        ->where('A.tglbukti', '>=', $dari1)
        ->where('A.tglbukti', '<=', $sampai)
        ->groupBy('A.hutang_nobukti');

    DB::table($Temphutangbyrberjalan)->insertUsing([
        'tglbukti',
        'nobukti',
        'hutang_nobukti',
        'nominal',
    ], $select_Temphutangbyrberjalan);
        

        // dd($select_TemphutangbyrberjalanCicil->get());

        $TemphutangbyrberjalanCicil = '##TemphutangbyrberjalanCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemphutangbyrberjalanCicil, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
            $table->integer('urut');
        });

        $select_TemphutangbyrberjalanCicil = DB::table($Temphutangbyr)->from(DB::raw($Temphutangbyr . " AS A"))
            ->select([
                DB::raw('A.tglbukti as tglbukti'),
                DB::raw('A.nobukti as nobukti'),
                'A.hutang_nobukti',
                DB::raw('A.nominal as nominal'),
                DB::raw("row_number() Over(partition BY A.hutang_nobukti Order By A.tglbukti) as urut")
            ])
            ->where('A.tglbukti', '>=', $dari1)
            ->where('A.tglbukti', '<=', $sampai);

        DB::table($TemphutangbyrberjalanCicil)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal',
            'urut',
        ], $select_TemphutangbyrberjalanCicil);

        // 

        $TempCicil = '##TempCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempCicil, function ($table) {
            $table->string('hutang_nobukti', 50);
            $table->integer('urut');
        });



        $select_TempCicil = DB::table($TemphutangbyrsaldoCicil)->from(DB::raw($TemphutangbyrsaldoCicil))
            ->select([
                'hutang_nobukti',
                DB::raw('MAX(urut) as urut'),
            ])
            ->groupBy('hutang_nobukti');


        DB::table($TempCicil)->insertUsing([
            'hutang_nobukti',
            'urut',
        ], $select_TempCicil);

        $TempCicilRekap = '##TempCicilRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempCicilRekap, function ($table) {
            $table->string('hutang_nobukti', 50);
            $table->integer('urut');
        });

        $select_TempCicilRekap = DB::table($TempCicil)->from(DB::raw($TempCicil))
            ->select([
                'hutang_nobukti',
                DB::raw('SUM(urut) as urut'),
            ])
            ->groupBy('hutang_nobukti');

        DB::table($TempCicilRekap)->insertUsing([
            'hutang_nobukti',
            'urut',
        ], $select_TempCicilRekap);
        //  dd($select_TempCicilRekap->get());
     

        $TempRekapHutang = '##TempRekapHutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempRekapHutang, function ($table) {
            // $table->bigIncrements('id');
            $table->id();
            $table->string('nobukti', 50);
            $table->double('nominal');
            $table->double('bayar');
            $table->integer('supplier_id');
        });



        $select_TempRekapHutang = DB::table($Temphutangsaldo . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0)) as saldo"),
                DB::raw("ISNULL(C.nominal, 0) as bayar"),
                DB::raw("d.supplier_id"),

            ])
            ->leftJoin($Temphutangbyrsaldo . ' AS B', 'A.nobukti', '=', 'B.hutang_nobukti')
            ->leftJoin($Temphutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.hutang_nobukti')
            ->join('hutangheader AS D', 'A.nobukti', '=', 'D.nobukti')
            ->where(DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0))"), '<>', 0)
            ->orderBy('d.supplier_id')
            ->orderBy('d.tglbukti')
            ->orderBy('a.nobukti');

   

        DB::table($TempRekapHutang)->insertUsing([
            'nobukti',
            'nominal',
            'bayar',
            'supplier_id',
        ], $select_TempRekapHutang);
        // dd($select_TempRekapHutang->get());
      
        $select_TempRekapHutang2 = DB::table($Temphutangberjalan . ' AS A')
            ->select([
                'A.nobukti',
                'A.nominal',
                DB::raw('ISNULL(C.nominal, 0) as bayar'),
                DB::raw("d.supplier_id"),             
            ])
            ->leftJoin($Temphutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.hutang_nobukti')
            ->leftjoin('hutangheader AS D', 'A.nobukti', '=', 'D.nobukti');

        DB::table($TempRekapHutang)->insertUsing([
            'nobukti',
            'nominal',
            'bayar',
            'supplier_id',
        ], $select_TempRekapHutang2);

        // dd('test');
        // dd(db::table($TempRekapHutang)->get());

        $Tempjt = '##Tempjt' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempjt, function ($table) {
            $table->string('nobukti', 50);
            $table->datetime('tgljatuhtempo');
            $table->LongText('keterangan');
        });

        $select_Tempjt = DB::table($TempRekapHutang . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw('MAX(B.tgljatuhtempo) as tgljatuhtempo'),
                DB::raw('MAX(B.keterangan) as keterangan'),

            ])
            ->join('hutangdetail as b', 'A.nobukti', 'b.nobukti')
            ->groupBy('A.nobukti');


        DB::table($Tempjt)->insertUsing([
            'nobukti',
            'tgljatuhtempo',
            'keterangan'
        ], $select_Tempjt);
        //  dd(db::table($TempRekapHutang)->get());

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DISETUJUI')
        ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

    $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DIPERIKSA')
        ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $select_data = DB::table($TempRekapHutang . ' AS A')
            ->select([
                'a.id',
                'D.namasupplier',
                db::raw("(case when isnull(C.keterangan,'')='' then isnull(e.keterangan,'') else isnull(C.keterangan,'') end) as keterangan"),
                'A.nobukti',
                'C.tglbukti',
                'E.tgljatuhtempo',
                DB::raw('ISNULL(B.urut, 0) + 1 as cicil'),
                'A.nominal',
                'A.bayar',
                DB::raw('SUM((ISNULL(A.nominal, 0) - A.bayar)) OVER (PARTITION BY c.supplier_id ORDER BY A.id ASC) as Saldo'),
                DB::raw("'$getJudul->text' AS text"),
                DB::raw("'$dari' AS dari"),
                DB::raw("'$sampai' AS sampai"),
                DB::raw("'Laporan Kartu Hutang Per Supplier' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            ])
            ->leftJoin($TempCicilRekap . ' AS B', 'A.nobukti', '=', 'B.hutang_nobukti')
            ->join('hutangheader AS C', 'A.nobukti', '=', 'C.nobukti')
            ->join('supplier AS D', 'C.supplier_id', '=', 'D.id')
            ->leftJoin($Tempjt . ' AS E', 'A.nobukti', '=', 'E.nobukti')
            ->orderBy('c.supplier_id')
            ->orderBy('c.tglbukti')
            ->orderBy('c.nobukti');


        // dd($select_data->get());
        $data = $select_data->get();
        return $data;
    }





    public function getExport($dari, $sampai, $supplierdari, $suppliersampai)
    {

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $Temphutang = '##Temphutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutang, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        $select_Temphutang = DB::table('hutangheader')->from(DB::raw("hutangheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                DB::raw('SUM(B.total) as nominal')
            ])
            ->join('hutangdetail AS B', 'A.nobukti', '=', 'B.NoBukti')
            ->where('A.supplier_id', '>=', $supplierdari)
            ->where('A.supplier_id', '<=', $suppliersampai)
            ->groupBy('A.nobukti');

        DB::table($Temphutang)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal',
        ], $select_Temphutang);

        // dd(DB::table($Temphutang)->get());
        $Temphutangbyr = '##Temphutangbyr' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangbyr, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temphutangbyr = DB::table('hutangbayarheader')->from(DB::raw("hutangbayarheader as A with (readuncommitted)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                'B.hutang_nobukti',
                DB::raw('SUM(B.nominal) as nominal')
            ])
            ->join(DB::raw("hutangbayardetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw($Temphutang . " AS C with (readuncommitted)"), 'B.hutang_nobukti', 'C.nobukti')
            ->groupBy('A.nobukti', 'B.hutang_nobukti');


        DB::table($Temphutangbyr)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal',
        ], $select_Temphutangbyr);
        //   dd($select_Temphutangbyr->get());

        //NOTE - Temphutangsaldo
        $Temphutangsaldo = '##Temphutangsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->double('nominal');
        });

        //NOTE - Temphutangbyrsaldo
        $Temphutangbyrsaldo = '##Temphutangbyrsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangbyrsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
        });
        // dd($Temphutangbyrsaldo);
        //NOTE - Temphutangbyrsaldo select insert
        $select_Temphutangbyrsaldo = DB::table($Temphutangbyr)->from(DB::raw($Temphutangbyr . " AS a"))
            ->select([
                DB::raw('MAX(a.tglbukti) as tglbukti'),
                DB::raw('MAX(a.nobukti) as nobukti'),
                'a.hutang_nobukti',
                DB::raw('SUM(a.nominal) as nominal'),

            ])

            ->where('a.tglbukti', '<', $dari)
            ->groupBy('a.hutang_nobukti');

        DB::table($Temphutangbyrsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal'
        ], $select_Temphutangbyrsaldo);


        //NOTE - Temphutangbyrsaldo
        $TemphutangbyrsaldoCicil = '##TemphutangbyrsaldoCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemphutangbyrsaldoCicil, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
            $table->double('urut');
        });



        //NOTE - Temphutangsaldo select insert
        $select_Temphutangsaldo = DB::table($Temphutang)->from(DB::raw($Temphutang . " AS a"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.nominal'
            ])
            ->where('A.tglbukti', '>', $dari);

        DB::table($Temphutangsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal'
        ], $select_Temphutangsaldo);
        // dd("Sda");
        // dd($select_Temphutangsaldo->get());









        //NOTE - Temphutangbyrsaldo select insert
        $select_TemphutangbyrsaldoCicil = DB::table($Temphutangbyr)->from(DB::raw($Temphutangbyr . " AS a"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.hutang_nobukti',
                'A.nominal',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY A.hutang_nobukti ORDER BY A.tglbukti) as urut')
            ])
            ->where('A.tglbukti', '<', $dari);
        // dd($select_TemphutangbyrsaldoCicil->get());



        DB::table($TemphutangbyrsaldoCicil)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal',
            'urut',
        ], $select_TemphutangbyrsaldoCicil);



        $Temphutangberjalan = '##Temphutangberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangberjalan, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });


        $Temphutangbyrberjalan = '##Temphutangbyrberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangbyrberjalan, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
        });

        $TemphutangbyrberjalanCicil = '##TemphutangbyrberjalanCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemphutangbyrberjalanCicil, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
            $table->integer('urut');
        });

        $select_Temphutangberjalan = DB::table($Temphutang)->from(DB::raw($Temphutang . " AS a"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.nominal',
            ])
            ->where('A.tglbukti', '>', $dari)
            ->where('A.tglbukti', '<=', $sampai);

        DB::table($Temphutangberjalan)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal'
        ], $select_Temphutangberjalan);
        // dd($select_Temphutangberjalan->get());

        $select_Temphutangbyrberjalan = DB::table($Temphutangbyr)->from(DB::raw($Temphutangbyr . " AS a"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                DB::raw('MAX(A.nobukti) as nobukti'),
                'A.hutang_nobukti',
                DB::raw('SUM(A.nominal) as nominal')
            ])
            ->where('A.tglbukti', '>', $dari)
            ->where('A.tglbukti', '<=', $sampai)
            ->groupBy('A.hutang_nobukti');

        DB::table($Temphutangbyrberjalan)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal',
        ], $select_Temphutangbyrberjalan);

        // dd($select_Temphutangbyrberjalan->get());

        $select_TemphutangbyrberjalanCicil = DB::table($Temphutangbyr)->from(DB::raw($Temphutangbyr . " AS a"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.hutang_nobukti',
                DB::raw('(A.nominal) as nominal'),
                DB::raw('ROW_NUMBER() OVER (PARTITION BY A.hutang_nobukti ORDER BY A.tglbukti) as urut')
            ])
            ->where('A.tglbukti', '>', $dari)
            ->where('A.tglbukti', '<=', $sampai);

        DB::table($TemphutangbyrberjalanCicil)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal',
            'urut'
        ], $select_TemphutangbyrberjalanCicil);

        // dd($select_TemphutangbyrberjalanCicil->get());


        $TempCicil = '##TempCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempCicil, function ($table) {
            $table->string('hutang_nobukti', 50);
            $table->integer('urut');
        });

        $select_TempCicil = DB::table($TemphutangbyrberjalanCicil)->from(DB::raw($TemphutangbyrberjalanCicil))
            ->select([
                'hutang_nobukti',
                DB::raw('MAX(urut) as urut'),
            ])
            ->groupBy('hutang_nobukti');

        // DB::statement('SET IDENTITY_INSERT ' . $TempCicil . ' ON');
        // DB::table($TempCicil)->insert($select_TempCicil->get()->toArray());
        // DB::statement('SET IDENTITY_INSERT ' . $TempCicil . ' OFF');

        DB::table($TempCicil)->insertUsing([
            'hutang_nobukti',
            'urut',
        ], $select_TempCicil);
        // dd($select_TempCicil->get());

        $TempCicilRekap = '##TempCicilRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempCicilRekap, function ($table) {
            $table->string('hutang_nobukti', 50);
            $table->integer('urut');
        });

        $select_TempCicilRekap = DB::table($TempCicil)->from(DB::raw($TempCicil))
            ->select([
                'hutang_nobukti',
                DB::raw('SUM(urut) as urut'),
            ])
            ->groupBy('hutang_nobukti');

        DB::table($TempCicilRekap)->insertUsing([
            'hutang_nobukti',
            'urut',
        ], $select_TempCicilRekap);
        //  dd($select_TempCicilRekap->get());


        $TempRekapHutang = '##TempRekapHutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempRekapHutang, function ($table) {
            $table->bigIncrements('id');
            $table->string('nobukti', 50);
            $table->double('nominal');
            $table->double('bayar');
        });

        // $select_TempRekapHutang = DB::table($Temphutangsaldo)->from(DB::raw($Temphutangsaldo))
        // ->select([
        //     'A.nobukti',
        //     DB::raw("(isnull(A.nominal,0)-isnull(B.nominal,0)) as saldo"),
        //     DB::raw("isnull(C.nominal,0) as bayar"),

        // ])
        // ->leftJoin($Temphutangbyrsaldo . ' AS B', 'A.nobukti', '=', 'B.hutang_nobukti')
        // ->leftJoin($Temphutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.hutang_nobukti')
        // ->join('hutangheader AS D', 'A.nobukti', '=', 'D.nobukti');
        // dd($select_TempRekapHutang->get());

        $select_TempRekapHutang = DB::table($Temphutangsaldo . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0)) as saldo"),
                DB::raw("ISNULL(C.nominal, 0) as bayar")
            ])
            ->leftJoin($Temphutangbyrsaldo . ' AS B', 'A.nobukti', '=', 'B.hutang_nobukti')
            ->leftJoin($Temphutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.hutang_nobukti')
            ->join('hutangheader AS D', 'A.nobukti', '=', 'D.nobukti')
            ->where(DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0))"), '<>', 0)
            ->orderBy('D.supplier_id')
            ->orderBy('A.nobukti');

        DB::table($TempRekapHutang)->insertUsing([
            'nobukti',
            'nominal',
            'bayar',
        ], $select_TempRekapHutang);
        // dd($select_TempRekapHutang->get());

        $select_TempRekapHutang2 = DB::table($Temphutangberjalan . ' AS A')
            ->select([
                'A.nobukti',
                'A.nominal',
                DB::raw('ISNULL(C.nominal, 0) as bayar')
            ])
            ->leftJoin($Temphutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.hutang_nobukti');

        DB::table($TempRekapHutang)->insertUsing([
            'nobukti',
            'nominal',
            'bayar',
        ], $select_TempRekapHutang);
        // dd($select_TempRekapHutang2->get());

        $Tempjt = '##Tempjt' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempjt, function ($table) {
            $table->string('nobukti', 50);
            $table->datetime('tgljatuhtempo');
        });

        $select_Tempjt = DB::table($TempRekapHutang . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw('MAX(B.tgljatuhtempo) as tgljatuhtempo'),
            ])
            ->join('hutangdetail as b', 'A.nobukti', 'b.nobukti')
            ->groupBy('A.nobukti');


        DB::table($Tempjt)->insertUsing([
            'nobukti',
            'tgljatuhtempo',
        ], $select_Tempjt);
        // dd($select_Tempjt->get());
        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DISETUJUI')
        ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

    $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DIPERIKSA')
        ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $select_data = DB::table($TempRekapHutang . ' AS A')
            ->select([
                'D.namasupplier',
                'C.keterangan',
                'A.nobukti',
                'C.tglbukti',
                'E.tgljatuhtempo',
                DB::raw('ISNULL(B.urut, 0) + 1 as cicil'),
                'A.nominal',
                'A.bayar',
                DB::raw('SUM((ISNULL(A.nominal, 0) - A.bayar)) OVER (PARTITION BY D.namasupplier ORDER BY A.id ASC) as Saldo'),
                DB::raw("'$getJudul->text' AS text"),
                DB::raw("'$dari' AS dari"),
                DB::raw("'$sampai' AS sampai"),
                DB::raw("'Laporan Kartu Hutang Per Supplier' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            ])
            ->leftJoin($TempCicilRekap . ' AS B', 'A.nobukti', '=', 'B.hutang_nobukti')
            ->join('hutangheader AS C', 'A.nobukti', '=', 'C.nobukti')
            ->join('supplier AS D', 'C.supplier_id', '=', 'D.id')
            ->leftJoin($Tempjt . ' AS E', 'A.nobukti', '=', 'E.nobukti')
            ->orderBy('D.namasupplier')
            ->orderBy('C.tglbukti')
            ->orderBy('C.nobukti');

        // dd($select_data->get());
        $data = $select_data->get();
        return $data;
    }
}
