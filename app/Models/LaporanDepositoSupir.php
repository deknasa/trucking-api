<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanDepositoSupir extends MyModel
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



    public function getReport($sampai, $jenis)
    {
        $penerimaantrucking_id=3;
        $sampai = date('Y-m-d', strtotime(request()->sampai)) ?? '1900/1/1';
        $jenis = request()->jenis ?? '';

        $temppenerimaantrucking = '##temppenerimaantrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaantrucking, function ($table) {
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('jumlah')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypenerimaantrucking = DB::table('penerimaantruckingheader')->from(
            DB::raw("penerimaantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'b.supir_id',
                DB::raw("count(b.supir_id) as jumlah"),
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("penerimaantruckingdetail b with (readuncommitted)"), 'a.id', 'b.penerimaantruckingheader_id')
            ->where('a.tglbukti', '<', $sampai)
            ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
            ->groupBy('b.supir_id');

        DB::table($temppenerimaantrucking)->insertUsing([
            'supir_id',
            'jumlah',
            'nominal',
        ], $querypenerimaantrucking);

        $temppengeluarantrucking = '##temppengeluarantrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengeluarantrucking, function ($table) {
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypengeluarantrucking = DB::table('pengeluarantruckingheader')->from(
            DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'b.supir_id',
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("pengeluarantruckingdetail b with (readuncommitted)"), 'a.id', 'b.pengeluarantruckingheader_id')
            ->where('a.tglbukti', '<', $sampai)
            ->where('a.pengeluarantrucking_id', '=', 1)
            ->groupBy('b.supir_id');

        DB::table($temppengeluarantrucking)->insertUsing([
            'supir_id',
            'nominal',
        ], $querypengeluarantrucking);

        // 
        $temppenerimaantruckinglist = '##temppenerimaantruckinglist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaantruckinglist, function ($table) {
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('jumlah')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypenerimaantruckinglist = DB::table('penerimaantruckingheader')->from(
            DB::raw("penerimaantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'b.supir_id',
                DB::raw("count(b.supir_id) as jumlah"),
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("penerimaantruckingdetail b with (readuncommitted)"), 'a.id', 'b.penerimaantruckingheader_id')
            ->where('a.tglbukti', '=', $sampai)
            ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
            ->groupBy('b.supir_id');

        DB::table($temppenerimaantruckinglist)->insertUsing([
            'supir_id',
            'jumlah',
            'nominal',
        ], $querypenerimaantruckinglist);

        $temppengeluarantruckinglist = '##temppengeluarantruckinglist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengeluarantruckinglist, function ($table) {
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypengeluarantruckinglist = DB::table('pengeluarantruckingheader')->from(
            DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'b.supir_id',
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("pengeluarantruckingdetail b with (readuncommitted)"), 'a.id', 'b.pengeluarantruckingheader_id')
            ->where('a.tglbukti', '=', $sampai)
            ->where('a.pengeluarantrucking_id', '=',  $penerimaantrucking_id)
            ->groupBy('b.supir_id');

        DB::table($temppengeluarantruckinglist)->insertUsing([
            'supir_id',
            'nominal',
        ], $querypengeluarantruckinglist);

        // 

        $temprangedeposito = '##temprangedeposito' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprangedeposito, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->double('nominalawal', 15, 2)->nullable();
            $table->double('nominalakhir', 15, 2)->nullable();
            $table->longtext('keterangan', 1000)->nullable();
        });

        $queryrangedeposito = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                DB::raw("cast(substring([text],1,charindex('-',[text])-1) as money) as nominalawal"),
                DB::raw("cast(substring([text],charindex('-',[text])+1,20) as money) as nominalakhir"),
                 DB::raw("'Keterangan Deposito '+format(cast(substring([text],1,charindex('-',[text])-1) as money),'#,#0')+' - '+format(cast(substring([text],charindex('-',[text])+1,20) as money),'#,#')  as keterangan"),
            )
            ->where('a.grp', '=', 'RANGE DEPOSITO SUPIR')
            ->where('a.subgrp', '=', 'RANGE DEPOSITO SUPIR')
            ->OrderBy('id', 'Asc');

        DB::table($temprangedeposito)->insertUsing([
            'id',
            'nominalawal',
            'nominalakhir',
            'keterangan',
        ], $queryrangedeposito);


        $tempsaldo = '##tempsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldo, function ($table) {
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->string('namasupir',200)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->double('deposito', 15, 2)->nullable();
            $table->double('penarikan', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('cicil', 15, 2)->nullable();
        });

        $querysaldo = DB::table('supir')->from(
            DB::raw("supir as c with (readuncommitted)")
        )
            ->select(
                'c.id as supir_id',
                'c.namasupir as namasupir',
                DB::raw("(isnull(b.nominal,0)-isnull(a.nominal,0)) as saldo"),
                DB::raw("isnull(b1.nominal,0) as deposito"),
                DB::raw("isnull(a1.nominal,0) as penarikan"),
                DB::raw("((isnull(b.nominal,0)-isnull(a.nominal,0))+isnull(b1.nominal,0))-isnull(a1.nominal,0) as total"),
                DB::raw("'DEPOSITO SUPIR A/N '+ltrim(rtrim(c.namasupir)) as keterangan"),
                DB::raw("(isnull(b.jumlah,0)+isnull(b1.jumlah,0))  as cicil"),

            )
            ->leftjoin(DB::raw($temppenerimaantrucking . "  as b "), 'c.id', 'b.supir_id')
            ->leftjoin(DB::raw($temppengeluarantrucking . "  as a "), 'c.id', 'a.supir_id')
            ->leftjoin(DB::raw($temppenerimaantruckinglist . "  as b1 "), 'c.id', 'b1.supir_id')
            ->leftjoin(DB::raw($temppengeluarantruckinglist . "  as a1 "), 'c.id', 'a1.supir_id')
            ->whereRaw(DB::raw("(isnull(b.nominal,0)-isnull(a.nominal,0))<>0 or isnull(b1.nominal,0)<>0 or isnull(a1.nominal,0)<>0"))
            ->orderBy('c.id', 'Asc');

            DB::table($tempsaldo)->insertUsing([
                'supir_id',
                'namasupir',
                'saldo',
                'deposito',
                'penarikan',
                'total',
                'keterangan',
                'cicil',
            ], $querysaldo);

            $query=DB::table($tempsaldo)->from(
                DB::raw($tempsaldo. " as a")
            )
            ->select (
                'b.id',
                'a.supir_id',
                'a.namasupir',
                'a.saldo',
                'a.deposito',
                'a.penarikan',
                'a.total',
                'a.keterangan',
                'a.cicil',                
                DB::raw("b.keterangan as keterangan"),
                DB::raw("'DEPOSITO SUPIR A/N '+trim(a.namasupir) as keterangandeposito")
            )
            ->join(DB::raw($temprangedeposito ." as b "), function ($join)  {
                $join->on('a.total', '>=', 'b.nominalawal');
                $join->on('a.total', '<=', 'b.nominalakhir');
            });

            $data=$query->get();
       
        return $data;
    }
}
