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
                'container.kodecontainer as container',
                'upahsupirrincian.statuscontainer_id',
                'statuscontainer.kodestatuscontainer as statuscontainer',
                'upahsupirrincian.nominalsupir',
                'upahsupirrincian.nominalkenek',
                'upahsupirrincian.nominalkomisi',
                'upahsupirrincian.nominaltol',
                'upahsupirrincian.liter',
            )
            ->leftJoin('container', 'container.id', 'upahsupirrincian.container_id')
            ->leftJoin('statuscontainer', 'statuscontainer.id', 'upahsupirrincian.statuscontainer_id')
            ->where('upahsupir_id', '=', $id)            
            ->orderBy('container.id', 'asc')
            ->orderBy('statuscontainer.kodestatuscontainer', 'desc');


        $data = $query->get();


        return $data;
    }

    public function get(){
        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';

        $container_id = request()->container_id ?? 0;
        $statuscontainer_id = request()->statuscontainer_id ?? 0;
        $query = DB::table("upahsupirrincian")->from(DB::raw("upahsupirrincian with (readuncommitted)"))
        ->select(
            'upahsupir.id',
            'upahsupir.kotadari_id',
            'upahsupir.kotasampai_id',
            'kotadari.kodekota as kotadari',
            'kotasampai.kodekota as kotasampai',
            'upahsupir.penyesuaian',
            'upahsupir.jarak',
            'parameter.memo as statusaktif',
            'container.kodecontainer as container',
            'statuscontainer.kodestatuscontainer as statuscontainer',
            'upahsupirrincian.nominalsupir',
            'upahsupirrincian.nominalkenek',
            'upahsupirrincian.nominalkomisi',
            'upahsupir.tglmulaiberlaku',
            'upahsupir.modifiedby',
            'upahsupir.created_at',
            'upahsupir.updated_at'
        )
        ->leftJoin(DB::raw("upahsupir with (readuncommitted)"), 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
        ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'upahsupir.statusaktif', '=', 'parameter.id')
        ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
        ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
        ->leftJoin(DB::raw("container with (readuncommitted)"), 'upahsupirrincian.container_id', 'container.id')
        ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'upahsupirrincian.statuscontainer_id', 'statuscontainer.id');

        
        $this->sort($query);

        $this->filter($query);

        if (($aktif == 'AKTIF')) {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('upahsupir.statusaktif', '=', $statusaktif->id);
        }
        if ($container_id > 0) {
            $query->where('upahsupirrincian.container_id', '=', $container_id);
        }
        if ($statuscontainer_id > 0) {
            $query->where('upahsupirrincian.statuscontainer_id', '=', $statuscontainer_id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getValidasiUpahsupir($container_id,$statuscontainer_id, $id)
    {
        $statusaktif = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('text', '=', 'AKTIF')
            ->first();

        $query = UpahSupir::from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'upahsupir.id',
            )
            ->leftJoin(DB::raw("upahsupirrincian with (readuncommitted)"), 'upahsupir.id', '=', 'upahsupirrincian.upahsupir_id')
            ->whereRaw("upahsupir.id in ($id)")
            ->where('upahsupirrincian.container_id', '=', $container_id)
            ->where('upahsupirrincian.statuscontainer_id', '=', $statuscontainer_id)
            ->where('upahsupir.statusaktif', '=', $statusaktif->id);
        
        $data = $query->first();


        return $data;
    }
    public function getValidasiKota($kota_id, $id)
    {
        $statusaktif = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('text', '=', 'AKTIF')
            ->first();

        $query = UpahSupir::from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'upahsupir.id',
            )
            ->whereRaw("upahsupir.id in ($id)")
            ->where(function ($query) use($kota_id) {
                $query->whereRaw("upahsupir.kotadari_id = $kota_id")
                    ->orWhereRaw("upahsupir.kotasampai_id = $kota_id");
            })
            ->where('upahsupir.statusaktif', '=', $statusaktif->id);
            
        $data = $query->first();


        return $data;
    }


    public function setUpRow()
    {
        $query = DB::table('statuscontainer')->select(
            'statuscontainer.kodestatuscontainer as statuscontainer',
            'statuscontainer.id as statuscontainer_id',
            'container.kodecontainer as container',
            'container.id as container_id',
            db::Raw("0 as nominalsupir"),
            db::Raw("0 as nominalkenek"),
            db::Raw("0 as nominalkomisi"),
            db::Raw("0 as nominaltol"),
            db::Raw("0 as liter")
        )
            ->crossJoin('container')
            ->orderBy('container.id', 'asc')
            ->orderBy('statuscontainer.kodestatuscontainer', 'desc');

        return $query->get();
    }
    public function setUpRowExcept($rincian)
    {
        $data = DB::table('statuscontainer')->select(
            'statuscontainer.kodestatuscontainer as statuscontainer',
            'statuscontainer.id as statuscontainer_id',
            'container.kodecontainer as container',
            'container.id as container_id'
        )->crossJoin('container')
        ->orderBy('container.id', 'asc')
        ->orderBy('statuscontainer.kodestatuscontainer', 'desc');
        
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
        for ($i = 0; $i < count($rincian); $i++) {
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
        )->whereNotIn('id', $arr);

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
                $table->string('dari')->nullable();
                $table->string('tujuan')->nullable();
            });

            $querytempupah = DB::table('upahsupir')->from(DB::raw("upahsupir with (readuncommitted)"))
                ->select(
                    'upahsupir.id as id',
                    'dari.keterangan as dari',
                    'kota.keterangan as tujuan',
                )
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'upahsupir.kotasampai_id', '=', 'kota.id')
                ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'upahsupir.kotadari_id', '=', 'dari.id');

            DB::table($tempupah)->insertUsing([
                'id',
                'dari',
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

            $statement = ' select b.dari,b.tujuan,A.* from (select id,' . $columnid . ' from 
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

    
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'penyesuaian' || $this->params['sortIndex'] == 'jarak' || $this->params['sortIndex'] == 'tglmulaiberlaku' || $this->params['sortIndex'] == 'modifiedby' || $this->params['sortIndex'] == 'created_at' || $this->params['sortIndex'] == 'updated_at' || $this->params['sortIndex'] == 'statusaktif') {
            return $query->orderBy('upahsupir.' . $this->params['sortIndex'], $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'container') {
            return $query->orderBy('container.kodecontainer', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'statuscontainer') {
            return $query->orderBy('statuscontainer.kodestatuscontainer', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kotadari') {
            return $query->orderBy('kotadari.kodekota', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kotasampai') {
            return $query->orderBy('kotasampai.kodekota', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {

            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {

                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'container') {
                            $query = $query->where('container.kodecontainer', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'statuscontainer') {
                            $query = $query->where('statuscontainer.kodestatuscontainer', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kotadari') {
                            $query = $query->where('kotadari.kodekota', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kotasampai') {
                            $query = $query->where('kotasampai.kodekota', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->WhereRaw("format(upahsupir.tglmulaiberlaku,'dd-MM-yyyy') like '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(upahsupir." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'nominalsupir' || $filters['field'] == 'nominalkenek' || $filters['field'] == 'nominalkomisi') {
                            $query = $query->whereRaw("format(upahsupirrincian.".$filters['field'].", '#,#0.00') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where('upahsupir.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } elseif ($filters['field'] == 'container') {
                                $query = $query->orWhere('container.kodecontainer', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'statuscontainer') {
                                $query = $query->orWhere('statuscontainer.kodestatuscontainer', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'kotadari') {
                                $query = $query->orWhere('kotadari.kodekota', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'kotasampai') {
                                $query = $query->orWhere('kotasampai.kodekota', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->orWhereRaw("format(upahsupir.tglmulaiberlaku,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(upahsupir." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominalsupir' || $filters['field'] == 'nominalkenek' || $filters['field'] == 'nominalkomisi') {
                                $query = $query->orWhereRaw("format(upahsupirrincian.".$filters['field'].", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere('upahsupir.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

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
    
    public function processStore(UpahSupir $upahsupir, array $data): UpahSupirRincian
    {
        $upahSupirRincian = new UpahSupirRincian();
        $upahSupirRincian->upahsupir_id = $data['upahsupir_id'];
        $upahSupirRincian->container_id = $data['container_id'];
        $upahSupirRincian->statuscontainer_id = $data['statuscontainer_id'];
        $upahSupirRincian->nominalsupir = $data['nominalsupir'];
        $upahSupirRincian->nominalkenek = $data['nominalkenek'];
        $upahSupirRincian->nominalkomisi = $data['nominalkomisi'];
        $upahSupirRincian->nominaltol = $data['nominaltol'];
        $upahSupirRincian->liter = $data['liter'];
        $upahSupirRincian->modifiedby = auth('api')->user()->name;
        
        if (!$upahSupirRincian->save()) {
            throw new \Exception("Error storing upah supir in detail.");
        }

        return $upahSupirRincian;
    }
}
