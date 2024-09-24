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
    public function getReport($dari, $sampai, $supplierdari, $suppliersampai, $prosesneraca)
    {
        $prosesneraca = $prosesneraca ?? 0;

        $sampai = $dari;
        $tgl = '01-' . date('m', strtotime($dari)) . '-' . date('Y', strtotime($dari));
        $dari1 = date('Y-m-d', strtotime($tgl));

        $keterangansupplier='';
        if ($supplierdari==0 || $suppliersampai==0 )  {
            $keterangansupplier='SEMUA';
        }

        if ($supplierdari == 0 || $suppliersampai == 0 ) {
            $supplierdari = db::table('supplier')->from(db::raw("supplier with (readuncommitted)"))
                ->select('id')->orderby('id', 'asc')->first()->id ?? 0;

                $suppliersampai = db::table('supplier')->from(db::raw("supplier with (readuncommitted)"))
                ->select('id')->orderby('id', 'desc')->first()->id ?? 0;

            }


        if ($supplierdari > $suppliersampai) {
            $supplierdari1 = $suppliersampai;
            $suppliersampai1 = $supplierdari;
            $supplierdari = $supplierdari1;
            $suppliersampai = $suppliersampai1;
        }

        $supplierdarinama=db::table('supplier')->from(db::raw("supplier with (readuncommitted)"))
        ->select('namasupplier')->orderby('id', 'asc')->where('id',$supplierdari)->first()->namasupplier ?? '';

        $suppliersampainama=db::table('supplier')->from(db::raw("supplier with (readuncommitted)"))
        ->select('namasupplier')->orderby('id', 'asc')->where('id',$suppliersampai)->first()->namasupplier ?? '';

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        // 

        $tglsaldo = DB::table('parameter')
            ->select('text')
            ->where('grp', 'SALDO')
            ->where('subgrp', 'SALDO')
            ->first()->text ?? '1900-01-01';

        $temppelunasansaldo = '##temppelunasansaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppelunasansaldo, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        $temphutangsaldo = '##temphutangsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphutangsaldo, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();

            $table->index('nobukti', 'temphutangsaldo_nobukti_index');

        });

        if ($prosesneraca==1) {
            $queryhutangsaldo = db::table('hutangheader')->from(db::raw("hutangheader a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                db::raw("sum(b.total) as nominal"),
            )
            ->join(db::raw("hutangdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti<'" . $dari1 . "'")
            ->whereRaw("a.coakredit='03.02.02.01'")
            ->whereRaw("(a.supplier_id>=" . $supplierdari . " and a.supplier_id<=" . $suppliersampai . ")")
            ->groupby('a.nobukti');
        } else {
            $queryhutangsaldo = db::table('hutangheader')->from(db::raw("hutangheader a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                db::raw("sum(b.total) as nominal"),
            )
            ->join(db::raw("hutangdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti<'" . $dari1 . "'")
            ->whereRaw("(a.supplier_id>=" . $supplierdari . " and a.supplier_id<=" . $suppliersampai . ")")
            ->groupby('a.nobukti');
        }


        DB::table($temphutangsaldo)->insertUsing([
            'nobukti',
            'nominal',
        ], $queryhutangsaldo);
      

        $querypelunasansaldo = db::table('pelunasanhutangheader')->from(db::raw("pelunasanhutangheader a with (readuncommitted)"))
            ->select(
                'c.nobukti',
                db::raw("sum(isnull(b.nominal,0)+isnull(b.potongan,0)) as nominal"),
            )
            ->join(db::raw("pelunasanhutangdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw($temphutangsaldo . " c "), 'b.hutang_nobukti', 'c.nobukti')
            ->whereRaw("a.tglbukti<'" . $dari1 . "' and a.tglbukti>'".$tglsaldo."'")
            ->groupby('c.nobukti');

        DB::table($temppelunasansaldo)->insertUsing([
            'nobukti',
            'nominal',
        ], $querypelunasansaldo);


        $temprekaphutang = '##temprekaphutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekaphutang, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });


        $queryrekaphutang = db::table($temphutangsaldo)->from(db::raw($temphutangsaldo . " a "))
            ->select(
                'a.nobukti',
                db::raw("(isnull(a.nominal,0)-isnull(b.nominal,0)) as nominal"),
            )
            ->leftjoin(db::raw($temppelunasansaldo . " b "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(isnull(a.nominal,0)-isnull(b.nominal,0))<>0");

        DB::table($temprekaphutang)->insertUsing([
            'nobukti',
            'nominal',
        ], $queryrekaphutang);


        if ($prosesneraca==1) {
            $queryrekaphutang = db::table("hutangheader")->from(db::raw("hutangheader a with (readuncommitted) "))
            ->select(
                'a.nobukti',
                db::raw("sum(isnull(b.total,0)) as nominal"),
            )
            ->leftjoin(db::raw("hutangdetail b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(a.tglbukti>='" . $dari1 . "' and a.tglbukti<='" . $sampai . "')")
            ->whereRaw("(a.supplier_id>=" . $supplierdari . " and a.supplier_id<=" . $suppliersampai . ")")
            ->whereRaw("a.coakredit='03.02.02.01'")
            ->groupby('a.nobukti');
        } else {
            $queryrekaphutang = db::table("hutangheader")->from(db::raw("hutangheader a with (readuncommitted) "))
            ->select(
                'a.nobukti',
                db::raw("sum(isnull(b.total,0)) as nominal"),
            )
            ->leftjoin(db::raw("hutangdetail b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(a.tglbukti>='" . $dari1 . "' and a.tglbukti<='" . $sampai . "')")
            ->whereRaw("(a.supplier_id>=" . $supplierdari . " and a.supplier_id<=" . $suppliersampai . ")")
            ->groupby('a.nobukti');
        }


           

        DB::table($temprekaphutang)->insertUsing([
            'nobukti',
            'nominal',
        ], $queryrekaphutang);

     

        // dd('test');
        $temprekapdata = '##temprekapdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapdata, function ($table) {
            $table->id();
            $table->integer('supplier_id')->nullable();
            $table->string('nobukti', 50);
            $table->dateTime('tglbukti');
            $table->double('nominalhutang')->nullable();
            $table->dateTime('tglbayar');
            $table->double('nominalbayar')->nullable();
            $table->string('nobuktihutang', 50);
            $table->dateTime('tglberjalan');
            $table->string('jenishutang', 50);
            $table->integer('urut')->nullable();

            $table->index('nobukti', 'temprekapdata_nobukti_index');
            $table->index('nobukti', 'temprekapdata_supplier_id_index');
        });


        $queryrekapdata = db::table($temprekaphutang)->from(db::raw($temprekaphutang . " a  "))
            ->select(
                'b.supplier_id',
                'a.nobukti',
                'b.tglbukti',
                'a.nominal',
                db::raw("'1900/1/1' as tglbayar"),
                db::raw("0 as nominalbayar"),
                'a.nobukti as nobuktihutang',
                'b.tglbukti as tglberjalan',
                // db::raw("(case when isnull(c.nobukti,'')=''  and b.tglbukti>'" . $tglsaldo . "'  then 'HUTANG PREDIKSI' else 'HUTANG USAHA' END) as jenishutang"),
                db::raw("(case when isnull(c.nobukti,'')=''  and b.tglbukti>'" . $tglsaldo . "'  then 'HUTANG USAHA' else 'HUTANG USAHA' END) as jenishutang"),
                db::raw("0 as urut"),
                )
            ->join(db::raw("hutangheader b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(db::raw("penerimaanstokheader c with (readuncommitted) "), 'b.nobukti', 'c.hutang_nobukti');
           
        DB::table($temprekapdata)->insertUsing([
            'supplier_id',
            'nobukti',
            'tglbukti',
            'nominalhutang',
            'tglbayar',
            'nominalbayar',
            'nobuktihutang',
            'tglberjalan',
            'jenishutang',
            'urut',
        ], $queryrekapdata);

       
        $queryrekapdata = db::table($temprekaphutang)->from(db::raw($temprekaphutang . " a  "))
            ->select(
                'b.supplier_id',
                'd.nobukti',
                db::raw("'1900/1/1' as tglbukti"),
                db::raw("0 as nominal"),
                "d.tglbukti as tglbayar",
                db::raw("(isnull(c.nominal,0)+isnull(c.potongan,0)) as nominalbayar"),
                'a.nobukti as nobuktihutang',
                'b.tglbukti as tglberjalan',
                // db::raw("(case when isnull(b.nobukti,'')=''  and b.tglbukti>'" . $tglsaldo . "'  then 'HUTANG PREDIKSI' else 'HUTANG USAHA' END) as jenishutang"),
                db::raw("(case when isnull(b.nobukti,'')=''  and b.tglbukti>'" . $tglsaldo . "'  then 'HUTANG USAHA' else 'HUTANG USAHA' END) as jenishutang"),
                db::raw("1 as urut"),                
            )
            ->join(db::raw("hutangheader b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("pelunasanhutangdetail c with (readuncommitted) "), 'a.nobukti', 'c.hutang_nobukti')
            ->join(db::raw("pelunasanhutangheader d with (readuncommitted) "), 'c.nobukti', 'd.nobukti')
            ->whereRaw("(d.tglbukti>='" . $dari1 . "' and d.tglbukti<='" . $sampai . "'  and d.tglbukti>'".$tglsaldo."')");

          

        DB::table($temprekapdata)->insertUsing([
            'supplier_id',
            'nobukti',
            'tglbukti',
            'nominalhutang',
            'tglbayar',
            'nominalbayar',
            'nobuktihutang',
            'tglberjalan',
            'jenishutang',
            'urut',
        ], $queryrekapdata);



        $temprekaphasil = '##temprekaphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekaphasil, function ($table) {
            $table->id();
            $table->string('supplier_id',1000)->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->double('nominalhutang')->nullable();
            $table->dateTime('tglbayar')->nullable();
            $table->double('nominalbayar')->nullable();
            $table->string('nobuktihutang', 50)->nullable();
            $table->dateTime('tglberjalan')->nullable();
            $table->double('saldo')->nullable();
            $table->double('saldobayar')->nullable();
            $table->string('jenishutang', 50)->nullable();
            $table->integer('urut')->nullable();
        });
    
        $queryrekaphasil = db::table($temprekapdata)->from(db::raw($temprekapdata . " a  "))
            ->select(
                'b.namasupplier as supplier_id',
                'a.nobukti',
                db::raw("(case when year(a.tglbukti)=1900 then null else a.tglbukti end ) as tglbukti"),
                'a.nominalhutang',
                db::raw("(case when year(a.tglbayar)=1900 then null else a.tglbayar end ) as tglbayar"),
                'a.nominalbayar',
                'a.nobuktihutang',
                'a.tglberjalan',
                db::raw("SUM(a.nominalhutang-a.nominalbayar) OVER (PARTITION BY a.jenishutang,b.namasupplier ORDER BY a.tglberjalan,a.nobuktihutang,a.urut ASC) as saldo"),
                db::raw("a.nominalhutang-a.nominalbayar  as saldobayar"),
                'a.jenishutang',
                db::raw("0 as urut")
                // 'a.urut'
            )
            ->leftjoin(db::raw("supplier b with (readuncommitted) "), 'a.supplier_id', 'b.id')
     
            ->orderby('b.namasupplier', 'asc')
            ->orderby('a.jenishutang', 'asc')
            ->orderby('a.tglberjalan', 'asc')
            ->orderby('a.nobuktihutang', 'asc')
            ->orderby('a.urut', 'asc');


        DB::table($temprekaphasil)->insertUsing([
            'supplier_id',
            'nobukti',
            'tglbukti',
            'nominalhutang',
            'tglbayar',
            'nominalbayar',
            'nobuktihutang',
            'tglberjalan',
            'saldo',
            'saldobayar',
            'jenishutang',
            'urut',
        ], $queryrekaphasil);
        // dd('test4');
        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

    $temprekapuji = '##temprekapuji' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
    Schema::create($temprekapuji, function ($table) {
        $table->id();
        $table->string('supplier_id_jenishutang',500)->nullable();
        $table->string('nobukti', 50);
        $table->dateTime('tglbukti');
        $table->double('nominalhutang')->nullable();
        $table->dateTime('tglbayar');
        $table->double('nominalbayar')->nullable();
        $table->string('nobuktihutang', 50);
        $table->dateTime('tglberjalan');
        $table->string('jenishutang', 50);
        $table->integer('urut')->nullable();

        $table->index('nobukti', 'temprekapdata_nobukti_index');
        $table->index('nobukti', 'temprekapdata_supplier_id_index');
    });
    

// $queryurut=db::table($temprekaphasil)->from(db::raw($temprekaphasil . " a"))
// ->select(
//     'a.id',
//     'a.supplier_id',
//     'a.jenishutang',
//     'a.nobukti',
//     'a.nobuktihutang',
//     )
//     ->orderby('a.id')
//     ->get();
//     $datadetail = json_decode($queryurut, true);
//     $xuji='';
//     $xnobukti='';
//     $xnobuktihutang='';

    $temprekaphasil1 = '##temprekaphasil1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
    Schema::create($temprekaphasil1, function ($table) {
        $table->id();
        $table->string('supplier_id_jenishutang',1000)->nullable();
        $table->string('nobukti', 50)->nullable();       
        $table->string('nobuktihutang', 50)->nullable();
        $table->dateTime('tglbukti')->nullable();

    });

    $temprekaphasil2 = '##temprekaphasil2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
    Schema::create($temprekaphasil2, function ($table) {
        $table->id();
        $table->string('supplier_id_jenishutang',1000)->nullable();
        $table->string('nobukti', 50)->nullable();
        $table->string('nobuktihutang', 50)->nullable();
        $table->dateTime('tglbukti')->nullable();
        $table->BigInteger('urut')->nullable();
    });

    $queryrekaphasil1=db::table($temprekaphasil)->from(db::raw($temprekaphasil . " a"))
->select(
    db::raw("trim(a.supplier_id)+'_'+trim(a.jenishutang) as supplier_id_jenishutang"),
    'a.nobukti',
    'a.nobuktihutang',
    'a.tglbukti',
    )
    ->whereRaw("isnull(a.nobukti,'')=isnull(a.nobuktihutang,'')")
    ->orderby(db::raw("trim(a.supplier_id)+'_'+trim(a.jenishutang)"));
   
   
    DB::table($temprekaphasil1)->insertUsing([
        'supplier_id_jenishutang',
        'nobukti',
        'nobuktihutang',
        'tglbukti',
    ], $queryrekaphasil1);
    
    $query=db::table($temprekaphasil1)
    ->select(
        'supplier_id_jenishutang',
        'nobukti',
        'nobuktihutang',
        'tglbukti',
        DB::raw('ROW_NUMBER() OVER (PARTITION BY supplier_id_jenishutang ORDER BY tglbukti) as urut')
    )
    ->orderby('tglbukti');

    DB::table($temprekaphasil2)->insertUsing([
        'supplier_id_jenishutang',
        'nobukti',
        'nobuktihutang',
        'tglbukti',
        'urut'
    ], $query);

    // DB::update(DB::raw("UPDATE " . $temprekaphasil . " SET urut=0"));
    // $urut=0;
    // foreach ($datadetail as $item) {
    //     $xuji2=$item['supplier_id'].$item['jenishutang'];

    //     if ($xuji2!=$xuji) {
    //         $urut=0;
    //     }
    //         if ($item['nobukti']==$item['nobuktihutang']) {
    //             $urut=$urut+1;
    //             DB::update(DB::raw("UPDATE " . $temprekaphasil . " SET urut=".$urut." where id=". $item['id']));
    //         }
            
        
    //     $xuji=$item['supplier_id'].$item['jenishutang'];
    //     $xnobukti=$item['nobukti'];
    //     $xnobuktihutang=$item['nobuktihutang'];
    
    // }


        $select_data = DB::table($temprekaphasil . ' AS A')
            ->select([
                'a.id',
                'a.supplier_id',
                'a.nobukti',
                db::raw("cast(a.tglbukti as date) as tglbukti"),
                'a.nominalhutang',
                db::raw("cast(a.tglbayar as date) as tglbayar"),
                'a.nominalbayar',
                'a.nobuktihutang',
                db::raw("cast(a.tglberjalan as date) as tglberjalan"),
                'a.saldo',
                'a.saldobayar',
                'a.jenishutang',
                db::raw("isnull(b.urut,0) as urut"),
                DB::raw("'$getJudul->text' AS text"),
                DB::raw("(case when '$keterangansupplier'='' then '$supplierdarinama' else '$keterangansupplier' end)  AS dari"),
                DB::raw("(case when '$keterangansupplier'='' then '$suppliersampainama' else '$keterangansupplier' end)   AS sampai"),

                DB::raw("'Laporan Kartu Hutang Per Supplier' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            ])
            ->leftjoin(db::raw($temprekaphasil2 ." b"),'a.nobukti','b.nobukti')

            ->orderBy('a.id', 'asc');

            // dd($select_data->get());

        if ($prosesneraca == 1) {
            $data = $select_data;
        } else {
            $data = $select_data->get();
        }

        // $data=$query->get();

        return $data;

        // 
    }

   

    public function getReportOld($dari, $sampai, $supplierdari, $suppliersampai, $prosesneraca)
    {

        $prosesneraca = $prosesneraca ?? 0;

        $sampai = $dari;
        $tgl = '01-' . date('m', strtotime($dari)) . '-' . date('Y', strtotime($dari));
        $dari1 = date('Y-m-d', strtotime($tgl));

        if ($supplierdari == 0) {
            $supplierdari = db::table('supplier')->from(db::raw("supplier with (readuncommitted)"))
                ->select('id')->orderby('id', 'asc')->first()->id ?? 0;
        }

        if ($suppliersampai == 0) {
            $suppliersampai = db::table('supplier')->from(db::raw("supplier with (readuncommitted)"))
                ->select('id')->orderby('id', 'desc')->first()->id ?? 0;
        }

        if ($supplierdari > $suppliersampai) {
            $supplierdari1 = $suppliersampai;
            $suppliersampai1 = $supplierdari;
            $supplierdari = $supplierdari1;
            $suppliersampai = $suppliersampai1;
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

        $select_Temphutangbyr = DB::table('pelunasanhutangheader')->from(DB::raw("pelunasanhutangheader as A with (readuncommitted)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                'B.hutang_nobukti',
                DB::raw('SUM(B.nominal) as nominal')
            ])
            ->join(DB::raw("pelunasanhutangdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw($Temphutang . " AS C with (readuncommitted)"), 'B.hutang_nobukti', 'C.nobukti')
            ->whereRaw("isnull(a.pengeluaran_nobukti,'')<>''")
            ->groupBy('A.nobukti', 'B.hutang_nobukti');


        DB::table($Temphutangbyr)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal',
        ], $select_Temphutangbyr);

        // 


        $Temphutangbyr_saldo = '##Temphutangbyr_saldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangbyr_saldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temphutangbyr_saldo = DB::table('pelunasanhutangheader')->from(DB::raw("pelunasanhutangheader as A with (readuncommitted)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                'B.hutang_nobukti',
                DB::raw('SUM(B.nominal) as nominal')
            ])
            ->join(DB::raw("pelunasanhutangdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw($Temphutang . " AS C with (readuncommitted)"), 'B.hutang_nobukti', 'C.nobukti')
            ->groupBy('A.nobukti', 'B.hutang_nobukti');


        DB::table($Temphutangbyr_saldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal',
        ], $select_Temphutangbyr_saldo);

        // 
        $Temphutangbyrretur = '##Temphutangbyrretur' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangbyrretur, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->string('pengeluaranstok_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temphutangbyrretur = DB::table('pelunasanhutangheader')->from(DB::raw("pelunasanhutangheader as A with (readuncommitted)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                'B.hutang_nobukti',
                DB::raw("max(d.nobukti) as pengeluaranstok_nobukti"),
                DB::raw('SUM(B.nominal) as nominal')
            ])
            ->join(DB::raw("pelunasanhutangdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw($Temphutang . " AS C with (readuncommitted)"), 'B.hutang_nobukti', 'C.nobukti')
            ->join(DB::raw("pengeluaranstokheader as d with (readuncommitted)"), 'A.nobukti', 'd.hutangbayar_nobukti')
            ->whereRaw("isnull(a.pengeluaran_nobukti,'')=''")
            ->whereRaw("isnull(d.hutangbayar_nobukti,'')<>''")
            ->groupBy('A.nobukti', 'B.hutang_nobukti');


        DB::table($Temphutangbyrretur)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'pengeluaranstok_nobukti',
            'nominal',
        ], $select_Temphutangbyrretur);
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
        $select_Temphutangbyrsaldo = DB::table($Temphutangbyr_saldo)->from(DB::raw($Temphutangbyr_saldo . " AS a"))
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

        // 


        // 



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


        // 

        $Temphutangbyrberjalanretur = '##Temphutangbyrberjalanretur' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphutangbyrberjalanretur, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('hutang_nobukti', 100);
            $table->double('nominal');
        });



        $select_Temphutangbyrberjalanretur = DB::table($Temphutangbyrretur)->from(DB::raw($Temphutangbyrretur . " AS A"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                DB::raw('MAX(A.nobukti) as nobukti'),
                'A.hutang_nobukti',
                DB::raw('SUM(A.nominal) as nominal')
            ])
            ->where('A.tglbukti', '>=', $dari1)
            ->where('A.tglbukti', '<=', $sampai)
            ->groupBy('A.hutang_nobukti');

        DB::table($Temphutangbyrberjalanretur)->insertUsing([
            'tglbukti',
            'nobukti',
            'hutang_nobukti',
            'nominal',
        ], $select_Temphutangbyrberjalanretur);

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
            $table->string('nobuktiretur', 50);
            $table->double('nominal');
            $table->double('bayar');
            $table->double('bayarretur');
            $table->integer('supplier_id');
        });



        $select_TempRekapHutang = DB::table($Temphutangsaldo . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw("'' as nobuktiretur"),
                DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0)) as saldo"),
                DB::raw("ISNULL(C.nominal, 0) as bayar"),
                DB::raw("0 as bayarretur"),
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
            'nobuktiretur',
            'nominal',
            'bayar',
            'bayarretur',
            'supplier_id',
        ], $select_TempRekapHutang);
        // dd($select_TempRekapHutang->get());

        $select_TempRekapHutang2 = DB::table($Temphutangberjalan . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw("'' as nobuktiretur"),
                'A.nominal',
                DB::raw('ISNULL(C.nominal, 0) as bayar'),
                DB::raw("0 as bayarretur"),
                DB::raw("d.supplier_id"),
            ])
            ->leftJoin($Temphutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.hutang_nobukti')
            ->leftjoin('hutangheader AS D', 'A.nobukti', '=', 'D.nobukti');

        DB::table($TempRekapHutang)->insertUsing([
            'nobukti',
            'nobuktiretur',
            'nominal',
            'bayar',
            'bayarretur',
            'supplier_id',
        ], $select_TempRekapHutang2);

        $querybayarretur = DB::table($Temphutangberjalan . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw("isnull(c.pengeluaranstok_nobukti,'') as nobuktiretur"),
                'A.nominal',
                DB::raw("0 as bayar"),
                DB::raw('ISNULL(C.nominal, 0) as bayarretur'),
                DB::raw("d.supplier_id"),
            ])
            ->Join($Temphutangbyrretur . ' AS C', 'A.nobukti', '=', 'C.hutang_nobukti')
            ->join('hutangheader AS D', 'A.nobukti', '=', 'D.nobukti');

        DB::table($TempRekapHutang)->insertUsing([
            'nobukti',
            'nobuktiretur',
            'nominal',
            'bayar',
            'bayarretur',
            'supplier_id',
        ], $querybayarretur);

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
                DB::raw("(case when isnull(a.nobuktiretur,'')='' then a.nobukti else a.nobuktiretur end) as nobukti"),
                'C.tglbukti',
                'E.tgljatuhtempo',
                DB::raw('ISNULL(B.urut, 0) + 1 as cicil'),
                DB::raw("(case when isnull(a.bayarretur,0)=0 then 
                (isnull(A.nominal,0)-isnull(a.bayar,0)) else  0 end)
                as nominal"),
                'a.bayarretur as bayar',
                // DB::raw('SUM((case when isnull(a.bayarretur,0)=0 then 
                // (isnull(A.nominal,0)-isnull(a.bayar,0)) else  0 end)) OVER (PARTITION BY c.supplier_id ORDER BY A.id ASC) as Saldo'),
                DB::raw('SUM((
                    (case when isnull(a.bayarretur,0)=0 then 
                (isnull(A.nominal,0)-isnull(a.bayar,0)) else  0 end)
                    - A.bayarretur)) OVER (PARTITION BY c.supplier_id ORDER BY A.id ASC) as Saldo'),

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
            ->whereRaw("(isnull(A.nominal,0)-isnull(a.bayar,0))<>0")
            ->orderBy('c.supplier_id')
            ->orderBy('c.tglbukti')
            ->orderBy('c.nobukti');


        // dd($select_data->get());
        if ($prosesneraca == 1) {
            $data = $select_data;
        } else {
            $data = $select_data->get();
        }

        return $data;
    }
}
