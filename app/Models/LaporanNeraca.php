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

    public function getReport($sampai, $eksport)
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

        $judul = Parameter::where('grp', '=', 'JUDULAN LAPORAN')->first();
        $judulLaporan = $judul->text;

        if ($eksport == 1) {

            DB::table('akunpusatdetail')
                ->where('bulan', '<>', 0)
                ->delete();


            $subquery1 = DB::table('jurnalumumpusatheader as J')
                ->select('D.coamain as FCOA', DB::raw('YEAR(D.tglbukti) as FThn'), DB::raw('MONTH(D.tglbukti) as FBln'), DB::raw('SUM(D.nominal) as FNominal'))
                ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
                ->join('mainakunpusat as C', 'C.coa', '=', 'D.coamain')
                ->where('D.tglbukti', '>=', $ptgl)
                ->groupBy('D.coamain', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));

            $subquery2 = DB::table('jurnalumumpusatheader as J')
                ->select('LR.coa', DB::raw('YEAR(D.tglbukti) as FThn'), DB::raw('MONTH(D.tglbukti) as FBln'), DB::raw('SUM(D.nominal) as FNominal'))
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

            $RecalKdPerkiraan = DB::table(DB::raw("({$subquery1->toSql()} UNION ALL {$subquery2->toSql()}) as V"))
                ->mergeBindings($subquery1)
                ->mergeBindings($subquery2)
                ->groupBy('FCOA', 'FThn', 'FBln')
                ->select('FCOA', 'FThn', 'FBln', DB::raw('SUM(FNominal) as FNominal'));

            DB::table('akunpusatdetail')->insertUsing([
                'coa',
                'tahun',
                'bulan',
                'nominal',

            ], $RecalKdPerkiraan);

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
                    db::raw("isnull(cd.nominal,0) as nominal"),
                    'a.order',
                    'a.keterangantype',
                    'a.akuntansi_id',
                )
                ->join(db::raw($tempAkunPusatDetail . " cd with (readuncommitted)"), 'c.coa', 'cd.coa')
                ->join(db::raw("maintypeakuntansi a with (readuncommitted)"), 'a.kodetype', 'c.type');

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
                    db::raw("( CASE d.akuntansi_id WHEN 1 THEN SUM(d.Nominal) ELSE SUM(d.Nominal * -1) END)  AS nominal"),
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
                    DB::raw("'" . $getJudul->text . "' as judul")
                )
                ->whereRaw("isnull(xx.Nominal,0)<>0")
                ->orderby('xx.id');

            goto selesai;
        }


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
                $table->string('namasupplier', 500)->nullable();
                $table->string('keterangan', 500)->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->date('tglbukti')->nullable();
                $table->date('tgljatuhtempo')->nullable();
                $table->integer('cicil')->nullable();
                $table->double('nominal')->nullable();
                $table->double('bayar')->nullable();
                $table->double('saldo')->nullable();
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
                'namasupplier',
                'keterangan',
                'nobukti',
                'tglbukti',
                'tgljatuhtempo',
                'cicil',
                'nominal',
                'bayar',
                'saldo',
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
                $table->string('namaagen', 500)->nullable();
                $table->string('keterangan', 500)->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->date('tglbukti')->nullable();
                $table->date('tgljatuhtempo')->nullable();
                $table->integer('cicil')->nullable();
                $table->double('nominal')->nullable();
                $table->double('bayar')->nullable();
                $table->double('saldo')->nullable();
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
                'namaagen',
                'keterangan',
                'nobukti',
                'tglbukti',
                'tgljatuhtempo',
                'cicil',
                'nominal',
                'bayar',
                'saldo',
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

            $temppinjamansupir = '##temppinjamansupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppinjamansupir, function ($table) {
                $table->date('tanggal')->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->string('keterangan', 500)->nullable();
                $table->double('debet')->nullable();
                $table->double('kredit')->nullable();
                $table->double('saldo')->nullable();
                $table->string('disetujui', 500)->nullable();
                $table->string('diperiksa', 500)->nullable();
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
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'disetujui',
                'diperiksa',
            ], (new LaporanKeteranganPinjamanSupir())->getReport($tglsd, $jenis, 1));

            // Pinjaman karyawan

            $temppinjamankaryawan = '##temppinjamankaryawan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppinjamankaryawan, function ($table) {
                $table->string('nobukti', 500)->nullable();
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
            ], (new LaporanPinjamanSupirKaryawan())->getReport($tglsd, 1));
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
            });



            DB::table($temppiutanglain)->insertUsing([
                'judul',
                'judullaporan',
                'urut',
                'nobukti',
                'tglbukti',
                'keterangan',
                'nominal',
                'jenisorder',
            ], (new LaporanRekapTitipanEmkl())->getData($tglsd, 1));
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

            $tempkasgantung = '##tempkasgantung' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkasgantung, function ($table) {
                $table->date('tanggal')->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->string('keterangan', 500)->nullable();
                $table->double('debet')->nullable();
                $table->double('kredit')->nullable();
                $table->double('saldo')->nullable();
                $table->string('disetujui', 500)->nullable();
                $table->string('diperiksa', 500)->nullable();
            });



            DB::table($tempkasgantung)->insertUsing([
                'tanggal',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'disetujui',
                'diperiksa',
            ], (new LaporanKasGantung())->getReport($tglsd, 1));

            // Kas 

            $tempkas = '##tempkas' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkas, function ($table) {
                $table->id();
                $table->integer('urut')->nullable();
                $table->string('keterangancoa', 500)->nullable();
                $table->string('namabank', 500)->nullable();
                $table->date('tglbukti')->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->longText('keterangan')->nullable();
                $table->double('debet')->nullable();
                $table->double('kredit')->nullable();
                $table->double('saldo')->nullable();
                $table->string('judullaporan', 500)->nullable();
                $table->string('judul', 500)->nullable();
                $table->string('tglcetak', 500)->nullable();
                $table->string('usercetak', 500)->nullable();
            });




            $kas_id = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text'
                )
                ->where('grp', 'KAS/BANK')
                ->where('subgrp', 'KAS')
                ->first()->text ?? 0;
            DB::table($tempkas)->insertUsing([
                'urut',
                'keterangancoa',
                'namabank',
                'tglbukti',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'judullaporan',
                'judul',
                'tglcetak',
                'usercetak',
            ], (new LaporanKasBank())->getReport($tglsd, $tglsd, $kas_id, 1));

            // dd(db::table($tempkas)->get());

            // Bank 

            $tempbank = '##tempbank' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbank, function ($table) {
                $table->id();
                $table->integer('urut')->nullable();
                $table->string('keterangancoa', 500)->nullable();
                $table->string('namabank', 500)->nullable();
                $table->date('tglbukti')->nullable();
                $table->string('nobukti', 500)->nullable();
                $table->longText('keterangan')->nullable();
                $table->double('debet')->nullable();
                $table->double('kredit')->nullable();
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
                'keterangancoa',
                'namabank',
                'tglbukti',
                'nobukti',
                'keterangan',
                'debet',
                'kredit',
                'saldo',
                'judullaporan',
                'judul',
                'tglcetak',
                'usercetak',
            ], (new LaporanKasBank())->getReport($tglsd, $tglsd, $bank_id, 1));

            // saldopersediaan 

            $tempstok = '##tempstok' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempstok, function ($table) {
                $table->string('header', 500)->nullable();
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
                    db::raw("sum(nominal-bayar) as nominal")
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
                    db::raw("sum(nominal-bayar) as nominal")
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

            DB::table($tempperkiraanbanding)->insert(
                [
                    'coa' =>  $memo['JURNAL'],
                    'nominal' => $piutanglain,
                ]
            );

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
                    db::raw("saldo as nominal")
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
                ->delete();


            $subquery1 = DB::table('jurnalumumpusatheader as J')
                ->select('D.coamain as FCOA', DB::raw('YEAR(D.tglbukti) as FThn'), DB::raw('MONTH(D.tglbukti) as FBln'), DB::raw('SUM(D.nominal) as FNominal'))
                ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
                ->join('mainakunpusat as C', 'C.coa', '=', 'D.coamain')
                ->where('D.tglbukti', '>=', $ptgl)
                ->groupBy('D.coamain', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));

            $subquery2 = DB::table('jurnalumumpusatheader as J')
                ->select('LR.coa', DB::raw('YEAR(D.tglbukti) as FThn'), DB::raw('MONTH(D.tglbukti) as FBln'), DB::raw('SUM(D.nominal) as FNominal'))
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

            $RecalKdPerkiraan = DB::table(DB::raw("({$subquery1->toSql()} UNION ALL {$subquery2->toSql()}) as V"))
                ->mergeBindings($subquery1)
                ->mergeBindings($subquery2)
                ->groupBy('FCOA', 'FThn', 'FBln')
                ->select('FCOA', 'FThn', 'FBln', DB::raw('SUM(FNominal) as FNominal'));

            DB::table('akunpusatdetail')->insertUsing([
                'coa',
                'tahun',
                'bulan',
                'nominal',

            ], $RecalKdPerkiraan);

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
                    db::raw("isnull(cd.nominal,0) as nominal"),
                    'a.order',
                    'a.keterangantype',
                    'a.akuntansi_id',
                )
                ->join(db::raw($tempAkunPusatDetail . " cd with (readuncommitted)"), 'c.coa', 'cd.coa')
                ->join(db::raw("maintypeakuntansi a with (readuncommitted)"), 'a.kodetype', 'c.type');

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
                    db::raw("( CASE d.akuntansi_id WHEN 1 THEN SUM(d.Nominal) ELSE SUM(d.Nominal * -1) END)  AS nominal"),
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
        $data = db::table($temtabel)->from(db::raw($temtabel . " xx"))
            ->select(
                'xx.TipeMaster',
                'xx.Order',
                'xx.Type',
                'xx.KeteranganType',
                'xx.coa',
                'xx.Parent',
                'xx.KeteranganCoa',
                'xx.Nominal',
                'xx.CmpyName',
                'xx.pBulan',
                'xx.pTahun',
                'xx.GNeraca',
                'xx.GLR',
                'xx.KeteranganCoaParent',
                'xx.pTglSd',
                db::raw("xx.coabanding"),
                db::raw("xx.nominalbanding"),
                db::raw("xx.selisih")
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
