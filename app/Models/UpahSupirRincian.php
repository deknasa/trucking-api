<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\StatusContainer;
use Illuminate\Support\Facades\Schema;

class UpahSupirRincian extends MyModel
{
    use HasFactory;

    protected $table = 'upahsupirrincian';

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
        $query = DB::table('upahsupirrincian')->from(DB::raw("upahsupirrincian with (readuncommitted)"))
        ->select(
            'upahsupirrincian.container_id',
            'container.keterangan as container',
            'upahsupirrincian.statuscontainer_id',
            'statuscontainer.keterangan as statuscontainer',
            'upahsupirrincian.nominalsupir',
            'upahsupirrincian.nominalkenek',
            'upahsupirrincian.nominalkomisi',
            'upahsupirrincian.nominaltol',
            'upahsupirrincian.liter',
        )
            ->leftJoin('container', 'container.id', 'upahsupirrincian.container_id')
            ->leftJoin('statuscontainer', 'statuscontainer.id', 'upahsupirrincian.statuscontainer_id')
            ->where('upahsupir_id', '=', $id);


        $data = $query->get();


        return $data;
    }

    public function setUpRow()
    {
        $query = DB::table('statuscontainer')->select(
            'statuscontainer.keterangan as statuscontainer',
            'statuscontainer.id as statuscontainer_id',
            'container.keterangan as container',
            'container.id as container_id',
            db::Raw("0 as nominalsupir"),
            db::Raw("0 as nominalkenek"),
            db::Raw("0 as nominalkomisi"),
            db::Raw("0 as nominaltol"),
            db::Raw("0 as liter")
        )
        ->crossJoin('container');

        return $query->get();
    }
    public function setUpRowExcept($rincian)
    {
        $data = DB::table('statuscontainer')->select(
            'statuscontainer.keterangan as statuscontainer',
            'statuscontainer.id as statuscontainer_id',
            'container.keterangan as container',
            'container.id as container_id'
        )->crossJoin('container');
        $temp = '##tempcrossjoin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->increments('id');
            $table->string('statuscontainer')->nullable();
            $table->string('statuscontainerId')->nullable();
            $table->string('container')->nullable();
            $table->string('containerId')->nullable();
        });

        DB::table($temp)->insertUsing([
            "statuscontainer",
            "statuscontainerId",
            "container",
            "containerId"
        ], $data);

        //select yang sudah ada
        $except = DB::table($temp)->select(
            "$temp.id",
        );
        for ($i=0; $i < count($rincian); $i++) { 
            $except->orWhere(function ($query) use ($rincian, $i) {
                $query->where('containerId', $rincian[$i]['container_id'])
                    ->where('statuscontainerId', $rincian[$i]['statuscontainer_id']);
            });
        }
        
        foreach ($except->get() as $e) {
            $arr[] = $e->id;
        }
        
        //select semua keluali
        $query = DB::table($temp)->select(
            "$temp.id",
            "$temp.statuscontainer",
            "$temp.statuscontainerId as statuscontainer_id",
            "$temp.container",
            "$temp.containerId as container_id"
        )->whereNotIn('id',$arr);

        // ->whereRaw(" NOT EXIST  ( select $temp.statuscontainer, $temp.container from   [$temp]  WHERE (statuscontainer = 'empty' and container = '20`') or (statuscontainer = 'FULL' and container = '40`') ) ");
        // ->whereRaw("(statuscontainer = 'FULL' and container = '40`')");

        return $query->get();
    }
    public function listpivot($dari, $sampai)
    { 
     
        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->string('container', 1000)->nullable();
            $table->string('litercontainer', 1000)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('liter', 10, 2)->nullable();
        });

        $query = DB::table('container')->from(DB::raw("container with (readuncommitted)"))
            ->select(
                'upahsupir_id as id',
                'container.id as container_id',
                'container.keterangan as container',
                'container.keterangan as litercontainer',
                DB::raw("isnull(upahsupirrincian.nominalsupir,0) as nominal"),
                DB::raw("isnull(upahsupirrincian.liter,0) as liter"),
            )
            ->leftJoin(DB::raw("upahsupirrincian with (readuncommitted)"), 'container.id', '=', 'upahsupirrincian.container_id')
            ->leftJoin(DB::raw("upahsupir with (readuncommitted)"), 'upahsupir.id', '=', 'upahsupirrincian.upahsupir_id')
            ->whereRaw("upahsupir.tglmulaiberlaku >= '$dari'")
            ->whereRaw("upahsupir.tglmulaiberlaku <= '$sampai'");

        DB::table($tempdata)->insertUsing([
            'id',
            'container_id',
            'container',
            'litercontainer',
            'nominal',
            'liter',
        ], $query);

        $id = DB::table($tempdata)->first();

       

        if ($id == null) {
            return null;
        } else {

            $tempupah = '##tempupah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempupah, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->string('tujuan')->nullable();
            });

            $querytempupah = DB::table('upahsupir')->from(DB::raw("upahsupir with (readuncommitted)"))
                ->select(
                    'upahsupir.id as id',
                    'kota.keterangan as tujuan',
                )
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'upahsupir.kotasampai_id', '=', 'kota.id');

            DB::table($tempupah)->insertUsing([
                'id',
                'tujuan',
            ], $querytempupah);


            $tempdatagroup = '##tempdatagroup' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempdatagroup, function ($table) {
                $table->unsignedBigInteger('container_id')->nullable();
            });

            $querydatagroup =  DB::table($tempdata)->from(
                DB::raw($tempdata)
            )
                ->select(
                    'container_id',
                )
                ->groupBy('container_id',);

            DB::table($tempdatagroup)->insertUsing([
                'container_id',
            ], $querydatagroup);

            $queryloop = DB::table($tempdatagroup)->from(
                DB::raw($tempdatagroup)
            )
                ->select(
                    'container.keterangan as container',
                    'container.keterangan as litercontainer'
                )
                ->leftJoin('container', "$tempdatagroup.container_id", 'container.id')
                ->orderBy('container.id', 'asc')
                ->get();

            $columnid = '';
            $columnliterid = '';
            $a = 0;
            $datadetail = json_decode($queryloop, true);

            foreach ($datadetail as $item) {
                if ($a == 0) {
                    $columnid = $columnid . '[' . $item['container'] . ']';
                    $columnliterid = $columnliterid . '[liter' . $item['litercontainer'] . ']';

                    DB::table($tempdata)
                        ->where('container', $item['container'])
                        ->update(['litercontainer' => 'liter' . $item['container']]);
                } else {
                    $columnid = $columnid . ',[' . $item['container'] . ']';
                    $columnliterid = $columnliterid . ',[liter' . $item['litercontainer'] . ']';

                    DB::table($tempdata)
                        ->where('container', $item['container'])
                        ->update(['litercontainer' => 'liter' . $item['container']]);
                }

                $a = $a + 1;
            }

            $statement = ' select b.tujuan,A.* from (select id,' . $columnid . ' from 
                (select A.id,A.container,A.nominal
                    from ' . $tempdata . ' A) as SourceTable
            
                Pivot (
                    max(nominal)
                    for container in (' . $columnid . ')
                    ) as PivotTable)A
                inner join ' . $tempupah . ' b with (readuncommitted) on A.id=B.id
            ';

            $statement2 = 'select b.tujuan,A.* from (select id,' . $columnliterid . ' from 
                (select A.id,A.litercontainer,A.liter
                    from ' . $tempdata . ' A) as SourceTable
            
                Pivot (
                    max(liter)
                    for litercontainer in (' . $columnliterid . ')
                    ) as PivotTable)A
                inner join ' . $tempupah . ' b with (readuncommitted) on A.id=B.id
            ';

            $data1 = DB::select(DB::raw($statement));
            $data2 = DB::select(DB::raw($statement2));
            $merger = [];
            foreach ($data1 as $key => $value) {
                $datas2 = json_decode(json_encode($data2[$key]), true);
                $datas1 = json_decode(json_encode($data1[$key]), true);
                $merger[] = array_merge($datas1, $datas2);
            }



            return $merger;
        }
    }
}

