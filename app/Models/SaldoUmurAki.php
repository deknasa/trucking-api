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

        $tempstokpja = '##tempstokpja' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstokpja, function ($table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->stringr('nobukti', 50)->nullable();
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
            $table->bigInteger('id')->nullable();
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
            'id',
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
            'jumlah',
        ], $queryjumlah);

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

        return $umuraki;
    }
}
