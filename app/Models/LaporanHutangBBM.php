<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanHutangBBM extends MyModel
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



    public function getReport($sampai)
    {
        $sampai = date("Y-m-d", strtotime($sampai));
        // // data coba coba
        // $query = DB::table('penerimaantruckingdetail')->from(
        //     DB::raw("penerimaantruckingdetail with (readuncommitted)")
        // )->select(
        //     'penerimaantruckingdetail.id',
        //     'supir.namasupir',
        //     'penerimaantruckingdetail.nominal',
        // )
        // ->leftJoin(DB::raw("supir with (readuncommitted)"), 'penerimaantruckingdetail.supir_id', 'supir.id')
        // ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.penerimaantruckingheader_id', 'penerimaantruckingheader.id')
        // ->where('penerimaantruckingheader.tglbukti','<=',$sampai);

        // $data = $query->get();
        // return $data;
        $penerimaanTrucking = PenerimaanTrucking::where('kodepenerimaan', '=', 'BBM')->first();

        $penerimaantrucking_id = $penerimaanTrucking->id;

        $pengeluaranTrucking = PengeluaranTrucking::where('kodepengeluaran', '=', 'KBBM')->first();

        $pengeluarantrucking_id = $pengeluaranTrucking->id;

        $penerimaanTruckingHeader = '##penerimaantruckingheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($penerimaanTruckingHeader, function ($table) {
            $table->BigInteger('id');
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->BigInteger('penerimaantrucking_id')->nullable();
            $table->BigInteger('bank_id')->nullable();
            $table->BigInteger('supir_id')->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->BigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->dateTime('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
        });

        $querypenerimaantruckingheader = DB::table("penerimaantruckingheader")->from(
            DB::raw("penerimaantruckingheader as a with (readuncommitted)")
        )

            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.keterangan',
                'a.penerimaantrucking_id',
                'a.bank_id',
                'a.supir_id',
                'a.coa',
                'a.penerimaan_nobukti',
                'a.statusformat',
                'a.statuscetak',
                'a.userbukacetak',
                'a.tglbukacetak',
                'a.jumlahcetak',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at'

            )
            ->where('a.tglbukti', '<=', $sampai)
            ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id);

        DB::table($penerimaanTruckingHeader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'keterangan',
            'penerimaantrucking_id',
            'bank_id',
            'supir_id',
            'coa',
            'penerimaan_nobukti',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $querypenerimaantruckingheader);

        $penerimaanTruckingDetail = '##penerimaantruckingdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($penerimaanTruckingDetail, function ($table) {
            $table->BigInteger('id');
            $table->BigInteger('penerimaantruckingheader_id');
            $table->string('nobukti', 50)->nullable();
            $table->BigInteger('supir_id')->nullable();
            $table->string('pengeluarantruckingheader_nobukti', 50)->nullable();
            $table->string('pengeluaranstokheader_nobukti', 50)->nullable();
            $table->BigInteger('stok_id')->nullable();
            $table->double('qty')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
        });

        $queryPenerimaanTruckingDetail = DB::table("penerimaantruckingdetail")->from(
            DB::raw("penerimaantruckingdetail as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.penerimaantruckingheader_id',
                'a.nobukti',
                'a.supir_id',
                'a.pengeluarantruckingheader_nobukti',
                'a.pengeluaranstokheader_nobukti',
                'a.stok_id',
                'a.qty',
                'a.nominal',
                'a.keterangan',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at'

            )
            ->join(DB::raw($penerimaanTruckingHeader . " as b"), 'a.nobukti', 'b.nobukti');

        DB::table($penerimaanTruckingDetail)->insertUsing([
            'id',
            'penerimaantruckingheader_id',
            'nobukti',
            'supir_id',
            'pengeluarantruckingheader_nobukti',
            'pengeluaranstokheader_nobukti',
            'stok_id',
            'qty',
            'nominal',
            'keterangan',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $queryPenerimaanTruckingDetail);

        $pengeluaranTruckingHeader = '##pengeluaranTruckingHeader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengeluaranTruckingHeader, function ($table) {
            $table->BigInteger('id');
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->BigInteger('pengeluarantrucking_id')->nullable();
            $table->BigInteger('bank_id')->nullable();
            $table->BigInteger('supir_id')->nullable();
            $table->integer('statusposting')->nullable();
            $table->dateTime('periodedari')->nullable();
            $table->dateTime('periodesampai')->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('pengeluaran_nobukti ', 50)->nullable();
            $table->BigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak')->nullable();
            $table->dateTime('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
        });

        $queryPengeluaranTruckingHeader = DB::table("pengeluarantruckingheader")->from(
            DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.keterangan',
                'a.pengeluarantrucking_id',
                'a.bank_id',
                'a.supir_id',
                'a.statusposting',
                'a.periodedari',
                'a.periodesampai',
                'a.coa',
                'a.pengeluaran_nobukti',
                'a.statusformat',
                'a.statuscetak',
                'a.userbukacetak',
                'a.tglbukacetak',
                'a.jumlahcetak',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at'

            )
            ->where('a.tglbukti', '<', $sampai)
            ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id);

        DB::table($pengeluaranTruckingHeader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'keterangan',
            'pengeluarantrucking_id',
            'bank_id',
            'supir_id',
            'statusposting',
            'periodedari',
            'periodesampai',
            'coa',
            'pengeluaran_nobukti',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $queryPengeluaranTruckingHeader);

        $pengeluaranTruckingDetail = '##pengeluaranTruckingDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengeluaranTruckingDetail, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $queryPengeluaranTruckingDetail = DB::table("pengeluarantruckingdetail")->from(
            DB::raw("pengeluarantruckingdetail as a")
        )
            ->select(
                'a.penerimaantruckingheader_nobukti',
                DB::raw("max(a.keterangan) as keterangan"),
                DB::raw("sum(a.nominal) as nominal"),

            )
            ->join(DB::raw($pengeluaranTruckingHeader . " as b"), 'a.nobukti', 'b.nobukti')
            ->groupBy('a.penerimaantruckingheader_nobukti');


        DB::table($pengeluaranTruckingDetail)->insertUsing([
            'nobukti',
            'keterangan',
            'nominal',
        ], $queryPengeluaranTruckingDetail);

        $tempLaporan = '##tempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempLaporan, function ($table) {
            $table->BigIncrements('id');
            $table->dateTime('tglbuktibbm')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('flag')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $queryTempLaporan = DB::table($penerimaanTruckingHeader)->from(
            DB::raw($penerimaanTruckingHeader . " as a")
        )
            ->select(
                'a.tglbukti',
                'a.tglbukti',
                'c.nobukti',
                'c.Keterangan',
                DB::raw("0 as flag"),
                DB::raw("(c.nominal - isnull(B.nominal, 0)) as nominal"),

            )
            ->leftjoin(DB::raw($pengeluaranTruckingDetail . " as b "), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw($penerimaanTruckingDetail . " as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->whereRaw("(c.nominal - isnull(B.nominal, 0)) <> 0")
            ->orderBy('a.tglbukti', 'ASC')
            ->orderBy('c.nobukti', 'ASC');

        DB::table($tempLaporan)->insertUsing([
            'tglbuktibbm',
            'tglbukti',
            'nobukti',
            'keterangan',
            'flag',
            'nominal',

        ], $queryTempLaporan);

        $queryTempLaporan = DB::table("pengeluarantruckingheader")->from(
            DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'a.tglbukti',
                'a.tglbukti',
                'c.nobukti',
                'c.Keterangan',
                DB::raw("0 as flag"),
                DB::raw("(c.nominal*-1) as nominal"),

            )
            ->join(DB::raw("pengeluarantruckingdetail as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->where('a.tglbukti',$sampai)
            ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)            
            ->orderBy('a.tglbukti', 'ASC')
            ->orderBy('c.nobukti', 'ASC');

            // dd($queryTempLaporan->get());

        DB::table($tempLaporan)->insertUsing([
            'tglbuktibbm',
            'tglbukti',
            'nobukti',
            'keterangan',
            'flag',
            'nominal',

        ], $queryTempLaporan);        

        $tempLaporan2 = '##tempLaporan2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempLaporan2, function ($table) {
            $table->BigIncrements('id');
            $table->date('tglbukti')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $queryTempLaporan2 = DB::table($tempLaporan)->from(
            DB::raw($tempLaporan . " as a")
        )
            ->select(
                'a.tglbukti',
                'a.nobukti',
                'a.Keterangan',
                'a.nominal',
                DB::raw("0 as saldo"),

            )
            ->orderBy('a.tglbuktibbm', 'ASC')
            ->orderBy('a.nobukti', 'ASC')
            ->orderBy('a.flag', 'ASC');

        DB::table($tempLaporan2)->insertUsing([
            'tglbukti',
            'nobukti',
            'keterangan',
            'nominal',
            'saldo',

        ], $queryTempLaporan2);

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $result = DB::table($tempLaporan2)->from(
            DB::raw($tempLaporan2 . " as a")
        )
            ->select(
                'a.tglbukti as tanggal',
                DB::raw("ltrim(rtrim(A.keterangan)) + ' - ' + ltrim(rtrim(A.nobukti)) as keterangan"),
                DB::raw("A.nominal * -1 as nominal"),
                DB::raw("sum ((isnull(A.saldo, 0) + A.nominal)) over (
                    order by
                        id asc
                ) * -1 as Saldo"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("'LAPORAN HUTANG BBM' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")

            )
            ->orderBy('a.id');

        $data = $result->get();


        return $data;
    }
}
