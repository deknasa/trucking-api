<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class LaporanKartuHutangPrediksi extends MyModel
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



    public function getReport($sampai, $dari)
    {
        $ptglawalprogram = '2023/5/1';

        $dari = '01-' . date('m', strtotime($sampai)) . '-' . date('Y', strtotime($sampai));
        $templist = '##templist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($templist, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('nobuktitrans', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->double('debet')->nullable();
            $table->double('kredit')->nullable();
            $table->double('saldo')->nullable();
        });

        $templistbukti = '##templistbukti' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($templistbukti, function ($table) {
            $table->string('nobukti', 1000)->nullable();
            $table->string('nobuktipengeluaran', 1000)->nullable();
        });


        $querylistbukti = DB::table('prosesgajisupirheader')->from(
            DB::raw("prosesgajisupirheader a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti as nobuktipengeluaran',
            );

        DB::table($templistbukti)->insertUsing([
            'nobukti',
            'nobuktipengeluaran',
        ], $querylistbukti);

        $querylistbukti = DB::table('prosesgajisupirheader')->from(
            DB::raw("prosesgajisupirheader a with (readuncommitted)")
        )
            ->select(
                'a.pengeluaran_nobukti as nobukti',
                db::raw("'' as nobuktipengeluaran")
            )
            ->WhereRaw("isnull(a.pengeluaran_nobukti,'')<>''");

        DB::table($templistbukti)->insertUsing([
            'nobukti',
            'nobuktipengeluaran',
        ], $querylistbukti);

        $querylistbukti = DB::table('hutangheader')->from(
            DB::raw("hutangheader a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                db::raw("'' as nobuktipengeluaran")
            );

        DB::table($templistbukti)->insertUsing([
            'nobukti',
            'nobuktipengeluaran',
        ], $querylistbukti);

        $querylistbukti = DB::table('pelunasanhutangheader')->from(
            DB::raw("pelunasanhutangheader a with (readuncommitted)")
        )
            ->select(
                'a.pengeluaran_nobukti as nobukti',
                db::raw("'' as nobuktipengeluaran")
                )
            ->WhereRaw("isnull(a.pengeluaran_nobukti,'')<>''");

        DB::table($templistbukti)->insertUsing([
            'nobukti',
            'nobuktipengeluaran',
        ], $querylistbukti);

        // 1
        $querytemplist = DB::table('saldohutangprediksi')
            ->from(DB::raw("saldohutangprediksi as a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                DB::raw("format(getdate(),'yyyy/MM/dd') as tglbuktitglbukti"),
                DB::raw("'' as nobuktitrans"),
                'a.keterangan',
                'a.nominal as debet',
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo")
            )
            ->OrderBy('a.id');

        DB::table($templist)->insertUsing([
            'nobukti',
            'tglbukti',
            'nobuktitrans',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querytemplist);

        // 2
        $querytemplist = DB::table('prosesgajisupirheader')
            ->from(DB::raw("prosesgajisupirheader as h with (readuncommitted)"))
            ->select(
                'h.nobukti',
                DB::raw("max(h.tglbukti) as tglbukti"),
                'h.nobukti as nobuktitrans',
                DB::raw("max(h.keterangan) as keterangan"),
                DB::raw("sum(-1 * j.nominal) AS debet"),
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo")
            )
            ->Join(DB::raw("jurnalumumdetail AS j with (readuncommitted)"), 'h.nobukti', '=', 'j.nobukti')
            ->whereRaw("j.tglbukti < '" . date('Y/m/d', strtotime($dari)) . "'")
            ->whereRaw("j.tglbukti>='" . date('Y/m/d', strtotime($ptglawalprogram)) . "'")
            ->whereRaw("j.coa='03.02.02.04'")
            ->GroupBy("h.nobukti");


        DB::table($templist)->insertUsing([
            'nobukti',
            'tglbukti',
            'nobuktitrans',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querytemplist);



        //3
        $querytemplist = DB::table('prosesgajisupirheader')
            ->from(DB::raw("prosesgajisupirheader as h with (readuncommitted)"))
            ->select(
                'h.nobukti',
                DB::raw("max(h.tglbukti) as tglbukti"),
                'h.pengeluaran_nobukti as nobuktitrans',
                DB::raw("max(h.keterangan) as keterangan"),
                DB::raw("sum(-1 * j.nominal) AS debet"),
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo")
            )
            ->Join(DB::raw("jurnalumumdetail AS j with (readuncommitted)"), 'h.pengeluaran_nobukti', '=', 'j.nobukti')
            ->whereRaw("j.tglbukti < '" . date('Y/m/d', strtotime($dari)) . "'")
            ->whereRaw("j.tglbukti>='" . date('Y/m/d', strtotime($ptglawalprogram)) . "'")
            ->whereRaw("isnull(h.pengeluaran_nobukti,'')<>''")
            ->whereRaw("j.coa='03.02.02.04'")
            ->GroupBy("h.nobukti")
            ->GroupBy("h.pengeluaran_nobukti");


        DB::table($templist)->insertUsing([
            'nobukti',
            'tglbukti',
            'nobuktitrans',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querytemplist);

        // 4
        $querytemplist = DB::table('hutangheader')
            ->from(DB::raw("hutangheader as d with (readuncommitted)"))
            ->select(
                'd.nobukti',
                DB::raw("max(j.tglbukti) as tglbukti"),
                'd.nobukti as nobuktitrans',
                DB::raw("max(j.keterangan) as keterangan"),
                DB::raw("sum(-1 * (j.nominal)) AS debet"),
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo")
            )
            ->Join(DB::raw("jurnalumumdetail AS j with (readuncommitted)"), 'd.nobukti', '=', 'j.nobukti')
            ->whereRaw("j.tglbukti < '" . date('Y/m/d', strtotime($dari)) . "'")
            ->whereRaw("j.tglbukti>='" . date('Y/m/d', strtotime($ptglawalprogram)) . "'")
            ->whereRaw("j.coa='03.02.02.04'")
            ->GroupBy("d.nobukti");


        DB::table($templist)->insertUsing([
            'nobukti',
            'tglbukti',
            'nobuktitrans',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querytemplist);

        // 5
        $querytemplist = DB::table('pelunasanhutangheader')
            ->from(DB::raw("pelunasanhutangheader as d with (readuncommitted)"))
            ->select(
                'd.nobukti',
                DB::raw("max(j.tglbukti) as tglbukti"),
                'd.pengeluaran_nobukti as nobuktitrans',
                DB::raw("max(j.keterangan) as keterangan"),
                DB::raw("sum(-1 * (j.nominal)) AS debet"),
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo")
            )
            ->Join(DB::raw("jurnalumumdetail AS j with (readuncommitted)"), 'j.nobukti', '=', 'd.pengeluaran_nobukti')
            ->whereRaw("j.tglbukti < '" . date('Y/m/d', strtotime($dari)) . "'")
            ->whereRaw("j.tglbukti>='" . date('Y/m/d', strtotime($ptglawalprogram)) . "'")
            ->whereRaw("isnull(d.pengeluaran_nobukti,'')<>''")
            ->whereRaw("j.coa='03.02.02.04'")
            ->GroupBy("d.nobukti")
            ->GroupBy("d.pengeluaran_nobukti");


        DB::table($templist)->insertUsing([
            'nobukti',
            'tglbukti',
            'nobuktitrans',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querytemplist);

        // 6
        $querytemplist = DB::table('jurnalumumheader')
            ->from(DB::raw("jurnalumumheader as d with (readuncommitted)"))
            ->select(
                'j.nobukti',
                DB::raw("max(j.tglbukti) as tglbukti"),
                'j.nobukti as nobuktitrans',
                DB::raw("max(j.keterangan) as keterangan"),
                DB::raw("sum(-1 * (j.nominal)) AS debet"),
                DB::raw("0 as kredit"),
                DB::raw("0 as saldo")
            )
            ->Join(DB::raw("jurnalumumdetail AS j with (readuncommitted)"), 'd.nobukti', '=', 'j.nobukti')
            ->leftJoin(DB::raw($templistbukti . " AS c with (readuncommitted)"), 'd.nobukti', '=', 'c.nobukti')
            ->whereRaw("j.tglbukti < '" . date('Y/m/d', strtotime($dari)) . "'")
            ->whereRaw("j.tglbukti>='" . date('Y/m/d', strtotime($ptglawalprogram)) . "'")
            ->whereRaw("isnull(c.nobukti,'')=''")
            ->whereRaw("j.coa='03.02.02.04'")
            ->GroupBy("j.nobukti");


        DB::table($templist)->insertUsing([
            'nobukti',
            'tglbukti',
            'nobuktitrans',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querytemplist);

        // 7
        $querytemplist = DB::table('prosesgajisupirheader')
            ->from(DB::raw("prosesgajisupirheader as h with (readuncommitted)"))
            ->select(
                'h.nobukti',
                'h.tglbukti  AS tglbukti',
                'h.nobukti AS nobuktitrans',
                'h.keterangan',
                DB::raw("-1 * j.nominal AS debet"),
                DB::raw("0 AS kredit"),
                DB::raw("0 as saldo")
            )
            ->Join(DB::raw("jurnalumumdetail AS j with (readuncommitted)"), 'h.nobukti', '=', 'j.nobukti')
            ->whereRaw("j.tglbukti BETWEEN '" . date('Y/m/d', strtotime($dari)) . "' and '" . date('Y/m/d', strtotime($sampai)) . "'")
            ->whereRaw("j.tglbukti>='" . date('Y/m/d', strtotime($ptglawalprogram)) . "'")
            ->whereRaw("j.coa='03.02.02.04'")
            ->orderBy('h.nobukti');


        DB::table($templist)->insertUsing([
            'nobukti',
            'tglbukti',
            'nobuktitrans',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querytemplist);

        // 8
        $querytemplist = DB::table('prosesgajisupirheader')
            ->from(DB::raw("prosesgajisupirheader as h with (readuncommitted)"))
            ->select(
                'h.nobukti',
                'h.tglbukti',
                'h.pengeluaran_nobukti AS nobuktitrans',
                'h.keterangan',
                DB::raw("0 AS debet"),
                'j.nominal AS kredit',
                DB::raw("0 as saldo")
            )
            ->Join(DB::raw("jurnalumumdetail AS j with (readuncommitted)"), 'h.pengeluaran_nobukti', '=', 'j.nobukti')
            ->whereRaw("j.tglbukti BETWEEN '" . date('Y/m/d', strtotime($dari)) . "' and '" . date('Y/m/d', strtotime($sampai)) . "'")
            ->whereRaw("j.tglbukti>='" . date('Y/m/d', strtotime($ptglawalprogram)) . "'")
            ->whereRaw("isnull(h.pengeluaran_nobukti,'')<>''")
            ->whereRaw("j.coa='03.02.02.04'")
            ->orderBy('h.nobukti');



        DB::table($templist)->insertUsing([
            'nobukti',
            'tglbukti',
            'nobuktitrans',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querytemplist);
        //   dd('test');
        // 9
        $querytemplist = DB::table('hutangheader')
            ->from(DB::raw("hutangheader as d with (readuncommitted)"))
            ->select(
                'j.nobukti',
                'j.tglbukti',
                'j.nobukti as nobuktitrans',
                'j.keterangan',
                DB::raw("-1 * (j.nominal) AS debet"),
                DB::raw("0 AS kredit"),
                DB::raw("0 as saldo")
            )
            ->Join(DB::raw("jurnalumumdetail AS j with (readuncommitted)"), 'd.nobukti', '=', 'j.nobukti')
            ->whereRaw("j.tglbukti BETWEEN '" . date('Y/m/d', strtotime($dari)) . "' and '" . date('Y/m/d', strtotime($sampai)) . "'")
            ->whereRaw("j.tglbukti>='" . date('Y/m/d', strtotime($ptglawalprogram)) . "'")
            ->whereRaw("j.coa='03.02.02.04'")
            ->orderBy('d.nobukti');



        DB::table($templist)->insertUsing([
            'nobukti',
            'tglbukti',
            'nobuktitrans',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querytemplist);

        // 10
        $querytemplist = DB::table('pelunasanhutangheader')
            ->from(DB::raw("pelunasanhutangheader as d with (readuncommitted)"))
            ->select(
                'j.nobukti',
                'j.tglbukti',
                'j.nobukti as nobuktitrans',
                'j.keterangan',
                DB::raw("0  AS debet"),
                DB::raw("(j.nominal) AS kredit"),
                DB::raw("0 as saldo")
            )
            ->Join(DB::raw("jurnalumumdetail AS j with (readuncommitted)"), 'd.pengeluaran_nobukti', '=', 'j.nobukti')
            ->whereRaw("j.tglbukti BETWEEN '" . date('Y/m/d', strtotime($dari)) . "' and '" . date('Y/m/d', strtotime($sampai)) . "'")
            ->whereRaw("j.tglbukti>='" . date('Y/m/d', strtotime($ptglawalprogram)) . "'")
            ->whereRaw("isnull(d.pengeluaran_nobukti,'')<>''")
            ->whereRaw("j.coa='03.02.02.04'")
            ->orderBy('d.nobukti');



        DB::table($templist)->insertUsing([
            'nobukti',
            'tglbukti',
            'nobuktitrans',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querytemplist);

        // 11
        $querytemplist = DB::table('jurnalumumheader')
            ->from(DB::raw("jurnalumumheader as d with (readuncommitted)"))
            ->select(
                'j.nobukti',
                'j.tglbukti',
                'j.nobukti as nobuktitrans',
                'j.keterangan',
                DB::raw("CASE SIGN(-1 * (j.nominal)) WHEN 1 THEN -1 * (j.nominal) ELSE 0 END AS debet"),
                DB::raw("CASE SIGN(-1 * (j.nominal)) WHEN 1 THEN 0 ELSE  (j.nominal) END AS kredit"),
                DB::raw("0 as saldo")
            )
            ->Join(DB::raw("jurnalumumdetail AS j with (readuncommitted)"), 'd.nobukti', '=', 'j.nobukti')
            ->leftJoin(DB::raw($templistbukti . " AS c with (readuncommitted)"), 'd.nobukti', '=', 'c.nobukti')
            ->whereRaw("j.tglbukti BETWEEN '" . date('Y/m/d', strtotime($dari)) . "' and '" . date('Y/m/d', strtotime($sampai)) . "'")
            ->whereRaw("j.tglbukti>='" . date('Y/m/d', strtotime($ptglawalprogram)) . "'")
            ->whereRaw("isnull(c.nobukti,'')=''")
            ->whereRaw("j.coa='03.02.02.04'")
            ->orderBy('d.nobukti');




        DB::table($templist)->insertUsing([
            'nobukti',
            'tglbukti',
            'nobuktitrans',
            'keterangan',
            'debet',
            'kredit',
            'saldo',
        ], $querytemplist);



        //   $templistrekap = '##templistrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        //   Schema::create($templistrekap, function ($table) {
        //       $table->id();            
        //       $table->string('nobukti', 1000)->nullable();
        //       $table->date('tglbukti')->nullable();
        //       $table->string('nobuktitrans', 1000)->nullable();
        //       $table->string('keterangan', 1000)->nullable();
        //       $table->double('debet')->nullable();
        //       $table->double('kredit')->nullable();
        //       $table->double('saldo')->nullable();
        //   });


        //   $querytemplistrekap = DB::table($templist)
        //   ->from(DB::raw($templist ." as a with (readuncommitted)"))
        //   ->select(
        //     'a.nobukti',
        //     DB::raw("max(a.tglbukti) as tglbukti"),
        //     'a.nobuktitrans',
        //     DB::raw("max(a.keterangan) as keterangan"),
        //     DB::raw("sum(a.debet) as debet"),
        //     DB::raw("sum(a.kredit) as kredit"),
        //        DB::raw("0 as saldo")
        //   )
        //   ->groupBy('a.nobukti')
        //   ->groupBy('a.nobuktitrans')

        //   DB::table($templistrekap)->insertUsing([
        //     'nobukti',
        //     'tglbukti',
        //     'nobuktitrans',
        //     'keterangan',
        //     'debet',
        //     'kredit',
        //     'saldo',
        // ], $querytemplistrekap);  

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



        $temtabel = 'temptes1222';
        Schema::dropIfExists($temtabel);
        Schema::create($temtabel, function (Blueprint $table) {
            $table->string('noebs', 1000)->nullable();
            $table->datetime('tanggal')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('nominal')->nullable();
            $table->double('bayar')->nullable();
        });

        // dd(db::table($templistbukti)->get());
        $querycek = DB::table($templist)
            ->from(DB::raw($templist . " as a with (readuncommitted)"))
            ->select(
                DB::raw("(case when isnull(b.nobukti,'')='' then a.nobukti else b.nobukti end) as noebs"),
                'a.tglbukti as tanggal',
                'a.nobuktitrans as nobukti',
                'a.keterangan',
                'a.debet as nominal',
                'a.kredit as bayar',
            )
            ->leftjoin(db::raw($templistbukti. " b "),'a.nobukti','b.nobuktipengeluaran');


        DB::table($temtabel)->insertUsing([
            'noebs',
            'tanggal',
            'nobukti',
            'keterangan',
            'nominal',
            'bayar',
        ], $querycek);



        $query = DB::table($templist)
            ->from(DB::raw($templist . " as a with (readuncommitted)"))
            ->select(
                DB::raw("(case when isnull(a.nobuktitrans,'')='' then a.nobukti else a.nobuktitrans end) as noebs"),
                'a.tglbukti as tanggal',
                'a.nobuktitrans as nobukti',
                'a.keterangan',
                'a.debet as nominal',
                'a.kredit as bayar',
                DB::raw("sum ( (isnull(A.saldo,0)+isnull(a.debet,0))-isnull(a.kredit,0)) over (order by a.id asc) as saldo"),
                DB::raw("'LAPORAN KARTU HUTANG PREDIKSI' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
            )
            ->Orderby('a.id');


        $data = $query->get();
        return $data;
    }
}
