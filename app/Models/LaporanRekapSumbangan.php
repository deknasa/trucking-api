<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanRekapSumbangan extends MyModel
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
        // // data coba coba
        // $query = DB::table('penerimaantruckingdetail')->from(
        //     DB::raw("penerimaantruckingdetail with (readuncommitted)")
        // )->select(
        //     'penerimaantruckingdetail.id',
        //     'supir.namasupir',
        //     'penerimaantruckingdetail.nominal',
        // )
        // ->leftJoin(DB::raw("supir with (readuncommitted)"), 'penerimaantruckingdetail.supir_id', 'supir.id')
        // ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.penerimaantruckingheader_id', 'penerimaantruckingheader.id')
        // ->where('penerimaantruckingheader.tglbukti','<=',$sampai);

        // $data = $query->get();
        // return $data;

        $dari = date('Y-m-d', strtotime(request()->dari)) ?? '1900/1/1';
        $sampai = date('Y-m-d', strtotime(request()->sampai)) ?? '1900/1/1';


        $pengeluaranTrucking = PengeluaranTrucking::where('kodepengeluaran', '=', 'BST')->first();

        $pengeluarantrucking_id = $pengeluaranTrucking->id;

        $tempDataRekap = '##tempDataRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempDataRekap, function ($table) {
            $table->string('noinvoice', 50)->nullable();
            $table->string('orderantrucking', 50)->nullable();
            $table->double('nominal')->nullable();
            $table->string('nobukti', 50)->nullable();
        });

        $queryTempDataRekap = DB::table("pengeluarantruckingheader")->from(
            DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'b.invoice_nobukti',
                'b.orderantrucking_nobukti',
                'b.nominal',
                'a.nobukti',
            )
            ->join(DB::raw("pengeluarantruckingdetail as b"), 'a.nobukti', 'b.nobukti')
            ->where('pengeluarantrucking_id', '=', $pengeluarantrucking_id)
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai);

        DB::table($tempDataRekap)->insertUsing([
            'noinvoice',
            'orderantrucking',
            'nominal',
            'nobukti',

        ], $queryTempDataRekap);

        $tempContainerJob = '##tempContainerJob' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempContainerJob, function ($table) {
            $table->string('orderantrucking', 50)->nullable();
            $table->Integer('container_id')->nullable();
        });

        $queryTempContainerJob = DB::table($tempDataRekap)->from(
            DB::raw($tempDataRekap." as a with (readuncommitted)")
        )
            ->select(
                'a.orderantrucking',
                DB::raw("max(container_id) as container_id"),
                
            )
            ->join(DB::raw("suratpengantar as b with (readuncommitted)"), 'a.orderantrucking', 'b.jobtrucking')
            ->groupBy('A.orderantrucking');

           

        DB::table($tempContainerJob)->insertUsing([
            'orderantrucking',
            'container_id',

        ], $queryTempContainerJob);


        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DISETUJUI')
        ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

    $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DIPERIKSA')
        ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $result = DB::table($tempDataRekap)->from(
            DB::raw($tempDataRekap." as a")
        )
            ->select(
                'a.noinvoice as nobukti',
                DB::raw("max(c.keterangan) as container"),
                DB::raw("sum(a.nominal) as nominal"),
                'a.nobukti as nobst',
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("'LAPORAN REKAP SUMBANGAN' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
                
            )
            ->join(DB::raw($tempContainerJob." as b"), 'a.orderantrucking', 'b.orderantrucking')
            ->join(DB::raw("container as c with(readuncommitted)"), 'b.container_id', 'c.id')
            ->groupBy('a.noinvoice')
            ->groupBy('b.container_id')
            ->groupBy('a.nobukti');

        $data = $result->get();

       return $data;

    }
}
