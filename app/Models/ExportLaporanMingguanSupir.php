<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportLaporanMingguanSupir extends Model
{
    use HasFactory;

    public function getExport($dari, $sampai, $tradodari, $tradosampai)
    {

        if ($tradodari == 0) {
            $tradodari = db::table('trado')->from(db::raw("trado with (readuncommitted)"))
                ->select('id')->orderby('id', 'asc')->first()->id ?? 0;
        }

        if ($tradosampai == 0) {
            $tradosampai = db::table('trado')->from(db::raw("trado with (readuncommitted)"))
                ->select('id')->orderby('id', 'desc')->first()->id ?? 0;
        }

        if ($tradodari > $tradosampai) {
            $tradodari1 = $tradosampai;
            $tradosampai1 = $tradodari;
            $tradodari = $tradodari1;
            $tradosampai = $tradosampai1;
        }


        $full = StatusContainer::where('kodestatuscontainer', '=', 'FULL')->first();
        $fullId = $full->id;

        $empty = StatusContainer::where('kodestatuscontainer', '=', 'EMPTY')->first();
        $emptyId = $empty->id;

        $fullEmpty = StatusContainer::where('kodestatuscontainer', '=', 'FULL EMPTY')->first();
        $fullEmptyId = $fullEmpty->id;

        $dari = date("Y-m-d", strtotime($dari));
        $sampai = date("Y-m-d", strtotime($sampai));

        $tempData = '##tempData' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempData, function ($table) {
            $table->BigIncrements('id');
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nopol', 1000)->nullable();
            $table->string('namasupir', 1000)->nullable();
            $table->string('rute', 1000)->nullable();
            $table->string('qty', 1000)->nullable();
            $table->string('lokasimuat', 1000)->nullable();
            $table->string('nocontseal', 1000)->nullable();
            $table->string('emkl', 1000)->nullable();
            $table->string('spfull', 500)->nullable();
            $table->string('spempty', 500)->nullable();
            $table->string('spfullempty', 500)->nullable();
            $table->string('jobtrucking', 500)->nullable();
            $table->double('gajisupir', 15, 2)->nullable();
            $table->string('nobuktiebs', 100)->nullable();
            $table->string('nobuktiric', 50)->nullable();
            $table->string('pengeluarannobuktiebs', 500)->nullable();
            $table->double('komisisupir', 15, 2)->nullable();
            $table->double('gajikenek', 15, 2)->nullable();
            $table->double('voucher', 15, 2)->nullable();
            $table->string('novoucher', 500)->nullable();
            $table->double('gajiritasi', 15, 2)->nullable();
            $table->string('ketritasi', 500)->nullable();
            $table->integer('urutric')->nullable();
            $table->double('omset', 15, 2)->nullable();
            $table->double('biayatambahan', 15, 2)->nullable();
            $table->longtext('keteranganbiayatambahan')->nullable();
            
        });






        $queryTempdata = DB::table("gajisupirheader")->from(
            DB::raw("gajisupirheader as a with (readuncommitted)")
        )

            ->select(
                'c.nobukti',
                'c.tglbukti',
                'd.kodetrado as nopol',
                'e.namasupir',
                DB::raw("ltrim(rtrim(isnull(f.kodekota ,'')))+'-'+ltrim(rtrim(isnull(g.kodekota,''))) +
                (case when isnull(c.penyesuaian,'')<>'' then ' ( '+isnull(c.penyesuaian,'')+' )' else '' end)
                as rute"),
                DB::raw("isnull(h.kodecontainer,'') as qty"),
                DB::raw("isnull(i.namapelanggan,'') as lokasimuat"),
                DB::raw("ltrim(rtrim(isnull(c.nocont,'')))+' / '+ltrim(rtrim(isnull(c.noseal,''))) as nocontseal"),
                DB::raw("isnull(j.namaagen,'') as emkl"),
                DB::raw("(case when c.statuscontainer_id =$fullId then c.nosp else '' end) as spfull"),
                DB::raw("(case when c.statuscontainer_id =$emptyId then c.nosp else '' end) as spempty"),
                DB::raw("(case when c.statuscontainer_id =$fullEmptyId then c.nosp else '' end) as spfullempty"),
                DB::raw("isnull(C.jobtrucking,'') as jobtrucking,isnull(c.gajisupir,0) as gajisupir"),
                DB::raw("isnull(k.nobukti,'') as nobuktiebs"),
                DB::raw("isnull(A.nobukti,'') as nobuktiric"),
                DB::raw("isnull(l.pengeluaran_nobukti,'') as pengeluarannobuktiebs"),
                DB::raw("isnull(b.komisisupir,0) as komisisupir"),
                DB::raw("isnull(b.gajikenek,0) as gajikenek"),
                DB::raw("isnull(b.voucher,0) as voucher"),
                DB::raw("isnull(b.novoucher,'') as novoucher"),
                DB::raw("isnull(b.gajiritasi,0) as gajiritasi"),
                DB::raw("isnull(o.[text],'') as ketritasi"),
                db::raw("row_number() Over( partition by a.nobukti Order By c.nobukti,c.tglbukti) as urutric"),
                DB::raw("isnull(c.omset,0) as omset"),
                DB::raw("isnull(b.biayatambahan,0) as biayatambahan"),
                DB::raw("isnull(b.keteranganbiayatambahan,'') as keteranganbiayatambahan"),

            )
            ->join(DB::raw("gajisupirdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("suratpengantar as c with (readuncommitted) "), 'b.suratpengantar_nobukti', 'c.nobukti')
            ->leftjoin(DB::raw("trado as d with (readuncommitted) "), 'c.trado_id', 'd.id')
            ->leftjoin(DB::raw("supir as e with (readuncommitted) "), 'c.supir_id', 'e.id')
            ->leftjoin(DB::raw("kota as f with (readuncommitted) "), 'c.dari_id', 'f.id')
            ->leftjoin(DB::raw("kota as g with (readuncommitted) "), 'c.sampai_id', 'g.id')
            ->leftjoin(DB::raw("container as h with (readuncommitted) "), 'c.container_id', 'h.id')
            ->leftjoin(DB::raw("pelanggan as i with (readuncommitted) "), 'c.pelanggan_id', 'i.id')
            ->leftjoin(DB::raw("agen as j with (readuncommitted) "), 'c.agen_id', 'j.id')
            ->leftjoin(DB::raw("prosesgajisupirdetail as k with (readuncommitted) "), 'a.nobukti', 'k.gajisupir_nobukti')
            ->leftjoin(DB::raw("prosesgajisupirheader as l with (readuncommitted) "), 'k.nobukti', 'l.nobukti')
            ->leftjoin(DB::raw("ritasi as m with (readuncommitted) "), 'b.ritasi_nobukti', 'm.nobukti')
            ->leftjoin(DB::raw("dataritasi as n with (readuncommitted) "), 'm.dataritasi_id', 'n.id')
            ->leftjoin(DB::raw("parameter as o with (readuncommitted) "), 'n.statusritasi', 'o.id')
            ->whereRaw("(c.tglbukti >= '$dari' and c.tglbukti <= '$sampai')")
            ->whereraw("(c.trado_id>=$tradodari")
            ->whereraw("c.trado_id<=$tradosampai)")
            ->orderBy("d.kodetrado", "asc")
            ->orderBy("e.namasupir", "asc")
            ->orderBy("c.tglbukti", "asc")
            ->orderBy("c.nobukti", "asc");

        DB::table($tempData)->insertUsing([
            'nobukti',
            'tglbukti',
            'nopol',
            'namasupir',
            'rute',
            'qty',
            'lokasimuat',
            'nocontseal',
            'emkl',
            'spfull',
            'spempty',
            'spfullempty',
            'jobtrucking',
            'gajisupir',
            'nobuktiebs',
            'nobuktiric',
            'pengeluarannobuktiebs',
            'komisisupir',
            'gajikenek',
            'voucher',
            'novoucher',
            'gajiritasi',
            'ketritasi',
            'urutric',
            'omset',
            'biayatambahan',
            'keteranganbiayatambahan'
        ], $queryTempdata);

        $tempuangjalan = '##tempdatauangjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempuangjalan, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('nominaluangjalan', 15, 2)->nullable();
            $table->double('nominaluangbbm', 15, 2)->nullable();
            $table->double('nominaluangmakan', 15, 2)->nullable();
        });

        $querytempuangjalan = DB::table("gajisupirheader")->from(
            DB::raw("gajisupirheader as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                db::raw("sum(a.uangjalan) as nominaluangjalan"),
                db::raw("sum(a.bbm) as nominaluangbbm"),
                db::raw("sum(a.uangmakanharian + isnull(a.biayaextra,0)) as nominaluangmakan"),
            )
            // ->join(DB::raw($tempData . " as c "), 'a.nobukti', 'c.nobuktiric')
            ->GroupBy('a.nobukti');

        DB::table($tempuangjalan)->insertUsing([
            'nobukti',
            'nominaluangjalan',
            'nominaluangbbm',
            'nominaluangmakan',
        ], $querytempuangjalan);

        $temptrip = '##tempdatatrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptrip, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('omsettambahan', 15, 2)->nullable();
            $table->double('liter', 15, 2)->nullable();
        });

        $querytemptrip = DB::table("suratpengantar")->from(
            DB::raw("suratpengantar as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                db::raw("sum(e.nominaltagih) as omsettambahan"),
                db::raw("max(d.liter) as liter"),
            )
            ->join(DB::raw("upahsupir as b with(readuncommitted) "), 'a.upah_id', 'b.id')
            ->join(DB::raw("upahsupirrincian as d with(readuncommitted) "), function ($join) {
                $join->on('b.id', '=', 'd.upahsupir_id');
                $join->on('a.container_id', '=', 'd.container_id');
                $join->on('a.statuscontainer_id', '=', 'd.statuscontainer_id');
            })
            ->join(DB::raw($tempData . " as c "), 'a.nobukti', 'c.nobukti')
            ->leftjoin(DB::raw("suratpengantarbiayatambahan as e with (readuncommitted) "), 'a.id', 'e.suratpengantar_id')
            ->GroupBy('a.nobukti');

        DB::table($temptrip)->insertUsing([
            'nobukti',
            'omsettambahan',
            'liter',
        ], $querytemptrip);

        // uang lain
        $tempuanglain = '##tempdatauanglain' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempuanglain, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('tolsupir', 15, 2)->nullable();
        });

        $querytempuanglain = DB::table("suratpengantar")->from(
            DB::raw("suratpengantar as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                db::raw("sum(isnull(a.tolsupir,0)) as tolsupir"),
                db::raw("sum(isnull(b.nominal,0)) as nominal"),
            )
            ->leftjoin(DB::raw("suratpengantarbiayatambahan as b with(readuncommitted) "), 'a.id', 'b.suratpengantar_id')
            ->join(DB::raw($tempData . " as c "), 'a.nobukti', 'c.nobukti')
            ->groupby('a.nobukti');



        DB::table($tempuanglain)->insertUsing([
            'nobukti',
            'tolsupir',
            'nominal',
        ], $querytempuanglain);

        // keterangan tambahan

        $tempketeranganlain = '##tempdataketeranganlain' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempketeranganlain, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
        });

        $temprekapketeranganlain = '##tempdatarekapketeranganlain' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapketeranganlain, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
        });

        $querytempketeranganlain = DB::table("suratpengantar")->from(
            DB::raw("suratpengantar as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                'b.keteranganbiaya as keterangan',
                'b.nominal'
            )
            ->leftjoin(DB::raw("suratpengantarbiayatambahan as b with(readuncommitted) "), 'a.id', 'b.suratpengantar_id')
            ->join(DB::raw($tempData . " as c "), 'a.nobukti', 'c.nobukti');

        DB::table($tempketeranganlain)->insertUsing([
            'nobukti',
            'keterangan',
            'nominal',
        ], $querytempketeranganlain);

        $querytemprekapketeranganlain = DB::table($tempketeranganlain)->from(
            DB::raw($tempketeranganlain . " as b with (readuncommitted)")
        )
            ->select(
                db::raw("
                    distinct b.nobukti,Stuff((SELECT DISTINCT ', ' + trim(a.keterangan)+' ( '+ format(a.nominal,'#,#') +' ) '
                        FROM " . $tempketeranganlain . " a
                        WHERE  a.nobukti=b.nobukti
                        FOR XML PATH('')), 1, 2, '') AS keterangan
                    ")
            );

        DB::table($temprekapketeranganlain)->insertUsing([
            'nobukti',
            'keterangan',
        ], $querytemprekapketeranganlain);



        // komisi supir bukti
        $tempbuktikomisi = '##tempdatabuktikomisi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempbuktikomisi, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('nobuktikbtkomisi', 500)->nullable();
        });

        $querytempbuktikomisi = DB::table("suratpengantar")->from(
            DB::raw("suratpengantar as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                db::raw("max(isnull(d.pengeluaran_nobukti,'')) as nobuktikbtkomisi"),
            )
            ->leftjoin(DB::raw("gajisupirdetail as b with(readuncommitted) "), function ($join) {
                $join->on('a.nobukti', '=', 'b.suratpengantar_nobukti');
            })
            ->leftjoin(DB::raw("pendapatansupirdetail as c with(readuncommitted) "), function ($join) {
                $join->on('a.nobukti', '=', 'c.nobuktitrip');
                $join->on('b.nobukti', '=', 'c.nobuktirincian');
                $join->on('a.supir_id', '=', 'c.supir_id');
            })
            ->leftjoin(DB::raw("pendapatansupirheader as d with(readuncommitted) "), function ($join) {
                $join->on('c.nobukti', '=', 'd.nobukti');
            })
            ->join(DB::raw($tempData . " as c1 "), 'a.nobukti', 'c1.nobukti')
            ->groupby('a.nobukti');


        DB::table($tempbuktikomisi)->insertUsing([
            'nobukti',
            'nobuktikbtkomisi',
        ], $querytempbuktikomisi);


        // 

        $tempDataOrderan = '##tempDataOrderan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempDataOrderan, function ($table) {
            $table->string('nobukti', 50)->nullable();
        });

        $queryJobTrucking = DB::table($tempData)->from(
            DB::raw($tempData . " as a with (readuncommitted)")
        )

            ->select(
                'jobtrucking'
            )
            ->groupBy("jobtrucking");

        DB::table($tempDataOrderan)->insertUsing([
            'nobukti',
        ], $queryJobTrucking);

        $tempDataInvoice = '##tempDataInvoice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempDataInvoice, function ($table) {
            $table->string('invoice', 50)->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->longText('nobuktitrip')->nullable();
        });

        $queryDataOrderan = DB::table("invoicedetail")->from(
            DB::raw("invoicedetail as a with (readuncommitted)")
        )

            ->select(
                'a.nobukti',
                'a.orderantrucking_nobukti',
                'a.suratpengantar_nobukti'
            )
            ->join(DB::raw($tempDataOrderan . " as b "), 'a.orderantrucking_nobukti', 'b.nobukti');

        DB::table($tempDataInvoice)->insertUsing([
            'invoice',
            'nobukti',
            'nobuktitrip'
        ], $queryDataOrderan);


        $tempDataInvoiceDetail = '##tempDataInvoiceDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempDataInvoiceDetail, function ($table) {
            $table->string('invoice', 50)->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->longText('nobuktitrip')->nullable();
            $table->datetime('tgl')->nullable();
        });

        $xinvoice = '';
        $xnobukti = '';
        $xnobuktitrip = '';

        $tempList = '##tempList' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempList, function ($table) {
            $table->string('notrip', 50)->nullable();
        });


        $rekapCursor1a = DB::table($tempDataInvoice)->select('invoice', 'nobukti', 'nobuktitrip');

        // $result = [];

        // foreach ($rekapCursor1a as $row) {
        //     $xinvoice = $row->invoice;
        //     $xnobukti = $row->nobukti;
        //     $xnobuktitrip = $row->nobuktitrip;

        //     $nobuktitripArr = explode(',', $xnobuktitrip);
        //     $trimmedNobuktitripArr = array_map('trim', $nobuktitripArr);

        //     foreach ($trimmedNobuktitripArr as $trimmedNobuktitrip) {
        //         $invoiceDetails = [
        //             'invoice' => $xinvoice,
        //             'nobukti' => $xnobukti,
        //             'nobuktitrip' => $trimmedNobuktitrip,
        //         ];

        //         $result[] = $invoiceDetails;
        //     }
        // }

        DB::table($tempDataInvoiceDetail)->insertUsing([
            'invoice',
            'nobukti',
            'nobuktitrip'
        ], $rekapCursor1a);

        DB::table($tempDataInvoiceDetail . ' as A')
            ->join('suratpengantar as B', 'A.nobuktitrip', '=', 'B.nobukti')
            ->update(['A.tgl' => DB::raw('B.tglbukti')]);

        $tempListTrip = '##tempListTrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempListTrip, function ($table) {
            $table->bigIncrements('id');
            $table->string('nobukti', 50)->nullable();
            $table->longText('nobuktitrip', 50)->nullable();
            $table->datetime('tgl')->nullable();
        });

        $queryListTrip = DB::table($tempDataInvoiceDetail)->from(
            DB::raw($tempDataInvoiceDetail . " as a")
        )
            ->select(
                'nobukti',
                'tgl',
                'nobuktitrip'
            )
            ->orderBy('nobukti', 'asc')
            ->orderBy('tgl', 'asc')
            ->orderBy('nobuktitrip', 'asc');

        DB::table($tempListTrip)->insertUsing([
            'nobukti',
            'tgl',
            'nobuktitrip',

        ], $queryListTrip);

        $tempRekapListTrip = '##tempListTrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempRekapListTrip, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->integer('id')->nullable();
        });

        $queryRekapListTrip = DB::table($tempListTrip)->from(
            DB::raw($tempListTrip . " as a")
        )
            ->select(
                'nobukti',
                DB::raw("min(id) as id")
            )
            ->groupBy('nobukti');

        DB::table($tempRekapListTrip)->insertUsing([
            'nobukti',
            'id',
        ], $queryRekapListTrip);

        $tempOrderanTrucking = '##tempOrderanTrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempOrderanTrucking, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->integer('id')->nullable();
            $table->string('notrip', 50)->nullable();
        });

        $queryOrderanTrucking = DB::table($tempRekapListTrip)->from(
            DB::raw($tempRekapListTrip . " as a")
        )
            ->select(
                'nobukti',
                'id'
            );

        DB::table($tempOrderanTrucking)->insertUsing([
            'nobukti',
            'id',
        ], $queryOrderanTrucking);

        DB::table($tempOrderanTrucking . ' as a')
            ->join($tempListTrip . ' as B', 'a.id', '=', 'B.id')
            ->update(['A.notrip' => DB::raw('B.nobuktitrip')]);

        $tempInvoice = '##tempInvoice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempInvoice, function ($table) {
            $table->string('jobtrucking', 100)->nullable();
            $table->double('omset', 15, 2)->nullable();
            $table->double('extralain', 15, 2)->nullable();
            $table->integer('id')->nullable();
            $table->string('invoice', 50)->nullable();
            $table->longtext('notrip')->nullable();
            $table->longtext('notripawal')->nullable();
        });

        $queryTempInvoice = DB::table('invoicedetail')->from(
            DB::raw("invoicedetail as a with (readuncommitted)")
        )
            ->select(
                'a.orderantrucking_nobukti',
                'a.total as nominal',
                DB::RAW("(a.nominalextra+a.nominalretribusi) as extralain"),
                'b.id',
                'a.nobukti',
                'b.notrip',
                db::raw("(case when charindex(',',suratpengantar_nobukti)=0 then suratpengantar_nobukti else  substring(suratpengantar_nobukti,0,charindex(',',suratpengantar_nobukti)) end) as notripawal")
            )
            ->join(DB::raw($tempOrderanTrucking . " as b "), 'a.orderantrucking_nobukti', 'b.nobukti');

        DB::table($tempInvoice)->insertUsing([
            'jobtrucking',
            'omset',
            'extralain',
            'id',
            'invoice',
            'notrip',
            'notripawal',
        ], $queryTempInvoice);

        $formatric = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('text as id')->where('grp', 'EXPORT RINCIAN MINGGUAN')->where('subgrp', 'EXPORT RINCIAN MINGGUAN')
            ->first()->id ?? 0;



        //     dd(db::table($tempuangjalan)->get());
        //     // dd(db::table($tempData)->get());

        // $data =  DB::table($tempData)->from(
        //     DB::raw($tempData . " as a")
        // )
        //     ->select(
        //         'a.nobukti',

        //     )
        //     ->leftjoin(DB::raw($tempInvoice . " as b "), 'a.nobukti', 'b.notrip')
        //     ->leftjoin(DB::raw($tempInvoice . " as c "), 'a.jobtrucking', 'c.jobtrucking')
        //     ->leftjoin(DB::raw($tempuangjalan . " as d "), 'a.nobuktiric', 'd.nobukti')


        //     ->get();

        //     dd($data);

        // dd(db::table($tempData)->where('jobtrucking','JT 0189/VI/2024')->get());
        // dd(db::table($tempInvoice)->where('jobtrucking','JT 0189/VI/2024')->get());


        $data =  DB::table($tempData)->from(
            DB::raw($tempData . " as a")
        )
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.nopol',
                'a.namasupir',
                'a.rute',
                'a.qty',
                'a.lokasimuat',
                'a.nocontseal',
                'a.emkl',
                'a.spfull',
                'a.spempty',
                'a.spfullempty',
                'a.jobtrucking',
                DB::raw("(case when isnull(c.invoice,'')='' then 0 else 
                        isnull(b.omset,0) 
                        end) as omset"),
                DB::raw("( case when isnull(c.invoice,'')='' then 0 else
                    (case when isnull(b.notrip,'')='' then isnull(e.omsettambahan,0) else isnull(b.extralain,0)  end) 
                    end) as omsettambahan"),
                DB::raw("0 as omsetextrabbm"),
                DB::raw("isnull(c.invoice,'') as invoice"),
                DB::raw("isnull(A.gajisupir,0) as borongan"),
                DB::raw("isnull(A.nobuktiebs,'') as nobuktiebs"),
                DB::raw("isnull(A.pengeluarannobuktiebs,'') as pengeluarannobuktiebs"),
                DB::raw("isnull(A.voucher,0) as voucher"),
                DB::raw("isnull(A.novoucher,'') as novoucher"),
                DB::raw("isnull(A.gajisupir,0) as gajisupir"),
                DB::raw("isnull(a.komisisupir,0)  as komisi"),
                DB::raw("isnull(a.gajikenek,0) as gajikenek"),
                DB::raw("0 as gajimingguan"),
                DB::raw("0 as gajilain"),
                DB::raw("'' as ket"),
                DB::raw("isnull(g.nobuktikbtkomisi,'') as nobuktikbtkomisi"),
                DB::raw("isnull(f.nominal,0) as uanglain"),
                DB::raw("isnull(h.keterangan,'') as ketuanglain"),
                DB::raw("isnull(f.tolsupir,0) as tolsupir"),
                DB::raw("0 as uangbon"),
                DB::raw("isnull(A.pengeluarannobuktiebs,'') as nobuktikbtebs2"),
                DB::raw("isnull(a.gajiritasi,0) as ritasi"),
                DB::raw("0 as extrabbm"),
                DB::raw("isnull(a.ketritasi,'') as ketritasi"),
                DB::raw("(case when isnull(a.urutric,0)=1 then isnull(d.nominaluangjalan,0) else 0 end) as uangjalan"),
                DB::raw("(case when isnull(a.urutric,0)=1 then isnull(d.nominaluangbbm,0) else 0 end) as uangbbm"),
                DB::raw("(case when isnull(a.urutric,0)=1 then isnull(d.nominaluangmakan,0) else 0 end) as uangmakan"),
                DB::raw("(isnull(A.gajisupir,0)+isnull(a.komisisupir,0)+isnull(a.gajikenek,0)+isnull(f.nominal,0) 
                            /*+(case when isnull(a.urutric,0)=1 then isnull(d.nominaluangjalan,0) else 0 end)*/                
                            +(case when isnull(a.urutric,0)=1 then isnull(d.nominaluangbbm,0) else 0 end)
                            +(case when isnull(a.urutric,0)=1 then isnull(d.nominaluangmakan,0) else 0 end)
                            )
                            as totalbiaya"),
                DB::raw("((isnull(a.omset,0))-
                            (isnull(A.gajisupir,0)+isnull(a.komisisupir,0)+isnull(a.gajikenek,0)+isnull(f.nominal,0) 
                            /*+(case when isnull(a.urutric,0)=1 then isnull(d.nominaluangjalan,0) else 0 end)*/                
                            +(case when isnull(a.urutric,0)=1 then isnull(d.nominaluangbbm,0) else 0 end)
                            +(case when isnull(a.urutric,0)=1 then isnull(d.nominaluangmakan,0) else 0 end)
                            ))
                             as sisa"),
                DB::raw("0 as bongkarmuat"),
                DB::raw("'' as panjar"),
                DB::raw("'' as mandor"),
                DB::raw("'' as supirex"),
                DB::raw("isnull(e.liter,0) as liter"),
                db::raw($formatric . " as formatric"),
                
                // SURABAYA
                DB::raw("isnull(a.gajikenek,0) as uangburuh"),
                DB::raw("isnull(a.biayatambahan,0) as uangextra"),                
                DB::raw("( case when isnull(c.invoice,'')='' then 0 else (isnull(a.omset,0) + isnull(e.omsettambahan,0)) end) as omsetsurabaya"),
                DB::raw("isnull(a.keteranganBiayaTambahan,0) as keteranganbiayatambahan"),
            )
            ->leftjoin(DB::raw($tempInvoice . " as b "), 'a.nobukti', 'b.notripawal')
            ->leftjoin(DB::raw($tempInvoice . " as c "), 'a.jobtrucking', 'c.jobtrucking')
            ->leftjoin(DB::raw($tempuangjalan . " as d "), 'a.nobuktiric', 'd.nobukti')
            ->leftjoin(DB::raw($temptrip . " as e "), 'a.nobukti', 'e.nobukti')
            ->leftjoin(DB::raw($tempuanglain . " as f "), 'a.nobukti', 'f.nobukti')
            ->leftjoin(DB::raw($tempbuktikomisi . " as g "), 'a.nobukti', 'g.nobukti')
            ->leftjoin(DB::raw($temprekapketeranganlain . " as h "), 'a.nobukti', 'h.nobukti')
            ->orderBy('a.nopol')
            ->orderBy('a.tglbukti')
            ->orderBy('a.namasupir')
            // ->whereraw("a.jobtrucking='JT 0189/VI/2024'")    
            ->get();



        return $data;
    }
}
