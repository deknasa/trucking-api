<?php

namespace App\Models;

use App\Http\Controllers\Api\TarifController;
use App\Http\Controllers\Api\TarifRincianController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreTarifRequest;
use App\Http\Requests\StoreTarifRincianRequest;




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
                'container.kodecontainer as container',
                DB::raw("isnull(tarifrincian.nominal,0) as nominal"),
            )
            ->leftJoin('tarifrincian', function ($join)  use ($id) {
                $join->on('tarifrincian.container_id', '=', 'container.id')
                    ->where('tarifrincian.tarif_id', '=', $id);
            });
        $data = $query->get();


        return $data;
    }

    public function cekupdateharga($data)
    {
        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->string('tujuan', 1000)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->string('penyesuaian', 1000)->nullable();
        });

        foreach ($data as $item) {
            $values = array(
                'tujuan' => $item['tujuan'],
                'tglmulaiberlaku' => $item['tglmulaiberlaku'],
                'penyesuaian' => $item['penyesuaian']
            );
            DB::table($tempdata)->insert($values);
        }

        $temptgl = '##temptgl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptgl, function ($table) {
            $table->string('tujuan', 1000)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->string('penyesuaian', 1000)->nullable();
        });

        $querytgl = DB::table('tarif')
            ->from(DB::raw("tarif with (readuncommitted)"))
            ->select(
                'tujuan',
                'tglmulaiberlaku',
                'penyesuaian'
            );

        DB::table($temptgl)->insertUsing(['tujuan', 'tglmulaiberlaku', 'penyesuaian'], $querytgl);


        $query = DB::table($tempdata)
            ->from(DB::raw($tempdata . " as a"))
            ->select(
                'a.tujuan',
                'a.penyesuaian',
                'a.tglmulaiberlaku'
            )
            ->join(DB::raw($temptgl . " as b"), 'a.penyesuaian', 'b.penyesuaian')
            ->first();


        if (isset($query)) {
            $kondisi = true;
        } else {
            $kondisi = false;
        }

        return $kondisi;
    }
    public function updateharga($data)
    {


        // dd($datadetail);
        foreach ($data as $item) {


            $querydetail = DB::table('container')
                ->from(
                    DB::raw("container  with (readuncommitted)")
                )
                ->select(
                    'id'
                )
                ->orderBy('id', 'Asc');
            $datadetail = json_decode($querydetail->get(), true);
            $a = 0;
            $container_id = [];
            $nominal = [];

            foreach ($datadetail as $itemdetail) {
                $a = $a + 1;
                $kolom = 'kolom' . $a;

                $container_id[] = $itemdetail['id'];
                $nominal[] = $item[$kolom];
            }

            $querykota = DB::table('kota')
                ->from(DB::raw("kota with (readuncommitted)"))
                ->select('id')
                ->where('kodekota', '=', $item['kota'])
                ->first();


            $parameter = new Parameter();
            $logrequest = [
                'grp' => 'STATUS AKTIF',
                'subgrp' => 'STATUS AKTIF',
            ];
            $statusaktif = $parameter->getdefaultparameter($logrequest);
            $logrequest = [
                'grp' => 'SISTEM TON',
                'subgrp' => 'SISTEM TON',
            ];
            $statussistemton = $parameter->getdefaultparameter($logrequest);

            $logrequest = [
                'grp' => 'PENYESUAIAN HARGA',
                'subgrp' => 'PENYESUAIAN HARGA',
            ];
            $statuspenyesuaianharga = $parameter->getdefaultparameter($logrequest);
            $getBukanPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS POSTING TNL')
                ->where('text', 'TIDAK POSTING TNL')->first();


            $tarifRequest = [
                'tujuan' => $item['tujuan'],
                'penyesuaian' => $item['penyesuaian'],
                'tglmulaiberlaku' => $item['tglmulaiberlaku'],
                'modifiedby' => $item['modifiedby'],
                'parent_id' => 0,
                'upahsupir_id' => 0,
                'statusaktif' =>  $statusaktif,
                'statuspostingtnl' => $getBukanPostingTnl->id,
                'statussistemton' => $statussistemton,
                'kota_id' => $querykota->id ?? 0,
                'zona_id' => 0,
                'keterangan' => '',
                'statuspenyesuaianharga' => $statuspenyesuaianharga,
                'container_id' => $container_id,
                'nominal' => $nominal,
            ];

            (new Tarif())->processStore($tarifRequest);
        }

        return $data;
    }

    public function listpivot($dari, $sampai)
    {
        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->string('container', 1000)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $query = DB::table('container')->from(DB::raw("container with (readuncommitted)"))
            ->select(
                'tarif.id as id',
                'container.id as container_id',
                'container.keterangan as container',
                DB::raw("isnull(tarifrincian.nominal,0) as nominal"),
            )
            ->leftJoin(DB::raw("tarifrincian with (readuncommitted)"), 'container.id', '=', 'tarifrincian.container_id')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'tarif.id', '=', 'tarifrincian.tarif_id')
            ->whereRaw("tarif.tglmulaiberlaku >= '$dari'")
            ->whereRaw("tarif.tglmulaiberlaku <= '$sampai'");


        DB::table($tempdata)->insertUsing([
            'id',
            'container_id',
            'container',
            'nominal',
        ], $query);


        $tempdatagroup = '##tempdatagroup' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatagroup, function ($table) {
            $table->string('container', 100)->nullable();
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


        $statement = " select b.tujuan as 
        [Tujuan],isnull(b.penyesuaian,'') as [Penyesuaian],cast(format(isnull(b.tglmulaiberlaku,'1900/1/1'),'yyyy/MM/dd') 
        as date) as [Tgl Mulai Berlaku],isnull(C.kodekota,'') 
        as [Kota],A.* from (select " . $columnid . ",id from 
         (
            select A.container,A.nominal,A.id
            from " . $tempdata . " A) as SourceTable
            Pivot (
                max(nominal)
                for container in (" . $columnid . ")
                ) as PivotTable)A
                inner join tarif b with (readuncommitted) on A.id=B.id
                left outer join kota c with (readuncommitted) on b.kota_id=c.id
        ";

        $data = DB::select(DB::raw($statement));

        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';
        $container_id = request()->containerId ?? 0;

        $query = TarifRincian::from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'tarif.id',
                'tarifrincian.id as tarifrincian_id',
                'container.kodecontainer as container_id',
                'tarifrincian.nominal as nominal',
                'tarif.tujuan',
                'tarif.penyesuaian',
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

        $this->sort($query);

        $this->filter($query);

        if (($aktif == 'AKTIF') && (request()->filters == null)) {
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

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);

        $data = $query->get();



        return $data;
    }

    public function getid($id)
    {

        if ($id == 'undefined') {
            $id = 0;
        }
        $container_id = request()->container_id;
        $statusjeniskendaraan = request()->statusjeniskendaraan;

        $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();
        if ($statusjeniskendaraan == $jenisTangki->id) {
            $getTon = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(DB::raw("cast(ISNULL(qtyton,0) as float) as qtyton"))->where('id', request()->idtrip)->first();
            $query = DB::table("tariftangki")->from(DB::raw("tariftangki with (readuncommitted)"))
                ->select(DB::raw("(ROUND(nominal * $getTon->qtyton, 0)) AS nominal"))
                ->where('id', $id);

            $data = $query->first();
        } else {

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
                ->where('tarifrincian.container_id', $container_id)
                ->where('tarifrincian.tarif_id', '=', $id);

            $data = $query->first();
        }

        return $data;
    }
    public function getValidasiTarif($container_id, $id)
    {

        $statusjenis = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'UPAH SUPIR')
            ->where('subgrp', '=', 'TARIF JENIS ORDER')
            ->first();
        if ($statusjenis->text == 'YA') {
            $jenisorder = strtolower(request()->jenisorder);
            $query = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                ->select(
                    'tarif_id',
                    'tarif' . $jenisorder . '_id'
                )
                ->where("id", $id);
            $data = $query->first();
        } else {

            $query = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                ->select(
                    'tarif_id',
                )
                ->where("id", $id);
            $data = $query->first();
        }

        return $data;
    }

    public function getExistNominal($container_id, $id)
    {
        $query = DB::table("tarifrincian")->from(DB::raw("tarifrincian with (readuncommitted)"))
            ->select(
                'nominal',
            )
            ->where("tarif_id", $id)
            ->where('container_id', $container_id);

        $data = $query->first();


        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'tujuan' || $this->params['sortIndex'] == 'tglmulaiberlaku' || $this->params['sortIndex'] == 'statussistemton' || $this->params['sortIndex'] == 'statuspenyesuaianharga' || $this->params['sortIndex'] == 'statusaktif') {
            return $query->orderBy('tarif.' . $this->params['sortIndex'], $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'container_id') {
            return $query->orderBy('container.kodecontainer', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kota_id') {
            return $query->orderBy('kota.kodekota', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'zona_id') {
            return $query->orderBy('zona.zona', $this->params['sortOrder']);
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
                            $query = $query->where('tarif.statusaktif', '=', "$filters[data]");
                            // $query = $query->where('parameter.text', '=', "$filters[data]");
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
                        } elseif ($filters['field'] == 'penyesuaian') {
                            $query = $query->Where('tarif.penyesuaian', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->WhereRaw("format(tarif.tglmulaiberlaku,'dd-MM-yyyy') like '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where('tarifrincian.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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
                            } elseif ($filters['field'] == 'penyesuaian') {
                                $query = $query->orWhere('tarif.penyesuaian', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->orWhereRaw("format(tarif.tglmulaiberlaku,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere('tarifrincian.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function setUpRow()
    {
        $query = DB::table('container')->select(
            'container.kodecontainer as container',
            'container.id as container_id'
        );

        return $query->get();
    }
    public function setUpRowExcept($rincian)
    {
        $data = DB::table('container')->select(
            'container.kodecontainer as container',
            'container.id as container_id'
        );
        $temp = '##tempcrossjoin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->increments('id');
            $table->string('container')->nullable();
            $table->string('containerId')->nullable();
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

    // public function processStore(Tarif $tarif, array $data): TarifRincian
    public function processStore(array $data, TarifRincian $tarifRincian): TarifRincian
    {
        // $tarifRincian = new TarifRincian();
        $tarifRincian->tarif_id = $data['tarif_id'];
        $tarifRincian->tas_id = $data['tas_id'];
        $tarifRincian->container_id = $data['container_id'];
        $tarifRincian->nominal = $data['nominal'];
        $tarifRincian->modifiedby = auth('api')->user()->user;
        $tarifRincian->info = html_entity_decode(request()->info);

        if (!$tarifRincian->save()) {
            throw new \Exception("Error storing tarif rincian.");
        }

        return $tarifRincian;
    }

    public function processUpdate(Tarif $tarif, array $data,TarifRincian $tarifRincian): TarifRincian
    {
        if ($data['detail_id'] !== "null") {
            $tarifRincian->find($data['detail_id']);
        }
        $tarifRincian->tarif_id = $data['tarif_id'];
        $tarifRincian->container_id = $data['container_id'];
        $tarifRincian->nominal = $data['nominal'];
        $tarifRincian->tas_id = $data['tas_id'];
        $tarifRincian->modifiedby = auth('api')->user()->user;
        $tarifRincian->info = html_entity_decode(request()->info);

        if (!$tarifRincian->save()) {
            throw new \Exception("Error updating tarif rincian.");
        }

        return $tarifRincian;
    }
    public function processDestroy(TarifRincian $tarifRincian,$idheader): TarifRincian
    {

        // dd($idheader);
        // $mandor = new Mandor();
        // dd($mandorDetail->get());
        TarifRincian::where('tarif_id', $idheader)->delete();



        (new LogTrail())->processStore([
            'namatabel' => 'TARIF RINCIAN',
            'postingdari' => 'DELETE TARIF RINCIAN',
            'idtrans' => $idheader,
            'nobuktitrans' => $idheader,
            'aksi' => 'DELETE',
            'datajson' => '',
            'modifiedby' => auth('api')->user()->name
        ]);

        return $tarifRincian;
    }    
}
