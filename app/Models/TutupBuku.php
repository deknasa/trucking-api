<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TutupBuku extends Model
{
    use HasFactory;

    public function processStore(array $data)
    {
        $tgltutupbuku = date('Y-m-d', strtotime($data['tgltutupbuku']));
        $this->saldoawalbank($tgltutupbuku);
        $this->saldoawalbukubesar($tgltutupbuku);
        $parameter = Parameter::where('grp', 'TUTUP BUKU')->where('subgrp', 'TUTUP BUKU')->first();

        $parameter->text = $tgltutupbuku;
        $parameter->modifiedby = auth('api')->user()->name;
        $parameter->info = html_entity_decode(request()->info);

        // proses saldo awal buku besar

 // cek saldo awal
 $tglsaldo = '2023-10-01';
 $awalsaldo = date('Y-m-d', strtotime($tglsaldo));

 $tutupbuku = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
     ->select(
         'a.text'
     )
     ->where('grp', 'TUTUP BUKU')
     ->where('subgrp', 'TUTUP BUKU')
     ->first()->text ?? '1900-01-01';

 $awaldari = date('Y-m-', strtotime( $tgltutupbuku)) . '01';
//  $awalcek = date('Y-m-d', strtotime($tutupbuku . ' +1 day'));
 $awalcek =  $awaldari;
 $akhircek = date('Y-m-d', strtotime($awaldari . ' -1 day'));

 $getcabangid = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
 ->select(
     'a.text'
 )
 ->where('a.grp', 'ID CABANG')
 ->where('a.subgrp', 'ID CABANG')
 ->first()->text ?? 0;


 if ($awalcek <= $awalsaldo) {
     $awalcek = $awalsaldo;
 }

 $tglawalcek = $awalcek;
 $tglakhircek = $akhircek;
 $bulan1 = date('m-Y', strtotime($awalcek));
 $bulan2 = date('m-Y', strtotime('1900-01-01'));


 $tempsaldobukubesar = '##tempsaldobukubesar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
 Schema::create($tempsaldobukubesar, function ($table) {
     $table->string('bulan', 1000)->nullable();
     $table->string('coa', 100)->nullable();
     $table->double('nominal', 15, 2)->nullable();
 });

 $querydetailsaldo = DB::table("jurnalumumpusatheader")->from(
     DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
 )
     ->select(
         db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
         'b.coa',
         DB::raw("sum(b.nominal) as nominal"),
     )
     ->join(DB::raw("jurnalumumpusatdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
     ->join(DB::raw("akunpusat as c with(readuncommitted)"), 'b.coa', 'c.coa')
     ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tglakhircek . "'")
     // ->whereRaw("b.coa='01.01.01.03'")
     ->groupby('b.coa')
     ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));

 // dd($querydetailsaldo->get());

 DB::table($tempsaldobukubesar)->insertUsing([
     'bulan',
     'coa',
     'nominal',
 ], $querydetailsaldo);

 $queryrekap = db::table($tempsaldobukubesar)->from(db::raw($tempsaldobukubesar . " a"))
     ->select(
         'bulan',
         'coa',
         db::raw("sum(nominal) as nominal"),
         db::raw("'ADMIN' as modifiedby"),
         db::raw("'' as info"),
         db::raw("getdate() as created_at"),
         db::raw("getdate() as updated_at"),
         db::raw($getcabangid . " as cabang_id"),
         db::raw("'" . $tglawalcek . "' as tglbukti"),
     )
     ->groupby('a.bulan')
     ->groupby('a.coa');

 $tempsaldobukubesarrekap = '##tempsaldobukubesarrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
 Schema::create($tempsaldobukubesarrekap, function ($table) {
     $table->string('bulan', 1000)->nullable();
     $table->string('coa', 100)->nullable();
     $table->double('nominal', 15, 2)->nullable();
     $table->string('modifiedby', 100)->nullable();
     $table->longtext('info')->nullable();
     $table->datetime('created_at')->nullable();
     $table->datetime('updated_at')->nullable();
     $table->integer('cabang_id')->nullable();
     $table->date('tglbukti')->nullable();
 });

 DB::table($tempsaldobukubesarrekap)->insertUsing([
     'bulan',
     'coa',
     'nominal',
     'info',
     'modifiedby',
     'created_at',
     'updated_at',
     'cabang_id',
     'tglbukti',
 ], $queryrekap);

 DB::delete(DB::raw("delete saldoawalbukubesar from saldoawalbukubesar as a 
                     inner join " . $tempsaldobukubesarrekap . " b on a.bulan=b.bulan and a.coa=b.coa"));


 DB::table("saldoawalbukubesar")->insertUsing([
     'bulan',
     'coa',
     'nominal',
     'info',
     'modifiedby',
     'created_at',
     'updated_at',
     'cabang_id',
     'tglbukti',
 ], $queryrekap);


 // akhir rekap saldo
// rekap saldo kas bank
$tempsaldoawal = '##tempsaldoawal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawal, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
        });



        // penerimaan
        $querydebet = DB::table("penerimaanheader")->from(
            DB::raw("penerimaanheader as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("a.bank_id"),
                DB::raw("sum(b.nominal) as nominaldebet"),
                DB::raw("0 as nominalkredit")
            )
            ->join(DB::raw("penerimaandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tglakhircek . "'")
            ->groupby('a.bank_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));



        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querydebet);

        $querydebet = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("a.bankke_id as bank_id"),
                DB::raw("sum(a.nominal) as nominaldebet"),
                DB::raw("0 as nominalkredit")
            )
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tglakhircek . "'")
            ->groupby('a.bankke_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));


        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querydebet);

        // pengeluaran

        $querykredit = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("a.bank_id"),
                DB::raw("0 as nominaldebet"),
                DB::raw("sum(b.nominal) as nominalkredit")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tglakhircek . "'")
            ->groupby('a.bank_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));


        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querykredit);

        $querykredit = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("a.bankdari_id as bank_id"),
                DB::raw("0 as nominaldebet"),
                DB::raw("sum(a.nominal) as nominalkredit")
            )
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tglakhircek . "'")
            ->groupby('a.bankdari_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));


        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querykredit);

        $temppengembaliankepusat = '##temppengembaliankepusat' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengembaliankepusat, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('bankpengembalian_id')->nullable();
        });

        $tempnonpengembaliankepusat = '##tempnonpengembaliankepusat' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempnonpengembaliankepusat, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('coa', 50)->nullable();
        });

        $querynonpengembalian = db::table("bank")->from(db::raw("bank a with (readuncommitted)"))
            ->select(
                'a.id as bank_id',
                'a.coa as coa',
            )
            ->whereraw("left(a.kodebank,12)<>'PENGEMBALIAN'");

        DB::table($tempnonpengembaliankepusat)->insertUsing([
            'bank_id',
            'coa',
        ], $querynonpengembalian);

        $querypengembalian = db::table("bank")->from(db::raw("bank a with (readuncommitted)"))
            ->select(
                'b.bank_id',
                'a.id as bankpengembalian_id',
            )
            ->join(db::raw($tempnonpengembaliankepusat . " b"), 'a.coa', 'b.coa')
            ->whereraw("left(a.kodebank,12)='PENGEMBALIAN'");

        DB::table($temppengembaliankepusat)->insertUsing([
            'bank_id',
            'bankpengembalian_id',
        ], $querypengembalian);

        // dd('test');
        $querykredit = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("c.bank_id"),
                DB::raw("0 as nominaldebet"),
                DB::raw("sum(b.nominal) as nominalkredit")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw($temppengembaliankepusat . " as c with (readuncommitted)"), 'a.bank_id', 'c.bankpengembalian_id')
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tglakhircek . "'")
            ->groupby('c.bank_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));



        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querykredit);

        DB::delete(DB::raw("delete " . $tempsaldoawal . " from " . $tempsaldoawal . " a 
                    inner join " . $temppengembaliankepusat . " b on a.bank_id=b.bankpengembalian_id"));



        // dd(db::table($tempsaldoawal)->where('bank_id',2)->get());

        // 

        $queryrekap = db::table($tempsaldoawal)->from(db::raw($tempsaldoawal . " a"))
            ->select(
                'bulan',
                'bank_id',
                db::raw("sum(nominaldebet) as nominaldebet"),
                db::raw("sum(nominalkredit) as nominalkredit"),
                db::raw("'' as info"),
                db::raw("getdate() as created_at"),
                db::raw("getdate() as updated_at"),
            )
            ->groupby('a.bulan')
            ->groupby('a.bank_id');

        $tempsaldoawalrekap = '##tempsaldoawalrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawalrekap, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
            $table->longtext('info')->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
        });

        DB::table($tempsaldoawalrekap)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
            'info',
            'created_at',
            'updated_at',
        ], $queryrekap);


        DB::delete(DB::raw("delete saldoawalbank from saldoawalbank as a 
            inner join " . $tempsaldoawalrekap . " b on a.bulan=b.bulan and a.bank_id=b.bank_id"));

        DB::table("saldoawalbank")->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
            'info',
            'created_at',
            'updated_at',
        ], $queryrekap);
//  

        // 
        if (!$parameter->save()) {
            throw new \Exception("Error update tutup buku.");
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($parameter->getTable()),
            'postingdari' => 'TUTUP BUKU',
            'idtrans' => $parameter->id,
            'nobuktitrans' => $parameter->id,
            'aksi' => 'EDIT',
            'datajson' => $parameter->toArray(),
            'modifiedby' => $parameter->modifiedby
        ]);

        return $parameter;
    }
    public function saldoawalbank($tgl1)
    {
        $bulan = date('m', strtotime($tgl1));
        $tahun = date('Y', strtotime($tgl1));
        $tgl = '01-' . $bulan . '-' . $tahun;
        $tgldari = date('Y-m-d', strtotime($tgl));
        $tgl2 = date('t-m-Y', strtotime($tgl));
        $tglsampai = date('Y-m-d', strtotime($tgl2));

        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
        });

        $queryrekap = db::table('penerimaanheader')->from(db::raw("
        penerimaanheader a with(readuncommitted)
        "))
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                'a.bank_id',
                db::raw("sum(b.nominal) as nominaldebet"),
                db::raw("0 as nominalkredit"),

            )
            ->join(db::raw("penerimaandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(a.tglbukti>='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
            ->groupBy(db::raw("format(a.tglbukti,'MM-yyyy')"))
            ->groupBy('a.bank_id');

        DB::table($temprekap)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ],  $queryrekap);

        $queryrekap = db::table('pengeluaranheader')->from(db::raw("
        pengeluaranheader a with(readuncommitted)
        "))
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                'a.bank_id',
                db::raw("0 as nominaldebet"),
                db::raw("sum(b.nominal) as nominalkredit"),

            )
            ->join(db::raw("pengeluarandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(a.tglbukti>='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
            ->groupBy(db::raw("format(a.tglbukti,'MM-yyyy')"))
            ->groupBy('a.bank_id');

        DB::table($temprekap)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ],  $queryrekap);

        $temprekapall = '##temprekapall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapall, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $queryrekapall = db::table($temprekap)->from(db::raw($temprekap . " a "))
            ->select(
                'a.bulan',
                'a.bank_id',
                db::raw("sum(a.nominaldebet) as nominaldebet"),
                db::raw("sum(a.nominalkredit) as nominalkredit"),
                db::raw("getdate() as created_at"),
                db::raw("getdate() as updated_at"),

            )
            ->groupBy('a.bulan')
            ->groupBy('a.bank_id');

        DB::table($temprekapall)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
            'created_at',
            'updated_at'
        ],  $queryrekapall);

        DB::delete(DB::raw("delete saldoawalbank from  saldoawalbank as a inner join " . $temprekapall . " b on a.bulan=b.bulan and a.bank_id=B.bank_id"));

        DB::table('saldoawalbank')->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
            'created_at',
            'updated_at'
        ],  $queryrekapall);
    }

    public function saldoawalbukubesar($tgl1)
    {
        $bulan = date('m', strtotime($tgl1));
        $tahun = date('Y', strtotime($tgl1));
        $tgl = '01-' . $bulan . '-' . $tahun;
        $tgldari = date('Y-m-d', strtotime($tgl));
        $tgl2 = date('t-m-Y', strtotime($tgl));
        $tglsampai = date('Y-m-d', strtotime($tgl2));

        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $queryrekap = db::table('jurnalumumpusatheader')->from(db::raw("
        jurnalumumpusatheader a with(readuncommitted)
        "))
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                'b.coa',
                db::raw("sum(b.nominal) as nominal"),

            )
            ->join(db::raw("jurnalumumpusatdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(a.tglbukti>='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
            ->groupBy(db::raw("format(a.tglbukti,'MM-yyyy')"))
            ->groupBy('b.coa');

        DB::table($temprekap)->insertUsing([
            'bulan',
            'coa',
            'nominal',
        ],  $queryrekap);



        $temprekapall = '##temprekapall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapall, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $queryrekapall = db::table($temprekap)->from(db::raw($temprekap . " a "))
            ->select(
                'a.bulan',
                'a.coa',
                db::raw("sum(a.nominal) as nominal"),
                db::raw("'" . auth('api')->user()->name . "'  as modifiedby"),
                db::raw("getdate() as created_at"),
                db::raw("getdate() as updated_at"),

            )
            ->groupBy('a.bulan')
            ->groupBy('a.coa');

        DB::table($temprekapall)->insertUsing([
            'bulan',
            'coa',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at'
        ],  $queryrekapall);

        DB::delete(DB::raw("delete saldoawalbukubesar from  saldoawalbukubesar as a inner join " . $temprekapall . " b on a.bulan=b.bulan and a.coa=B.coa"));

        DB::table('saldoawalbukubesar')->insertUsing([
            'bulan',
            'coa',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at'
        ],  $queryrekapall);
    }
}
