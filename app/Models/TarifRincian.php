<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TarifRincian extends MyModel
{
    use HasFactory;

    protected $table = 'tarifrincian';

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
        $query = DB::table('container')->from(DB::raw("container with (readuncommitted)"))
            ->select(
                'tarifrincian.id as id',
                'container.id as container_id',
                'container.keterangan as container',
                DB::raw("isnull(tarifrincian.nominal,0) as nominal"),
            )
            ->leftJoin('tarifrincian', function ($join)  use ($id) {
                $join->on('tarifrincian.container_id', '=', 'container.id')
                    ->where('tarifrincian.tarif_id', '=', $id);
            });


        $data = $query->get();


        return $data;
    }

    public function updateharga($data)
    {

        // $data=json_encode($data);
        // $data=json_decode($data,true);
        
        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->string('kolomA',2000)->default('');
            $table->string('kolomB',2000)->default('');
            $table->string('kolomC', 2000)->default('');
            $table->string('kolomD', 2000)->default('');
        });

        $datadetail = $data;

        foreach ($datadetail as $item) {
            $temp=new $tempdata;
            $temp->kolomA=$item['tujuan'];
            $temp->kolomA=$item['20`'];
            $temp->kolomA=$item['40`'];
            $temp->kolomA=$item['tglberlaku'];
            $temp->save();
        }

dd($data);
    }

    public function listpivot()
    {

        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->unsignedBigInteger('id')->default(0);
            $table->unsignedBigInteger('container_id')->default(0);
            $table->string('container', 1000)->default('');
            $table->double('nominal', 15, 2)->default(0);
        });

        $query = DB::table('container')->from(DB::raw("container with (readuncommitted)"))
            ->select(
                'tarif.id as id',
                'container.id as container_id',
                'container.keterangan as container',
                DB::raw("isnull(tarifrincian.nominal,0) as nominal"),
            )
            ->leftJoin(DB::raw("tarifrincian with (readuncommitted)"), 'container.id', '=', 'tarifrincian.container_id')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'tarif.id', '=', 'tarifrincian.tarif_id');


        DB::table($tempdata)->insertUsing([
            'id',
            'container_id',
            'container',
            'nominal',
        ], $query);


        $tempdatagroup = '##tempdatagroup' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatagroup, function ($table) {
            $table->string('container', 100)->default('');
        });

        $querydatagroup =  DB::table($tempdata)->from(
            DB::raw($tempdata)
        )
            ->select(
                'container',
            )
            ->groupBy('container');

        DB::table($tempdatagroup)->insertUsing([
            'container',
        ], $querydatagroup);


        $queryloop = DB::table($tempdatagroup)->from(
            DB::raw($tempdatagroup)
        )
            ->select(
                'container',
            )
            ->orderBy('container', 'asc')
            ->get();
        // dd('test');
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

    public function get()
    {
        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';

        $container_id = request()->container_id ?? 0;
        $query = TarifRincian::from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'tarif.id',
                'tarifrincian.id as tarifrincian_id',
                'container.kodecontainer as container_id',
                'tarifrincian.nominal as nominal',
                'tarif.tujuan',
                'parameter.memo as statusaktif',
                'sistemton.memo as statussistemton',
                'kota.kodekota as kota_id',
                'zona.zona as zona_id',
                'tarif.tglmulaiberlaku',
                'p.memo as statuspenyesuaianharga',
                'tarif.modifiedby',
                'tarif.created_at',
                'tarif.updated_at'
            )
            ->Join(DB::raw("tarif  with (readuncommitted)"), 'tarif.id', '=', 'tarifrincian.tarif_id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tarif.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'container.id', '=', "tarifrincian.container_id")
            ->leftJoin(DB::raw("parameter AS p with (readuncommitted)"), 'tarif.statuspenyesuaianharga', '=', 'p.id')
            ->leftJoin(DB::raw("parameter AS sistemton with (readuncommitted)"), 'tarif.statussistemton', '=', 'sistemton.id');





        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);

        $this->filter($query);

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('tarif.statusaktif', '=', $statusaktif->id);
        }
        if ($container_id > 0) {
            $query->where('tarifrincian.container_id', '=', $container_id);
        }


        // dd($query->toSql());
        $this->paginate($query);

        $data = $query->get();



        return $data;
    }

    public function getid($id)
    {

        $query = Tarif::from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'tarif.id',
                'tarifrincian.id as tarifrincian_id',
                'container.kodecontainer as container_id',
                'tarifrincian.nominal as nominal',
                'tarif.tujuan',
                'parameter.memo as statusaktif',
                'sistemton.memo as statussistemton',
                'kota.kodekota as kota_id',
                'zona.zona as zona_id',
                'tarif.tglmulaiberlaku',
                'p.memo as statuspenyesuaianharga',
                'tarifrincian.modifiedby',
                'tarifrincian.created_at',
                'tarifrincian.updated_at'
            )
            ->leftJoin(DB::raw("tarif  with (readuncommitted)"), 'tarif.id', '=', 'tarifrincian.tarif_id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tarif.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'container.id', '=', "tarifrincian.container_id")
            ->leftJoin(DB::raw("parameter AS p with (readuncommitted)"), 'tarif.statuspenyesuaianharga', '=', 'p.id')
            ->leftJoin(DB::raw("parameter AS sistemton with (readuncommitted)"), 'tarif.statussistemton', '=', 'sistemton.id')
            ->where('tarifrincian.id', '=', $id);

        $data = $query->first();


        return $data;
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {

            switch ($this->params['filters']['groupOp']) {



                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {

                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'container_id') {
                            $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kota_id') {
                            $query = $query->where('kota.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'statuspenyesuaianharga') {
                            $query = $query->where('p.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'statussistemton') {
                            $query = $query->where('sistemton.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'tujuan') {
                            $query = $query->Where('tarif.tujuan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->WhereRaw("format(tarif.tglmulaiberlaku,'dd-MM-yyyy') like '%$filters[data]%'");
                        } else {
                            $query = $query->where('tarifrincian.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {

                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'id') {
                            $query = $query->orWhereRaw("(tarifrincian.id like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(tarifrincian.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%')");
                        } elseif ($filters['field'] == 'container_id') {
                            $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kota_id') {
                            $query = $query->orWhere('kota.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'zona_id') {
                            $query = $query->orWhere('zona.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'statuspenyesuaianharga') {
                            $query = $query->orWhere('p.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'statussistemton') {
                            $query = $query->orWhere('sistemton.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'tujuan') {
                            $query = $query->orWhere('tarif.tujuan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->orWhereRaw("format(tarif.tglmulaiberlaku,'dd-MM-yyyy') like '%$filters[data]%'");
                        } else {
                            $query = $query->orWhere('tarifrincian.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
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
