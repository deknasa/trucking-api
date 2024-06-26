<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanArusKas extends MyModel
{
    use HasFactory;

    protected $table = 'laporanaruskas';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    public function getReport($periode)
    {

        $cabang_id = 1;
        $blnpilih = substr($periode, 0, 2);
        $thnpilih = substr($periode, -4);
        if ($blnpilih == 1) {
            $blnbefore = 12;
            $thnbefore = $thnpilih - 1;
        } else {
            $blnbefore = $blnpilih - 1;
            $thnbefore = $thnpilih;
        }

        $parameter = new Parameter();
        $saldoawal = $parameter->cekText('SALDO AWAL ARUS KAS', 'SALDO AWAL ARUS KAS') ?? '0';

        $saldodebetbeforesaldo = db::table("penerimaanheader")->from(db::raw("penerimaanheader a with (readuncommitted)"))
            ->select(
                db::raw("sum(b.nominal) as nominal")
            )
            ->join(db::raw("penerimaandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti<cast(trim(str(" . $thnbefore . "))+'/'+trim(str(" . $blnbefore . "))+'/1' as datetime)")
            ->first()
            ->nominal ?? 0;

        $saldokreditbeforesaldo = db::table("pengeluaranheader")->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                db::raw("sum(b.nominal*-1) as nominal")
            )
            ->join(db::raw("pengeluarandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti<cast(trim(str(" . $thnbefore . "))+'/'+trim(str(" . $blnbefore . "))+'/1' as datetime)")
            ->first()
            ->nominal ?? 0;

        $saldobefore = $saldoawal + $saldokreditbeforesaldo + $saldodebetbeforesaldo;

        $saldodebetpilihsaldo = db::table("penerimaanheader")->from(db::raw("penerimaanheader a with (readuncommitted)"))
            ->select(
                db::raw("sum(b.nominal) as nominal")
            )
            ->join(db::raw("penerimaandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti<cast(trim(str(" . $thnpilih . "))+'/'+trim(str(" . $blnpilih . "))+'/1' as datetime)")
            ->first()
            ->nominal ?? 0;

        $saldokreditpilihsaldo = db::table("pengeluaranheader")->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                db::raw("sum(b.nominal*-1) as nominal")
            )
            ->join(db::raw("pengeluarandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti<cast(trim(str(" . $thnpilih . "))+'/'+trim(str(" . $blnpilih . "))+'/1' as datetime)")
            ->first()
            ->nominal ?? 0;

        $saldopilih = $saldoawal + $saldokreditpilihsaldo + $saldodebetpilihsaldo;


        $temprekapdata = '##temprekapdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapdata, function ($table) {
            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();
            $table->string('coa', 50)->nullable();
            $table->integer('order')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $queryrekap = db::table("penerimaanheader")->from(db::raw("penerimaanheader a with (readuncommitted)"))
            ->select(
                db::raw("month(a.tglbukti) as bulan"),
                db::raw("year(a.tglbukti) as tahun"),
                'b.coakredit as coa',
                db::raw("sum(B.nominal) as nominal"),
                db::raw("1 as [order]"),
            )
            ->join(db::raw("penerimaandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("month(a.tglbukti)=" . $blnbefore)
            ->whereraw("year(a.tglbukti)=" . $thnbefore)
            ->groupBy(db::raw("month(a.tglbukti)"))
            ->groupBy(db::raw("year(a.tglbukti)"))
            ->groupBy('b.coakredit');

        DB::table($temprekapdata)->insertUsing([
            'bulan',
            'tahun',
            'coa',
            'nominal',
            'order',
        ], $queryrekap);

        $queryrekap = db::table("pengeluaranheader")->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                db::raw("month(a.tglbukti) as bulan"),
                db::raw("year(a.tglbukti) as tahun"),
                'b.coadebet as coa',
                db::raw("sum(B.nominal*-1) as nominal"),
                db::raw("2 as [order]"),
            )
            ->join(db::raw("pengeluarandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("month(a.tglbukti)=" . $blnbefore)
            ->whereraw("year(a.tglbukti)=" . $thnbefore)
            ->groupBy(db::raw("month(a.tglbukti)"))
            ->groupBy(db::raw("year(a.tglbukti)"))
            ->groupBy('b.coadebet');

        DB::table($temprekapdata)->insertUsing([
            'bulan',
            'tahun',
            'coa',
            'nominal',
            'order',
        ], $queryrekap);
        // 

        $queryrekap = db::table("penerimaanheader")->from(db::raw("penerimaanheader a with (readuncommitted)"))
            ->select(
                db::raw("month(a.tglbukti) as bulan"),
                db::raw("year(a.tglbukti) as tahun"),
                'b.coakredit as coa',
                db::raw("sum(B.nominal) as nominal"),
                db::raw("1 as [order]"),
            )
            ->join(db::raw("penerimaandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("month(a.tglbukti)=" . $blnpilih)
            ->whereraw("year(a.tglbukti)=" . $thnpilih)
            ->groupBy(db::raw("month(a.tglbukti)"))
            ->groupBy(db::raw("year(a.tglbukti)"))
            ->groupBy('b.coakredit');

        DB::table($temprekapdata)->insertUsing([
            'bulan',
            'tahun',
            'coa',
            'nominal',
            'order',
        ], $queryrekap);

        $queryrekap = db::table("pengeluaranheader")->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                db::raw("month(a.tglbukti) as bulan"),
                db::raw("year(a.tglbukti) as tahun"),
                'b.coadebet as coa',
                db::raw("sum(B.nominal*-1) as nominal"),
                db::raw("2 as [order]"),
            )
            ->join(db::raw("pengeluarandetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("month(a.tglbukti)=" . $blnpilih)
            ->whereraw("year(a.tglbukti)=" . $thnpilih)
            ->groupBy(db::raw("month(a.tglbukti)"))
            ->groupBy(db::raw("year(a.tglbukti)"))
            ->groupBy('b.coadebet');

        DB::table($temprekapdata)->insertUsing([
            'bulan',
            'tahun',
            'coa',
            'nominal',
            'order',
        ], $queryrekap);


        $temprekapdatabefore = '##temprekapdatabefore' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapdatabefore, function ($table) {
            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();
            $table->string('coa', 50)->nullable();
            $table->integer('order')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $temprekapdatakini = '##temprekapdatakini' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapdatakini, function ($table) {
            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();
            $table->string('coa', 50)->nullable();
            $table->integer('order')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $queryrekapbefore = db::table($temprekapdata)->from(db::raw($temprekapdata . " a"))
            ->select(
                'a.bulan',
                'a.tahun',
                'a.coa',
                db::raw("sum(a.nominal) as nominal"),
                'a.order',
            )
            ->whereraw("a.bulan=" . $blnbefore)
            ->whereraw("a.tahun=" . $thnbefore)
            ->groupBy('a.bulan')
            ->groupBy('a.tahun')
            ->groupBy('a.coa')
            ->groupBy('a.order');

        DB::table($temprekapdatabefore)->insertUsing([
            'bulan',
            'tahun',
            'coa',
            'nominal',
            'order',
        ], $queryrekapbefore);


        $queryrekapkini = db::table($temprekapdata)->from(db::raw($temprekapdata . " a"))
            ->select(
                'a.bulan',
                'a.tahun',
                'a.coa',
                db::raw("sum(a.nominal) as nominal"),
                'a.order',
            )
            ->whereraw("a.bulan=" . $blnpilih)
            ->whereraw("a.tahun=" . $thnpilih)
            ->groupBy('a.bulan')
            ->groupBy('a.tahun')
            ->groupBy('a.coa')
            ->groupBy('a.order');

        DB::table($temprekapdatakini)->insertUsing([
            'bulan',
            'tahun',
            'coa',
            'nominal',
            'order',
        ], $queryrekapkini);

        $tempcoa = '##tempcoa' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempcoa, function ($table) {
            $table->string('coa', 50)->nullable();
            $table->string('keterangancoa', 50)->nullable();
            $table->integer('order')->nullable();
        });


        $querycoa = db::table("akunpusat")->from(db::raw("akunpusat a"))
            ->select(
                'a.coa',
                'a.keterangancoa',
                db::raw("1 as [order]"),
            );

        DB::table($tempcoa)->insertUsing([
            'coa',
            'keterangancoa',
            'order',
        ], $querycoa);


        $querycoa = db::table("akunpusat")->from(db::raw("akunpusat a"))
            ->select(
                'a.coa',
                'a.keterangancoa',
                db::raw("2 as [order]"),
            );

        DB::table($tempcoa)->insertUsing([
            'coa',
            'keterangancoa',
            'order',
        ], $querycoa);

        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasil, function ($table) {
            $table->id();
            $table->string('coa', 50)->nullable();
            $table->string('keterangancoa', 50)->nullable();
            $table->integer('orderbefore')->nullable();
            $table->integer('bulanbefore')->nullable();
            $table->integer('tahunbefore')->nullable();
            $table->double('nominalbefore', 15, 2)->nullable();
            $table->integer('order')->nullable();
            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('saldobefore', 15, 2)->nullable();
            $table->double('saldopilih', 15, 2)->nullable();
        });

        $queryhasil = db::table($tempcoa)->from(db::raw($tempcoa . " a "))
            ->select(
                'a.coa',
                'a.keterangancoa',
                'b.order as orderbefore',
                db::raw($blnbefore . " as bulanbefore"),
                db::raw($thnbefore . " as tahunbefore"),
                db::raw("isnull(b.nominal,0) as nominalbefore"),
                'c.order as order',
                db::raw($blnpilih . " as bulan"),
                db::raw($thnpilih . " as tahun"),
                db::raw("isnull(c.nominal,0) as nominal"),
                db::raw($saldobefore . " as saldobefore"),
                db::raw($saldopilih . " as saldopilih"),
            )
            ->leftjoin(DB::raw($temprekapdatabefore . " as b"), function ($join) {
                $join->on('a.coa', '=', 'b.coa');
                $join->on('a.order', '=', 'b.order');
            })
            ->leftjoin(DB::raw($temprekapdatakini . " as c"), function ($join) {
                $join->on('a.coa', '=', 'c.coa');
                $join->on('a.order', '=', 'c.order');
            })
            ->whereraw("isnull(b.coa,'')<>'' or isnull(c.coa,'')<>''")
            ->orderBy('a.keterangancoa', 'asc');

        DB::table($temphasil)->insertUsing([
            'coa',
            'keterangancoa',
            'orderbefore',
            'bulanbefore',
            'tahunbefore',
            'nominalbefore',
            'order',
            'bulan',
            'tahun',
            'nominal',
            'saldobefore',
            'saldopilih',
        ], $queryhasil);

        $query = db::table($temphasil)->from(db::raw($temphasil . " a"))
            ->select(
                'a.coa',
                'a.keterangancoa',
                db::raw("(case when a.bulanbefore=1 then 'JAN' 
                                when a.bulanbefore=2 then 'FEB'
                                when a.bulanbefore=3 then 'MAR'
                                when a.bulanbefore=4 then 'APR'
                                when a.bulanbefore=5 then 'MAY'
                                when a.bulanbefore=6 then 'JUN'
                                when a.bulanbefore=7 then 'JUL'
                                when a.bulanbefore=8 then 'AGU'
                                when a.bulanbefore=9 then 'SEP'
                                when a.bulanbefore=10 then 'OKT'
                                when a.bulanbefore=11 then 'NOV'
                                when a.bulanbefore=12 then 'DES'
                                ELSE '' end) +' '+trim(str(a.tahunbefore)) as periodeawal
                "),
                db::raw("(case when a.bulan=1 then 'JAN' 
                                when a.bulan=2 then 'FEB'
                                when a.bulan=3 then 'MAR'
                                when a.bulan=4 then 'APR'
                                when a.bulan=5 then 'MAY'
                                when a.bulan=6 then 'JUN'
                                when a.bulan=7 then 'JUL'
                                when a.bulan=8 then 'AGU'
                                when a.bulan=9 then 'SEP'
                                when a.bulan=10 then 'OKT'
                                when a.bulan=11 then 'NOV'
                                when a.bulan=12 then 'DES'
                                ELSE '' end) +' '+trim(str(a.tahun)) as periodeakhir
                "),                
                'a.nominalbefore as nominalawal',
                db::raw("(case when a.[orderbefore]=1 then 'ARUS KAS/BANK MASUK' else  'ARUS KAS/BANK KELUAR' end) as jenisarus"),
                db::raw("(case when a.[orderbefore]=1 then 'PENDAPATAN' else  'BIAYA' end) as type"),
                'a.nominal as nominalakhir',
                'a.saldobefore',
                'a.saldopilih',
                db::raw("'Laporan Arus Kas / Bank Periode ".$periode."' as judullaporan"),
                db::raw("'PT. TRANSPORINDO AGUNG SEJAHTERA' as judul"),
                db::raw("'Tgl Cetak: " . date('d-m-Y H:i:s'). "' as tglcetak"),
                db::raw("'User: " . auth('api')->user()->name. "' as usercetak"),
            )
            // ->where('a.order',1)
            ->orderby('a.id', 'asc');

            // dd($query->get());

            $dataSaldo = [

                "keterangancoa" => "SALDO AWAL",
                "nominalawal" => $saldobefore,
                "nominalakhir" => $saldopilih,
            ];
            $data = [
                "data" => $query->get(),
                "dataSaldo" => $dataSaldo,
            ];            

        return $data;

        // 

         
    }
}
