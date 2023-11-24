<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanDepositoKaryawan extends MyModel
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
        $penerimaantrucking_id = 6;
        $pengeluarantrucking_id = 16;
        $sampai = date('Y-m-d', strtotime($sampai)) ?? '1900/1/1';
        $jenis = request()->jenis ?? '';

        $temppenerimaantrucking = '##temppenerimaantrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaantrucking, function ($table) {
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->unsignedBigInteger('jumlah')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });
        $querypenerimaantrucking = DB::table('penerimaantruckingheader')->from(
            DB::raw("penerimaantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'b.karyawan_id',
                DB::raw("count(b.karyawan_id) as jumlah"),
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("penerimaantruckingdetail b with (readuncommitted)"), 'a.id', 'b.penerimaantruckingheader_id')
            ->where('a.tglbukti', '<', $sampai)
            ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
            ->groupBy('b.karyawan_id');

        DB::table($temppenerimaantrucking)->insertUsing([
            'karyawan_id',
            'jumlah',
            'nominal',
        ], $querypenerimaantrucking);

        $temppengeluarantrucking = '##temppengeluarantrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengeluarantrucking, function ($table) {
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypengeluarantrucking = DB::table('pengeluarantruckingheader')->from(
            DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'b.karyawan_id',
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("pengeluarantruckingdetail b with (readuncommitted)"), 'a.id', 'b.pengeluarantruckingheader_id')
            ->where('a.tglbukti', '<', $sampai)
            ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
            ->groupBy('b.karyawan_id');

        DB::table($temppengeluarantrucking)->insertUsing([
            'karyawan_id',
            'nominal',
        ], $querypengeluarantrucking);

        // 
        $temppenerimaantruckinglist = '##temppenerimaantruckinglist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaantruckinglist, function ($table) {
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->unsignedBigInteger('jumlah')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypenerimaantruckinglist = DB::table('penerimaantruckingheader')->from(
            DB::raw("penerimaantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'b.karyawan_id',
                DB::raw("count(b.karyawan_id) as jumlah"),
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("penerimaantruckingdetail b with (readuncommitted)"), 'a.id', 'b.penerimaantruckingheader_id')
            ->where('a.tglbukti', '=', $sampai)
            ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
            ->groupBy('b.karyawan_id');

        DB::table($temppenerimaantruckinglist)->insertUsing([
            'karyawan_id',
            'jumlah',
            'nominal',
        ], $querypenerimaantruckinglist);

        $temppengeluarantruckinglist = '##temppengeluarantruckinglist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengeluarantruckinglist, function ($table) {
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypengeluarantruckinglist = DB::table('pengeluarantruckingheader')->from(
            DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'b.karyawan_id',
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("pengeluarantruckingdetail b with (readuncommitted)"), 'a.id', 'b.pengeluarantruckingheader_id')
            ->where('a.tglbukti', '=', $sampai)
            ->where('a.pengeluarantrucking_id', '=',  $penerimaantrucking_id)
            ->groupBy('b.karyawan_id');

        DB::table($temppengeluarantruckinglist)->insertUsing([
            'karyawan_id',
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
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->string('namakaryawan', 200)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->double('deposito', 15, 2)->nullable();
            $table->double('penarikan', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('cicil', 15, 2)->nullable();
        });

        $querysaldo = DB::table('karyawan')->from(
            DB::raw("karyawan as c with (readuncommitted)")
        )
            ->select(
                'c.id as karyawan_id',
                'c.namakaryawan as namakaryawan',
                DB::raw("(isnull(b.nominal,0)-isnull(a.nominal,0)) as saldo"),
                DB::raw("isnull(b1.nominal,0) as deposito"),
                DB::raw("isnull(a1.nominal,0) as penarikan"),
                DB::raw("((isnull(b.nominal,0)-isnull(a.nominal,0))+isnull(b1.nominal,0))-isnull(a1.nominal,0) as total"),
                DB::raw("'DEPOSITO KARYAWAN A/N '+ltrim(rtrim(c.namakaryawan)) as keterangan"),
                DB::raw("(isnull(b.jumlah,0)+isnull(b1.jumlah,0))  as cicil"),

            )
            ->leftjoin(DB::raw($temppenerimaantrucking . "  as b "), 'c.id', 'b.karyawan_id')
            ->leftjoin(DB::raw($temppengeluarantrucking . "  as a "), 'c.id', 'a.karyawan_id')
            ->leftjoin(DB::raw($temppenerimaantruckinglist . "  as b1 "), 'c.id', 'b1.karyawan_id')
            ->leftjoin(DB::raw($temppengeluarantruckinglist . "  as a1 "), 'c.id', 'a1.karyawan_id')
            ->whereRaw(DB::raw("(isnull(b.nominal,0)-isnull(a.nominal,0))<>0 or isnull(b1.nominal,0)<>0 or isnull(a1.nominal,0)<>0"))
            ->orderBy('c.id', 'Asc');

        DB::table($tempsaldo)->insertUsing([
            'karyawan_id',
            'namakaryawan',
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
                'a.karyawan_id',
                'a.namakaryawan',
                'a.saldo',
                'a.deposito',
                'a.penarikan',
                'a.total',
                'a.keterangan',
                'a.cicil',
                DB::raw("b.keterangan as keterangan"),
                DB::raw("'DEPOSITO KARYAWAN A/N '+trim(a.namakaryawan) as keterangandeposito"),
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
            ->orderBy('a.namakaryawan', 'asc');

    //   dd($query->get());
        if ($prosesneraca == 1) {
            $data = $query;
        } else {
            $data = $query->get();
        }

        return $data;
    }

}
