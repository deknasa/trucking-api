<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKartuPiutangPerAgen extends MyModel
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

    public function getReport($dari, $sampai, $agenDari, $agenSampai, $prosesneraca)
    {
        $prosesneraca = $prosesneraca ?? 0;

        $sampai = $dari;
        $tgl = '01-' . date('m', strtotime($dari)) . '-' . date('Y', strtotime($dari));
        $dari1 = date('Y-m-d', strtotime($tgl));
        $keteranganagen = '';
        if ($agenDari == 0 || $agenSampai == 0) {
            $keteranganagen = 'SEMUA';
        }

        if ($agenDari == 0) {
            $agenDari = db::table('agen')->from(db::raw("agen with (readuncommitted)"))
                ->select('id')->orderby('id', 'asc')->first()->id ?? 0;
        }

        if ($agenSampai == 0) {
            $agenSampai = db::table('agen')->from(db::raw("agen with (readuncommitted)"))
                ->select('id')->orderby('id', 'desc')->first()->id ?? 0;
        }

        if ($agenDari > $agenSampai) {
            $agenDari1 = $agenSampai;
            $agenSampai1 = $agenDari;
            $agenDari = $agenDari1;
            $agenSampai = $agenSampai1;
        }

        $agendarinama = db::table('agen')->from(db::raw("agen with (readuncommitted)"))
            ->select('namaagen')->orderby('id', 'asc')->where('id', $agenDari)->first()->namaagen ?? '';

        $agensampainama = db::table('agen')->from(db::raw("agen with (readuncommitted)"))
            ->select('namaagen')->orderby('id', 'asc')->where('id', $agenSampai)->first()->namaagen ?? '';


        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $tglsaldo = DB::table('parameter')
            ->select('text')
            ->where('grp', 'SALDO')
            ->where('subgrp', 'SALDO')
            ->first()->text ?? '1900-01-01';

        $temppelunasansaldo = '##temppelunasansaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppelunasansaldo, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        $temppiutangsaldo = '##temppiutangsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppiutangsaldo, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        $querypiutangsaldo = db::table('piutangheader')->from(db::raw("piutangheader a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                db::raw("sum(b.nominal) as nominal"),
            )
            ->join(db::raw("piutangdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("a.tglbukti<'" . $dari1 . "'")
            ->whereRaw("(a.agen_id>=" . $agenDari . " and a.agen_id<=" . $agenSampai . ")")
            ->groupby('a.nobukti');
        // dd($querypiutangsaldo->get());

        DB::table($temppiutangsaldo)->insertUsing([
            'nobukti',
            'nominal',
        ], $querypiutangsaldo);

        $querypelunasansaldo = db::table('pelunasanpiutangheader')->from(db::raw("pelunasanpiutangheader a with (readuncommitted)"))
            ->select(
                'c.nobukti',
                db::raw("sum(isnull(b.nominal,0)+isnull(b.potongan,0)+isnull(b.potonganpph,0)) as nominal"),
            )
            ->join(db::raw("pelunasanpiutangdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw($temppiutangsaldo . " c "), 'b.piutang_nobukti', 'c.nobukti')
            ->whereRaw("a.tglbukti<'" . $dari1 . "'")
            ->groupby('c.nobukti');

        DB::table($temppelunasansaldo)->insertUsing([
            'nobukti',
            'nominal',
        ], $querypelunasansaldo);


        $temprekappiutang = '##temprekappiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekappiutang, function ($table) {
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });



        $queryrekappiutang = db::table($temppiutangsaldo)->from(db::raw($temppiutangsaldo . " a "))
            ->select(
                'a.nobukti',
                db::raw("(isnull(a.nominal,0)-isnull(b.nominal,0)) as nominal"),
            )
            ->leftjoin(db::raw($temppelunasansaldo . " b "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(isnull(a.nominal,0)-isnull(b.nominal,0))<>0");

        DB::table($temprekappiutang)->insertUsing([
            'nobukti',
            'nominal',
        ], $queryrekappiutang);


        $queryrekappiutang = db::table("piutangheader")->from(db::raw("piutangheader a with (readuncommitted) "))
            ->select(
                'a.nobukti',
                db::raw("sum(isnull(b.nominal,0)) as nominal"),
            )
            ->leftjoin(db::raw("piutangdetail b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("(a.tglbukti>='" . $dari1 . "' and a.tglbukti<='" . $sampai . "')")
            ->whereRaw("(a.agen_id>=" . $agenDari . " and a.agen_id<=" . $agenSampai . ")")
            ->groupby('a.nobukti');

        DB::table($temprekappiutang)->insertUsing([
            'nobukti',
            'nominal',
        ], $queryrekappiutang);

        // dd('test');
        $temprekapdata = '##temprekapdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapdata, function ($table) {
            $table->id();
            $table->integer('agen_id')->nullable();
            $table->string('nobukti', 50);
            $table->dateTime('tglbukti');
            $table->double('nominalpiutang')->nullable();
            $table->dateTime('tgllunas');
            $table->double('nominallunas')->nullable();
            $table->string('nobuktipiutang', 50);
            $table->dateTime('tglberjalan');
            $table->string('jenispiutang', 50);
            $table->integer('urut')->nullable();
        });


        // dd(db::table($temprekappiutang)->get());

        $queryrekapdata = db::table($temprekappiutang)->from(db::raw($temprekappiutang . " a  "))
            ->select(
                'b.agen_id',
                'a.nobukti',
                'b.tglbukti',
                'a.nominal',
                db::raw("'1900/1/1' as tgllunas"),
                db::raw("0 as nominallunas"),
                'a.nobukti as nobuktipiutang',
                'b.tglbukti as tglberjalan',
                db::raw(" 'PIUTANG USAHA' as jenispiutang"),
                db::raw("0 as urut"),
            )
            ->join(db::raw("piutangheader b with (readuncommitted) "), 'a.nobukti', 'b.nobukti');

        DB::table($temprekapdata)->insertUsing([
            'agen_id',
            'nobukti',
            'tglbukti',
            'nominalpiutang',
            'tgllunas',
            'nominallunas',
            'nobuktipiutang',
            'tglberjalan',
            'jenispiutang',
            'urut',
        ], $queryrekapdata);


        $queryrekapdata = db::table($temprekappiutang)->from(db::raw($temprekappiutang . " a  "))
            ->select(
                'b.agen_id',
                'd.nobukti',
                db::raw("'1900/1/1' as tglbukti"),
                db::raw("0 as nominal"),
                "d.tglbukti as tgllunas",
                db::raw("(isnull(c.nominal,0)) as nominallunas"),
                'a.nobukti as nobuktipiutang',
                'b.tglbukti as tglberjalan',
                db::raw(" 'PIUTANG USAHA' as jenispiutang"),
                db::raw("1 as urut"),
            )
            ->join(db::raw("piutangheader b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("pelunasanpiutangdetail c with (readuncommitted) "), 'a.nobukti', 'c.piutang_nobukti')
            ->join(db::raw("pelunasanpiutangheader d with (readuncommitted) "), 'c.nobukti', 'd.nobukti')
            ->whereRaw("(d.tglbukti>='" . $dari1 . "' and d.tglbukti<='" . $sampai . "')")
            ->whereRaw("isnull(c.nominal,0)<>0");

        DB::table($temprekapdata)->insertUsing([
            'agen_id',
            'nobukti',
            'tglbukti',
            'nominalpiutang',
            'tgllunas',
            'nominallunas',
            'nobuktipiutang',
            'tglberjalan',
            'jenispiutang',
            'urut',
        ], $queryrekapdata);

        // get nota kredit pendapatan
        $queryrekapdata = db::table($temprekappiutang)->from(db::raw($temprekappiutang . " a  "))
            ->select(
                'b.agen_id',
                'e.nobukti',
                db::raw("'1900/1/1' as tglbukti"),
                db::raw("0 as nominal"),
                "d.tglbukti as tgllunas",
                db::raw("(isnull(c.potongan,0)) as nominallunas"),
                'a.nobukti as nobuktipiutang',
                'b.tglbukti as tglberjalan',
                db::raw(" 'PIUTANG USAHA' as jenispiutang"),
                db::raw("2 as urut"),
            )
            ->join(db::raw("piutangheader b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("pelunasanpiutangdetail c with (readuncommitted) "), 'a.nobukti', 'c.piutang_nobukti')
            ->join(db::raw("pelunasanpiutangheader d with (readuncommitted) "), 'c.nobukti', 'd.nobukti')
            // ->leftjoin(db::raw("notakreditdetail e with (readuncommitted) "), 'c.invoice_nobukti', 'e.invoice_nobukti')
            ->leftjoin(DB::raw("notakreditdetail as e"), function ($join) {
                $join->on('c.invoice_nobukti', '=', 'e.invoice_nobukti');
                $join->on('d.notakredit_nobukti', '=', 'e.nobukti');
            })
            ->whereRaw("(d.tglbukti>='" . $dari1 . "' and d.tglbukti<='" . $sampai . "')")
            ->whereRaw("isnull(c.potongan,0)<>0");

        DB::table($temprekapdata)->insertUsing([
            'agen_id',
            'nobukti',
            'tglbukti',
            'nominalpiutang',
            'tgllunas',
            'nominallunas',
            'nobuktipiutang',
            'tglberjalan',
            'jenispiutang',
            'urut',
        ], $queryrekapdata);

        // get nota kredit pph
        $queryrekapdata = db::table($temprekappiutang)->from(db::raw($temprekappiutang . " a  "))
            ->select(
                'b.agen_id',
                'e.nobukti',
                db::raw("'1900/1/1' as tglbukti"),
                db::raw("0 as nominal"),
                "d.tglbukti as tgllunas",
                db::raw("(isnull(c.potonganpph,0)) as nominallunas"),
                'a.nobukti as nobuktipiutang',
                'b.tglbukti as tglberjalan',
                db::raw(" 'PIUTANG USAHA' as jenispiutang"),
                db::raw("3 as urut"),
            )
            ->join(db::raw("piutangheader b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("pelunasanpiutangdetail c with (readuncommitted) "), 'a.nobukti', 'c.piutang_nobukti')
            ->join(db::raw("pelunasanpiutangheader d with (readuncommitted) "), 'c.nobukti', 'd.nobukti')
            // ->leftjoin(db::raw("notakreditdetail e with (readuncommitted) "), 'c.invoice_nobukti', 'e.invoice_nobukti')
            ->leftjoin(DB::raw("notakreditdetail as e"), function ($join) {
                $join->on('c.invoice_nobukti', '=', 'e.invoice_nobukti');
                $join->on('d.notakreditpph_nobukti', '=', 'e.nobukti');
            })
            ->whereRaw("(d.tglbukti>='" . $dari1 . "' and d.tglbukti<='" . $sampai . "')")
            ->whereRaw("isnull(c.potonganpph,0)<>0");

        DB::table($temprekapdata)->insertUsing([
            'agen_id',
            'nobukti',
            'tglbukti',
            'nominalpiutang',
            'tgllunas',
            'nominallunas',
            'nobuktipiutang',
            'tglberjalan',
            'jenispiutang',
            'urut',
        ], $queryrekapdata);

        $queryrekapdata = db::table($temprekappiutang)->from(db::raw($temprekappiutang . " a  "))
            ->select(
                'b.agen_id',
                'e.nobukti',
                db::raw("'1900/1/1' as tglbukti"),
                db::raw("0 as nominal"),
                "d.tglbukti as tgllunas",
                db::raw("(isnull(c.nominallebihbayar,0)) as nominallunas"),
                'a.nobukti as nobuktipiutang',
                'b.tglbukti as tglberjalan',
                db::raw(" 'PIUTANG USAHA' as jenispiutang"),
                db::raw("4 as urut"),
            )
            ->join(db::raw("piutangheader b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("pelunasanpiutangdetail c with (readuncommitted) "), 'a.nobukti', 'c.piutang_nobukti')
            ->join(db::raw("pelunasanpiutangheader d with (readuncommitted) "), 'c.nobukti', 'd.nobukti')
            ->leftjoin(db::raw("notadebetdetail e with (readuncommitted) "), 'c.invoice_nobukti', 'e.invoice_nobukti')
            ->whereRaw("(d.tglbukti>='" . $dari1 . "' and d.tglbukti<='" . $sampai . "')")
            ->whereRaw("isnull(c.nominallebihbayar,0)<>0");

        DB::table($temprekapdata)->insertUsing([
            'agen_id',
            'nobukti',
            'tglbukti',
            'nominalpiutang',
            'tgllunas',
            'nominallunas',
            'nobuktipiutang',
            'tglberjalan',
            'jenispiutang',
            'urut',
        ], $queryrekapdata);

        $temprekaphasil = '##temprekaphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekaphasil, function ($table) {
            $table->id();
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
        });

        $queryrekaphasil = db::table($temprekapdata)->from(db::raw($temprekapdata . " a  "))
            ->select(
                'b.namaagen as agen_id',
                'a.nobukti',
                db::raw("(case when year(a.tglbukti)=1900 then null else a.tglbukti end ) as tglbukti"),
                'a.nominalpiutang',
                db::raw("(case when year(a.tgllunas)=1900 then null else a.tgllunas end ) as tgllunas"),
                'a.nominallunas',
                'a.nobuktipiutang',
                'a.tglberjalan',
                db::raw("SUM(a.nominalpiutang-a.nominallunas) OVER (PARTITION BY a.jenispiutang,b.namaagen ORDER BY a.tglberjalan,a.nobuktipiutang,a.urut ASC) as saldo"),
                db::raw("a.nominalpiutang-a.nominallunas  as saldobayar"),
                'a.jenispiutang',
                'a.urut'
            )
            ->leftjoin(db::raw("agen b with (readuncommitted) "), 'a.agen_id', 'b.id')

            ->orderby('b.namaagen', 'asc')
            ->orderby('a.jenispiutang', 'asc')
            ->orderby('a.tglberjalan', 'asc')
            ->orderby('a.nobuktipiutang', 'asc')
            ->orderby('a.urut', 'asc');


        DB::table($temprekaphasil)->insertUsing([
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
        ], $queryrekaphasil);

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $queryurut = db::table($temprekaphasil)->from(db::raw($temprekaphasil . " a"))
            ->select(
                'a.id',
                'a.agen_id',
                'a.jenispiutang',
                'a.nobukti',
                'a.nobuktipiutang',
            )
            ->orderby('a.id')
            ->get();
        $datadetail = json_decode($queryurut, true);
        $xuji = '';
        $xnobukti = '';
        $xnobuktipiutang = '';

        DB::update(DB::raw("UPDATE " . $temprekaphasil . " SET urut=0"));
        $urut = 0;
        foreach ($datadetail as $item) {
            $xuji2 = $item['agen_id'] . $item['jenispiutang'];

            if ($xuji2 != $xuji) {
                $urut = 0;
            }
            if ($item['nobukti'] == $item['nobuktipiutang']) {
                $urut = $urut + 1;
                DB::update(DB::raw("UPDATE " . $temprekaphasil . " SET urut=" . $urut . " where id=" . $item['id']));
            }


            $xuji = $item['agen_id'] . $item['jenispiutang'];
        }


        $select_data = DB::table($temprekaphasil . ' AS A')
            ->select([
                'a.id',
                'a.agen_id',
                'a.nobukti',
                'a.tglbukti',
                'a.nominalpiutang',
                'a.tgllunas',
                'a.nominallunas',
                'a.nobuktipiutang',
                'a.tglberjalan',
                'a.saldo',
                'a.saldobayar',
                'a.jenispiutang',
                'a.urut',
                DB::raw("'$getJudul->text' AS text"),
                DB::raw("(case when '$keteranganagen'='' then '$agendarinama' else '$keteranganagen' end)  AS dari"),
                DB::raw("(case when '$keteranganagen'='' then '$agensampainama' else '$keteranganagen' end)   AS sampai"),
                DB::raw("'Laporan Kartu Piutang Per Customer' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            ])

            ->orderBy('a.id', 'asc');

        // dd($select_data->get());

        if ($prosesneraca == 1) {
            $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE UTAMA')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($param->memo, true);
            $coapiutangusaha = $memo['JURNAL'];

            $select_data->join(db::raw("piutangheader b with (readuncommitted)"), 'a.nobuktipiutang', 'b.nobukti');
            $select_data->where('b.coadebet', $coapiutangusaha);

            // dd($select_data->get());
            $data = $select_data;
        } else {
            $data = $select_data->get();
        }

        return $data;
    }





    public function getReportOld($dari, $sampai, $agenDari, $agenSampai, $prosesneraca)
    {

        $prosesneraca = $prosesneraca ?? 0;

        $sampai = $dari;
        $tgl = '01-' . date('m', strtotime($dari)) . '-' . date('Y', strtotime($dari));
        $dari1 = date('Y-m-d', strtotime($tgl));
        if ($agenDari == 0) {
            $agenDari = db::table('agen')->from(db::raw("agen with (readuncommitted)"))
                ->select('id')->orderby('id', 'asc')->first()->id ?? 0;
        }

        if ($agenSampai == 0) {
            $agenSampai = db::table('agen')->from(db::raw("agen with (readuncommitted)"))
                ->select('id')->orderby('id', 'desc')->first()->id ?? 0;
        }

        if ($agenDari > $agenSampai) {
            $agenDari1 = $agenSampai;
            $agenSampai1 = $agenDari;
            $agenDari = $agenDari1;
            $agenSampai = $agenSampai1;
        }

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $tempKartuPiutang = '##tempKartuPiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempKartuPiutang, function ($table) {
            $table->bigIncrements('id');
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->double('nominal')->nullable();
            $table->date('tgllunas')->nullable();
            $table->double('bayar')->nullable();
            $table->integer('agen_id');
            $table->string('group', 50);
        });


        // START TEMPPIUTANG
        $Temppiutang = '##Temppiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutang, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
            $table->integer('agen_id');
            $table->string('group', 50);
        });
        $select_Temppiutang = DB::table('piutangheader')->from(DB::raw("piutangheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                DB::raw('SUM(B.nominal) as nominal'),
                DB::raw('MAX(A.agen_id) as agen_id'),
                'A.nobukti as group',
            ])
            ->join('piutangdetail AS B', 'A.nobukti', '=', 'B.NoBukti')
            ->where('A.agen_id', '>=', $agenDari)
            ->where('A.agen_id', '<=', $agenSampai)
            ->groupBy('A.nobukti');


        DB::table($Temppiutang)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal',
            'agen_id',
            'group',
        ], $select_Temppiutang);

        DB::table($tempKartuPiutang)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal',
            'agen_id',
            'group'
        ], $select_Temppiutang);
        // END TEMPPIUTANG

        // START TEMPPIUTANG BAYAR
        $Temppiutangbyr = '##Temppiutangbyr' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyr, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangbyr = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader as A with (readuncommitted)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                'B.piutang_nobukti',
                DB::raw('SUM(B.nominal) as nominal')
            ])
            ->join(DB::raw("pelunasanPiutangdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw($Temppiutang . " AS C with (readuncommitted)"), 'B.piutang_nobukti', 'C.nobukti')
            ->groupBy('A.nobukti', 'B.piutang_nobukti');

        DB::table($Temppiutangbyr)->insertUsing([
            'tglbukti',
            'nobukti',
            'piutang_nobukti',
            'nominal',
        ], $select_Temppiutangbyr);
        //   dd($select_Temppiutangbyr->get());
        // END TEMPPIUTANG BAYAR

        //NOTE - Temppiutangsaldo
        $Temppiutangsaldo = '##Temppiutangsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangsaldo = DB::table($Temppiutang)->from(DB::raw($Temppiutang . " AS A"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.nominal'
            ])
            ->where('A.tglbukti', '<', $dari1);

        DB::table($Temppiutangsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal'
        ], $select_Temppiutangsaldo);
        // dd($select_Temppiutangsaldo->get());




        //NOTE - Temppiutangbyrsaldo
        $Temppiutangbyrsaldo = '##Temppiutangbyrsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyrsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangbyrsaldo = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS a"))
            ->select([
                DB::raw('MAX(a.tglbukti) as tglbukti'),
                DB::raw('MAX(a.nobukti) as nobukti'),
                'a.piutang_nobukti',
                DB::raw('SUM(a.nominal) as nominal'),

            ])

            ->where('a.tglbukti', '<', $dari1)
            ->groupBy('a.piutang_nobukti');

        DB::table($Temppiutangbyrsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'piutang_nobukti',
            'nominal'
        ], $select_Temppiutangbyrsaldo);
        //datanya tidak ada
        // dd($select_Temppiutangbyrsaldo->get());

        //NOTE - TemppiutangbyrsaldoCicil
        // $TemppiutangbyrsaldoCicil = '##TemppiutangbyrsaldoCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($TemppiutangbyrsaldoCicil, function ($table) {
        //     $table->datetime('tglbukti');
        //     $table->string('nobukti', 100);
        //     $table->string('piutang_nobukti', 100);
        //     $table->double('nominal');
        //     $table->integer('urut');
        // });

        // $select_TemppiutangbyrsaldoCicil = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS A"))
        //     ->select([
        //         'A.tglbukti as tglbukti',
        //         'A.nobukti',
        //         'A.piutang_nobukti',
        //         'A.nominal as nominal',
        //         DB::raw('ROW_NUMBER() OVER (PARTITION BY A.piutang_nobukti ORDER BY A.tglbukti) as urut')
        //     ])
        //     ->where('A.tglbukti', '<', $dari1);
        //datanya tidak ada
        // dd("ASdas");
        // dd($select_TemppiutangbyrsaldoCicil->get());

        $Temppiutangberjalan = '##Temppiutangberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangberjalan, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        $select_Temppiutangberjalan = DB::table($Temppiutang)->from(DB::raw($Temppiutang . " AS A"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.nominal',
            ])
            ->where('A.tglbukti', '>', $dari1)
            ->where('A.tglbukti', '<=', $sampai);


        DB::table($Temppiutangberjalan)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal'
        ], $select_Temppiutangberjalan);

        $Temppiutangbyrberjalan = '##Temppiutangbyrberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyrberjalan, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangbyrberjalan = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS A"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                DB::raw('MAX(A.nobukti) as nobukti'),
                'A.piutang_nobukti',
                DB::raw('SUM(A.nominal) as nominal')
            ])
            ->where('A.tglbukti', '>', $dari1)
            ->where('A.tglbukti', '<=', $sampai)
            ->groupBy('A.piutang_nobukti');

        DB::table($Temppiutangbyrberjalan)->insertUsing([
            'tglbukti',
            'nobukti',
            'piutang_nobukti',
            'nominal',
        ], $select_Temppiutangbyrberjalan);

        // $TemppiutangbyrberjalanCicil = '##TemppiutangbyrberjalanCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($TemppiutangbyrberjalanCicil, function ($table) {
        //     $table->datetime('tglbukti');
        //     $table->string('nobukti', 100);
        //     $table->string('piutang_nobukti', 100);
        //     $table->double('nominal');
        //     $table->integer('urut');
        // });

        // $select_TemppiutangbyrberjalanCicil = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS A"))
        //     ->select([
        //         DB::raw('A.tglbukti as tglbukti'),
        //         DB::raw('A.nobukti as nobukti'),
        //         'A.piutang_nobukti',
        //         DB::raw('A.nominal as nominal'),
        //         DB::raw("row_number() Over(partition BY A.piutang_nobukti Order By A.tglbukti) as urut")
        //     ])
        //     ->where('A.tglbukti', '>', $dari1)
        //     ->where('A.tglbukti', '<=', $sampai);

        // DB::table($TemppiutangbyrberjalanCicil)->insertUsing([
        //     'tglbukti',
        //     'nobukti',
        //     'piutang_nobukti',
        //     'nominal',
        //     'urut',
        // ], $select_TemppiutangbyrberjalanCicil);

        // $TempCicil = '##TempCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($TempCicil, function ($table) {
        //     $table->string('piutang_nobukti', 50);
        //     $table->integer('urut');
        // });

        // $select_TempCicil = DB::table($TemppiutangbyrsaldoCicil)->from(DB::raw($TemppiutangbyrsaldoCicil))
        //     ->select([
        //         'piutang_nobukti',
        //         DB::raw('MAX(urut) as urut'),
        //     ])
        //     ->groupBy('piutang_nobukti');

        // DB::table($TempCicil)->insertUsing([
        //     'piutang_nobukti',
        //     'urut',
        // ], $select_TempCicil);

        // $select_TempCicil2 = DB::table($TemppiutangbyrberjalanCicil)->from(DB::raw($TemppiutangbyrberjalanCicil))
        //     ->select([
        //         'piutang_nobukti',
        //         DB::raw('MAX(urut) as urut'),
        //     ])
        //     ->groupBy('piutang_nobukti');

        // DB::table($TempCicil)->insertUsing([
        //     'piutang_nobukti',
        //     'urut',
        // ], $select_TempCicil2);
        // // dd($select_TempCicil->get());


        // $TempCicilRekap = '##TempCicilRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($TempCicilRekap, function ($table) {
        //     $table->string('piutang_nobukti', 50);
        //     $table->integer('urut');
        // });

        // $select_TempCicilRekap = DB::table($TempCicil)->from(DB::raw($TempCicil))
        //     ->select([
        //         'piutang_nobukti',
        //         DB::raw('SUM(urut) as urut'),
        //     ])
        //     ->groupBy('piutang_nobukti');

        // DB::table($TempCicilRekap)->insertUsing([
        //     'piutang_nobukti',
        //     'urut',
        // ], $select_TempCicilRekap);
        // //    dd($select_TempCicilRekap->get());


        // $TempRekappiutang = '##TempRekappiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($TempRekappiutang, function ($table) {
        //     $table->bigIncrements('id');
        //     $table->string('nobukti', 50);
        //     $table->double('nominal');
        //     $table->double('bayar');
        // });



        // $select_TempRekappiutang = DB::table($Temppiutangsaldo . ' AS A')
        //     ->select([
        //         'A.nobukti',
        //         DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0)) as saldo"),
        //         DB::raw("ISNULL(C.nominal, 0) as bayar")
        //     ])
        //     ->leftJoin($Temppiutangbyrsaldo . ' AS B', 'A.nobukti', '=', 'B.piutang_nobukti')
        //     ->leftJoin($Temppiutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.piutang_nobukti')
        //     ->join(DB::raw("piutangheader AS D with (readuncommitted)"), 'A.nobukti', '=', 'D.nobukti')
        //     ->where(DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0))"), '<>', 0)
        //     ->orderBy('D.agen_id')
        //     ->orderBy('D.tglbukti')
        //     ->orderBy('A.nobukti');

        // DB::table($TempRekappiutang)->insertUsing([
        //     'nobukti',
        //     'nominal',
        //     'bayar',
        // ], $select_TempRekappiutang);

        // dd($select_TempRekappiutang->get());
        $select_Pelunasan = DB::table($Temppiutangsaldo . ' AS A')
            ->select([
                'B.nobukti',
                'B.tglbukti as tgllunas',
                DB::raw("ISNULL(B.nominal, 0) as bayar"),
                'D.agen_id',
                'A.nobukti as group',
            ])
            ->leftJoin($Temppiutangbyrsaldo . ' AS B', 'A.nobukti', '=', 'B.piutang_nobukti')
            ->leftJoin($Temppiutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.piutang_nobukti')
            ->join(DB::raw("piutangheader AS D with (readuncommitted)"), 'A.nobukti', '=', 'D.nobukti')
            // ->where(DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0))"), '<>', 0)
            ->where(DB::raw("ISNULL(B.nominal, 0)"), '<>', 0)
            ->orderBy('D.agen_id')
            ->orderBy('D.tglbukti')
            ->orderBy('A.nobukti');
        // return $select_Pelunasan->get();
        DB::table($tempKartuPiutang)->insertUsing([
            'nobukti',
            'tgllunas',
            'bayar',
            'agen_id',
            'group'
        ], $select_Pelunasan);
        // END PELUNASAN TO REKAP

        // START POTONGAN TO REKAP
        $select_tempNotaPiutang = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader as A with (readuncommitted)"))
            ->select([
                'A.notakredit_nobukti as nobukti',
                DB::raw('MAX(A.tglbukti) as tgllunas'),
                DB::raw('SUM(B.potongan) as bayar'),
                DB::raw('MAX(A.agen_id) as agen_id'),
                'B.piutang_nobukti as group',
            ])
            ->join(DB::raw("pelunasanPiutangdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw($Temppiutang . " AS C with (readuncommitted)"), 'B.piutang_nobukti', 'C.nobukti')
            ->where(DB::raw("ISNULL(B.potongan, 0)"), '<>', 0)
            ->groupBy('A.notakredit_nobukti', 'B.piutang_nobukti');
        DB::table($tempKartuPiutang)->insertUsing([
            'nobukti',
            'tgllunas',
            'bayar',
            'agen_id',
            'group'
        ], $select_tempNotaPiutang);

        // LAPORAN REKAP
        $select_data = DB::table($tempKartuPiutang . ' AS A')
            ->select([
                'D.namaagen',
                'A.nobukti',
                'a.tglbukti',
                // // DB::raw("ISNULL(A.tglbukti, '')"),
                // Db::raw("(case when isnull(A.tglbukti,'')='' then '' else C.tglbukti end) as tglbukti"),                         
                'A.tgllunas',
                'A.nominal',
                'A.bayar',
                'A.group',
                DB::raw('SUM((ISNULL(A.nominal, 0) - ISNULL(A.bayar, 0))) OVER (PARTITION BY D.namaagen ORDER BY A.nobukti ASC) as Saldo'),
                DB::raw("'$getJudul->text' AS text"),
                DB::raw("'$dari' AS dari"),
                DB::raw("'$sampai' AS sampai"),
                DB::raw("'Laporan Kartu Piutang Per Agen' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
            ])
            ->join('piutangheader AS C', 'A.group', '=', 'C.nobukti')
            ->join(DB::raw("agen AS D with (readuncommitted)"), 'A.agen_id', '=', 'D.id')


            ->orderBy('D.namaagen')
            ->orderBy('C.tglbukti')
            ->orderBy('A.nobukti');
        // ->orderBy('A.tglbukti');

        // END LAPORAN REKAP
        // END POTONGAN TO REKAP
        // $select_TempRekappiutang2 = DB::table($Temppiutangberjalan . ' AS A')
        //     ->select([
        //         'A.nobukti',
        //         'A.nominal',
        //         DB::raw('ISNULL(C.nominal, 0) as bayar')
        //     ])

        //     ->leftJoin($Temppiutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.piutang_nobukti');

        // // dd($select_TempRekappiutang2->get());

        // DB::table($TempRekappiutang)->insertUsing([
        //     'nobukti',
        //     'nominal',
        //     'bayar',
        // ], $select_TempRekappiutang2);

        // $Tempketerangan = '##Tempketerangan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($Tempketerangan, function ($table) {
        //     $table->string('nobukti', 50);
        //     $table->LongText('keterangan');
        // });

        // $select_Tempketerangan = DB::table($TempRekappiutang . ' AS A')
        //     ->select([
        //         'A.nobukti',
        //         DB::raw('MAX(B.keterangan) as keterangan'),

        //     ])
        //     ->join('piutangdetail as b', 'A.nobukti', 'b.nobukti')
        //     ->groupBy('A.nobukti');


        // DB::table($Tempketerangan)->insertUsing([
        //     'nobukti',
        //     'keterangan'
        // ], $select_Tempketerangan);

        // // dd($select_TempRekappiutang2->get());

        // $select_data = DB::table($TempRekappiutang . ' AS A')
        //     ->select([
        //         'D.namaagen',
        //         db::raw("(case when isnull(C.keterangan,'')='' then isnull(e.keterangan,'') else isnull(C.keterangan,'') end) as keterangan"),
        //         'A.nobukti',
        //         'C.tglbukti',
        //         DB::raw("dateadd(d,isnull(d.[top],0),c.tglbukti) as tgljatuhtempo"),
        //         DB::raw('ISNULL(B.urut, 0) + 1 as cicil'),
        //         'A.nominal',
        //         'A.bayar',
        //         DB::raw('SUM((ISNULL(A.nominal, 0) - A.bayar)) OVER (PARTITION BY D.namaagen ORDER BY A.id ASC) as Saldo'),
        //         DB::raw("'$getJudul->text' AS text"),
        //         DB::raw("'$dari' AS dari"),
        //         DB::raw("'$sampai' AS sampai"),
        //         DB::raw("'Laporan Kartu Piutang Per Agen' as judulLaporan"),
        //         DB::raw("'" . $getJudul->text . "' as judul"),
        //         DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
        //         DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
        //         db::raw("'" . $disetujui . "' as disetujui"),
        //         db::raw("'" . $diperiksa . "' as diperiksa"),
        //     ])
        //     ->leftJoin($TempCicilRekap . ' AS B', 'A.nobukti', '=', 'B.piutang_nobukti')
        //     ->join(DB::raw("piutangheader AS C with (readuncommitted)"), 'A.nobukti', '=', 'C.nobukti')
        //     ->join(DB::raw("agen AS D with (readuncommitted)"), 'C.agen_id', '=', 'D.id')
        //     ->leftJoin($Tempketerangan . ' AS e', 'e.nobukti', '=', 'a.nobukti')


        //     ->orderBy('D.namaagen')
        //     ->orderBy('C.tglbukti')
        //     ->orderBy('C.nobukti');
        // dd($select_data->get());
        // $data = $select_data->get();

        if ($prosesneraca == 1) {
            $data = $select_data;
        } else {
            $data = $select_data->get();
        }
        return $data;
    }





    public function getExport($dari, $sampai, $agenDari, $agenSampai)
    {

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $Temppiutang = '##Temppiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutang, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });

        $select_Temppiutang = DB::table('piutangheader')->from(DB::raw("piutangheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                DB::raw('SUM(B.nominal) as nominal')
            ])
            ->join('piutangdetail AS B', 'A.nobukti', '=', 'B.NoBukti')
            ->where('A.agen_id', '>=', $agenDari)
            ->where('A.agen_id', '<=', $agenSampai)
            ->groupBy('A.nobukti');

        DB::table($Temppiutang)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal',
        ], $select_Temppiutang);
        // dd($select_Temppiutang->get());



        $Temppiutangbyr = '##Temppiutangbyr' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyr, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangbyr = DB::table('pelunasanPiutangheader')->from(DB::raw("pelunasanPiutangheader as A with (readuncommitted)"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                'A.nobukti',
                'B.piutang_nobukti',
                DB::raw('SUM(B.nominal+potongan) as nominal')
            ])
            ->join(DB::raw("pelunasanPiutangdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw($Temppiutang . " AS C with (readuncommitted)"), 'B.piutang_nobukti', 'C.nobukti')
            ->groupBy('A.nobukti', 'B.piutang_nobukti');


        DB::table($Temppiutangbyr)->insertUsing([
            'tglbukti',
            'nobukti',
            'piutang_nobukti',
            'nominal',
        ], $select_Temppiutangbyr);
        //   dd($select_Temppiutangbyr->get());


        //NOTE - Temppiutangsaldo
        $Temppiutangsaldo = '##Temppiutangsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangsaldo = DB::table($Temppiutang)->from(DB::raw($Temppiutang . " AS a"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.nominal'
            ])
            ->where('A.tglbukti', '>', $dari);

        DB::table($Temppiutangsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal'
        ], $select_Temppiutangsaldo);
        // dd($select_Temppiutangsaldo->get());




        //NOTE - Temphutangbyrsaldo
        $Temppiutangbyrsaldo = '##Temppiutangbyrsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyrsaldo, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        $select_Temppiutangbyrsaldo = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS a"))
            ->select([
                DB::raw('MAX(a.tglbukti) as tglbukti'),
                DB::raw('MAX(a.nobukti) as nobukti'),
                'a.piutang_nobukti',
                DB::raw('SUM(a.nominal) as nominal'),

            ])

            ->where('a.tglbukti', '<', $dari)
            ->groupBy('a.piutang_nobukti');

        DB::table($Temppiutangbyrsaldo)->insertUsing([
            'tglbukti',
            'nobukti',
            'piutang_nobukti',
            'nominal'
        ], $select_Temppiutangbyrsaldo);
        //datanya tidak ada
        // dd($select_Temppiutangbyrsaldo->get());

        //NOTE - TemppiutangbyrsaldoCicil
        $TemppiutangbyrsaldoCicil = '##TemppiutangbyrsaldoCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemppiutangbyrsaldoCicil, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
            $table->integer('urut');
        });

        $select_TemppiutangbyrsaldoCicil = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS a"))
            ->select([
                'A.tglbukti as tglbukti',
                'A.nobukti',
                'A.piutang_nobukti',
                'A.nominal as nominal',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY A.piutang_nobukti ORDER BY A.tglbukti) as urut')
            ])
            ->where('A.tglbukti', '<', $dari);
        //datanya tidak ada
        // dd("ASdas");
        // dd($select_TemppiutangbyrsaldoCicil->get());

        $Temppiutangberjalan = '##Temppiutangberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangberjalan, function ($table) {
            $table->datetime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->double('nominal')->nullable();
        });


        $Temppiutangbyrberjalan = '##Temppiutangbyrberjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppiutangbyrberjalan, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
        });

        $TemppiutangbyrberjalanCicil = '##TemppiutangbyrberjalanCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TemppiutangbyrberjalanCicil, function ($table) {
            $table->datetime('tglbukti');
            $table->string('nobukti', 100);
            $table->string('piutang_nobukti', 100);
            $table->double('nominal');
            $table->integer('urut');
        });

        $select_Temppiutangberjalan = DB::table($Temppiutang)->from(DB::raw($Temppiutang . " AS a"))
            ->select([
                'A.tglbukti',
                'A.nobukti',
                'A.nominal',
            ])
            ->where('A.tglbukti', '>', $dari)
            ->where('A.tglbukti', '<=', $sampai);

        DB::table($Temppiutangberjalan)->insertUsing([
            'tglbukti',
            'nobukti',
            'nominal'
        ], $select_Temppiutangberjalan);
        // dd($select_Temppiutangberjalan->get());

        $select_Temppiutangbyrberjalan = DB::table($Temppiutangbyr)->from(DB::raw($Temppiutangbyr . " AS a"))
            ->select([
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                DB::raw('MAX(A.nobukti) as nobukti'),
                'A.piutang_nobukti',
                DB::raw('SUM(A.nominal) as nominal')
            ])
            ->where('A.tglbukti', '>', $dari)
            ->where('A.tglbukti', '<=', $sampai)
            ->groupBy('A.piutang_nobukti');

        DB::table($Temppiutangbyrberjalan)->insertUsing([
            'tglbukti',
            'nobukti',
            'piutang_nobukti',
            'nominal',
        ], $select_Temppiutangbyrberjalan);
        // dd($select_Temppiutangbyrberjalan->get());


        $TempCicil = '##TempCicil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempCicil, function ($table) {
            $table->string('piutang_nobukti', 50);
            $table->integer('urut');
        });

        $select_TempCicil = DB::table($TemppiutangbyrsaldoCicil)->from(DB::raw($TemppiutangbyrsaldoCicil))
            ->select([
                'piutang_nobukti',
                DB::raw('MAX(urut) as urut'),
            ])
            ->groupBy('piutang_nobukti');

        DB::table($TempCicil)->insertUsing([
            'piutang_nobukti',
            'urut',
        ], $select_TempCicil);
        // dd($select_TempCicil->get());

        $TempCicilRekap = '##TempCicilRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempCicilRekap, function ($table) {
            $table->string('piutang_nobukti', 50);
            $table->integer('urut');
        });

        $select_TempCicilRekap = DB::table($TempCicil)->from(DB::raw($TempCicil))
            ->select([
                'piutang_nobukti',
                DB::raw('SUM(urut) as urut'),
            ])
            ->groupBy('piutang_nobukti');

        DB::table($TempCicilRekap)->insertUsing([
            'piutang_nobukti',
            'urut',
        ], $select_TempCicilRekap);
        //    dd($select_TempCicilRekap->get());


        $TempRekappiutang = '##TempRekappiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempRekappiutang, function ($table) {
            $table->bigIncrements('id');
            $table->string('nobukti', 50);
            $table->double('nominal');
            $table->double('bayar');
        });



        $select_TempRekappiutang = DB::table($Temppiutangsaldo . ' AS A')
            ->select([
                'A.nobukti',
                DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0)) as saldo"),
                DB::raw("ISNULL(C.nominal, 0) as bayar")
            ])
            ->leftJoin($Temppiutangbyrsaldo . ' AS B', 'A.nobukti', '=', 'B.piutang_nobukti')
            ->leftJoin($Temppiutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.piutang_nobukti')
            ->join('piutangheader AS D', 'A.nobukti', '=', 'D.nobukti')
            ->where(DB::raw("(ISNULL(A.nominal, 0) - ISNULL(B.nominal, 0))"), '<>', 0)
            ->orderBy('D.agen_id')
            ->orderBy('A.nobukti');
        // dd($select_TempRekappiutang->get()); sampai sini datanya ada


        DB::table($TempRekappiutang)->insertUsing([
            'nobukti',
            'nominal',
            'bayar',
        ], $select_TempRekappiutang);

        // dd($select_TempRekappiutang->get());

        $select_TempRekappiutang2 = DB::table($Temppiutangberjalan . ' AS A')
            ->select([
                'A.nobukti',
                'A.nominal',
                DB::raw('ISNULL(C.nominal, 0) as bayar')
            ])

            ->leftJoin($Temppiutangbyrberjalan . ' AS C', 'A.nobukti', '=', 'C.piutang_nobukti');

        // dd($select_TempRekappiutang2->get());

        DB::table($TempRekappiutang)->insertUsing([
            'nobukti',
            'nominal',
            'bayar',
        ], $select_TempRekappiutang2);
        // dd($select_TempRekappiutang2->get());

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $select_data = DB::table($TempRekappiutang . ' AS A')
            ->select([
                'D.namaagen',
                'C.keterangan',
                'A.nobukti',
                'C.tglbukti',
                'C.tgljatuhtempo',
                DB::raw('ISNULL(B.urut, 0) + 1 as cicil'),
                'A.nominal',
                'A.bayar',
                DB::raw('SUM((ISNULL(A.nominal, 0) - A.bayar)) OVER (PARTITION BY D.namaagen ORDER BY A.id ASC) as Saldo'),
                DB::raw("'$getJudul->text' AS text"),
                DB::raw("'$dari' AS dari"),
                DB::raw("'$sampai' AS sampai"),
                DB::raw("'Laporan Kartu Piutang Per Agen' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
            ])
            ->leftJoin($TempCicilRekap . ' AS B', 'A.nobukti', '=', 'B.piutang_nobukti')
            ->join('piutangheader AS C', 'A.nobukti', '=', 'C.nobukti')
            ->join('agen AS D', 'C.agen_id', '=', 'D.id')
            ->orderBy('D.namaagen')
            ->orderBy('C.tglbukti')
            ->orderBy('C.nobukti');
        // dd($select_data->get());
        $data = $select_data->get();
        return $data;
    }
}
