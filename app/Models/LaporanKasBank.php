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

        $tglsaldo = '2023-10-01';
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



        if ($awalcek <= $awalsaldo) {
            $awalcek = $awalsaldo;
        }

        $tglawalcek = $awalcek;
        $tglakhircek = $akhircek;
        $bulan1 = date('m-Y', strtotime($awalcek));
        $bulan2 = date('m-Y', strtotime('1900-01-01'));
        // dd($bulan1);
        while ($awalcek <= $akhircek) {
            $bulan1 = date('m-Y', strtotime($awalcek));
            if ($bulan1 != $bulan2) {
                DB::delete(DB::raw("delete saldoawalbank from saldoawalbank as a WHERE isnull(a.bulan,'')='" . $bulan1 . "'"));
            }

            $awalcek = date('Y-m-d', strtotime($awalcek . ' +1 day'));
            $awalcek2 = date('Y-m-d', strtotime($awalcek . ' +1 day'));
            $bulan2 = date('m-Y', strtotime($awalcek2));
        }


        $tempsaldoawal = '##tempsaldoawal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawal, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
        });

        $tempsaldoawaldebet = '##tempsaldoawaldebet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawaldebet, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $tempsaldoawalkredit = '##tempsaldoawalkredit' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldoawalkredit, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        // penerimaan
        $querydebet = DB::table("penerimaanheader")->from(
            DB::raw("penerimaanheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("a.bank_id"),
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("penerimaandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tglakhircek . "'")
            ->groupby('a.bank_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));


        DB::table($tempsaldoawaldebet)->insertUsing([
            'bulan',
            'bank_id',
            'nominal',
        ], $querydebet);

        $querydebet = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                DB::raw("a.bankke_id as bank_id"),
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("sum(a.nominal) as nominal")
            )
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tglakhircek . "'")
            ->groupby('a.bankke_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));


        DB::table($tempsaldoawaldebet)->insertUsing([
            'bulan',
            'bank_id',
            'nominal',
        ], $querydebet);

        // pengeluaran

        $querykredit = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("a.bank_id"),
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tglakhircek . "'")
            ->groupby('a.bank_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));


        DB::table($tempsaldoawalkredit)->insertUsing([
            'bulan',
            'bank_id',
            'nominal',
        ], $querykredit);

        $querykredit = DB::table("pindahbuku")->from(
            DB::raw("pindahbuku as a with (readuncommitted)")
        )
            ->select(
                DB::raw("a.bankke_id as bank_id"),
                db::raw("format(a.tglbukti,'MM-yyyy') as bulan"),
                DB::raw("sum(a.nominal) as nominal")
            )
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tglakhircek . "'")
            ->groupby('a.bankke_id')
            ->groupby(db::raw("format(a.tglbukti,'MM-yyyy')"));


        DB::table($tempsaldoawalkredit)->insertUsing([
            'bulan',
            'bank_id',
            'nominal',
        ], $querykredit);

        // 

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


        $saldoawal =  ($querysaldoawal->nominal + $querysaldoawalpenerimaan->nominal + $querysaldoawalpenerimaanpindahbuku->nominal) - ($querysaldoawalpengeluaran->nominal + $querysaldoawalpengeluaranpindahbuku->nominal + $saldoawalpengembaliankepusat);

        dd($saldoawal);
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
                'b.nominal as debet',
                DB::raw("0 as kredit"),
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
                'a.nominal as debet',
                DB::raw("0 as kredit"),
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
                DB::raw("0 as debet"),
                'b.nominal as kredit',
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
                    DB::raw("0 as debet"),
                    'b.nominal as kredit',
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
                DB::raw("0 as debet"),
                'a.nominal as kredit',
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
                DB::raw("sum ((isnull(a.saldo,0)+a.debet)-a.Kredit) over (order by a.id asc) as saldo"),
                DB::raw("'Laporan Kas/Bank' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftjoin(DB::raw("akunpusat as b with (readuncommitted)"), 'a.coa', 'b.coa')
            ->orderBy('a.id', 'Asc');

        if ($prosesneraca == 1) {
            $data = $queryhasil;
        } else {
            $data = $queryhasil->get();
        }


        return $data;
    }
}
