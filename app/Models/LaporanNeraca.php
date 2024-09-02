<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class LaporanNeraca extends MyModel
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

    public function getReport($sampai, $eksport, $cabang_id)
    {
        $bulan = substr($sampai, 0, 2);
        $tahun = substr($sampai, -4);

        $tgl = $tahun . '-' . $bulan . '-02';
        $tgl1 = $tahun . '-' . $bulan . '-01';

        $tgl3 = date('Y-m-d', strtotime($tgl1 . ' +32 days'));


        $tahun2 = date('Y', strtotime($tgl3));
        $bulan2 = date('m', strtotime($tgl3));



        $ptgl = $tahun . '-' . $bulan . '-01';

        $tgldari = $ptgl;

        $datetime = $tahun2 . '-' . $bulan2 . '-1';

        $tglsd =  date('Y-m-d', strtotime($datetime . ' -1 day'));
        $tglsd1 =  date('Y-m-d', strtotime($tglsd . ' +1 day'));


        // if (date('Y-m-d', strtotime($ptgl)) >= date('Y-m-d', strtotime('2024-05-01'))) {
        //     dd('testA');
        // } else {
        //     dd('testB');
        // }
        // dd('testC');
        $judul = Parameter::where('grp', '=', 'JUDULAN LAPORAN')->first();
        $judulLaporan = $judul->text;

        //   dd($tglsd1);
        // $eksport = 1;

        $getcabangid = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text'
            )
            ->where('a.grp', 'ID CABANG')
            ->where('a.subgrp', 'ID CABANG')
            ->first()->text ?? 0;

        $cabang = db::table("cabang")->from(db::raw("cabang a with (readuncommitted)"))
            ->select(
                'a.namacabang'
            )
            ->where('a.id', $cabang_id)
            ->first()->namacabang ?? '';

        if ($cabang_id != $getcabangid) {
            $eksport = 1;
        }

        if ($getcabangid == 1) {
            $eksport = 1;
            if ($cabang_id == 0) {
                $cabang = 'SEMUA';
            }
        }

        if ($cabang_id == $getcabangid) {

            $tglbulan = $tahun . '/' . $bulan . '/1';
            $tgluji = date('Y-m-d', strtotime('+1 months', strtotime($tglbulan)));
            if ($getcabangid == 1) {
                DB::table('akunpusatdetail')
                    ->where('bulan', '<>', 0)
                    ->whereRaw("cabang_id=" . $cabang_id)
                    ->whereRaw("cast(str(tahun)+'/'+str(bulan)+'/1' as datetime)>='" . $tgluji . "'")
                    ->whereRaw("cast(trim(str(tahun))+'/'+trim(str((case when bulan=0 then 1 else bulan end)))+'/1' as datetime)>='2024/5/1'")
                    ->delete();

                DB::table('akunpusatdetail')
                    ->where('bulan', '<>', 0)
                    ->whereRaw("cast(str(tahun)+'/'+str(bulan)+'/1' as datetime)>='" . $tgluji . "'")
                    ->whereRaw("cast(trim(str(tahun))+'/'+trim(str((case when bulan=0 then 1 else bulan end)))+'/1' as datetime)>='2024/5/1'")
                    ->delete();
            } else {
                DB::table('akunpusatdetail')
                    ->where('bulan', '<>', 0)
                    ->whereRaw("cabang_id=" . $cabang_id)
                    ->whereRaw("cast(str(tahun)+'/'+str(bulan)+'/1' as datetime)>='" . $tgluji . "'")
                    ->delete();

                DB::table('akunpusatdetail')
                    ->where('bulan', '<>', 0)
                    ->whereRaw("cast(str(tahun)+'/'+str(bulan)+'/1' as datetime)>='" . $tgluji . "'")
                    ->delete();
            }
        }
        if ($eksport == 1) {

            // if ($cabang_id == $getcabangid && $getcabangid != 1) {
            if ($cabang_id == $getcabangid) {

                if ($getcabangid == 1) {
                    DB::table('akunpusatdetail')
                        ->where('bulan', '<>', 0)
                        ->whereRaw("cabang_id=" . $cabang_id)
                        ->whereRaw("bulan=" . $bulan . " and tahun=" . $tahun)
                        ->whereRaw("cast(trim(str(tahun))+'/'+trim(str((case when bulan=0 then 1 else bulan end)))+'/1' as datetime)>='2024/5/1'")
                        ->delete();
                } else {
                    DB::table('akunpusatdetail')
                        ->where('bulan', '<>', 0)
                        ->whereRaw("cabang_id=" . $cabang_id)
                        ->whereRaw("bulan=" . $bulan . " and tahun=" . $tahun)
                        ->delete();
                }






                $subquery1 = DB::table('jurnalumumpusatheader as J')
                    ->select(
                        'D.coamain as FCOA',
                        DB::raw('YEAR(D.tglbukti) as FThn'),
                        DB::raw('MONTH(D.tglbukti) as FBln'),
                        db::raw($cabang_id . ' as cabang_id'),
                        DB::raw(
                            'round(SUM(D.nominal),2) as FNominal',


                        )
                    )
                    ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
                    ->join('mainakunpusat as C', 'C.coa', '=', 'D.coamain')
                    ->where('D.tglbukti', '>=', $ptgl)
                    ->where('j.cabang_id',  $cabang_id)
                    ->groupBy('D.coamain', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));

                if ($cabang == 'SEMUA') {
                    $subquery2 = DB::table('jurnalumumpusatheader as J')
                        ->select(
                            'LR.coa',
                            DB::raw('YEAR(D.tglbukti) as FThn'),
                            DB::raw('MONTH(D.tglbukti) as FBln'),
                            db::raw($cabang_id . ' as cabang_id'),
                            DB::raw('round(SUM(D.nominal),2) as FNominal'),
                        )
                        ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
                        ->join('perkiraanlabarugi as LR', function ($join) {
                            $join->on('LR.tahun', '=', DB::raw('YEAR(J.tglbukti)'))
                                ->on('LR.bulan', '=', DB::raw('MONTH(J.tglbukti)'));
                        })
                        ->whereIn('D.coamain', function ($query) {
                            $query->select(DB::raw('DISTINCT C.coa'))
                                ->from('maintypeakuntansi as AT')
                                ->join('mainakunpusat as C', 'AT.kodetype', '=', 'C.Type')
                                ->where('AT.order', '>=', 4000)
                                ->where('AT.order', '<', 9000)

                                ->where('C.type', '<>', 'Laba/Rugi');
                        })
                        ->where('D.tglbukti', '>=', $ptgl)
                        ->where('j.cabang_id',  $cabang_id)
                        ->groupBy('LR.coa', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));
                } else {
                    $subquery2 = DB::table('jurnalumumpusatheader as J')
                        ->select(
                            'LR.coa',
                            DB::raw('YEAR(D.tglbukti) as FThn'),
                            DB::raw('MONTH(D.tglbukti) as FBln'),
                            db::raw($cabang_id . ' as cabang_id'),
                            DB::raw('round(SUM(D.nominal),2) as FNominal'),
                        )
                        ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
                        ->join('perkiraanlabarugi as LR', function ($join) use ($cabang_id) {
                            $join->on('LR.tahun', '=', DB::raw('YEAR(J.tglbukti)'))
                                ->on('LR.bulan', '=', DB::raw('MONTH(J.tglbukti)'))
                                ->on('LR.cabang_id', '=', DB::raw($cabang_id));
                        })
                        ->whereIn('D.coamain', function ($query) {
                            $query->select(DB::raw('DISTINCT C.coa'))
                                ->from('maintypeakuntansi as AT')
                                ->join('mainakunpusat as C', 'AT.kodetype', '=', 'C.Type')
                                ->where('AT.order', '>=', 4000)
                                ->where('AT.order', '<', 9000)

                                ->where('C.type', '<>', 'Laba/Rugi');
                        })
                        ->where('D.tglbukti', '>=', $ptgl)
                        ->where('j.cabang_id',  $cabang_id)
                        ->groupBy('LR.coa', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));
                }

                // dd('test');

                $RecalKdPerkiraan = DB::table(DB::raw("({$subquery1->toSql()} UNION ALL {$subquery2->toSql()}) as V"))
                    ->mergeBindings($subquery1)
                    ->mergeBindings($subquery2)
                    ->groupBy('FCOA', 'FThn', 'FBln', 'cabang_id')
                    ->select('FCOA', 'FThn', 'FBln', 'cabang_id', DB::raw('round(SUM(FNominal),2) as FNominal'));

                // dd($RecalKdPerkiraan->toSql());
                if ($getcabangid == 1) {
                    if (date('Y-m-d', strtotime($ptgl)) >= date('Y-m-d', strtotime('2024-05-01'))) {
                        DB::table('akunpusatdetail')->insertUsing([
                            'coa',
                            'tahun',
                            'bulan',
                            'cabang_id',
                            'nominal',

                        ], $RecalKdPerkiraan);
                    }
                } else {
                    DB::table('akunpusatdetail')->insertUsing([
                        'coa',
                        'tahun',
                        'bulan',
                        'cabang_id',
                        'nominal',

                    ], $RecalKdPerkiraan);
                }


                if ($bulan == 1) {

                    if ($getcabangid == 1) {
                        DB::table('akunpusatdetail')
                            ->where('bulan', '=', 0)
                            ->where('tahun', '=', $tahun)
                            ->whereRaw("cabang_id=" . $cabang_id)
                            ->whereRaw("cast(trim(str(tahun))+'/'+trim(str((case when bulan=0 then 1 else bulan end)))+'/1' as datetime)>='2024/5/1'")
                            ->delete();
                    } else {
                        DB::table('akunpusatdetail')
                            ->where('bulan', '=', 0)
                            ->where('tahun', '=', $tahun)
                            ->whereRaw("cabang_id=" . $cabang_id)
                            ->delete();
                    }



                    $tahunsaldo = $tahun - 1;
                    // $querysaldo=db::table('akunpusatdetail')->from(db::raw("akunpusatdetail a with (readuncommitted)"))
                    // ->select(
                    //     'a.coa',
                    //     db::raw($tahun. ' as tahun'),
                    //     db::raw('0 as bulan'),
                    //     'a.cabang_id',
                    //     db::raw("sum(a.nominal) as nominal")
                    // )
                    // ->where('a.tahun',$tahunsaldo)
                    // ->whereRaw("cabang_id=" . $cabang_id)
                    // ->groupBy('a.coa')
                    // ->groupBy('a.cabang_id');

                    $tempAkunPusatDetailsaldo = '##tempAkunPusatDetailsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempAkunPusatDetailsaldo, function ($table) {
                        $table->bigIncrements('id');
                        $table->string('coa', 50)->nullable();
                        $table->integer('tahun')->nullable();
                        $table->integer('bulan')->nullable();
                        $table->integer('cabang_id')->nullable();
                        $table->double('nominal')->nullable();
                    });

                    $querysaldo1 = db::table('akunpusatdetail')->from(db::raw("akunpusatdetail a with (readuncommitted)"))
                        ->select(
                            'a.coa',
                            db::raw($tahun . ' as tahun'),
                            db::raw('0 as bulan'),
                            'a.cabang_id',
                            db::raw("sum(a.nominal) as nominal")
                        )
                        ->where('a.tahun', $tahunsaldo)
                        // ->whereraw("a.tahun<>0")
                        ->whereRaw("cabang_id=" . $cabang_id)
                        ->groupBy('a.coa')
                        ->groupBy('a.cabang_id');

                    DB::table($tempAkunPusatDetailsaldo)->insertUsing([
                        'coa',
                        'tahun',
                        'bulan',
                        'cabang_id',
                        'nominal',

                    ], $querysaldo1);


                    $querysaldo2 = db::table('saldoakunpusatdetail')->from(db::raw("saldoakunpusatdetail a with (readuncommitted)"))
                        ->select(
                            'a.coa',
                            db::raw($tahun . ' as tahun'),
                            db::raw('0 as bulan'),
                            'a.cabang_id',
                            db::raw("sum(a.nominal) as nominal")
                        )
                        ->where('a.tahun', $tahunsaldo)
                        ->whereRaw("cabang_id=" . $cabang_id)
                        ->groupBy('a.coa')
                        ->groupBy('a.cabang_id');

                    DB::table($tempAkunPusatDetailsaldo)->insertUsing([
                        'coa',
                        'tahun',
                        'bulan',
                        'cabang_id',
                        'nominal',

                    ], $querysaldo2);
                    // dd(db::table($tempAkunPusatDetailsaldo)->where('coa','01.01.01.03' )->get());


                    $querysaldo = db::table($tempAkunPusatDetailsaldo)->from(db::raw($tempAkunPusatDetailsaldo . " a"))
                        ->select(
                            'a.coa',
                            db::raw($tahun . ' as tahun'),
                            db::raw('0 as bulan'),
                            'a.cabang_id',
                            db::raw("sum(a.nominal) as nominal")
                        )
                        ->where('a.tahun', $tahun)
                        // ->where('coa','01.01.01.03' )
                        ->groupBy('a.coa')
                        ->groupBy('a.cabang_id');

                    if ($getcabangid == 1) {
                        if (date('Y-m-d', strtotime($ptgl)) >= date('Y-m-d', strtotime('2024-05-01'))) {
                            DB::table('akunpusatdetail')->insertUsing([
                                'coa',
                                'tahun',
                                'bulan',
                                'cabang_id',
                                'nominal',

                            ], $querysaldo);
                        }
                    } else {
                        DB::table('akunpusatdetail')->insertUsing([
                            'coa',
                            'tahun',
                            'bulan',
                            'cabang_id',
                            'nominal',

                        ], $querysaldo);
                    }
                }
            }
            if ($getcabangid == 1) {
                DB::update(DB::raw("update akunpusatdetail set coagroup='05.03.01.01' where coa in('05.03.01.02','05.03.01.07','05.03.01.01','05.03.01.03','05.03.01.04','05.03.01.05')and isnull(coagroup,'')=''"));
            }


            $tempAkunPusatDetail = '##tempAkunPusatDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempAkunPusatDetail, function ($table) {
                $table->bigIncrements('id');
                $table->string('coa', 50)->nullable();
                $table->string('coagroup', 50)->nullable();
                $table->integer('bulan')->nullable();
                $table->integer('tahun')->nullable();
                $table->double('nominal')->nullable();
                $table->string('modifiedby')->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
            });

            $queryTempSaldoAkunPusatDetail = DB::table('saldoakunpusatdetail')->from(
                DB::raw('saldoakunpusatdetail')
            )
                ->select(
                    'coa',
                    db::raw("null as coagroup"),
                    'bulan',
                    'tahun',
                    'nominal',
                    'modifiedby',
                    'created_at',
                    'updated_at'

                )
                ->whereRaw("(cabang_id=" .  $cabang_id . " or " . $cabang_id . "=0)")
                ->orderBy('id', 'asc');


            DB::table($tempAkunPusatDetail)->insertUsing([
                'coa',
                'coagroup',
                'bulan',
                'tahun',
                'nominal',
                'modifiedby',
                'created_at',
                'updated_at',

            ], $queryTempSaldoAkunPusatDetail);
            if ($cabang == 'SEMUA') {
                $queryTempAkunPusatDetail = DB::table('akunpusatdetail')->from(
                    DB::raw('akunpusatdetail')
                )
                    ->select(
                        db::raw("(case when isnull(coagroup,'')<>'' and '" . $cabang . "' = 'SEMUA' then isnull(coagroup,'') else coa end) as coa"),
                        // 'coa',
                        'coagroup',
                        'bulan',
                        'tahun',
                        'nominal',
                        'modifiedby',
                        'created_at',
                        'updated_at'

                    )
                    ->whereRaw("(cabang_id=" .  $cabang_id . " or " . $cabang_id . "=0)")
                    ->whereRaw("(cabang_id=" .  $cabang_id . " or " . $cabang_id . "=0)")
                    ->orderBy('id', 'asc');
            } else {
                $queryTempAkunPusatDetail = DB::table('akunpusatdetail')->from(
                    DB::raw('akunpusatdetail')
                )
                    ->select(
                        // db::raw("(case when isnull(coagroup,'')<>'' and '".$cabang. "' = 'SEMUA' then isnull(coagroup,'') else coa end) as coa"),
                        // db::raw("(case when isnull(coagroup,'')<>'' and '" . $cabang . "' = 'SEMUA' then isnull(coagroup,'') else coa end) as coa"),

                        'coa',
                        'coagroup',
                        'bulan',
                        'tahun',
                        'nominal',
                        'modifiedby',
                        'created_at',
                        'updated_at'

                    )
                    ->whereRaw("(cabang_id=" .  $cabang_id . " or " . $cabang_id . "=0)")
                    ->whereRaw("(cabang_id=" .  $cabang_id . " or " . $cabang_id . "=0)")
                    ->orderBy('id', 'asc');
            }



            DB::table($tempAkunPusatDetail)->insertUsing([
                'coa',
                'coagroup',
                'bulan',
                'tahun',
                'nominal',
                'modifiedby',
                'created_at',
                'updated_at',

            ], $queryTempAkunPusatDetail);

            // dd(db::table($tempAkunPusatDetail)->where('coa','05.03.01.01')->get());

            $tempquery1 = '##tempquery1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempquery1, function ($table) {
                $table->bigIncrements('id');
                $table->string('type', 500)->nullable();
                $table->string('coa', 500)->nullable();
                $table->string('coagroup', 500)->nullable();
                $table->string('keterangancoa', 500)->nullable();
                $table->string('parent', 500)->nullable();
                $table->integer('statusaktif')->nullable();
                $table->integer('statusneraca')->nullable();
                $table->integer('statuslabarugi')->nullable();
                $table->integer('tahun')->nullable();
                $table->integer('bulan')->nullable();
                $table->double('nominal')->nullable();
                $table->integer('order')->nullable();
                $table->string('keterangantype', 500)->nullable();
                $table->integer('akuntansi_id')->nullable();
            });




            $query1 = db::table('mainakunpusat')->from(db::raw("mainakunpusat c with (readuncommitted)"))
                ->select(
                    'c.type',
                    // db::raw("(case when isnull(coagroup,'')<>'' and '".$cabang. "' = 'SEMUA' then isnull(coagroup,'') else coa end) as coa"),
                    'c.coa',
                    'cd.coagroup',
                    'c.keterangancoa',
                    'c.parent',
                    'c.statusaktif',
                    'c.statusneraca',
                    'c.statuslabarugi',
                    db::raw("isnull(cd.tahun," . $tahun . ") as tahun"),
                    db::raw("isnull(cd.bulan,0) as bulan"),
                    db::raw("isnull(cd.nominal,0) as nominal"),
                    'a.order',
                    'a.keterangantype',
                    'a.akuntansi_id',
                )
                ->join(db::raw($tempAkunPusatDetail . " cd with (readuncommitted)"), 'c.coa', 'cd.coa')
                ->leftjoin(db::raw("maintypeakuntansi a with (readuncommitted)"), 'a.kodetype', 'c.type');
            // dd($query1->where('c.coa','05.03.01.07')->where('cd.tahun', $tahun)->get());

            DB::table($tempquery1)->insertUsing([
                'type',
                'coa',
                'coagroup',
                'keterangancoa',
                'parent',
                'statusaktif',
                'statusneraca',
                'statuslabarugi',
                'tahun',
                'bulan',
                'nominal',
                'order',
                'keterangantype',
                'akuntansi_id',

            ], $query1);


            $tempquery2 = '##tempquery2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempquery2, function ($table) {
                $table->bigIncrements('id');
                $table->string('tipemaster', 500)->nullable();
                $table->integer('order')->nullable();
                $table->string('type', 500)->nullable();
                $table->string('keterangantype', 500)->nullable();
                $table->string('coa', 500)->nullable();
                $table->string('parent', 500)->nullable();
                $table->string('keterangancoa', 500)->nullable();
                $table->double('nominal')->nullable();
                $table->string('cmpyname', 500)->nullable();
                $table->integer('pbulan')->nullable();
                $table->integer('ptahun')->nullable();
                $table->integer('gneraca')->nullable();
                $table->integer('glr')->nullable();
                $table->string('keterangancoaparent', 500)->nullable();
                $table->string('ptglsd', 50)->nullable();
            });


            // dd(db::table($tempquery1)->where('coa','05.02.01.01')
            // ->where('tahun', $tahun)
            //     ->whereRaw("bulan<=cast(" . $bulan . " as integer)")
            // ->get());
            $query2 = db::table($tempquery1)->from(db::raw($tempquery1 . " d"))
                ->select(
                    db::raw("(CASE d.akuntansi_id WHEN 1 THEN 'AKTIVA' ELSE 'PASSIVA' END) AS tipemaster"),
                    'd.order',
                    db::raw("max(d.type) as type"),
                    db::raw("max(d.keterangantype) as keterangantype"),
                    'd.coa',
                    // db::raw("(case when isnull(d.coagroup,'')='' then d.coa else isnull(d.coagroup,'') end) as coa"),
                    db::raw("max(d.parent) as parent"),
                    'd.keterangancoa',
                    db::raw("( CASE d.akuntansi_id WHEN 1 THEN round(SUM(d.Nominal),2) ELSE round(SUM(d.Nominal * -1),2) END)  AS nominal"),
                    db::raw("'" . $judulLaporan . "' as cmpyname"),
                    db::raw($bulan . " as pbulan"),
                    db::raw($tahun . " as ptahun"),
                    db::raw("max(d.statusneraca) as gneraca"),
                    db::raw("max(d.statuslabarugi) as glr"),
                    db::raw("max(isnull(e.keterangancoa,'')) as keterangancoaparent"),
                    db::raw($tglsd . " as ptglsd"),
                )
                ->leftjoin(db::raw("akunpusat e with (readuncommitted)"), 'd.parent', 'e.coa')
                ->where('d.tahun', $tahun)
                ->whereRaw("d.bulan<=cast(" . $bulan . " as integer)")
                ->where('d.order', '<', 4000)
                ->groupBy('d.akuntansi_id')
                ->groupBy('d.order')
                // ->groupBy(db::raw("(case when isnull(d.coagroup,'')='' then d.coa else isnull(d.coagroup,'') end)"))
                ->groupBy('d.coa')
                ->groupBy('d.keterangancoa');
            // ->having(DB::raw('sum(d.nominal)'), '<>', 0);

            //  dd($query2->where('d.coa','05.03.01.07')->get());
            //  dd(db::table($tempquery1)->where('coa','05.03.01.07')->where('tahun', $tahun)->get());

            DB::table($tempquery2)->insertUsing([
                'tipemaster',
                'order',
                'type',
                'keterangantype',
                'coa',
                'parent',
                'keterangancoa',
                'nominal',
                'cmpyname',
                'pbulan',
                'ptahun',
                'gneraca',
                'glr',
                'keterangancoaparent',
                'ptglsd',
            ], $query2);

            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();


            $data = db::table($tempquery2)->from(db::raw($tempquery2 . " xx"))
                ->select(
                    'xx.TipeMaster',
                    'xx.Order',
                    'xx.Type',
                    'xx.KeteranganType',
                    'xx.coa',
                    'xx.Parent',
                    'xx.KeteranganCoa',
                    db::raw("round(xx.Nominal,2) as Nominal"),
                    'xx.CmpyName',
                    'xx.pBulan',
                    'xx.pTahun',
                    'xx.GNeraca',
                    'xx.GLR',
                    'xx.KeteranganCoaParent',
                    'xx.pTglSd',
                    DB::raw("'" . $getJudul->text . "' as judul"),
                    db::raw("0 as selisih"),
                    db::raw("(case when '" . $cabang . "'='' then '' else 'Cabang :" . $cabang . "'  end) as Cabang")


                )
                ->whereRaw("isnull(xx.Nominal,0)<>0")
                ->orderby('xx.id');

            goto selesai;
        }

        // goto mulai;

        // rekap akunpusat detail


        //         DB::delete(DB::raw("delete akunpusatdetail from akunpusatdetail as a WHERE isnull(a.bulan,0)<>0"));


        //         $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        //         Schema::create($temprekap, function ($table) {
        //             $table->id();
        //             $table->longText('fcoa')->nullable();
        //             $table->integer('fthn')->nullable();
        //             $table->integer('fbln')->nullable();
        //             $table->double('nominal', 15, 2)->nullable();
        //         });

        //         $query1 = db::table('jurnalumumpusatheader')->from(db::raw("jurnalumumpusatheader j with (readuncommitted)"))
        //             ->select(
        //                 'd.coamain as fcoa',
        //                 db::raw("year(d.tglBukti) as fthn"),
        //                 db::raw("month(d.tglBukti) as fbln"),
        //                 db::raw("sum(d.nominal) as fnominal"),
        //             )
        //             ->join(db::raw("jurnalumumpusatdetail d with (readuncommitted)"), 'j.nobukti', 'd.nobukti')
        //             ->join(db::raw("mainakunpusat c with (readuncommitted)"), 'c.coa', 'd.coamain')
        //             ->whereRaw("d.tglbukti>='" . $ptgl . "'")
        //             ->groupby('d.coamain')
        //             ->groupby(db::raw("year(d.tglbukti)"))
        //             ->groupby(db::month("year(d.tglbukti)"));

        //         DB::table($temprekap)->insertUsing([
        //             'fcoa',
        //             'fthn',
        //             'fbln',
        //             'nominal',
        //         ], $query1);



        //         $query2 = db::table('jurnalumumpusatheader')->from(db::raw("jurnalumumpusatheader j with (readuncommitted)"))
        //             ->select(
        //                 'lr.coa as fcoa',
        //                 db::raw("year(d.tglBukti) as fthn"),
        //                 db::raw("month(d.tglBukti) as fbln"),
        //                 db::raw("sum(d.nominal) as fnominal"),
        //             )
        //             ->join(db::raw("jurnalumumpusatdetail d with (readuncommitted)"), 'j.nobukti', 'd.nobukti')
        //             ->join(DB::raw("perkiraanlabarugi lr with(readuncommitted)"), function ($join) {
        //                 $join->on('lr.tahun', '=', db::raw("year(j.tglbukti)"));
        //                 $join->on('lr.bulan', '=', db::raw("month(j.tglbukti)"));
        //             })
        //             ->whereRaw("D.coamain IN (SELECT DISTINCT C.coa FROM maintypeakuntansi AT INNER JOIN mainakunpusat C ON AT.kodetype = C.[Type]
        // 		            WHERE AT.[order] >= 4000 AND AT.[order] < 9000 AND C.[type]<>'Laba/Rugi')  ")
        //             ->whereRaw("d.tglbukti>='" . $ptgl . "'")
        //             ->groupby('lr.coa')
        //             ->groupby(db::raw("year(d.tglbukti)"))
        //             ->groupby(db::month("year(d.tglbukti)"));

        //         DB::table($temprekap)->insertUsing([
        //             'fcoa',
        //             'fthn',
        //             'fbln',
        //             'nominal',
        //         ], $query2);

        //         $query = db::table($temprekap)->from(db::raw($temprekap . " a "))
        //             ->select(
        //                 'a.fcoa',
        //                 'a.fthn',
        //                 'a.fbln',
        //                 db::raw("sum(a.fnominal) as fnominal ")
        //             )
        //             ->grpupBY('a.fcoa')
        //             ->grpupBY('a.fthn')
        //             ->grpupBY('a.fbln');

        //         DB::table('akunpusatdetail')->insertUsing([
        //             'fcoa',
        //             'fthn',
        //             'fbln',
        //             'nominal',
        //         ], $query);
        // // 

        $parameter = new Parameter();
        $tglsaldo = $parameter->cekText('SALDO', 'SALDO') ?? '1900-01-01';
        $tglsaldo = date('Y-m-d', strtotime($tglsaldo));
        $tgluji = date('Y-m-d', strtotime($tglsd1 . ' -1 days'));

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'LaporanNeracaController';

        // dd($proses);

        if ($proses == 'reload') {
            $tempperkiraanbanding = '##tempperkiraanbanding' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempperkiraanbanding, function ($table) {
                $table->bigIncrements('id');
                $table->string('coa', 50)->nullable();
                $table->double('nominal')->nullable();
            });



            $tempkartuhutang = '##tempkartuhutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkartuhutang, function ($table) {
                $table->integer('id')->nullable();
                $table->string('supplier_id', 1000)->nullable();
                $table->string('nobukti', 50)->nullable();
                $table->dateTime('tglbukti')->nullable();
                $table->double('nominalhutang')->nullable();
                $table->dateTime('tglbayar')->nullable();
                $table->double('nominalbayar')->nullable();
                $table->string('nobuktihutang', 50)->nullable();
                $table->dateTime('tglberjalan')->nullable();
                $table->double('saldo')->nullable();
                $table->double('saldobayar')->nullable();
                $table->string('jenishutang', 50)->nullable();
                $table->integer('urut')->nullable();
                $table->string('text', 500)->nullable();
                $table->string('dari', 500)->nullable();
                $table->string('sampai', 500)->nullable();
                $table->string('judullaporan', 500)->nullable();
                $table->string('judul', 500)->nullable();
                $table->string('tglcetak', 500)->nullable();
                $table->string('usercetak', 500)->nullable();
                $table->string('disetujui', 500)->nullable();
                $table->string('diperiksa', 500)->nullable();
            });



            DB::table($tempkartuhutang)->insertUsing([
                'id',
                'supplier_id',
                'nobukti',
                'tglbukti',
                'nominalhutang',
                'tglbayar',
                'nominalbayar',
                'nobuktihutang',
                'tglberjalan',
                'saldo',
                'saldobayar',
                'jenishutang',
                'urut',
                'text',
                'dari',
                'sampai',
                'judullaporan',
                'judul',
                'tglcetak',
                'usercetak',
                'disetujui',
                'diperiksa',
            ], (new LaporanKartuHutangPerSupplier())->getReport($tglsd, $tglsd, 0, 0, 1));

            $tempkartupiutang = '##tempkartupiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkartupiutang, function ($table) {
                $table->integer('id')->nullable();
                $table->string('agen_id', 1000)->nullable();
                $table->string('nobukti', 50)->nullable();
                $table->dateTime('tglbukti')->nullable();
                $table->double('nominalpiutang')->nullable();
                $table->dateTime('tgllunas')->nullable();
                $table->double('nominallunas')->nullable();
                $table->string('nobuktipiutang', 50)->nullable();
                $table->dateTime('tglberjalan')->nullable();
                $table->double('saldo')->nullable();
                $table->double('saldobayar')->nullable();
                $table->string('jenispiutang', 50)->nullable();
                $table->integer('urut')->nullable();
                $table->string('text', 500)->nullable();
                $table->string('dari', 500)->nullable();
                $table->string('sampai', 500)->nullable();
                $table->string('judullaporan', 500)->nullable();
                $table->string('judul', 500)->nullable();
                $table->string('tglcetak', 500)->nullable();
                $table->string('usercetak', 500)->nullable();
                $table->string('disetujui', 500)->nullable();
                $table->string('diperiksa', 500)->nullable();
            });


            DB::table($tempkartupiutang)->insertUsing([
                'id',
                'agen_id',
                'nobukti',
                'tglbukti',
                'nominalpiutang',
                'tgllunas',
                'nominallunas',
                'nobuktipiutang',
                'tglberjalan',
                'saldo',
                'saldobayar',
                'jenispiutang',
                'urut',
                'text',
                'dari',
                'sampai',
                'judullaporan',
                'judul',
                'tglcetak',
                'usercetak',
                'disetujui',
                'diperiksa',
            ], (new LaporanKartuPiutangPerAgen())->getReport($tglsd, $tglsd, 0, 0, 1));

            // dd(db::table($tempkartupiutang)->get());

            $temppinjamansupir = '##temppinjamansupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppinjamansupir, function ($table) {
                $table->dateTime('tanggal')->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->string('namasupir', 500)->nullable();
                $table->string('keterangan', 500)->nullable();
                $table->double('debet')->nullable();
                $table->double('kredit')->nullable();
                $table->double('saldo')->nullable();
                $table->string('disetujui', 500)->nullable();
                $table->string('diperiksa', 500)->nullable();
                $table->string('judullaporan', 500)->nullable();
                $table->string('judul', 500)->nullable();
                $table->longText('tglcetak')->nullable();
                $table->string('usercetak', 500)->nullable();
            });


            $jenis = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.id'
                )
                ->where('grp', 'STATUS POSTING')
                ->where('subgrp', 'STATUS POSTING')
                ->where('text', 'POSTING')
                ->first()->id ?? 0;

            // dump($tglsd);
            // DD($jenis);


            DB::table($temppinjamansupir)->insertUsing([
                'tanggal',
                'nobukti',
                'namasupir',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'disetujui',
                'diperiksa',
                'judullaporan',
                'judul',
                'tglcetak',
                'usercetak',
            ], (new LaporanKeteranganPinjamanSupir())->getReport($tglsd, $jenis, 1));

            // Pinjaman karyawan

            $temppinjamankaryawan = '##temppinjamankaryawan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppinjamankaryawan, function ($table) {
                $table->string('nobukti', 500)->nullable();
                $table->string('namakaryawan', 500)->nullable();
                $table->string('nobuktipelunasan', 500)->nullable();
                $table->date('tglbukti')->nullable();
                $table->date('tglbuktipelunasan')->nullable();
                $table->string('keterangan', 500)->nullable();
                $table->double('debet')->nullable();
                $table->double('kredit')->nullable();
                $table->double('saldo')->nullable();
                $table->string('judullaporan', 500)->nullable();
                $table->string('judul', 500)->nullable();
                $table->string('tglcetak', 500)->nullable();
                $table->string('usercetak', 500)->nullable();
                $table->string('disetujui', 500)->nullable();
                $table->string('diperiksa', 500)->nullable();
            });


            DB::table($temppinjamankaryawan)->insertUsing([
                'nobukti',
                'namakaryawan',
                'nobuktipelunasan',
                'tglbukti',
                'tglbuktipelunasan',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'judullaporan',
                'judul',
                'tglcetak',
                'usercetak',
                'disetujui',
                'diperiksa',
            ], (new LaporanPinjamanSupirKaryawan())->getReport($tglsd, 1, 83));
            // 

            // Piutang LAin

            $temppiutanglain = '##temppiutanglain' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppiutanglain, function ($table) {
                $table->string('judul', 500)->nullable();
                $table->string('judullaporan', 500)->nullable();
                $table->integer('urut')->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->date('tglbukti')->nullable();
                $table->string('keterangan', 500)->nullable();
                $table->double('nominal')->nullable();
                $table->string('jenisorder', 500)->nullable();
                $table->string('jenislaporan', 500)->nullable();
            });


            $tglpiutanglain = date('Y-m-d', strtotime($tglsd1 . ' -1 days'));
            DB::table($temppiutanglain)->insertUsing([
                'judul',
                'judullaporan',
                'urut',
                'nobukti',
                'tglbukti',
                'keterangan',
                'nominal',
                'jenisorder',
                'jenislaporan',
            ], (new LaporanRekapTitipanEmkl())->getData($tglpiutanglain, 1));
            // 

            // Deposito SUpir

            $tempdepositosupir = '##tempdepositosupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempdepositosupir, function ($table) {
                $table->integer('id')->nullable();
                $table->integer('supir_id')->nullable();
                $table->string('namasupir', 500)->nullable();
                $table->double('saldo')->nullable();
                $table->double('deposito')->nullable();
                $table->double('penarikan')->nullable();
                $table->double('total')->nullable();
                $table->string('keterangan', 500)->nullable();
                $table->string('cicil', 500)->nullable();
                $table->string('keterangan2', 500)->nullable();
                $table->string('keterangandeposito', 500)->nullable();
                $table->string('judullaporan', 500)->nullable();
                $table->string('judul', 500)->nullable();
                $table->string('tglcetak', 500)->nullable();
                $table->string('usercetak', 500)->nullable();
                $table->string('disetujui', 500)->nullable();
                $table->string('diperiksa', 500)->nullable();
            });

// dd((new LaporanDepositoSupir())->getReport($tglsd, '', 1)->get());

            DB::table($tempdepositosupir)->insertUsing([
                'id',
                'supir_id',
                'namasupir',
                'saldo',
                'deposito',
                'penarikan',
                'total',
                'keterangan',
                'cicil',
                'keterangan2',
                'keterangandeposito',
                'judullaporan',
                'judul',
                'tglcetak',
                'usercetak',
                'disetujui',
                'diperiksa',
            ], (new LaporanDepositoSupir())->getReport($tglsd, '', 1));

            // Kas Gantung

            // mulai:;

            $tempkasgantung = '##tempkasgantung' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkasgantung, function ($table) {
                $table->datetime('tanggal')->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->string('keterangan', 500)->nullable();
                $table->double('debet')->nullable();
                $table->double('kredit')->nullable();
                $table->double('saldo')->nullable();
                $table->string('disetujui', 500)->nullable();
                $table->string('diperiksa', 500)->nullable();
                $table->string('judul', 500)->nullable();
                $table->string('judullaporan', 500)->nullable();
                $table->longtext('tglcetak')->nullable();
                $table->string('usercetak', 500)->nullable();
            });

            $tempkasgantungtnl = '##tempkasgantungtnl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkasgantungtnl, function ($table) {
                $table->datetime('tanggal')->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->string('keterangan', 500)->nullable();
                $table->double('debet')->nullable();
                $table->double('kredit')->nullable();
                $table->double('saldo')->nullable();
                $table->string('disetujui', 500)->nullable();
                $table->string('diperiksa', 500)->nullable();
                $table->string('judul', 500)->nullable();
                $table->string('judullaporan', 500)->nullable();
                $table->longtext('tglcetak')->nullable();
                $table->string('usercetak', 500)->nullable();
            });




            // if ($tglsaldo==$tgluji) {
            //     $tglkasbank = date('Y-m-d', strtotime($tglsd1));
            // } else {
            //     $tglkasbank = date('Y-m-d', strtotime($tglsd1 . ' -1 days'));
            // }

            // dd($tglsd);
            DB::table($tempkasgantung)->insertUsing([
                'tanggal',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'disetujui',
                'diperiksa',
                'judul',
                'judullaporan',
                'tglcetak',
                'usercetak',
            ], (new LaporanKasGantung())->getReport($tglsd, 1, 1));

            // dd(db::table($tempkasgantung)->get());

            $getcabang = $parameter->cekText('CABANG', 'CABANG') ?? '';
            if ($getcabang == 'MAKASSAR') {
                DB::table($tempkasgantungtnl)->insertUsing([
                    'tanggal',
                    'nobukti',
                    'keterangan',
                    'debet',
                    'kredit',
                    'saldo',
                    'disetujui',
                    'diperiksa',
                    'judul',
                    'judullaporan',
                    'tglcetak',
                    'usercetak',
                ], (new LaporanKasGantung())->getReport($tglsd, 1, 6));
            }


            // Kas 

            $tempkas = '##tempkas' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkas, function ($table) {
                $table->id();
                $table->integer('urut')->nullable();
                $table->integer('urutdetail')->nullable();
                $table->string('keterangancoa', 500)->nullable();
                $table->string('namabank', 500)->nullable();
                $table->date('tglbukti')->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->longText('keterangan')->nullable();
                $table->double('debet')->nullable();
                $table->double('kredit')->nullable();
                $table->double('totaldebet')->nullable();
                $table->double('totalkredit')->nullable();
                $table->double('saldo')->nullable();
                $table->string('judullaporan', 500)->nullable();
                $table->string('judul', 500)->nullable();
                $table->string('tglcetak', 500)->nullable();
                $table->string('usercetak', 500)->nullable();
            });






            // dd($tglsaldo,$tgluji);
            if ($tglsaldo == $tgluji) {
                $tglkasbank = date('Y-m-d', strtotime($tglsd1));
            } else {
                $tglkasbank = date('Y-m-d', strtotime($tglsd1 . ' -1 days'));
            }



            $kas_id = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text'
                )
                ->where('grp', 'KAS/BANK')
                ->where('subgrp', 'KAS')
                ->first()->text ?? 0;


            DB::table($tempkas)->insertUsing([
                'urut',
                'urutdetail',
                'keterangancoa',
                'namabank',
                'tglbukti',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'totaldebet',
                'totalkredit',
                'saldo',
                'judullaporan',
                'judul',
                'tglcetak',
                'usercetak',
            ], (new LaporanKasBank())->getReport($tglkasbank, $tglkasbank, $kas_id, 1));

            // dd($tglkasbank);
            // dd(db::table($tempkas)->get());

            $parameter = new Parameter();

            $getcabang = $parameter->cekText('CABANG', 'CABANG') ?? '1900-01-01';
            if ($getcabang == 'MAKASSAR') {
                $tempkastnl = '##tempkastnl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempkastnl, function ($table) {
                    $table->id();
                    $table->integer('urut')->nullable();
                    $table->integer('urutdetail')->nullable();
                    $table->string('keterangancoa', 500)->nullable();
                    $table->string('namabank', 500)->nullable();
                    $table->date('tglbukti')->nullable();
                    $table->string('nobukti', 500)->nullable();
                    $table->longText('keterangan')->nullable();
                    $table->double('debet')->nullable();
                    $table->double('kredit')->nullable();
                    $table->double('totaldebet')->nullable();
                    $table->double('totalkredit')->nullable();
                    $table->double('saldo')->nullable();
                    $table->string('judullaporan', 500)->nullable();
                    $table->string('judul', 500)->nullable();
                    $table->string('tglcetak', 500)->nullable();
                    $table->string('usercetak', 500)->nullable();
                });



                // $tglkasbank = date('Y-m-d', strtotime($tglsd1 . ' -1 days'));

                $kas_idtnl = 6;

                // if ($tglsaldo == $tgluji) {
                $tglkasbank = date('Y-m-d', strtotime($tglsd1));
                // } else {
                //     $tglkasbank = date('Y-m-d', strtotime($tglsd1 . ' -1 days'));
                // }
                // dd($tglkasbank, $kas_idtnl,$tgluji,$tglsd1);
                DB::table($tempkastnl)->insertUsing([
                    'urut',
                    'urutdetail',
                    'keterangancoa',
                    'namabank',
                    'tglbukti',
                    'nobukti',
                    'keterangan',
                    'debet',
                    'kredit',
                    'totaldebet',
                    'totalkredit',
                    'saldo',
                    'judullaporan',
                    'judul',
                    'tglcetak',
                    'usercetak',
                ], (new LaporanKasBank())->getReport($tglkasbank, $tglkasbank, $kas_idtnl, 1));
            }


            // dd(db::table($tempkas)->get());

            // Bank 

            $tempbank = '##tempbank' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbank, function ($table) {
                $table->id();
                $table->integer('urut')->nullable();
                $table->integer('urutdetail')->nullable();
                $table->string('keterangancoa', 500)->nullable();
                $table->string('namabank', 500)->nullable();
                $table->date('tglbukti')->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->longText('keterangan')->nullable();
                $table->double('debet')->nullable();
                $table->double('kredit')->nullable();
                $table->double('totaldebet')->nullable();
                $table->double('totalkredit')->nullable();
                $table->double('saldo')->nullable();
                $table->string('judullaporan', 500)->nullable();
                $table->string('judul', 500)->nullable();
                $table->string('tglcetak', 500)->nullable();
                $table->string('usercetak', 500)->nullable();
            });




            $bank_id = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text'
                )
                ->where('grp', 'KAS/BANK')
                ->where('subgrp', 'BANK')
                ->first()->text ?? 0;
            DB::table($tempbank)->insertUsing([
                'urut',
                'urutdetail',
                'keterangancoa',
                'namabank',
                'tglbukti',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'totaldebet',
                'totalkredit',
                'saldo',
                'judullaporan',
                'judul',
                'tglcetak',
                'usercetak',
            ], (new LaporanKasBank())->getReport($tglkasbank, $tglkasbank, $bank_id, 1));

            // saldopersediaan 

            // mulai:;

            $tempstok = '##tempstok' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempstok, function ($table) {
                $table->string('header', 500)->nullable();
                $table->string('judul', 500)->nullable();
                $table->string('lokasi', 500)->nullable();
                $table->string('namalokasi', 500)->nullable();
                $table->string('kategori', 500)->nullable();
                $table->string('tgldari', 500)->nullable();
                $table->string('tglsampai', 500)->nullable();
                $table->string('stokdari', 500)->nullable();
                $table->string('stoksampai', 500)->nullable();
                $table->string('vulkanisirke', 500)->nullable();
                $table->string('id', 500)->nullable();
                $table->string('kodebarang', 500)->nullable();
                $table->string('namabarang', 500)->nullable();
                $table->string('tanggal', 500)->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->string('satuan', 500)->nullable();
                $table->double('nilaisaldo', 15, 2)->nullable();
                $table->string('disetujui', 500)->nullable();
                $table->string('diperiksa', 500)->nullable();
            });




            DB::table($tempstok)->insertUsing([
                'header',
                'judul',
                'lokasi',
                'namalokasi',
                'kategori',
                'tgldari',
                'tglsampai',
                'stokdari',
                'stoksampai',
                'vulkanisirke',
                'id',
                'kodebarang',
                'namabarang',
                'tanggal',
                'qty',
                'satuan',
                'nilaisaldo',
                'disetujui',
                'diperiksa',
            ], (new LaporanSaldoInventory())->getReport('', '', '', 186, 364, $tglsd, 0, 0, 1, 1));

            //  dd(db::table($tempdepositosupir)->get());
            // dd('test');
            $coahutangusaha = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.memo'
                )
                ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                ->where('subgrp', 'HUTANG USAHA')
                ->where('text', 'HUTANG USAHA')
                ->first();
            $memo = json_decode($coahutangusaha->memo, true);


            $hutangusaha = db::table($tempkartuhutang)->from(db::raw($tempkartuhutang . " a"))
                ->select(
                    db::raw("sum(saldobayar) as nominal")
                )->first()->nominal ?? 0;

            DB::table($tempperkiraanbanding)->insert(
                [
                    'coa' =>  $memo['JURNAL'],
                    'nominal' => $hutangusaha,
                ]
            );

            $coapiutangusaha = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.memo'
                )
                ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                ->where('subgrp', 'PIUTANG USAHA')
                ->where('text', 'PIUTANG USAHA')
                ->first();
            $memo = json_decode($coapiutangusaha->memo, true);


            $piutangusaha = db::table($tempkartupiutang)->from(db::raw($tempkartupiutang . " a"))
                ->select(
                    db::raw("sum(saldobayar) as nominal")
                )->first()->nominal ?? 0;

            DB::table($tempperkiraanbanding)->insert(
                [
                    'coa' =>  $memo['JURNAL'],
                    'nominal' => $piutangusaha,
                ]
            );


            // pinjaman supir

            $coapinjamansupir = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.memo'
                )
                ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                ->where('subgrp', 'PINJAMAN SUPIR')
                ->where('text', 'PINJAMAN SUPIR')
                ->first();
            $memo = json_decode($coapinjamansupir->memo, true);


            $pinjamansupir = db::table($temppinjamansupir)->from(db::raw($temppinjamansupir . " a"))
                ->select(
                    db::raw("sum(debet-kredit) as nominal")
                )->first()->nominal ?? 0;

            DB::table($tempperkiraanbanding)->insert(
                [
                    'coa' =>  $memo['JURNAL'],
                    'nominal' => $pinjamansupir,
                ]
            );

            // pinjaman karyawan

            $coapinjamankaryawan = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.memo'
                )
                ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                ->where('subgrp', 'PINJAMAN KARYAWAN')
                ->where('text', 'PINJAMAN KARYAWAN')
                ->first();
            $memo = json_decode($coapinjamankaryawan->memo, true);


            $pinjamankaryawan = db::table($temppinjamankaryawan)->from(db::raw($temppinjamankaryawan . " a"))
                ->select(
                    db::raw("sum(debet-kredit) as nominal")
                )->first()->nominal ?? 0;

            DB::table($tempperkiraanbanding)->insert(
                [
                    'coa' =>  $memo['JURNAL'],
                    'nominal' => $pinjamankaryawan,
                ]
            );
            if ($getcabang == 'MAKASSAR') {
                // dd(db::table($tempkastnl)->get());
                // kas fisik tnl
                $coakastnl = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                    ->select(
                        'a.memo'
                    )
                    ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                    ->where('subgrp', 'KAS FISIK TNL')
                    ->where('text', 'KAS FISIK TNL')
                    ->first();
                $memo = json_decode($coakastnl->memo, true);

                $kastnl = db::table($tempkastnl)->from(db::raw($tempkastnl . " a"))
                    ->select(
                        db::raw("saldo as nominal")
                    )->first()->nominal ?? 0;

                DB::table($tempperkiraanbanding)->insert(
                    [
                        'coa' =>  $memo['JURNAL'],
                        'nominal' => $kastnl,
                    ]
                );
            }

            // Piutang Lain

            $coapiutanglain = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.memo'
                )
                ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                ->where('subgrp', 'PIUTANG LAIN')
                ->where('text', 'PIUTANG LAIN')
                ->first();
            $memo = json_decode($coapiutanglain->memo, true);


            $piutanglain = db::table($temppiutanglain)->from(db::raw($temppiutanglain . " a"))
                ->select(
                    db::raw("sum(nominal) as nominal")
                )->first()->nominal ?? 0;

            $parameter = new Parameter();

            $cabang = $parameter->cekText('ID CABANG', 'ID CABANG') ?? '0';

            $tempcabangpiutanglain = '##tempcabangpiutanglain' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempcabangpiutanglain, function ($table) {
                $table->integer('id')->nullable();
            });

            DB::table($tempcabangpiutanglain)->insert(
                [
                    'id' =>  3,
                ]
            );

            DB::table($tempcabangpiutanglain)->insert(
                [
                    'id' =>  7,
                ]
            );

            $querycabang = db::table($tempcabangpiutanglain)->from(db::raw($tempcabangpiutanglain . " a"))
                ->select(
                    'a.id'
                )
                ->where('a.id', $cabang)
                ->first();
            if (isset($querycabang)) {
                DB::table($tempperkiraanbanding)->insert(
                    [
                        'coa' =>  $memo['JURNAL'],
                        'nominal' => $piutanglain,
                    ]
                );
            }




            // Deposito Supir

            $coadepositosupir = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.memo'
                )
                ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                ->where('subgrp', 'DEPOSITO SUPIR')
                ->where('text', 'DEPOSITO SUPIR')
                ->first();
            $memo = json_decode($coadepositosupir->memo, true);


            $depositosupir = db::table($tempdepositosupir)->from(db::raw($tempdepositosupir . " a"))
                ->select(
                    db::raw("sum(total) as nominal")
                )->first()->nominal ?? 0;

            DB::table($tempperkiraanbanding)->insert(
                [
                    'coa' =>  $memo['JURNAL'],
                    'nominal' => $depositosupir,
                ]
            );

            // Kas gantung

            $coakasgantung = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.memo'
                )
                ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                ->where('subgrp', 'KAS GANTUNG')
                ->where('text', 'KAS GANTUNG')
                ->first();
            $memo = json_decode($coakasgantung->memo, true);


            // dd(db::table($tempkasgantung)->get());
            $kasgantung = db::table($tempkasgantung)->from(db::raw($tempkasgantung . " a"))
                ->select(
                    db::raw("sum(debet-kredit) as nominal")
                )->first()->nominal ?? 0;

            DB::table($tempperkiraanbanding)->insert(
                [
                    'coa' =>  $memo['JURNAL'],
                    'nominal' => $kasgantung,
                ]
            );

            if ($getcabang == 'MAKASSAR') {
                $coakasgantungtnl = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                    ->select(
                        'a.memo'
                    )
                    ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                    ->where('subgrp', 'KAS GANTUNG TNL')
                    ->where('text', 'KAS GANTUNG TNL')
                    ->first();
                $memo = json_decode($coakasgantungtnl->memo, true);


                // dd(db::table($tempkasgantung)->get());
                $kasgantung = db::table($tempkasgantungtnl)->from(db::raw($tempkasgantungtnl . " a"))
                    ->select(
                        db::raw("sum(debet-kredit) as nominal")
                    )->first()->nominal ?? 0;

                DB::table($tempperkiraanbanding)->insert(
                    [
                        'coa' =>  $memo['JURNAL'],
                        'nominal' => $kasgantung,
                    ]
                );
            }

            // Kas harian

            $coakas = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.memo'
                )
                ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                ->where('subgrp', 'KAS FISIK')
                ->where('text', 'KAS FISIK')
                ->first();
            $memo = json_decode($coakas->memo, true);

            //    dd(db::table($tempkas)->get());
            $kas = db::table($tempkas)->from(db::raw($tempkas . " a"))
                ->select(
                    db::raw("saldo as nominal")
                )
                ->orderBy('id', 'desc')
                ->first()->nominal ?? 0;

            DB::table($tempperkiraanbanding)->insert(
                [
                    'coa' =>  $memo['JURNAL'],
                    'nominal' => $kas,
                ]
            );

            // Kas harian

            $coabank = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.memo'
                )
                ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                ->where('subgrp', 'BCA-1')
                ->where('text', 'BCA-1')
                ->first();
            $memo = json_decode($coabank->memo, true);


            $bank = db::table($tempbank)->from(db::raw($tempbank . " a"))
                ->select(
                    db::raw("a.saldo as nominal")
                )->orderBy('id', 'desc')
                ->first()->nominal ?? 0;

            DB::table($tempperkiraanbanding)->insert(
                [
                    'coa' =>  $memo['JURNAL'],
                    'nominal' => $bank,
                ]
            );

            // Sparepart

            $coastok = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.memo'
                )
                ->where('grp', 'PERKIRAAN PEMBANDING NERACA')
                ->where('subgrp', 'SPAREPART')
                ->where('text', 'SPAREPART')
                ->first();
            $memo = json_decode($coastok->memo, true);


            $stok = db::table($tempstok)->from(db::raw($tempstok . " a"))
                ->select(
                    db::raw("sum(nilaisaldo) as nominal")
                )
                ->first()->nominal ?? 0;

            DB::table($tempperkiraanbanding)->insert(
                [
                    'coa' =>  $memo['JURNAL'],
                    'nominal' => $stok,
                ]
            );
            // dd(db::table($tempperkiraanbanding)->get());



            DB::table('akunpusatdetail')
                ->where('bulan', '<>', 0)
                ->whereRaw("bulan=" . $bulan . " and tahun=" . $tahun)
                ->delete();


            if ($getcabangid == 1) {
                $coalabarugiberjalan = '05.02.01.01';
            } else if ($getcabangid == 2) {
                $coalabarugiberjalan = '05.02.02.01';
            } else if ($getcabangid == 3) {
                $coalabarugiberjalan = '05.02.03.01';
            } else if ($getcabangid == 4) {
                $coalabarugiberjalan = '05.02.04.01';
            } else if ($getcabangid == 5) {
                $coalabarugiberjalan = '05.02.05.01';
            } else if ($getcabangid == 6) {
                $coalabarugiberjalan = '05.02.07.01';
            } else if ($getcabangid == 7) {
                $coalabarugiberjalan = '05.02.08.01';
            } else {
                $coalabarugiberjalan = '05.02.01.01';
            }

            $querytahunsaldo = db::table("akunpusatdetail")->from(db::raw("akunpusatdetail a with (readuncommitted)"))
                ->select(
                    'a.coa'
                )
                ->where('bulan', '=', 0)
                ->whereRaw("tahun=" . $tahun)
                ->whereRaw("coa='" . $coalabarugiberjalan . "'")
                ->First();

            if (isset($querytahunsaldo)) {
                $subquery1 = DB::table('jurnalumumpusatheader as J')
                    ->select('D.coamain as FCOA', DB::raw('YEAR(D.tglbukti) as FThn'), DB::raw('MONTH(D.tglbukti) as FBln'), DB::raw('round(SUM(D.nominal),2) as FNominal'))
                    ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
                    ->join('mainakunpusat as C', 'C.coa', '=', 'D.coamain')
                    ->where('D.tglbukti', '>=', $ptgl)
                    ->whereraw("D.coamain<>'" . $coalabarugiberjalan . "'")
                    ->groupBy('D.coamain', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));
            } else {
                $subquery1 = DB::table('jurnalumumpusatheader as J')
                    ->select('D.coamain as FCOA', DB::raw('YEAR(D.tglbukti) as FThn'), DB::raw('MONTH(D.tglbukti) as FBln'), DB::raw('round(SUM(D.nominal),2) as FNominal'))
                    ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
                    ->join('mainakunpusat as C', 'C.coa', '=', 'D.coamain')
                    ->where('D.tglbukti', '>=', $ptgl)
                    // ->whereraw("D.coamain<>'" . $coalabarugiberjalan . "'")
                    ->groupBy('D.coamain', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));
            }


            if ($cabang == 'SEMUA') {
                $subquery2 = DB::table('jurnalumumpusatheader as J')
                    ->select('LR.coa', DB::raw('YEAR(D.tglbukti) as FThn'), DB::raw('MONTH(D.tglbukti) as FBln'), DB::raw('round(SUM(D.nominal),2) as FNominal'))
                    ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
                    ->join('perkiraanlabarugi as LR', function ($join) {
                        $join->on('LR.tahun', '=', DB::raw('YEAR(J.tglbukti)'))
                            ->on('LR.bulan', '=', DB::raw('MONTH(J.tglbukti)'));
                    })
                    ->whereIn('D.coamain', function ($query) {
                        $query->select(DB::raw('DISTINCT C.coa'))
                            ->from('maintypeakuntansi as AT')
                            ->join('mainakunpusat as C', 'AT.kodetype', '=', 'C.Type')
                            ->where('AT.order', '>=', 4000)
                            ->where('AT.order', '<', 9000)
                            ->where('C.type', '<>', 'Laba/Rugi');
                    })
                    ->where('D.tglbukti', '>=', $ptgl)
                    ->groupBy('LR.coa', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));
            } else {

                // dd($ptgl);
                $subquery2 = DB::table('jurnalumumpusatheader as J')
                    ->select('LR.coa', DB::raw('YEAR(D.tglbukti) as FThn'), DB::raw('MONTH(D.tglbukti) as FBln'), DB::raw('round(SUM(D.nominal),2) as FNominal'))
                    ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
                    ->join('perkiraanlabarugi as LR', function ($join) use ($cabang_id) {
                        $join->on('LR.tahun', '=', DB::raw('YEAR(J.tglbukti)'))
                            ->on('LR.bulan', '=', DB::raw('MONTH(J.tglbukti)'))
                            ->on('LR.cabang_id', '=', DB::raw($cabang_id));
                    })
                    ->whereIn('D.coamain', function ($query) {
                        $query->select(DB::raw('DISTINCT C.coa'))
                            ->from('maintypeakuntansi as AT')
                            ->join('mainakunpusat as C', 'AT.kodetype', '=', 'C.Type')
                            ->where('AT.order', '>=', 4000)
                            ->where('AT.order', '<', 9000)
                            ->where('C.type', '<>', 'Laba/Rugi');
                    })
                    ->whereraw("month(D.tglbukti)=month('" . $ptgl . "') and year(D.tglbukti)=year('" . $ptgl . "')")
                    ->groupBy('LR.coa', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));
            }

            $RecalKdPerkiraan = DB::table(DB::raw("({$subquery1->toSql()} UNION ALL {$subquery2->toSql()}) as V"))
                ->mergeBindings($subquery1)
                ->mergeBindings($subquery2)
                ->groupBy('FCOA', 'FThn', 'FBln')
                ->select('FCOA', 'FThn', 'FBln', DB::raw('round(SUM(FNominal),2) as FNominal'));

            // dd($RecalKdPerkiraan->toSql());
            DB::table('akunpusatdetail')->insertUsing([
                'coa',
                'tahun',
                'bulan',
                'nominal',

            ], $RecalKdPerkiraan);

            // laba rugi

            $tempsaldolabarugi = '##tempsaldolabarugi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempsaldolabarugi, function ($table) {
                $table->bigIncrements('id');
                $table->integer('tahun')->nullable();
                $table->integer('bulan')->nullable();
                $table->double('nominal')->nullable();
            });

            $saldolabarugi = db::table("akunpusatdetail")->from(db::raw("akunpusatdetail a with (readuncommitted)"))
                ->select(
                    'a.nominal'
                )
                ->whereraw("a.tahun=year('" . $ptgl . "')")
                ->whereraw("a.bulan=0")
                ->whereraw("a.coa='" . $coalabarugiberjalan . "'")
                ->first()->nominal ?? 0;




            $subquery1test = DB::table('jurnalumumpusatheader as J')
                ->select(DB::raw('YEAR(D.tglbukti) as FThn'), DB::raw('MONTH(D.tglbukti) as FBln'), DB::raw("round(SUM(D.nominal),2)-(" . $saldolabarugi . ") as FNominal"))
                ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
                ->join('mainakunpusat as C', 'C.coa', '=', 'D.coamain')
                // ->whereraw("month(D.tglbukti)=month('". $ptgl."') and year(D.tglbukti)=year('".$ptgl."')")
                ->whereRaw("D.tglbukti>='" . $tahun . "/1/1'")
                ->whereraw("year(D.tglbukti)=year('" . $ptgl . "')")
                ->whereIn('D.coamain', function ($query) {
                    $query->select(DB::raw('DISTINCT C.coa'))
                        ->from('maintypeakuntansi as AT')
                        ->join('mainakunpusat as C', 'AT.kodetype', '=', 'C.Type')
                        ->where('AT.order', '>=', 4000)
                        ->where('AT.order', '<', 9000)
                        ->where('C.type', '<>', 'Laba/Rugi');
                })
                ->whereRaw("D.tglbukti>='" . $tahun . "/1/1'")
                ->whereraw("year(D.tglbukti)=year('" . $ptgl . "')")
                ->groupBy(DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'))
                ->OrderBY(DB::raw('YEAR(D.tglbukti)'), 'asc')
                ->OrderBY(DB::raw('MONTH(D.tglbukti)'), 'asc');

            DB::table($tempsaldolabarugi)->insertUsing([
                'tahun',
                'bulan',
                'nominal',

            ], $subquery1test);

            $tempsaldolabarugi2 = '##tempsaldolabarugi2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempsaldolabarugi2, function ($table) {
                $table->bigIncrements('id');
                $table->integer('tahun')->nullable();
                $table->integer('bulan')->nullable();
                $table->double('nominal')->nullable();
            });

            $querysaldolr2 = db::table($tempsaldolabarugi)->from(db::raw($tempsaldolabarugi . " a"))
                ->select(
                    'a.tahun',
                    db::raw("(a.bulan+1) as bulan"),
                    db::raw("a.nominal"),
                )
                ->orderbY('a.bulan', 'asc')
                ->orderbY('a.tahun', 'asc');



            DB::table($tempsaldolabarugi2)->insertUsing([
                'tahun',
                'bulan',
                'nominal',

            ], $querysaldolr2);



            $querysaldolr = db::table($tempsaldolabarugi)->from(db::raw($tempsaldolabarugi . " a"))
                ->select(
                    db::raw("'" . $coalabarugiberjalan . "' as coa"),
                    'a.tahun',
                    'a.bulan',
                    db::raw("round((case when a.bulan>1 then a.nominal-isnull(b.nominal,0) else a.nominal end),2) as nominal"),
                    // db::raw("a.nominal"),
                    // db::raw("isnull(b.nominal,0)"),
                )
                ->leftjoin(db::raw($tempsaldolabarugi2 . " as b"), 'a.bulan', '=', 'b.bulan')

                ->orderbY('a.bulan', 'asc')
                ->orderbY('a.tahun', 'asc');

            // dd($querysaldolr->get());


            if (isset($querytahunsaldo)) {
                // dd('test');
                DB::table('akunpusatdetail')
                    ->where('bulan', '<>', 0)
                    ->whereRaw("tahun=" . $tahun)
                    ->whereRaw("coa='" . $coalabarugiberjalan . "'")
                    ->delete();

                DB::table('akunpusatdetail')->insertUsing([
                    'coa',
                    'tahun',
                    'bulan',
                    'nominal',

                ], $querysaldolr);
            }






            if ($bulan == 1) {
                DB::table('akunpusatdetail')
                    ->where('bulan', '=', 0)
                    ->where('tahun', '=', $tahun)
                    ->delete();

                $tahunsaldo = $tahun - 1;


                $tempAkunPusatDetailsaldo = '##tempAkunPusatDetailsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempAkunPusatDetailsaldo, function ($table) {
                    $table->bigIncrements('id');
                    $table->string('coa', 50)->nullable();
                    $table->integer('tahun')->nullable();
                    $table->integer('bulan')->nullable();
                    $table->double('nominal')->nullable();
                });

                $querysaldo1 = db::table('akunpusatdetail')->from(db::raw("akunpusatdetail a with (readuncommitted)"))
                    ->select(
                        'a.coa',
                        db::raw($tahun . ' as tahun'),
                        db::raw('0 as bulan'),
                        db::raw("sum(a.nominal) as nominal")
                    )
                    ->where('a.tahun', $tahunsaldo)
                    ->groupBy('a.coa');

                DB::table($tempAkunPusatDetailsaldo)->insertUsing([
                    'coa',
                    'tahun',
                    'bulan',
                    'nominal',

                ], $querysaldo1);


                $querysaldo2 = db::table('saldoakunpusatdetail')->from(db::raw("saldoakunpusatdetail a with (readuncommitted)"))
                    ->select(
                        'a.coa',
                        db::raw($tahun . ' as tahun'),
                        db::raw('0 as bulan'),
                        db::raw("sum(a.nominal) as nominal")
                    )
                    ->where('a.tahun', $tahunsaldo)
                    ->groupBy('a.coa');

                DB::table($tempAkunPusatDetailsaldo)->insertUsing([
                    'coa',
                    'tahun',
                    'bulan',
                    'nominal',

                ], $querysaldo2);
                // dd(db::table($tempAkunPusatDetailsaldo)->where('coa','01.01.01.03' )->get());


                $querysaldo = db::table($tempAkunPusatDetailsaldo)->from(db::raw($tempAkunPusatDetailsaldo . " a"))
                    ->select(
                        'a.coa',
                        db::raw($tahun . ' as tahun'),
                        db::raw('0 as bulan'),
                        db::raw("sum(a.nominal) as nominal")
                    )
                    ->where('a.tahun', $tahun)
                    // ->where('coa','01.01.01.03' )
                    ->groupBy('a.coa');

                // dd($querysaldo->get());



                DB::table('akunpusatdetail')->insertUsing([
                    'coa',
                    'tahun',
                    'bulan',
                    'nominal',

                ], $querysaldo);
            }

            $tempAkunPusatDetail = '##tempAkunPusatDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempAkunPusatDetail, function ($table) {
                $table->bigIncrements('id');
                $table->string('coa', 50)->nullable();
                $table->integer('bulan')->nullable();
                $table->integer('tahun')->nullable();
                $table->double('nominal')->nullable();
                $table->string('modifiedby')->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
            });

            $queryTempSaldoAkunPusatDetail = DB::table('saldoakunpusatdetail')->from(
                DB::raw('saldoakunpusatdetail')
            )
                ->select(
                    'coa',
                    'bulan',
                    'tahun',
                    'nominal',
                    'modifiedby',
                    'created_at',
                    'updated_at'

                )
                ->orderBy('id', 'asc');

            DB::table($tempAkunPusatDetail)->insertUsing([
                'coa',
                'bulan',
                'tahun',
                'nominal',
                'modifiedby',
                'created_at',
                'updated_at',

            ], $queryTempSaldoAkunPusatDetail);

            // test 123455
            $queryTempAkunPusatDetail = DB::table('akunpusatdetail')->from(
                DB::raw('akunpusatdetail')
            )
                ->select(
                    'coa',
                    'bulan',
                    'tahun',
                    'nominal',
                    'modifiedby',
                    'created_at',
                    'updated_at'

                )
                // ->whereraw("bulan<" . $bulan)
                // ->where('tahun', $tahun)
                ->orderBy('id', 'asc');

            DB::table($tempAkunPusatDetail)->insertUsing([
                'coa',
                'bulan',
                'tahun',
                'nominal',
                'modifiedby',
                'created_at',
                'updated_at',

            ], $queryTempAkunPusatDetail);

            $tempquery1 = '##tempquery1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempquery1, function ($table) {
                $table->bigIncrements('id');
                $table->string('type', 500)->nullable();
                $table->string('coa', 500)->nullable();
                $table->string('keterangancoa', 500)->nullable();
                $table->string('parent', 500)->nullable();
                $table->integer('statusaktif')->nullable();
                $table->integer('statusneraca')->nullable();
                $table->integer('statuslabarugi')->nullable();
                $table->integer('tahun')->nullable();
                $table->integer('bulan')->nullable();
                $table->double('nominal')->nullable();
                $table->integer('order')->nullable();
                $table->string('keterangantype', 500)->nullable();
                $table->integer('akuntansi_id')->nullable();
            });


            $query1 = db::table('mainakunpusat')->from(db::raw("mainakunpusat c with (readuncommitted)"))
                ->select(
                    'c.type',
                    'c.coa',
                    'c.keterangancoa',
                    'c.parent',
                    'c.statusaktif',
                    'c.statusneraca',
                    'c.statuslabarugi',
                    db::raw("isnull(cd.tahun," . $tahun . ") as tahun"),
                    db::raw("isnull(cd.bulan,0) as bulan"),
                    db::raw("round(isnull(cd.nominal,0),2) as nominal"),
                    'a.order',
                    'a.keterangantype',
                    'a.akuntansi_id',
                )
                ->join(db::raw($tempAkunPusatDetail . " cd with (readuncommitted)"), 'c.coa', 'cd.coa')
                ->join(db::raw("maintypeakuntansi a with (readuncommitted)"), 'a.kodetype', 'c.type');

            // dd(db::table($tempAkunPusatDetail)->get());

            DB::table($tempquery1)->insertUsing([
                'type',
                'coa',
                'keterangancoa',
                'parent',
                'statusaktif',
                'statusneraca',
                'statuslabarugi',
                'tahun',
                'bulan',
                'nominal',
                'order',
                'keterangantype',
                'akuntansi_id',

            ], $query1);


            $tempquery2 = '##tempquery2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempquery2, function ($table) {
                $table->bigIncrements('id');
                $table->string('tipemaster', 500)->nullable();
                $table->integer('order')->nullable();
                $table->string('type', 500)->nullable();
                $table->string('keterangantype', 500)->nullable();
                $table->string('coa', 500)->nullable();
                $table->string('parent', 500)->nullable();
                $table->string('keterangancoa', 500)->nullable();
                $table->double('nominal')->nullable();
                $table->string('cmpyname', 500)->nullable();
                $table->integer('pbulan')->nullable();
                $table->integer('ptahun')->nullable();
                $table->integer('gneraca')->nullable();
                $table->integer('glr')->nullable();
                $table->string('keterangancoaparent', 500)->nullable();
                $table->string('ptglsd', 50)->nullable();
            });


            $query2 = db::table($tempquery1)->from(db::raw($tempquery1 . " d"))
                ->select(
                    db::raw("(CASE d.akuntansi_id WHEN 1 THEN 'AKTIVA' ELSE 'PASSIVA' END) AS tipemaster"),
                    'd.order',
                    db::raw("max(d.type) as type"),
                    db::raw("max(d.keterangantype) as keterangantype"),
                    'd.coa',
                    db::raw("max(d.parent) as parent"),
                    'd.keterangancoa',
                    db::raw("( CASE d.akuntansi_id WHEN 1 THEN round(SUM(d.Nominal),2) ELSE round(SUM(d.Nominal * -1),2) END)  AS nominal"),
                    db::raw("'" . $judulLaporan . "' as cmpyname"),
                    db::raw($bulan . " as pbulan"),
                    db::raw($tahun . " as ptahun"),
                    db::raw("max(d.statusneraca) as gneraca"),
                    db::raw("max(d.statuslabarugi) as glr"),
                    db::raw("max(isnull(e.keterangancoa,'')) as keterangancoaparent"),
                    db::raw($tglsd . " as ptglsd"),
                )
                ->leftjoin(db::raw("akunpusat e with (readuncommitted)"), 'd.parent', 'e.coa')
                ->where('d.tahun', $tahun)
                ->whereRaw("d.bulan<=cast(" . $bulan . " as integer)")
                ->where('d.order', '<', 4000)
                ->groupBy('d.akuntansi_id')
                ->groupBy('d.order')
                ->groupBy('d.coa')
                ->groupBy('d.keterangancoa');
            // ->having(DB::raw('sum(d.nominal)'), '<>', 0);

            $query2test = db::table($tempquery1)->from(db::raw($tempquery1 . " d"))
                ->select(
                    db::raw("(CASE d.akuntansi_id WHEN 1 THEN 'AKTIVA' ELSE 'PASSIVA' END) AS tipemaster"),
                    'd.order',
                    db::raw("(d.type) as type"),
                    db::raw("(d.keterangantype) as keterangantype"),
                    'd.coa',
                    db::raw("(d.parent) as parent"),
                    'd.keterangancoa',
                    db::raw("( CASE d.akuntansi_id WHEN 1 THEN round((d.Nominal),2) ELSE round((d.Nominal * -1),2) END)  AS nominal"),
                    db::raw("'" . $judulLaporan . "' as cmpyname"),
                    db::raw($bulan . " as pbulan"),
                    db::raw($tahun . " as ptahun"),
                    db::raw("(d.statusneraca) as gneraca"),
                    db::raw("(d.statuslabarugi) as glr"),
                    db::raw("(isnull(e.keterangancoa,'')) as keterangancoaparent"),
                    db::raw($tglsd . " as ptglsd"),
                )
                ->leftjoin(db::raw("akunpusat e with (readuncommitted)"), 'd.parent', 'e.coa')
                ->where('d.tahun', $tahun)
                ->whereRaw("d.bulan<=cast(" . $bulan . " as integer)")
                ->where('d.order', '<', 4000)
                ->whereraw("d.coa='" . $coalabarugiberjalan . "'");

            // dd($query2test->get());


            // dd($query2->tosql());

            DB::table($tempquery2)->insertUsing([
                'tipemaster',
                'order',
                'type',
                'keterangantype',
                'coa',
                'parent',
                'keterangancoa',
                'nominal',
                'cmpyname',
                'pbulan',
                'ptahun',
                'gneraca',
                'glr',
                'keterangancoaparent',
                'ptglsd',
            ], $query2);

            $data = db::table($tempquery2)->from(db::raw($tempquery2 . " xx"))
                ->select(
                    'xx.TipeMaster',
                    'xx.Order',
                    'xx.Type',
                    'xx.KeteranganType',
                    'xx.coa',
                    'xx.Parent',
                    'xx.KeteranganCoa',
                    db::raw("round(xx.Nominal,2) as Nominal"),
                    'xx.CmpyName',
                    'xx.pBulan',
                    'xx.pTahun',
                    'xx.GNeraca',
                    'xx.GLR',
                    'xx.KeteranganCoaParent',
                    'xx.pTglSd',
                    db::raw("isnull(b.coa,'') as coabanding"),
                    db::raw("round(isnull(b.nominal,0),2) as nominalbanding"),
                    db::raw(" cast((case when isnull(b.coa,'')<>'' and 
                (round(isnull(b.nominal,0),2)-round(isnull(xx.Nominal,0),2)) <>0 then 1 else 0 end) as bit)
                as selisih")
                )
                ->leftjoin(db::raw($tempperkiraanbanding . " b"), 'xx.coa', 'b.coa')
                ->whereRaw("isnull(xx.Nominal,0)<>0")
                ->orderby('xx.id');



            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );


            Schema::create($temtabel, function (Blueprint $table) {
                $table->id();
                $table->longText('TipeMaster')->nullable();
                $table->integer('Order')->nullable();
                $table->string('Type', 1000)->nullable();
                $table->string('KeteranganType', 1000)->nullable();
                $table->string('coa', 1000)->nullable();
                $table->string('Parent', 500)->nullable();
                $table->string('KeteranganCoa', 500)->nullable();
                $table->double('Nominal', 15, 2)->nullable();
                $table->string('CmpyName', 500)->nullable();
                $table->integer('pBulan')->nullable();
                $table->integer('pTahun')->nullable();
                $table->integer('GNeraca')->nullable();
                $table->integer('GLR')->nullable();
                $table->string('KeteranganCoaParent', 500)->nullable();
                $table->string('pTglSd', 500)->nullable();
                $table->string('coabanding', 500)->nullable();
                $table->double('nominalbanding', 15, 2)->nullable();
                $table->boolean('selisih',)->nullable();
            });

            DB::table($temtabel)->insertUsing([
                'TipeMaster',
                'Order',
                'Type',
                'KeteranganType',
                'coa',
                'Parent',
                'KeteranganCoa',
                'Nominal',
                'CmpyName',
                'pBulan',
                'pTahun',
                'GNeraca',
                'GLR',
                'KeteranganCoaParent',
                'pTglSd',
                'coabanding',
                'nominalbanding',
                'selisih',
            ], $data);
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }

        if ($cabang_id != $getcabangid) {
            $getJudul = db::table('cabang')->from(db::raw("cabang a with (readuncommitted)"))
                ->select(
                    'a.judullaporan as text'
                )
                ->where('a.id', $cabang_id)
                ->first();
        } else {
            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();
        }

        $data = db::table($temtabel)->from(db::raw($temtabel . " xx"))
            ->select(
                'xx.TipeMaster',
                'xx.Order',
                'xx.Type',
                'xx.KeteranganType',
                'xx.coa',
                'xx.Parent',
                'xx.KeteranganCoa',
                db::raw("round(xx.Nominal,2) as Nominal"),
                'xx.CmpyName',
                'xx.pBulan',
                'xx.pTahun',
                'xx.GNeraca',
                'xx.GLR',
                'xx.KeteranganCoaParent',
                'xx.pTglSd',
                db::raw("xx.coabanding"),
                db::raw("xx.nominalbanding"),
                db::raw("xx.selisih"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                db::raw("'' as Cabang"),
                DB::raw("'" . auth('api')->user()->name . "' as usercetak")
            )
            ->orderby('xx.id');

        selesai:;

        // $data = DB::select(DB::raw("
        //         SELECT xx.TipeMaster, xx.[Order], xx.[Type], xx.KeteranganType, xx.coa, xx.Parent,
        //         xx.KeteranganCoa, xx.Nominal, xx.CmpyName, xx.pBulan, xx.pTahun,
        //         xx.GNeraca, xx.GLR, xx.KeteranganCoaParent, xx.pTglSd
        // FROM
        // (
        //     SELECT CASE d.akuntansi_id WHEN 1 THEN 'AKTIVA' ELSE 'PASSIVA' END AS TipeMaster,
        //         d.[order], MAX(d.[Type]) AS Type, MAX(d.keterangantype) AS KeteranganType,
        //         d.coa, MAX(d.Parent) AS Parent,
        //         d.Keterangancoa,
        //         CASE d.akuntansi_id WHEN 1 THEN SUM(d.Nominal) ELSE SUM(d.Nominal * -1) END AS Nominal,
        //         '$judulLaporan' AS CmpyName,
        //         MAX($bulan) AS pBulan, MAX($tahun) AS pTahun,
        //         MAX(d.statusneraca) AS GNeraca, MAX(d.statuslabarugi) AS GLR,
        //         (SELECT KeteranganCoa FROM akunpusat WHERE coa = MAX(d.Parent)) AS KeteranganCoaParent,
        //         '$tglsd' AS pTglSd
        //     FROM
        //     (
        //         SELECT C.[type], C.coa, C.keterangancoa,
        //             C.Parent, C.statusaktif, C.statusneraca, C.statuslabarugi,
        //             ISNULL(cd.tahun, $tahun) AS Tahun,
        //             ISNULL(cd.bulan, 0) AS Bulan,
        //             ISNULL(cd.nominal, 0) AS Nominal,
        //             A.[Order], A.keterangantype, A.akuntansi_id
        //         FROM mainakunpusat C
        //         LEFT OUTER JOIN $tempAkunPusatDetail cd ON C.coa = cd.coa
        //         INNER JOIN maintypeakuntansi A ON A.[kodetype] = C.[type]
        //     ) d
        //     WHERE (d.Tahun = $tahun) AND (d.Bulan <= $bulan) AND (d.[Order] < 4000)
        //     GROUP BY d.akuntansi_id, d.[order], d.coa, d.keterangancoa
        //     HAVING SUM(d.Nominal) <> 0
        // ) xx
        // "));


        return $data->get();
    }
}
