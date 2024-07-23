<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TarifTangki extends MyModel
{
    use HasFactory;

    protected $table = 'tariftangki';

    protected $casts = [
        'tglberlaku' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();


        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'TarifTangkiController';

        $aktif = request()->aktif ?? '';
        // 
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
                $table->longText('parent_id')->nullable();
                $table->longText('upahsupirtangki')->nullable();
                $table->longText('tujuan')->nullable();
                $table->longText('penyesuaian')->nullable();
                $table->longText('statusaktif')->nullable();
                $table->longText('statusaktif_text')->nullable();
                $table->longText('kota_id')->nullable();
                $table->bigInteger('kotaId')->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->longText('statuspenyesuaianharga')->nullable();
                $table->longText('statuspenyesuaianharga_text')->nullable();
                $table->longText('statuspostingtnl')->nullable();
                $table->longText('statuspostingtnl_text')->nullable();
                $table->longText('keterangan')->nullable();
                $table->double('nominal', 15, 2)->nullable();
                $table->longText('modifiedby')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->longText('tglcetak')->nullable();
                $table->longText('usercetak')->nullable();
                $table->longText('tujuanpenyesuaian')->nullable();
                $table->bigInteger('statusaktif_id')->nullable();
            });

            $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
                ->select(
                    'tariftangki.id',
                    'parent.tujuan as parent_id',
                    db::raw("isnull(kotadari.keterangan,'')+(case when isnull(kotasampai.keterangan,'')='' then '' else ' - ' +isnull(kotasampai.keterangan,'') end)+ 
             (case when isnull(upahsupirtangki.penyesuaian,'')='' then '' else ' ( ' +isnull(upahsupirtangki.penyesuaian,'')+ ' ) ' end) as upahsupirtangki
             "),
                    'tariftangki.tujuan',
                    'tariftangki.penyesuaian',
                    'parameter.memo as statusaktif',
                    'parameter.text as statusaktif_text',
                    'kota.kodekota as kota_id',
                    'tariftangki.kota_id as kotaId',
                    'tariftangki.tglmulaiberlaku',
                    'p.memo as statuspenyesuaianharga',
                    'p.text as statuspenyesuaianharga_text',
                    'posting.memo as statuspostingtnl',
                    'posting.text as statuspostingtnl_text',
                    'tariftangki.keterangan',
                    'tariftangki.nominal',
                    'tariftangki.modifiedby',
                    'tariftangki.created_at',
                    'tariftangki.updated_at',
                    DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                    DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                    DB::raw("(trim(tariftangki.tujuan)+(case when trim(tariftangki.penyesuaian)='' then '' else ' - ' end)+trim(tariftangki.penyesuaian)) as tujuanpenyesuaian"),
                    'tariftangki.statusaktif as statusaktif_id',

                )
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tariftangki.statusaktif', '=', 'parameter.id')
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tariftangki.kota_id', '=', 'kota.id')
                ->leftJoin(DB::raw("tariftangki as parent with (readuncommitted)"), 'tariftangki.parent_id', '=', 'parent.id')
                ->leftJoin(DB::raw("parameter AS p with (readuncommitted)"), 'tariftangki.statuspenyesuaianharga', '=', 'p.id')
                ->leftJoin(DB::raw("parameter AS posting with (readuncommitted)"), 'tariftangki.statuspostingtnl', '=', 'posting.id')
                ->leftJoin(DB::raw("upahsupirtangki as upahsupirtangki with (readuncommitted)"), 'upahsupirtangki.tariftangki_id', '=', 'tariftangki.id')
                ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupirtangki.kotadari_id')
                ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupirtangki.kotasampai_id');


            DB::table($temtabel)->insertUsing([
                'id',
                'parent_id',
                'upahsupirtangki',
                'tujuan',
                'penyesuaian',
                'statusaktif',
                'statusaktif_text',
                'kota_id',
                'kotaId',
                'tglmulaiberlaku',
                'statuspenyesuaianharga',
                'statuspenyesuaianharga_text',
                'statuspostingtnl',
                'statuspostingtnl_text',
                'keterangan',
                'nominal',
                'modifiedby',
                'created_at',
                'updated_at',
                'tglcetak',
                'usercetak',
                'tujuanpenyesuaian',
                'statusaktif_id'
            ], $query);
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
        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " tariftangki with (readuncommitted)")
        )
            ->select(
                'tariftangki.id',
                'tariftangki.parent_id',
                'tariftangki.upahsupirtangki',
                'tariftangki.tujuan',
                'tariftangki.penyesuaian',
                'tariftangki.statusaktif',
                'tariftangki.kota_id',
                'tariftangki.kotaId',
                'tariftangki.nominal',
                'tariftangki.tglmulaiberlaku',
                'tariftangki.statuspenyesuaianharga',
                'tariftangki.statuspostingtnl',
                'tariftangki.keterangan',
                'tariftangki.modifiedby',
                'tariftangki.created_at',
                'tariftangki.updated_at',
                'tariftangki.tglcetak',
                'tariftangki.usercetak',
                'tariftangki.tujuanpenyesuaian',
            );


        // dd('test');
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('tariftangki.statusaktif_id', '=', $statusaktif->id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->filter($query);
        $this->sort($query);

        $this->paginate($query);
        // dd($query->toSql());l
        $data = $query->get();

        return $data;
    }


    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama', 300)->nullable();
            $table->unsignedBigInteger('statuspenyesuaianharga')->nullable();
            $table->string('statuspenyesuaianharganama', 300)->nullable();
            $table->unsignedBigInteger('statuspostingtnl')->nullable();
        });

        $statusAktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();


        $statusPenyesuaianHarga = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'PENYESUAIAN HARGA')
            ->where('subgrp', '=', 'PENYESUAIAN HARGA')
            ->where('default', '=', 'YA')
            ->first();

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS POSTING TNL')
            ->where('subgrp', '=', 'STATUS POSTING TNL')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuspostingtnl = $status->id ?? 0;

        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $statusAktif->id ?? 0,
                "statusaktifnama" => $statusAktif->text ?? "",
                "statuspenyesuaianharga" => $statusPenyesuaianHarga->id ?? 0,
                "statuspenyesuaianharganama" => $statusPenyesuaianHarga->text ?? "",
                "statuspostingtnl" => $iddefaultstatuspostingtnl,
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama',
                'statuspenyesuaianharga',
                'statuspenyesuaianharganama',
                'statuspostingtnl',
            );

        $data = $query->first();

        return $data;
    }

    public function sort($query)
    {
        return $query->orderBy('tariftangki.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('tariftangki.statusaktif_text', '=', "$filters[data]");
                        }
                        //  elseif ($filters['field'] == 'container_id') {
                        //     $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'parent_id') {
                        //     $query = $query->where('parent.tujuan', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'upahsupir_id') {
                        //     $query = $query->where('B.kotasampai_id', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'kota_id') {
                        //     $query = $query->where('kota.keterangan', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'keterangan_id') {
                        //     $query = $query->where('keterangan.keterangan', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'zona_id') {
                        //     $query = $query->where('zona.keterangan', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'tujuanpenyesuaian') {
                        //     $query = $query->whereRaw("(trim(tarif.tujuan)+(case when trim(tarif.penyesuaian)='' then '' else ' - ' end)+trim(tarif.penyesuaian)) LIKE '%$filters[data]%'");
                        // } elseif ($filters['field'] == 'jenisorder') {
                        //     $query = $query->where('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'statuspenyesuaianharga') {
                        //     $query = $query->where('p.text', '=', "$filters[data]");
                        // } elseif ($filters['field'] == 'statuspostingtnl') {
                        //     $query = $query->where('posting.text', '=', "$filters[data]");
                        // } elseif ($filters['field'] == 'statussistemton') {
                        //     $query = $query->where('sistemton.text', '=', "$filters[data]");
                        // } else
                        else if ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'check') {
                            $query = $query->whereRaw('1 = 1');
                        } else if ($filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format(tariftangki.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        }else {
                            // $query = $query->where('tarif.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('tariftangki' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('tariftangki.statusaktif_text', '=', "$filters[data]");
                                // }  elseif ($filters['field'] == 'container_id') {
                                //     $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                                // } elseif ($filters['field'] == 'parent_id') {
                                //     $query = $query->orWhere('parent.tujuan', 'LIKE', "%$filters[data]%");
                                // } elseif ($filters['field'] == 'upahsupir_id') {
                                //     $query = $query->orWhere('kotasampai_id', 'LIKE', "%$filters[data]%");
                                // } elseif ($filters['field'] == 'kota_id') {
                                //     $query = $query->orWhere('kota.keterangan', 'LIKE', "%$filters[data]%");
                                // } elseif ($filters['field'] == 'zona_id') {
                                //     $query = $query->orWhere('zona.keterangan', 'LIKE', "%$filters[data]%");
                                // } elseif ($filters['field'] == 'tujuanpenyesuaian') {
                                //     $query = $query->orWhereRaw("(trim(tarif.tujuan)+(case when trim(tarif.penyesuaian)='' then '' else ' - ' end)+trim(tarif.penyesuaian)) LIKE '%$filters[data]%'");
                                // } elseif ($filters['field'] == 'jenisorder') {
                                //     $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                                // } elseif ($filters['field'] == 'statuspenyesuaianharga') {
                                //     $query = $query->orWhere('p.text', '=', "$filters[data]");
                                // } elseif ($filters['field'] == 'statuspostingtnl') {
                                //     $query = $query->orWhere('posting.text', '=', "$filters[data]");
                                // } elseif ($filters['field'] == 'statussistemton') {
                                //     $query = $query->orWhere('sistemton.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'check') {
                                $query = $query->whereRaw('1 = 1');
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->OrwhereRaw("format(tariftangki.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            }else {
                                // $query = $query->orWhere('tarif.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw('tariftangki' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function selectColumns()
    { //sesuaikan dengan createtemp
        $temtabel = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temtabel, function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->longText('parent_id')->nullable();
            $table->longText('upahsupirtangki')->nullable();
            $table->longText('tujuan')->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->longText('statusaktif')->nullable();
            $table->longText('statusaktif_text')->nullable();
            $table->longText('kota_id')->nullable();
            $table->bigInteger('kotaId')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->longText('statuspenyesuaianharga')->nullable();
            $table->longText('statuspenyesuaianharga_text')->nullable();
            $table->longText('statuspostingtnl')->nullable();
            $table->longText('statuspostingtnl_text')->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->bigInteger('statusaktif_id')->nullable();
        });

        $query1 = DB::table('tariftangki')->from(DB::raw("tariftangki with (readuncommitted)"))
            ->select(
                'tariftangki.id',
                'parent.tujuan as parent_id',
                db::raw("isnull(kotadari.keterangan,'')+(case when isnull(kotasampai.keterangan,'')='' then '' else ' - ' +isnull(kotasampai.keterangan,'') end)+ 
             (case when isnull(upahsupirtangki.penyesuaian,'')='' then '' else ' ( ' +isnull(upahsupirtangki.penyesuaian,'')+ ' ) ' end) as upahsupirtangki
             "),
                'tariftangki.tujuan',
                'tariftangki.penyesuaian',
                'parameter.memo as statusaktif',
                'parameter.text as statusaktif_text',
                'kota.kodekota as kota_id',
                'tariftangki.kota_id as kotaId',
                'tariftangki.tglmulaiberlaku',
                'p.memo as statuspenyesuaianharga',
                'p.text as statuspenyesuaianharga_text',
                'posting.memo as statuspostingtnl',
                'posting.text as statuspostingtnl_text',
                'tariftangki.keterangan',
                'tariftangki.nominal',
                'tariftangki.modifiedby',
                'tariftangki.created_at',
                'tariftangki.updated_at',
                'tariftangki.statusaktif as statusaktif_id',

            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tariftangki.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tariftangki.kota_id', '=', 'kota.id')
           ->leftJoin(DB::raw("tariftangki as parent with (readuncommitted)"), 'tariftangki.parent_id', '=', 'parent.id')
            ->leftJoin(DB::raw("parameter AS p with (readuncommitted)"), 'tariftangki.statuspenyesuaianharga', '=', 'p.id')
            ->leftJoin(DB::raw("parameter AS posting with (readuncommitted)"), 'tariftangki.statuspostingtnl', '=', 'posting.id')
            ->leftJoin(DB::raw("upahsupirtangki as upahsupirtangki with (readuncommitted)"), 'upahsupirtangki.tariftangki_id', '=', 'tariftangki.id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupirtangki.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupirtangki.kotasampai_id');

        DB::table($temtabel)->insertUsing([
            'id',
            'parent_id',
            'upahsupirtangki',
            'tujuan',
            'penyesuaian',
            'statusaktif',
            'statusaktif_text',
            'kota_id',
            'kotaId',
            'tglmulaiberlaku',
            'statuspenyesuaianharga',
            'statuspenyesuaianharga_text',
            'statuspostingtnl',
            'statuspostingtnl_text',
            'keterangan',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at',
            'statusaktif_id'
        ], $query1);

        $query2 = db::table($temtabel)->from(db::raw($temtabel . " as tariftangki with (readuncommitted)"))
            ->select(
                'tariftangki.id',
                'tariftangki.parent_id',
                'tariftangki.upahsupirtangki',
                'tariftangki.tujuan',
                'tariftangki.penyesuaian',
                'tariftangki.statusaktif',
                'tariftangki.statusaktif_text',
                'tariftangki.kota_id',
                'tariftangki.kotaId',
                'tariftangki.tglmulaiberlaku',
                'tariftangki.statuspenyesuaianharga',
                'tariftangki.statuspenyesuaianharga_text',
                'tariftangki.statuspostingtnl',
                'tariftangki.statuspostingtnl_text',
                'tariftangki.keterangan',
                'tariftangki.nominal',
                'tariftangki.modifiedby',
                'tariftangki.created_at',
                'tariftangki.updated_at',
                'tariftangki.statusaktif_id'
            );
        return $query2;
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##tempAB' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('parent_id', 200)->nullable();
            $table->longText('upahsupirtangki')->nullable();
            $table->string('tujuan', 200)->nullable();
            $table->string('penyesuaian', 200)->nullable();
            $table->string('statusaktif')->nullable();
            $table->longText('statusaktif_text')->nullable();
            $table->string('kota_id')->nullable();
            $table->bigInteger('kotaId')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->string('statuspenyesuaianharga')->nullable();
            $table->longText('statuspenyesuaianharga_text')->nullable();
            $table->longText('statuspostingtnl')->nullable();
            $table->longText('statuspostingtnl_text')->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->bigInteger('statusaktif_id')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = $this->selectColumns();

        $this->sort($query); 
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'parent_id',
            'upahsupirtangki',
            'tujuan',
            'penyesuaian',
            'statusaktif',
            'statusaktif_text',
            'kota_id',
            'kotaId',
            'tglmulaiberlaku',
            'statuspenyesuaianharga',
            'statuspenyesuaianharga_text',
            'statuspostingtnl',
            'statuspostingtnl_text',
            'keterangan',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at',
            'statusaktif_id'
        ], $models);

        return  $temp;
    }

    public function findAll($id)
    {
        $query = DB::table('tariftangki')->from(DB::raw("tariftangki with (readuncommitted)"))
            ->select(
                'tariftangki.id',
                DB::raw("(case when tariftangki.parent_id=0 then null else tariftangki.parent_id end) as parent_id"),
                'parent.tujuan as parent',
                db::raw("isnull(kotadari.keterangan,'')+(case when isnull(kotasampai.keterangan,'')='' then '' else ' - ' +isnull(kotasampai.keterangan,'') end)+ 
                (case when isnull(upahsupirtangki.penyesuaian,'')='' then '' else ' ( ' +isnull(upahsupirtangki.penyesuaian,'')+ ' ) ' end) as upahsupirtangki
                "),
                'kotadari.keterangan as dari',
                'kotasampai.keterangan as sampai',
                'upahsupirtangki.penyesuaian as penyesuaianupah',
                'upahsupirtangki.id as upahsupirtangki_id',
                DB::raw("TRIM(tariftangki.tujuan) as tujuan"),
                'tariftangki.penyesuaian',
                'tariftangki.statusaktif',
                DB::raw("(case when tariftangki.kota_id=0 then null else tariftangki.kota_id end) as kota_id"),
                'kota.keterangan as kota',
                'tariftangki.tglmulaiberlaku',
                'tariftangki.nominal',
                'tariftangki.statuspenyesuaianharga',
                'tariftangki.keterangan',
                'param_statusaktif.text as statusaktifnama',
                'param_statuspnyharga.text as statuspenyesuaianharganama'
            )

            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tariftangki.kota_id', '=', 'kota.id')
            ->leftJoin(DB::raw("tariftangki as parent with (readuncommitted)"), 'tariftangki.parent_id', '=', 'parent.id')
            ->leftJoin(DB::raw("upahsupirtangki as upahsupirtangki with (readuncommitted)"), 'upahsupirtangki.tariftangki_id', '=', 'tariftangki.id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupirtangki.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupirtangki.kotasampai_id')
            ->leftJoin(DB::raw("parameter as param_statusaktif with (readuncommitted)"), 'tariftangki.statusaktif', '=', 'param_statusaktif.id')
            ->leftJoin(DB::raw("parameter as param_statuspnyharga with (readuncommitted)"), 'tariftangki.statuspenyesuaianharga', '=', 'param_statuspnyharga.id')
            ->where('tariftangki.id', $id);

        $data = $query->first();
        return $data;
    }


    public function export($dari, $sampai)
    {
        
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table('tariftangki')->from(DB::raw("tariftangki with (readuncommitted)"))
            ->select(
                'tariftangki.id',
                'parent.tujuan as parent_id',
                db::raw("isnull(kotadari.keterangan,'')+(case when isnull(kotasampai.keterangan,'')='' then '' else ' - ' +isnull(kotasampai.keterangan,'') end)+ 
                (case when isnull(upahsupirtangki.penyesuaian,'')='' then '' else ' ( ' +isnull(upahsupirtangki.penyesuaian,'')+ ' ) ' end) as upahsupirtangki
                "),
                DB::raw("TRIM(tariftangki.tujuan) as tujuan"),
                'tariftangki.penyesuaian',
                'statusaktif.text as statusaktif',
                DB::raw("(case when tariftangki.kota_id=0 then null else tariftangki.kota_id end) as kota_id"),
                'kota.keterangan as kota',
                'tariftangki.tglmulaiberlaku',
                'tariftangki.nominal',
                'penyesuaianharga.text as statuspenyesuaianharga',
                'tariftangki.keterangan',
                DB::raw("'Laporan Tarif Tangki' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )

            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tariftangki.kota_id', '=', 'kota.id')
            ->leftJoin(DB::raw("tariftangki as parent with (readuncommitted)"), 'tariftangki.parent_id', '=', 'parent.id')
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'tariftangki.statusaktif', '=', 'statusaktif.id')
            ->leftJoin(DB::raw("parameter as penyesuaianharga with (readuncommitted)"), 'tariftangki.statuspenyesuaianharga', '=', 'penyesuaianharga.id')
            ->leftJoin(DB::raw("upahsupirtangki as upahsupirtangki with (readuncommitted)"), 'upahsupirtangki.tariftangki_id', '=', 'tariftangki.id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupirtangki.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupirtangki.kotasampai_id')
            ->whereBetween('tariftangki.tglmulaiberlaku', [date('Y-m-d', strtotime($dari)), date('Y-m-d', strtotime($sampai))]);


        $data = $query->get();
        return $data;
    }

    
    public function cekvalidasihapus($id)
    {
        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.tariftangki_id'
            )
            ->where('a.tariftangki_id', '=', $id)
            ->first();
        if (isset($suratPengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];
            goto selesai;
        }

        $upahSupir = DB::table('upahsupirtangki')
            ->from(
                DB::raw("upahsupirtangki as a with (readuncommitted)")
            )
            ->select(
                'a.tariftangki_id'
            )
            ->where('a.tariftangki_id', '=', $id)
            ->first();
        if (isset($upahSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Supir tangki',
            ];
            goto selesai;
        }


        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }

    public function processStore(array $data): TarifTangki
    {
        $tarifTangki = new TarifTangki();
        $tarifTangki->parent_id = $data['parent_id'] ?? '';
        $tarifTangki->tujuan = $data['tujuan'];
        $tarifTangki->penyesuaian = $data['penyesuaian'] ?? '';
        $tarifTangki->statusaktif = $data['statusaktif'];
        $tarifTangki->kota_id = $data['kota_id'];
        $tarifTangki->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
        $tarifTangki->statuspenyesuaianharga = $data['statuspenyesuaianharga'];
        $tarifTangki->statuspostingtnl = $data['statuspostingtnl'];
        $tarifTangki->keterangan = $data['keterangan'];
        $tarifTangki->nominal = $data['nominal'];
        $tarifTangki->tas_id = $data['tas_id'] ?? '';
        $tarifTangki->modifiedby = auth('api')->user()->user;
        $tarifTangki->info = html_entity_decode(request()->info);

        if (!$tarifTangki->save()) {
            throw new \Exception("Error storing tarif.");
        }
        $upahsupirtangki_id = $data['upahsupirtangki_id'] ?? 0;
        if ($upahsupirtangki_id != 0) {
            $datadetailsUpahSupir = (new UpahSupirTangki())->processUpdateTarif([
                'tariftangki_id' => $tarifTangki->id,
                'id' => $upahsupirtangki_id,
            ]);
        }

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($tarifTangki->getTable()),
            'postingdari' => 'ENTRY TARIF TANGKI',
            'idtrans' => $tarifTangki->id,
            'nobuktitrans' => $tarifTangki->id,
            'aksi' => 'ENTRY',
            'datajson' => $tarifTangki->toArray(),
            'modifiedby' => $tarifTangki->modifiedby
        ]);
        return $tarifTangki;
    }


    public function processUpdate(TarifTangki $tarifTangki, array $data): TarifTangki
    {

        $tarifTangki->parent_id = $data['parent_id'] ?? '';
        $tarifTangki->tujuan = $data['tujuan'];
        $tarifTangki->penyesuaian = $data['penyesuaian'] ?? '';
        $tarifTangki->statusaktif = $data['statusaktif'];
        $tarifTangki->kota_id = $data['kota_id'];
        $tarifTangki->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
        $tarifTangki->statuspenyesuaianharga = $data['statuspenyesuaianharga'];
        $tarifTangki->keterangan = $data['keterangan'];
        $tarifTangki->nominal = $data['nominal'];
        $tarifTangki->info = html_entity_decode(request()->info);

        if (!$tarifTangki->save()) {
            throw new \Exception("Error updating tarif.");
        }

        $upahsupirtangki_id = $data['upahsupirtangki_id'] ?? 0;
        if ($upahsupirtangki_id != 0) {
            $datadetailsUpahSupir = (new UpahSupirTangki())->processUpdateTarif([
                'tariftangki_id' => $tarifTangki->id,
                'id' => $upahsupirtangki_id,
            ]);
        }

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($tarifTangki->getTable()),
            'postingdari' => 'EDIT TARIF TANGKI',
            'idtrans' => $tarifTangki->id,
            'nobuktitrans' => $tarifTangki->id,
            'aksi' => 'EDIT',
            'datajson' => $tarifTangki->toArray(),
            'modifiedby' => $tarifTangki->modifiedby
        ]);

        return $tarifTangki;
    }


    public function processDestroy($id): TarifTangki
    {
        $tarifTangki = new TarifTangki();
        $tarifTangki = $tarifTangki->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tarifTangki->getTable()),
            'postingdari' => 'DELETE TARIF TANGKI',
            'idtrans' => $tarifTangki->id,
            'nobuktitrans' => $tarifTangki->id,
            'aksi' => 'DELETE',
            'datajson' => $tarifTangki->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $tarifTangki;
    }



    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Tarif = TarifTangki::find($data['Id'][$i]);

            $Tarif->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            // dd($Tarif);
            if ($Tarif->save()) {

                (new LogTrail())->processStore([

                    'namatabel' => strtoupper($Tarif->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF TARIF TANGKI',
                    'idtrans' => $Tarif->id,
                    'nobuktitrans' => $Tarif->id,
                    'aksi' => $aksi,
                    'datajson' => $Tarif->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $Tarif;
    }

    public function processApprovalaktif(array $data)
    {
        $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Tarif = TarifTangki::find($data['Id'][$i]);

            $Tarif->statusaktif = $statusaktif->id;
            $aksi = $statusaktif->text;

            // dd($Tarif);
            if ($Tarif->save()) {

                (new LogTrail())->processStore([

                    'namatabel' => strtoupper($Tarif->getTable()),
                    'postingdari' => 'APPROVAL AKTIF TARIF TANGKI',
                    'idtrans' => $Tarif->id,
                    'nobuktitrans' => $Tarif->id,
                    'aksi' => $aksi,
                    'datajson' => $Tarif->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $Tarif;
    }
}
