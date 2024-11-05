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

        $cabang = (new Parameter())->cekText('CABANG', 'CABANG');
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

        // rekap saldo

        // rekap ke saldo awal bank
        // dd($tgl2 );
        $tglsaldo = '2023-10-01';
        $awalsaldo = date('Y-m-d', strtotime($tglsaldo));

        $tutupbuku = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text'
            )
            ->where('grp', 'TUTUP BUKU')
            ->where('subgrp', 'TUTUP BUKU')
            ->first()->text ?? '1900-01-01';

        $awaldari = date('Y-m-', strtotime($tgl)) . '01';
        $awalcek = date('Y-m-d', strtotime($tutupbuku . ' +1 day'));
        $akhircek = date('Y-m-d', strtotime($awaldari . ' -1 day'));



        if ($awalcek <= $awalsaldo) {
            $awalcek = $awalsaldo;
        }

        $tglawalcek = $awalcek;
        $tglakhircek = $akhircek;
        // dump($tglawalcek);
        // dump($tglakhircek);
        $bulan1 = date('m-Y', strtotime($awalcek));
        $bulan2 = date('m-Y', strtotime('1900-01-01'));
        // dd($bulan1);
        // while ($awalcek <= $akhircek) {
        //     $bulan1 = date('m-Y', strtotime($awalcek));
        //     if ($bulan1 != $bulan2) {
        //         // dump($bulan1);
        //         // dump($bulan1);
        //         DB::delete(DB::raw("delete saldoawalbank WHERE isnull(bulan,'')='" . $bulan1 . "'"));
        //     }

        //     $awalcek = date('Y-m-d', strtotime($awalcek . ' +1 day'));
        //     $awalcek2 = date('Y-m-d', strtotime($awalcek . ' +1 day'));
        //     $bulan2 = date('m-Y', strtotime($awalcek2));
        // }
        // DB::delete(DB::raw("delete saldoawalbank WHERE isnull(bulan,'')='" . $bulan2 . "'"));

        // dd('test1');
        $tempsaldoawal = '##tempsaldoawal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawal, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
        });



        // penerimaan
        $querydebet = DB::table("penerimaanheader")->from(
            DB::raw("penerimaanheader as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("a.bank_id"),
                DB::raw("sum(b.nominal) as nominaldebet"),
                DB::raw("0 as nominalkredit")
            )
            ->join(DB::raw("penerimaandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tgl2 . "'")
            ->groupby('a.bank_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));



        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querydebet);

        $querydebet = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("a.bankke_id as bank_id"),
                DB::raw("sum(a.nominal) as nominaldebet"),
                DB::raw("0 as nominalkredit")
            )
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tgl2 . "'")
            ->groupby('a.bankke_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));


        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querydebet);

        // pengeluaran

        $querykredit = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("a.bank_id"),
                DB::raw("0 as nominaldebet"),
                DB::raw("sum(b.nominal) as nominalkredit")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("isnull(a.alatbayar_id,0)  not in(3,4)")
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tgl2 . "'")
            ->groupby('a.bank_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));


        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querykredit);

        $querykredit = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("a.bank_id"),
                DB::raw("0 as nominaldebet"),
                DB::raw("sum(b.nominal) as nominalkredit")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("pencairangiropengeluaranheader as c with (readuncommitted)"), 'b.nobukti', 'c.pengeluaran_nobukti')
            ->whereraw("isnull(a.alatbayar_id,0)  in(3,4)")
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tgl2 . "'")
            ->groupby('a.bank_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));


        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querykredit);


        $querykredit = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("a.bankdari_id as bank_id"),
                DB::raw("0 as nominaldebet"),
                DB::raw("sum(a.nominal) as nominalkredit")
            )
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tgl2 . "'")
            ->groupby('a.bankdari_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));


        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querykredit);

        $temppengembaliankepusat = '##temppengembaliankepusat' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengembaliankepusat, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('bankpengembalian_id')->nullable();
        });

        $tempnonpengembaliankepusat = '##tempnonpengembaliankepusat' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempnonpengembaliankepusat, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('coa', 50)->nullable();
        });

        $querynonpengembalian = db::table("bank")->from(db::raw("bank a with (readuncommitted)"))
            ->select(
                'a.id as bank_id',
                'a.coa as coa',
            )
            ->where('a.statusaktif', 1)
            ->whereraw("left(a.kodebank,12)<>'PENGEMBALIAN'");

        DB::table($tempnonpengembaliankepusat)->insertUsing([
            'bank_id',
            'coa',
        ], $querynonpengembalian);

        $querypengembalian = db::table("bank")->from(db::raw("bank a with (readuncommitted)"))
            ->select(
                'b.bank_id',
                'a.id as bankpengembalian_id',
            )
            ->join(db::raw($tempnonpengembaliankepusat . " b"), 'a.coa', 'b.coa')
            ->where('a.statusaktif', 1)
            ->whereraw("left(a.kodebank,12)='PENGEMBALIAN'");

        DB::table($temppengembaliankepusat)->insertUsing([
            'bank_id',
            'bankpengembalian_id',
        ], $querypengembalian);

        // dd('test');
        $querykredit = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("c.bank_id"),
                DB::raw("0 as nominaldebet"),
                DB::raw("sum(b.nominal) as nominalkredit")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw($temppengembaliankepusat . " as c with (readuncommitted)"), 'a.bank_id', 'c.bankpengembalian_id')
            ->whereraw("isnull(a.alatbayar_id,0) not in(3,4)")
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tgl2 . "'")
            ->groupby('c.bank_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));



        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querykredit);

        $querykredit = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("c.bank_id"),
                DB::raw("0 as nominaldebet"),
                DB::raw("sum(b.nominal) as nominalkredit")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw($temppengembaliankepusat . " as c with (readuncommitted)"), 'a.bank_id', 'c.bankpengembalian_id')
            ->join(DB::raw("pencairangiropengeluaranheader as d with (readuncommitted)"), 'a.nobukti', 'd.pengeluaran_nobukti')
            ->whereraw("isnull(a.alatbayar_id,0)  in(3,4)")
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tgl2 . "'")
            ->groupby('c.bank_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));



        DB::table($tempsaldoawal)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
        ], $querykredit);

        DB::delete(DB::raw("delete " . $tempsaldoawal . " from " . $tempsaldoawal . " a 
              inner join " . $temppengembaliankepusat . " b on a.bank_id=b.bankpengembalian_id"));



        // dd(db::table($tempsaldoawal)->where('bank_id',2)->get());

        // 

        $queryrekap = db::table($tempsaldoawal)->from(db::raw($tempsaldoawal . " a"))
            ->select(
                'bulan',
                'bank_id',
                db::raw("sum(nominaldebet) as nominaldebet"),
                db::raw("sum(nominalkredit) as nominalkredit"),
                db::raw("'' as info"),
                db::raw("getdate() as created_at"),
                db::raw("getdate() as updated_at"),
            )
            ->groupby('a.bulan')
            ->groupby('a.bank_id');


        $tempsaldoawalrekap = '##tempsaldoawalrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawalrekap, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
            $table->longtext('info')->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
        });

        DB::table($tempsaldoawalrekap)->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
            'info',
            'created_at',
            'updated_at',
        ], $queryrekap);


        DB::delete(DB::raw("delete saldoawalbank from saldoawalbank as a 
                inner join " . $tempsaldoawalrekap . " b on a.bulan=b.bulan and a.bank_id=b.bank_id"));

        DB::table("saldoawalbank")->insertUsing([
            'bulan',
            'bank_id',
            'nominaldebet',
            'nominalkredit',
            'info',
            'created_at',
            'updated_at',
        ], $queryrekap);


        // akhir rekap
        // end rekap


        $querySaldoAwal = DB::table("saldoawalbank")->from(
            DB::raw("saldoawalbank")
        )
            ->select(
                DB::Raw('isnull(sum(isnull(nominaldebet,0)-isnull(nominalkredit,0)),0) as saldoawal'),
            )
            ->whereRaw("right(bulan,4)+left(bulan,2)<right('" . $tahun . "',4)+left('" . $bulan . "',2)")
            ->where('bank_id', $jenis)
            ->first();

        // dd($querySaldoAwal->tosql());

        $saldoAwal = $querySaldoAwal->saldoawal;


        $tempList = '##tempList' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempList, function ($table) {
            $table->integer('jenis')->nullable();
            $table->integer('jenismasuk')->nullable();
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->unsignedBigInteger('nilaikosongdebet')->nullable();
            $table->unsignedBigInteger('nilaikosongkredit')->nullable();
        });



        $tempList2 = '##tempList2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempList2, function ($table) {
            $table->integer('jenis')->nullable();
            $table->integer('jenismasuk')->nullable();
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->unsignedBigInteger('nilaikosongdebet')->nullable();
            $table->unsignedBigInteger('nilaikosongkredit')->nullable();
        });

        DB::table($tempList)->insert([
            'jenis' => 1,
            'jenismasuk' => 0,
            'tgl' => date('Y-m-d', strtotime($tanggal)),
            'nobukti' => '',
            'coa' => '',
            'perkiraan' => '',
            'keterangan' => 'SALDO AWAL',
            'debet' => 0,
            'kredit' => 0,
            'saldo' => $saldoAwal,
            "nilaikosongdebet" => "1",
            "nilaikosongkredit" => "1",
        ]);

        // dd(db::table($tempList)->get());

        while ($tgl1 <= $tgl2) {
            DB::table($tempList)->insert([
                'jenis' => 1,
                'jenismasuk' => 0,
                'tgl' => date('Y-m-d', strtotime($tgl1)),
                'nobukti' => '',
                'coa' => '',
                'perkiraan' => '',
                'keterangan' => 'SALDO AWAL',
                'debet' => 0,
                'kredit' => 0,
                'saldo' => 0,
                "nilaikosongdebet" => "1",
                "nilaikosongkredit" => "1",
            ]);

            $tgl1 = date('Y-m-d', strtotime($tgl1 . ' +1 day'));
        }

        $queryTempList = DB::table('penerimaandetail')->from(
            DB::raw('penerimaandetail as a')
        )
            ->select(
                'a.coakredit as coa',
                DB::raw("2 as jenis"),
                DB::raw("1 as jenismasuk"),
                'a.tgljatuhtempo',
                'a.nobukti',
                DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                'a.keterangan',
                // db::raw("(case when nominal>0 then (nominal) else 0 end) as debet"),
                // DB::raw("(case when nominal<0 then abs(nominal) else 0 end) as kredit"),
                db::raw("nominal as debet"),
                DB::raw("0  as kredit"),
                DB::raw("0 as saldo"),
                DB::raw("0  as nilaikosongdebet"),
                DB::raw("1  as nilaikosongkredit"),

            )
            ->join(DB::raw("penerimaanheader as b "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("akunpusat as c "), 'a.coakredit', 'c.coa')
            ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
            ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
            ->where('b.bank_id', '=', $jenis);

        DB::table($tempList)->insertUsing([
            'coa',
            'jenis',
            'jenismasuk',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $queryTempList);

        // disini

        $queryTempPindahBuku = DB::table('pindahbuku')->from(
            DB::raw('pindahbuku as a')
        )
            ->select(
                'a.coadebet as coa',
                DB::raw("3 as jenis"),
                DB::raw("1 as jenismasuk"),
                'a.tgljatuhtempo',
                'a.nobukti',
                DB::raw("isnull(C.keterangancoa,'')  as perkiraan"),
                'a.keterangan',
                'nominal as debet',
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo"),
                DB::raw("0  as nilaikosongdebet"),
                DB::raw("1  as nilaikosongkredit"),

            )
            ->leftjoin(DB::raw("akunpusat as c "), 'a.coakredit', 'c.coa')
            ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
            ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
            ->where('a.bankke_id', '=', $jenis);

        DB::table($tempList)->insertUsing([
            'coa',
            'jenis',
            'jenismasuk',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $queryTempPindahBuku);

        $queryTempPengeluaran = DB::table('pengeluarandetail')->from(
            DB::raw('pengeluarandetail as a')
        )
            ->select(
                'c.coa as coa',
                DB::raw("4 as jenis"),
                DB::raw("2 as jenismasuk"),
                'a.tgljatuhtempo',
                'a.nobukti',
                DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                'a.keterangan',
                // DB::raw("(case when nominal<0 then abs(nominal) else 0 end) as debet"),
                // DB::raw("(case when nominal>0 then nominal else 0 end) as kredit"),
                DB::raw("0  as debet"),
                DB::raw("nominal  as kredit"),
                DB::raw("0 as saldo"),
                DB::raw("1  as nilaikosongdebet"),
                DB::raw("0  as nilaikosongkredit"),
            )
            ->join(DB::raw("pengeluaranheader as b "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("akunpusat as c "), db::raw("
                (case when '" . $cabang . "' = 'PUSAT' then a.coadebet else 
            (case when a.coakredit='03.02.02.05' then a.coakredit else a.coadebet end) end)
            "), 'c.coa')
            ->whereraw("isnull(b.alatbayar_id,0) not in(3,4)")
            ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
            ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
            ->where('b.bank_id', '=', $jenis);

        DB::table($tempList)->insertUsing([
            'coa',
            'jenis',
            'jenismasuk',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $queryTempPengeluaran);

        // $queryTempPengeluaran = DB::table('pengeluarandetail')->from(
        //     DB::raw('pengeluarandetail as a')
        // )
        //     ->select(
        //         'a.coadebet as coa',
        //         DB::raw("4 as jenis"),
        //         'a.tgljatuhtempo',
        //         'a.nobukti',
        //         DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
        //         'a.keterangan',
        //         // DB::raw("(case when nominal<0 then abs(nominal) else 0 end) as debet"),
        //         // DB::raw("(case when nominal>0 then nominal else 0 end) as kredit"),
        //         DB::raw("0  as debet"),
        //         DB::raw("nominal  as kredit"),
        //         DB::raw("0 as saldo"),
        //     )
        //     ->join(DB::raw("pengeluaranheader as b "), 'a.nobukti', 'b.nobukti')
        //     ->leftjoin(DB::raw("akunpusat as c "), 'a.coadebet', 'c.coa')
        //     ->whereraw("isnull(a.alatbayar_id,0) not in(3,4)")
        //     ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
        //     ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
        //     ->where('b.bank_id', '=', $jenis);

        // DB::table($tempList)->insertUsing([
        //     'coa',
        //     'jenis',
        //     'tgl',
        //     'nobukti',
        //     'perkiraan',
        //     'keterangan',
        //     'debet',
        //     'kredit',
        //     'saldo'
        // ], $queryTempPengeluaran);

        $queryTempPengeluaran = DB::table('pengeluarandetail')->from(
            DB::raw('pengeluarandetail as a')
        )
            ->select(
                'c.coa as coa',
                DB::raw("4 as jenis"),
                DB::raw("2 as jenismasuk"),
                'a.tgljatuhtempo',
                'a.nobukti',
                DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                'a.keterangan',
                // DB::raw("(case when nominal<0 then abs(nominal) else 0 end) as debet"),
                // DB::raw("(case when nominal>0 then nominal else 0 end) as kredit"),
                DB::raw("0  as debet"),
                DB::raw("nominal  as kredit"),
                DB::raw("0 as saldo"),
                DB::raw("1  as nilaikosongdebet"),
                DB::raw("0  as nilaikosongkredit"),
            )
            ->join(DB::raw("pengeluaranheader as b "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("akunpusat as c "), db::raw("
            (case when '" . $cabang . "' = 'PUSAT' then a.coadebet else 
            (case when a.coakredit='03.02.02.05' then a.coakredit else a.coadebet end) end)"), 'c.coa')
            ->join(DB::raw("pencairangiropengeluaranheader as d with (readuncommitted)"), 'b.nobukti', 'd.pengeluaran_nobukti')
            ->whereraw("isnull(b.alatbayar_id,0) in(3,4)")
            ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
            ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
            ->where('b.bank_id', '=', $jenis);

        DB::table($tempList)->insertUsing([
            'coa',
            'jenis',
            'jenismasuk',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $queryTempPengeluaran);


        $queryTempPindahBukuDua = DB::table('pindahbuku')->from(
            DB::raw('pindahbuku as a')
        )
            ->select(
                'a.coakredit as coa',
                DB::raw("5 as jenis"),
                DB::raw("2 as jenismasuk"),
                'a.tgljatuhtempo',
                'a.nobukti',
                DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                'a.keterangan',
                DB::raw("0 as debet"),
                DB::raw("nominal as kredit"),
                DB::raw("0 as saldo"),
                DB::raw("1  as nilaikosongdebet"),
                DB::raw("0  as nilaikosongkredit"),
            )

            ->leftjoin(DB::raw("akunpusat as c "), 'a.coadebet', 'c.coa')
            ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
            ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
            ->where('a.bankdari_id', '=', $jenis);

        DB::table($tempList)->insertUsing([
            'coa',
            'jenis',
            'jenismasuk',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $queryTempPindahBukuDua);

        // pengembalian kepusat

        $coabank = db::table("bank")->from(db::raw("bank a with (readuncommitted)"))
            ->select(
                'a.coa'
            )->where('a.id', $jenis)
            ->first()->coa ?? '';


        $bankpengembaliankepusat = db::table('bank')->from(db::raw("bank a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.coa', $coabank)
            ->where('a.statusaktif', 1)
            ->whereRaw("a.id<>" . $jenis)
            ->first();

        if (isset($bankpengembaliankepusat)) {
            $queryTempPengeluaran = DB::table('pengeluarandetail')->from(
                DB::raw('pengeluarandetail as a')
            )
                ->select(
                    'c.coa as coa',
                    DB::raw("6 as jenis"),
                    DB::raw("2 as jenismasuk"),
                    'a.tgljatuhtempo',
                    'a.nobukti',
                    DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                    'a.keterangan',
                    // DB::raw("(case when nominal<0 then abs(nominal) else 0 end) as debet"),
                    // DB::raw("(case when nominal>0 then nominal else 0 end) as kredit"),
                    DB::raw("0 as debet"),
                    DB::raw("nominal as kredit"),
                    DB::raw("0 as saldo"),
                    DB::raw("1  as nilaikosongdebet"),
                    DB::raw("0  as nilaikosongkredit"),
                )
                ->join(DB::raw("pengeluaranheader as b "), 'a.nobukti', 'b.nobukti')
                ->leftjoin(DB::raw("akunpusat as c "), db::raw("
                (case when '" . $cabang . "' = 'PUSAT' then a.coadebet else 
                (case when a.coakredit='03.02.02.05' then a.coakredit else a.coadebet end) end)
                "), 'c.coa')
                ->whereraw("isnull(b.alatbayar_id,0) not in(3,4)")
                ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
                ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
                ->where('b.bank_id', '=', $bankpengembaliankepusat->id);

            DB::table($tempList)->insertUsing([
                'coa',
                'jenis',
                'jenismasuk',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'nilaikosongdebet',
                'nilaikosongkredit'
            ], $queryTempPengeluaran);

            $queryTempPengeluaran = DB::table('pengeluarandetail')->from(
                DB::raw('pengeluarandetail as a')
            )
                ->select(
                    'c.coa as coa',
                    DB::raw("6 as jenis"),
                    DB::raw("2 as jenismasuk"),                    
                    'a.tgljatuhtempo',
                    'a.nobukti',
                    DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                    'a.keterangan',
                    // DB::raw("(case when nominal<0 then abs(nominal) else 0 end) as debet"),
                    // DB::raw("(case when nominal>0 then nominal else 0 end) as kredit"),
                    DB::raw("0 as debet"),
                    DB::raw("nominal as kredit"),
                    DB::raw("0 as saldo"),
                    DB::raw("1  as nilaikosongdebet"),
                    DB::raw("0  as nilaikosongkredit"),
                )
                ->join(DB::raw("pengeluaranheader as b "), 'a.nobukti', 'b.nobukti')
                ->leftjoin(DB::raw("akunpusat as c "), db::raw("
                (case when  '" . $cabang . "' = 'PUSAT' then a.coadebet else                 
                (case when a.coakredit='03.02.02.05' then a.coakredit else a.coadebet end) end)"), 'c.coa')
                ->join(DB::raw("pencairangiropengeluaranheader as d with (readuncommitted)"), 'b.nobukti', 'd.pengeluaran_nobukti')
                ->whereraw("isnull(b.alatbayar_id,0) in(3,4)")
                ->whereRaw("month(A.tgljatuhtempo)= cast(left($bulan,2) as integer)")
                ->whereRaw("year(A.tgljatuhtempo)= cast(right($tahun,4) as integer)")
                ->where('b.bank_id', '=', $bankpengembaliankepusat->id);

            DB::table($tempList)->insertUsing([
                'coa',
                'jenis',
                'jenismasuk',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'nilaikosongdebet',
                'nilaikosongkredit'
            ], $queryTempPengeluaran);
        }





        // 



        $queryTempList2 = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                'jenis',
                'jenismasuk',
                'coa',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'nilaikosongdebet',
                'nilaikosongkredit'
            );


        DB::table($tempList2)->insertUsing([
            'jenis',
            'jenismasuk',
            'coa',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
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
            $table->integer('jenismasuk');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->unsignedBigInteger('nilaikosongdebet')->nullable();
            $table->unsignedBigInteger('nilaikosongkredit')->nullable();
        });

        $queryTempListRekap = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                DB::raw("'LAPORAN HARIAN' AS  jenislaporan"),
                'jenis',
                'jenismasuk',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'nilaikosongdebet',
                'nilaikosongkredit'

            )
            ->orderBy('tgl', 'ASC')
            ->orderBy('jenis', 'ASC')
            ->orderBy('nobukti', 'ASC');

        DB::table($tempListRekap)->insertUsing([
            'jenislaporan',
            'jenis',
            'jenismasuk',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $queryTempListRekap);

        $tempLaporan = '##tempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempLaporan, function ($table) {
            $table->string('jenislaporan', 100);
            $table->integer('jenis');
            $table->integer('jenismasuk');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->integer('id')->nullable();
            $table->unsignedBigInteger('nilaikosongdebet')->nullable();
            $table->unsignedBigInteger('nilaikosongkredit')->nullable();
        });

        $queryTempLaporan = DB::table($tempListRekap)->from(
            DB::raw($tempListRekap . ' as a')
        )
            ->select(
                DB::raw("'LAPORAN HARIAN' AS  jenislaporan"),
                'a.jenis',
                'a.jenismasuk',
                'a.tgl as tglbukti',
                'a.nobukti',
                'a.perkiraan',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(A.saldo,0)+A.debet)-A.Kredit) over (order by id asc) as Saldo"),
                'a.id',
                'a.nilaikosongdebet',
                'a.nilaikosongkredit'

            )
            ->where('a.jenislaporan', 'LAPORAN HARIAN')
            ->orderBy('a.id', 'ASC');


        DB::table($tempLaporan)->insertUsing([
            'jenislaporan',
            'jenis',
            'jenismasuk',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'id',
            'nilaikosongdebet',
            'nilaikosongkredit'
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
            $table->integer('jenismasuk');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->unsignedBigInteger('nilaikosongdebet')->nullable();
            $table->unsignedBigInteger('nilaikosongkredit')->nullable();
        });

        $queryTempRekap = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                DB::raw("'LAPORAN REKAP' AS  jenislaporan"),
                'jenis',
                'jenismasuk',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'nilaikosongdebet',
                'nilaikosongkredit'

            )
            ->orderBy('tgl', 'ASC')
            ->orderBy('jenis', 'ASC')
            ->orderBy('nobukti', 'ASC');

        DB::table($tempRekap)->insertUsing([
            'jenislaporan',
            'jenis',
            'jenismasuk',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $queryTempRekap);

        $queryTempLaporanRekap = DB::table($tempRekap)->from(
            DB::raw($tempRekap . ' as a')
        )
            ->select(
                DB::raw("'LAPORAN REKAP' AS  jenislaporan"),
                'a.jenis',
                'a.jenismasuk',
                'a.tgl as tglbukti',
                'a.nobukti',
                'a.perkiraan',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(A.saldo,0)+A.debet)-A.Kredit) over (order by id asc) as Saldo"),
                'a.id',
                'a.nilaikosongdebet',
                'a.nilaikosongkredit'

            )
            ->where('a.jenislaporan', 'LAPORAN REKAP')
            ->orderBy('a.id', 'ASC');

        DB::table($tempLaporan)->insertUsing([
            'jenislaporan',
            'jenis',
            'jenismasuk',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'id',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $queryTempLaporanRekap);

        // dd(db::table($tempList)->get());

        $querySaloAwalRekap01 = DB::table($tempList)->from(
            DB::raw($tempList . ' as a')
        )
            ->select(
                DB::raw("SUM(isnull(saldo,0)+isnull(debet,0)) as saldoawalrekap01"),
            )
            ->where('jenis', '<=', 3)
            ->first();

        $saldoAwalRekap01 = $querySaloAwalRekap01->saldoawalrekap01;


        // dd($saldoAwalRekap01);
        DB::table($tempList)
            ->where("jenis", "<=", 3)
            ->delete();

        DB::table($tempList)->insert([
            'jenis' => 1,
            'jenismasuk' => 0,
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
            $table->integer('jenismasuk');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->unsignedBigInteger('nilaikosongdebet')->nullable();
            $table->unsignedBigInteger('nilaikosongkredit')->nullable();
        });

        $queryLaporanRekap01 = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                DB::raw("'LAPORAN REKAP 01' AS  jenislaporan"),
                'jenis',
                'jenismasuk',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'nilaikosongdebet',
                'nilaikosongkredit'
            )
            ->orderBy('tgl', 'ASC')
            ->orderBy('jenis', 'ASC')
            ->orderBy('nobukti', 'ASC');

        DB::table($tempRekap01)->insertUsing([
            'jenislaporan',
            'jenis',
            'jenismasuk',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $queryLaporanRekap01);

        // dd(db::table($tempRekap01)->whereraw("tgl='2024/1/1'")->get());


        $queryLaporanRekap01Dua = DB::table($tempRekap01)->from(
            DB::raw($tempRekap01 . " as a")
        )
            ->select(
                DB::raw("'LAPORAN REKAP 01' AS  jenislaporan"),
                'a.jenis',
                'a.jenismasuk',
                'a.tgl as tglbukti',
                'a.nobukti',
                'a.perkiraan',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(A.saldo,0)+A.debet)-A.Kredit) over (order by id asc) as Saldo"),
                'a.id',
                'a.nilaikosongdebet',
                'a.nilaikosongkredit'
            )
            ->where('a.jenislaporan', '=', 'LAPORAN REKAP 01')
            ->orderBy('a.id', 'ASC');


        DB::table($tempLaporan)->insertUsing([
            'jenislaporan',
            'jenis',
            'jenismasuk',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'id',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $queryLaporanRekap01Dua);

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $getData = DB::table($tempLaporan)->select(
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'id',
            DB::raw("'" . $getJudul->text . "' as judul"),
            'nilaikosongdebet',
            'nilaikosongkredit'
        )
            ->orderBy('jenislaporan', 'asc')
            ->orderBy('jenismasuk', 'asc')
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


        // rekap kas harian

        $temppenerimaan = '##temppenerimaan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaan, function ($table) {
            $table->string('coa', 50)->nullable();
            $table->longtext('keterangancoa')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $temppengeluaran = '##temppengeluaran' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengeluaran, function ($table) {
            $table->string('coa', 50)->nullable();
            $table->longtext('keterangancoa')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypenerimaan = db::table("penerimaandetail")->from(db::raw("penerimaandetail a with (readuncommitted)"))
            ->select(
                'c.coa',
                'c.keterangancoa',
                db::raw("sum(a.nominal) as nominal")
            )
            ->join(db::raw("penerimaanheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("akunpusat c with (readuncommitted)"), 'a.coakredit', 'c.coa')
            ->whereRaw("format(b.tglbukti,'MM-yyyy')='" . $sampai . "'")
            ->where('b.bank_id', $jenis)
            ->groupby('c.coa')
            ->groupby('c.keterangancoa');

        DB::table($temppenerimaan)->insertUsing([
            'coa',
            'keterangancoa',
            'nominal'
        ], $querypenerimaan);

        $querypenerimaan = db::table("pindahbuku")->from(db::raw("pindahbuku a with (readuncommitted)"))
            ->select(
                'c.coa',
                'c.keterangancoa',
                db::raw("sum(a.nominal) as nominal")
            )
            ->join(db::raw("akunpusat c with (readuncommitted)"), 'a.coakredit', 'c.coa')
            ->whereRaw("format(a.tglbukti,'MM-yyyy')='" . $sampai . "'")
            ->where('a.bankke_id', $jenis)
            ->groupby('c.coa')
            ->groupby('c.keterangancoa');

        DB::table($temppenerimaan)->insertUsing([
            'coa',
            'keterangancoa',
            'nominal'
        ], $querypenerimaan);

        // dd(db::table($temppenerimaan)->get());


        $querypengeluaran = db::table("pengeluarandetail")->from(db::raw("pengeluarandetail a with (readuncommitted)"))
            ->select(
                'c.coa',
                'c.keterangancoa',
                db::raw("sum(a.nominal) as nominal")
            )
            ->join(db::raw("pengeluaranheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("akunpusat c with (readuncommitted)"), db::raw("
            (case when  '" . $cabang . "' = 'PUSAT' then a.coadebet else 
            (case when a.coakredit='03.02.02.05' then a.coakredit else a.coadebet end) end)"), 'c.coa')
            ->whereRaw("format(b.tglbukti,'MM-yyyy')='" . $sampai . "'")
            ->where('b.bank_id', $jenis)
            ->whereraw("b.alatbayar_id not in (3,4)")
            ->groupby('c.coa')
            ->groupby('c.keterangancoa');

            // dd($querypengeluaran->toSql());

        DB::table($temppengeluaran)->insertUsing([
            'coa',
            'keterangancoa',
            'nominal'
        ], $querypengeluaran);

        $querypengeluaran = db::table("pengeluarandetail")->from(db::raw("pengeluarandetail a with (readuncommitted)"))
            ->select(
                'c.coa',
                'c.keterangancoa',
                db::raw("sum(a.nominal) as nominal")
            )
            ->join(db::raw("pengeluaranheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("akunpusat c with (readuncommitted)"), db::raw("
            (case when  '" . $cabang . "' = 'PUSAT' then a.coadebet else 
            (case when a.coakredit='03.02.02.05' then a.coakredit else a.coadebet end) end)
            "), 'c.coa')
            ->join(db::raw("pencairangiropengeluaranheader d with (readuncommitted)"), 'b.nobukti', 'd.pengeluaran_nobukti')
            ->whereRaw("format(b.tglbukti,'MM-yyyy')='" . $sampai . "'")
            ->where('b.bank_id', $jenis)
            ->whereraw("b.alatbayar_id in (3,4)")
            ->groupby('c.coa')
            ->groupby('c.keterangancoa');


        DB::table($temppengeluaran)->insertUsing([
            'coa',
            'keterangancoa',
            'nominal'
        ], $querypengeluaran);

        $querypengeluaran = db::table("pindahbuku")->from(db::raw("pindahbuku a with (readuncommitted)"))
            ->select(
                'c.coa',
                'c.keterangancoa',
                db::raw("sum(a.nominal) as nominal")
            )
            ->join(db::raw("akunpusat c with (readuncommitted)"), db::raw("
            (case when  '" . $cabang . "' = 'PUSAT' then a.coadebet else 
            (case when a.coakredit='03.02.02.05' then a.coakredit else a.coadebet end) end)"), 'c.coa')
            ->whereRaw("format(a.tglbukti,'MM-yyyy')='" . $sampai . "'")
            ->where('a.bankdari_id', $jenis)
            ->groupby('c.coa')
            ->groupby('c.keterangancoa');

        DB::table($temppengeluaran)->insertUsing([
            'coa',
            'keterangancoa',
            'nominal'
        ], $querypengeluaran);

        // dd(db::table($temppenerimaan)->get());
        // dd(db::table($temppengeluaran)->get());



        $temppenerimaanrekap = '##temppenerimaanrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaanrekap, function ($table) {
            $table->string('coa', 50)->nullable();
            $table->longtext('keterangancoa')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $temppengeluaranrekap = '##temppengeluaranrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengeluaranrekap, function ($table) {
            $table->string('coa', 50)->nullable();
            $table->longtext('keterangancoa')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querypenerimaan = db::table($temppenerimaan)->from(db::raw($temppenerimaan . " a "))
            ->select(
                'a.coa',
                'a.keterangancoa',
                db::raw("sum(a.nominal) as nominal")
            )
            ->groupby('a.coa')
            ->groupby('a.keterangancoa');

        DB::table($temppenerimaanrekap)->insertUsing([
            'coa',
            'keterangancoa',
            'nominal'
        ], $querypenerimaan);

        $querypengeluaran = db::table($temppengeluaran)->from(db::raw($temppengeluaran . " a "))
            ->select(
                'a.coa',
                'a.keterangancoa',
                db::raw("sum(a.nominal) as nominal")
            )
            ->groupby('a.coa')
            ->groupby('a.keterangancoa');

        DB::table($temppengeluaranrekap)->insertUsing([
            'coa',
            'keterangancoa',
            'nominal'
        ], $querypengeluaran);



        $temppenerimaanrekap2 = '##temppenerimaanrekap2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppenerimaanrekap2, function ($table) {
            $table->integer('urut')->nullable();
            $table->string('coa', 50)->nullable();
            $table->longtext('keterangancoa')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $temppengeluaranrekap2 = '##temppengeluaranrekap2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppengeluaranrekap2, function ($table) {
            $table->integer('urut')->nullable();
            $table->string('coa', 50)->nullable();
            $table->longtext('keterangancoa')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });


        $querypenerimaan = db::table($temppenerimaanrekap)->from(db::raw($temppenerimaanrekap . " a "))
            ->select(
                db::raw("row_number() Over(Order By keterangancoa) As urut"),
                'a.coa',
                'a.keterangancoa',
                db::raw("(a.nominal) as nominal")
            )
            ->Orderby('a.keterangancoa');

        DB::table($temppenerimaanrekap2)->insertUsing([
            'urut',
            'coa',
            'keterangancoa',
            'nominal'
        ], $querypenerimaan);

        $querypenerimaan = db::table($temppengeluaranrekap)->from(db::raw($temppengeluaranrekap . " a "))
            ->select(
                db::raw("row_number() Over(Order By keterangancoa) As urut"),
                'a.coa',
                'a.keterangancoa',
                db::raw("(a.nominal) as nominal")
            )
            ->Orderby('a.keterangancoa');

        DB::table($temppengeluaranrekap2)->insertUsing([
            'urut',
            'coa',
            'keterangancoa',
            'nominal'
        ], $querypenerimaan);

        $tempurut = '##tempurut' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempurut, function ($table) {
            $table->integer('urut')->nullable();
        });

        // dd(db::table($temppenerimaanrekap2)->get());
        // dd(db::table($temppengeluaranrekap)->get());


        $queryurut = db::table($temppengeluaranrekap2)->from(db::raw($temppengeluaranrekap2 . " a "))
            ->select(
                'a.urut',
            );

        DB::table($tempurut)->insertUsing([
            'urut',
        ], $queryurut);

        $queryurut = db::table($temppengeluaranrekap2)->from(db::raw($temppengeluaranrekap2 . " a "))
            ->select(
                'a.urut',
            )
            ->leftjoin(db::raw($tempurut . " b"), 'a.urut', 'b.urut')
            ->whereraw("isnull(b.urut,0)=0");


        DB::table($tempurut)->insertUsing([
            'urut',
        ], $queryurut);


        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasil, function ($table) {
            $table->integer('urut')->nullable();
            $table->longtext('keterangancoapenerimaan')->nullable();
            $table->double('nominalpenerimaan', 15, 2)->nullable();
            $table->longtext('keterangancoapengeluaran')->nullable();
            $table->double('nominalpengeluaran', 15, 2)->nullable();
        });

        $queryhasil = db::table($tempurut)->from(db::raw($tempurut . " a "))
            ->select(
                'a.urut',
                db::raw("isnull(b.keterangancoa,'') as keterangancoapenerimaan"),
                db::raw("isnull(b.nominal,0) as nominalpenerimaan"),
                db::raw("isnull(c.keterangancoa,'') as keterangancoapengeluaran"),
                db::raw("isnull(c.nominal,0) as nominalpengeluaran"),
            )
            ->leftjoin(db::raw($temppenerimaanrekap2 . " b"), 'a.urut', 'b.urut')
            ->leftjoin(db::raw($temppengeluaranrekap2 . " c"), 'a.urut', 'c.urut')
            ->orderBy('a.urut');

        DB::table($temphasil)->insertUsing([
            'urut',
            'keterangancoapenerimaan',
            'nominalpenerimaan',
            'keterangancoapengeluaran',
            'nominalpengeluaran',
        ], $queryhasil);



        $queryhasil = db::table($tempRekapPerkiraan)->from(db::raw($tempRekapPerkiraan . " a "))
            ->select(
                db::raw("0 as urut"),
                db::raw("'SALDO AWAL' keterangancoapenerimaan"),
                db::raw("isnull(b.nominal,0) as nominalpenerimaan"),
                db::raw("'' as keterangancoapengeluaran"),
                db::raw("0 as nominalpengeluaran"),
            )
            ->Join($tempRekapDebet . " as b", 'a.coa', '=', 'b.coa')
            ->whereraw("isnull(a.perkiraan,'')=''");



        DB::table($temphasil)->insertUsing([
            'urut',
            'keterangancoapenerimaan',
            'nominalpenerimaan',
            'keterangancoapengeluaran',
            'nominalpengeluaran',
        ], $queryhasil);

                // dd(db::table($temphasil)->get());

        // dd(db::table($temphasil)->get());

        $getData2 = DB::table($temphasil)->from(
            DB::raw($temphasil . " as a")
        )
            ->select(
                DB::raw("a.keterangancoapenerimaan as perkiraan"),
                DB::raw("a.keterangancoapengeluaran as perkiraanpengeluaran"),
                DB::raw("isnull(a.nominalpenerimaan,0) as nominaldebet"),
                DB::raw("isnull(a.nominalpengeluaran,0) as nominalkredit"),
                DB::raw("'REKAP LAPORAN KAS' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul")
            )
            ->orderBy('a.urut', 'asc')
            ->get();

        // 


        // 

        // $getData2 = DB::table($tempRekapPerkiraan)->from(
        //     DB::raw($tempRekapPerkiraan . " as a")
        // )
        //     ->select(
        //         'a.coa',
        //         DB::raw("(case when A.perkiraan='' then 'SALDO AWAL' else A.perkiraan end) perkiraan"),
        //         DB::raw("isnull(B.nominal,0) as nominaldebet"),
        //         DB::raw("isnull(C.nominal,0) as nominalkredit"),
        //         DB::raw("'" . $getJudul->text . "' as judul")
        //     )
        //     ->leftJoin($tempRekapDebet . " as b", 'a.coa', '=', 'b.coa')
        //     ->leftJoin($tempRekapKredit . " as c", 'a.coa', '=', 'c.coa')
        //     ->get();


// dd($getData);


        return [$getData, $getData2];
    }
}
