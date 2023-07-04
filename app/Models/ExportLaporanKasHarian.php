<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportLaporanKasHarian extends MyModel
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

    public function getExport($sampai, $jenis)
    {


        $bulan = substr($sampai, 0, 2);
        $tahun = substr($sampai, -4);

        $tgl = $tahun . '-' . $bulan . '-02';
        $tgl1 = $tahun . '-' . $bulan . '-02';

        $tgl3 = date('Y-m-d', strtotime($tgl1 . ' +33 days'));



        $tahun2 = date('Y', strtotime($tgl3));
        $bulan2 = date('m', strtotime($tgl3));

        $tanggal = $tahun . '-' . $bulan . '-01';

        $tgl2 = $tahun2 . '-' . $bulan2 . '-1';
        $tgl2 = date('Y-m-d', strtotime($tgl2 . ' -1 day'));


        $querySaldoAwal = DB::table("saldoawalbank")->from(
            DB::raw("saldoawalbank")
        )
            ->select(
                DB::Raw('isnull(sum(isnull(nominaldebet,0)-isnull(nominalkredit,0)),0) as saldoawal'),
            )
            ->whereRaw("right(bulan,4)+left(bulan,2)<right($tahun,4)+left($bulan,2)")
            ->where('bank_id', $jenis)->first();


        $saldoAwal = $querySaldoAwal->saldoawal;


        $tempList = '##tempList' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempList, function ($table) {
            $table->integer('jenis')->nullable();
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });



        $tempList2 = '##tempList2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempList2, function ($table) {
            $table->integer('jenis')->nullable();
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        DB::table($tempList)->insert([
            'jenis' => 1,
            'tgl' => date('Y-m-d', strtotime($tanggal)),
            'nobukti' => '',
            'coa' => '',
            'perkiraan' => '',
            'keterangan' => 'SALDO AWAL',
            'debet' => 0,
            'kredit' => 0,
            'saldo' => $saldoAwal
        ]);


        while ($tgl1 <= $tgl2) {
            DB::table($tempList)->insert([
                'jenis' => 1,
                'tgl' => date('Y-m-d', strtotime($tgl1)),
                'nobukti' => '',
                'coa' => '',
                'perkiraan' => '',
                'keterangan' => 'SALDO AWAL',
                'debet' => 0,
                'kredit' => 0,
                'saldo' => 0
            ]);

            $tgl1 = date('Y-m-d', strtotime($tgl1 . ' +1 day'));
        }

        $queryTempList = DB::table('penerimaandetail')->from(
            DB::raw('penerimaandetail as a')
        )
            ->select(
                'a.coakredit as coa',
                DB::raw("2 as jenis"),
                'a.tgljatuhtempo',
                'a.nobukti',
                DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                'a.keterangan',
                'nominal as debet',
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo"),

            )
            ->join(DB::raw("penerimaanheader as b "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("akunpusat as c "), 'a.coakredit', 'c.coa')
            ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
            ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
            ->where('b.bank_id', '=', $jenis);

        DB::table($tempList)->insertUsing([
            'coa',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo'
        ], $queryTempList);


        $queryTempPindahBuku = DB::table('pindahbuku')->from(
            DB::raw('pindahbuku as a')
        )
            ->select(
                'a.coadebet as coa',
                DB::raw("3 as jenis"),
                'a.tgljatuhtempo',
                'a.nobukti',
                DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                'a.keterangan',
                'nominal as debet',
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo"),

            )
            ->leftjoin(DB::raw("akunpusat as c "), 'a.coadebet', 'c.coa')
            ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
            ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
            ->where('a.bankke_id', '=', $jenis);

        DB::table($tempList)->insertUsing([
            'coa',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo'
        ], $queryTempPindahBuku);

        $queryTempPengeluaran = DB::table('pengeluarandetail')->from(
            DB::raw('pengeluarandetail as a')
        )
            ->select(
                'a.coadebet as coa',
                DB::raw("4 as jenis"),
                'a.tgljatuhtempo',
                'a.nobukti',
                DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                'a.keterangan',
                DB::raw("0 as debet"),
                DB::raw("nominal as kredit"),
                DB::raw("0 as saldo"),
            )
            ->join(DB::raw("pengeluaranheader as b "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("akunpusat as c "), 'a.coadebet', 'c.coa')
            ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
            ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
            ->where('b.bank_id', '=', $jenis);

        DB::table($tempList)->insertUsing([
            'coa',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo'
        ], $queryTempPengeluaran);

        $queryTempPindahBukuDua = DB::table('pindahbuku')->from(
            DB::raw('pindahbuku as a')
        )
            ->select(
                'a.coakredit as coa',
                DB::raw("5 as jenis"),
                'a.tgljatuhtempo',
                'a.nobukti',
                DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                'a.keterangan',
                DB::raw("0 as debet"),
                DB::raw("nominal as kredit"),
                DB::raw("0 as saldo"),
            )
            ->leftjoin(DB::raw("akunpusat as c "), 'a.coakredit', 'c.coa')
            ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
            ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
            ->where('a.bankdari_id', '=', $jenis);

        DB::table($tempList)->insertUsing([
            'coa',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo'
        ], $queryTempPindahBukuDua);

        $queryTempList2 = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                'jenis',
                'coa',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
            );


        DB::table($tempList2)->insertUsing([
            'jenis',
            'coa',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $queryTempList2);

        DB::table($tempList2)
            ->where("keterangan", "=", "SALDO AWAL")
            ->where('tgl', '>=', $tgl)
            ->delete();


        $tempListRekap = '##tempListRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempListRekap, function ($table) {
            $table->bigIncrements('id');
            $table->string('jenislaporan', 100);
            $table->integer('jenis');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $queryTempListRekap = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                DB::raw("'LAPORAN HARIAN' AS  jenislaporan"),
                'jenis',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo'

            )
            ->orderBy('tgl', 'ASC')
            ->orderBy('jenis', 'ASC')
            ->orderBy('nobukti', 'ASC');

        DB::table($tempListRekap)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $queryTempListRekap);

        $tempLaporan = '##tempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempLaporan, function ($table) {
            $table->string('jenislaporan', 100);
            $table->integer('jenis');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->integer('id')->nullable();
        });

        $queryTempLaporan = DB::table($tempListRekap)->from(
            DB::raw($tempListRekap . ' as a')
        )
            ->select(
                DB::raw("'LAPORAN HARIAN' AS  jenislaporan"),
                'a.jenis',
                'a.tgl as tglbukti',
                'a.nobukti',
                'a.perkiraan',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(A.saldo,0)+A.debet)-A.Kredit) over (order by id asc) as Saldo"),
                'a.id'

            )
            ->where('a.jenislaporan', 'LAPORAN HARIAN')
            ->orderBy('a.id', 'ASC');


        DB::table($tempLaporan)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'id'
        ], $queryTempLaporan);

        DB::table($tempList)
            ->where("keterangan", "=", "SALDO AWAL")
            ->where('tgl', '>=', $tgl)
            ->delete();

        $tempRekap = '##tempRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempRekap, function ($table) {
            $table->bigIncrements('id');
            $table->string('jenislaporan', 100);
            $table->integer('jenis');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $queryTempRekap = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                DB::raw("'LAPORAN REKAP' AS  jenislaporan"),
                'jenis',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo'

            )
            ->orderBy('tgl', 'ASC')
            ->orderBy('jenis', 'ASC')
            ->orderBy('nobukti', 'ASC');

        DB::table($tempRekap)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $queryTempRekap);

        $queryTempLaporanRekap = DB::table($tempRekap)->from(
            DB::raw($tempRekap . ' as a')
        )
            ->select(
                DB::raw("'LAPORAN REKAP' AS  jenislaporan"),
                'a.jenis',
                'a.tgl as tglbukti',
                'a.nobukti',
                'a.perkiraan',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(A.saldo,0)+A.debet)-A.Kredit) over (order by id asc) as Saldo"),
                'a.id'

            )
            ->where('a.jenislaporan', 'LAPORAN REKAP')
            ->orderBy('a.id', 'ASC');

        DB::table($tempLaporan)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'id'
        ], $queryTempLaporanRekap);

        $querySaloAwalRekap01 = DB::table($tempList)->from(
            DB::raw($tempList . ' as a')
        )
            ->select(
                DB::raw("SUM(isnull(saldo,0)+isnull(debet,0)) as saldoawalrekap01"),
            )
            ->where('jenis', '<=', 3)
            ->first();

        $saldoAwalRekap01 = $querySaloAwalRekap01->saldoawalrekap01;

        DB::table($tempList)
            ->where("jenis", "<=", 3)
            ->delete();

        DB::table($tempList)->insert([
            'jenis' => 1,
            'tgl' => date('Y-m-d', strtotime($tanggal)),
            'nobukti' => '',
            'perkiraan' => '',
            'keterangan' => 'SALDO AWAL',
            'debet' => 0,
            'kredit' => 0,
            'saldo' => $saldoAwalRekap01
        ]);

        // dd(DB::table($tempList)->select("*")->get());

        $tempRekap01 = '##tempRekap01' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempRekap01, function ($table) {
            $table->bigIncrements('id');
            $table->string('jenislaporan', 100);
            $table->integer('jenis');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $queryLaporanRekap01 = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                DB::raw("'LAPORAN REKAP 01' AS  jenislaporan"),
                'jenis',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo'
            )
            ->orderBy('tgl', 'ASC')
            ->orderBy('jenis', 'ASC')
            ->orderBy('nobukti', 'ASC');

        DB::table($tempRekap01)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $queryLaporanRekap01);


        $queryLaporanRekap01Dua = DB::table($tempRekap01)->from(
            DB::raw($tempRekap01 . " as a")
        )
            ->select(
                DB::raw("'LAPORAN REKAP 01' AS  jenislaporan"),
                'a.jenis',
                'a.tgl as tglbukti',
                'a.nobukti',
                'a.perkiraan',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(A.saldo,0)+A.debet)-A.Kredit) over (order by id asc) as Saldo"),
                'a.id'
            )
            ->where('a.jenislaporan', '=', 'LAPORAN REKAP 01')
            ->orderBy('a.id', 'ASC');


        DB::table($tempLaporan)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'id'
        ], $queryLaporanRekap01Dua);

        $getData = DB::table($tempLaporan)->select("*")
            ->orderBy('jenislaporan', 'asc')
            ->orderBy('id', 'asc')
            ->get();


        $tempRekapPerkiraan = '##tempRekapPerkiraan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempRekapPerkiraan, function ($table) {
            $table->string('coa', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
        });

        $queryRekapPerkiraan = DB::table($tempList2)->from(
            DB::raw($tempList2 . " as a")
        )
            ->select(
                'coa',
                'perkiraan',
            )
            ->groupBy('coa')
            ->groupBy('perkiraan');


        DB::table($tempRekapPerkiraan)->insertUsing([
            'coa',
            'perkiraan',
        ], $queryRekapPerkiraan);


        $tempRekapDebet = '##tempRekapDebet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempRekapDebet, function ($table) {
            $table->string('coa', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $tempRekapKredit = '##tempRekapKredit' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempRekapKredit, function ($table) {
            $table->string('coa', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });


        $queryRekapDebet = DB::table($tempList2)->from(
            DB::raw($tempList2)
        )
            ->select(
                'coa',
                DB::raw("max((case when perkiraan='' then 'SALDO AWAL' else perkiraan end)) as perkiraan"),
                DB::raw("sum(debet+saldo) as nominaldebet")
            )
            ->whereRaw('jenis in(1,2,3)')
            ->groupBy('coa');


        DB::table($tempRekapDebet)->insertUsing([
            'coa',
            'perkiraan',
            'nominal'
        ], $queryRekapDebet);

        $queryRekapKredit = DB::table($tempList2)->from(
            DB::raw($tempList2)
        )
            ->select(
                'coa',
                DB::raw("max((case when perkiraan='' then 'SALDO AWAL' else perkiraan end)) as perkiraan"),
                DB::raw("sum(kredit) as nominalkredit")
            )
            ->whereRaw('jenis in(1,2,3)')
            ->groupBy('coa');

        DB::table($tempRekapKredit)->insertUsing([
            'coa',
            'perkiraan',
            'nominal'
        ], $queryRekapKredit);

        $queryRekapKredit = DB::table($tempList2)->from(
            DB::raw($tempList2)
        )
            ->select(
                'coa',
                DB::raw("max((case when perkiraan='' then 'SALDO AWAL' else perkiraan end)) as perkiraan"),
                DB::raw("sum(kredit) as nominalkredit")
            )
            ->whereRaw('jenis in(4,5)')
            ->groupBy('coa');

        DB::table($tempRekapKredit)->insertUsing([
            'coa',
            'perkiraan',
            'nominal'
        ], $queryRekapKredit);



        $getData2 = DB::table($tempRekapPerkiraan)->from(
            DB::raw($tempRekapPerkiraan . " as a")
        )
            ->select(
                'a.coa',
                DB::raw("(case when A.perkiraan='' then 'SALDO AWAL' else A.perkiraan end) perkiraan"),
                DB::raw("isnull(B.nominal,0) as nominaldebet"),
                DB::raw("isnull(C.nominal,0) as nominalkredit")
            )
            ->leftJoin($tempRekapDebet . " as b", 'a.coa', '=', 'b.coa')
            ->leftJoin($tempRekapKredit . " as c", 'a.coa', '=', 'c.coa')
            ->get();




        return [$getData, $getData2];
    }
}
