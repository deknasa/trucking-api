<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKomisiSupir extends MyModel
{
    use HasFactory;

    public function getReport($dari, $sampai, $supir_id)
    {
        $tempAwal = '##tempAwal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempAwal, function ($table) {
            $table->string('gajisupir_nobukti', 1000)->nullable();
            $table->string('nobukti_trip', 1000)->nullable();
            $table->date('tgl_ric')->nullable();
            $table->date('tgl_trip')->nullable();
            $table->integer('dari_id')->nullable();
            $table->integer('sampai_id')->nullable();
            $table->integer('pelanggan_id')->nullable();
            $table->integer('supir_id')->nullable();
            $table->double('nominal')->nullable();
            $table->double('gajikenek')->nullable();
            $table->longText('keterangan')->nullable();
        });

        $queryGajisupir = DB::table('gajisupirheader')->from(
            db::raw("gajisupirheader a with (readuncommitted)")
        )
            ->select(
                'a.nobukti as gajisupir_nobukti',
                'c.nobukti as nobukti_trip',
                'a.tglbukti as tgl_ric',
                'c.tglbukti as tgl_trip',
                'c.dari_id',
                'c.sampai_id',
                'c.pelanggan_id',
                'a.supir_id',
                'b.komisisupir as nominal',
                DB::raw("0 as gajikenek"),
                DB::raw("'' as keterangan")
            )
            ->join(DB::raw("gajisupirdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("suratpengantar c with (readuncommitted)"), 'b.suratpengantar_nobukti', 'c.nobukti')

            ->whereRaw("c.tglbukti>='" . $dari . "' and  c.tglbukti<='" . $sampai . "'")
            ->whereRaw("(a.supir_id=" . $supir_id . " or " . $supir_id . "=0)")
            ->where('b.komisisupir', '!=', 0)
            ->Orderby('c.tglbukti', 'asc')
            ->Orderby('a.nobukti', 'asc');

        DB::table($tempAwal)->insertUsing([
            'gajisupir_nobukti',
            'nobukti_trip',
            'tgl_ric',
            'tgl_trip',
            'dari_id',
            'sampai_id',
            'pelanggan_id',
            'supir_id',
            'nominal',
            'gajikenek',
            'keterangan',
        ], $queryGajisupir);

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($tempAwal)->from(db::raw("$tempAwal as a with (readuncommitted)"))
            ->select(
                'a.gajisupir_nobukti',
                'a.nobukti_trip',
                'a.tgl_ric',
                'a.tgl_trip',
                'a.nominal',
                'a.gajikenek',
                'a.keterangan',
                'supir.namasupir as supir',
                'pelanggan.namapelanggan as pelanggan',
                'dari.kodekota as dari',
                'sampai.kodekota as sampai',
                DB::raw("'LAPORAN KOMISI SUPIR' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'a.supir_id', 'supir.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'a.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'a.dari_id', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'a.sampai_id', 'sampai.id');
        $data = $query->get();

        return $data;
    }
}
