<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaldoUmurAki extends MyModel
{

    use HasFactory;
    protected $table = 'saldosuratpengantar';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get($id)
    {

        // filter stok yang pja
        // dd('test');
        $tempstokpja = '##tempstokpja' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstokpja, function ($table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->string('nobukti', 50)->nullable();
        });

        $pengeluaranstok_pja = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text'
            )
            ->where('a.grp', 'PENJUALAN STOK AFKIR')
            ->where('a.subgrp', 'PENJUALAN STOK AFKIR')
            ->first()->text ?? 0;



        $querypja = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with(readuncommitted)"))
            ->select(
                'b.stok_id',
                'a.nobukti',

            )
            ->join(db::raw("pengeluaranstokdetail b with(readuncommitted)"), "a.nobukti", "b.nobukti")
            ->where('b.stok_id', $id)
            ->where("a.pengeluaranstok_id", $pengeluaranstok_pja);

        DB::table($tempstokpja)->insertUsing([
            'stok_id',
            'nobukti',
        ], $querypja);


        $tempSaldoAki = '##tempSaldoAki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempSaldoAki, function ($table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->Integer('trado_id')->nullable();
            $table->date('tglawal')->nullable();
            $table->integer('jumlahharitrip')->nullable();
            $table->bigInteger('idsaldo')->nullable();
        });

        $querysaldo = db::table('saldoumuraki')->from(db::raw("saldoumuraki a with (readuncommitted)"))
            ->select(
                'a.stok_id',
                'a.trado_id',
                'a.tglawal',
                'a.jumlahharitrip',
                'a.id',
            )
            ->leftjoin(db::raw($tempstokpja . " b"), "a.stok_id", "b.stok_id")
            ->where('a.stok_id', $id)
            ->whereraw("isnull(b.stok_id,0)=0");


        DB::table($tempSaldoAki)->insertUsing([
            'stok_id',
            'trado_id',
            'tglawal',
            'jumlahharitrip',
            'idsaldo',
        ], $querysaldo);


        $tempumurakiberjalan = '##tempumurakiberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumurakiberjalan, function ($table) {
            $table->id();
            $table->Integer('trado_id')->nullable();
            $table->date('tglbukti')->nullable();
        });

        $querytrip = db::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                'a.tglbukti',
            )
            ->join(db::raw($tempSaldoAki . " b"), "a.trado_id", "b.trado_id")
            ->groupBy('a.trado_id')
            ->groupBy('a.tglbukti');

        DB::table($tempumurakiberjalan)->insertUsing([
            'trado_id',
            'tglbukti',
        ], $querytrip);


        $tempumurakiberjalanrekap = '##tempumurakiberjalanrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumurakiberjalanrekap, function ($table) {
            $table->id();
            $table->Integer('trado_id')->nullable();
            $table->integer('jumlahhari')->nullable();
        });

        $queryjumlah = db::table($tempumurakiberjalan)->from(db::raw($tempumurakiberjalan . " a "))
            ->select(
                'a.trado_id',
                db::raw("count(a.tglbukti) as jumlah"),
            )
            ->groupBy('a.trado_id');
        DB::table($tempumurakiberjalanrekap)->insertUsing([
            'trado_id',
            'jumlahhari',
        ], $queryjumlah);

        $hariaki = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text as id'
            )
            ->where('a.grp', 'HARIAKI')
            ->where('a.subgrp', 'HARIAKI')
            ->where('a.text', 'TANGGAL')
            ->first();


        if (isset($hariaki)) {
            $queryaki = db::table($tempSaldoAki)->from(db::raw($tempSaldoAki . " a"))
                ->select(
                    'a.tglawal'
                )
                ->where('a.stok_id', $id)
                ->first();
            if (isset($queryaki)) {
                $awal  = date_create(date('Y-m-d', strtotime($queryaki->tglawal)));
                $akhir = date_create();
                $umuraki  = date_diff($awal, $akhir)->days;
            } else {
                $umuraki = 0;
            }
        } else {
            $umursaldo = db::table($tempSaldoAki)->from(db::raw($tempSaldoAki . " a "))
                ->select(
                    db::raw("sum(a.jumlahharitrip) as jumlahhari")
                )
                ->first()->jumlahhari ?? 0;

            $umur = db::table($tempumurakiberjalanrekap)->from(db::raw($tempumurakiberjalanrekap . " a "))
                ->select(
                    db::raw("sum(a.jumlahhari) as jumlahhari")
                )
                ->first()->jumlahhari ?? 0;

            $umuraki = $umursaldo + $umur;
        }

        return $umuraki;
    }


    public function getallstok()
    {

        // filter stok yang pja
        // dd('test');
        $tempstokpja = '##tempstokpja' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstokpja, function ($table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->string('nobukti', 50)->nullable();
        });

        $pengeluaranstok_pja = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text'
            )
            ->where('a.grp', 'PENJUALAN STOK AFKIR')
            ->where('a.subgrp', 'PENJUALAN STOK AFKIR')
            ->first()->text ?? 0;



        $querypja = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with(readuncommitted)"))
            ->select(
                'b.stok_id',
                'a.nobukti',

            )
            ->join(db::raw("pengeluaranstokdetail b with(readuncommitted)"), "a.nobukti", "b.nobukti")
            ->join(db::raw("stok c with(readuncommitted)"), "b.stok_id", "c.id")
            ->where("a.pengeluaranstok_id", $pengeluaranstok_pja)
            ->whereRaw("isnull(C.kelompok_id,0)=3");

        DB::table($tempstokpja)->insertUsing([
            'stok_id',
            'nobukti',
        ], $querypja);


        $tempSaldoAki = '##tempSaldoAki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempSaldoAki, function ($table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->Integer('trado_id')->nullable();
            $table->date('tglawal')->nullable();
            $table->integer('jumlahharitrip')->nullable();
            $table->bigInteger('idsaldo')->nullable();
        });

        $querysaldo = db::table('saldoumuraki')->from(db::raw("saldoumuraki a with (readuncommitted)"))
            ->select(
                'a.stok_id',
                'a.trado_id',
                'a.tglawal',
                'a.jumlahharitrip',
                'a.id',
            )
            ->leftjoin(db::raw($tempstokpja . " b"), "a.stok_id", "b.stok_id")
            ->join(db::raw("stok c with(readuncommitted)"), "a.stok_id", "c.id")
            ->whereRaw("isnull(c.kelompok_id,0)=3")
            ->whereraw("isnull(b.stok_id,0)=0");


        DB::table($tempSaldoAki)->insertUsing([
            'stok_id',
            'trado_id',
            'tglawal',
            'jumlahharitrip',
            'idsaldo',
        ], $querysaldo);


        $tempumurakiberjalan = '##tempumurakiberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumurakiberjalan, function ($table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->Integer('trado_id')->nullable();
            $table->date('tglbukti')->nullable();
        });

        $querytrip = db::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'b.stok_id',
                'a.trado_id',
                'a.tglbukti',
            )
            ->join(db::raw($tempSaldoAki . " b"), "a.trado_id", "b.trado_id")
            ->groupBy('b.stok_id')
            ->groupBy('a.trado_id')
            ->groupBy('a.tglbukti');

        DB::table($tempumurakiberjalan)->insertUsing([
            'stok_id',
            'trado_id',
            'tglbukti',
        ], $querytrip);

        // absensi supir

        
        $queryabsensi = db::table('absensisupirheader')->from(db::raw("absensisupirheader a with (readuncommitted)"))
            ->select(
                'b.stok_id',
                'c.trado_id',
                'a.tglbukti',
            )
            ->join(db::raw("absensisupirdetail c"), "a.nobukti", "c.nobukti")
            ->join(db::raw($tempSaldoAki . " b"), "c.trado_id", "b.trado_id")
            ->leftjoin(db::raw("absentrado d"), "c.absen_id", "d.id")
            ->whereraw("isnull(d.kodeabsen,'')='I'")
            ->groupBy('b.stok_id')
            ->groupBy('c.trado_id')
            ->groupBy('a.tglbukti');

        DB::table($tempumurakiberjalan)->insertUsing([
            'stok_id',
            'trado_id',
            'tglbukti',
        ], $queryabsensi);        


        $tempumurakiberjalanrekap = '##tempumurakiberjalanrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumurakiberjalanrekap, function ($table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->Integer('trado_id')->nullable();
            $table->integer('jumlahhari')->nullable();
        });

        $queryjumlah = db::table($tempumurakiberjalan)->from(db::raw($tempumurakiberjalan . " a "))
            ->select(
                'a.stok_id',
                'a.trado_id',
                db::raw("count(a.tglbukti) as jumlah"),
            )
            ->groupBy('a.stok_id')
            ->groupBy('a.trado_id');
        DB::table($tempumurakiberjalanrekap)->insertUsing([
            'stok_id',
            'trado_id',
            'jumlahhari',
        ], $queryjumlah);


        $tempumurlistumuraki = '##tempumurlistumuraki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumurlistumuraki, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
        });


        $hariaki = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text as id'
            )
            ->where('a.grp', 'HARIAKI')
            ->where('a.subgrp', 'HARIAKI')
            ->where('a.text', 'TANGGAL')
            ->first();

        $tempakipg = '##tempakipg' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempakipg, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->datetime('tglpg')->nullable();
        });

        $queryakipg =db::table($tempSaldoAki)->from(db::raw($tempSaldoAki . " a with(readuncommitted)"))
        ->select(
            'a.stok_id',
            db::raw("max(c.tglbukti) as tglpg")
        )
        ->join(db::raw("penerimaanstokdetail b with (readuncommitted)"),'a.stok_id','b.stok_id')
        ->join(db::raw("penerimaanstokheader c with (readuncommitted)"),'b.nobukti','c.nobukti')
        ->whereRaw("isnull(c.penerimaanstok_id,0)=5")
        ->whereRaw("isnull(c.gudangke_id,0)=3")
        ->groupby('a.stok_id');

        DB::table($tempakipg)->insertUsing([
            'stok_id',
            'tglpg',
        ], $queryakipg);

        $tempakikor = '##tempakikor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempakikor, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->datetime('tglkor')->nullable();
        });


        $queryakikor =db::table($tempSaldoAki)->from(db::raw($tempSaldoAki . " a with(readuncommitted)"))
        ->select(
            'a.stok_id',
            db::raw("max(c.tglbukti) as tglkor")
        )
        ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"),'a.stok_id','b.stok_id')
        ->join(db::raw("pengeluaranstokheader c with (readuncommitted)"),'b.nobukti','c.nobukti')
        ->whereRaw("isnull(c.pengeluaranstok_id,0)=3")
        ->groupby('a.stok_id');

        DB::table($tempakikor)->insertUsing([
            'stok_id',
            'tglkor',
        ], $queryakikor);


        if (isset($hariaki)) {
            $umursaldo = db::table($tempSaldoAki)->from(db::raw($tempSaldoAki . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("DATEDIFF(d ,a.tglawal,
                    (case when isnull(b.stok_id,0)=0 then 
                    cast(format(getdate(),'yyyy/MM/dd') as datetime)
                    else  
                        (case when isnull(b.tglpg,'1900/1/1')>=isnull(c.tglkor,'1900/1/1') then 
                            b.tglpg else  c.tglkor end)

                    end)
                    
                    ) as jumlahhari ")
                )
                ->leftjoin(db::raw($tempakipg ." b "),'a.stok_id','b.stok_id')
                ->leftjoin(db::raw($tempakikor ." c "),'a.stok_id','c.stok_id')
                ->orderby('a.stok_id', 'asc');

            DB::table($tempumurlistumuraki)->insertUsing([
                'stok_id',
                'jumlahhari',
            ], $umursaldo);

            $umuraki = db::table($tempumurlistumuraki)->from(db::raw($tempumurlistumuraki . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("a.jumlahhari as jumlahhari"),
                    'b.tglawal'

                )
                ->join(db::raw($tempSaldoAki . " b"), 'a.stok_id', 'b.stok_id')

                ->orderby('a.stok_id', 'asc');
        } else {
            $umursaldo = db::table($tempSaldoAki)->from(db::raw($tempSaldoAki . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("sum(a.jumlahharitrip) as jumlahhari")
                )
                ->groupby('a.stok_id')
                ->orderby('a.stok_id', 'asc');

            DB::table($tempumurlistumuraki)->insertUsing([
                'stok_id',
                'jumlahhari',
            ], $umursaldo);

            $umur = db::table($tempumurakiberjalanrekap)->from(db::raw($tempumurakiberjalanrekap . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("sum(a.jumlahhari) as jumlahhari")
                )
                ->groupby('a.stok_id')
                ->orderby('a.stok_id', 'asc');

            DB::table($tempumurlistumuraki)->insertUsing([
                'stok_id',
                'jumlahhari',
            ], $umur);
            $umuraki = db::table($tempumurlistumuraki)->from(db::raw($tempumurlistumuraki . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("sum(a.jumlahhari) as jumlahhari"),
                    db::raw("'1900/1/1' as tglawal")
                )
                ->groupby('a.stok_id')
                ->orderby('a.stok_id', 'asc');
        }


        return $umuraki;
    }

    public function getallstoktnl()
    {

        // filter stok yang pja
        // dd('test');
        $tempstokpja = '##tempstokpja' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::connection('srvtnl')->create($tempstokpja, function ($table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->string('nobukti', 50)->nullable();
        });

        $pengeluaranstok_pja = db::connection('srvtnl')->table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text'
            )
            ->where('a.grp', 'PENJUALAN STOK AFKIR')
            ->where('a.subgrp', 'PENJUALAN STOK AFKIR')
            ->first()->text ?? 0;



        $querypja = db::connection('srvtnl')->table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with(readuncommitted)"))
            ->select(
                'b.stok_id',
                'a.nobukti',

            )
            ->join(db::raw("pengeluaranstokdetail b with(readuncommitted)"), "a.nobukti", "b.nobukti")
            ->join(db::raw("stok c with(readuncommitted)"), "b.stok_id", "c.id")
            ->where("a.pengeluaranstok_id", $pengeluaranstok_pja)
            ->whereRaw("isnull(C.kelompok_id,0)=3");

        DB::connection('srvtnl')->table($tempstokpja)->insertUsing([
            'stok_id',
            'nobukti',
        ], $querypja);


        $tempSaldoAki = '##tempSaldoAki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::connection('srvtnl')->create($tempSaldoAki, function ($table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->Integer('trado_id')->nullable();
            $table->date('tglawal')->nullable();
            $table->integer('jumlahharitrip')->nullable();
            $table->bigInteger('idsaldo')->nullable();
        });

        $querysaldo = db::connection('srvtnl')->table('saldoumuraki')->from(db::raw("saldoumuraki a with (readuncommitted)"))
            ->select(
                'a.stok_id',
                'a.trado_id',
                'a.tglawal',
                'a.jumlahharitrip',
                'a.id',
            )
            ->leftjoin(db::raw($tempstokpja . " b"), "a.stok_id", "b.stok_id")
            ->join(db::raw("stok c with(readuncommitted)"), "a.stok_id", "c.id")
            ->whereRaw("isnull(c.kelompok_id,0)=3")
            ->whereraw("isnull(b.stok_id,0)=0");


        DB::connection('srvtnl')->table($tempSaldoAki)->insertUsing([
            'stok_id',
            'trado_id',
            'tglawal',
            'jumlahharitrip',
            'idsaldo',
        ], $querysaldo);


        $tempumurakiberjalan = '##tempumurakiberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::connection('srvtnl')->create($tempumurakiberjalan, function ($table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->Integer('trado_id')->nullable();
            $table->date('tglbukti')->nullable();
        });

        $querytrip = db::connection('srvtnl')->table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'b.stok_id',
                'a.trado_id',
                'a.tglbukti',
            )
            ->join(db::raw($tempSaldoAki . " b"), "a.trado_id", "b.trado_id")
            ->groupBy('b.stok_id')
            ->groupBy('a.trado_id')
            ->groupBy('a.tglbukti');

        DB::connection('srvtnl')->table($tempumurakiberjalan)->insertUsing([
            'stok_id',
            'trado_id',
            'tglbukti',
        ], $querytrip);

        // absensi supir

        
        $queryabsensi = db::connection('srvtnl')->table('absensisupirheader')->from(db::raw("absensisupirheader a with (readuncommitted)"))
            ->select(
                'b.stok_id',
                'c.trado_id',
                'a.tglbukti',
            )
            ->join(db::raw("absensisupirdetail c"), "a.nobukti", "c.nobukti")
            ->join(db::raw($tempSaldoAki . " b"), "c.trado_id", "b.trado_id")
            ->leftjoin(db::raw("absentrado d"), "c.absen_id", "d.id")
            ->whereraw("isnull(d.kodeabsen,'')='I'")
            ->groupBy('b.stok_id')
            ->groupBy('c.trado_id')
            ->groupBy('a.tglbukti');

        DB::connection('srvtnl')->table($tempumurakiberjalan)->insertUsing([
            'stok_id',
            'trado_id',
            'tglbukti',
        ], $queryabsensi);        


        $tempumurakiberjalanrekap = '##tempumurakiberjalanrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::connection('srvtnl')->create($tempumurakiberjalanrekap, function ($table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->Integer('trado_id')->nullable();
            $table->integer('jumlahhari')->nullable();
        });

        $queryjumlah = db::connection('srvtnl')->table($tempumurakiberjalan)->from(db::raw($tempumurakiberjalan . " a "))
            ->select(
                'a.stok_id',
                'a.trado_id',
                db::raw("count(a.tglbukti) as jumlah"),
            )
            ->groupBy('a.stok_id')
            ->groupBy('a.trado_id');
        DB::connection('srvtnl')->table($tempumurakiberjalanrekap)->insertUsing([
            'stok_id',
            'trado_id',
            'jumlahhari',
        ], $queryjumlah);


        $tempumurlistumuraki = '##tempumurlistumuraki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::connection('srvtnl')->create($tempumurlistumuraki, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
        });


        $hariaki = db::connection('srvtnl')->table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text as id'
            )
            ->where('a.grp', 'HARIAKI')
            ->where('a.subgrp', 'HARIAKI')
            ->where('a.text', 'TANGGAL')
            ->first();

        $tempakipg = '##tempakipg' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::connection('srvtnl')->create($tempakipg, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->datetime('tglpg')->nullable();
        });

        $queryakipg =db::connection('srvtnl')->table($tempSaldoAki)->from(db::raw($tempSaldoAki . " a with(readuncommitted)"))
        ->select(
            'a.stok_id',
            db::raw("max(c.tglbukti) as tglpg")
        )
        ->join(db::raw("penerimaanstokdetail b with (readuncommitted)"),'a.stok_id','b.stok_id')
        ->join(db::raw("penerimaanstokheader c with (readuncommitted)"),'b.nobukti','c.nobukti')
        ->whereRaw("isnull(c.penerimaanstok_id,0)=5")
        ->whereRaw("isnull(c.gudangke_id,0)=3")
        ->groupby('a.stok_id');

        DB::connection('srvtnl')->table($tempakipg)->insertUsing([
            'stok_id',
            'tglpg',
        ], $queryakipg);

        $tempakikor = '##tempakikor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::connection('srvtnl')->create($tempakikor, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->datetime('tglkor')->nullable();
        });


        $queryakikor =db::connection('srvtnl')->table($tempSaldoAki)->from(db::raw($tempSaldoAki . " a with(readuncommitted)"))
        ->select(
            'a.stok_id',
            db::raw("max(c.tglbukti) as tglkor")
        )
        ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"),'a.stok_id','b.stok_id')
        ->join(db::raw("pengeluaranstokheader c with (readuncommitted)"),'b.nobukti','c.nobukti')
        ->whereRaw("isnull(c.pengeluaranstok_id,0)=3")
        ->groupby('a.stok_id');

        DB::connection('srvtnl')->table($tempakikor)->insertUsing([
            'stok_id',
            'tglkor',
        ], $queryakikor);


        if (isset($hariaki)) {
            $umursaldo = db::connection('srvtnl')->table($tempSaldoAki)->from(db::raw($tempSaldoAki . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("DATEDIFF(d ,a.tglawal,
                    (case when isnull(b.stok_id,0)=0 then 
                    cast(format(getdate(),'yyyy/MM/dd') as datetime)
                    else  
                        (case when isnull(b.tglpg,'1900/1/1')>=isnull(c.tglkor,'1900/1/1') then 
                            b.tglpg else  c.tglkor end)

                    end)
                    
                    ) as jumlahhari ")
                )
                ->leftjoin(db::raw($tempakipg ." b "),'a.stok_id','b.stok_id')
                ->leftjoin(db::raw($tempakikor ." c "),'a.stok_id','c.stok_id')
                ->orderby('a.stok_id', 'asc');

            DB::connection('srvtnl')->table($tempumurlistumuraki)->insertUsing([
                'stok_id',
                'jumlahhari',
            ], $umursaldo);

            $umuraki = db::connection('srvtnl')->table($tempumurlistumuraki)->from(db::raw($tempumurlistumuraki . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("a.jumlahhari as jumlahhari"),
                    'b.tglawal'

                )
                ->join(db::raw($tempSaldoAki . " b"), 'a.stok_id', 'b.stok_id')

                ->orderby('a.stok_id', 'asc');
        } else {
            $umursaldo = db::connection('srvtnl')->table($tempSaldoAki)->from(db::raw($tempSaldoAki . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("sum(a.jumlahharitrip) as jumlahhari")
                )
                ->groupby('a.stok_id')
                ->orderby('a.stok_id', 'asc');

            DB::connection('srvtnl')->table($tempumurlistumuraki)->insertUsing([
                'stok_id',
                'jumlahhari',
            ], $umursaldo);

            $umur = db::connection('srvtnl')->table($tempumurakiberjalanrekap)->from(db::raw($tempumurakiberjalanrekap . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("sum(a.jumlahhari) as jumlahhari")
                )
                ->groupby('a.stok_id')
                ->orderby('a.stok_id', 'asc');

            DB::connection('srvtnl')->table($tempumurlistumuraki)->insertUsing([
                'stok_id',
                'jumlahhari',
            ], $umur);
            $umuraki = db::connection('srvtnl')->table($tempumurlistumuraki)->from(db::raw($tempumurlistumuraki . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("sum(a.jumlahhari) as jumlahhari"),
                    db::raw("'1900/1/1' as tglawal")
                )
                ->groupby('a.stok_id')
                ->orderby('a.stok_id', 'asc');
        }


        return $umuraki;
    }
}
