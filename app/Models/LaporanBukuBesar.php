<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanBukuBesar extends MyModel
{
    use HasFactory;

    protected $table = 'jurnalumumheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];


    public function getReport()
    {
        $dari = date('Y-m-d', strtotime(request()->dari)) ?? '1900/1/1';
        $sampai = date('Y-m-d', strtotime(request()->sampai)) ?? '1900/1/1';
        $coadari_id = request()->coadari_id ?? '0';
        $coasampai_id = request()->coasampai_id ?? '0';


        // dd('test');
        $tempsaldo = '##tempsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldo, function ($table) {
            $table->double('urut', 15, 2)->default(0);
            $table->string('coa', 1000)->default('');
            $table->dateTime('tglbukti')->default('1900/1/1');
            $table->string('nobukti', 100)->default('');
            $table->longText('keterangan')->default('');
            $table->double('debet', 15, 2)->default(0);
            $table->double('kredit', 15, 2)->default(0);
            $table->double('saldo', 15, 2)->default(0);
        });


        $querysaldoawal = DB::table("jurnalumumpusatheader")->from(
            DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("1 as urut"),
                'b.coa',
                DB::raw("'1900/1/1' as tglbukti"),
                DB::raw("'' as nobukti"),
                DB::raw("'SALDO AWAL' as keterangan"),
                DB::raw("0 as debet"),
                DB::raw("0 as kredit"),
                DB::raw("sum(b.nominal) as saldo")
            )
            ->join(DB::raw("jurnalumumpusatdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("akunpusat as c with(readuncommitted)"), 'b.coa', 'c.coa')
            ->where('a.tglbukti', '<', $dari)
            ->whereRaw("(c.id >=" . $coadari_id)
            ->whereRaw(DB::raw("c.id <=" . $coadari_id . ")"))
            ->groupBy('b.coa');

            // dd($querysaldoawal->get());


   
        DB::table($tempsaldo)->insertUsing([
            'urut',
            'coa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querysaldoawal);

        $querysaldoawal = DB::table("akunpusat")->from(
            DB::raw("akunpusat as a with (readuncommitted)")
        )
            ->select(
                DB::raw("1 as urut"),
                'a.coa',
                DB::raw("'1900/1/1' as tglbukti"),
                DB::raw("'' as nobukti"),
                DB::raw("'SALDO AWAL' as keterangan"),
                DB::raw("0 as debet"),
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo")
            )
            ->leftjoin(DB::raw($tempsaldo)." as b",'a.coa','b.coa')
            ->whereRaw("(a.id >=" . $coadari_id)
            ->whereRaw(DB::raw("a.id <=" . $coadari_id . ")"))
            ->whereRaw("isnull(B.coa,'')=''");

            DB::table($tempsaldo)->insertUsing([
                'urut',
                'coa',
                'tglbukti',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
            ], $querysaldoawal);


        $tempdetail = '##tempdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdetail, function ($table) {
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

        $querydetail = DB::table("jurnalumumpusatheader")->from(
            DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("2 as urut"),
                'b.coa',
                DB::raw("a.tglbukti"),
                DB::raw("a.nobukti as nobukti"),
                DB::raw("b.keterangan as keterangan"),
                DB::raw("(case when nominal>=0 then nominal else 0 end) as debet"),
                DB::raw("(case when nominal<0 then abs(nominal) else 0 end) as kredit"),
                DB::raw("0 as saldo")
            )
            ->join(DB::raw("jurnalumumpusatdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("akunpusat as c with(readuncommitted)"), 'b.coa', 'c.coa')
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai)
            ->where('c.id', '>=', $coadari_id)
            ->where('c.id', '<=', $coasampai_id)
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('b.nominal', 'desc')
            ->orderBy('a.nobukti', 'asc')
            ->orderBy('b.id', 'asc');


            //    dd($querydetail->get());
        DB::table($tempdetail)->insertUsing([
            'urut',
            'coa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querydetail);




        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->id();
            $table->unsignedBigInteger('urut')->default(0);
            $table->string('coa', 1000)->default('');
            $table->dateTime('tglbukti')->default('1900/1/1');
            $table->string('nobukti', 100)->default('');
            $table->longText('keterangan')->default('');
            $table->double('debet', 15, 2)->default(0);
            $table->double('kredit', 15, 2)->default(0);
            $table->double('saldo', 15, 2)->default(0);
        });

        $queryRekap1 = DB::table($tempsaldo)
            ->select(
                'urut',
                'coa',
                'tglbukti',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
            );


        DB::table($temprekap)->insertUsing([
            'urut',
            'coa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $queryRekap1);

        $queryRekap = DB::table($tempdetail)
            ->select(
                'urut',
                'coa',
                'tglbukti',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
            )->orderBy('id','asc');
            

        DB::table($temprekap)->insertUsing([
            'urut',
            'coa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $queryRekap);

        $queryRekap = DB::table($temprekap)
            ->select(
                'id',
                'urut',
                'coa',
                DB::raw("(case when year(tglbukti)=1900 then null else tglbukti end) as tglbukti"),
                'nobukti',
                'keterangan',
                DB::raw("(case when debet=0 then null else debet end) as debet"),
                DB::raw("(case when kredit=0 then null else kredit end) as kredit"),
                DB::raw("sum ((isnull(saldo,0)+debet)-Kredit) over (order by id asc) as Saldo")
            )
            ->orderBy('id', 'Asc');


        // dd($querysaldoawal->ToSql());
        // dd($queryRekap->get());

        // $query = DB::table('jurnalumumdetail AS A')
        //     ->from(
        //         DB::raw("jurnalumumdetail AS A with (readuncommitted)")
        //     )
        //     ->select(['A.nominal as debet', 'b.nominal as kredit', 'A.nominal as saldo', 'A.keterangan', 'jurnalumumheader.nobukti', 'jurnalumumheader.tglbukti'])
        //     ->leftJoin(
        //         DB::raw("(SELECT baris,nobukti,nominal FROM jurnalumumpusatdetail with (readuncommitted) WHERE nominal<0) B"),
        //         function ($join) {
        //             $join->on('A.baris', '=', 'B.baris');
        //         }
        //     )
        //     ->leftJoin(DB::raw("jurnalumumheader with (readuncommitted)"), 'jurnalumumheader.nobukti', 'A.nobukti')
        //     ->whereRaw("A.nobukti = B.nobukti")
        //     ->whereRaw("A.nominal >= 0");

        $data = $queryRekap->get();
        return $data;
    }
}
