<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanHistoryDeposito extends MyModel
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

    public function getReport($supirdari_id)
    {

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        // dd("Sdsa");
        $pidpenerimaantrucking = 3;
        $pidpengeluarantrucking = 2;


        $Temppenerimaanpengeluaran = '##Temppenerimaanpengeluaran' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppenerimaanpengeluaran, function ($table) {
            $table->string('nobukti', 50);
            $table->date('tglbukti');
            $table->string('keterangan', 1000);
            $table->double('nominal');
            $table->integer('tipe');
        });

        $select_Temppenerimaanpengeluaran = DB::table('penerimaantruckingheader')->from(DB::raw("penerimaantruckingheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'a.nobukti',
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                DB::raw('MAX(B.keterangan) as keterangan'),
                DB::raw('SUM(B.nominal) as nominal'),
                DB::raw('1'),

            ])
            ->join(DB::raw("penerimaantruckingdetail AS b with (readuncommitted)"), 'a.nobukti', '=', 'b.nobukti')
            ->where('a.penerimaantrucking_id', '=', $pidpenerimaantrucking)
            ->where('a.supir_id', '=', $supirdari_id)
            ->groupBy('a.nobukti');

        DB::table($Temppenerimaanpengeluaran)->insertUsing([
            'nobukti',
            'tglbukti',
            'keterangan',
            'nominal',
            'tipe',
        ], $select_Temppenerimaanpengeluaran);
        // dd($select_Temppenerimaanpengeluaran->get());



        $select_Temppenerimaanpengeluaran2 = DB::table('pengeluarantruckingheader')->from(DB::raw("pengeluarantruckingheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'a.nobukti',
                DB::raw('MAX(a.tglbukti) as tglbukti'),
                DB::raw('MAX(b.keterangan) as keterangan'),
                DB::raw('SUM(b.nominal * -1) as nominal'),
                DB::raw('2')
            ])
            ->join(DB::raw("pengeluarantruckingdetail AS b with (readuncommitted)"), 'a.nobukti', '=', 'b.nobukti')
            ->where('a.pengeluarantrucking_id', '=', $pidpengeluarantrucking)
            ->where('a.supir_id', '=', $supirdari_id)
            ->groupBy('a.nobukti');

        // dd($select_Temppenerimaanpengeluaran2->get());

        DB::table($Temppenerimaanpengeluaran)->insertUsing([
            'nobukti',
            'tglbukti',
            'keterangan',
            'nominal',
            'tipe',
        ], $select_Temppenerimaanpengeluaran2);
        //    dd($select_Temppenerimaanpengeluaran2->get());




        $Temprekap = '##Temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temprekap, function ($table) {
            $table->bigIncrements('id');
            $table->string('nobukti', 50);
            $table->date('tglbukti');
            $table->string('keterangan', 1000);
            $table->double('nominal');
            $table->double('saldo')->nullable();
        });

         $select_Temprekap = DB::table($Temppenerimaanpengeluaran)->from(DB::raw($Temppenerimaanpengeluaran))
            ->select([
                'nobukti',
                'tglbukti',
                'keterangan',
                'nominal',
            ])
            ->orderBy('tglbukti','asc')
            ->orderBy('tipe','asc');

            // dd($select_Temprekap->get());
             
            DB::table($Temprekap)->insertUsing([
                'nobukti',
                'tglbukti',
                'keterangan',
                'nominal',
            ], $select_Temprekap);
          


            $select_Temprekap2 = DB::table($Temprekap)->from(DB::raw($Temprekap))
            ->select([
                'nobukti',
                'tglbukti',
                'keterangan',
                'nominal',
                DB::raw('SUM((ISNULL(saldo, 0) + nominal)) OVER (ORDER BY id ASC) as Saldo')
            ])
            ->orderBy('id');
  
            $data = $select_Temprekap2->get();
            return $data;


    
    }

    public function getExport($supirdari_id)
    {
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

            $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        // dd("Sdsa");
        $pidpenerimaantrucking = 3;
        $pidpengeluarantrucking = 2;


        $Temppenerimaanpengeluaran = '##Temppenerimaanpengeluaran' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppenerimaanpengeluaran, function ($table) {
            $table->string('nobukti', 50);
            $table->date('tglbukti');
            $table->string('keterangan', 1000);
            $table->double('nominal');
            $table->integer('tipe');
        });

        $select_Temppenerimaanpengeluaran = DB::table('penerimaantruckingheader')->from(DB::raw("penerimaantruckingheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'a.nobukti',
                DB::raw('MAX(A.tglbukti) as tglbukti'),
                DB::raw('MAX(B.keterangan) as keterangan'),
                DB::raw('SUM(B.nominal) as nominal'),
                DB::raw('1'),

            ])
            ->join(DB::raw("penerimaantruckingdetail AS b with (readuncommitted)"), 'a.nobukti', '=', 'b.nobukti')
            ->where('a.penerimaantrucking_id', '=', $pidpenerimaantrucking)
            ->where('a.supir_id', '=', $supirdari_id)
            ->groupBy('a.nobukti');

        DB::table($Temppenerimaanpengeluaran)->insertUsing([
            'nobukti',
            'tglbukti',
            'keterangan',
            'nominal',
            'tipe',
        ], $select_Temppenerimaanpengeluaran);
        // dd($select_Temppenerimaanpengeluaran->get());



        $select_Temppenerimaanpengeluaran2 = DB::table('pengeluarantruckingheader')->from(DB::raw("pengeluarantruckingheader AS A WITH (READUNCOMMITTED)"))
            ->select([
                'a.nobukti',
                DB::raw('MAX(a.tglbukti) as tglbukti'),
                DB::raw('MAX(b.keterangan) as keterangan'),
                DB::raw('SUM(b.nominal * -1) as nominal'),
                DB::raw('2')
            ])
            ->join(DB::raw("pengeluarantruckingdetail AS b with (readuncommitted)"), 'a.nobukti', '=', 'b.nobukti')
            ->where('a.pengeluarantrucking_id', '=', $pidpengeluarantrucking)
            ->where('a.supir_id', '=', $supirdari_id)
            ->groupBy('a.nobukti');

        // dd($select_Temppenerimaanpengeluaran2->get());

        DB::table($Temppenerimaanpengeluaran)->insertUsing([
            'nobukti',
            'tglbukti',
            'keterangan',
            'nominal',
            'tipe',
        ], $select_Temppenerimaanpengeluaran2);
        //    dd($select_Temppenerimaanpengeluaran2->get());




        $Temprekap = '##Temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temprekap, function ($table) {
            $table->bigIncrements('id');
            $table->string('nobukti', 50);
            $table->date('tglbukti');
            $table->string('keterangan', 1000);
            $table->double('nominal');
            $table->double('saldo')->nullable();
        });

         $select_Temprekap = DB::table($Temppenerimaanpengeluaran)->from(DB::raw($Temppenerimaanpengeluaran))
            ->select([
                'nobukti',
                'tglbukti',
                'keterangan',
                'nominal',
            ])
            ->orderBy('tglbukti','asc')
            ->orderBy('tipe','asc');

            // dd($select_Temprekap->get());
             
            DB::table($Temprekap)->insertUsing([
                'nobukti',
                'tglbukti',
                'keterangan',
                'nominal',
            ], $select_Temprekap);
          


            $select_Temprekap2 = DB::table($Temprekap)->from(DB::raw($Temprekap))
            ->select([
                'nobukti',
                'tglbukti',
                'keterangan',
                'nominal',
                DB::raw('SUM((ISNULL(saldo, 0) + nominal)) OVER (ORDER BY id ASC) as Saldo')
            ])
            ->orderBy('id');
  
            $data = $select_Temprekap2->get();
            return $data;

    }
}
