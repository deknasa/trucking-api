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



    public function getReport($sampai, $jenis, $prosesneraca)
    {
        $prosesneraca = $prosesneraca ?? 0;
        $penerimaantrucking_id = 3;
        $pengeluarantrucking_id = 2;
        $sampai = date('Y-m-d', strtotime($sampai)) ?? '1900/1/1';
        $jenis = request()->jenis ?? '';

        $temppenerimaantrucking = '##temppenerimaantrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaantrucking, function ($table) {
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('jumlah')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });
        $parameter = new Parameter();
        $statusaktif = $parameter->cekId('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF') ?? 0;
        $statusnonaktif = $parameter->cekId('STATUS AKTIF', 'STATUS AKTIF', 'NON AKTIF') ?? 0;
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
            ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
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
            ->where('a.pengeluarantrucking_id', '=',  $pengeluarantrucking_id)
            ->groupBy('b.supir_id');

        DB::table($temppengeluarantruckinglist)->insertUsing([
            'supir_id',
            'nominal',
        ], $querypengeluarantruckinglist);

        // 

        $temprangedeposito1 = '##temprangedeposito1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprangedeposito1, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->double('nominalawal', 15, 2)->nullable();
            $table->double('nominalakhir', 15, 2)->nullable();
            $table->longtext('keterangan', 1000)->nullable();
            $table->integer('urut')->nullable();
        });

        $queryrangedeposito1 = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                DB::raw("cast(substring([text],1,charindex('-',[text])-1) as money) as nominalawal"),
                DB::raw("cast(substring([text],charindex('-',[text])+1,20) as money) as nominalakhir"),
                DB::raw("'Keterangan Deposito '+format(cast(substring([text],1,charindex('-',[text])-1) as money),'#,#0')+' - '+format(cast(substring([text],charindex('-',[text])+1,20) as money),'#,#')  as keterangan"),
                DB::raw("row_number() Over(Order By a.id desc) as urut"),
            )
            ->where('a.grp', '=', 'RANGE DEPOSITO SUPIR')
            ->where('a.subgrp', '=', 'RANGE DEPOSITO SUPIR')
            ->OrderBy('id', 'asc');

        DB::table($temprangedeposito1)->insertUsing([
            'id',
            'nominalawal',
            'nominalakhir',
            'keterangan',
            'urut',
        ], $queryrangedeposito1);

        $temprangedeposito = '##temprangedeposito' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprangedeposito, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->double('nominalawal', 15, 2)->nullable();
            $table->double('nominalakhir', 15, 2)->nullable();
            $table->longtext('keterangan', 1000)->nullable();
        });

        $queryrangedeposito = DB::table($temprangedeposito1)->from(
            DB::raw($temprangedeposito1 . " as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                DB::raw("a.nominalawal"),
                DB::raw("a.nominalakhir"),
                DB::raw("(case when a.urut=1 then 'Keterangan Deposito Di Atas '+format(a.nominalawal,'#,#0') else a.keterangan end)   as keterangan"),
            )
            ->OrderBy('a.id', 'Asc');

        DB::table($temprangedeposito)->insertUsing([
            'id',
            'nominalawal',
            'nominalakhir',
            'keterangan',
        ], $queryrangedeposito);

        // dd(db::table($temprangedeposito)->OrderBy('id', 'Asc')->get());


        $tempsaldo = '##tempsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldo, function ($table) {
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->string('namasupir', 200)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->double('deposito', 15, 2)->nullable();
            $table->double('penarikan', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('cicil', 15, 2)->nullable();
        });

        // dd(db::table($temppengeluarantruckinglist)->get());
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

        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasil, function ($table) {
            $table->id();
            $table->integer('iddeposito')->nullable();
            $table->integer('supir_id')->nullable();
            $table->string('namasupir', 200)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->double('deposito', 15, 2)->nullable();
            $table->double('penarikan', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('cicil', 15, 2)->nullable();
            $table->longText('keterangan1')->nullable();
            $table->longText('keterangandeposito')->nullable();
            $table->longText('judulLaporan')->nullable();
            $table->longText('judul')->nullable();
            $table->longText('tglcetak')->nullable();
            $table->longText('usercetak')->nullable();
            $table->longText('disetujui')->nullable();
            $table->longText('diperiksa')->nullable();
        });

        $queryhasil = DB::table($tempsaldo)->from(
            DB::raw($tempsaldo . " as a")
        )
            ->select(
                'b.id as iddeposito',
                'a.supir_id',
                'a.namasupir',
                'a.saldo',
                'a.deposito',
                'a.penarikan',
                'a.total',
                'a.keterangan as keterangan1',
                'a.cicil',
                DB::raw("b.keterangan as keterangan"),
                DB::raw("'DEPOSITO SUPIR A/N '+trim(a.namasupir) as keterangandeposito"),
                DB::raw("'Laporan Deposito' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa")
            )
            ->join(DB::raw($temprangedeposito . " as b "), function ($join) {
                $join->on('a.total', '>=', 'b.nominalawal');
                $join->on('a.total', '<=', 'b.nominalakhir');
            })
            ->join(DB::raw("supir c with (readuncommitted)"), 'a.supir_id', 'c.id')
            ->where('c.statusaktif', $statusaktif)

            ->orderBy('b.id', 'asc')
            ->orderBy('a.namasupir', 'asc');

        DB::table($temphasil)->insertUsing([
            'iddeposito',
            'supir_id',
            'namasupir',
            'saldo',
            'deposito',
            'penarikan',
            'total',
            'keterangan1',
            'cicil',
            'keterangan',
            'keterangandeposito',
            'judulLaporan',
            'judul',
            'tglcetak',
            'usercetak',
            'disetujui',
            'diperiksa',
        ], $queryhasil);

        $queryhasil = DB::table($tempsaldo)->from(
            DB::raw($tempsaldo . " as a")
        )
            ->select(
                db::raw("1000 as iddeposito"),
                'a.supir_id',
                'a.namasupir',
                'a.saldo',
                'a.deposito',
                'a.penarikan',
                'a.total',
                'a.keterangan as keterangan1',
                'a.cicil',
                DB::raw("'Keterangan Deposito Supir Non Aktif' as keterangan"),
                DB::raw("'DEPOSITO SUPIR A/N '+trim(a.namasupir) as keterangandeposito"),
                DB::raw("'Laporan Deposito' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa")
            )
            ->join(DB::raw("supir c with (readuncommitted)"), 'a.supir_id', 'c.id')
            ->where('c.statusaktif', $statusnonaktif)

            ->orderBy('a.namasupir', 'asc');

        DB::table($temphasil)->insertUsing([
            'iddeposito',
            'supir_id',
            'namasupir',
            'saldo',
            'deposito',
            'penarikan',
            'total',
            'keterangan1',
            'cicil',
            'keterangan',
            'keterangandeposito',
            'judulLaporan',
            'judul',
            'tglcetak',
            'usercetak',
            'disetujui',
            'diperiksa',
        ], $queryhasil);

        $query = DB::table($temphasil)->from(
            DB::raw($temphasil . " as a")
        )
            ->select(
                'a.iddeposito as id',
                'a.supir_id',
                'a.namasupir',
                'a.saldo',
                'a.deposito',
                'a.penarikan',
                'a.total',
                'a.keterangan',
                'a.cicil',
                'a.keterangan1',
                'a.keterangandeposito',
                'a.judulLaporan',
                'a.judul',
                'a.tglcetak',
                'a.usercetak',
                'a.disetujui',
                'a.diperiksa'
            )
            ->orderby('a.iddeposito','asc');

        //   dd($query->get());
        if ($prosesneraca == 1) {
            $data = $query;
        } else {
            $data = $query->get();
        }

        // dd($data);
        return $data;
    }

    // lama
    public function getReportLama($sampai, $jenis, $prosesneraca)
    {
        // dd('test');
        $prosesneraca = $prosesneraca ?? 0;
        $penerimaantrucking_id = 3;
        $pengeluarantrucking_id = 2;
        $sampai = date('Y-m-d', strtotime($sampai)) ?? '1900/1/1';
        $jenis = request()->jenis ?? '';

        $temppenerimaantrucking = '##temppenerimaantrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaantrucking, function ($table) {
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('jumlah')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });
        $querypenerimaantrucking = DB::table('penerimaantruckinglamaheader')->from(
            DB::raw("penerimaantruckinglamaheader as a with (readuncommitted)")
        )
            ->select(
                'b.supir_id',
                DB::raw("count(b.supir_id) as jumlah"),
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("penerimaantruckinglamadetail b with (readuncommitted)"), 'a.id', 'b.penerimaantruckinglamaheader_id')
            ->where('a.tglbukti', '<', $sampai)
            ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
            ->groupBy('b.supir_id');

        DB::table($temppenerimaantrucking)->insertUsing([
            'supir_id',
            'jumlah',
            'nominal',
        ], $querypenerimaantrucking);

        // dd(db::table($temppenerimaantrucking)->get());
        $temppengeluarantrucking = '##temppengeluarantrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengeluarantrucking, function ($table) {
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypengeluarantrucking = DB::table('pengeluarantruckinglamaheader')->from(
            DB::raw("pengeluarantruckinglamaheader as a with (readuncommitted)")
        )
            ->select(
                'b.supir_id',
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("pengeluarantruckinglamadetail b with (readuncommitted)"), 'a.id', 'b.pengeluarantruckinglamaheader_id')
            ->where('a.tglbukti', '<', $sampai)
            ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
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

        $querypenerimaantruckinglist = DB::table('penerimaantruckinglamaheader')->from(
            DB::raw("penerimaantruckinglamaheader as a with (readuncommitted)")
        )
            ->select(
                'b.supir_id',
                DB::raw("count(b.supir_id) as jumlah"),
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("penerimaantruckinglamadetail b with (readuncommitted)"), 'a.id', 'b.penerimaantruckinglamaheader_id')
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

        $querypengeluarantruckinglist = DB::table('pengeluarantruckinglamaheader')->from(
            DB::raw("pengeluarantruckinglamaheader as a with (readuncommitted)")
        )
            ->select(
                'b.supir_id',
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("pengeluarantruckinglamadetail b with (readuncommitted)"), 'a.id', 'b.pengeluarantruckinglamaheader_id')
            ->where('a.tglbukti', '=', $sampai)
            ->where('a.pengeluarantrucking_id', '=',  $pengeluarantrucking_id)
            ->groupBy('b.supir_id');

        DB::table($temppengeluarantruckinglist)->insertUsing([
            'supir_id',
            'nominal',
        ], $querypengeluarantruckinglist);

        // 

        $temprangedeposito1 = '##temprangedeposito1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprangedeposito1, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->double('nominalawal', 15, 2)->nullable();
            $table->double('nominalakhir', 15, 2)->nullable();
            $table->longtext('keterangan', 1000)->nullable();
            $table->integer('urut')->nullable();
        });

        $queryrangedeposito1 = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                DB::raw("cast(substring([text],1,charindex('-',[text])-1) as money) as nominalawal"),
                DB::raw("cast(substring([text],charindex('-',[text])+1,20) as money) as nominalakhir"),
                DB::raw("'Keterangan Deposito '+format(cast(substring([text],1,charindex('-',[text])-1) as money),'#,#0')+' - '+format(cast(substring([text],charindex('-',[text])+1,20) as money),'#,#')  as keterangan"),
                DB::raw("row_number() Over(Order By a.id desc) as urut"),
            )
            ->where('a.grp', '=', 'RANGE DEPOSITO SUPIR')
            ->where('a.subgrp', '=', 'RANGE DEPOSITO SUPIR')
            ->OrderBy('id', 'asc');

        DB::table($temprangedeposito1)->insertUsing([
            'id',
            'nominalawal',
            'nominalakhir',
            'keterangan',
            'urut',
        ], $queryrangedeposito1);

        $temprangedeposito = '##temprangedeposito' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprangedeposito, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->double('nominalawal', 15, 2)->nullable();
            $table->double('nominalakhir', 15, 2)->nullable();
            $table->longtext('keterangan', 1000)->nullable();
        });

        $queryrangedeposito = DB::table($temprangedeposito1)->from(
            DB::raw($temprangedeposito1 . " as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                DB::raw("a.nominalawal"),
                DB::raw("a.nominalakhir"),
                DB::raw("(case when a.urut=1 then 'Keterangan Deposito Di Atas '+format(a.nominalawal,'#,#0') else a.keterangan end)   as keterangan"),
            )
            ->OrderBy('a.id', 'Asc');

        DB::table($temprangedeposito)->insertUsing([
            'id',
            'nominalawal',
            'nominalakhir',
            'keterangan',
        ], $queryrangedeposito);

        // dd(db::table($temprangedeposito)->OrderBy('id', 'Asc')->get());


        $tempsaldo = '##tempsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldo, function ($table) {
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->string('namasupir', 200)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->double('deposito', 15, 2)->nullable();
            $table->double('penarikan', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('cicil', 15, 2)->nullable();
        });

        // dd(db::table($temppengeluarantruckinglist)->get());
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

        $query = DB::table($tempsaldo)->from(
            DB::raw($tempsaldo . " as a")
        )
            ->select(
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
                DB::raw("'DEPOSITO SUPIR A/N '+trim(a.namasupir) as keterangandeposito"),
                DB::raw("'Laporan Deposito' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
            )
            ->join(DB::raw($temprangedeposito . " as b "), function ($join) {
                $join->on('a.total', '>=', 'b.nominalawal');
                $join->on('a.total', '<=', 'b.nominalakhir');
            })
            ->orderBy('b.id', 'asc')
            ->orderBy('a.namasupir', 'asc');

        //   dd($query->get());
        if ($prosesneraca == 1) {
            $data = $query;
        } else {
            $data = $query->get();
        }

        return $data;
    }
}
