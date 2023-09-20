<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class MainTypeAkuntansi extends MyModel
{
    use HasFactory;

    protected $table = 'maintypeakuntansi';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {


        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'maintypeakuntansi.id',
                'maintypeakuntansi.kodetype',
                'maintypeakuntansi.order',
                'maintypeakuntansi.keterangantype',
                'maintypeakuntansi.akuntansi_id',
                'akuntansi.kodeakuntansi as akuntansi',
                'parameter.memo as statusaktif',
                'maintypeakuntansi.modifiedby',
                'maintypeakuntansi.created_at',
                'maintypeakuntansi.updated_at',
                DB::raw("'Laporan Main Tipe Akuntansi' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'maintypeakuntansi.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("akuntansi with (readuncommitted)"), 'maintypeakuntansi.akuntansi_id', '=', 'akuntansi.id');


        $this->filter($query);


        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('maintypeakuntansi.statusaktif', '=', $statusaktif->id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;


        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
        return $data;
    }

    public function default()
    {


        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('DEFAULT', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id]);




        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif'
            );

        $data = $query->first();

        return $data;
    }

    public function find($id)
    {
        $this->setRequestParameters();

        $data = MainTypeAkuntansi::from(DB::raw("maintypeakuntansi with (readuncommitted)"))

            ->select(
                'maintypeakuntansi.id',
                'maintypeakuntansi.kodetype',
                'maintypeakuntansi.order',
                'maintypeakuntansi.keterangantype',
                'maintypeakuntansi.akuntansi_id',
                'akuntansi.kodeakuntansi as akuntansi',
                'maintypeakuntansi.statusaktif',
                'maintypeakuntansi.modifiedby',
                'maintypeakuntansi.created_at',
                'maintypeakuntansi.updated_at',
            )

            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'maintypeakuntansi.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("akuntansi with (readuncommitted)"), 'maintypeakuntansi.akuntansi_id', '=', 'akuntansi.id')
            ->where('maintypeakuntansi.id', $id)
            ->first();

        // dd("her");

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            "$this->table.kodetype",
            "$this->table.order",
            "$this->table.keterangantype",
            "$this->table.akuntansi_id",
            "parameter.text as statusaktif",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'maintypeakuntansi.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("akuntansi with (readuncommitted)"), 'maintypeakuntansi.akuntansi_id', '=', 'akuntansi.id');
    }



    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodetype', 100)->nullable();
            $table->integer('order')->nullable();
            $table->longText('keterangantype')->nullable();
            $table->unsignedBigInteger('akuntansi_id')->nullable();
            $table->string('statusaktif', 500)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'kodetype',
            'order',
            'keterangantype',
            'akuntansi_id',
            'statusaktif',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'akuntansi') {
            return $query->orderBy('akuntansi.kodeakuntansi', $this->params['sortOrder']);
        }
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'maintypeakuntansi') {
                            $query = $query->where('kodetype', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'maintypeakuntansi') {
                            $query = $query->where('order', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'akuntansi') {
                            $query = $query->where('akuntansi.kodeakuntansi', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'keterangantype') {
                            $query = $query->where('keterangantype', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%' escape '|'");
                        } else {
                            // $query = $query->whereRaw($this->table . ".".  $filters['field'] ." LIKE '%".str_replace($filters['data'],'[','|[') ."%' escape '|'");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter.text', '=', $filters['data']);
                            } elseif ($filters['field'] == 'maintypeakuntansi') {
                                $query = $query->orWhere('kodetype', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'maintypeakuntansi') {
                                $query = $query->orWhere('order', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'akuntansi') {
                                $query = $query->orWhere('akuntansi.kodeakuntansi', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'akuntansi') {
                                $query = $query->orWhere('keterangan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'keterangantype') {
                                $query = $query->orWhere('keterangantype', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                // $query = $query->OrwhereRaw($this->table . ".".  $filters['field'] ." LIKE '%".str_replace($filters['data'],'[','|[') ."%' escape '|'");
                                // $query = $query->orWhereRaw($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function processStore(array $data): MainTypeAkuntansi
    {
        $maintypeakuntansi = new MainTypeAkuntansi();
        $maintypeakuntansi->kodetype = $data['kodetype'];
        $maintypeakuntansi->order = $data['order'];
        $maintypeakuntansi->keterangantype = $data['keterangantype'];
        $maintypeakuntansi->akuntansi_id = $data['akuntansi_id'];
        $maintypeakuntansi->statusaktif = $data['statusaktif'];
        $maintypeakuntansi->modifiedby = auth('api')->user()->user;
        $maintypeakuntansi->info = html_entity_decode(request()->info);

        if (!$maintypeakuntansi->save()) {
            throw new \Exception('Error storing tipe akuntansi.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $maintypeakuntansi->getTable(),
            'postingdari' => 'ENTRY main tipe akuntansi',
            'idtrans' => $maintypeakuntansi->id,
            'nobuktitrans' => $maintypeakuntansi->id,
            'aksi' => 'ENTRY',
            'datajson' => $maintypeakuntansi->toArray(),
        ]);

        return $maintypeakuntansi;
    }

    public function processUpdate(MainTypeAkuntansi $maintypeakuntansi, array $data): MainTypeAkuntansi
    {
        $maintypeakuntansi->kodetype = $data['kodetype'];
        $maintypeakuntansi->order = $data['order'];
        $maintypeakuntansi->keterangantype = $data['keterangantype'];
        $maintypeakuntansi->akuntansi_id = $data['akuntansi_id'];
        $maintypeakuntansi->statusaktif = $data['statusaktif'];
        $maintypeakuntansi->modifiedby = auth('api')->user()->user;
        $maintypeakuntansi->info = html_entity_decode(request()->info);

        if (!$maintypeakuntansi->save()) {
            throw new \Exception('Error updating akuntansi.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $maintypeakuntansi->getTable(),
            'postingdari' => 'EDIT maintypeakuntansi',
            'idtrans' => $maintypeakuntansi->id,
            'nobuktitrans' => $maintypeakuntansi->id,
            'aksi' => 'EDIT',
            'datajson' => $maintypeakuntansi->toArray(),
        ]);

        return $maintypeakuntansi;
    }

    public function processDestroy($id): MainTypeAkuntansi
    {
        $maintypeakuntansi = new MainTypeAkuntansi();
        $maintypeakuntansi = $maintypeakuntansi->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($maintypeakuntansi->getTable()),
            'postingdari' => 'DELETE maintypeakuntansi',
            'idtrans' => $maintypeakuntansi->id,
            'nobuktitrans' => $maintypeakuntansi->id,
            'aksi' => 'DELETE',
            'datajson' => $maintypeakuntansi->toArray(),
        ]);

        return $maintypeakuntansi;
    }
}
