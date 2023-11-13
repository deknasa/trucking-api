<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanHistoryPinjaman extends MyModel
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


    public function getReport($supirdari_id, $supirsampai_id)
    {
        $getJudul = DB::table('parameter')->select('text')->where('grp', 'JUDULAN LAPORAN')->where('subgrp', 'JUDULAN LAPORAN')->first();
        $pengeluarantrucking_id = 1;
        $penerimaantrucking_id = 2;

        $temphistory = '##temphistory' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphistory, function ($table) {
            $table->string('nobukti', 50);
            $table->date('tglbukti');
            $table->integer('supir_id');
            $table->double('nominal');
            $table->integer('tipe');
            $table->longtext('keterangan');
            $table->double('saldo')->nullable();
        });

        $select_temphistory = DB::table('pengeluarantruckingheader')->from(DB::raw("pengeluarantruckingheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.nobukti',
                'A.tglbukti',
                'B.supir_id',
                'B.nominal',
                DB::raw('1 as tipe'),
                DB::raw("isnull(b.keterangan,'') as keterangan"),
            ])
            ->join(DB::raw("pengeluarantruckingdetail AS B with (readuncommitted)"), 'A.nobukti', '=', 'B.nobukti');
        if ($supirdari_id != '') {
            $select_temphistory->whereRaw("B.supir_id >= $supirdari_id")
                ->whereRaw("B.supir_id <= $supirsampai_id");
        }
        $select_temphistory->where('a.pengeluarantrucking_id', $pengeluarantrucking_id)
            ->orderBy('B.supir_id')
            ->orderBy('A.tglbukti')
            ->orderBy('A.nobukti');


        DB::table($temphistory)->insertUsing([
            'nobukti',
            'tglbukti',
            'supir_id',
            'nominal',
            'tipe',
            'keterangan',
        ], $select_temphistory);
        // dd($select_temphistory->get());
        // dd($select_temphistory->get());

        $select_temphistory2 = DB::table('penerimaantruckingheader')->from(DB::raw("penerimaantruckingheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                DB::raw("A.penerimaan_nobukti + (CASE WHEN ISNULL(D.nobukti, '') = '' THEN '' ELSE '( ' + ISNULL(D.nobukti, '') + ' ) ' END) AS nobukti"),
                'A.tglbukti',
                'B.supir_id',
                DB::raw('(B.nominal * -1) as nominal'),
                DB::raw('2 as tipe'),
                DB::raw("isnull(b.keterangan,'') as keterangan"),
            ])
            ->join(DB::raw("penerimaantruckingdetail AS B with (readuncommitted)"), 'A.nobukti', '=', 'B.nobukti')
            ->leftJoin(DB::raw("gajisupirpelunasanpinjaman AS C with (readuncommitted)"), 'A.nobukti', 'C.penerimaantrucking_nobukti')
            ->leftJoin(DB::raw("prosesgajisupirdetail AS D with (readuncommitted)"), 'c.gajisupir_nobukti', 'd.gajisupir_nobukti');

        if ($supirdari_id != '') {
            $select_temphistory2->where('B.supir_id', '>=', $supirdari_id)
                ->where('B.supir_id', '<=', $supirsampai_id);
        }
        $select_temphistory2->where('penerimaantrucking_id', '=', $penerimaantrucking_id)
            ->orderBy('B.supir_id')
            ->orderBy('A.tglbukti')
            ->orderBy('A.nobukti');

        DB::table($temphistory)->insertUsing([
            'nobukti',
            'tglbukti',
            'supir_id',
            'nominal',
            'tipe',
            'keterangan',
        ], $select_temphistory2);
        // dd($select_temphistory2->get());

        $temphistoryrekap = '##temphistoryrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphistoryrekap, function ($table) {
            $table->bigIncrements('id');
            $table->string('nobukti', 50);
            $table->datetime('tglbukti');
            $table->integer('supir_id');
            $table->double('nominal');
            $table->integer('tipe');
            $table->string('namasupir', 1000);
            $table->longtext('keterangan');
            $table->double('saldo')->nullable();
        });

        $select_temphistoryrekap = DB::table($temphistory)->from(DB::raw($temphistory . " AS a"))
            ->select([
                'A.nobukti',
                'A.tglbukti',
                'A.supir_id',
                'nominal',
                'a.tipe',
                'b.namasupir',
                'a.keterangan',
            ])
            ->join(DB::raw("supir AS B with (readuncommitted)"), 'A.supir_id', '=', 'B.id')
            ->orderBy('B.namasupir')
            ->orderBy('A.tglbukti')
            ->orderBy('A.tipe');



        DB::table($temphistoryrekap)->insertUsing([
            'nobukti',
            'tglbukti',
            'supir_id',
            'nominal',
            'tipe',
            'namasupir',
            'keterangan',
        ], $select_temphistoryrekap);
        // dd($select_temphistoryrekap->get());

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $supirdari = db::table("supir")->from(db::raw("supir a with (readuncommitted)"))
            ->select(
                'a.namasupir'
            )
            ->where('a.id', $supirdari_id)
            ->first()
            ->namasupir ?? 'SEMUA';

        $supirsampai = db::table("supir")->from(db::raw("supir a with (readuncommitted)"))
            ->select(
                'a.namasupir'
            )
            ->where('a.id', $supirsampai_id)
            ->first()
            ->namasupir ?? 'SEMUA';



        $select_temphistoryrekap2 = DB::table($temphistoryrekap)->from(DB::raw($temphistoryrekap . " AS a"))
            ->select([
                'A.nobukti',
                'A.tglbukti',
                'A.namasupir',
                'A.nominal',
                DB::raw('SUM(ISNULL(A.saldo, 0) + A.nominal) OVER (PARTITION BY A.namasupir ORDER BY A.id ASC) AS Saldo'),
                'A.keterangan',

                DB::raw("'Laporan History Pinjaman' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                db::raw("'" . $supirdari . "' as supirdari"),
                db::raw("'" . $supirsampai . "' as supirsampai"),
            ])
            ->orderBy('A.id');
        // dd($select_temphistoryrekap2->get());
        $data = $select_temphistoryrekap2->get();
        return $data;
    }



    public function getExport($supirdari_id, $supirsampai_id)
    {
        // dd("sdad");
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        // dd("Sdsa");
        $pengeluarantrucking_id = 1;
        $penerimaantrucking_id = 2;

        $temphistory = '##temphistory' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphistory, function ($table) {
            $table->string('nobukti', 50);
            $table->date('tglbukti');
            $table->integer('supir_id');
            $table->double('nominal');
            $table->integer('tipe');
            $table->double('saldo')->nullable();
        });

        $select_temphistory = DB::table('pengeluarantruckingheader')->from(DB::raw("pengeluarantruckingheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'A.nobukti',
                'A.tglbukti',
                'B.supir_id',
                'B.nominal',
                DB::raw('1 as tipe'),
            ])
            ->join(DB::raw("pengeluarantruckingdetail AS B with (readuncommitted)"), 'A.nobukti', '=', 'B.nobukti')
            ->where('B.supir_id', '>=', $supirdari_id)
            ->where('B.supir_id', '<=', $supirsampai_id)
            ->where('pengeluarantrucking_id', '=', $pengeluarantrucking_id)
            ->orderBy('B.supir_id')
            ->orderBy('A.tglbukti')
            ->orderBy('A.nobukti');


        DB::table($temphistory)->insertUsing([
            'nobukti',
            'tglbukti',
            'supir_id',
            'nominal',
            'tipe',
        ], $select_temphistory);
        // dd($select_temphistory->get());
        // dd($select_temphistory->get());

        $select_temphistory2 = DB::table('penerimaantruckingheader')->from(DB::raw("penerimaantruckingheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                DB::raw("A.nobukti + (CASE WHEN ISNULL(D.nobukti, '') = '' THEN '' ELSE '( ' + ISNULL(D.nobukti, '') + ' ) ' END) AS nobukti"),
                'A.tglbukti',
                'B.supir_id',
                DB::raw('(B.nominal * -1) as nominal'),
                DB::raw('2 as tipe'),
            ])
            ->join(DB::raw("penerimaantruckingdetail AS B with (readuncommitted)"), 'A.nobukti', '=', 'B.nobukti')
            ->leftJoin(DB::raw("gajisupirpelunasanpinjaman AS C with (readuncommitted)"), 'A.nobukti', 'C.penerimaantrucking_nobukti')
            ->leftJoin(DB::raw("prosesgajisupirdetail AS D with (readuncommitted)"), 'c.gajisupir_nobukti', 'd.gajisupir_nobukti')
            ->where('B.supir_id', '>=', $supirdari_id)
            ->where('B.supir_id', '<=', $supirsampai_id)
            ->where('penerimaantrucking_id', '=', $penerimaantrucking_id)
            ->orderBy('B.supir_id')
            ->orderBy('A.tglbukti')
            ->orderBy('A.nobukti');

        DB::table($temphistory)->insertUsing([
            'nobukti',
            'tglbukti',
            'supir_id',
            'nominal',
            'tipe',
        ], $select_temphistory2);
        // dd($select_temphistory2->get());

        $temphistoryrekap = '##temphistoryrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphistoryrekap, function ($table) {
            $table->bigIncrements('id');
            $table->string('nobukti', 50);
            $table->datetime('tglbukti');
            $table->integer('supir_id');
            $table->double('nominal');
            $table->integer('tipe');
            $table->string('namasupir', 1000);
            $table->double('saldo')->nullable();
        });

        $select_temphistoryrekap = DB::table($temphistory)->from(DB::raw($temphistory . " AS a"))
            ->select([
                'A.nobukti',
                'A.tglbukti',
                'A.supir_id',
                'nominal',
                'a.tipe',
                'b.namasupir',
            ])
            ->join(DB::raw("supir AS B with (readuncommitted)"), 'A.supir_id', '=', 'B.id')
            ->orderBy('B.namasupir')
            ->orderBy('A.tglbukti')
            ->orderBy('A.tipe');



        DB::table($temphistoryrekap)->insertUsing([
            'nobukti',
            'tglbukti',
            'supir_id',
            'nominal',
            'tipe',
            'namasupir',
        ], $select_temphistoryrekap);
        // dd($select_temphistoryrekap->get());

        $select_temphistoryrekap2 = DB::table($temphistoryrekap)->from(DB::raw($temphistoryrekap . " AS a"))
            ->select([
                'A.nobukti',
                'A.tglbukti',
                'A.namasupir',
                'A.nominal',
                DB::raw('SUM(ISNULL(A.saldo, 0) + A.nominal) OVER (PARTITION BY A.namasupir ORDER BY A.id ASC) AS Saldo'),

                DB::raw("'Laporan History Pinjaman' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            ])
            ->orderBy('A.id');
        // dd($select_temphistoryrekap2->get());
        $data = $select_temphistoryrekap2->get();
        return $data;
    }
}
