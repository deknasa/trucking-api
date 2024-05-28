<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportLaporanKasGantung extends MyModel
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

    public function getExport($sampai, $jenis)
    {
        $bulan = substr($sampai, 0, 2);
        $tahun = substr($sampai, -4);

        $tgl = $tahun . '-' . $bulan . '-02';
        $tgl1 = $tahun . '-' . $bulan . '-02';

        $tgl3 = date('Y-m-d', strtotime($tgl1 . ' +33 days'));



        $tahun2 = date('Y', strtotime($tgl3));
        $bulan2 = date('m', strtotime($tgl3));

        $tanggal = $tahun . '-' . $bulan . '-01';

        $tgl2 = $tahun2 . '-' . $bulan2 . '-1';
        $tgl1 = date('Y-m-d', strtotime($tanggal));
        $tgl1rekap = date('Y-m-d', strtotime($tanggal));
        $tgl2 = date('Y-m-d', strtotime($tgl2 . ' -1 day'));

        $coagantung  = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL KAS GANTUNG')->where('subgrp', 'DEBET')->first();
        $memoKredit = json_decode($coagantung->memo, true);
        $gantungcoa = $memoKredit['JURNAL'];


        $Temppengembalian = '##Temppengembalian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppengembalian, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $Tempkasgantung = '##Tempkasgantung' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempkasgantung, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->string('coa', 1000)->nullable();
        });

        $TempRekap = '##TempRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempRekap, function ($table) {
            $table->dateTime('tgl')->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->dateTime('tglinput')->nullable();
        });


        $querykasgantung = db::table("kasgantungheader")->from(db::raw("kasgantungheader a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                db::raw("sum(b.nominal) as nominal"),
                db::raw("max(b.keterangan) as keterangan"),
                db::raw("'" . $gantungcoa . "' as coa")
            )
            ->join(db::raw("kasgantungdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->groupBy('a.nobukti');



        DB::table($Tempkasgantung)->insertUsing([
            'nobukti',
            'nominal',
            'keterangan',
            'coa',
        ], $querykasgantung);



        while ($tgl1 <= $tgl2) {

            DB::delete(DB::raw("delete " . $Temppengembalian));

            $querytemppengembalian = db::table("pengembaliankasgantungheader")->from(db::raw("pengembaliankasgantungheader a with (readuncommitted)"))
                ->select(
                    'b.kasgantung_nobukti',
                    db::raw("sum(b.nominal) as nominal"),
                )
                ->join(db::raw("pengembaliankasgantungdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->whereRaw("a.tglbukti<'" . $tgl1 . "'")
                ->groupBy('b.kasgantung_nobukti');

            DB::table($Temppengembalian)->insertUsing([
                'nobukti',
                'nominal',
            ], $querytemppengembalian);

            // if ($tgl1 == $tgl2) {

                
            //     $querytemprekap = db::table($Tempkasgantung)->from(db::raw($Tempkasgantung . " a "))
            //         ->select(
            //             db::raw("'" . $tgl1 . "' as tgl"),
            //             'b.tglbukti',
            //             'b.nobukti',
            //             'd.keterangancoa',
            //             'a.keterangan',
            //             db::raw("(isnull(A.nominal,0)-isnull(c.nominal,0)) as debet"),
            //             db::raw("0 as kredit"),
            //             'b.created_at as tglinput'
            //         )
            //         ->join(db::raw("kasgantungheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            //         ->leftjoin(db::raw($Temppengembalian . " c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            //         ->leftjoin(db::raw("akunpusat d with (readuncommitted)"), 'a.coa', 'd.coa')
            //         ->whereRaw("(isnull(A.nominal,0)-isnull(c.nominal,0))<>0")
            //         ->whereRaw("b.tglbukti<'" . $tgl1 . "'");
            // } else {

              

                $querytemprekap = db::table($Tempkasgantung)->from(db::raw($Tempkasgantung . " a "))
                    ->select(
                        db::raw("'" . $tgl1 . "' as tgl"),
                        'b.tglbukti',
                        'b.nobukti',
                        'd.keterangancoa',
                        'a.keterangan',
                        db::raw("(isnull(A.nominal,0)-isnull(c.nominal,0)) as debet"),
                        db::raw("0 as kredit"),
                        'b.created_at as tglinput'
                        )
                    ->join(db::raw("kasgantungheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                    ->leftjoin(db::raw($Temppengembalian . " c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                    ->leftjoin(db::raw("akunpusat d with (readuncommitted)"), 'a.coa', 'd.coa')
                    ->whereRaw("(isnull(A.nominal,0)-isnull(c.nominal,0))<>0")
                    ->whereRaw("b.tglbukti<='" . $tgl1 . "'");
            // }


            DB::table($TempRekap)->insertUsing([
                'tgl',
                'tglbukti',
                'nobukti',
                'coa',
                'keterangan',
                'debet',
                'kredit',
                'tglinput',
            ], $querytemprekap);

            $querytemprekap = db::table("pengembaliankasgantungdetail")->from(db::raw("pengembaliankasgantungdetail a with (readuncommitted)"))
                ->select(
                    db::raw("'" . $tgl1 . "' as tgl"),
                    'b.tglbukti',
                    'a.kasgantung_nobukti as nobukti',
                    'd.keterangancoa',
                    'a.keterangan',
                    db::raw("0 as debet"),
                    db::raw("a.nominal as kredit"),
                    'b.created_at as tglinput'

                )
                ->join(db::raw("pengembaliankasgantungheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->leftjoin(db::raw("akunpusat d with (readuncommitted)"), db::raw("'" . $gantungcoa . "'"), 'd.coa')
                ->whereRaw("b.tglbukti='" . $tgl1 . "'");


            DB::table($TempRekap)->insertUsing([
                'tgl',
                'tglbukti',
                'nobukti',
                'coa',
                'keterangan',
                'debet',
                'kredit',
                'tglinput',
            ], $querytemprekap);

            $tgl1 = date('Y-m-d', strtotime($tgl1 . ' +1 day'));
        }


        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();




        // dd(db::table($TempRekap)->get());

        $getData = db::table($TempRekap)->from(db::raw($TempRekap . " a "))
            ->select(
                db::raw("format(a.tglbukti,'dd-MM-yyyy') as tglbukti"),
                // 'a.tglbukti',
                'a.nobukti',
                'a.coa as perkiraan',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                db::raw("0 as saldo"),
                db::raw("format(a.tgl,'dd-MM-yyyy') as tgl"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'LAPORAN HARIAN' as jenislaporan"),
            )
            ->orderBy('a.tgl', 'asc')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('a.tglinput', 'asc')
            ->orderBy('a.nobukti', 'asc')
            ->get();

        // rekap kas gantung
        $Temppengembalianrekap = '##Temppengembalianrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppengembalianrekap, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $Tempkasgantungrekap = '##Tempkasgantungrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempkasgantungrekap, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->string('coa', 1000)->nullable();
        });




        $querykasgantungrekap = db::table("kasgantungheader")->from(db::raw("kasgantungheader a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                db::raw("sum(b.nominal) as nominal"),
                db::raw("max(b.keterangan) as keterangan"),
                db::raw("'" . $gantungcoa . "' as coa")
            )
            ->join(db::raw("kasgantungdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            // ->where('a.tglbukti',$tgl2)
            ->whereRaw("a.tglbukti<='" . $tgl2 . "'")
            ->groupBy('a.nobukti');



        DB::table($Tempkasgantungrekap)->insertUsing([
            'nobukti',
            'nominal',
            'keterangan',
            'coa',
        ], $querykasgantungrekap);

        $querytemppengembalianrekap = db::table("pengembaliankasgantungheader")->from(db::raw("pengembaliankasgantungheader a with (readuncommitted)"))
            ->select(
                'b.kasgantung_nobukti',
                db::raw("sum(b.nominal) as nominal"),
            )
            ->join(db::raw("pengembaliankasgantungdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti<'" . $tgl1rekap . "'")
            ->groupBy('b.kasgantung_nobukti');

        DB::table($Temppengembalianrekap)->insertUsing([
            'nobukti',
            'nominal',
        ], $querytemppengembalianrekap);


        $TempRekapkasgantung = '##TempRekapkasgantung' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempRekapkasgantung, function ($table) {
            $table->Integer('jenis')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->date('tglbukti2')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('nobukti2', 50)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('nominalbayar', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
        });

        $queryTempRekapkasgantung = db::table($Tempkasgantungrekap)->from(db::raw($Tempkasgantungrekap . " a"))
            ->select(
                db::raw("1 as jenis"),
                'c.tglbukti',
                'c.tglbukti as tglbukti2',
                'a.nobukti',
                'a.nobukti as nobukti2',
                'a.keterangan',
                'a.nominal',
                db::raw("0 as nominalbayar"),
                db::raw("0 as nominalsisa"),
            )
            ->leftjoin(db::raw($Temppengembalianrekap . " b "), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("kasgantungheader c with (readuncommitted) "), 'a.nobukti', 'c.nobukti')
            ->whereRaw("(isnull(a.nominal,0)-isnull(B.nominal,0))<>0")
            ->orderBy('c.tglbukti', 'asc');

        DB::table($TempRekapkasgantung)->insertUsing([
            'jenis',
            'tglbukti',
            'tglbukti2',
            'nobukti',
            'nobukti2',
            'keterangan',
            'nominal',
            'nominalbayar',
            'sisa',
        ], $queryTempRekapkasgantung);

        $queryTempRekapkasgantung = db::table($Tempkasgantungrekap)->from(db::raw($Tempkasgantungrekap . " a"))
            ->select(
                db::raw("3 as jenis"),
                'c.tglbukti',
                'c.tglbukti as tglbukti2',
                'a.nobukti',
                'a.nobukti as nobukti2',
                db::raw("'SISA PELUNASAN' as keterangan"),
                db::raw("0 as nominal"),
                db::raw("0 as nominalbayar"),
                db::raw("0 as nominalsisa"),
            )
            ->leftjoin(db::raw($Temppengembalianrekap . " b "), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("kasgantungheader c with (readuncommitted) "), 'a.nobukti', 'c.nobukti')
            ->whereRaw("(isnull(a.nominal,0)-isnull(B.nominal,0))<>0")
            ->orderBy('c.tglbukti', 'asc');

        DB::table($TempRekapkasgantung)->insertUsing([
            'jenis',
            'tglbukti',
            'tglbukti2',
            'nobukti',
            'nobukti2',
            'keterangan',
            'nominal',
            'nominalbayar',
            'sisa',
        ], $queryTempRekapkasgantung);

        $tgl1a = $tahun . '-' . $bulan . '-01';
        // dd($tgl1a );
        $queryTempRekapkasgantung = db::table($Tempkasgantungrekap)->from(db::raw($Tempkasgantungrekap . " a"))
            ->select(

                db::raw("2 as jenis"),
                'd.tglbukti',
                'c.tglbukti as tglbukti2',
                'a.nobukti',
                'c.penerimaan_nobukti  as nobukti2',
                'b.keterangan',
                db::raw("0 as nominal"),
                'b.nominal as nominalbayar',
                db::raw("0 as nominalsisa"),
            )

            ->join(db::raw("pengembaliankasgantungdetail b with (readuncommitted) "), 'a.nobukti', 'b.kasgantung_nobukti')
            ->join(db::raw("pengembaliankasgantungheader c with (readuncommitted) "), 'b.nobukti', 'c.nobukti')
            ->join(db::raw("kasgantungheader d with (readuncommitted) "), 'a.nobukti', 'd.nobukti')
            // ->whereRaw("c.tglbukti<='" . $tgl2 . "'")
            ->whereRaw("(c.tglbukti>='" . $tgl1a . "' and c.tglbukti<='" . $tgl2 . "')")

            ->orderBy('c.tglbukti', 'asc');

            // dd($queryTempRekapkasgantung->get());

        DB::table($TempRekapkasgantung)->insertUsing([
            'jenis',
            'tglbukti',
            'tglbukti2',
            'nobukti',
            'nobukti2',
            'keterangan',
            'nominal',
            'nominalbayar',
            'sisa',
        ], $queryTempRekapkasgantung);



        $Temphasil = '##Temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphasil, function ($table) {
            $table->id();
            $table->Integer('jenis')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->date('tglbukti2')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('nobukti2', 50)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('nominalbayar', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
        });


        $queryhasil = db::table($TempRekapkasgantung)->from(db::raw($TempRekapkasgantung . " a"))
            ->select(
                'a.jenis',
                'a.tglbukti',
                'a.tglbukti2',
                'a.nobukti',
                'a.nobukti2',
                'a.keterangan',
                'a.nominal',
                'a.nominalbayar',
                db::raw("sum ((nominal-nominalbayar)) over (partition by nobukti order by tglbukti,nobukti,jenis,tglbukti2 asc) as sisa"),
            )

            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('a.nobukti', 'asc')
            ->orderBy('a.jenis', 'asc')
            ->orderBy('a.tglbukti2', 'asc');

        DB::table($Temphasil)->insertUsing([
            'jenis',
            'tglbukti',
            'tglbukti2',
            'nobukti',
            'nobukti2',
            'keterangan',
            'nominal',
            'nominalbayar',
            'sisa',
        ], $queryhasil);



        $getdata2 = db::table($Temphasil)->from(db::raw($Temphasil . " a"))
            ->select(
                'a.jenis',
                db::raw("format(a.tglbukti2,'dd-MM-yyyy') as tglbukti"),
                'a.nobukti2 as nobukti',
                'a.keterangan',
                'a.nominal',
                'a.nominalbayar',
                db::raw("(case when a.jenis<>3 then 0 else a.sisa end) as sisa"),
                // 'a.tglbukti as tglbukti3',
                'a.nobukti as nobukti3',


            )
            ->orderby('a.id', 'asc')
            ->get();


                // dd($getData);

        return [$getData, $getdata2];
    }


    public function getExportlalu($sampai, $jenis)
    {


        $bulan = substr($sampai, 0, 2);
        $tahun = substr($sampai, -4);

        $tgl = $tahun . '-' . $bulan . '-02';
        $tgl1 = $tahun . '-' . $bulan . '-02';

        $tgl3 = date('Y-m-d', strtotime($tgl1 . ' +33 days'));



        $tahun2 = date('Y', strtotime($tgl3));
        $bulan2 = date('m', strtotime($tgl3));

        $tanggal = $tahun . '-' . $bulan . '-01';

        $tgl2 = $tahun2 . '-' . $bulan2 . '-1';
        $tgl2 = date('Y-m-d', strtotime($tgl2 . ' -1 day'));


        $querySaldoAwal = DB::table("saldoawalbank")->from(
            DB::raw("saldoawalbank")
        )
            ->select(
                DB::Raw('isnull(sum(isnull(nominaldebet,0)-isnull(nominalkredit,0)),0) as saldoawal'),
            )
            ->whereRaw("right(bulan,4)+left(bulan,2)<right($tahun,4)+left($bulan,2)")
            ->where('bank_id', $jenis)->first();


        $saldoAwal = $querySaldoAwal->saldoawal;


        $tempList = '##tempList' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempList, function ($table) {
            $table->integer('jenis')->nullable();
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });



        $tempList2 = '##tempList2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempList2, function ($table) {
            $table->integer('jenis')->nullable();
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        DB::table($tempList)->insert([
            'jenis' => 1,
            'tgl' => date('Y-m-d', strtotime($tanggal)),
            'nobukti' => '',
            'coa' => '',
            'perkiraan' => '',
            'keterangan' => 'SALDO AWAL',
            'debet' => 0,
            'kredit' => 0,
            'saldo' => $saldoAwal
        ]);


        while ($tgl1 <= $tgl2) {
            DB::table($tempList)->insert([
                'jenis' => 1,
                'tgl' => date('Y-m-d', strtotime($tgl1)),
                'nobukti' => '',
                'coa' => '',
                'perkiraan' => '',
                'keterangan' => 'SALDO AWAL',
                'debet' => 0,
                'kredit' => 0,
                'saldo' => 0
            ]);

            $tgl1 = date('Y-m-d', strtotime($tgl1 . ' +1 day'));
        }

        $queryTempList = DB::table('pengembaliankasgantungdetail')->from(
            DB::raw('pengembaliankasgantungdetail as a')
        )
            ->select(
                'a.coa as coa',
                DB::raw("2 as jenis"),
                'b.tglbukti as tgljatuhtempo',
                'a.nobukti',
                DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                'a.keterangan',
                'a.nominal as debet',
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo"),

            )
            ->join(DB::raw("pengembaliankasgantungheader as b "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("akunpusat as c "), 'a.coa', 'c.coa')
            ->whereRaw("month(b.tglbukti)= cast(left($bulan,2) as integer)")
            ->whereRaw("year(b.tglbukti)= cast(right($tahun,4) as integer)")
            ->where('b.bank_id', '=', $jenis);

        DB::table($tempList)->insertUsing([
            'coa',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo'
        ], $queryTempList);



        $queryTempPengeluaran = DB::table('kasgantungdetail')->from(
            DB::raw('kasgantungdetail as a')
        )
            ->select(
                'a.coa as coa',
                DB::raw("4 as jenis"),
                'b.tglbukti as tgljatuhtempo',
                'a.nobukti',
                DB::raw("isnull(C.keterangancoa,'') as perkiraan"),
                'a.keterangan',
                DB::raw("0 as debet"),
                DB::raw("a.nominal as kredit"),
                DB::raw("0 as saldo"),
            )
            ->join(DB::raw("kasgantungheader as b "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("akunpusat as c "), 'a.coa', 'c.coa')
            ->whereRaw("month(b.tglbukti)= cast(left($bulan,2) as integer)")
            ->whereRaw("year(b.tglbukti)= cast(right($tahun,4) as integer)")
            ->where('b.bank_id', '=', $jenis);

        DB::table($tempList)->insertUsing([
            'coa',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo'
        ], $queryTempPengeluaran);

        $queryTempList2 = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                'jenis',
                'coa',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
            );


        DB::table($tempList2)->insertUsing([
            'jenis',
            'coa',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $queryTempList2);

        DB::table($tempList2)
            ->where("keterangan", "=", "SALDO AWAL")
            ->where('tgl', '>=', $tgl)
            ->delete();


        $tempListRekap = '##tempListRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempListRekap, function ($table) {
            $table->bigIncrements('id');
            $table->string('jenislaporan', 100);
            $table->integer('jenis');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $queryTempListRekap = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                DB::raw("'LAPORAN KAS GANTUNG' AS  jenislaporan"),
                'jenis',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo'

            )
            ->orderBy('tgl', 'ASC')
            ->orderBy('jenis', 'ASC')
            ->orderBy('nobukti', 'ASC');

        DB::table($tempListRekap)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $queryTempListRekap);

        $tempLaporan = '##tempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempLaporan, function ($table) {
            $table->string('jenislaporan', 100);
            $table->integer('jenis');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
            $table->integer('id')->nullable();
        });

        $queryTempLaporan = DB::table($tempListRekap)->from(
            DB::raw($tempListRekap . ' as a')
        )
            ->select(
                DB::raw("'LAPORAN KAS GANTUNG' AS  jenislaporan"),
                'a.jenis',
                'a.tgl as tglbukti',
                'a.nobukti',
                'a.perkiraan',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(A.saldo,0)+A.debet)-A.Kredit) over (order by id asc) as Saldo"),
                'a.id'

            )
            ->where('a.jenislaporan', 'LAPORAN KAS GANTUNG')
            ->orderBy('a.id', 'ASC');


        DB::table($tempLaporan)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'id'
        ], $queryTempLaporan);

        DB::table($tempList)
            ->where("keterangan", "=", "SALDO AWAL")
            ->where('tgl', '>=', $tgl)
            ->delete();

        $tempRekap = '##tempRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempRekap, function ($table) {
            $table->bigIncrements('id');
            $table->string('jenislaporan', 100);
            $table->integer('jenis');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $queryTempRekap = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                DB::raw("'LAPORAN REKAP' AS  jenislaporan"),
                'jenis',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo'

            )
            ->orderBy('tgl', 'ASC')
            ->orderBy('jenis', 'ASC')
            ->orderBy('nobukti', 'ASC');

        DB::table($tempRekap)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $queryTempRekap);

        $queryTempLaporanRekap = DB::table($tempRekap)->from(
            DB::raw($tempRekap . ' as a')
        )
            ->select(
                DB::raw("'LAPORAN REKAP' AS  jenislaporan"),
                'a.jenis',
                'a.tgl as tglbukti',
                'a.nobukti',
                'a.perkiraan',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(A.saldo,0)+A.debet)-A.Kredit) over (order by id asc) as Saldo"),
                'a.id'

            )
            ->where('a.jenislaporan', 'LAPORAN REKAP')
            ->orderBy('a.id', 'ASC');

        DB::table($tempLaporan)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'id'
        ], $queryTempLaporanRekap);

        $querySaloAwalRekap01 = DB::table($tempList)->from(
            DB::raw($tempList . ' as a')
        )
            ->select(
                DB::raw("SUM(isnull(saldo,0)+isnull(debet,0)) as saldoawalrekap01"),
            )
            ->where('jenis', '<=', 3)
            ->first();

        $saldoAwalRekap01 = $querySaloAwalRekap01->saldoawalrekap01;

        DB::table($tempList)
            ->where("jenis", "<=", 3)
            ->delete();

        DB::table($tempList)->insert([
            'jenis' => 1,
            'tgl' => date('Y-m-d', strtotime($tanggal)),
            'nobukti' => '',
            'perkiraan' => '',
            'keterangan' => 'SALDO AWAL',
            'debet' => 0,
            'kredit' => 0,
            'saldo' => $saldoAwalRekap01
        ]);

        // dd(DB::table($tempList)->select("*")->get());

        $tempRekap01 = '##tempRekap01' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempRekap01, function ($table) {
            $table->bigIncrements('id');
            $table->string('jenislaporan', 100);
            $table->integer('jenis');
            $table->dateTime('tgl')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('perkiraan', 1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('debet', 15, 2)->nullable();
            $table->double('kredit', 15, 2)->nullable();
            $table->double('saldo', 15, 2)->nullable();
        });

        $queryLaporanRekap01 = DB::table($tempList)->from(
            DB::raw($tempList)
        )
            ->select(
                DB::raw("'LAPORAN REKAP 01' AS  jenislaporan"),
                'jenis',
                'tgl',
                'nobukti',
                'perkiraan',
                'keterangan',
                'debet',
                'kredit',
                'saldo'
            )
            ->orderBy('tgl', 'ASC')
            ->orderBy('jenis', 'ASC')
            ->orderBy('nobukti', 'ASC');

        DB::table($tempRekap01)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $queryLaporanRekap01);


        $queryLaporanRekap01Dua = DB::table($tempRekap01)->from(
            DB::raw($tempRekap01 . " as a")
        )
            ->select(
                DB::raw("'LAPORAN REKAP 01' AS  jenislaporan"),
                'a.jenis',
                'a.tgl as tglbukti',
                'a.nobukti',
                'a.perkiraan',
                'a.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(A.saldo,0)+A.debet)-A.Kredit) over (order by id asc) as Saldo"),
                'a.id'
            )
            ->where('a.jenislaporan', '=', 'LAPORAN REKAP 01')
            ->orderBy('a.id', 'ASC');


        DB::table($tempLaporan)->insertUsing([
            'jenislaporan',
            'jenis',
            'tgl',
            'nobukti',
            'perkiraan',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
            'id'
        ], $queryLaporanRekap01Dua);

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        // disini

        // dd(db::table($tempLaporan)->get());
        $getData = DB::table($tempLaporan)->from(db::raw($tempLaporan . " a"))->select(
            db::raw("format(a.tgl,'dd-MM-yyyy') as tgl"),
            'a.nobukti',
            'a.perkiraan',
            'a.keterangan',
            'a.debet',
            'a.kredit',
            'a.saldo',
            'a.id',
            'a.jenislaporan',
            DB::raw("'" . $getJudul->text . "' as judul")
        )
            ->orderBy('a.id', 'asc')
            ->get();




        // dd($getData);



        return $getData;
    }
    // 


    public function getExportOld($periode)
    {
        $pengembaliankasgantungheader = '##pengembaliankasgantungheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengembaliankasgantungheader, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->bigInteger('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('coakasmasuk', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->bigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });


        $dataheader = DB::table('pengembaliankasgantungheader')->from(DB::raw("pengembaliankasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.nobukti',
                'A.tglbukti',
                'A.pelanggan_id',
                'A.keterangan',
                'A.bank_id',
                'A.tgldari',
                'A.tglsampai',
                'A.penerimaan_nobukti',
                'A.coakasmasuk',
                'A.postingdari',
                'A.tglkasmasuk',
                'A.statusformat',
                'A.statuscetak',
                'A.userbukacetak',
                'A.tglbukacetak',
                'A.jumlahcetak',
                'A.modifiedby',
                'A.created_at',
                'A.updated_at'
            ])
            ->where('A.tglbukti', '<', $periode);


        DB::table($pengembaliankasgantungheader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pelanggan_id',
            'keterangan',
            'bank_id',
            'tgldari',
            'tglsampai',
            'penerimaan_nobukti',
            'coakasmasuk',
            'postingdari',
            'tglkasmasuk',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $dataheader);

        //   dd($dataheader->get());

        //NOTE - pengembalian kas gantung detail
        $pengembaliankasgantungdetail = '##pengembaliankasgantungdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengembaliankasgantungdetail, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->bigInteger('pengembaliankasgantung_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->float('nominal')->nullable();
            $table->string('coa')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('kasgantung_nobukti', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $datadetail = DB::table('pengembaliankasgantungdetail')->from(DB::raw("pengembaliankasgantungdetail AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.pengembaliankasgantung_id',
                'A.nobukti',
                'A.nominal',
                'A.coa',
                'A.keterangan',
                'A.modifiedby',
                'A.kasgantung_nobukti',
                'A.created_at',
                'A.updated_at'
            ])
            ->join(DB::raw("pengembaliankasgantungheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti');


        DB::table($pengembaliankasgantungdetail)->insertUsing([
            'id',
            'pengembaliankasgantung_id',
            'nobukti',
            'nominal',
            'coa',
            'keterangan',
            'modifiedby',
            'kasgantung_nobukti',
            'created_at',
            'updated_at',
        ], $datadetail);
        // dd($datadetail->get());

        //NOTE - kas gantung header
        $kasgantungheader = '##kasgantungheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($kasgantungheader, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('penerima_id')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->string('coakaskeluar', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkaskeluar')->nullable();
            $table->bigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $kasheader = DB::table('kasgantungheader')->from(DB::raw("kasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.nobukti',
                'A.tglbukti',
                'A.keterangan',
                'A.penerima_id',
                'A.bank_id',
                'A.pengeluaran_nobukti',
                'A.coakaskeluar',
                'A.postingdari',
                'A.tglkaskeluar',
                'A.statusformat',
                'A.statuscetak',
                'A.userbukacetak',
                'A.tglbukacetak',
                'A.jumlahcetak',
                'A.modifiedby',
                'A.created_at',
                'A.updated_at'
            ])
            ->where('A.tglbukti', '<', $periode);
        //  dd($kasheader->get());

        DB::table($kasgantungheader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'keterangan',
            'penerima_id',
            'bank_id',
            'pengeluaran_nobukti',
            'coakaskeluar',
            'postingdari',
            'tglkaskeluar',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $kasheader);
        // dd($kasheader->get());


        //NOTE - kasgantungdetail
        $kasgantungdetail = '##kasgantungdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($kasgantungdetail, function ($table) {
            $table->string('nobukti', 50);
            $table->longText('keterangan');
            $table->float('nominal');
        });

        $kasdetail = DB::table('kasgantungdetail')->from(DB::raw("kasgantungdetail AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.nobukti',
                DB::raw('MAX(A.keterangan)'),
                DB::raw('SUM(A.nominal) as nominal')
            ])
            ->join(DB::raw("kasgantungheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->groupBy('A.nobukti');

        DB::table($kasgantungdetail)->insertUsing([
            'nobukti',
            'keterangan',
            'nominal',
        ], $kasdetail);
        //  dd('$kasdetail->get()');


        //NOTE - pengembaliankasgantungheader2
        $pengembaliankasgantungheader2 = '##pengembaliankasgantungheader2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengembaliankasgantungheader2, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->bigInteger('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('coakasmasuk', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->bigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $dataheader2 = DB::table('pengembaliankasgantungheader')->from(DB::raw("pengembaliankasgantungheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.id',
                'A.nobukti',
                'A.tglbukti',
                'A.pelanggan_id',
                'A.keterangan',
                'A.bank_id',
                'A.tgldari',
                'A.tglsampai',
                'A.penerimaan_nobukti',
                'A.coakasmasuk',
                'A.postingdari',
                'A.tglkasmasuk',
                'A.statusformat',
                'A.statuscetak',
                'A.userbukacetak',
                'A.tglbukacetak',
                'A.jumlahcetak',
                'A.modifiedby',
                'A.created_at',
                'A.updated_at',
            ])
            ->where('A.tglbukti', '<', $periode);


        DB::table($pengembaliankasgantungheader2)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pelanggan_id',
            'keterangan',
            'bank_id',
            'tgldari',
            'tglsampai',
            'penerimaan_nobukti',
            'coakasmasuk',
            'postingdari',
            'tglkasmasuk',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $dataheader2);




        //NOTE - pengembaliankasgantungdetail2
        $pengembaliankasgantungdetail2 = '##pengembaliankasgantungdetail2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengembaliankasgantungdetail2, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('kasgantung_nobukti', 50)->nullable();
            $table->float('nominal')->nullable();
            $table->longText('keterangan')->nullable();
        });

        $kasdetail2 = DB::table('pengembaliankasgantungdetail')->from(DB::raw("pengembaliankasgantungdetail AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.nobukti',
                'A.kasgantung_nobukti',
                DB::raw('SUM(A.nominal) as nominal'),
                DB::raw('MAX(A.keterangan)'),
            ])
            ->join(DB::raw($pengembaliankasgantungheader2 . " as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->groupBy('A.nobukti', 'A.kasgantung_nobukti');


        DB::table($pengembaliankasgantungdetail2)->insertUsing([
            'nobukti',
            'kasgantung_nobukti',
            'nominal',
            'keterangan',
        ], $kasdetail2);
        // dd($kasdetail2->get());



        //NOTE - TempLaporan
        $TempLaporan = '##TempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempLaporan, function ($table) {
            $table->bigIncrements('id');
            $table->dateTime('tglbuktikasgantung');
            $table->dateTime('tglbukti');
            $table->string('nobukti', 50);
            $table->longText('keterangan');
            $table->integer('flag');
            $table->float('debet');
            $table->float('kredit');
            $table->float('saldo')->nullable();
        });

        $temp_kasgantungheader = DB::table('kasgantungheader')->from(DB::raw($kasgantungheader . " AS a"))
            ->select([
                'A.tglbukti',
                'A.tglbukti',
                'C.nobukti',
                'C.keterangan',
                DB::raw('0 as flag'),
                'C.nominal as debet',
                DB::raw('0 as kredit'),
            ])
            ->leftJoin(DB::raw($pengembaliankasgantungdetail . " AS b"), function ($join) {
                $join->on('a.nobukti', '=', 'b.kasgantung_nobukti')
                    ->whereNull('b.nobukti');
            })
            ->join(DB::raw($kasgantungdetail . " AS c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('c.nobukti', 'desc');

        DB::table($TempLaporan)->insertUsing([
            'tglbuktikasgantung',
            'tglbukti',
            'nobukti',
            'keterangan',
            'flag',
            'debet',
            'kredit'
        ], $temp_kasgantungheader);
        // dd($temp_kasgantungheader->get());






        //NOTE - TempLaporan
        $temp_pengembaliankasgantungheader2 = DB::table('pengembaliankasgantungheader2')->from(DB::raw($pengembaliankasgantungheader2 . " AS a"))
            ->select([
                'B.tglbukti',
                'A.tglbukti',
                'C.kasgantung_nobukti as nobukti',
                'C.keterangan',
                DB::raw('1 as flag'),
                DB::raw('0 as debet'),
                'c.nominal as kredit',
            ])
            ->join(DB::raw($pengembaliankasgantungdetail2 . " c with (readuncommitted)"), 'a.nobukti', '=', 'c.nobukti')
            ->join('kasgantungheader as b', 'c.kasgantung_nobukti', 'b.nobukti')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('c.kasgantung_nobukti', 'desc');

        DB::table($TempLaporan)->insertUsing([
            'tglbuktikasgantung',
            'tglbukti',
            'nobukti',
            'keterangan',
            'flag',
            'debet',
            'kredit'
        ], $temp_pengembaliankasgantungheader2);
        // dd($temp_pengembaliankasgantungheader2->get());

        //NOTE - TempLaporan2
        $TempLaporan2 = '##TempLaporan2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempLaporan2, function ($table) {
            $table->bigIncrements('id');
            $table->dateTime('tglbukti');
            $table->string('nobukti', 50);
            $table->longText('keterangan');
            $table->float('debet');
            $table->float('kredit');
            $table->float('saldo')->nullable();
        });

        $select_TempLaporan = DB::table('TempLaporan')->from(DB::raw($TempLaporan . " AS a"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.keterangan',
                'A.debet',
                'A.kredit',
                DB::raw('0 as saldo'),

            ])
            ->orderBy('a.tglbuktikasgantung', 'asc')
            ->orderBy('a.nobukti', 'asc')
            ->orderBy('a.flag', 'desc');

        DB::table($TempLaporan2)->insertUsing([
            'tglbukti',
            'nobukti',
            'keterangan',
            'debet',
            'kredit',
            'saldo'
        ], $select_TempLaporan);


        $select_TempLaporan2 = DB::table('TempLaporan2')->from(DB::raw($TempLaporan2 . " AS a"))
            ->select([
                'A.tglbukti as tanggal',
                'A.nobukti',
                'A.keterangan',
                'A.debet',
                'A.kredit',
                DB::raw('sum((isnull(A.saldo, 0) + A.debet) - A.kredit) over (order by id asc) as Saldo'),

            ])
            ->orderBy('a.id', 'asc');
        // dd($select_TempLaporan2->get());

        $data = $select_TempLaporan2->get();
        return $data;
    }
}
