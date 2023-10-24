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
        if (isset($hariaki)) {
            $umursaldo = db::table($tempSaldoAki)->from(db::raw($tempSaldoAki . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("DATEDIFF(d ,a.tglawal,cast(format(getdate(),'yyyy/MM/dd') as datetime)) as jumlahhari ")
                )
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
            ->join(db::raw($tempSaldoAki ." b"),'a.stok_id','b.stok_id')

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
}
