<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Otobon extends MyModel
{
    use HasFactory;
    protected $table = 'otobon';
    public function get()
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'otobon.id',
                'agen.namaagen as agen',
                'container.keterangan as container',
                'otobon.nominal',
                'otobon.modifiedby',
                'otobon.created_at',
                'otobon.updated_at',
                DB::raw("'Laporan Otobon' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'otobon.agen_id', 'agen.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'otobon.container_id', 'container.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table('otobon')->from(DB::raw("otobon with (readuncommitted)"))
            ->select(
                'otobon.id',
                'otobon.agen_id',
                'agen.namaagen as agen',
                'otobon.container_id',
                'container.keterangan as container',
                'otobon.nominal'
            )
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'agen.id', 'otobon.agen_id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'container.id', 'otobon.container_id')
            ->where('otobon.id', $id)
            ->first();

        return $query;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw("otobon with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "otobon.id,
                    agen.namaagen as agen,
                    container.keterangan as container,
                    otobon.nominal,            
                    otobon.modifiedby,
                    otobon.created_at,
                    otobon.updated_at"
                )
            )
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'otobon.agen_id', 'agen.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'otobon.container_id', 'container.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('agen', 1000)->nullable();
            $table->string('container', 50)->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'agen', 'container', 'nominal', 'modifiedby', 'created_at', 'updated_at'], $models);

        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'agen') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'container') {
            return $query->orderBy('container.keterangan', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'agen') {
                            $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'container') {
                            $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format(otobon.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'agen') {
                                $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'container') {
                                $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format(otobon.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data): Otobon
    {
        $otobon = new Otobon();
        $otobon->agen_id = $data['agen_id'];
        $otobon->container_id = $data['container_id'];
        $otobon->nominal = $data['nominal'];
        $otobon->modifiedby = auth('api')->user()->name;
        $otobon->info = html_entity_decode(request()->info);

        if (!$otobon->save()) {
            throw new \Exception("Error storing otobon.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($otobon->getTable()),
            'postingdari' => 'ENTRY OTOBON',
            'idtrans' => $otobon->id,
            'nobuktitrans' => $otobon->id,
            'aksi' => 'ENTRY',
            'datajson' => $otobon->toArray(),
            'modifiedby' => $otobon->modifiedby
        ]);

        return $otobon;
    }

    public function processUpdate(Otobon $otobon, array $data): Otobon
    {
        $otobon->agen_id = $data['agen_id'];
        $otobon->container_id = $data['container_id'];
        $otobon->nominal = $data['nominal'];
        $otobon->modifiedby = auth('api')->user()->name;
        $otobon->info = html_entity_decode(request()->info);

        if (!$otobon->save()) {
            throw new \Exception("Error update otobon.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($otobon->getTable()),
            'postingdari' => 'EDIT OTOBON',
            'idtrans' => $otobon->id,
            'nobuktitrans' => $otobon->id,
            'aksi' => 'EDIT',
            'datajson' => $otobon->toArray(),
            'modifiedby' => $otobon->modifiedby
        ]);

        return $otobon;
    }
    public function processDestroy($id): Otobon
    {
        $otobon = new Otobon();
        $otobon = $otobon->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($otobon->getTable()),
            'postingdari' => 'DELETE OTOBON',
            'idtrans' => $otobon->id,
            'nobuktitrans' => $otobon->id,
            'aksi' => 'DELETE',
            'datajson' => $otobon->toArray(),
            'modifiedby' => $otobon->modifiedby
        ]);

        return $otobon;
    }
}
