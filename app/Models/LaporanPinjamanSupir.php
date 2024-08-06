<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPinjamanSupir extends MyModel
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



    public function getReport($sampai, $jenis)
    {

        $pengeluarantrucking_id = 1;
        $penerimaantrucking_id = 2;

        // dd($sampai);

        $temphistory = '##temphistory' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temphistory, function ($table) {
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('supir_id')->nullable();
            $table->double('nominal')->nullable();
            $table->integer('tipe')->nullable();
            $table->string('namasupir', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
        });

        if ($jenis == 0) {
            $queryhistory = DB::table('pengeluarantruckingheader')->from(
                DB::raw("pengeluarantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    'a.nobukti',
                    'a.tglbukti',
                    'b.supir_id',
                    'b.nominal',
                    DB::raw("1 as tipe"),
                    db::raw("isnull(c.namasupir,'') as namasupir"),
                    'a.created_at'
                )
                ->join(DB::raw("pengeluarantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                ->leftjoin(DB::raw("supir as c with (readuncommitted) "), 'b.supir_id', 'c.id')
                ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
                ->whereRaw("a.tglbukti<='" . date('Y/m/d', strtotime($sampai)) . "'")
                // ->whereRaw("isnull(b.supir_id,0)<>0")

                ->OrderBy('c.namasupir', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
        } else {
            $queryhistory = DB::table('pengeluarantruckingheader')->from(
                DB::raw("pengeluarantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    'a.nobukti',
                    'a.tglbukti',
                    'b.supir_id',
                    'b.nominal',
                    DB::raw("1 as tipe"),
                    db::raw("isnull(c.namasupir,'') as namasupir"),
                    'a.created_at'
                )
                ->join(DB::raw("pengeluarantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                ->leftjoin(DB::raw("supir as c with (readuncommitted) "), 'b.supir_id', 'c.id')
                ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
                ->whereRaw("a.tglbukti<='" . date('Y/m/d', strtotime($sampai)) . "'")
                // ->whereRaw("isnull(b.supir_id,0)<>0")
                ->where('a.statusposting', '=', $jenis)

                ->OrderBy('c.namasupir', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
        }


        DB::table($temphistory)->insertUsing([
            'nobukti',
            'tglbukti',
            'supir_id',
            'nominal',
            'tipe',
            'namasupir',
            'created_at',
        ], $queryhistory);

        // dd($sampai);
        if ($jenis == 0) {
            $queryhistory = DB::table('penerimaantruckingheader')->from(
                DB::raw("penerimaantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    db::raw("b.pengeluarantruckingheader_nobukti as nobukti"),
                    // db::raw("a.nobukti as nobukti"),
                    'a.tglbukti',
                    'b.supir_id',
                    db::raw("(b.nominal*-1) as nominal"),
                    DB::raw("1 as tipe"),
                    db::raw("isnull(f.namasupir,'') as namasupir"),
                    'a.created_at',

                )
                ->join(DB::raw("penerimaantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                // ->join(DB::raw("gajisupirpelunasanpinjaman as c with(readuncommitted)"), function ($join) {
                //     $join->on('a.nobukti', '=', 'c.penerimaantrucking_nobukti');
                //     $join->on('b.supir_id', '=', 'c.supir_id');
                //     $join->on('b.pengeluarantruckingheader_nobukti', '=', 'c.pengeluarantrucking_nobukti');
                // })

                // ->leftjoin(DB::raw("prosesgajisupirdetail as d with (readuncommitted) "), 'c.gajisupir_nobukti', 'd.gajisupir_nobukti')
                ->leftjoin(DB::raw("pengeluarantruckingheader as e with (readuncommitted) "), 'b.pengeluarantruckingheader_nobukti', 'e.nobukti')
                ->leftjoin(DB::raw("supir as f with (readuncommitted) "), 'b.supir_id', 'f.id')


                ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
                ->whereRaw("a.tglbukti<'" . date('Y/m/d', strtotime($sampai)) . "'")
                // ->whereRaw("isnull(b.supir_id,0)<>0")

                ->OrderBy('f.namasupir', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
        } else {
            $queryhistory = DB::table('penerimaantruckingheader')->from(
                DB::raw("penerimaantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    db::raw("b.pengeluarantruckingheader_nobukti as nobukti"),
                    // db::raw("a.nobukti as nobukti"),
                    'a.tglbukti',
                    'b.supir_id',
                    db::raw("(b.nominal*-1) as nominal"),
                    DB::raw("1 as tipe"),
                    db::raw("isnull(f.namasupir,'') as namasupir"),
                    'a.created_at',

                )
                ->join(DB::raw("penerimaantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                // ->join(DB::raw("gajisupirpelunasanpinjaman as c with(readuncommitted)"), function ($join) {
                //     $join->on('a.nobukti', '=', 'c.penerimaantrucking_nobukti');
                //     $join->on('b.supir_id', '=', 'c.supir_id');
                //     $join->on('b.pengeluarantruckingheader_nobukti', '=', 'c.pengeluarantrucking_nobukti');
                // })

                // ->leftjoin(DB::raw("prosesgajisupirdetail as d with (readuncommitted) "), 'c.gajisupir_nobukti', 'd.gajisupir_nobukti')
                ->leftjoin(DB::raw("pengeluarantruckingheader as e with (readuncommitted) "), 'b.pengeluarantruckingheader_nobukti', 'e.nobukti')
                ->leftjoin(DB::raw("supir as f with (readuncommitted) "), 'b.supir_id', 'f.id')


                ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
                ->whereRaw("a.tglbukti<'" . date('Y/m/d', strtotime($sampai)) . "'")
                // ->whereRaw("isnull(b.supir_id,0)<>0")
                ->where('e.statusposting', '=', $jenis)

                ->OrderBy('f.namasupir', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
        }

        DB::table($temphistory)->insertUsing([
            'nobukti',
            'tglbukti',
            'supir_id',
            'nominal',
            'tipe',
            'namasupir',
            'created_at',
        ], $queryhistory);

        // dd(db::table($temphistory)->get());

        // $queryhistory=db::table($temphistory)->from(db::raw($temphistory . " a with (readuncommitted)"))
        // ->select(
        //     db::raw("sum(a.nominal) as nominal")
        // )
        // ->where('a.nobukti','PJT 0005/VI/2024')
        // ->get();

        // dd($queryhistory);

        $temprekapdata = '##temprekapdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temprekapdata, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->string('nobuktipelunasan', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->date('tglbuktipelunasan')->nullable();
            $table->double('nominal')->nullable();
            $table->string('namasupir', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
        });

        $queryrekapdata = DB::table($temphistory)->from(
            DB::raw($temphistory . " a  ")
        )
            ->select(
                'a.nobukti',
                DB::raw("'' as nobuktipelunasan"),
                DB::raw("min(a.tglbukti) as tglbukti"),
                DB::raw("'1900/1/1' as tglbuktipelunasan"),
                DB::raw("sum(a.nominal) as nominal"),
                DB::raw("max(a.namasupir) as namasupir"),
                DB::raw("max(a.created_at) as created_at"),
            )
            ->groupBy('a.nobukti');

        DB::table($temprekapdata)->insertUsing([
            'nobukti',
            'nobuktipelunasan',
            'tglbukti',
            'tglbuktipelunasan',
            'nominal',
            'namasupir',
            'created_at',
        ], $queryrekapdata);

        // dd(db::table($temprekapdata)->get());

        // $queryhistory=db::table($temprekapdata)->from(db::raw($temprekapdata . " a with (readuncommitted)"))
        // ->select(
        //     // 'a.nobukti',
        //     db::raw("sum(a.nominal) as nominal")
        // )
        // // ->groupby('a.nobukti')
        // ->get();

        // dd($queryhistory);        

        if ($jenis == 0) {
            $queryrekapdata = DB::table('penerimaantruckingheader')->from(
                DB::raw("penerimaantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    'b.pengeluarantruckingheader_nobukti as nobukti',
                    DB::raw("a.nobukti+(case when isnull(e.nobukti,'')='' then '' else ' ( ' +isnull(e.nobukti,'') +' ) ' end) 
                 as nobuktipelunasan"),
                    'e.tglbukti',
                    'a.tglbukti as tglbuktipelunasan',
                    DB::raw("(b.nominal*-1) as nominal"),
                    db::raw("isnull(f.namasupir,'') as namasupir"),
                    'a.created_at'
                )
                ->join(DB::raw("penerimaantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                // ->leftjoin(DB::raw("gajisupirpelunasanpinjaman as c with(readuncommitted)"), function ($join) {
                //     $join->on('a.nobukti', '=', 'c.penerimaantrucking_nobukti');
                //     $join->on('b.supir_id', '=', 'c.supir_id');
                //     $join->on('b.pengeluarantruckingheader_nobukti', '=', 'c.pengeluarantrucking_nobukti');
                // })

                // ->leftjoin(DB::raw("prosesgajisupirdetail as d with (readuncommitted) "), 'c.gajisupir_nobukti', 'd.gajisupir_nobukti')
                ->join(DB::raw("pengeluarantruckingheader as e with (readuncommitted) "), 'b.pengeluarantruckingheader_nobukti', 'e.nobukti')
                ->join(DB::raw("supir as f with (readuncommitted) "), 'b.supir_id', 'f.id')
                ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
                ->whereRaw("a.tglbukti='" . date('Y/m/d', strtotime($sampai)) . "'")

                ->OrderBy('f.namasupir', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
            // ->whereraw('e.nobukti','PJT 0005/VI/2024');
            // dd($queryrekapdata->tosql
        } else {
            $queryrekapdata = DB::table('penerimaantruckingheader')->from(
                DB::raw("penerimaantruckingheader a with (readuncommitted) ")
            )
                ->select(
                    'b.pengeluarantruckingheader_nobukti as nobukti',
                    DB::raw("a.nobukti+(case when isnull(e.nobukti,'')='' then '' else ' ( ' +isnull(e.nobukti,'') +' ) ' end) 
                 as nobuktipelunasan"),
                    'e.tglbukti',
                    'a.tglbukti as tglbuktipelunasan',
                    DB::raw("(b.nominal*-1) as nominal"),
                    db::raw("isnull(f.namasupir,'') as namasupir"),
                    'a.created_at'
                )
                ->join(DB::raw("penerimaantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
                // ->leftjoin(DB::raw("gajisupirpelunasanpinjaman as c with(readuncommitted)"), function ($join) {
                //     $join->on('a.nobukti', '=', 'c.penerimaantrucking_nobukti');
                //     $join->on('b.supir_id', '=', 'c.supir_id');
                //     $join->on('b.pengeluarantruckingheader_nobukti', '=', 'c.pengeluarantrucking_nobukti');
                // })

                // ->leftjoin(DB::raw("prosesgajisupirdetail as d with (readuncommitted) "), 'c.gajisupir_nobukti', 'd.gajisupir_nobukti')
                ->join(DB::raw("pengeluarantruckingheader as e with (readuncommitted) "), 'b.pengeluarantruckingheader_nobukti', 'e.nobukti')
                ->join(DB::raw("supir as f with (readuncommitted) "), 'b.supir_id', 'f.id')
                ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
                ->whereRaw("a.tglbukti='" . date('Y/m/d', strtotime($sampai)) . "'")
                ->where('e.statusposting', '=', $jenis)

                ->OrderBy('f.namasupir', 'asc')
                ->OrderBy('a.tglbukti', 'asc')
                ->OrderBy('a.nobukti', 'asc');
            // ->whereraw('e.nobukti','PJT 0005/VI/2024');
            // dd($queryrekapdata->tosql
        }


        DB::table($temprekapdata)->insertUsing([
            'nobukti',
            'nobuktipelunasan',
            'tglbukti',
            'tglbuktipelunasan',
            'nominal',
            'namasupir',
            'created_at',
        ], $queryrekapdata);

        // $queryhistory = DB::table('pengeluarantruckingheader')->from(
        //     DB::raw("pengeluarantruckingheader a with (readuncommitted) ")
        // )
        //     ->select(
        //         'a.nobukti',
        //         'a.tglbukti',
        //         'b.supir_id',
        //         'b.nominal',
        //         DB::raw("1 as tipe"),
        //         db::raw("isnull(c.namasupir,'') as namasupir"),
        //         'a.created_at'
        //     )
        //     ->join(DB::raw("pengeluarantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
        //     ->leftjoin(DB::raw("supir as c with (readuncommitted) "), 'b.supir_id', 'c.id')
        //     ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
        //     ->whereRaw("a.tglbukti='" . date('Y/m/d', strtotime($sampai)) . "'")
        //     // ->whereRaw("isnull(b.supir_id,0)<>0")
        //     ->where('a.statusposting', '=', $jenis)

        //     ->OrderBy('c.namasupir', 'asc')
        //     ->OrderBy('a.tglbukti', 'asc')
        //     ->OrderBy('a.nobukti', 'asc');

        //     DB::table($temprekapdata)->insertUsing([
        //         'nobukti',
        //         'nobuktipelunasan',
        //         'tglbukti',
        //         'tglbuktipelunasan',
        //         'nominal',
        //         'namasupir',
        //         'created_at',
        //     ], $queryrekapdata);            


        $temprekapdatahasil = '##temprekapdatahasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temprekapdatahasil, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->string('nobuktipelunasan', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->date('tglbuktipelunasan')->nullable();
            $table->double('nominal')->nullable();
            $table->string('namasupir', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
        });

        $queryrekapdatahasil = DB::table($temprekapdata)->from(
            DB::raw($temprekapdata . " a ")
        )
            ->select(
                'a.nobukti',
                'a.nobuktipelunasan',
                'a.tglbukti',
                'a.tglbuktipelunasan',
                'a.nominal',
                'a.namasupir',
                'a.created_at'
            )
            ->whereraw("isnull(a.nominal,0)<>0")
            ->OrderBy('a.tglbukti', 'asc')
            ->OrderBy('a.created_at', 'asc')
            ->OrderBy('a.nobukti', 'asc')
            ->OrderBy('a.id', 'asc');


        DB::table($temprekapdatahasil)->insertUsing([
            'nobukti',
            'nobuktipelunasan',
            'tglbukti',
            'tglbuktipelunasan',
            'nominal',
            'namasupir',
            'created_at',
        ], $queryrekapdatahasil);

        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temphasil, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->string('namasupir', 1000)->nullable();
            $table->string('nobuktipelunasan', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->date('tglbuktipelunasan')->nullable();
            $table->double('debet')->nullable();
            $table->double('kredit')->nullable();
            $table->double('saldo')->nullable();
        });

        $queryhasil = DB::table($temprekapdatahasil)->from(
            DB::raw($temprekapdatahasil . " a ")
        )
            ->select(
                'a.nobukti',
                'a.namasupir',
                'a.nobuktipelunasan',
                'a.tglbukti',
                'a.tglbuktipelunasan',
                DB::raw("(case when isnull(a.nobuktipelunasan,'')='' then a.nominal else 0 end) as debet"),
                DB::raw("(case when isnull(a.nobuktipelunasan,'')='' then 0 else a.nominal end) as kredit"),
                DB::raw("0 as saldo")

            )
            ->OrderBy('a.id', 'asc');

        DB::table($temphasil)->insertUsing([
            'nobukti',
            'namasupir',
            'nobuktipelunasan',
            'tglbukti',
            'tglbuktipelunasan',
            'debet',
            'kredit',
            'saldo',
        ], $queryhasil);

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

        if ($jenis == 0) {
            $judul1 = 'POSTING / NON POSTING';
        } else {
            $parameter = new Parameter();
            $judul1 = $parameter->cekdataText($jenis) ?? '';
        }
        $query = DB::table($temphasil)->from(
            DB::raw($temphasil . " a ")
        )
            ->select(
                'a.nobukti',
                'a.namasupir',
                'a.nobuktipelunasan',
                'a.tglbukti',
                'a.tglbuktipelunasan',
                'b.keterangan',
                'a.debet',
                DB::raw("abs(a.kredit) as kredit"),
                DB::raw("sum ((isnull(a.saldo,0)+a.debet+a.kredit)) over (order by a.id asc) as Saldo"),
                DB::raw("'LAPORAN PINJAMAN SUPIR " . $judul1 . "' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
            )
            ->leftjoin(DB::raw("pengeluarantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')

            ->OrderBy('a.id', 'asc');

        $data = $query->get();
        return $data;
    }
}
