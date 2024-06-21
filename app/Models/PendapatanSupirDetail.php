<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PendapatanSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pendapatansupirdetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function findUpdate($id)
    {
        $query = DB::table('pendapatansupirdetail')->from(DB::raw("pendapatansupirdetail with (readuncommitted)"))
            ->select(
                'pendapatansupirdetail.supir_id',
                'supir.namasupir as supir',
                'pendapatansupirdetail.nominal',
                'pendapatansupirdetail.keterangan'
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pendapatansupirdetail.supir_id', 'supir.id')
            ->where('pendapatansupirdetail.pendapatansupir_id', $id)
            ->get();

        return $query;
    }
    public function get()
    {
        $this->setRequestParameters();


        $formatCetak = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'FORMAT CETAK')
            ->where('subgrp', 'KOMISI SUPIR')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));
        $tempsaldopendapatan = '##tempsaldopendapatan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldopendapatan, function ($table) {
            $table->string('nobukti', 1000)->nullable();
            $table->string('nobuktitrip', 1000)->nullable();
            $table->integer('supir_id')->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tgltrip')->nullable();
            $table->date('tgl_ric')->nullable();
            $table->string('nobuktirincian', 1000)->nullable();
            $table->integer('dari_id')->nullable();
            $table->integer('sampai_id')->nullable();
            $table->double('nominal')->nullable();
            $table->double('gajikenek')->nullable();
        });

        if ($formatCetak->text == 'FORMAT 1') {
            $querysaldopendapatan = $query->select(
                $this->table . '.nobukti',
                $this->table . '.nobuktitrip',
                DB::raw("(case when ISNULL(suratpengantar.supir_id, '') = '' then ISNULL(saldosuratpengantar.supir_id, '') else ISNULL(suratpengantar.supir_id, '')  end) as supir_id"),
                DB::raw("(case when ISNULL(suratpengantar.trado_id, '') = '' then ISNULL(saldosuratpengantar.trado_id, '') else ISNULL(suratpengantar.trado_id, '')  end) as trado_id"),
                DB::raw("(case when ISNULL(suratpengantar.tglbukti, '') = '' then ISNULL(saldopendapatansupir.suratpengantar_tglbukti, '') else ISNULL(suratpengantar.tglbukti, '')  end) as tgltrip"),
                DB::raw("null as tgl_ric"),
                $this->table . '.nobuktirincian',
                DB::raw("(case when ISNULL(suratpengantar.dari_id, '') = '' then ISNULL(saldopendapatansupir.dari_id, '') else ISNULL(suratpengantar.dari_id, '')  end) as dari_id"),
                DB::raw("(case when ISNULL(suratpengantar.sampai_id, '') = '' then ISNULL(saldopendapatansupir.sampai_id, '') else ISNULL(suratpengantar.sampai_id, '')  end) as sampai_id"),
                $this->table . '.nominal',
                DB::raw("(case when pendapatansupirdetail.gajikenek IS NULL then 0 else pendapatansupirdetail.gajikenek end) as gajikenek"),
            )
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), $this->table . '.nobuktitrip', 'suratpengantar.nobukti')
                ->leftJoin(DB::raw("saldopendapatansupir with (readuncommitted)"), $this->table . '.nobuktitrip', 'saldopendapatansupir.suratpengantar_nobukti')
                ->leftJoin(DB::raw("saldosuratpengantar with (readuncommitted)"), 'saldopendapatansupir.suratpengantar_nobukti', 'saldosuratpengantar.nobukti')
                ->where($this->table . '.pendapatansupir_id', '=', request()->pendapatansupir_id);
        } else {
            $querysaldopendapatan = $query->select(
                $this->table . '.nobukti',
                $this->table . '.nobuktitrip',
                $this->table . '.supir_id',
                DB::raw("0 as trado_id"),
                DB::raw("null as tgltrip"),
                'gajisupirheader.tglbukti as tgl_ric',
                $this->table . '.nobuktirincian',
                $this->table . '.dari_id',
                $this->table . '.sampai_id',
                DB::raw("(case when pendapatansupirdetail.nominal IS NULL then 0 else pendapatansupirdetail.nominal end) as nominal"),
                DB::raw("(case when pendapatansupirdetail.gajikenek IS NULL then 0 else pendapatansupirdetail.gajikenek end) as gajikenek"),

            )->join(DB::raw("gajisupirheader with (readuncommitted)"), 'gajisupirheader.nobukti', 'pendapatansupirdetail.nobuktirincian')
                ->where('pendapatansupirdetail.pendapatansupir_id', request()->pendapatansupir_id);
        }
        DB::table($tempsaldopendapatan)->insertUsing([
            'nobukti',
            'nobuktitrip',
            'supir_id',
            'trado_id',
            'tgltrip',
            'tgl_ric',
            'nobuktirincian',
            'dari_id',
            'sampai_id',
            'nominal',
            'gajikenek'

        ], $querysaldopendapatan);
        if (isset(request()->forReport) && request()->forReport) {

            $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'DEPOSITO')->first();
            $deposito = $params->text;

            if ($deposito == 'YA') {
                $tempdepo = '##depopinjam' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempdepo, function ($table) {
                    $table->bigInteger('supir_id')->nullable();
                    $table->double('deposito')->nullable();
                    $table->double('pengembalianpinjaman')->nullable();
                    $table->double('total_amount')->nullable();
                });

                $fetch = DB::table(function ($subquery) {
                    $getNobukti = DB::table("pendapatansupirheader")->from(DB::raw("pendapatansupirheader with (readuncommitted)"))->where('id', request()->pendapatansupir_id)->first();
                    $subquery->select(
                        DB::raw('b.nominal as deposito'),
                        'b.supir_id',
                        DB::raw('0 as pinjaman')
                    )
                        ->from('penerimaantruckingheader as a')
                        ->leftJoin('penerimaantruckingdetail as b', 'a.nobukti', '=', 'b.nobukti')
                        ->where('a.pendapatansupir_bukti', $getNobukti->nobukti)
                        ->where('a.nobukti', 'like', '%dpo%');

                    $subquery->unionAll(
                        DB::table('penerimaantruckingheader as a')
                            ->select(
                                DB::raw('0 as deposito'),
                                'b.supir_id',
                                DB::raw('SUM(b.nominal) as pinjaman')
                            )
                            ->leftJoin('penerimaantruckingdetail as b', 'a.nobukti', '=', 'b.nobukti')
                            ->where('a.pendapatansupir_bukti', $getNobukti->nobukti)
                            ->where('a.nobukti', 'like', '%pjp%')
                            ->groupBy('b.supir_id')
                    );
                }, 'combined_data')
                    ->select(
                        'combined_data.supir_id',
                        DB::raw('SUM(isnull(combined_data.deposito,0)) AS deposito'),
                        DB::raw('SUM(isnull(combined_data.pinjaman,0)) AS pengembalianpinjaman'),
                        DB::raw('SUM(isnull(combined_data.pinjaman, combined_data.deposito)) AS total_amount')
                    )
                    ->groupBy('combined_data.supir_id');

                DB::table($tempdepo)->insertUsing(['supir_id', 'deposito', 'pengembalianpinjaman', 'total_amount'], $fetch);

                $getNobukti = DB::table("pendapatansupirheader")->from(DB::raw("pendapatansupirheader with (readuncommitted)"))->where('id', request()->pendapatansupir_id)->first();

                $tempPendapatan = '##pendapatan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempPendapatan, function ($table) {
                    $table->bigInteger('supir_id')->nullable();
                    $table->double('nominal')->nullable();
                    $table->double('gajikenek')->nullable();
                    $table->bigInteger('trado_id')->nullable();
                });

                $fetch2 = DB::table("pendapatansupirdetail")->from(DB::raw("pendapatansupirdetail as a with (readuncommitted)"))
                    ->select(
                        'a.supir_id',
                        'a.nominal',
                        'a.gajikenek',
                        db::raw("(case when isnull(b.trado_id,0)=0 then isnull(b1.trado_id,0) else isnull(b.trado_id,0) end) as trado_id"),
                    )
                    ->leftJoin(DB::raw("suratpengantar as b with (readuncommitted)"), 'a.nobuktitrip', 'b.nobukti')
                    ->leftJoin(DB::raw("saldosuratpengantar as b1 with (readuncommitted)"), 'a.nobuktitrip', 'b1.nobukti')
                    ->where('a.nobukti', $getNobukti->nobukti);

                DB::table($tempPendapatan)->insertUsing(['supir_id', 'nominal', 'gajikenek', 'trado_id'], $fetch2);



                $tempkomisi = '##komisi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempkomisi, function ($table) {
                    $table->string('jenis', 500)->nullable();
                    $table->bigInteger('trado_id')->nullable();
                    $table->string('kode_trado', 500)->nullable();
                    $table->string('namasupir', 500)->nullable();
                    $table->double('komisi')->nullable();
                    $table->double('deposito')->nullable();
                    $table->double('pengembalianpinjaman')->nullable();
                    $table->double('total')->nullable();
                });

                $query = DB::table($tempPendapatan)->from(DB::raw("$tempPendapatan as t1 with (readuncommitted)"))
                    ->select(
                        DB::raw(
                            "'SUPIR' as jenis,t1.trado_id,trado.kodetrado,isnull(supir.namasupir,'') as namasupir,
                    SUM(t1.nominal) AS komisi,
                    SUM(ISNULL(t2.deposito, 0)) AS deposito,
                    SUM(ISNULL(t2.pengembalianpinjaman, 0)) AS pengembalianpinjaman,
                    SUM(t1.nominal  - ISNULL(t2.total_amount, 0)) AS total
                    "
                        )
                    )
                    ->leftJoin(DB::raw("$tempdepo as t2 with (readuncommitted)"), 't1.supir_id', 't2.supir_id')
                    ->leftJoin(DB::raw("trado with (readuncommitted)"), 't1.trado_id', 'trado.id')
                    ->leftJoin(DB::raw("supir with (readuncommitted)"), 't1.supir_id', 'supir.id')
                    ->groupBy('t1.trado_id', 'supir.namasupir', 'trado.kodetrado');

                DB::table($tempkomisi)->insertUsing(['jenis', 'trado_id', 'kode_trado', 'namasupir', 'komisi', 'deposito', 'pengembalianpinjaman', 'total'], $query);

                $query = DB::table($tempPendapatan)->from(DB::raw("$tempPendapatan as t1 with (readuncommitted)"))
                    ->select(DB::raw("'KENEK' as jenis,t1.trado_id,trado.kodetrado,'' as namasupir,
                SUM( t1.gajikenek ) AS komisi,
                0 AS deposito,
                0 AS pengembalianpinjaman,
                SUM( t1.gajikenek ) AS total

                "))
                    ->leftJoin(DB::raw("$tempdepo as t2 with (readuncommitted)"), 't1.supir_id', 't2.supir_id')
                    ->leftJoin(DB::raw("trado with (readuncommitted)"), 't1.trado_id', 'trado.id')
                    ->leftJoin(DB::raw("supir with (readuncommitted)"), 't1.supir_id', 'supir.id')
                    ->groupBy('t1.trado_id',  'trado.kodetrado');

                DB::table($tempkomisi)->insertUsing(['jenis', 'trado_id', 'kode_trado', 'namasupir', 'komisi', 'deposito', 'pengembalianpinjaman', 'total'], $query);

                $query = db::table($tempkomisi)->from(db::raw($tempkomisi . " as a"))
                    ->select(
                        'a.jenis',
                        'a.trado_id',
                        'a.kode_trado',
                        'a.namasupir',
                        'a.komisi',
                        'a.deposito',
                        'a.pengembalianpinjaman',
                        'a.total',
                        DB::raw("'BUKTI KOMISI '+a.jenis as judulLaporan"),
                    )
                    ->whereRaw("a.jenis='KENEK'")
                    ->orderBY('a.jenis', 'desc')
                    ->orderBY('a.kode_trado', 'asc')
                    ->orderBY('a.namasupir', 'asc');
            } else {

                $query = DB::table($tempsaldopendapatan)->from(
                    db::raw($tempsaldopendapatan . " a ")
                )->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'bank.namabank as bank',
                    'header.tgldari',
                    'header.tglsampai',
                    'header.periode',
                    'supir.namasupir as supir_id',
                    'a.nobuktitrip',
                    'a.tgltrip',
                    'a.nobuktirincian',
                    DB::raw("isnull(b.kodekota,'') as dari"),
                    DB::raw("isnull(c.kodekota,'') as sampai"),
                    'a.nominal',
                    'a.gajikenek'
                )
                    ->leftJoin(DB::raw("pendapatansupirheader as header with (readuncommitted)"), 'header.nobukti', 'a.nobukti')
                    ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
                    ->leftJoin(DB::raw("supir with (readuncommitted)"), 'header.supir_id', 'supir.id')
                    ->leftJoin(DB::raw("kota b with (readuncommitted)"), 'a.dari_id', 'b.id')
                    ->leftJoin(DB::raw("kota c with (readuncommitted)"), 'a.sampai_id', 'c.id');
            }
        } else {

            $query = DB::table($tempsaldopendapatan)->from(
                db::raw($tempsaldopendapatan . " a ")
            )
                ->select(
                    'a.nobukti',
                    'a.nobuktitrip as nobukti_trip',
                    'supir.namasupir as supirdetail',
                    'trado.kodetrado as tradodetail',
                    'a.tgltrip as tgl_trip',
                    'a.tgl_ric',
                    'a.nobuktirincian',
                    DB::raw("isnull(b.kodekota,'') as dari"),
                    DB::raw("isnull(c.kodekota,'') as sampai"),
                    'a.nominal as nominal_detail',
                    'a.gajikenek',
                    DB::raw("(a.gajikenek + a.nominal) as totaldetail"),
                    db::raw("cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as date) as tgldarisuratpengantar"),
                    db::raw("cast(cast(format((cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaisuratpengantar"),
                    db::raw("cast((format(gajisupirheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldarigajisupirheader"),
                    db::raw("cast(cast(format((cast((format(gajisupirheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaigajisupirheader"),
    
                )
                ->leftJoin(DB::raw("kota b with (readuncommitted)"), 'a.dari_id', 'b.id')
                ->leftJoin(DB::raw("kota c with (readuncommitted)"), 'a.sampai_id', 'c.id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'a.supir_id', 'supir.id')
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'a.nobuktitrip', '=', 'suratpengantar.nobukti')
                ->leftJoin(DB::raw("gajisupirheader with (readuncommitted)"), 'a.nobuktirincian', '=', 'gajisupirheader.nobukti')

                ->leftJoin(DB::raw("trado with (readuncommitted)"), 'a.trado_id', 'trado.id');

            $this->sort($query);
            $this->filter($query);
            $this->totalNominal = $query->sum('a.nominal');
            $this->totalGajiKenek = $query->sum('a.gajikenek');
            $this->totalAll = $query->sum(DB::raw("(a.gajikenek + a.nominal)"));
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function getsupir()
    {
        $formatCetak = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'FORMAT CETAK')
            ->where('subgrp', 'KOMISI SUPIR')
            ->first();

        if (isset(request()->forReport) && request()->forReport) {

            $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'DEPOSITO')->first();
            $deposito = $params->text;

            if ($deposito == 'YA') {
                $tempdepo = '##depopinjam' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempdepo, function ($table) {
                    $table->bigInteger('supir_id')->nullable();
                    $table->double('deposito')->nullable();
                    $table->double('pengembalianpinjaman')->nullable();
                    $table->double('total_amount')->nullable();
                });


                $tempdeposito = '##tempdeposito' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempdeposito, function ($table) {
                    $table->bigInteger('supir_id')->nullable();
                    $table->double('nominal')->nullable();
                });
                $getNobukti = DB::table("pendapatansupirheader")->from(DB::raw("pendapatansupirheader with (readuncommitted)"))->where('id', request()->pendapatansupir_id)->first();

                $querydeposito = db::table("penerimaantruckingheader")->from(db::raw("penerimaantruckingheader a with (readuncommitted)"))
                    ->select(
                        'b.supir_id',
                        db::raw("sum(b.nominal) as nominal")
                    )
                    ->join(db::raw("penerimaantruckingdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                    ->where('a.pendapatansupir_bukti', $getNobukti->nobukti)
                    ->whereraw("a.penerimaantrucking_id=3")
                    ->groupby('b.supir_id');

                DB::table($tempdeposito)->insertUsing(['supir_id', 'nominal'], $querydeposito);


                $temppengembalianpinjaman = '##temppengembalianpinjaman' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($temppengembalianpinjaman, function ($table) {
                    $table->bigInteger('supir_id')->nullable();
                    $table->double('nominal')->nullable();
                });

                $querypengembalianpinjaman = db::table("penerimaantruckingheader")->from(db::raw("penerimaantruckingheader a with (readuncommitted)"))
                    ->select(
                        'b.supir_id',
                        db::raw("sum(b.nominal) as nominal")
                    )
                    ->join(db::raw("penerimaantruckingdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                    ->where('a.pendapatansupir_bukti', $getNobukti->nobukti)
                    ->whereraw("a.penerimaantrucking_id=2")
                    ->groupby('b.supir_id');

                DB::table($temppengembalianpinjaman)->insertUsing(['supir_id', 'nominal'], $querypengembalianpinjaman);

                // dump(db::table($tempdeposito)->select(db::raw("sum(nominal) as nominal"))->first());
                // dd(db::table($temppengembalianpinjaman)->select(db::raw("sum(nominal) as nominal"))->first());

                $fetch = db::table('supir')->from(db::raw("supir a with (readuncommitted)"))
                    ->select(
                        'a.id as supir_id',
                        db::raw("sum(isnull(b.nominal,0)) as deposito"),
                        db::raw("sum(isnull(c.nominal,0)) as pengembalianpinjaman"),
                        db::raw("sum((isnull(b.nominal,0)+isnull(c.nominal,0))) as total_amount"),
                    )
                    ->leftjoin(db::raw($tempdeposito . " b"), 'a.id', 'b.supir_id')
                    ->leftjoin(db::raw($temppengembalianpinjaman . " c"), 'a.id', 'c.supir_id')
                    ->whereRaw("(isnull(b.nominal,0)+isnull(c.nominal,0))<>0")
                    ->groupBy("a.id");




                // dd($fetch->get());
                DB::table($tempdepo)->insertUsing(['supir_id', 'deposito', 'pengembalianpinjaman', 'total_amount'], $fetch);


                // dd(db::table($tempdepo)->select(db::raw("sum(deposito) as deposito"),db::raw("sum(pengembalianpinjaman) as pengembalianpinjaman"))->first());

                $getNobukti = DB::table("pendapatansupirheader")->from(DB::raw("pendapatansupirheader with (readuncommitted)"))->where('id', request()->pendapatansupir_id)->first();

                $tempPendapatan = '##pendapatan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempPendapatan, function ($table) {
                    $table->bigInteger('supir_id')->nullable();
                    $table->double('nominal')->nullable();
                    $table->double('gajikenek')->nullable();
                    $table->bigInteger('trado_id')->nullable();
                    $table->string('nobuktirincian', 500)->nullable();
                });

                $fetch2 = DB::table("pendapatansupirdetail")->from(DB::raw("pendapatansupirdetail as a with (readuncommitted)"))
                    ->select(
                        'a.supir_id',
                        'a.nominal',
                        'a.gajikenek',
                        db::raw("(case when isnull(b.trado_id,0)=0 then isnull(b1.trado_id,0) else isnull(b.trado_id,0) end) as trado_id"),
                        'a.nobuktirincian'
                    )
                    ->leftJoin(DB::raw("suratpengantar as b with (readuncommitted)"), 'a.nobuktitrip', 'b.nobukti')
                    ->leftJoin(DB::raw("saldosuratpengantar as b1 with (readuncommitted)"), 'a.nobuktitrip', 'b1.nobukti')
                    ->where('a.nobukti', $getNobukti->nobukti);

                DB::table($tempPendapatan)->insertUsing(['supir_id', 'nominal', 'gajikenek', 'trado_id','a.nobuktirincian'], $fetch2);



                $tempkomisi = '##komisi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempkomisi, function ($table) {
                    $table->string('jenis', 500)->nullable();
                    $table->bigInteger('trado_id')->nullable();
                    $table->bigInteger('supir_id')->nullable();
                    $table->string('kode_trado', 500)->nullable();
                    $table->string('namasupir', 500)->nullable();
                    $table->double('komisi')->nullable();
                    // $table->string('nobuktirincian', 500)->nullable();
                });

                if ($formatCetak->text == 'FORMAT 1') {

                    $query = DB::table($tempPendapatan)->from(DB::raw("$tempPendapatan as t1 with (readuncommitted)"))
                        ->select(
                            DB::raw(
                                "'SUPIR' as jenis,t1.supir_id,max(t1.trado_id) as trado_id,max(trado.kodetrado) as kode_trado,isnull(supir.namasupir,'') as namasupir,
                                    SUM(t1.nominal) AS komisi"
                            )
                        )
                        ->leftJoin(DB::raw("trado with (readuncommitted)"), 't1.trado_id', 'trado.id')
                        ->leftJoin(DB::raw("supir with (readuncommitted)"), 't1.supir_id', 'supir.id')
                        ->groupBy('t1.supir_id', 'supir.namasupir');
                } else {

                    $query = DB::table($tempPendapatan)->from(DB::raw("$tempPendapatan as t1 with (readuncommitted)"))
                        ->select(
                            DB::raw(
                                "'SUPIR' as jenis,t1.supir_id,max(t1.trado_id) as trado_id,max(trado.kodetrado) as kode_trado,isnull(supir.namasupir,'') as namasupir,
                                    SUM(t1.gajikenek) AS komisi"
                            )
                        )
                        ->leftJoin(DB::raw("trado with (readuncommitted)"), 't1.trado_id', 'trado.id')
                        ->leftJoin(DB::raw("supir with (readuncommitted)"), 't1.supir_id', 'supir.id')
                        ->groupBy('t1.supir_id', 'supir.namasupir');
                }

                DB::table($tempkomisi)->insertUsing(['jenis', 'supir_id', 'trado_id', 'kode_trado', 'namasupir', 'komisi'], $query);

                $tempkomisi2 = '##komisi2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempkomisi2, function ($table) {
                    $table->string('jenis', 500)->nullable();
                    $table->bigInteger('trado_id')->nullable();
                    $table->bigInteger('supir_id')->nullable();
                    $table->string('kode_trado', 500)->nullable();
                    $table->string('namasupir', 500)->nullable();
                    $table->double('komisi')->nullable();
                    $table->integer('urut')->nullable();
                    $table->double('deposito')->nullable();
                    $table->double('pengembalianpinjaman')->nullable();
                    // $table->string('nobuktirincian', 500)->nullable();
                });

                $query = db::table($tempkomisi)->from(db::raw($tempkomisi . " a"))
                    ->select(
                        'a.jenis',
                        'a.trado_id',
                        'a.supir_id',
                        'a.kode_trado',
                        'a.namasupir',
                        'a.komisi',
                        DB::raw('ROW_NUMBER() OVER (PARTITION BY A.namasupir ORDER BY A.namasupir,a.kode_trado) as urut'),
                        db::raw(" isnull(b.deposito,0)  as deposito"),
                        db::raw(" isnull(b.pengembalianpinjaman,0)  as pengembalianpinjaman"),
                        // 'a.nobuktirincian'
                    )
                    ->leftjoin(db::raw($tempdepo . " b"), 'a.supir_id', 'b.supir_id')
                    ->orderBY('a.namasupir', 'asc')
                    ->orderBY('a.kode_trado', 'asc');

                DB::table($tempkomisi2)->insertUsing(['jenis', 'supir_id', 'trado_id', 'kode_trado', 'namasupir', 'komisi', 'urut', 'deposito', 'pengembalianpinjaman'], $query);


                $query = db::table($tempkomisi2)->from(db::raw($tempkomisi2 . " as a"))
                    ->select(
                        'a.jenis',
                        'a.trado_id',
                        'a.kode_trado',
                        'a.namasupir',
                        'a.komisi',
                        // 'a.nobuktirincian',
                        db::raw("(case when a.urut=1 then isnull(a.deposito,0) else 0 end) as deposito"),
                        db::raw("(case when a.urut=1 then isnull(a.pengembalianpinjaman,0) else 0 end) as pengembalianpinjaman"),
                        db::raw("(isnull(a.komisi,0)-isnull(a.deposito,0)-isnull(a.pengembalianpinjaman,0)) as total"),
                        DB::raw("'BUKTI KOMISI '+a.jenis as judulLaporan"),
                    )
                    ->WHEREraw("a.jenis='SUPIR'")
                    ->orderBY('a.namasupir', 'asc')
                    ->orderBY('a.kode_trado', 'asc');
            }
            return $query->get();
        }
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'dari') {
            return $query->orderBy(DB::raw("isnull(b.kodekota,'')"), $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'sampai') {
            return $query->orderBy(DB::raw("isnull(c.kodekota,'')"), $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tgl_trip') {
            return $query->orderBy(DB::raw("a.tgltrip"), $this->params['sortOrder']);
        } else {
            return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'dari') {
                                $query = $query->where(DB::raw("isnull(b.kodekota,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'sampai') {
                                $query = $query->where(DB::raw("isnull(c.kodekota,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supirdetail') {
                                $query = $query->where(DB::raw("isnull(supir.namasupir,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tradodetail') {
                                $query = $query->where(DB::raw("isnull(trado.kodetrado,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nobukti_trip') {
                                $query = $query->where(DB::raw("isnull(a.nobuktitrip,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal_detail') {
                                $query = $query->whereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'gajikenek') {
                                $query = $query->whereRaw("format(a.gajikenek, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'totaldetail') {
                                $query = $query->whereRaw("format((a.gajikenek + a.nominal), '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgl_trip') {
                                $query = $query->whereRaw("format(a.tgltrip, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgl_ric') {
                                $query = $query->whereRaw("format(a.tgl_ric, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'dari') {
                                $query = $query->orWhere(DB::raw("isnull(b.kodekota,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'sampai') {
                                $query = $query->orWhere(DB::raw("isnull(c.kodekota,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supirdetail') {
                                $query = $query->orWhere(DB::raw("isnull(supir.namasupir,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tradodetail') {
                                $query = $query->orWhere(DB::raw("isnull(trado.kodetrado,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nobukti_trip') {
                                $query = $query->orWhere(DB::raw("isnull(a.nobuktitrip,'')"), 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal_detail') {
                                $query = $query->orWhereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'gajikenek') {
                                $query = $query->orWhereRaw("format(a.gajikenek, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'totaldetail') {
                                $query = $query->orWhereRaw("format((a.gajikenek + a.nominal), '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgl_trip') {
                                $query = $query->OrwhereRaw("format(a.tgltrip, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgl_ric') {
                                $query = $query->OrwhereRaw("format(a.tgl_ric, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });
                    break;
                default:

                    break;
            }


            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(PendapatanSupirHeader $pendapatanSupirHeader, array $data): PendapatanSupirDetail
    {
        $pendapatanSupirDetail = new PendapatanSupirDetail();
        $pendapatanSupirDetail->pendapatansupir_id = $pendapatanSupirHeader->id;
        $pendapatanSupirDetail->nobukti = $pendapatanSupirHeader->nobukti;
        $pendapatanSupirDetail->supir_id = $data['supir_id'];
        $pendapatanSupirDetail->dari_id = $data['dari_id'];
        $pendapatanSupirDetail->sampai_id = $data['sampai_id'];
        $pendapatanSupirDetail->nominal = $data['nominal'];
        $pendapatanSupirDetail->gajikenek = $data['gajikenek'];
        $pendapatanSupirDetail->nobuktirincian = $data['nobuktirincian'];
        $pendapatanSupirDetail->nobuktitrip = $data['nobuktitrip'];
        $pendapatanSupirDetail->modifiedby = $data['modifiedby'];

        if (!$pendapatanSupirDetail->save()) {
            throw new \Exception("Error storing pendapatan Supir detail.");
        }

        return $pendapatanSupirDetail;
    }
}
