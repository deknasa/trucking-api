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



    public function getReport($dari, $sampai, $bank_id)
    {

        $dari = date('Y-m-d', strtotime(request()->dari)) ?? '1900/1/1';
        $sampai = date('Y-m-d', strtotime(request()->sampai)) ?? '1900/1/1';
        $bank_id = request()->bank_id ?? '0';

        $tempsaldo = '##tempsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldo, function ($table) {
            $table->id();
            $table->double('urut', 15, 2)->default(0);
            $table->string('coa', 1000)->default('');
            $table->dateTime('tglbukti')->default('1900/1/1');
            $table->string('nobukti', 100)->default('');
            $table->longText('keterangan')->default('');
            $table->double('debet', 15, 2)->default(0);
            $table->double('kredit', 15, 2)->default(0);
            $table->double('saldo', 15, 2)->default(0);
        });



        $querysaldoawalpenerimaan = DB::table("penerimaanheader")->from(
            DB::raw("penerimaanheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("penerimaandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.tglbukti', '<', $dari)
            ->where('a.bank_id', '=', $bank_id)
            ->first();

        $querysaldoawalpengeluaran = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("sum(b.nominal) as nominal")
            )
            ->join(DB::raw("pengeluarandetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.tglbukti', '<', $dari)
            ->where('a.bank_id', '=', $bank_id)
            ->first();

        $saldoawal = $querysaldoawalpenerimaan->nominal - $querysaldoawalpengeluaran->nominal;

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

        $querypengeluaran = DB::table("pengeluaranheader")->from(
            DB::raw("pengeluaranheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("3 as urut"),
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

        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->id();
            $table->double('urut', 15, 2)->default(0);
            $table->string('coa', 1000)->default('');
            $table->dateTime('tglbukti')->default('1900/1/1');
            $table->string('nobukti', 100)->default('');
            $table->longText('keterangan')->default('');
            $table->double('debet', 15, 2)->default(0);
            $table->double('kredit', 15, 2)->default(0);
            $table->double('saldo', 15, 2)->default(0);
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

        // dd($querykasbank);

        $queryhasil = DB::table($temprekap)->from(
            $tempsaldo . " as a"
        )
            ->select(
                'a.urut',
                DB::raw("isnull(b.keterangancoa,'') as keterangancoa"),
                DB::raw("'" . $querykasbank->namabank . "' as namabank"),
                'a.tglbukti',
                'a.nobukti',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(a.saldo,0)+a.debet)-a.Kredit) over (order by a.id asc) as saldo")
            )
            ->leftjoin(DB::raw("akunpusat as b with (readuncommitted)"), 'a.coa', 'b.coa')
            ->orderBy('a.id', 'Asc');


        $data = $queryhasil->get();
        return $data;
    }
}
