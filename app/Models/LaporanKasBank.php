<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use DateTime;

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
        $cabang = (new Parameter())->cekText('CABANG', 'CABANG');

        // dd('test');
        // dd($dari, $sampai, $bank_id, $prosesneraca);
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

        $tutupbuku = date('Y-m-', strtotime($tutupbuku . '-30 day')) . '01';
        $awaldari = date('Y-m-', strtotime($dari)) . '01';
        $awalcek = date('Y-m-d', strtotime($tutupbuku));
        $akhircek = date('Y-m-d', strtotime($awaldari . ' -1 day'));

        $tgl3 = date('Y-m-d', strtotime($dari . ' +33 days'));

        $tahun2 = date('Y', strtotime($tgl3));
        $bulan2 = date('m', strtotime($tgl3));

        $tgl2 = $tahun2 . '-' . $bulan2 . '-1';
        $tgl2 = date('Y-m-d', strtotime($tgl2 . ' -1 day'));

        // dd($awalcek);
        if ($awalcek <= $awalsaldo) {
            $awalcek = $awalsaldo;
        }

        // dd($awalcek);
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
            ->whereraw("isnull(a.alatbayar_id,0) not in(3,4)")
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
                db::raw("format(c.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("a.bank_id"),
                DB::raw("0 as nominaldebet"),
                DB::raw("sum(b.nominal) as nominalkredit")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("pencairangiropengeluaranheader as c with (readuncommitted)"), 'a.nobukti', 'c.pengeluaran_nobukti')
            ->whereraw("isnull(a.alatbayar_id,0) in(3,4)")
            ->whereRaw("c.tglbukti>='" . $tglawalcek . "' and c.tglbukti<='" . $tgl2 . "'")
            ->groupby('a.bank_id')
            ->groupby(db::raw("format(c.tglbukti,'MM-yyyy')"));

        // dd($querykredit->get());
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
                db::raw("format(d.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("c.bank_id"),
                DB::raw("0 as nominaldebet"),
                DB::raw("sum(b.nominal) as nominalkredit")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw($temppengembaliankepusat . " as c with (readuncommitted)"), 'a.bank_id', 'c.bankpengembalian_id')
            ->join(DB::raw("pencairangiropengeluaranheader as d with (readuncommitted)"), 'a.nobukti', 'd.pengeluaran_nobukti')
            ->whereraw("isnull(a.alatbayar_id,0) in(3,4)")
            ->whereRaw("d.tglbukti>='" . $tglawalcek . "' and d.tglbukti<='" . $tgl2 . "'")
            ->groupby('c.bank_id')
            ->groupby(db::raw("format(d.tglbukti,'MM-yyyy')"));

        // dd($tglawalcek, $tgl2);

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

        // dd($queryrekap->get());

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
            $table->double('urutdetail', 15, 2)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->dateTime('tglbukti2')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->unsignedBigInteger('nilaikosongdebet')->nullable();
            $table->unsignedBigInteger('nilaikosongkredit')->nullable();
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

        $querysaldoawalpengeluaran1 = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("isnull(a.alatbayar_id,0) not in(3,4)")
            ->whereRaw("a.tglbukti>=cast(ltrim(rtrim(str(year('" . $dariformat . "'))))+'/'+ltrim(rtrim(str(month('" . $dariformat . "'))))+'/1' as datetime) ")
            ->where('a.tglbukti', '<', $dari)
            ->where('a.bank_id', '=', $bank_id)
            ->first();

        $querysaldoawalpengeluaran2 = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("pencairangiropengeluaranheader as c with (readuncommitted)"), 'a.nobukti', 'c.pengeluaran_nobukti')
            ->whereraw("isnull(a.alatbayar_id,0)  in(3,4)")
            ->whereRaw("c.tglbukti>=cast(ltrim(rtrim(str(year('" . $dariformat . "'))))+'/'+ltrim(rtrim(str(month('" . $dariformat . "'))))+'/1' as datetime) ")
            ->where('c.tglbukti', '<', $dari)
            ->where('a.bank_id', '=', $bank_id)
            ->first();
        $querysaldoawalpengeluaran1 = $querysaldoawalpengeluaran1->nominal ?? 0;
        $querysaldoawalpengeluaran2 = $querysaldoawalpengeluaran2->nominal ?? 0;
        $querysaldoawalpengeluaran = +$querysaldoawalpengeluaran1 + $querysaldoawalpengeluaran2;

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
            ->where('a.statusaktif', 1)
            ->whereRaw("a.id<>" . $bank_id)
            ->first();
        if (isset($bankpengembaliankepusat)) {
            $querysaldoawalpengembaliankepusat1 = DB::table("pengeluaranheader")->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("sum(b.nominal) as nominal")
                )
                ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->whereraw("isnull(a.alatbayar_id,0) not in(3,4)")
                ->whereRaw("a.tglbukti>=cast(ltrim(rtrim(str(year('" . $dariformat . "'))))+'/'+ltrim(rtrim(str(month('" . $dariformat . "'))))+'/1' as datetime) ")
                ->where('a.tglbukti', '<', $dari)
                ->where('a.bank_id', '=', $bankpengembaliankepusat->id)
                ->first();

            $querysaldoawalpengembaliankepusat2 = DB::table("pengeluaranheader")->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("sum(b.nominal) as nominal")
                )
                ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->join(DB::raw("pencairangiropengeluaranheader as c with (readuncommitted)"), 'a.nobukti', 'c.pengeluaran_nobukti')
                ->whereraw("isnull(a.alatbayar_id,0)  in(3,4)")
                ->whereRaw("c.tglbukti>=cast(ltrim(rtrim(str(year('" . $dariformat . "'))))+'/'+ltrim(rtrim(str(month('" . $dariformat . "'))))+'/1' as datetime) ")
                ->where('c.tglbukti', '<', $dari)
                ->where('a.bank_id', '=', $bankpengembaliankepusat->id)
                ->first();
        }
        $saldoawalpengembaliankepusat1 = $querysaldoawalpengembaliankepusat1->nominal ?? 0;
        $saldoawalpengembaliankepusat2 = $querysaldoawalpengembaliankepusat2->nominal ?? 0;
        $saldoawalpengembaliankepusat = $saldoawalpengembaliankepusat1 + $saldoawalpengembaliankepusat2;

        $month = date('m', strtotime($dariformat));
        $year = date('Y', strtotime($dariformat));

        if ($month == 1) {
            $bulan = 12;
            $year = $year - 1;
        } else {
            $bulan = Intval($month) - 1;
        }
        $date = $year . '-' . $bulan . '-01';
        $dariformatsaldo = $date;
        // dd($dariformatsaldo);
        $querysaldoawal = DB::table("saldoawalbank")->from(
            DB::raw("saldoawalbank as a with (readuncommitted)")
        )
            ->select(
                DB::raw("sum(isnull(a.nominaldebet,0)-isnull(a.nominalkredit,0)) as nominal")
            )
            ->whereRaw("cast(right(a.bulan,4)+'/'+left(a.bulan,2)+'/1' as date)<='" . $dariformatsaldo . "'")
            // ->whereRaw("a.bulan<>format(cast('" . $dariformat . "' as date),'MM-yyyy')")
            // ->where('a.tglbukti', '<', $dari)
            ->where('a.bank_id', '=', $bank_id)
            ->first();
        // dd($querysaldoawal->toSql());

        // dd($querysaldoawal->tosql());
        // dd($querysaldoawal->to);
        // dump($querysaldoawal->nominal,$querysaldoawalpenerimaan->nominal,$querysaldoawalpenerimaanpindahbuku->nominal) ;
        // dump('test');
        // dd($querysaldoawalpengeluaran,$querysaldoawalpengeluaranpindahbuku->nominal,$saldoawalpengembaliankepusat);        
        // dd($querysaldoawal->nominal);
        $saldoawal =  ($querysaldoawal->nominal + $querysaldoawalpenerimaan->nominal + $querysaldoawalpenerimaanpindahbuku->nominal) - ($querysaldoawalpengeluaran + $querysaldoawalpengeluaranpindahbuku->nominal + $saldoawalpengembaliankepusat);
        // dd($saldoawal);

        // dd($querysaldoawal->nominal,$querysaldoawalpenerimaan->nominal,$querysaldoawalpenerimaanpindahbuku->nominal,$querysaldoawalpengeluaran->nominal,$querysaldoawalpengeluaranpindahbuku->nominal,$saldoawalpengembaliankepusat);

        // dd($saldoawal);

        // dd($saldoawal);
        // data coba coba

        // dd($saldoawal);

        DB::table($tempsaldo)->insert(
            array(
                'urut' => '1',
                'urutdetail' => '1',
                "coa" => "",
                "tglbukti" => $dari,
                "tglbukti2" => $dari,
                "nobukti" => "SALDO AWAL",
                "keterangan" => "SALDO AWAL",
                "debet" => "0",
                "kredit" => "0",
                "saldo" => $saldoawal ?? 0,
                "nilaikosongdebet" => "1",
                "nilaikosongkredit" => "1",
            )
        );
        //   dd(db::table($tempsaldo)->get());

        $querypenerimaan = DB::table("penerimaanheader")->from(
            DB::raw("penerimaanheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("2 as urut"),
                DB::raw('ROW_NUMBER() OVER (PARTITION BY A.nobukti ORDER BY b.id) as urutdetail'),
                'b.coakredit as coa',
                'a.tglbukti',
                'a.tglbukti as tglbukti2',
                'a.nobukti',
                'b.keterangan',
                // DB::raw("(case when b.nominal>0 then b.nominal else 0 end) as debet "),
                // DB::raw("(case when b.nominal<0 then abs(b.nominal) else 0 end) as kredit "),
                DB::raw("b.nominal  as debet "),
                DB::raw("0  as kredit "),
                DB::raw("0 as saldo"),
                DB::raw("0  as nilaikosongdebet"),
                DB::raw("1  as nilaikosongkredit"),
            )
            ->join(DB::raw("penerimaandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai)
            ->where('a.bank_id', '=', $bank_id)
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc');

        DB::table($tempsaldo)->insertUsing([
            'urut',
            'urutdetail',
            'coa',
            'tglbukti',
            'tglbukti2',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $querypenerimaan);

        $querypenerimaanpindahbuku = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                DB::raw("3 as urut"),
                DB::raw("1 as urutdetail"),
                'a.coakredit as coa',
                'a.tglbukti',
                'a.tglbukti as tglbukti2',
                'a.nobukti',
                'a.keterangan',
                // DB::raw("(case when a.nominal>0 then a.nominal else 0 end) as debet "),
                // DB::raw("(case when a.nominal<0 then abs(a.nominal) else 0 end) as kredit "),
                DB::raw("a.nominal  as debet "),
                DB::raw("0 as kredit "),
                DB::raw("0 as saldo"),
                DB::raw("0  as nilaikosongdebet"),
                DB::raw("1  as nilaikosongkredit"),
            )
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai)
            ->where('a.bankke_id', '=', $bank_id)
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc');

        DB::table($tempsaldo)->insertUsing([
            'urut',
            'urutdetail',
            'coa',
            'tglbukti',
            'tglbukti2',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $querypenerimaanpindahbuku);

        $querypengeluaran = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("4 as urut"),
                DB::raw('ROW_NUMBER() OVER (PARTITION BY A.nobukti ORDER BY b.id) as urutdetail'),
                db::raw("
                (case when '" . $cabang . "' = 'PUSAT' then b.coadebet else 
                (case when b.coakredit='03.02.02.05' then b.coakredit else b.coadebet end)  end)
                as coa"),
                // 'b.coadebet as coa',
                'b.tgljatuhtempo',
                'b.tgljatuhtempo as tglbukti2',
                'a.nobukti',
                'b.keterangan',
                // DB::raw("(case when b.nominal<0 then abs(b.nominal) else 0 end) as debet "),
                // DB::raw("(case when b.nominal>0 then b.nominal else 0 end) as kredit "),
                DB::raw(" 0  as debet "),
                DB::raw("b.nominal  as kredit "),
                DB::raw("0 as saldo"),
                DB::raw("1  as nilaikosongdebet"),
                DB::raw("0  as nilaikosongkredit"),
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("isnull(a.alatbayar_id,0) not in(3,4)")
            ->where('b.tgljatuhtempo', '>=', $dari)
            ->where('b.tgljatuhtempo', '<=', $sampai)
            ->where('a.bank_id', '=', $bank_id)
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc');

        DB::table($tempsaldo)->insertUsing([
            'urut',
            'urutdetail',
            'coa',
            'tglbukti',
            'tglbukti2',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $querypengeluaran);

        $querypengeluaran = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("4 as urut"),
                DB::raw('ROW_NUMBER() OVER (PARTITION BY A.nobukti ORDER BY b.id) as urutdetail'),
                db::raw("
                (case when '" . $cabang . "' = 'PUSAT' then b.coadebet else 
                (case when b.coakredit='03.02.02.05' then b.coakredit else b.coadebet end) end)
                as coa"),

                // 'b.coadebet as coa',
                'c.tglbukti',
                'c.tglbukti as tglbukti2',
                'a.nobukti',
                'b.keterangan',
                // DB::raw("(case when b.nominal<0 then abs(b.nominal) else 0 end) as debet "),
                // DB::raw("(case when b.nominal>0 then b.nominal else 0 end) as kredit "),
                DB::raw(" 0  as debet "),
                DB::raw("b.nominal  as kredit "),
                DB::raw("0 as saldo"),
                DB::raw("1  as nilaikosongdebet"),
                DB::raw("0  as nilaikosongkredit"),
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("pencairangiropengeluaranheader as c with (readuncommitted)"), 'a.nobukti', 'c.pengeluaran_nobukti')
            ->whereraw("isnull(a.alatbayar_id,0) in(3,4)")
            ->where('c.tglbukti', '>=', $dari)
            ->where('c.tglbukti', '<=', $sampai)
            ->where('a.bank_id', '=', $bank_id)
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc');

        DB::table($tempsaldo)->insertUsing([
            'urut',
            'urutdetail',
            'coa',
            'tglbukti',
            'tglbukti2',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $querypengeluaran);
        if (isset($bankpengembaliankepusat)) {
            $querypengeluaran = DB::table("pengeluaranheader")->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
                ->select(
                    DB::raw("5 as urut"),
                    DB::raw('ROW_NUMBER() OVER (PARTITION BY A.nobukti ORDER BY b.id) as urutdetail'),
                    db::raw("
                              (case when '" . $cabang . "' = 'PUSAT' then b.coadebet else 
                        (case when b.coakredit='03.02.02.05' then b.coakredit else b.coadebet end)  end)
                        as coa"),

                    // 'b.coadebet as coa',
                    'b.tgljatuhtempo as tglbukti',
                    'b.tgljatuhtempo as  tglbukti2',
                    'a.nobukti',
                    'b.keterangan',
                    // DB::raw("(case when b.nominal<0 then abs(b.nominal) else 0 end) as debet "),
                    // DB::raw("(case when b.nominal>0 then b.nominal else 0 end) as kredit "),
                    DB::raw("0  as debet "),
                    DB::raw("b.nominal  as kredit "),
                    DB::raw("0 as saldo"),
                    DB::raw("1  as nilaikosongdebet"),
                    DB::raw("0  as nilaikosongkredit"),
                )
                ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->where('b.tgljatuhtempo', '>=', $dari)
                ->where('b.tgljatuhtempo', '<=', $sampai)
                ->where('a.bank_id', '=', $bankpengembaliankepusat->id)
                ->orderBy('b.tgljatuhtempo', 'Asc')
                ->orderBy('a.nobukti', 'Asc');

            // dd($bankpengembaliankepusat->id);
            DB::table($tempsaldo)->insertUsing([
                'urut',
                'urutdetail',
                'coa',
                'tglbukti',
                'tglbukti2',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'nilaikosongdebet',
                'nilaikosongkredit'
            ], $querypengeluaran);
        }

        $querypengeluaranpindahbuku = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                DB::raw("6 as urut"),
                DB::raw("1 as urutdetail"),
                'a.coadebet as coa',
                'a.tglbukti',
                'a.tglbukti as tglbukti2',
                'a.nobukti',
                'a.keterangan',
                // DB::raw("(case when a.nominal<0 then abs(a.nominal) else 0 end) as debet "),
                // DB::raw("(case when a.nominal>0 then a.nominal else 0 end) as kredit "),
                DB::raw(" 0 as debet "),
                DB::raw("a.nominal  as kredit "),
                DB::raw("0 as saldo"),
                DB::raw("1  as nilaikosongdebet"),
                DB::raw("0  as nilaikosongkredit"),
            )
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai)
            ->where('a.bankdari_id', '=', $bank_id)
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.nobukti', 'Asc');

        DB::table($tempsaldo)->insertUsing([
            'urut',
            'urutdetail',
            'coa',
            'tglbukti',
            'tglbukti2',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit'
        ], $querypengeluaranpindahbuku);


        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->id();
            $table->double('urut', 15, 2)->nullable();
            $table->double('urutdetail', 15, 2)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->dateTime('tglbukti2')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->unsignedBigInteger('nilaikosongdebet')->nullable();
            $table->unsignedBigInteger('nilaikosongkredit')->nullable();
        });

        // dd(db::table($tempsaldo)->get());
        $query = DB::table($tempsaldo)->from(
            $tempsaldo . " as a"
        )
            ->select(
                'a.urut',
                'a.urutdetail',
                'a.coa',
                'a.tglbukti',
                'a.tglbukti as tglbukti2',
                'a.nobukti',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                'a.saldo',
                'a.nilaikosongdebet',
                'a.nilaikosongkredit',
            )
            ->orderBy('a.tglbukti', 'Asc')
            ->orderBy('a.urut', 'Asc')
            ->orderBy('a.id', 'Asc');


        // dd($query->get());

        DB::table($temprekap)->insertUsing([
            'urut',
            'urutdetail',
            'coa',
            'tglbukti',
            'tglbukti2',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'nilaikosongdebet',
            'nilaikosongkredit',
        ], $query);


        $querykasbank = DB::table('bank')->from(
            DB::raw("bank as a with (readuncommitted)")
        )
            ->select(
                'a.namabank',
                'a.tipe'
            )
            ->where('a.id', '=', $bank_id)
            ->first();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        // dd(db::table($tempsaldo)->orderby('id','asc')->get());



        $tempnominal = '##tempnominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempnominal, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('totaldebet', 15, 2)->nullable();
            $table->double('totalkredit', 15, 2)->nullable();
        });

        $querynominal = DB::table($tempsaldo)->from(
            $tempsaldo . " as a"
        )
            ->select(
                'a.nobukti',
                DB::raw("sum(a.debet) as totaldebet"),
                DB::raw("sum(a.kredit) as totalkredit"),
            )
            ->groupBy('a.nobukti');
        DB::table($tempnominal)->insertUsing([
            'nobukti',
            'totaldebet',
            'totalkredit',
        ], $querynominal);


        $count = db::table($temprekap)->count();


        // dd(db::table($temprekap)->get());

        if ($prosesneraca == 1) {
            $queryhasil = DB::table($temprekap)->from(
                $tempsaldo . " as a"
            )
                ->select(
                    'a.urut',
                    'a.urutdetail',
                    DB::raw("isnull(b.keterangancoa,'') as keterangancoa"),
                    DB::raw("'" . $querykasbank->namabank . "' as namabank"),
                    DB::raw("(case when year(isnull(a.tglbukti,'1900/1/1')) < '2000' then '" . $dari . "' else a.tglbukti end) as tglbukti"),
                    'a.tglbukti2',
                    'a.nobukti',
                    'a.keterangan',
                    'a.debet',
                    'a.kredit',
                    'c.totaldebet',
                    'c.totalkredit',
                    DB::raw("sum ((isnull(a.saldo,0)+isnull(a.debet,0))-isnull(a.Kredit,0)) over (order by a.tglbukti,a.id) as saldo"),
                    DB::raw("'Laporan Buku " . ucwords(strtolower($querykasbank->tipe)) . "' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul"),
                    DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                    DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
                )
                ->leftjoin(DB::raw("akunpusat as b with (readuncommitted)"), 'a.coa', 'b.coa')
                ->leftjoin(DB::raw("$tempnominal as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                ->orderBy('a.tglbukti2', 'Asc')
                ->orderBy('a.id', 'Asc');
            $data = $queryhasil;
            // dd($data->get());
        } else {
            // if ( $count>1) {
            //     $queryhasil->whereraw("a.nobukti not in ('SALDO AWAL')");
            // }
            // dd(db::table($temprekap)->get());
            if ($cabang == 'PUSAT') {

                $queryhasil = DB::table($temprekap)->from(
                    $tempsaldo . " as a"
                )
                    ->select(
                        'a.id',
                        'a.urut',
                        'a.urutdetail',
                        DB::raw("isnull(b.keterangancoa,'') as keterangancoa"),
                        DB::raw("'" . $querykasbank->namabank . "' as namabank"),
                        DB::raw("(case when year(isnull(a.tglbukti,'1900/1/1')) < '2000' then '" . $dari . "' else 
                    format(a.tglbukti,'dd-')+
                    (case when month(a.tglbukti)=1 then 'JAN'
                          when month(a.tglbukti)=2 then 'FEB'
                          when month(a.tglbukti)=3 then 'MAR'
                          when month(a.tglbukti)=4 then 'APR'
                          when month(a.tglbukti)=5 then 'MAY'
                          when month(a.tglbukti)=6 then 'JUN'
                          when month(a.tglbukti)=7 then 'JUL'
                          when month(a.tglbukti)=8 then 'AGU'
                          when month(a.tglbukti)=9 then 'SEP'
                          when month(a.tglbukti)=10 then 'OKT'
                          when month(a.tglbukti)=11 then 'NOV'
                          when month(a.tglbukti)=12 then 'DES' ELSE '' END)

                    +format(a.tglbukti,'-yy') 
                     end) as tglbukti"),
                        DB::raw("a.tglbukti as tglbukti2"),
                        'a.nobukti',
                        'a.keterangan',
                        'a.debet',
                        'a.kredit',
                        'a.nilaikosongdebet',
                        'a.nilaikosongkredit',
                        'c.totaldebet',
                        'c.totalkredit',
                        //     DB::raw("sum ((isnull(a.saldo,0)+
                        // (case when isnull(a.urutdetail,0)=1 then  isnull(c.totaldebet,0) else 0 end)
                        // )-
                        // (case when isnull(a.urutdetail,0)=1 then  isnull(c.totalkredit,0) else 0 end)
                        // ) over (order by a.tglbukti,a.urut,a.nobukti,a.id) as saldo"),

                        DB::raw("sum ((isnull(a.saldo,0)+isnull(a.debet,0))-isnull(a.Kredit,0)) over (order by a.tglbukti,a.urut,a.nobukti,a.id) as saldo"),
                        DB::raw("'Laporan Buku " . ucwords(strtolower($querykasbank->tipe)) . "' as judulLaporan"),
                        DB::raw("'" . $getJudul->text . "' as judul"),
                        DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                        DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
                    )
                    ->leftjoin(DB::raw("akunpusat as b with (readuncommitted)"), 'a.coa', 'b.coa')
                    ->leftjoin(DB::raw("$tempnominal as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                    ->orderBy('a.tglbukti2', 'Asc')
                    ->orderBy('a.urut', 'Asc')
                    ->orderBy('a.nobukti', 'Asc')
                    ->orderBy('a.id', 'Asc');
            } else {

                $queryhasil = DB::table($temprekap)->from(
                    $tempsaldo . " as a"
                )
                    ->select(
                        'a.id',
                        'a.urut',
                        'a.urutdetail',
                        DB::raw("isnull(b.keterangancoa,'') as keterangancoa"),
                        DB::raw("'" . $querykasbank->namabank . "' as namabank"),
                        DB::raw("(case when year(isnull(a.tglbukti,'1900/01/01')) < '2000' then CAST('" . $dari . "' AS DATE) else CAST(a.tglbukti AS DATE) end) as tglbukti"),
                        DB::raw("a.tglbukti2"),
                        'a.nobukti',
                        'a.keterangan',
                        'a.debet',
                        'a.kredit',
                        'a.nilaikosongdebet',
                        'a.nilaikosongkredit',
                        'c.totaldebet',
                        'c.totalkredit',
                        DB::raw("sum ((isnull(a.saldo,0)+isnull(a.debet,0))-isnull(a.Kredit,0)) over (order by a.tglbukti,a.urut,a.nobukti,a.id) as saldo"),
                        DB::raw("'Laporan Buku " . ucwords(strtolower($querykasbank->tipe)) . "' as judulLaporan"),
                        DB::raw("'" . $getJudul->text . "' as judul"),
                        DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                        DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
                    )
                    ->leftjoin(DB::raw("akunpusat as b with (readuncommitted)"), 'a.coa', 'b.coa')
                    ->leftjoin(DB::raw("$tempnominal as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                    ->orderBy('a.tglbukti2', 'Asc')
                    ->orderBy('a.urut', 'Asc')
                    ->orderBy('a.nobukti', 'Asc')
                    ->orderBy('a.id', 'Asc');
            }
            // dd($queryhasil->get());

            $dataSaldo = [
                'urut' => '1',
                'urutdetail' => '1',
                "coa" => "",
                "tglbukti" =>  $dari,
                "nobukti" => "SALDO AWAL",
                "keterangan" => "SALDO AWAL",
                "debet" => "0",
                "kredit" => "0",
                "saldo" => $saldoawal ?? 0,
            ];
            $data = [
                "data" => $queryhasil->get(),
                "dataSaldo" => $dataSaldo,
            ];

            // dd($data);
        }


        return $data;
    }
}
