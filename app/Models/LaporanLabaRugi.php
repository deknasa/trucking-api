<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanLabaRugi extends MyModel
{
    use HasFactory;

    protected $table = '';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];
    // $sampai = date("Y-m-d", strtotime($sampai));
    // // data coba coba
    // $query = DB::table('penerimaantruckingdetail')->from(
    //     DB::raw("penerimaantruckingdetail with (readuncommitted)")
    // )->select(
    //     'penerimaantruckingdetail.id',
    //     'supir.namasupir',
    //     'penerimaantruckingdetail.nominal',
    // )
    // ->leftJoin(DB::raw("supir with (readuncommitted)"), 'penerimaantruckingdetail.supir_id', 'supir.id')
    // ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.penerimaantruckingheader_id', 'penerimaantruckingheader.id')
    // ->where('penerimaantruckingheader.tglbukti','<=',$sampai);

    // $data = $query->get();
    // return $data;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getReport($bulan, $tahun, $cabang_id)
    {

        $cabang_id = $cabang_id ?? 0;

        $getcabangid = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text'
            )
            ->where('a.grp', 'ID CABANG')
            ->where('a.subgrp', 'ID CABANG')
            ->first()->text ?? 0;


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


        $cmpy = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->value('text');

        $Temprekappendapatan = '##Temprekappendapatan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temprekappendapatan, function ($table) {
            $table->string('coamain', 30);
            $table->double('nominal');
        });

        $select_Temprekappendapatan = DB::table('jurnalumumpusatdetail')->from(DB::raw("jurnalumumpusatdetail AS D WITH (READUNCOMMITTED)"))

            ->select(
                'D.coamain',
                DB::raw('SUM(-D.Nominal)')
            )

            ->join(DB::raw("jurnalumumpusatheader as H with (readuncommitted)"), 'H.nobukti', '=', 'D.nobukti')
            ->join('mainakunpusat as CD', 'CD.COA', '=', 'D.coamain')
            ->whereRaw("MONTH(D.tglbukti) = " . $bulan . " AND YEAR(D.tglbukti) = " . $tahun)
            ->whereraw("(h.cabang_id=" . $cabang_id . " or " . $cabang_id . "=0)")

            ->groupBy('D.coamain');

        // dd("Adas");
        DB::table($Temprekappendapatan)->insertUsing([
            'coamain',
            'nominal',
        ], $select_Temprekappendapatan);
        // dd($select_Temprekappendapatan->get());

        $TempLabaRugi = '##TempLabaRugi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempLabaRugi, function ($table) {
            $table->bigIncrements('id');
            $table->string('keteranganmain', 500);
            $table->integer('ordermain');
            $table->string('type', 1000);
            $table->string('coa', 100);
            $table->string('keterangancoa', 1000);
            $table->double('nominal');
            $table->double('nominalparent');
            $table->string('cmpyname', 300);
            $table->integer('statuslabarugi');
            $table->integer('bln');
            $table->integer('thn');
            $table->integer('order');
            $table->string('parent', 30);
            $table->string('KeteranganParent', 1000);
            $table->string('diperiksa', 1000);
            $table->string('disetujui', 1000);
            $table->string('judul', 1000);
            $table->string('cabang', 1000);
            $table->string('usercetak', 1000);
        });

        $TempLabaRugiParent = '##TempLabaRugiParent' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempLabaRugiParent, function ($table) {
            $table->string('KeteranganParent', 1000);
            $table->double('nominal');
        });

        $resultsparent = DB::table('mainakunpusat AS C')
            ->select(
                DB::raw("ISNULL(G.keterangancoa, '') AS KeteranganParent"),
                DB::raw('sum(ISNULL(E.nominal, 0)) AS Nominal'),
            )
            ->join('mainTypeakuntansi AS AT', 'AT.id', '=', 'C.type_id')
            ->leftJoin('mainakunpusat AS G', 'C.parent', '=', 'G.coa')
            ->leftJoin($Temprekappendapatan . ' AS E', 'C.coa', '=', 'E.CoaMAin')
            ->whereIn('AT.kodetype', ['Pendapatan'])
            ->whereRaw("isnull(E.nominal,0)<>0")
            ->whereRaw("ISNULL(G.keterangancoa, '')<>''")
            ->groupBy('G.keterangancoa');

        DB::table($TempLabaRugiParent)->insertUsing([
            'KeteranganParent',
            'nominal',
        ], $resultsparent);

        $results2parent = DB::table('mainakunpusat AS C')
            ->select(
                DB::raw("ISNULL(G.keterangancoa, '') AS KeteranganParent"),
                DB::raw("sum(ISNULL(E.nominal, 0)) AS Nominal"),
            )
            ->join('mainTypeakuntansi AS AT', 'AT.id', '=', 'C.type_id')
            ->leftJoin('mainakunpusat AS G', 'C.parent', '=', 'G.coa')
            ->leftJoin($Temprekappendapatan . ' AS E', 'C.coa', '=', 'E.CoaMAin')
            ->whereIn('AT.kodetype', ['Beban'])
            ->whereRaw("isnull(E.nominal,0)<>0")
            ->whereRaw("ISNULL(G.keterangancoa, '')<>''")
            ->groupBy('G.keterangancoa');

        DB::table($TempLabaRugiParent)->insertUsing([
            'KeteranganParent',
            'nominal',
        ], $results2parent);

        //    $cmpy = 'PT. TRANSPORINDO AGUNG SEJAHTERA';


        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';


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

        $results = DB::table('mainakunpusat AS C')
            ->select(
                DB::raw("'PENDAPATAN :' AS keteranganmain"),
                DB::raw('1 AS ordermain'),
                'AT.kodeType AS type',
                'C.COA AS coa',
                'C.keterangancoa',
                DB::raw('ISNULL(E.nominal, 0) AS Nominal'),
                DB::raw('ISNULL(f.nominal, 0) AS Nominalparent'),
                DB::raw("'$cmpy' AS CmpyName"),
                'C.statuslabarugi',
                DB::raw("'$bulan' AS bulan"),
                DB::raw("'$tahun' AS tahun"),
                'AT.Order',
                'C.Parent',
                DB::raw("ISNULL(G.keterangancoa, '') AS KeteranganParent"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                db::raw("(case when '" . $cabang . "'='' then '' else 'Cabang : " . $cabang . "'  end) as Cabang"),
                DB::raw("'" . auth('api')->user()->name . "' as usercetak")
            )
            ->join('mainTypeakuntansi AS AT', 'AT.id', '=', 'C.type_id')
            ->leftJoin('mainakunpusat AS G', 'C.parent', '=', 'G.coa')
            ->leftJoin($Temprekappendapatan . ' AS E', 'C.coa', '=', 'E.CoaMAin')
            ->leftJoin($TempLabaRugiParent . ' AS f', 'g.keterangancoa', '=', 'f.KeteranganParent')
            ->whereIn('AT.kodetype', ['Pendapatan'])
            ->whereRaw("isnull(E.nominal,0)<>0")
            ->orderBy('coa');

        DB::table($TempLabaRugi)->insertUsing([
            'keteranganmain',
            'ordermain',
            'type',
            'coa',
            'keterangancoa',
            'nominal',
            'nominalparent',
            'cmpyname',
            'statuslabarugi',
            'bln',
            'thn',
            'order',
            'parent',
            'KeteranganParent',
            'diperiksa',
            'disetujui',
            'judul',
            'cabang',
            'usercetak'
        ], $results);
        // dd($results->get()); 

        $results2 = DB::table('mainakunpusat AS C')
            ->select(
                DB::raw("'BIAYA - BIAYA :' AS keteranganmain"),
                DB::raw('2 AS OrderMain'),
                'AT.kodeType AS type',
                'C.COA AS coa',
                'C.keterangancoa',
                DB::raw('ISNULL(E.nominal, 0) AS Nominal'),
                DB::raw('ISNULL(f.nominal, 0) AS Nominalparent'),
                DB::raw("'$cmpy' AS CmpyName"),
                'C.statuslabarugi',
                DB::raw("'$bulan' AS bulan"),
                DB::raw("'$tahun' AS tahun"),
                'AT.Order',
                'C.Parent',
                DB::raw("ISNULL(G.keterangancoa, '') AS KeteranganParent"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                db::raw("(case when '" . $cabang . "'='' then '' else 'Cabang :" . $cabang . "'  end) as Cabang"),
                DB::raw("'" . auth('api')->user()->name . "' as usercetak")
            )
            ->join('mainTypeakuntansi AS AT', 'AT.id', '=', 'C.type_id')
            ->leftJoin('mainakunpusat AS G', 'C.parent', '=', 'G.coa')
            ->leftJoin($Temprekappendapatan . ' AS E', 'C.coa', '=', 'E.CoaMAin')
            ->leftJoin($TempLabaRugiParent . ' AS f', 'g.keterangancoa', '=', 'f.KeteranganParent')
            ->whereIn('AT.kodetype', ['Beban'])
            ->whereRaw("isnull(E.nominal,0)<>0");

        DB::table($TempLabaRugi)->insertUsing([
            'keteranganmain',
            'ordermain',
            'type',
            'coa',
            'keterangancoa',
            'nominal',
            'nominalparent',
            'cmpyname',
            'statuslabarugi',
            'bln',
            'thn',
            'order',
            'parent',
            'KeteranganParent',
            'diperiksa',
            'disetujui',
            'judul',
            'cabang',
            'usercetak'
        ], $results2);

        $data1 = $results->get();
        $data2 = $results2->get();

        $mergedData = $data1->concat($data2);
        // return [$data1, $data2];
        return $mergedData;
    }

    public function getExport($bulan, $tahun)
    {
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $cmpy = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->value('text');

        $Temprekappendapatan = '##Temprekappendapatan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temprekappendapatan, function ($table) {
            $table->string('coamain', 30);
            $table->double('nominal');
        });

        $select_Temprekappendapatan = DB::table('jurnalumumpusatdetail')->from(DB::raw("jurnalumumpusatdetail AS D WITH (READUNCOMMITTED)"))

            ->select(
                'D.coamain',
                DB::raw('SUM(-D.Nominal)')
            )

            ->join(DB::raw("jurnalumumpusatheader as H with (readuncommitted)"), 'H.nobukti', '=', 'D.nobukti')
            ->join('mainakunpusat as CD', 'CD.COA', '=', 'D.coamain')
            ->whereRaw('MONTH(D.tglbukti) = ? AND YEAR(D.tglbukti) = ?', [$bulan, $tahun])
            ->groupBy('D.coamain');
        // dd("Adas");
        DB::table($Temprekappendapatan)->insertUsing([
            'coamain',
            'nominal',
        ], $select_Temprekappendapatan);
        // dd($select_Temprekappendapatan->get());

        $TempLabaRugi = '##TempLabaRugi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempLabaRugi, function ($table) {
            $table->bigIncrements('id');
            $table->string('keteranganmain', 500);
            $table->integer('ordermain');
            $table->string('type', 1000);
            $table->string('coa', 100);
            $table->string('keterangancoa', 1000);
            $table->double('nominal');
            $table->double('nominalparent');
            $table->string('cmpyname', 300);
            $table->integer('statuslabarugi');
            $table->integer('bln');
            $table->integer('thn');
            $table->integer('order');
            $table->string('parent', 30);
            $table->string('KeteranganParent', 1000);
            $table->string('diperiksa', 1000);
            $table->string('disetujui', 1000);
        });

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        // $cmpy = 'PT. TRANSPORINDO AGUNG SEJAHTERA';
        $bulan = '02';
        $tahun = '2023';


        $results = DB::table('mainakunpusat AS C')
            ->select(
                DB::raw("'PENDAPATAN :' AS keteranganmain"),
                DB::raw('1 AS ordermain'),
                'AT.kodeType AS type',
                'C.COA AS coa',
                'C.keterangancoa',
                DB::raw('ISNULL(E.nominal, 0) AS Nominal'),
                DB::raw('ISNULL(f.nominal, 0) AS Nominalparent'),
                DB::raw("'$cmpy' AS CmpyName"),
                'C.statuslabarugi',
                DB::raw("'$bulan' AS bulan"),
                DB::raw("'$tahun' AS tahun"),
                'AT.Order',
                'C.Parent',
                DB::raw("ISNULL(G.keterangancoa, '') AS KeteranganParent"),

                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),


            )
            ->join('mainTypeakuntansi AS AT', 'AT.id', '=', 'C.type_id')
            ->leftJoin('mainakunpusat AS G', 'C.parent', '=', 'G.coa')
            ->leftJoin($Temprekappendapatan . ' AS E', 'C.coa', '=', 'E.CoaMAin')
            ->whereIn('AT.kodetype', ['Pendapatan'])
            ->orderBy('coa');


        DB::table($TempLabaRugi)->insertUsing([
            'keteranganmain',
            'ordermain',
            'type',
            'coa',
            'keterangancoa',
            'nominal',
            'cmpyname',
            'statuslabarugi',
            'bln',
            'thn',
            'order',
            'parent',
            'KeteranganParent',
            'diperiksa',
            'disetujui',
        ], $results);
        // dd($results->get()); 

        $results2 = DB::table('mainakunpusat AS C')
            ->select(
                DB::raw("'BIAYA - BIAYA :' AS keteranganmain"),
                DB::raw('2 AS OrderMain'),
                'AT.kodeType AS type',
                'C.COA AS coa',
                'C.keterangancoa',
                DB::raw('ISNULL(E.nominal, 0) AS Nominal'),
                DB::raw("'$cmpy' AS CmpyName"),
                'C.statuslabarugi',
                DB::raw("'$bulan' AS bulan"),
                DB::raw("'$tahun' AS tahun"),
                'AT.Order',
                'C.Parent',
                DB::raw("ISNULL(G.keterangancoa, '') AS KeteranganParent"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            )
            ->join('mainTypeakuntansi AS AT', 'AT.id', '=', 'C.type_id')
            ->leftJoin('mainakunpusat AS G', 'C.parent', '=', 'G.coa')
            ->leftJoin($Temprekappendapatan . ' AS E', 'C.coa', '=', 'E.CoaMAin')
            ->whereIn('AT.kodetype', ['Beban']);

        DB::table($TempLabaRugi)->insertUsing([
            'keteranganmain',
            'ordermain',
            'type',
            'coa',
            'keterangancoa',
            'nominal',
            'cmpyname',
            'statuslabarugi',
            'bln',
            'thn',
            'order',
            'parent',
            'KeteranganParent',
            'diperiksa',
            'disetujui',
        ], $results2);


        $data1 = $results->get();
        $data2 = $results2->get();

        $mergedData = $data1->concat($data2);

        return $mergedData;
    }
}
