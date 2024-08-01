<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ExportPerhitunganBonus extends Model
{
    use HasFactory;


    public function getReport($periode, $tahun, $cabang_id)
    {

        $pperiode = $periode;
        $pthn = $tahun;
        $pcabang_id = $cabang_id;


        if ($pperiode == 1) {
            $ptgl1 = $pthn . '/1/1';
            $ptgl2 = $pthn . '/2/1';
            $ptgl3 = $pthn . '/3/1';
        }

        if ($pperiode == 2) {
            $ptgl1 = $pthn . '/4/1';
            $ptgl2 = $pthn . '/5/1';
            $ptgl3 = $pthn . '/6/1';
        }

        if ($pperiode == 3) {
            $ptgl1 = $pthn . '/7/1';
            $ptgl2 = $pthn . '/8/1';
            $ptgl3 = $pthn . '/9/1';
        }

        if ($pperiode == 4) {
            $ptgl1 = $pthn . '/10/1';
            $ptgl2 = $pthn . '/11/1';
            $ptgl3 = $pthn . '/12/1';
        }



        $pTglDr1 = date('Y-m-d', strtotime($ptgl1));
        $pTglSd1 = date('Y-m-t', strtotime($pTglDr1));

        $pTglDr2 = date('Y-m-d', strtotime($ptgl2));
        $pTglSd2 = date('Y-m-t', strtotime($pTglDr2));
        $pTglDr3 = date('Y-m-d', strtotime($ptgl3));
        $pTglSd3 = date('Y-m-t', strtotime($pTglDr3));

        // dd('test');

        $tempcoaPendapatan = '##tempcoaPendapatan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempcoaPendapatan, function ($table) {
            $table->bigIncrements('id');
            $table->string('ketmain', 200)->nullable();
            $table->integer('ordermain')->nullable();
            $table->string('ftype', 200)->nullable();
            $table->string('fcoa', 200)->nullable();
            $table->string('fketcoa', 200)->nullable();
            $table->double('nominal1')->nullable();
            $table->double('nominal2')->nullable();
            $table->double('nominal3')->nullable();
            $table->string('cmpyname', 300)->nullable();
            $table->integer('glr')->nullable();
            $table->integer('pperiode')->nullable();
            $table->integer('tahun')->nullable();
            $table->integer('forder')->nullable();
            $table->string('fcoaws', 200)->nullable();
            $table->string('fparent', 200)->nullable();
            $table->string('fketparent', 300)->nullable();
            $table->string('pcabang', 200)->nullable();
        });

        $judul = Parameter::where('grp', '=', 'JUDULAN LAPORAN')->first();
        $cmpyname = $judul->text;

        $querycoaPendapatan = db::table('mainakunpusat')->from(db::raw("mainakunpusat c with (readuncommitted)"))
            ->select(
                db::raw("'PENDAPATAN :' as ketmain"),
                db::raw("1 as ordermain"),
                'at.keterangantype as ftype',
                'c.coa as fcoa',
                'c.keterangancoa as fketcoa',
                db::raw("CAST(0 AS MONEY) AS nominal1"),
                db::raw("CAST(0 AS MONEY) AS nominal2"),
                db::raw("CAST(0 AS MONEY) AS nominal3"),
                db::raw("'" . $cmpyname . "' AS cmpyname"),
                'c.statuslabarugi as glr',
                db::raw($pperiode . " AS pperiode"),
                db::raw($pthn . " AS pthn"),
                db::raw("at.[order] AS forder"),
                'c.coa as fcoaws',
                'c.Parent as fparent',
                db::raw("isnull(c1.keterangancoa,'')  AS fketparent"),
                db::raw($pcabang_id . " as pcabang")
            )
            ->join(db::raw("maintypeakuntansi at with (readuncommitted)"), 'at.id', 'c.type_id')
            ->leftjoin(db::raw("mainakunpusat c1 with (readuncommitted)"), 'c.parent', 'c1.coa')
            ->whereRaw("AT.[KeteranganType] IN ('Pendapatan') ");

        DB::table($tempcoaPendapatan)->insertUsing([
            'ketmain',
            'ordermain',
            'ftype',
            'fcoa',
            'fketcoa',
            'nominal1',
            'nominal2',
            'nominal3',
            'cmpyname',
            'glr',
            'pperiode',
            'tahun',
            'forder',
            'fcoaws',
            'fparent',
            'fketparent',
            'pcabang',
        ], $querycoaPendapatan);

        $tempcoaBiaya = '##tempcoaBiaya' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempcoaBiaya, function ($table) {
            $table->bigIncrements('id');
            $table->string('ketmain', 200)->nullable();
            $table->integer('ordermain')->nullable();
            $table->string('ftype', 200)->nullable();
            $table->string('fcoa', 200)->nullable();
            $table->string('fketcoa', 200)->nullable();
            $table->double('nominal1')->nullable();
            $table->double('nominal2')->nullable();
            $table->double('nominal3')->nullable();
            $table->string('cmpyname', 300)->nullable();
            $table->integer('glr')->nullable();
            $table->integer('pperiode')->nullable();
            $table->integer('tahun')->nullable();
            $table->integer('forder')->nullable();
            $table->string('fcoaws', 200)->nullable();
            $table->string('fparent', 200)->nullable();
            $table->string('fketparent', 300)->nullable();
            $table->string('pcabang', 200)->nullable();
        });

        $querycoaBiaya = db::table('mainakunpusat')->from(db::raw("mainakunpusat c with (readuncommitted)"))
            ->select(
                db::raw("'BIAYA - BIAYA :' as ketmain"),
                db::raw("2 as ordermain"),
                'at.keterangantype as ftype',
                'c.coa as fcoa',
                'c.keterangancoa as fketcoa',
                db::raw("CAST(0 AS MONEY) AS nominal1"),
                db::raw("CAST(0 AS MONEY) AS nominal2"),
                db::raw("CAST(0 AS MONEY) AS nominal3"),
                db::raw("'" . $cmpyname . "' AS cmpyname"),
                'c.statuslabarugi as glr',
                db::raw($pperiode . " AS pperiode"),
                db::raw($pthn . " AS pthn"),
                db::raw("at.[order] AS forder"),
                'c.coa as fcoaws',
                'c.Parent as fparent',
                db::raw("isnull(c1.keterangancoa,'')  AS fketparent"),
                db::raw($pcabang_id . " as pcabang")
            )
            ->join(db::raw("maintypeakuntansi at with (readuncommitted)"), 'at.id', 'c.type_id')
            ->leftjoin(db::raw("mainakunpusat c1 with (readuncommitted)"), 'c.parent', 'c1.coa')
            ->whereRaw("AT.[KeteranganType] IN ('Beban') ");

        DB::table($tempcoaBiaya)->insertUsing([
            'ketmain',
            'ordermain',
            'ftype',
            'fcoa',
            'fketcoa',
            'nominal1',
            'nominal2',
            'nominal3',
            'cmpyname',
            'glr',
            'pperiode',
            'tahun',
            'forder',
            'fcoaws',
            'fparent',
            'fketparent',
            'pcabang',
        ], $querycoaBiaya);

        $tempJ_RAppPendapatan1 = '##tempJ_RAppPendapatan1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempJ_RAppPendapatan1, function ($table) {
            $table->bigIncrements('id');
            $table->string('fcoamain', 200)->nullable();
            $table->double('fnominal')->nullable();
        });

        $tempJ_RAppPendapatan2 = '##tempJ_RAppPendapatan2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempJ_RAppPendapatan2, function ($table) {
            $table->bigIncrements('id');
            $table->string('fcoamain', 200)->nullable();
            $table->double('fnominal')->nullable();
        });

        $tempJ_RAppPendapatan3 = '##tempJ_RAppPendapatan3' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempJ_RAppPendapatan3, function ($table) {
            $table->bigIncrements('id');
            $table->string('fcoamain', 200)->nullable();
            $table->double('fnominal')->nullable();
        });


        $queryJ_RAppPendapatan1 = db::table('jurnalumumpusatdetail')->from(db::raw("jurnalumumpusatdetail d with (readuncommitted)"))
            ->select(
                'd.coamain as fcoamain',
                db::raw("sum(-d.nominal) as fnominal")
            )
            ->join(db::raw($tempcoaPendapatan . " cd"), 'cd.fcoa', 'd.coamain')
            ->join(db::raw("jurnalumumpusatheader  e with (readuncommitted)"), 'd.nobukti', 'e.nobukti')
            ->whereRaw("d.tglbukti between '" . $pTglDr1 . "' and '" . $pTglSd1 . "'")
            ->whereRaw("(ISNULL(e.cabang_id,'')=" . $pcabang_id . " OR " . $pcabang_id . "=0)")
            ->groupby('d.coamain');

        // dd( $queryJ_RAppPendapatan1->get());

        DB::table($tempJ_RAppPendapatan1)->insertUsing([
            'fcoamain',
            'fnominal',
        ], $queryJ_RAppPendapatan1);

        $queryJ_RAppPendapatan2 = db::table('jurnalumumpusatdetail')->from(db::raw("jurnalumumpusatdetail d with (readuncommitted)"))
            ->select(
                'd.coamain as fcoamain',
                db::raw("sum(-d.nominal) as fnominal")
            )
            ->join(db::raw($tempcoaPendapatan . " cd"), 'cd.fcoa', 'd.coamain')
            ->join(db::raw("jurnalumumpusatheader  e with (readuncommitted)"), 'd.nobukti', 'e.nobukti')
            ->whereRaw("d.tglbukti between '" . $pTglDr2 . "' and '" . $pTglSd2 . "'")
            ->whereRaw("(ISNULL(e.cabang_id,'')=" . $pcabang_id . " OR " . $pcabang_id . "=0)")
            ->groupby('d.coamain');

        DB::table($tempJ_RAppPendapatan2)->insertUsing([
            'fcoamain',
            'fnominal',
        ], $queryJ_RAppPendapatan2);


        $queryJ_RAppPendapatan3 = db::table('jurnalumumpusatdetail')->from(db::raw("jurnalumumpusatdetail d with (readuncommitted)"))
            ->select(
                'd.coamain as fcoamain',
                db::raw("sum(-d.nominal) as fnominal")
            )
            ->join(db::raw($tempcoaPendapatan . " cd"), 'cd.fcoa', 'd.coamain')
            ->join(db::raw("jurnalumumpusatheader  e with (readuncommitted)"), 'd.nobukti', 'e.nobukti')
            ->whereRaw("d.tglbukti between '" . $pTglDr3 . "' and '" . $pTglSd3 . "'")
            ->whereRaw("(ISNULL(e.cabang_id,'')=" . $pcabang_id . " OR " . $pcabang_id . "=0)")
            ->groupby('d.coamain');

        DB::table($tempJ_RAppPendapatan3)->insertUsing([
            'fcoamain',
            'fnominal',
        ], $queryJ_RAppPendapatan3);


        $tempJ_RAppBiaya1 = '##tempJ_RAppBiaya1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempJ_RAppBiaya1, function ($table) {
            $table->bigIncrements('id');
            $table->string('fcoamain', 200)->nullable();
            $table->double('fnominal')->nullable();
        });

        $tempJ_RAppBiaya2 = '##tempJ_RAppBiaya2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempJ_RAppBiaya2, function ($table) {
            $table->bigIncrements('id');
            $table->string('fcoamain', 200)->nullable();
            $table->double('fnominal')->nullable();
        });

        $tempJ_RAppBiaya3 = '##tempJ_RAppBiaya3' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempJ_RAppBiaya3, function ($table) {
            $table->bigIncrements('id');
            $table->string('fcoamain', 200)->nullable();
            $table->double('fnominal')->nullable();
        });

        $queryJ_RAppBiaya1 = db::table('jurnalumumpusatdetail')->from(db::raw("jurnalumumpusatdetail d with (readuncommitted)"))
            ->select(
                'd.coamain as fcoamain',
                db::raw("sum(d.nominal) as fnominal")
            )
            ->join(db::raw($tempcoaBiaya . " cd"), 'cd.fcoa', 'd.coamain')
            ->join(db::raw("jurnalumumpusatheader  e with (readuncommitted)"), 'd.nobukti', 'e.nobukti')
            ->whereRaw("d.tglbukti between '" . $pTglDr1 . "' and '" . $pTglSd1 . "'")
            ->whereRaw("(ISNULL(e.cabang_id,'')=" . $pcabang_id . " OR " . $pcabang_id . "=0)")
            ->groupby('d.coamain');

        DB::table($tempJ_RAppBiaya1)->insertUsing([
            'fcoamain',
            'fnominal',
        ], $queryJ_RAppBiaya1);

        $queryJ_RAppBiaya2 = db::table('jurnalumumpusatdetail')->from(db::raw("jurnalumumpusatdetail d with (readuncommitted)"))
            ->select(
                'd.coamain as fcoamain',
                db::raw("sum(d.nominal) as fnominal")
            )
            ->join(db::raw($tempcoaBiaya . " cd"), 'cd.fcoa', 'd.coamain')
            ->join(db::raw("jurnalumumpusatheader  e with (readuncommitted)"), 'd.nobukti', 'e.nobukti')
            ->whereRaw("d.tglbukti between '" . $pTglDr2 . "' and '" . $pTglSd2 . "'")
            ->whereRaw("(ISNULL(e.cabang_id,'')=" . $pcabang_id . " OR " . $pcabang_id . "=0)")
            ->groupby('d.coamain');

        DB::table($tempJ_RAppBiaya2)->insertUsing([
            'fcoamain',
            'fnominal',
        ], $queryJ_RAppBiaya2);


        $queryJ_RAppBiaya3 = db::table('jurnalumumpusatdetail')->from(db::raw("jurnalumumpusatdetail d with (readuncommitted)"))
            ->select(
                'd.coamain as fcoamain',
                db::raw("sum(d.nominal) as fnominal")
            )
            ->join(db::raw($tempcoaBiaya . " cd"), 'cd.fcoa', 'd.coamain')
            ->join(db::raw("jurnalumumpusatheader  e with (readuncommitted)"), 'd.nobukti', 'e.nobukti')
            ->whereRaw("d.tglbukti between '" . $pTglDr3 . "' and '" . $pTglSd3 . "'")
            ->whereRaw("(ISNULL(e.cabang_id,'')=" . $pcabang_id . " OR " . $pcabang_id . "=0)")
            ->groupby('d.coamain');

        DB::table($tempJ_RAppBiaya3)->insertUsing([
            'fcoamain',
            'fnominal',
        ], $queryJ_RAppBiaya3);


        DB::update(DB::raw("UPDATE " . $tempcoaPendapatan . " SET nominal1=b.fnominal 
        from " . $tempcoaPendapatan . " a inner join " . $tempJ_RAppPendapatan1 . " b on a.fcoa=b.fcoamain"));

        DB::update(DB::raw("UPDATE " . $tempcoaPendapatan . " SET nominal2=b.fnominal 
        from " . $tempcoaPendapatan . " a inner join " . $tempJ_RAppPendapatan2 . " b on a.fcoa=b.fcoamain"));

        DB::update(DB::raw("UPDATE " . $tempcoaPendapatan . " SET nominal3=b.fnominal 
        from " . $tempcoaPendapatan . " a inner join " . $tempJ_RAppPendapatan3 . " b on a.fcoa=b.fcoamain"));

        DB::update(DB::raw("UPDATE " . $tempcoaBiaya . " SET nominal1=b.fnominal 
        from " . $tempcoaBiaya . " a inner join " . $tempJ_RAppBiaya1 . " b on a.fcoa=b.fcoamain"));

        DB::update(DB::raw("UPDATE " . $tempcoaBiaya . " SET nominal2=b.fnominal 
        from " . $tempcoaBiaya . " a inner join " . $tempJ_RAppBiaya2 . " b on a.fcoa=b.fcoamain"));

        DB::update(DB::raw("UPDATE " . $tempcoaBiaya . " SET nominal3=b.fnominal 
        from " . $tempcoaBiaya . " a inner join " . $tempJ_RAppBiaya3 . " b on a.fcoa=b.fcoamain"));

        $tempcoaPendapatanBiaya = '##tempcoaPendapatanBiaya' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempcoaPendapatanBiaya, function ($table) {
            $table->bigIncrements('id');
            $table->integer('jenis')->nullable();
            $table->string('ketmain', 200)->nullable();
            $table->integer('ordermain')->nullable();
            $table->string('ftype', 200)->nullable();
            $table->string('fcoa', 200)->nullable();
            $table->string('fketcoa', 200)->nullable();
            $table->double('nominal1')->nullable();
            $table->double('nominal2')->nullable();
            $table->double('nominal3')->nullable();
            $table->string('cmpyname', 300)->nullable();
            $table->integer('glr')->nullable();
            $table->integer('pperiode')->nullable();
            $table->integer('tahun')->nullable();
            $table->integer('forder')->nullable();
            $table->string('fcoaws', 200)->nullable();
            $table->string('fparent', 200)->nullable();
            $table->string('fketparent', 300)->nullable();
            $table->string('pcabang', 200)->nullable();
        });


        $querycoaPendapatanBiaya = db::table($tempcoaPendapatan)->from(db::raw($tempcoaPendapatan . " a"))
            ->select(
                db::raw("0 as jenis"),
                'a.ketmain',
                'a.ordermain',
                'a.ftype',
                'a.fcoa',
                'a.fketcoa',
                'a.nominal1',
                'a.nominal2',
                'a.nominal3',
                'a.cmpyname',
                'a.glr',
                'a.pperiode',
                'a.tahun',
                'a.forder',
                'a.fcoaws',
                'a.fparent',
                'a.fketparent',
                'a.pCabang'
            );


        DB::table($tempcoaPendapatanBiaya)->insertUsing([
            'jenis',
            'ketmain',
            'ordermain',
            'ftype',
            'fcoa',
            'fketcoa',
            'nominal1',
            'nominal2',
            'nominal3',
            'cmpyname',
            'glr',
            'pperiode',
            'tahun',
            'forder',
            'fcoaws',
            'fparent',
            'fketparent',
            'pcabang',
        ], $querycoaPendapatanBiaya);

        $querycoaPendapatanBiaya = db::table($tempcoaBiaya)->from(db::raw($tempcoaBiaya . " a"))
            ->select(
                db::raw("1 as jenis"),
                'a.ketmain',
                'a.ordermain',
                'a.ftype',
                'a.fcoa',
                'a.fketcoa',
                'a.nominal1',
                'a.nominal2',
                'a.nominal3',
                'a.cmpyname',
                'a.glr',
                'a.pperiode',
                'a.tahun',
                'a.forder',
                'a.fcoaws',
                'a.fparent',
                 db::raw("(case when c.parent in('07.04.01.00','07.04.02.00') then 'ADMINISTRASI & KANTOR' else isnull(b.keterangancoa,'') end) as fketparent"),
                'a.pCabang'
            )
            ->leftjoin(db::raw("mainakunpusat b with (readuncommitted)"), 'a.fparent', 'b.coa')
            ->join(db::raw("mainakunpusat c with (readuncommitted)"), 'a.fcoa', 'c.coa')
            ->whereraw("isnull(b.keterangancoa,'') not in ('KENDARAAN')");


        DB::table($tempcoaPendapatanBiaya)->insertUsing([
            'jenis',
            'ketmain',
            'ordermain',
            'ftype',
            'fcoa',
            'fketcoa',
            'nominal1',
            'nominal2',
            'nominal3',
            'cmpyname',
            'glr',
            'pperiode',
            'tahun',
            'forder',
            'fcoaws',
            'fparent',
            'fketparent',
            'pcabang',
        ], $querycoaPendapatanBiaya);

        $querycoaPendapatanBiaya = db::table($tempcoaBiaya)->from(db::raw($tempcoaBiaya . " a"))
        ->select(
            db::raw("1 as jenis"),
            db::raw("'BIAYA - BIAYA :' as ketmain"),
            db::raw("max(a.ordermain) as ordermain"),
            db::raw("max(a.ftype) as ftype"),
            db::raw("'07.01.01.27' as fcoa"),
            db::raw("'B. KENDARAAN' as fketcoa"),
            db::raw("sum(a.nominal1) as nominal1"),
            db::raw("sum(a.nominal2) as nominal2"),
            db::raw("sum(a.nominal3) as nominal3"),
            db::raw("max(a.cmpyname) as cmpyname"),
            db::raw("max(a.glr) as glr"),
            db::raw("max(a.pperiode) as pperiode"),
            db::raw("max(a.tahun) as tahun"),
            db::raw("max(a.forder) as forder"),
            db::raw("max(a.fcoaws) as fcoaws"),
            db::raw("max(a.fparent) as fparent"),
            // db::raw("(case when a.fparent in('07.01.00.00','07.03.00.00') then 'Biaya Operasional' else 'Biaya Umum Dan Adm' end) as fketparent"),
            db::raw("'ADMINISTRASI & KANTOR' as fketparent"),
            db::raw("max(a.pCabang) as pCabang")
        )
        ->leftjoin(db::raw("mainakunpusat b with (readuncommitted)"), 'a.fparent', 'b.coa')
        ->whereraw("isnull(b.keterangancoa,'') in ('KENDARAAN')");
        


    DB::table($tempcoaPendapatanBiaya)->insertUsing([
        'jenis',
        'ketmain',
        'ordermain',
        'ftype',
        'fcoa',
        'fketcoa',
        'nominal1',
        'nominal2',
        'nominal3',
        'cmpyname',
        'glr',
        'pperiode',
        'tahun',
        'forder',
        'fcoaws',
        'fparent',
        'fketparent',
        'pcabang',
    ], $querycoaPendapatanBiaya);        


        $queryhasil = db::table($tempcoaPendapatanBiaya)->from(db::raw($tempcoaPendapatanBiaya . " a with (readuncommitted)"))
            ->select(
                'a.jenis',
                'a.ketmain',
                'a.ordermain',
                'a.ftype',
                'a.fcoa',
                'a.fketcoa',
                'a.nominal1',
                'a.nominal2',
                'a.nominal3',
                'a.cmpyname',
                'a.glr',
                'a.pperiode',
                'a.tahun',
                'a.forder',
                'a.fcoaws',
                'a.fparent',
                'a.fketparent',
                'a.pCabang'
            )



            ->whereraw("isnull(nominal1,0)<>0 or isnull(nominal2,0)<>0 or isnull(nominal3,0)<>0")
            ->OrderBy('a.jenis', 'asc')
            ->OrderBy('a.ftype', 'asc')
            ->OrderBy('a.fcoa', 'asc')
            ->OrderBy('a.fketparent', 'asc');


        $data = $queryhasil->get();

        // dd($data);
        return $data;



        // return $data = 
        // [
        //     [
        //         "perkiraan" =>  "Pendapatan - Usaha Jakarta",
        //         "bulankesatu" => "1450580500",
        //         "bulankedua" => "1488409000",
        //         "bulanketiga" => "1615542000",
        //     ],
        //     [
        //         "perkiraan" => "Pendapatan - Lain",
        //         "bulankesatu" => "16427500.00",
        //         "bulankedua" => "20385060.00",
        //         "bulanketiga" => "20745500.00",
        //     ],
        //     [
        //         "perkiraan"=>"Pendapatan - Bunga",
        //         "bulankesatu"=>"478158.86",
        //         "bulankedua"=>"213181.58",
        //         "bulanketiga"=>"179752.96",
        //     ],
        //     [
        //         "perkiraan" =>"Potongan Pendapatan Usaha",
        //         "bulankesatu" =>"0",
        //         "bulankedua" =>"-81000.00",
        //         "bulanketiga" =>"-300000.00"
        //     ],
        //     [
        //         "perkiraan" =>"TOTAL PENDAPATAN",
        //         "bulankesatu" =>"1467486158.86",
        //         "bulankedua" =>"1508926241.58",
        //         "bulanketiga" =>"1636167252.96"
        //     ]
        // ];





    }
}
