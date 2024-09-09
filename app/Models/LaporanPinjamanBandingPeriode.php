<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPinjamanBandingPeriode extends Model
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

    public function getReport($periode, $jenis, $prosesneraca)
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
        $prosesneraca = $prosesneraca ?? 0;


        $penerimaanTrucking = PenerimaanTrucking::where('kodepenerimaan', '=', 'PJP')->first();

        $penerimaantrucking_id = $penerimaanTrucking->id;

        $penerimaanTruckingkaryawan = PenerimaanTrucking::where('kodepenerimaan', '=', 'PJPK')->first();
        $penerimaantruckingkaryawan_id = $penerimaanTruckingkaryawan->id;


        $periode = date("Y-m-d", strtotime($periode));
        $statusposting = $jenis;
        $parameter = new Parameter();
        $idstatusposting = $parameter->cekId('STATUS POSTING', 'STATUS POSTING', 'POSTING') ?? 0;
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

        // if ((($idstatusposting==$statusposting) || ($statusposting==0)) && ($prosesneraca != 1)) {
        //     $querypenerimaantruckingheader = DB::table("penerimaantruckingheader")->from(
        //         DB::raw("penerimaantruckingheader as a with (readuncommitted)")
        //     )

        //         ->select(
        //             'a.id',
        //             'a.nobukti',
        //             'a.tglbukti',
        //             'a.keterangan',
        //             'a.penerimaantrucking_id',
        //             'a.bank_id',
        //             'a.supir_id',
        //             'a.coa',
        //             'a.penerimaan_nobukti',
        //             'a.statusformat',
        //             'a.statuscetak',
        //             'a.userbukacetak',
        //             'a.tglbukacetak',
        //             'a.jumlahcetak',
        //             'a.modifiedby',
        //             'a.created_at',
        //             'a.updated_at'

        //         )
        //         ->where('a.tglbukti', '<', $periode)
        //         ->where('a.penerimaantrucking_id', '=', $penerimaantruckingkaryawan_id);

        //         DB::table($penerimaanTruckingHeader)->insertUsing([
        //             'id',
        //             'nobukti',
        //             'tglbukti',
        //             'keterangan',
        //             'penerimaantrucking_id',
        //             'bank_id',
        //             'supir_id',
        //             'coa',
        //             'penerimaan_nobukti',
        //             'statusformat',
        //             'statuscetak',
        //             'userbukacetak',
        //             'tglbukacetak',
        //             'jumlahcetak',
        //             'modifiedby',
        //             'created_at',
        //             'updated_at',
        //         ], $querypenerimaantruckingheader);
        // }




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

        $penerimaanTruckingDetailrekap = '##penerimaanTruckingDetailrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($penerimaanTruckingDetailrekap, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('supir_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypenerimaanTruckingDetail = DB::table("penerimaantruckingdetail")->from(
            DB::raw("penerimaantruckingdetail as a")
        )
            ->select(
                'a.pengeluarantruckingheader_nobukti as nobukti',
                DB::raw("max(a.keterangan) as keterangan"),
                DB::raw("sum(a.nominal) as nominal"),
                DB::raw("max(a.supir_id) as supir_id"),

            )
            ->join(DB::raw($penerimaanTruckingHeader . " as b"), 'a.nobukti', 'b.nobukti')
            ->groupBy('a.pengeluarantruckingheader_nobukti');


        DB::table($penerimaanTruckingDetailrekap)->insertUsing([
            'nobukti',
            'keterangan',
            'nominal',
            'supir_id',
        ], $querypenerimaanTruckingDetail);

        // dd(db::table($penerimaanTruckingDetailrekap)->get());

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
            $table->string('posting', 500)->nullable();
        });

        $pengeluaranTrucking = PengeluaranTrucking::where('kodepengeluaran', '=', 'PJT')->first();

        $pengeluarantrucking_id = $pengeluaranTrucking->id;


        $pengeluaranTruckingkaryawan = PengeluaranTrucking::where('kodepengeluaran', '=', 'PJK')->first();
        $pengeluarantruckingkaryawan_id = $pengeluaranTruckingkaryawan->id;

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
                'a.updated_at',
                db::raw("(case when " . $statusposting . "=0 then ' ( '+isnull(e.[text],'')+' ) '  else '' end) as posting"),


            )
            ->leftjoin(db::raw('parameter e with (readuncommitted)'), 'a.statusposting', 'e.id')
            ->where('a.tglbukti', '<=', $periode)
            ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
            // ->where('a.statusposting', '=', $statusposting);
            ->whereRaw("(a.statusposting=" . $statusposting . " or " . $statusposting . "=0)");

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
            'posting',
        ], $queryPengeluaranTruckingHeader);

        // if ((($idstatusposting==$statusposting) || ($statusposting==0)) && ($prosesneraca != 1)) {
        //     $queryPengeluaranTruckingHeader = DB::table("pengeluarantruckingheader")->from(
        //         DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
        //     )
        //         ->select(
        //             'a.id',
        //             'a.nobukti',
        //             'a.tglbukti',
        //             'a.keterangan',
        //             'a.pengeluarantrucking_id',
        //             'a.bank_id',
        //             'a.supir_id',
        //             'a.statusposting',
        //             'a.periodedari',
        //             'a.periodesampai',
        //             'a.coa',
        //             'a.pengeluaran_nobukti',
        //             'a.statusformat',
        //             'a.statuscetak',
        //             'a.userbukacetak',
        //             'a.tglbukacetak',
        //             'a.jumlahcetak',
        //             'a.modifiedby',
        //             'a.created_at',
        //             'a.updated_at',
        //             db::raw("(case when " . $statusposting . "=0 then ' ( '+isnull(e.[text],'')+' ) '  else '' end) as posting"),


        //         )
        //         ->leftjoin(db::raw('parameter e with (readuncommitted)'), 'a.statusposting', 'e.id')
        //         ->where('a.tglbukti', '<=', $periode)
        //         ->where('a.pengeluarantrucking_id', '=', $pengeluarantruckingkaryawan_id)
        //         // ->where('a.statusposting', '=', $statusposting);
        //         ->whereRaw("(a.statusposting=" . $statusposting . " or " . $statusposting . "=0)");

        //     DB::table($pengeluaranTruckingHeader)->insertUsing([
        //         'id',
        //         'nobukti',
        //         'tglbukti',
        //         'keterangan',
        //         'pengeluarantrucking_id',
        //         'bank_id',
        //         'supir_id',
        //         'statusposting',
        //         'periodedari',
        //         'periodesampai',
        //         'coa',
        //         'pengeluaran_nobukti',
        //         'statusformat',
        //         'statuscetak',
        //         'userbukacetak',
        //         'tglbukacetak',
        //         'jumlahcetak',
        //         'modifiedby',
        //         'created_at',
        //         'updated_at',
        //         'posting',
        //     ], $queryPengeluaranTruckingHeader);
        // }

        $pengeluaranTruckingDetail = '##pengeluaranTruckingDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengeluaranTruckingDetail, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('supir_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $queryPengeluaranTruckingDetail = DB::table("pengeluarantruckingdetail")->from(
            DB::raw("pengeluarantruckingdetail as a")
        )
            ->select(
                'a.nobukti',
                DB::raw("max(a.keterangan) as keterangan"),
                DB::raw("sum(a.nominal) as nominal"),
                DB::raw("max(a.supir_id) as supir_id"),

            )
            ->join(DB::raw($pengeluaranTruckingHeader . " as b"), 'a.nobukti', 'b.nobukti')
            ->groupBy('a.nobukti');


        DB::table($pengeluaranTruckingDetail)->insertUsing([
            'nobukti',
            'keterangan',
            'nominal',
            'supir_id',
        ], $queryPengeluaranTruckingDetail);

        // dd(db::table($pengeluaranTruckingDetail)->get());



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
            ->whereRaw("a.tglbukti='" . $periode . "'")
            ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id);

        // dump($periode);
        // dd($penerimaantrucking_id);
        // dd($querypenerimaantruckingheader2->toSql());


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

        // if ((($idstatusposting==$statusposting) || ($statusposting==0)) && ($prosesneraca != 1)) {
        //     $querypenerimaantruckingheader2 = DB::table("penerimaantruckingheader")->from(
        //         DB::raw("penerimaantruckingheader as a with (readuncommitted)")
        //     )
        //         ->select(
        //             'a.id',
        //             'a.nobukti',
        //             'a.tglbukti',
        //             'a.keterangan',
        //             'a.penerimaantrucking_id',
        //             'a.bank_id',
        //             'a.supir_id',
        //             'a.coa',
        //             'a.penerimaan_nobukti',
        //             'a.statusformat',
        //             'a.statuscetak',
        //             'a.userbukacetak',
        //             'a.tglbukacetak',
        //             'a.jumlahcetak',
        //             'a.modifiedby',
        //             'a.created_at',
        //             'a.updated_at'

        //         )
        //         ->whereRaw("a.tglbukti='" . $periode . "'")
        //         ->where('a.penerimaantrucking_id', '=', $penerimaantruckingkaryawan_id);



        //     DB::table($penerimaanTruckingHeader2)->insertUsing([
        //         'id',
        //         'nobukti',
        //         'tglbukti',
        //         'keterangan',
        //         'penerimaantrucking_id',
        //         'bank_id',
        //         'supir_id',
        //         'coa',
        //         'penerimaan_nobukti',
        //         'statusformat',
        //         'statuscetak',
        //         'userbukacetak',
        //         'tglbukacetak',
        //         'jumlahcetak',
        //         'modifiedby',
        //         'created_at',
        //         'updated_at',
        //     ], $querypenerimaantruckingheader2);    
        // }



        $penerimaanTruckingDetail2 = '##penerimaantruckingdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($penerimaanTruckingDetail2, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('pengeluarantruckingheader_nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('supir_id')->nullable();
        });

        $queryPenerimaanTruckingDetail2 = DB::table("penerimaantruckingdetail")->from(
            DB::raw("penerimaantruckingdetail as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                'a.pengeluarantruckingheader_nobukti',
                DB::raw("sum(a.nominal) as nominal"),
                DB::raw("max(a.keterangan) as keterangan"),
                DB::raw("max(a.supir_id) as supir_id"),

            )
            ->join(DB::raw($penerimaanTruckingHeader2 . " as b"), 'a.nobukti', 'b.nobukti')
            ->groupBy('a.nobukti', 'a.pengeluarantruckingheader_nobukti');

        DB::table($penerimaanTruckingDetail2)->insertUsing([
            'nobukti',
            'pengeluarantruckingheader_nobukti',
            'nominal',
            'keterangan',
            'supir_id',
        ], $queryPenerimaanTruckingDetail2);



        $tempLaporan = '##tempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempLaporan, function ($table) {
            $table->BigIncrements('id');
            $table->dateTime('tglbuktipinjaman')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('nobuktipelunasan', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('flag')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->string('namasupir', 1000)->nullable();
        });

        $queryTempLaporan = DB::table($pengeluaranTruckingHeader)->from(
            DB::raw($pengeluaranTruckingHeader . " as a")
        )
            ->select(
                // 'b.nobukti as nobukti2',
                'a.tglbukti',
                'a.tglbukti',
                'c.nobukti',
                'c.nobukti as nobuktipelunasan',
                db::raw("trim(c.keterangan)+isnull(a.posting,'') as keterangan"),
                DB::raw("0 as flag"),
                db::raw("(isnull(c.nominal,0)-isnull(b.nominal,0)) as debet"),
                DB::raw("0 as kredit"),
                db::raw("isnull(d.namasupir,'') as namasupir")

            )
            ->leftjoin(DB::raw($penerimaanTruckingDetailrekap . " as b "), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw($pengeluaranTruckingDetail . " as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->leftjoin(DB::raw("supir as d with (readuncommitted) "), 'c.supir_id', 'd.id')
            // ->whereRaw("isnull(B.nobukti,'')=''")
            ->orderBy('d.namasupir', 'ASC')
            ->orderBy('a.tglbukti', 'ASC')
            ->orderBy('c.nobukti', 'ASC');

        // dd($queryTempLaporan->get());
        DB::table($tempLaporan)->insertUsing([
            'tglbuktipinjaman',
            'tglbukti',
            'nobukti',
            'nobuktipelunasan',
            'keterangan',
            'flag',
            'debet',
            'kredit',
            'namasupir',
        ], $queryTempLaporan);

        DB::delete(DB::raw("delete " . $tempLaporan . " from " . $tempLaporan . " as a WHERE isnull(a.debet,0)=0"));


        // dd(db::table($penerimaanTruckingHeader2)->get());

        $queryTempLaporanDua = DB::table($penerimaanTruckingHeader2)->from(
            DB::raw($penerimaanTruckingHeader2 . " as a")
        )
            ->select(
                'b.tglbukti',
                'a.tglbukti',
                'c.pengeluarantruckingheader_nobukti as nobukti',
                'a.penerimaan_nobukti as nobuktipelunasan',
                db::raw("(case when " . $statusposting . "=0 then trim(c.keterangan)+ ' ( '+isnull(e.[text],'')+' ) '  else c.keterangan end) as keterangan"),
                DB::raw("1 as flag"),
                DB::raw("0 as debet"),
                'c.nominal as kredit',
                db::raw("isnull(d.namasupir,'') as namasupir")

            )
            ->join(DB::raw($penerimaanTruckingDetail2 . " as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->join(DB::raw("pengeluarantruckingheader as b"), 'c.pengeluarantruckingheader_nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("supir as d with (readuncommitted) "), 'c.supir_id', 'd.id')
            ->leftjoin(db::raw('parameter e with (readuncommitted)'), 'b.statusposting', 'e.id')
            // ->where('b.statusposting', '=', $statusposting)
            ->whereRaw("(b.statusposting=" . $statusposting . " or " . $statusposting . "=0)")

            ->orderBy('d.namasupir', 'ASC')
            ->orderBy('a.tglbukti', 'ASC')
            ->orderBy('c.pengeluarantruckingheader_nobukti', 'ASC');

        // dd($queryTempLaporanDua->get());


        DB::table($tempLaporan)->insertUsing([
            'tglbuktipinjaman',
            'tglbukti',
            'nobukti',
            'nobuktipelunasan',
            'keterangan',
            'flag',
            'debet',
            'kredit',
            'namasupir'
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
            $table->string('namasupir', 1000)->nullable();
        });

        $queryTempLaporan2 = DB::table($tempLaporan)->from(
            DB::raw($tempLaporan . " as a")
        )
            ->select(
                'a.tglbukti',
                db::raw("(case when isnull(a.nobuktipelunasan,'')='' then a.nobukti else a.nobuktipelunasan end) as nobukti"),
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("0 as saldo"),
                'a.namasupir',

            )
            ->orderBy('a.namasupir', 'ASC')
            ->orderBy('a.flag', 'ASC')
            ->orderBy('a.tglbuktipinjaman', 'ASC')
            ->orderBy('a.nobukti', 'ASC');



        DB::table($tempLaporan2)->insertUsing([
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'namasupir'
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
        $parameter = new Parameter();
        if ($statusposting == 0) {
            $judul1 = ' ( SEMUA )';
        } else {
            $judul1 = ' ( ' . $parameter->cekdataText($statusposting) . ' ) ' ?? '';
        }
        $queryRekap = DB::table($tempLaporan2)->from(
            DB::raw($tempLaporan2 . " as a")
        )
            ->select(
                db::raw("cast(a.tglbukti as date) as tanggal"),
                'a.nobukti',
                'a.namasupir',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(A.saldo,0)+A.debet)-A.Kredit) over (order by id asc) as Saldo"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("upper('Laporan Keterangan Pinjaman Supir')+'" . $judul1 . "' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
            )
            ->orderBy('a.id');


        if ($prosesneraca == 1) {
            $data = $queryRekap;
            // dd($data->get());
        } else {
            $data = $queryRekap->get();
        }
        //  dd($data);
        return $data;
    }
}
