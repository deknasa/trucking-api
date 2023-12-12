<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanBiayaSupir extends Model
{
    use HasFactory;

    public function getExport($dari, $sampai)
    {

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->id();
            $table->integer('supir_id')->nullable();
            $table->string('namasupir', 150)->nullable();
            $table->string('noktp', 50)->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('keterangancoa', 150)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        // BLL
        $fetch = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
        ->select(
            'pengeluarantruckingdetail.supir_id',
            'supir.namasupir',
            'supir.noktp',
            'pengeluarantruckingdetail.nobukti',
            'pengeluaranheader.tglbukti',
            'pengeluarantruckingheader.pengeluaran_nobukti',
            'pengeluarantruckingheader.coa',
            'akunpusat.keterangancoa',
            'pengeluarantruckingdetail.nominal'
        )
        ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
        ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'pengeluarantruckingheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
        ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
        ->where('pengeluarantruckingheader.pengeluarantrucking_id', 10)
        ->where('pengeluarantruckingdetail.nominal', '!=', 0)
        ->whereBetween('pengeluarantruckingheader.tglbukti', [$dari, $sampai])
        ->orderBy('pengeluarantruckingdetail.nobukti');

        DB::table($temp)->insertUsing([
            'supir_id',
            'namasupir',
            'noktp',
            'nobukti',
            'tglbukti',
            'pengeluaran_nobukti',
            'coa',
            'keterangancoa',
            'nominal'
        ], $fetch);

        
        // BSB
        $fetch = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
        ->select(
            'pengeluarantruckingdetail.supir_id',
            'supir.namasupir',
            'supir.noktp',
            'pengeluarantruckingdetail.nobukti',
            'pengeluaranheader.tglbukti',
            'pengeluarantruckingheader.pengeluaran_nobukti',
            'pengeluarantruckingheader.coa',
            'akunpusat.keterangancoa',
            'pengeluarantruckingdetail.nominal'
        )
        ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
        ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'pengeluarantruckingheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
        ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
        ->where('pengeluarantruckingheader.pengeluarantrucking_id', 4)
        ->where('pengeluarantruckingdetail.nominal', '!=', 0)
        ->whereBetween('pengeluarantruckingheader.tglbukti', [$dari, $sampai])
        ->orderBy('pengeluarantruckingdetail.nobukti');

        DB::table($temp)->insertUsing([
            'supir_id',
            'namasupir',
            'noktp',
            'nobukti',
            'tglbukti',
            'pengeluaran_nobukti',
            'coa',
            'keterangancoa',
            'nominal'
        ], $fetch);

        // BLN
        $fetch = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
        ->select(
            'pengeluarantruckingdetail.supir_id',
            'supir.namasupir',
            'supir.noktp',
            'pengeluarantruckingdetail.nobukti',
            'pengeluaranheader.tglbukti',
            'pengeluarantruckingheader.pengeluaran_nobukti',
            'pengeluarantruckingheader.coa',
            'akunpusat.keterangancoa',
            'pengeluarantruckingdetail.nominal'
        )
        ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
        ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'pengeluarantruckingheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
        ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
        ->where('pengeluarantruckingheader.pengeluarantrucking_id', 11)
        ->where('pengeluarantruckingdetail.nominal', '!=', 0)
        ->whereBetween('pengeluarantruckingheader.tglbukti', [$dari, $sampai])
        ->orderBy('pengeluarantruckingdetail.nobukti');

        DB::table($temp)->insertUsing([
            'supir_id',
            'namasupir',
            'noktp',
            'nobukti',
            'tglbukti',
            'pengeluaran_nobukti',
            'coa',
            'keterangancoa',
            'nominal'
        ], $fetch);

        // BTU
        $fetch = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
        ->select(
            'pengeluarantruckingdetail.supir_id',
            'supir.namasupir',
            'supir.noktp',
            'pengeluarantruckingdetail.nobukti',
            'pengeluaranheader.tglbukti',
            'pengeluarantruckingheader.pengeluaran_nobukti',
            'pengeluarantruckingheader.coa',
            'akunpusat.keterangancoa',
            'pengeluarantruckingdetail.nominal'
        )
        ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
        ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'pengeluarantruckingheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
        ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
        ->where('pengeluarantruckingheader.pengeluarantrucking_id', 12)
        ->where('pengeluarantruckingdetail.nominal', '!=', 0)
        ->whereBetween('pengeluarantruckingheader.tglbukti', [$dari, $sampai])
        ->orderBy('pengeluarantruckingdetail.nobukti');

        DB::table($temp)->insertUsing([
            'supir_id',
            'namasupir',
            'noktp',
            'nobukti',
            'tglbukti',
            'pengeluaran_nobukti',
            'coa',
            'keterangancoa',
            'nominal'
        ], $fetch);

        // BPT
        $fetch = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
        ->select(
            'pengeluarantruckingdetail.supir_id',
            'supir.namasupir',
            'supir.noktp',
            'pengeluarantruckingdetail.nobukti',
            'pengeluaranheader.tglbukti',
            'pengeluarantruckingheader.pengeluaran_nobukti',
            'pengeluarantruckingheader.coa',
            'akunpusat.keterangancoa',
            'pengeluarantruckingdetail.nominal'
        )
        ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
        ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'pengeluarantruckingheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
        ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
        ->where('pengeluarantruckingheader.pengeluarantrucking_id', 13)
        ->where('pengeluarantruckingdetail.nominal', '!=', 0)
        ->whereBetween('pengeluarantruckingheader.tglbukti', [$dari, $sampai])
        ->orderBy('pengeluarantruckingdetail.nobukti');

        DB::table($temp)->insertUsing([
            'supir_id',
            'namasupir',
            'noktp',
            'nobukti',
            'tglbukti',
            'pengeluaran_nobukti',
            'coa',
            'keterangancoa',
            'nominal'
        ], $fetch);

        // BGS
        $fetch = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
        ->select(
            'pengeluarantruckingdetail.supir_id',
            'supir.namasupir',
            'supir.noktp',
            'pengeluarantruckingdetail.nobukti',
            'pengeluaranheader.tglbukti',
            'pengeluarantruckingheader.pengeluaran_nobukti',
            'pengeluarantruckingheader.coa',
            'akunpusat.keterangancoa',
            'pengeluarantruckingdetail.nominal'
        )
        ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
        ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'pengeluarantruckingheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
        ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
        ->where('pengeluarantruckingheader.pengeluarantrucking_id', 14)
        ->where('pengeluarantruckingdetail.nominal', '!=', 0)
        ->whereBetween('pengeluarantruckingheader.tglbukti', [$dari, $sampai])
        ->orderBy('pengeluarantruckingdetail.nobukti');

        DB::table($temp)->insertUsing([
            'supir_id',
            'namasupir',
            'noktp',
            'nobukti',
            'tglbukti',
            'pengeluaran_nobukti',
            'coa',
            'keterangancoa',
            'nominal'
        ], $fetch);

        // BIT
        $fetch = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
        ->select(
            'pengeluarantruckingdetail.supir_id',
            'supir.namasupir',
            'supir.noktp',
            'pengeluarantruckingdetail.nobukti',
            'pengeluaranheader.tglbukti',
            'pengeluarantruckingheader.pengeluaran_nobukti',
            'pengeluarantruckingheader.coa',
            'akunpusat.keterangancoa',
            'pengeluarantruckingdetail.nominal'
        )
        ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
        ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'pengeluarantruckingheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
        ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
        ->where('pengeluarantruckingheader.pengeluarantrucking_id', 15)
        ->where('pengeluarantruckingdetail.nominal', '!=', 0)
        ->whereBetween('pengeluarantruckingheader.tglbukti', [$dari, $sampai])
        ->orderBy('pengeluarantruckingdetail.nobukti');

        DB::table($temp)->insertUsing([
            'supir_id',
            'namasupir',
            'noktp',
            'nobukti',
            'tglbukti',
            'pengeluaran_nobukti',
            'coa',
            'keterangancoa',
            'nominal'
        ], $fetch);

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($temp)->from(DB::raw("$temp as a with (readuncommitted)"))
        ->select(
            'a.supir_id',
            'a.namasupir',
            'a.noktp',
            'a.nobukti',
            'a.tglbukti',
            'a.pengeluaran_nobukti',
            'a.coa',
            'a.keterangancoa',
            'a.nominal',            
            DB::raw("'Laporan Biaya Supir' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
        )
        ->get();
        return $query;
    }
}
