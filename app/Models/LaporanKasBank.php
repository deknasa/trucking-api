<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKasBank extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluaranstokdetailfifo';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];



    public function getReport($dari, $sampai, $bank_id, $prosesneraca)
    {
        $prosesneraca = $prosesneraca ?? 0;
        $dariformat = date('Y/m/d', strtotime($dari));
        $sampaiformat = date('Y/m/d', strtotime($sampai));

        $dari = date('Y-m-d', strtotime($dari)) ?? '1900/1/1';
        $sampai = date('Y-m-d', strtotime($sampai)) ?? '1900/1/1';
        $bank_id = $bank_id;

        // rekap ke saldo awal bank

        $parameter = new Parameter();

        $tglsaldo = $parameter->cekText('SALDO', 'SALDO') ?? '1900-01-01';
        $tglsaldo = date('Y-m-d', strtotime($tglsaldo . ' +1 day'));
        $awalsaldo = date('Y-m-d', strtotime($tglsaldo));

        $tutupbuku = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text'
            )
            ->where('grp', 'TUTUP BUKU')
            ->where('subgrp', 'TUTUP BUKU')
            ->first()->text ?? '1900-01-01';

        $awaldari = date('Y-m-', strtotime($dari)) . '01';
        $awalcek = date('Y-m-d', strtotime($tutupbuku . ' +1 day'));
        $akhircek = date('Y-m-d', strtotime($awaldari . ' -1 day'));

        $tgl3 = date('Y-m-d', strtotime($dari . ' +33 days'));

        $tahun2 = date('Y', strtotime($tgl3));
        $bulan2 = date('m', strtotime($tgl3));

        $tgl2 = $tahun2 . '-' . $bulan2 . '-1';
        $tgl2 = date('Y-m-d', strtotime($tgl2 . ' -1 day'));

        if ($awalcek <= $awalsaldo) {
            $awalcek = $awalsaldo;
        }

        $tglawalcek = $awalcek;
        $tglakhircek = $akhircek;
        $bulan1 = date('m-Y', strtotime($awalcek));
        $bulan2 = date('m-Y', strtotime('1900-01-01'));
        // dd($bulan1);
        // while ($awalcek <= $akhircek) {
        //     $bulan1 = date('m-Y', strtotime($awalcek));
        //     if ($bulan1 != $bulan2) {
        //         DB::delete(DB::raw("delete saldoawalbank from saldoawalbank as a WHERE isnull(a.bulan,'')='" . $bulan1 . "'"));
        //     }

        //     $awalcek = date('Y-m-d', strtotime($awalcek . ' +1 day'));
        //     $awalcek2 = date('Y-m-d', strtotime($awalcek . ' +1 day'));
        //     $bulan2 = date('m-Y', strtotime($awalcek2));
        // }
        // DB::delete(DB::raw("delete saldoawalbank WHERE isnull(bulan,'')='" . $bulan2 . "'"));


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

        $tempsaldo = '##tempsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldo, function ($table) {
            $table->id();
            $table->double('urut', 15, 2)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });



        $coabank = db::table("bank")->from(db::raw("bank a with (readuncommitted)"))
            ->select(
                'a.coa'
            )->where('a.id', $bank_id)
            ->first()->coa ?? '';





        $querysaldoawalpenerimaan = DB::table("penerimaanheader")->from(
            DB::raw("penerimaanheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("penerimaandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti>=cast(ltrim(rtrim(str(year('" . $dariformat . "'))))+'/'+ltrim(rtrim(str(month('" . $dariformat . "'))))+'/1' as datetime) ")
            ->where('a.tglbukti', '<', $dari)
            ->where('a.bank_id', '=', $bank_id)
            ->first();

        $querysaldoawalpenerimaanpindahbuku = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                DB::raw("sum(a.nominal) as nominal")
            )
            ->whereRaw("a.tglbukti>=cast(ltrim(rtrim(str(year('" . $dariformat . "'))))+'/'+ltrim(rtrim(str(month('" . $dariformat . "'))))+'/1' as datetime) ")
            ->where('a.tglbukti', '<', $dari)
            ->where('a.bankke_id', '=', $bank_id)
            ->first();

        $querysaldoawalpengeluaran = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti>=cast(ltrim(rtrim(str(year('" . $dariformat . "'))))+'/'+ltrim(rtrim(str(month('" . $dariformat . "'))))+'/1' as datetime) ")
            ->where('a.tglbukti', '<', $dari)
            ->where('a.bank_id', '=', $bank_id)
            ->first();

        $querysaldoawalpengeluaranpindahbuku = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                DB::raw("sum(a.nominal) as nominal")
            )
            ->whereRaw("a.tglbukti>=cast(ltrim(rtrim(str(year('" . $dariformat . "'))))+'/'+ltrim(rtrim(str(month('" . $dariformat . "'))))+'/1' as datetime) ")
            ->where('a.tglbukti', '<', $dari)
            ->where('a.bankdari_id', '=', $bank_id)
            ->first();


        $bankpengembaliankepusat = db::table('bank')->from(db::raw("bank a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.coa', $coabank)
            ->whereRaw("a.id<>" . $bank_id)
            ->first();
        if (isset($bankpengembaliankepusat)) {
            $querysaldoawalpengembaliankepusat = DB::table("pengeluaranheader")->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("sum(b.nominal) as nominal")
                )
                ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->whereRaw("a.tglbukti>=cast(ltrim(rtrim(str(year('" . $dariformat . "'))))+'/'+ltrim(rtrim(str(month('" . $dariformat . "'))))+'/1' as datetime) ")
                ->where('a.tglbukti', '<', $dari)
                ->where('a.bank_id', '=', $bankpengembaliankepusat->id)
                ->first();
        }
        $saldoawalpengembaliankepusat = $querysaldoawalpengembaliankepusat->nominal ?? 0;



        $querysaldoawal = DB::table("saldoawalbank")->from(
            DB::raw("saldoawalbank as a with (readuncommitted)")
        )
            ->select(
                DB::raw("sum(isnull(a.nominaldebet,0)-isnull(a.nominalkredit,0)) as nominal")
            )
            ->whereRaw("cast(right(a.bulan,4)+'/'+left(a.bulan,2)+'/1' as date)<'" . $dariformat . "'")
            ->whereRaw("a.bulan<>format(cast('" . $dariformat . "' as date),'MM-yyyy')")
            // ->where('a.tglbukti', '<', $dari)
            ->where('a.bank_id', '=', $bank_id)
            ->first();

        // dd($querysaldoawal->to);
        $saldoawal =  ($querysaldoawal->nominal + $querysaldoawalpenerimaan->nominal + $querysaldoawalpenerimaanpindahbuku->nominal) - ($querysaldoawalpengeluaran->nominal + $querysaldoawalpengeluaranpindahbuku->nominal + $saldoawalpengembaliankepusat);

        // dd($saldoawal);

        // dd($saldoawal);
        // data coba coba


        DB::table($tempsaldo)->insert(
            array(
                'urut' => '1',
                "coa" => "",
                "tglbukti" => "1900/1/1",
                "nobukti" => "",
                "keterangan" => "SALDO AWAL",
                "debet" => "0",
                "kredit" => "0",
                "saldo" => $saldoawal ?? 0,
            )
        );

        $querypenerimaan = DB::table("penerimaanheader")->from(
            DB::raw("penerimaanheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("2 as urut"),
                'b.coakredit as coa',
                'a.tglbukti',
                'a.nobukti',
                'b.keterangan',
                // DB::raw("(case when b.nominal>0 then b.nominal else 0 end) as debet "),
                // DB::raw("(case when b.nominal<0 then abs(b.nominal) else 0 end) as kredit "),
                DB::raw("b.nominal  as debet "),
                DB::raw("0  as kredit "),
                DB::raw("0 as saldo"),
            )
            ->join(DB::raw("penerimaandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai)
            ->where('a.bank_id', '=', $bank_id)
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc');

        DB::table($tempsaldo)->insertUsing([
            'urut',
            'coa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querypenerimaan);

        $querypenerimaanpindahbuku = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                DB::raw("3 as urut"),
                'a.coakredit as coa',
                'a.tglbukti',
                'a.nobukti',
                'a.keterangan',
                // DB::raw("(case when a.nominal>0 then a.nominal else 0 end) as debet "),
                // DB::raw("(case when a.nominal<0 then abs(a.nominal) else 0 end) as kredit "),
                DB::raw("a.nominal  as debet "),
                DB::raw("0 as kredit "),
                DB::raw("0 as saldo"),
            )
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai)
            ->where('a.bankke_id', '=', $bank_id)
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc');

        DB::table($tempsaldo)->insertUsing([
            'urut',
            'coa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querypenerimaanpindahbuku);

        $querypengeluaran = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("4 as urut"),
                'b.coadebet as coa',
                'a.tglbukti',
                'a.nobukti',
                'b.keterangan',
                // DB::raw("(case when b.nominal<0 then abs(b.nominal) else 0 end) as debet "),
                // DB::raw("(case when b.nominal>0 then b.nominal else 0 end) as kredit "),
                DB::raw(" 0  as debet "),
                DB::raw("b.nominal  as kredit "),
                DB::raw("0 as saldo"),
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai)
            ->where('a.bank_id', '=', $bank_id)
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc');

        DB::table($tempsaldo)->insertUsing([
            'urut',
            'coa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querypengeluaran);
        if (isset($bankpengembaliankepusat)) {
            $querypengeluaran = DB::table("pengeluaranheader")->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("5 as urut"),
                    'b.coadebet as coa',
                    'a.tglbukti',
                    'a.nobukti',
                    'b.keterangan',
                    // DB::raw("(case when b.nominal<0 then abs(b.nominal) else 0 end) as debet "),
                    // DB::raw("(case when b.nominal>0 then b.nominal else 0 end) as kredit "),
                    DB::raw("0  as debet "),
                    DB::raw("b.nominal  as kredit "),
                    DB::raw("0 as saldo"),
                )
                ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->where('a.tglbukti', '>=', $dari)
                ->where('a.tglbukti', '<=', $sampai)
                ->where('a.bank_id', '=', $bankpengembaliankepusat->id)
                ->orderBy('a.tglbukti', 'Asc')
                ->orderBy('a.nobukti', 'Asc');

            // dd($bankpengembaliankepusat->id);
            DB::table($tempsaldo)->insertUsing([
                'urut',
                'coa',
                'tglbukti',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
            ], $querypengeluaran);
        }

        $querypengeluaranpindahbuku = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                DB::raw("6 as urut"),
                'a.coadebet as coa',
                'a.tglbukti',
                'a.nobukti',
                'a.keterangan',
                // DB::raw("(case when a.nominal<0 then abs(a.nominal) else 0 end) as debet "),
                // DB::raw("(case when a.nominal>0 then a.nominal else 0 end) as kredit "),
                DB::raw(" 0 as debet "),
                DB::raw("a.nominal  as kredit "),
                DB::raw("0 as saldo"),
            )
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai)
            ->where('a.bankdari_id', '=', $bank_id)
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc');

        DB::table($tempsaldo)->insertUsing([
            'urut',
            'coa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querypengeluaranpindahbuku);


        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->id();
            $table->double('urut', 15, 2)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $query = DB::table($tempsaldo)->from(
            $tempsaldo . " as a"
        )
            ->select(
                'a.urut',
                'a.coa',
                'a.tglbukti',
                'a.nobukti',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                'a.saldo',
            )
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.urut', 'Asc')
            ->orderBy('a.id', 'Asc');




        DB::table($temprekap)->insertUsing([
            'urut',
            'coa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $query);


        $querykasbank = DB::table('bank')->from(
            DB::raw("bank as a with (readuncommitted)")
        )
            ->select(
                'a.namabank'
            )
            ->where('a.id', '=', $bank_id)
            ->first();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

            // dd(db::table($temprekap)->orderby('id','asc')->get());

        $queryhasil = DB::table($temprekap)->from(
            $tempsaldo . " as a"
        )
            ->select(
                'a.urut',
                DB::raw("isnull(b.keterangancoa,'') as keterangancoa"),
                DB::raw("'" . $querykasbank->namabank . "' as namabank"),
                DB::raw("(case when year(isnull(a.tglbukti,'1900/1/1')) < '2000' then null else a.tglbukti end) as tglbukti"),
                'a.nobukti',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(a.saldo,0)+isnull(a.debet,0))-isnull(a.Kredit,0)) over (order by a.tglbukti,a.id) as saldo"),
                DB::raw("'Laporan Buku Kas Bank' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftjoin(DB::raw("akunpusat as b with (readuncommitted)"), 'a.coa', 'b.coa')
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.id', 'Asc');

        if ($prosesneraca == 1) {
            $data = $queryhasil;
        } else {
            $data = $queryhasil->get();
        }

        // dd($data);
        return $data;
    }
}