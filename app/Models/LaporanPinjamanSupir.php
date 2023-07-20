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



    public function getReport($sampai)
    {
        $pengeluarantrucking_id = 1;
        $penerimaantrucking_id = 2;

        $temphistory = '##temphistory' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temphistory, function ($table) {
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('supir_id')->nullable();
            $table->double('nominal')->nullable();
            $table->integer('tipe')->nullable();
        });

        $queryhistory = DB::table('pengeluarantruckingheader')->from(
            DB::raw("pengeluarantruckingheader a with (readuncommitted) ")
        )
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'b.supir_id',
                'b.nominal',
                DB::raw("1 as tipe")
            )
            ->join(DB::raw("pengeluarantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
            ->whereRaw("a.tglbukti<='" . date('Y/m/d', strtotime($sampai)) . "'")
            ->whereRaw("isnull(b.supir_id,0)<>0")
            ->OrderBy('a.tglbukti', 'asc')
            ->OrderBy('a.nobukti', 'asc');


        DB::table($temphistory)->insertUsing([
            'nobukti',
            'tglbukti',
            'supir_id',
            'nominal',
            'tipe',
        ], $queryhistory);


        $queryhistory = DB::table('penerimaantruckingheader')->from(
            DB::raw("penerimaantruckingheader a with (readuncommitted) ")
        )
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'b.supir_id',
                'b.nominal',
                DB::raw("1 as tipe")
            )
            ->join(DB::raw("penerimaantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("gajisupirpelunasanpinjaman as c with(readuncommitted)"), function ($join) {
                $join->on('a.nobukti', '=', 'c.penerimaantrucking_nobukti');
                $join->on('b.supir_id', '=', 'c.supir_id');
                $join->on('b.pengeluarantruckingheader_nobukti', '=', 'c.pengeluarantrucking_nobukti');
            })

            ->leftjoin(DB::raw("prosesgajisupirdetail as d with (readuncommitted) "), 'c.gajisupir_nobukti', 'd.gajisupir_nobukti')
            ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
            ->whereRaw("a.tglbukti<'" . date('Y/m/d', strtotime($sampai)) . "'")
            ->whereRaw("isnull(b.supir_id,0)<>0")
            ->OrderBy('a.tglbukti', 'asc')
            ->OrderBy('a.nobukti', 'asc');


        DB::table($temphistory)->insertUsing([
            'nobukti',
            'tglbukti',
            'supir_id',
            'nominal',
            'tipe',
        ], $queryhistory);

        $temprekapdata = '##temprekapdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temprekapdata, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->string('nobuktipelunasan', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->date('tglbuktipelunasan')->nullable();
            $table->double('nominal')->nullable();
        });

        $queryrekapdata = DB::table($temphistory)->from(
            DB::raw($temphistory . " a  ")
        )
            ->select(
                'a.nobukti',
                DB::raw("'' as nobuktipelunasan"),
                DB::raw("min(a.tglbukti) as tglbukti"),
                DB::raw("'1900/1/1' as tglbuktipelunasan"),
                DB::raw("sum(a.nominal) as nominal")
            )
            ->groupBy('a.nobukti');

        DB::table($temprekapdata)->insertUsing([
            'nobukti',
            'nobuktipelunasan',
            'tglbukti',
            'tglbuktipelunasan',
            'nominal',
        ], $queryrekapdata);

        $queryrekapdata = DB::table('penerimaantruckingheader')->from(
            DB::raw("penerimaantruckingheader a with (readuncommitted) ")
        )
            ->select(
                'b.pengeluarantruckingheader_nobukti as nobukti',
                DB::raw("a.nobukti+(case when isnull(d.nobukti,'')='' then '' else ' ( ' +isnull(d.nobukti,'') +' ) ' end) 
             as nobuktipelunasan"),
                'e.tglbukti',
                'a.tglbukti as tglbuktipelunasan',
                DB::raw("(b.nominal*-1) as nominal")
            )
            ->join(DB::raw("penerimaantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("gajisupirpelunasanpinjaman as c with(readuncommitted)"), function ($join) {
                $join->on('a.nobukti', '=', 'c.penerimaantrucking_nobukti');
                $join->on('b.supir_id', '=', 'c.supir_id');
                $join->on('b.pengeluarantruckingheader_nobukti', '=', 'c.pengeluarantrucking_nobukti');
            })

            ->leftjoin(DB::raw("prosesgajisupirdetail as d with (readuncommitted) "), 'c.gajisupir_nobukti', 'd.gajisupir_nobukti')
            ->leftjoin(DB::raw("pengeluarantruckingheader as e with (readuncommitted) "), 'b.pengeluarantruckingheader_nobukti', 'e.nobukti')
            ->where('a.penerimaantrucking_id', '=', $penerimaantrucking_id)
            ->whereRaw("a.tglbukti='" . date('Y/m/d', strtotime($sampai)) . "'")
            ->whereRaw("isnull(b.supir_id,0)<>0")
            ->OrderBy('a.tglbukti', 'asc')
            ->OrderBy('a.nobukti', 'asc');


        DB::table($temprekapdata)->insertUsing([
            'nobukti',
            'nobuktipelunasan',
            'tglbukti',
            'tglbuktipelunasan',
            'nominal',
        ], $queryrekapdata);

        $temprekapdatahasil = '##temprekapdatahasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temprekapdatahasil, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->string('nobuktipelunasan', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->date('tglbuktipelunasan')->nullable();
            $table->double('nominal')->nullable();
        });

        $queryrekapdatahasil = DB::table($temprekapdata)->from(
            DB::raw($temprekapdata . " a ")
        )
            ->select(
                'a.nobukti',
                'a.nobuktipelunasan',
                'a.tglbukti',
                'a.tglbuktipelunasan',
                'a.nominal'
            )
            ->OrderBy('a.tglbukti', 'asc')
            ->OrderBy('a.nobukti', 'asc');


        DB::table($temprekapdatahasil)->insertUsing([
            'nobukti',
            'nobuktipelunasan',
            'tglbukti',
            'tglbuktipelunasan',
            'nominal',
        ], $queryrekapdatahasil);

        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temphasil, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
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

        $query = DB::table($temphasil)->from(
            DB::raw($temphasil . " a ")
        )
            ->select(
                'a.nobukti',
                'a.nobuktipelunasan',
                'a.tglbukti',
                'a.tglbuktipelunasan',
                'b.keterangan',
                'a.debet',
                'a.kredit',
                DB::raw("sum ((isnull(a.saldo,0)+a.debet+a.kredit)) over (order by a.id asc) as Saldo"),
                DB::raw("'Laporan Pinjaman Supir' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftjoin(DB::raw("pengeluarantruckingdetail as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')

            ->OrderBy('a.id', 'asc');

        $data = $query->get();
        return $data;
    }
}
