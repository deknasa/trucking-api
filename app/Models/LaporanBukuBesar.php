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
        $cabang_id = request()->cabang_id ?? '0';

        $dariformat = date('Y/m/d', strtotime($dari));
        $sampaiformat = date('Y/m/d', strtotime($sampai));


        // dd('test');

        // cek saldo awal
        $tglsaldo = '2023-10-01';
        $awalsaldo = date('Y-m-d', strtotime($tglsaldo));

        $tutupbuku = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text'
            )
            ->where('grp', 'TUTUP BUKU')
            ->where('subgrp', 'TUTUP BUKU')
            ->first()->text ?? '1900-01-01';

        $awaldari = date('Y-m-', strtotime($dariformat)) . '01';
        $awalcek = date('Y-m-d', strtotime($tutupbuku . ' +1 day'));
        $akhircek = date('Y-m-d', strtotime($awaldari . ' -1 day'));

        $getcabangid = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text'
            )
            ->where('a.grp', 'ID CABANG')
            ->where('a.subgrp', 'ID CABANG')
            ->first()->text ?? 0;

        if ($cabang_id != $getcabangid) {

            goto bedacabang;
        }


        // dd($awalcek);

        if ($awalcek <= $awalsaldo) {
            $awalcek = $awalsaldo;
        }

        $tglawalcek = $awalcek;
        $tglakhircek = $akhircek;
        $bulan1 = date('m-Y', strtotime($awalcek));
        $bulan2 = date('m-Y', strtotime('1900-01-01'));

        // while ($awalcek <= $akhircek) {
        //     $bulan1 = date('m-Y', strtotime($awalcek));
        //     if ($bulan1 != $bulan2) {
        //         DB::delete(DB::raw("delete saldoawalbukubesar from saldoawalbukubesar as a WHERE isnull(a.bulan,'')='" . $bulan1 . "'"));
        //     }

        //     $awalcek = date('Y-m-d', strtotime($awalcek . ' +1 day'));
        //     $awalcek2 = date('Y-m-d', strtotime($awalcek . ' +1 day'));
        //     $bulan2 = date('m-Y', strtotime($awalcek2));
        // }

        $tempsaldobukubesar = '##tempsaldobukubesar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldobukubesar, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->string('coa', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $querydetailsaldo = DB::table("jurnalumumpusatheader")->from(
            DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
        )
            ->select(
                db::raw("format(b.tglbukti,'MM-yyyy') as bulan"),
                'b.coa',
                DB::raw("sum(b.nominal) as nominal"),
            )
            ->join(DB::raw("jurnalumumpusatdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("akunpusat as c with(readuncommitted)"), 'b.coa', 'c.coa')
            ->whereRaw("a.tglbukti>='" . $tglawalcek . "' and a.tglbukti<='" . $tglakhircek . "'")
            // ->whereRaw("b.coa='01.01.01.03'")
            ->groupby('b.coa')
            ->groupby(db::raw("format(b.tglbukti,'MM-yyyy')"));

        // dd($querydetailsaldo->get());

        DB::table($tempsaldobukubesar)->insertUsing([
            'bulan',
            'coa',
            'nominal',
        ], $querydetailsaldo);

        $queryrekap = db::table($tempsaldobukubesar)->from(db::raw($tempsaldobukubesar . " a"))
            ->select(
                'bulan',
                'coa',
                db::raw("sum(nominal) as nominal"),
                db::raw("'ADMIN' as modifiedby"),
                db::raw("'' as info"),
                db::raw("getdate() as created_at"),
                db::raw("getdate() as updated_at"),
                db::raw($getcabangid . " as cabang_id"),
                db::raw("'" . $tglawalcek . "' as tglbukti"),
            )
            ->groupby('a.bulan')
            ->groupby('a.coa');

        $tempsaldobukubesarrekap = '##tempsaldobukubesarrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldobukubesarrekap, function ($table) {
            $table->string('bulan', 1000)->nullable();
            $table->string('coa', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('modifiedby', 100)->nullable();
            $table->longtext('info')->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->integer('cabang_id')->nullable();
            $table->date('tglbukti')->nullable();
        });

        DB::table($tempsaldobukubesarrekap)->insertUsing([
            'bulan',
            'coa',
            'nominal',
            'info',
            'modifiedby',
            'created_at',
            'updated_at',
            'cabang_id',
            'tglbukti',
        ], $queryrekap);

        DB::delete(DB::raw("delete saldoawalbukubesar from saldoawalbukubesar as a 
                            inner join " . $tempsaldobukubesarrekap . " b on a.bulan=b.bulan and a.coa=b.coa"));


        DB::table("saldoawalbukubesar")->insertUsing([
            'bulan',
            'coa',
            'nominal',
            'info',
            'modifiedby',
            'created_at',
            'updated_at',
            'cabang_id',
            'tglbukti',
        ], $queryrekap);

        // akhir rekap saldo

        bedacabang:;

        $tempsaldorekap = '##tempsaldorekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldorekap, function ($table) {
            $table->string('coa', 1000)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });




        $querysaldoawal = DB::table("saldoawalbukubesar")->from(
            DB::raw("saldoawalbukubesar as a with (readuncommitted)")
        )
            ->select(
                'a.coa',
                DB::raw("cast(sum(isnull(a.nominal,0)) as money) as nominal")
            )
            ->join(db::raw("akunpusat b with (readuncommitted)"), 'a.coa', 'b.coa')
            ->join(db::raw("typeakuntansi c with (readuncommitted)"), 'b.type_id', 'c.id')
            ->whereRaw("(c.[order] BETWEEN 1110 AND 3310)")
            ->whereRaw("cast(right(a.bulan,4)+'/'+left(a.bulan,2)+'/1' as date)<'" . $dariformat . "'")
            ->whereRaw("a.bulan<>format(cast('" . $dariformat . "' as date),'MM-yyyy')")
            ->whereraw("(a.cabang_id=" . $cabang_id . " or " . $cabang_id . "=0)")
            // ->whereraw("b.id=106")

            ->groupBy('a.coa');

            // dd( $querysaldoawal->tosql());
            // dd( $querysaldoawal->get());


        DB::table($tempsaldorekap)->insertUsing([
            'coa',
            'saldo',
        ], $querysaldoawal);
        // dd('test');

        // dd(dB::table($tempsaldorekap)->get());

        $tempsaldo2 = '##tempsaldo2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldo2, function ($table) {
            $table->double('urut', 15, 2)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('keterangancoa', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });


        $querysaldoawal = DB::table("jurnalumumpusatheader")->from(
            DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("1 as urut"),
                'b.coa',
                'c.keterangancoa',
                DB::raw("'1900/1/1' as tglbukti"),
                DB::raw("'' as nobukti"),
                DB::raw("'SALDO AWAL' as keterangan"),
                DB::raw("0 as debet"),
                DB::raw("0 as kredit"),
                DB::raw("sum(isnull(b.nominal,0)) as saldo")
            )
            ->join(DB::raw("jurnalumumpusatdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("akunpusat as c with(readuncommitted)"), 'b.coa', 'c.coa')
            ->join(db::raw("typeakuntansi d with (readuncommitted)"), 'c.type_id', 'd.id')
            ->whereRaw("(d.[Order] BETWEEN 1110 AND 3310)")
            ->whereRaw("b.tglbukti>=cast(ltrim(rtrim(str(year('" . $dariformat . "'))))+'/'+ltrim(rtrim(str(month('" . $dariformat . "'))))+'/1' as datetime) ")
            ->where('b.tglbukti', '<', $dari)
            ->whereRaw("(c.id >=" . $coadari_id)
            ->whereRaw(DB::raw("c.id <=" . $coasampai_id . ")"))
            ->whereraw("(a.cabang_id=" . $cabang_id . " or " . $cabang_id . "=0)")
            ->groupBy('b.coa', 'c.keterangancoa');

            // dd($querysaldoawal->get());
        // dd($cabang_id);

        // dd($querysaldoawal->get());



        DB::table($tempsaldo2)->insertUsing([
            'urut',
            'coa',
            'keterangancoa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querysaldoawal);

        // dd(db::table($tempsaldo2)->get());



        $querysaldoawal = DB::table("akunpusat")->from(
            DB::raw("akunpusat as a with (readuncommitted)")
        )
            ->select(
                DB::raw("1 as urut"),
                'a.coa',
                'a.keterangancoa',
                DB::raw("'1900/1/1' as tglbukti"),
                DB::raw("'' as nobukti"),
                DB::raw("'SALDO AWAL' as keterangan"),
                DB::raw("0 as debet"),
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo")
            )
            ->leftjoin(DB::raw($tempsaldo2) . " as b", 'a.coa', 'b.coa')
            ->join(DB::raw("akunpusat as c with(readuncommitted)"), 'a.coa', 'c.coa')
            ->join(db::raw("typeakuntansi d with (readuncommitted)"), 'c.type_id', 'd.id')
            ->whereRaw("(d.[Order] BETWEEN 1110 AND 3310)")
            ->whereRaw("(a.id >=" . $coadari_id)
            ->whereRaw(DB::raw("a.id <=" . $coasampai_id . ")"))
            ->whereRaw("isnull(B.coa,'')=''");

           
        // dd($querysaldoawal->get());
        DB::table($tempsaldo2)->insertUsing([
            'urut',
            'coa',
            'keterangancoa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querysaldoawal);

        // dd(db::table($tempsaldo2)->get());

        $tempsaldo = '##tempsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaldo, function ($table) {
            $table->double('urut', 15, 2)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('keterangancoa', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $querysaldoawal = DB::table(DB::raw($tempsaldo2))->from(
            DB::raw(DB::raw($tempsaldo2) . " a with (readuncommitted)")
        )
            ->select(
                DB::raw("1 as urut"),
                'a.coa',
                'a.keterangancoa',
                DB::raw("'1900/1/1' as tglbukti"),
                DB::raw("'' as nobukti"),
                DB::raw("'SALDO AWAL' as keterangan"),
                DB::raw("0 as debet"),
                DB::raw("0 as kredit"),
                DB::raw("(isnull(a.saldo,0)+isnull(b.saldo,0)) as saldo")
            )
            ->leftjoin(DB::raw($tempsaldorekap) . " as b", 'a.coa', 'b.coa')
            ->whereRaw("(isnull(a.saldo,0)+isnull(b.saldo,0))<>0");

            // dd($querysaldoawal->get());
        DB::table($tempsaldo)->insertUsing([
            'urut',
            'coa',
            'keterangancoa',
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
            $table->double('urut', 15, 2)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('keterangancoa', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $querydetail = DB::table("jurnalumumpusatheader")->from(
            DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("2 as urut"),
                'b.coa',
                'c.keterangancoa',
                DB::raw("a.tglbukti"),
                DB::raw("a.nobukti as nobukti"),
                DB::raw("b.keterangan as keterangan"),
                DB::raw("(case when nominal>=0 then nominal else 0 end) as debet"),
                DB::raw("(case when nominal<0 then abs(nominal) else 0 end) as kredit"),
                DB::raw("0 as saldo")
            )
            ->join(DB::raw("jurnalumumpusatdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("akunpusat as c with(readuncommitted)"), 'b.coa', 'c.coa')
            ->where('b.tglbukti', '>=', $dari)
            ->where('b.tglbukti', '<=', $sampai)
            ->where('c.id', '>=', $coadari_id)
            ->where('c.id', '<=', $coasampai_id)
            ->whereraw("(a.cabang_id=" . $cabang_id . " or " . $cabang_id . "=0)")
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('a.nobukti', 'asc')
            ->orderBy('b.nominal', 'desc')
            ->orderBy('b.id', 'asc');


        DB::table($tempdetail)->insertUsing([
            'urut',
            'coa',
            'keterangancoa',
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
            $table->unsignedBigInteger('urut')->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('keterangancoa', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $queryRekap1 = DB::table($tempsaldo)
            ->select(
                'urut',
                'coa',
                'keterangancoa',
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
            'keterangancoa',
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
                'keterangancoa',
                'tglbukti',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
            )->orderBy('id', 'asc');


        DB::table($temprekap)->insertUsing([
            'urut',
            'coa',
            'keterangancoa',
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $queryRekap);

        if ($cabang_id == 0) {
            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();
        } else {
            if ($cabang_id != $getcabangid) {
                $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                    ->select('text')
                    ->where('grp', 'JUDULAN LAPORAN')
                    ->where('subgrp', 'JUDULAN LAPORAN')
                    ->first();
            } else {
                $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                    ->select('text')
                    ->where('grp', 'JUDULAN LAPORAN')
                    ->where('subgrp', 'JUDULAN LAPORAN')
                    ->first();
            }
        }


        $cabang = db::table("cabang")->from(db::raw("cabang a with (readuncommitted)"))
            ->select(
                'a.namacabang'
            )
            ->where('a.id', $cabang_id)
            ->first()->namacabang ?? '';

        if ($cabang_id == 0) {
            $cabang = 'SEMUA';
        }

        if ($cabang_id == $getcabangid) {
            $cabang = '';
        }

        $queryRekap = DB::table($temprekap)
            ->select(
                'id',
                'urut',
                'coa',
                'keterangancoa',
                DB::raw("(case when year(tglbukti)=1900 then null else tglbukti end) as tglbukti"),
                'nobukti',
                'keterangan',
                // DB::raw("(case when debet=0 then null else debet end) as debet"),
                // DB::raw("(case when kredit=0 then null else kredit end) as kredit"),
                'debet',
                'kredit',
                DB::raw("sum ((isnull(saldo,0)+debet)-Kredit) over (partition by coa order by id asc) as Saldo"),
                DB::raw("'Laporan Buku Besar' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("(case when '" . $cabang . "'='' then '' else 'Cabang :" . $cabang . "'  end) as Cabang")

            )
            ->orderBy('coa', 'Asc')
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
