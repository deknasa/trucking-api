<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKeteranganPinjamanSupir extends MyModel
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



    public function getReport($periode, $jenis)
    {
        // $sampai = date("Y-m-d", strtotime($sampai));
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

        $penerimaanTrucking = PenerimaanTrucking::where('kodepenerimaan', '=', 'PJP')->first();

        $penerimaantrucking_id = $penerimaanTrucking->id;

        $periode = date("Y-m-d", strtotime($periode));
        $statusposting = $jenis;

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
            ->where('a.tglbukti', '<', $periode)
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

        $pengeluaranTrucking = PengeluaranTrucking::where('kodepengeluaran', '=', 'PJT')->first();

        $pengeluarantrucking_id = $pengeluaranTrucking->id;

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
            ->where('a.tglbukti', '<=', $periode)
            ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
            ->where('a.statusposting', '=', $statusposting);

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
                'a.nobukti',
                DB::raw("max(a.keterangan) as keterangan"),
                DB::raw("sum(a.nominal) as nominal"),

            )
            ->join(DB::raw($pengeluaranTruckingHeader . " as b"), 'a.nobukti', 'b.nobukti')
            ->groupBy('a.nobukti');

        DB::table($pengeluaranTruckingDetail)->insertUsing([
            'nobukti',
            'keterangan',
            'nominal',
        ], $queryPengeluaranTruckingDetail);



        // 2
        $penerimaanTruckingHeader2 = '##penerimaantruckingheader2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($penerimaanTruckingHeader2, function ($table) {
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

        $querypenerimaantruckingheader2 = DB::table("penerimaantruckingheader")->from(
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
            ->where('a.tglbukti', '=', $periode)
            ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id);


        DB::table($penerimaanTruckingHeader2)->insertUsing([
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
        ], $querypenerimaantruckingheader2);

        

        $penerimaanTruckingDetail2 = '##penerimaantruckingdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($penerimaanTruckingDetail2, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('pengeluarantruckingheader_nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
        });

        $queryPenerimaanTruckingDetail2 = DB::table("penerimaantruckingdetail")->from(
            DB::raw("penerimaantruckingdetail as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                'a.pengeluarantruckingheader_nobukti',
                DB::raw("sum(a.nominal) as nominal"),
                DB::raw("max(a.keterangan) as keterangan"),

            )
            ->join(DB::raw($penerimaanTruckingHeader2 . " as b"), 'a.nobukti', 'b.nobukti')
            ->groupBy('a.nobukti', 'a.pengeluarantruckingheader_nobukti');

        DB::table($penerimaanTruckingDetail2)->insertUsing([
            'nobukti',
            'pengeluarantruckingheader_nobukti',
            'nominal',
            'keterangan',
        ], $queryPenerimaanTruckingDetail2);

       

        $tempLaporan = '##tempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempLaporan, function ($table) {
            $table->BigIncrements('id');
            $table->dateTime('tglbuktipinjaman')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('flag')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $queryTempLaporan = DB::table($pengeluaranTruckingHeader)->from(
            DB::raw($pengeluaranTruckingHeader . " as a")
        )
            ->select(
                'a.tglbukti',
                'a.tglbukti',
                'c.nobukti',
                'c.keterangan',
                DB::raw("0 as flag"),
                'c.nominal as debet',
                DB::raw("0 as kredit"),

            )
            ->leftjoin(DB::raw($penerimaanTruckingDetail . " as b "), 'a.nobukti', 'b.pengeluarantruckingheader_nobukti')
            ->join(DB::raw($pengeluaranTruckingDetail . " as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->whereRaw("isnull(B.nobukti,'')=''")
            ->orderBy('a.tglbukti', 'ASC')
            ->orderBy('c.nobukti', 'ASC');


        DB::table($tempLaporan)->insertUsing([
            'tglbuktipinjaman',
            'tglbukti',
            'nobukti',
            'keterangan',
            'flag',
            'debet',
            'kredit',
        ], $queryTempLaporan);

        

        $queryTempLaporanDua = DB::table($penerimaanTruckingHeader2)->from(
            DB::raw($penerimaanTruckingHeader2 . " as a")
        )
            ->select(
                'b.tglbukti',
                'a.tglbukti',
                'c.pengeluarantruckingheader_nobukti as nobukti',
                'c.keterangan',
                DB::raw("1 as flag"),
                DB::raw("0 as debet"),
                'c.nominal as kredit',

            )
            ->join(DB::raw($penerimaanTruckingDetail2 . " as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->join(DB::raw("pengeluarantruckingheader as b"), 'c.pengeluarantruckingheader_nobukti', 'b.nobukti')
            ->where('b.statusposting', '=', $statusposting)
            ->orderBy('a.tglbukti', 'ASC')
            ->orderBy('c.pengeluarantruckingheader_nobukti', 'ASC');

        DB::table($tempLaporan)->insertUsing([
            'tglbuktipinjaman',
            'tglbukti',
            'nobukti',
            'keterangan',
            'flag',
            'debet',
            'kredit',
        ], $queryTempLaporanDua);

        $tempLaporan2 = '##tempLaporan2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempLaporan2, function ($table) {
            $table->BigIncrements('id');
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $queryTempLaporan2 = DB::table($tempLaporan)->from(
            DB::raw($tempLaporan . " as a")
        )
            ->select(
                'a.tglbukti',
                'a.nobukti',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("0 as saldo"),

            )
            ->orderBy('a.tglbuktipinjaman', 'ASC')
            ->orderBy('a.nobukti', 'ASC')
            ->orderBy('a.flag', 'ASC');

        DB::table($tempLaporan2)->insertUsing([
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo'
        ], $queryTempLaporan2);

      
        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $queryRekap = DB::table($tempLaporan2)->from(
            DB::raw($tempLaporan2 . " as a")
        )
            ->select(
                'a.tglbukti as tanggal',
                'a.nobukti',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(A.saldo,0)+A.debet)-A.Kredit) over (order by id asc) as Saldo"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            )
            ->orderBy('a.id');


        $data = $queryRekap->get();

        return $data;
    }
}
