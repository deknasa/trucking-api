<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanTripTrado extends MyModel
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
        $dari = date('Y-m-d', strtotime(request()->dari)) ?? '1900/1/1';
        $sampai = date('Y-m-d', strtotime(request()->sampai)) ?? '1900/1/1';

        $statusContainer1 = StatusContainer::where('kodestatuscontainer', '=', 'FULL')->first();

        $statusContainer2 = StatusContainer::where('kodestatuscontainer', '=', 'EMPTY')->first();

        $statusContainer3 = StatusContainer::where('kodestatuscontainer', '=', 'FULL EMPTY')->first();

        // $getPelabuhan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PELABUHAN CABANG')->where('subgrp', 'PELABUHAN CABANG')->first();
        // $kotaPort = Kota::where('id', $getPelabuhan->text)->first();

        $parameter = new Parameter();
        $statuspelabuhan = $parameter->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN','PELABUHAN') ?? 0;
        $kotaPort=db::table("kota")->from(db::raw("kota a with (readuncommitted)"))
        ->select(
            db::raw("STRING_AGG(id,',') as id"),
        )
        ->where('a.statuspelabuhan',$statuspelabuhan)
        ->first()->id ?? 1; 

        $full_id = $statusContainer1->id;
        $empty_id = $statusContainer2->id;
        $fullEmpty_id = $statusContainer3->id;
        // $kotaport_id = $kotaPort->id;
        $kotaport_id = $kotaPort;


        $tempData = '##tempData' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempData, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->datetime('tglbukti')->nullable();
            $table->integer('statuscontainer_id')->nullable();
            $table->integer('upah_id')->nullable();
            $table->integer('trado_id')->nullable();
            $table->integer('supir_id')->nullable();
            $table->integer('dari_id')->nullable();
        });

        $queryTempData = DB::table("suratpengantar")->from(
            DB::raw("suratpengantar as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.statuscontainer_id',
                'a.upah_id',
                'a.trado_id',
                'a.supir_id',
                'a.dari_id',
            )
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai);

        DB::table($tempData)->insertUsing([
            'nobukti',
            'tglbukti',
            'statuscontainer_id',
            'upah_id',
            'trado_id',
            'supir_id',
            'dari_id',

        ], $queryTempData);


        $tempRekapTradoStatus = '##tempRekapTradoStatus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempRekapTradoStatus, function ($table) {
            $table->integer('trado_id')->nullable();
            $table->integer('full')->nullable();
            $table->integer('empty')->nullable();
        });

        $queryTempRekapTradoStatus = DB::table($tempData)->from(
            DB::raw($tempData . " as a with (readuncommitted)")
        )
            ->select(
                'a.trado_id',
                DB::raw("(case when A.statuscontainer_id in($full_id,$fullEmpty_id) then 1 else 0 end) as [full]"),
                DB::raw("(case when A.statuscontainer_id in($empty_id) then 1 else 0 end) as [empty]"),

            );

        DB::table($tempRekapTradoStatus)->insertUsing([
            'trado_id',
            'full',
            'empty',
        ], $queryTempRekapTradoStatus);

        $tempDariPort = '##tempDariPort' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempDariPort, function ($table) {
            $table->integer('trado_id')->nullable();
            $table->integer('full')->nullable();
            $table->integer('empty')->nullable();
        });

        $queryDariPort = DB::table($tempData)->from(
            DB::raw($tempData . " as a with (readuncommitted)")
        )
            ->select(
                'a.trado_id',
                DB::raw("(case when A.statuscontainer_id in($full_id,$fullEmpty_id) then 1 else 0 end) as [full]"),
                DB::raw("(case when A.statuscontainer_id in($empty_id) then 1 else 0 end) as [empty]"),

            )
            ->whereraw("a.dari_id in(". $kotaport_id.")");

        DB::table($tempDariPort)->insertUsing([
            'trado_id',
            'full',
            'empty',
        ], $queryDariPort);

        $RekapStatus = '##RekapStatus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($RekapStatus, function ($table) {
            $table->integer('trado_id')->nullable();
            $table->integer('full')->nullable();
            $table->integer('empty')->nullable();
        });

        $tempRekapPort = '##tempRekapPort' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempRekapPort, function ($table) {
            $table->integer('trado_id')->nullable();
            $table->integer('full')->nullable();
            $table->integer('empty')->nullable();
        });

        $queryRekapStatus = DB::table($tempRekapTradoStatus)->from(
            DB::raw("$tempRekapTradoStatus as a")
        )
            ->select(
                'a.trado_id',
                DB::raw("sum(a.[full]) as [full]"),
                DB::raw("sum(a.[empty]) as [empty]")

            )
            ->groupBy("a.trado_id");

        DB::table($RekapStatus)->insertUsing([
            'trado_id',
            'full',
            'empty',
        ], $queryRekapStatus);


        $queryRekapPort = DB::table($tempDariPort)->from(
            DB::raw("$tempDariPort as a")
        )
            ->select(
                'a.trado_id',
                DB::raw("sum(a.[full]) as [full]"),
                DB::raw("sum(a.[empty]) as [empty]")

            )
            ->groupBy("a.trado_id");

        DB::table($tempRekapPort)->insertUsing([
            'trado_id',
            'full',
            'empty',
        ], $queryRekapPort);

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

        $result = DB::table("trado")->from(
            DB::raw("trado as a with (readuncommitted)")
        )
            ->select(
                DB::raw("a.kodetrado as [NoPol]"),
                DB::raw("isnull(c.[full], 0) as [full]"),
                DB::raw("isnull(c.[empty], 0) as [empty]"),
                DB::raw("isnull(B.namasupir, '') as NamaSupir"),
                DB::raw("isnull(D.[full], 0) as [fullport]"),
                DB::raw("isnull(D.[empty], 0) as [emptyport]"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("'LAPORAN TRIP TRADO' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")

            )
           ->leftJoin(DB::raw("supir as b with (readuncommitted)"), 'a.supir_id', 'b.id')
           ->leftJoin(DB::raw("$RekapStatus as c with (readuncommitted)"), 'a.id', 'c.trado_id')
           ->leftJoin(DB::raw("$tempRekapPort as d with (readuncommitted)"), 'a.id', 'd.trado_id')
           ->whereRaw("isnull(C.[full], 0) <> 0")
           ->orWhereRaw("isnull(c.[empty], 0) <> 0")
           ->orwhereRaw("isnull(d.[full], 0) <> 0")
           ->orwhereRaw("isnull(d.[empty], 0) <> 0");


         $data = $result->get();


         return $data;
    }
}
