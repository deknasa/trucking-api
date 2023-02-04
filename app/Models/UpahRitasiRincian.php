<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpahRitasiRincian extends MyModel
{
    use HasFactory;

    protected $table = 'upahritasirincian';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getAll($id)
    {
        $query = DB::table('upahritasirincian')->from(DB::raw("upahritasirincian with (readuncommitted)"))
        ->select(
            'upahritasirincian.container_id',
            'container.keterangan as container',
            'upahritasirincian.nominalsupir',
            'upahritasirincian.liter',
        )
            ->leftJoin('container', 'container.id', 'upahritasirincian.container_id')
            ->where('upahritasi_id', '=', $id);


        $data = $query->get();

        return $data;
    }

    public function listpivot()
    {

        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->unsignedBigInteger('id')->default(0);
            $table->unsignedBigInteger('container_id')->default(0);
            $table->string('container', 1000)->default('');
            $table->double('nominalsupir', 15, 2)->default(0);
            $table->double('liter', 15, 2)->default(0);
        });

        $query = DB::table('container')->from(DB::raw("container with (readuncommitted)"))
            ->select(
                'upahritasi.id as id',
                'container.id as container_id',
                'container.keterangan as container',
                DB::raw("isnull(upahritasirincian.nominalsupir,0) as nominal"),
                DB::raw("isnull(upahritasirincian.liter,0) as liter"),
            )
            ->leftJoin(DB::raw("upahritasirincian with (readuncommitted)"), 'container.id', '=', 'upahritasirincian.container_id')
            ->leftJoin(DB::raw("upahritasi with (readuncommitted)"), 'upahritasi.id', '=', 'upahritasirincian.upahritasi_id');


        DB::table($tempdata)->insertUsing([
            'id',
            'container_id',
            'container',
            'nominalsupir',
            'liter',
        ], $query);


        $tempdatagroup = '##tempdatagroup' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatagroup, function ($table) {
            $table->string('container', 100)->default('');
            $table->unsignedBigInteger('container_id')->default(0);
        });

        $querydatagroup =  DB::table($tempdata)->from(
            DB::raw($tempdata)
        )
            ->select(
                'container',
                'container_id'
            )
            ->groupBy('container')
            ->groupBy('container_id')
            ->orderBy('container_id','Asc');

        DB::table($tempdatagroup)->insertUsing([
            'container',
        ], $querydatagroup);


        $queryloop = DB::table($tempdatagroup)->from(
            DB::raw($tempdatagroup)
        )
            ->select(
                'container',
                'container_id',
            )
            ->orderBy('container_id', 'asc')
            ->get();
       
        $columnid = '';
        $a = 0;
        $datadetail = json_decode($queryloop, true);
        foreach ($datadetail as $item) {
            if ($a == 0) {
                $columnid = $columnid . '[' . $item['container'] . ']';
            } else {
                $columnid = $columnid . ',[' . $item['container'] . ']';
            }

            $a = $a + 1;
        }


        $statement = ' select b.tujuan,A.* from (select id,' . $columnid . ' from 
         (
            select A.id,A.container,A.nominal
            from ' . $tempdata . ' A) as SourceTable
            Pivot (
                max(nominal)
                for container in (' . $columnid . ')
                ) as PivotTable)A
                inner join tarif b with (readuncommitted) on A.id=B.id
        ';

        $data = DB::select(DB::raw($statement));


        return $data;
    }

    public function setUpRow()
    {
        $query = DB::table('container')->select(
            'container.keterangan as container',
            'container.id as container_id'
        );

        return $query->get();
    }
    public function setUpRowExcept($rincian)
    {
        $data = DB::table('container')->select(
            'container.keterangan as container',
            'container.id as container_id'
        );
        $temp = '##tempcrossjoin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->increments('id');
            $table->string('container')->default('');
            $table->string('containerId')->default('0');
        });

        DB::table($temp)->insertUsing([
            "container",
            "containerId"
        ], $data);

        //select yang sudah ada
        $except = DB::table($temp)->select(
            "$temp.id",
        );
        for ($i = 0; $i < count($rincian); $i++) {
            $except->orWhere(function ($query) use ($rincian, $i) {
                $query->where('containerId', $rincian[$i]['container_id']);
            });
        }

        foreach ($except->get() as $e) {
            $arr[] = $e->id;
        }

        //select semua keluali
        $query = DB::table($temp)->select(
            "$temp.id",
            "$temp.container",
            "$temp.containerId as container_id"
        )->whereNotIn('id', $arr);

        // ->whereRaw(" NOT EXIST  ( select $temp.statuscontainer, $temp.container from   [$temp]  WHERE (statuscontainer = 'empty' and container = '20`') or (statuscontainer = 'FULL' and container = '40`') ) ");
        // ->whereRaw("(statuscontainer = 'FULL' and container = '40`')");

        return $query->get();
    }
}
