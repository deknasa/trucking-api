<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKartuPanjar extends MyModel
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
    public function getSisapanjarbukti($dari, $sampai, $agenDari, $agenSampai, $prosesneraca,$agen_id,$tgl,$nobukti) {
        $temppanjar = '##temppanjar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppanjar, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal')->nullable();                
        });

        DB::table($temppanjar)->insertUsing([
            'nobukti',
            'nominal'
        ], (new LaporanKartuPanjar())->getSisapanjar($tgl, $tgl, 0, 0, 1,$agen_id,$tgl));

        db::table($temppanjar)->whereRaw("nobukti not in('". $nobukti ."')")->delete();

        $data=db::table($temppanjar)->from(db::raw($temppanjar . " a "))
        ->select(
            'a.nobukti',
            'a.nominal'
        )
        ->orderby('a.nobukti','asc');

        return $data;
    }


    public function getSisapanjar($dari, $sampai, $agenDari, $agenSampai, $prosesneraca,$agen_id,$tgl) {
        $tempkartupanjar = '##tempkartupanjar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkartupanjar, function ($table) {
            $table->integer('id')->nullable();
            $table->string('agen',1000)->nullable();
            $table->integer('agen_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->double('nominalpiutang')->nullable();
            $table->dateTime('tgllunas')->nullable();
            $table->double('nominallunas')->nullable();
            $table->string('nobuktipiutang', 50)->nullable();
            $table->dateTime('tglberjalan')->nullable();
            $table->double('saldo')->nullable();
            $table->double('saldobayar')->nullable();
            $table->string('jenispiutang', 50)->nullable();
            $table->integer('urut')->nullable();
            $table->string('text', 500)->nullable();
            $table->string('dari', 500)->nullable();
            $table->string('sampai', 500)->nullable();
            $table->string('judullaporan', 500)->nullable();
            $table->string('judul', 500)->nullable();
            $table->string('tglcetak', 500)->nullable();
            $table->string('usercetak', 500)->nullable();
            $table->string('disetujui', 500)->nullable();
            $table->string('diperiksa', 500)->nullable();
        });


        DB::table($tempkartupanjar)->insertUsing([
            'id',
            'agen',
            'agen_id',
            'nobukti',
            'tglbukti',
            'nominalpiutang',
            'tgllunas',
            'nominallunas',
            'nobuktipiutang',
            'tglberjalan',
            'saldo',
            'saldobayar',
            'jenispiutang',
            'urut',
            'text',
            'dari',
            'sampai',
            'judullaporan',
            'judul',
            'tglcetak',
            'usercetak',
            'disetujui',
            'diperiksa',
        ], (new LaporanKartuPanjar())->getReport($tgl, $tgl, 0, 0, 1));

        $tempdatapanjar = '##tempdatapanjar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatapanjar, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('agen_id',1000)->nullable();
            $table->double('nominal')->nullable();                
        });

        $querydatapanjar=db::table($tempkartupanjar)->from(db::raw($tempkartupanjar . " a "))
        ->select(
            'a.nobuktipiutang as nobukti',
            db::raw("max(a.agen_id) as agen_id"),
            db::raw("max(a.nominalpiutang) as nominal")
        )
        ->groupBY('a.nobuktipiutang');

        DB::table($tempdatapanjar)->insertUsing([
            'nobukti',
            'agen_id',
            'nominal',
        ], $querydatapanjar);            



        $tempdatalunas = '##tempdatalunas' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatalunas, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('agen_id',1000)->nullable();
            $table->double('nominal')->nullable();                
        });

        $querydatalunas=db::table($tempkartupanjar)->from(db::raw($tempkartupanjar . " a "))
        ->select(
            'a.nobuktipiutang as nobukti',
            db::raw("max(a.agen_id) as agen_id"),
            db::raw("sum(a.nominallunas) as nominal")
        )
        ->groupBY('a.nobuktipiutang');

        DB::table($tempdatalunas)->insertUsing([
            'nobukti',
            'agen_id',
            'nominal',
        ], $querydatalunas); 


        $temppanjar = '##temppanjar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppanjar, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal')->nullable();                
        });

        $querypanjar=db::table($tempdatapanjar)->from(db::raw($tempdatapanjar . " a "))
        ->select(
            'a.nobukti',
            db::raw("(a.nominal-isnull(lunas.nominal,0)) as nominal")
        )
        ->leftJoin(DB::raw($tempdatalunas . " lunas"), 'a.nobukti', 'lunas.nobukti')
        ->whereRaw("isnull(a.agen_id,0)=" . $agen_id );

        DB::table($temppanjar)->insertUsing([
            'nobukti',
            'nominal',
        ], $querypanjar);

        $querypanjar=db::table($tempdatapanjar)->from(db::raw($tempdatapanjar . " a "))
        ->select(
            'a.nobukti',
            db::raw("(a.nominal-isnull(lunas.nominal,0)) as nominal")
        )
        ->leftJoin(DB::raw($tempdatalunas . " lunas"), 'a.nobukti', 'lunas.nobukti')
        ->whereRaw("isnull(a.agen_id,0)=0" );

        DB::table($temppanjar)->insertUsing([
            'nobukti',
            'nominal',
        ], $querypanjar);

        $data=db::table($temppanjar)->from(db::raw($temppanjar . " a "))
        ->select(
            'a.nobukti',
            'a.nominal'
        )
        ->orderby('a.nobukti','asc');

        return $data;

    }
    

    public function getReport($dari, $sampai, $agenDari, $agenSampai, $prosesneraca)
    {
        $prosesneraca = $prosesneraca ?? 0;

        $sampai = $dari;
        $tgl = '01-' . date('m', strtotime($dari)) . '-' . date('Y', strtotime($dari));
        $dari1 = date('Y-m-d', strtotime($tgl));
        $keteranganagen='';
    if ($agenDari==0 || $agenSampai==0 )  {
        $keteranganagen='SEMUA';
    }

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

        $agendarinama=db::table('agen')->from(db::raw("agen with (readuncommitted)"))
        ->select('namaagen')->orderby('id', 'asc')->where('id',$agenDari)->first()->namaagen ?? '';

        $agensampainama=db::table('agen')->from(db::raw("agen with (readuncommitted)"))
        ->select('namaagen')->orderby('id', 'asc')->where('id',$agenSampai)->first()->namaagen ?? '';


        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

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

        $temppiutangsaldo = '##temppiutangsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppiutangsaldo, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        // dd($sampai);
        $coauangditerimadimuka='03.02.02.08';
        $querypiutangsaldo = db::table('notadebetheader')->from(db::raw("notadebetheader a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                db::raw("sum(b.lebihbayar) as nominal"),
            )
            ->join(db::raw("notadebetdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti<='" . $sampai . "'")
            ->whereRaw("b.coalebihbayar='" . $coauangditerimadimuka . "'")
            ->groupby('a.nobukti');
            
        DB::table($temppiutangsaldo)->insertUsing([
            'nobukti',
            'nominal',
        ], $querypiutangsaldo);

        // dd( $querypiutangsaldo ->get());
        // dd(db::table($temppiutangsaldo)->get());
     
        $querypelunasansaldo = db::table('notadebetfifo')->from(db::raw("notadebetfifo a with (readuncommitted)"))
            ->select(
                'c.nobukti',
                db::raw("sum(isnull(a.nominal,0)) as nominal"),
            )
            ->join(db::raw("pelunasanpiutangheader b with (readuncommitted)"), 'a.pelunasanpiutang_nobukti', 'b.nobukti')
            ->join(db::raw($temppiutangsaldo . " c "), 'b.notadebet_nobukti', 'c.nobukti')
            ->whereRaw("b.tglbukti<'" . $dari1 . "'")
            ->groupby('c.nobukti');

        DB::table($temppelunasansaldo)->insertUsing([
            'nobukti',
            'nominal',
        ], $querypelunasansaldo);

        // dd(db::table($temppelunasansaldo) ->get());
   

        $temprekappiutang = '##temprekappiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekappiutang, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

     

        $queryrekappiutang = db::table($temppiutangsaldo)->from(db::raw($temppiutangsaldo . " a "))
            ->select(
                'a.nobukti',
                db::raw("(isnull(a.nominal,0)-isnull(b.nominal,0)) as nominal"),
            )
            ->leftjoin(db::raw($temppelunasansaldo . " b "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(isnull(a.nominal,0)-isnull(b.nominal,0))<>0");

        DB::table($temprekappiutang)->insertUsing([
            'nobukti',
            'nominal',
        ], $queryrekappiutang);

        //    dd(db::table($temprekappiutang) ->get());

     
        // $queryrekappiutang = db::table("notadebetfifo")->from(db::raw("notadebetfifo a with (readuncommitted) "))
        //     ->select(
        //         'a.notadebet_nobukti as nobukti',
        //         db::raw("sum(isnull(a.nominal,0)) as nominal"),
        //     )
        //     ->leftjoin(db::raw("pelunasanpiutangheader b with (readuncommitted) "), 'a.pelunasanpiutang_nobukti', 'b.nobukti')
        //     ->whereRaw("(b.tglbukti>='" . $dari1 . "' and b.tglbukti<='" . $sampai . "')")
        //     ->groupby('a.notadebet_nobukti');

        // DB::table($temprekappiutang)->insertUsing([
        //     'nobukti',
        //     'nominal',
        // ], $queryrekappiutang);

                //    dd(db::table($temprekappiutang) ->get());


        // dd('test');
        $temprekapdata = '##temprekapdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapdata, function ($table) {
            $table->id();
            $table->integer('agen_id')->nullable();
            $table->string('nobukti', 50);
            $table->dateTime('tglbukti');
            $table->double('nominalpiutang')->nullable();
            $table->dateTime('tgllunas');
            $table->double('nominallunas')->nullable();
            $table->string('nobuktipiutang', 50);
            $table->dateTime('tglberjalan');
            $table->string('jenispiutang', 50);
            $table->integer('urut')->nullable();
        });


        // dd(db::table($temprekappiutang)->get());

        $queryrekapdata = db::table($temprekappiutang)->from(db::raw($temprekappiutang . " a  "))
            ->select(
                'b.agen_id',
                'a.nobukti',
                'b.tglbukti',
                'a.nominal',
                db::raw("'1900/1/1' as tgllunas"),
                db::raw("0 as nominallunas"),
                'a.nobukti as nobuktipiutang',
                'b.tglbukti as tglberjalan',
                db::raw(" 'PIUTANG USAHA' as jenispiutang"),
                db::raw("0 as urut"),
                )
            ->join(db::raw("notadebetheader b with (readuncommitted) "), 'a.nobukti', 'b.nobukti');

        DB::table($temprekapdata)->insertUsing([
            'agen_id',
            'nobukti',
            'tglbukti',
            'nominalpiutang',
            'tgllunas',
            'nominallunas',
            'nobuktipiutang',
            'tglberjalan',
            'jenispiutang',
            'urut',
        ], $queryrekapdata);


        $queryrekapdata = db::table($temprekappiutang)->from(db::raw($temprekappiutang . " a  "))
            ->select(
                'b.agen_id',
                'd.nobukti',
                db::raw("'1900/1/1' as tglbukti"),
                db::raw("0 as nominal"),
                "d.tglbukti as tgllunas",
                db::raw("(isnull(c.nominal,0)) as nominallunas"),
                'a.nobukti as nobuktipiutang',
                'b.tglbukti as tglberjalan',
                db::raw(" 'PIUTANG USAHA' as jenispiutang"),
                db::raw("1 as urut"),                
            )
            ->join(db::raw("notadebetheader b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("notadebetfifo c with (readuncommitted) "), 'a.nobukti', 'c.notadebet_nobukti')
            ->join(db::raw("pelunasanpiutangheader d with (readuncommitted) "), 'c.pelunasanpiutang_nobukti', 'd.nobukti')
            ->whereRaw("(d.tglbukti>='" . $dari1 . "' and d.tglbukti<='" . $sampai . "')")
            ->whereRaw("isnull(c.nominal,0)<>0");

            DB::table($temprekapdata)->insertUsing([
                'agen_id',
                'nobukti',
                'tglbukti',
                'nominalpiutang',
                'tgllunas',
                'nominallunas',
                'nobuktipiutang',
                'tglberjalan',
                'jenispiutang',
                'urut',
            ], $queryrekapdata);
    

                   

        $temprekaphasil = '##temprekaphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekaphasil, function ($table) {
            $table->id();
            $table->integer('agen_id')->nullable();
            $table->string('agen',1000)->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->double('nominalpiutang')->nullable();
            $table->dateTime('tgllunas')->nullable();
            $table->double('nominallunas')->nullable();
            $table->string('nobuktipiutang', 50)->nullable();
            $table->dateTime('tglberjalan')->nullable();
            $table->double('saldo')->nullable();
            $table->double('saldobayar')->nullable();
            $table->string('jenispiutang', 50)->nullable();
            $table->integer('urut')->nullable();
        });

        DB::table($temprekapdata)
        ->from(db::raw($temprekapdata)) 
        ->join(db::raw("notadebetfifo b with (readuncommitted)"),db::raw($temprekapdata.".nobuktipiutang"),'b.notadebet_nobukti')
        ->join(db::raw("pelunasanpiutangheader c with (readuncommitted)"),'b.pelunasanpiutang_nobukti','c.nobukti')
        ->update([
                'agen_id' => db::raw("c.agen_id"),
            ]);

        // DB::table($temprekapdata)
        // ->from(db::raw($temprekapdata)) 
        // ->join(db::raw("agen b with (readuncommitted)"),db::raw($temprekapdata.".agen_id"),'b.id')
        // ->update([
        //         'agen' => db::raw("b.namaagen"),
        //     ]);

        $queryrekaphasil = db::table($temprekapdata)->from(db::raw($temprekapdata . " a  "))
            ->select(
                'b.namaagen as agen',
                'b.id as agen_id',
                'a.nobukti',
                db::raw("(case when year(a.tglbukti)=1900 then null else a.tglbukti end ) as tglbukti"),
                'a.nominalpiutang',
                db::raw("(case when year(a.tgllunas)=1900 then null else a.tgllunas end ) as tgllunas"),
                'a.nominallunas',
                'a.nobuktipiutang',
                'a.tglberjalan',
                db::raw("SUM(a.nominalpiutang-a.nominallunas) OVER (PARTITION BY a.jenispiutang,b.namaagen ORDER BY a.tglberjalan,a.nobuktipiutang,a.urut ASC) as saldo"),
                db::raw("a.nominalpiutang-a.nominallunas  as saldobayar"),
                'a.jenispiutang',
                'a.urut'
            )
            ->leftjoin(db::raw("agen b with (readuncommitted) "), 'a.agen_id', 'b.id')
     
            ->orderby('b.namaagen', 'asc')
            ->orderby('a.jenispiutang', 'asc')
            ->orderby('a.tglberjalan', 'asc')
            ->orderby('a.nobuktipiutang', 'asc')
            ->orderby('a.urut', 'asc');


        DB::table($temprekaphasil)->insertUsing([
            'agen',
            'agen_id',
            'nobukti',
            'tglbukti',
            'nominalpiutang',
            'tgllunas',
            'nominallunas',
            'nobuktipiutang',
            'tglberjalan',
            'saldo',
            'saldobayar',
            'jenispiutang',
            'urut',
        ], $queryrekaphasil);

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

            // dd('test');



$queryurut=db::table($temprekaphasil)->from(db::raw($temprekaphasil . " a"))
->select(
    'a.id',
    'a.agen_id',
    'a.jenispiutang',
    'a.nobukti',
    'a.nobuktipiutang',
    )
    ->orderby('a.id')
    ->get();
    $datadetail = json_decode($queryurut, true);
    $xuji='';
    $xnobukti='';
    $xnobuktipiutang='';

    DB::update(DB::raw("UPDATE " . $temprekaphasil . " SET urut=0"));
    $urut=0;
    foreach ($datadetail as $item) {
        $xuji2=$item['agen_id'].$item['jenispiutang'];

        if ($xuji2!=$xuji) {
            $urut=0;
        }
            if ($item['nobukti']==$item['nobuktipiutang']) {
                $urut=$urut+1;
                DB::update(DB::raw("UPDATE " . $temprekaphasil . " SET urut=".$urut." where id=". $item['id']));
            }
            
        
        $xuji=$item['agen_id'].$item['jenispiutang'];
  
    
    }

     

        $select_data = DB::table($temprekaphasil . ' AS A')
            ->select([
                'a.id',
                'a.agen',
                'a.agen_id',
                'a.nobukti',
                db::raw("cast(a.tglbukti as date) as tglbukti"),
                'a.nominalpiutang',
                db::raw("cast(a.tgllunas as date) as tgllunas"),
                'a.nominallunas',
                'a.nobuktipiutang',
                db::raw("cast(a.tglberjalan as date) as tglberjalan"),
                'a.saldo',
                'a.saldobayar',
                'a.jenispiutang',
                'a.urut',
                DB::raw("'$getJudul->text' AS text"),
                DB::raw("(case when '$keteranganagen'='' then '$agendarinama' else '$keteranganagen' end)  AS dari"),
                DB::raw("(case when '$keteranganagen'='' then '$agensampainama' else '$keteranganagen' end)   AS sampai"),
                DB::raw("'Laporan Kartu Piutang Per Customer' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            ])

            ->orderBy('a.id', 'asc');

            // dd($select_data->get());

        if ($prosesneraca == 1) {
            $data = $select_data;
        } else {
            $data = $select_data->get();
        }

        return $data;

    }


     
   
 
    
}
