<?php

namespace App\Models;

use App\Http\Controllers\Api\UpahRitasiController;
use App\Http\Controllers\Api\UpahRitasiRincianController;
use App\Http\Requests\StoreUpahRitasiRequest;
use App\Http\Requests\StoreUpahRitasiRincianRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
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
        $query = DB::table('container')->from(DB::raw("container with (readuncommitted)"))
            ->select(
                'container.id as container_id',
                'container.keterangan as container',
                'upahritasirincian.nominalsupir',
                'upahritasirincian.liter',
            )
            ->leftJoin('upahritasirincian', function ($join)  use ($id) {
                $join->on('upahritasirincian.container_id', '=', 'container.id')
                    ->where('upahritasirincian.upahritasi_id', '=', $id);
            });



        $data = $query->get();

        return $data;
    }

    public function getLookup()
    {
        $this->setRequestParameters();

        $proses = request()->proses ?? 'reload';
        $tglbukti = date('Y-m-d', strtotime(request()->tglbukti)) ?? '1900-01-01';
        $user = auth('api')->user()->name;
        $class = 'UpahRitasiRincianController';
        $pilihKotaId = request()->pilihkota_id ?? 0;
        $ritasiDariKe = request()->ritasidarike ?? '';

        if ($proses == 'reload') {

            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();
            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );

            Schema::create($temtabel, function (Blueprint $table) {
                $table->bigInteger('id')->nullable();
                $table->longtext('kodekota')->nullable();
                $table->longtext('keterangan')->nullable();
                $table->longtext('modifiedby')->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
            });

            // GET KOTA DENGAN UPAH AKTIF

            $tempKotaUpah = '##tempkotaupah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempKotaUpah, function (Blueprint $table) {
                $table->bigInteger('kota_id')->nullable();
            });
            $queryGetKota = DB::table('upahritasi')->from(DB::raw("upahritasi with (readuncommitted)"))
                ->select('kotadari_id as kota_id')
                ->where('statusaktif', 1)
                ->where("nominalsupir", "<>", 0)
                ->groupBy('kotadari_id');

            DB::table($tempKotaUpah)->insertUsing([
                'kota_id',
            ],  $queryGetKota);

            $queryGetKota = DB::table('upahritasi')->from(DB::raw("upahritasi as a with (readuncommitted)"))
                ->select('a.kotasampai_id as kota_id')
                ->where('a.statusaktif', 1)
                ->leftJoin(DB::raw("$tempKotaUpah as b with (readuncommitted)"), 'a.kotasampai_id', 'b.kota_id')
                ->where("a.nominalsupir", "<>", 0)
                ->groupBy('a.kotasampai_id');

            DB::table($tempKotaUpah)->insertUsing([
                'kota_id',
            ],  $queryGetKota);

            $tempKotaFinal = '##tempKotaFinal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempKotaFinal, function (Blueprint $table) {
                $table->bigInteger('kota_id')->nullable();
            });

            if ($pilihKotaId != 0) {

                $query = DB::table("upahritasi")->from(DB::raw("upahritasi with (readuncommitted)"))
                    ->select('kotadari_id as kota_id')->where('kotasampai_id', $pilihKotaId)->where('nominalsupir', '<>', 0)->where('statusaktif', 1);

                DB::table($tempKotaFinal)->insertUsing([
                    'kota_id',
                ],  $query);

                $query = DB::table("upahritasi")->from(DB::raw("upahritasi with (readuncommitted)"))
                    ->select('kotasampai_id as kota_id')->where('kotadari_id', $pilihKotaId)->where('nominalsupir', '<>', 0)->where('statusaktif', 1);

                DB::table($tempKotaFinal)->insertUsing([
                    'kota_id',
                ],  $query);
            } else {
                $query = DB::table($tempKotaUpah)->from(DB::raw("$tempKotaUpah with (readuncommitted)"))
                    ->select('kota_id')
                    ->groupBy('kota_id');

                DB::table($tempKotaFinal)->insertUsing([
                    'kota_id',
                ],  $query);
            }

            $query = db::table($tempKotaFinal)->from(DB::raw("$tempKotaFinal as a with (readuncommitted)"))
                ->select(
                    'kota.id',
                    'kota.kodekota',
                    'kota.keterangan',
                    'kota.modifiedby',
                    'kota.created_at',
                    'kota.updated_at',
                )
                ->join(DB::raw("kota with (readuncommitted)"), 'a.kota_id', 'kota.id');

            DB::table($temtabel)->insertUsing([
                'id',
                'kodekota',
                'keterangan',
                'modifiedby',
                'created_at',
                'updated_at'
            ],  $query);
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            $temtabel = $querydata->namatabel;
        }

        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.kodekota',
                'a.keterangan',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );

        $this->sort($query);
        $this->filter($query);
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function sort($query)
    {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {

            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function filterExport($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->where('parameter.text', '=', $filters['data']);
                                // } elseif ($filters['field'] == 'statusluarkota') {
                                //     $query = $query->where('statusluarkota.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'kotadari_id') {
                                $query = $query->where('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kotasampai_id') {
                                $query = $query->where('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'zona_id') {
                                //     $query = $query->where('zona.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'jarak') {
                                $query = $query->whereRaw("format($this->table.jarak, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusaktif') {
                                    $query = $query->orWhere('parameter.text', '=', $filters['data']);
                                    // } elseif ($filters['field'] == 'statusluarkota') {
                                    // $query = $query->orWhere('statusluarkota.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'kotadari_id') {
                                    $query = $query->orWhere('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'kotasampai_id') {
                                    $query = $query->orWhere('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                                    // } else if ($filters['field'] == 'zona_id') {
                                    //     $query = $query->orWhere('zona.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'jarak') {
                                    $query = $query->orWhereRaw("format($this->table.jarak, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglmulaiberlaku') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
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


    public function cekupdateharga($data)
    {
        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->string('kotadari', 1000)->nullable();
            $table->string('kotasampai', 1000)->nullable();
            $table->double('nominalsupir', 15, 2)->nullable();
            $table->double('jarak', 15, 2)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
        });

        $tglsaldo = (new Parameter())->cekText('SALDO', 'SALDO') ?? '1900-01-01';
        foreach ($data as $item) {
            $values = array(
                'kotadari' => $item['kotadari'],
                'kotasampai' => $item['kotasampai'],
                'nominalsupir' => $item['nominalsupir'],
                'jarak' => $item['jarak'],
                'tglmulaiberlaku' => $tglsaldo,
            );
            DB::table($tempdata)->insert($values);
        }

        $temptgl = '##temptgl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptgl, function ($table) {
            $table->string('kotadari', 1000)->nullable();
            $table->string('kotasampai', 1000)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
        });

        $querytgl = DB::table('upahritasi')
            ->from(DB::raw("upahritasi with (readuncommitted)"))
            ->select(
                'kotadari.keterangan as kotadari',
                'kotasampai.keterangan as kotasampai',
                'tglmulaiberlaku',
            )

            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahritasi.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahritasi.kotasampai_id');

        DB::table($temptgl)->insertUsing(['kotadari', 'kotasampai', 'tglmulaiberlaku'], $querytgl);

        // dd( DB::table($tempdata)->get(),  DB::table($temptgl)->get());
        $query = DB::table($tempdata)
            ->from(DB::raw($tempdata . " as a"))
            ->join(DB::raw($temptgl . " as b"), 'a.tglmulaiberlaku', 'b.tglmulaiberlaku')
            ->whereRaw("trim(a.kotadari) = trim(b.kotadari)")
            ->whereRaw("trim(a.kotasampai) = trim(b.kotasampai)")
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

            $kotadari = Kota::from(DB::raw("kota with (readuncommitted)"))->where('kodekota', strtoupper(trim($item['kotadari'])))->first();
            $kotasampai = Kota::from(DB::raw("kota with (readuncommitted)"))->where('kodekota', strtoupper(trim($item['kotasampai'])))->first();

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
            $liter = [];

            foreach ($datadetail as $itemdetail) {
                $a = $a + 1;
                $kolom = 'kolom' . $a;

                $container_id[] = $itemdetail['id'];
                $liter[] = $item[$kolom];
            }

            $tglsaldo = (new Parameter())->cekText('SALDO', 'SALDO') ?? '1900-01-01';

            $upahRitasiRequest = [
                'parent_id' => 0,
                'tarif_id' => 0,
                'kotadari_id' => $kotadari->id,
                'kotasampai_id' => $kotasampai->id,
                'jarak' => $item['jarak'],
                'nominalsupir' => $item['nominalsupir'],
                'zona_id' => 0,
                'statusaktif' =>  1,
                'tglmulaiberlaku' => date('Y-m-d', strtotime($tglsaldo)),
                'modifiedby' => $item['modifiedby'],
                'container_id' => $container_id,
                'liter' => $liter
            ];

            $upahRitasi = new StoreUpahRitasiRequest($upahRitasiRequest);
            app(UpahRitasiController::class)->store($upahRitasi);
        }




        return $data;
    }
    public function listpivot()
    {
        $this->setRequestParameters();

        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->string('container', 1000)->nullable();
            $table->string('litercontainer', 1000)->nullable();
            $table->double('liter', 10, 2)->nullable();
        });

        $query = DB::table('container')->from(DB::raw("container with (readuncommitted)"))
            ->select(
                'upahritasi.id as id',
                'container.id as container_id',
                'container.keterangan as container',
                'container.keterangan as litercontainer',
                DB::raw("isnull(upahritasirincian.liter,0) as liter"),
            )
            ->leftJoin(DB::raw("upahritasirincian with (readuncommitted)"), 'container.id', '=', 'upahritasirincian.container_id')
            ->leftJoin(DB::raw("upahritasi with (readuncommitted)"), 'upahritasi.id', '=', 'upahritasirincian.upahritasi_id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahritasi.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahritasi.kotasampai_id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'upahritasi.statusaktif', 'parameter.id');


        $this->filterExport($query);
        DB::table($tempdata)->insertUsing([
            'id',
            'container_id',
            'container',
            'litercontainer',
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
                $table->string('jarak')->nullable();
                $table->string('tglmulaiberlaku')->nullable();
                $table->double('nominal', 15, 2)->nullable();
            });

            $querytempupah = DB::table('upahritasi')->from(DB::raw("upahritasi with (readuncommitted)"))
                ->select(
                    'upahritasi.id as id',
                    'dari.keterangan as dari',
                    'kota.keterangan as tujuan',
                    'upahritasi.jarak',
                    'upahritasi.tglmulaiberlaku',
                    DB::raw("isnull(upahritasi.nominalsupir,0) as nominal"),

                )
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'upahritasi.kotasampai_id', '=', 'kota.id')
                ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'upahritasi.kotadari_id', '=', 'dari.id');


            DB::table($tempupah)->insertUsing([
                'id',
                'dari',
                'tujuan',
                'jarak',
                'tglmulaiberlaku',
                'nominal',

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

            // $statement = ' select b.dari as [Dari],b.tujuan as [Tujuan],b.jarak as [Jarak], b.nominal as [Nominal],b.tglmulaiberlaku as [Tgl Mulai Berlaku],A.* from (select id,' . $columnid . ' from 
            //     (select A.id,A.container
            //         from ' . $tempdata . ' A) as SourceTable

            //     Pivot (
            //         max(nominal)
            //         for container in (' . $columnid . ')
            //         ) as PivotTable)A
            //     inner join ' . $tempupah . ' b with (readuncommitted) on A.id=B.id


            // ';

            $statement2 = 'select b.dari as [Dari],b.tujuan as [Tujuan],b.jarak as [Jarak], b.nominal as [Nominal],A.* from (select id,' . $columnliterid . ' from 
                (select A.id,A.litercontainer,A.liter
                    from ' . $tempdata . ' A) as SourceTable
            
                Pivot (
                    max(liter)
                    for litercontainer in (' . $columnliterid . ')
                    ) as PivotTable)A
                inner join ' . $tempupah . ' b with (readuncommitted) on A.id=B.id
            ';

            // $data1 = DB::select(DB::raw($statement));
            $data2 = DB::select(DB::raw($statement2));
            // $merger = [];
            // foreach ($data1 as $key => $value) {
            //     $datas2 = json_decode(json_encode($data2[$key]), true);
            //     $datas1 = json_decode(json_encode($data1[$key]), true);
            //     $merger[] = array_merge($datas1, $datas2);
            // }


            return $data2;
        }
    }

    public function setUpRow()
    {
        $query = DB::table('container')->select(
            'container.keterangan as container',
            'container.id as container_id',
            db::Raw("0 as nominalsupir"),
            db::Raw("0 as liter"),
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

    public function processStore(UpahRitasi $upahritasi, array $data): UpahRitasiRincian
    {
        $upahritasirincian = new UpahRitasiRincian();
        $upahritasirincian->upahritasi_id = $data['upahritasi_id'];
        $upahritasirincian->container_id = $data['container_id'];
        $upahritasirincian->nominalsupir = 0;
        $upahritasirincian->liter = $data['liter'];
        $upahritasirincian->modifiedby = auth('api')->user()->name;
        $upahritasirincian->info = html_entity_decode(request()->info);

        if (!$upahritasirincian->save()) {
            throw new \Exception("Gagal menyimpan upah ritasi detail.");
        }

        return $upahritasirincian;
    }
}
