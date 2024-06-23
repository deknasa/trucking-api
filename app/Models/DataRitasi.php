<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataRitasi extends MyModel
{
    use HasFactory;
    protected $table = 'dataritasi';

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
                'dataritasi.id',
                'dataritasi.statusritasi as statusritasi_id',
                'statusritasi.text as statusritasi',
                'dataritasi.nominal',
                'parameter.memo as statusaktif',
                'dataritasi.modifiedby',
                'dataritasi.created_at',
                'dataritasi.updated_at',
                DB::raw("'Laporan Data Ritasi' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'dataritasi.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusritasi with (readuncommitted)"), 'dataritasi.statusritasi', 'statusritasi.id');


        $this->filter($query);


        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('dataritasi.statusaktif', '=', $statusaktif->id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;


        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
        return $data;
    }

    public function processStore(array $data, DataRitasi $dataritasi): DataRitasi
    {
        // $cabang = new Cabang();
        $dataritasi->statusritasi = $data['statusritasi'];
        $dataritasi->nominal = $data['nominal'];
        $dataritasi->statusaktif = $data['statusaktif'];
        $dataritasi->tas_id = $data['tas_id'] ?? '';
        $dataritasi->modifiedby = auth('api')->user()->user;
        $dataritasi->info = html_entity_decode(request()->info);
        if (!$dataritasi->save()) {
            throw new \Exception('Error storing cabang.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $dataritasi->getTable(),
            'postingdari' => 'ENTRY DATA RITASI',
            'idtrans' => $dataritasi->id,
            'nobuktitrans' => $dataritasi->id,
            'aksi' => 'ENTRY',
            'datajson' => $dataritasi->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        // $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        // $data['tas_id'] = $cabang->id;

        // if ($cekStatusPostingTnl->text == 'POSTING TNL') {
        //     $this->saveToTnl('cabang', 'add', $data);
        // }

        return $dataritasi;
    }

    public function processUpdate(DataRitasi $dataritasi, array $data): DataRitasi
    {
        $dataritasi->statusritasi = $data['statusritasi'];
        $dataritasi->nominal = $data['nominal'];
        $dataritasi->statusaktif = $data['statusaktif'];
        $dataritasi->modifiedby = auth('api')->user()->user;
        $dataritasi->info = html_entity_decode(request()->info);
        if (!$dataritasi->save()) {
            throw new \Exception('Error updating dataritasi.');
        }
        
        (new LogTrail())->processStore([
            'namatabel' => $dataritasi->getTable(),
            'postingdari' => 'EDIT DATA RITASI',
            'idtrans' => $dataritasi->id,
            'nobuktitrans' => $dataritasi->id,
            'aksi' => 'EDIT',
            'datajson' => $dataritasi->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $dataritasi;
    }

    public function processDestroy(DataRitasi $dataritasi): DataRitasi
    {
        $dataritasi = $dataritasi->lockAndDestroy($dataritasi->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($dataritasi->getTable()),
            'postingdari' => 'DELETE DATA RITASI',
            'idtrans' => $dataritasi->id,
            'nobuktitrans' => $dataritasi->id,
            'aksi' => 'DELETE',
            'datajson' => $dataritasi->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $dataritasi;
    }

    public function findAll($id)
    {
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'dataritasi.id',
                'dataritasi.statusritasi as statusritasi_id',
                'dataritasi.statusritasi as statusritasi',
                'statusritasi.text as statusritasinama',
                'dataritasi.nominal',
                'dataritasi.statusaktif',
                'parameter.text as statusaktifnama',
                'dataritasi.modifiedby',
                'dataritasi.created_at',
                'dataritasi.updated_at',
            )
            ->where('dataritasi.id', $id)
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'dataritasi.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusritasi with (readuncommitted)"), 'dataritasi.statusritasi', 'statusritasi.id');
        return $query->first();
    }


    public function default()
    {

        // $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempdefault, function ($table) {
        //     $table->unsignedBigInteger('statusaktif')->nullable();
        // });

        // $statusaktif=Parameter::from (
        //     db::Raw("parameter with (readuncommitted)")
        // )
        // ->select (
        //     'memo',
        //     'id'
        // )
        // ->where('grp','=','STATUS AKTIF')
        // ->where('subgrp','=','STATUS AKTIF');

        // $datadetail = json_decode($statusaktif->get(), true);

        // $iddefault=0;
        // foreach ($datadetail as $item) {
        //     $memo = json_decode($item['memo'], true);
        //     $default=$memo['DEFAULT'];
        //     if ($default=="YA") {
        //         $iddefault=$item['id'];
        //         DB::table($tempdefault)->insert(["statusaktif" => $iddefault]);
        //     } 
        // }



        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->string('statusaktifnama')->nullable();
            $table->unsignedBigInteger('statusaktif')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'text',
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('DEFAULT', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id, "statusaktifnama" => $statusaktif->text]);




        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktifnama',
                'statusaktif'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            'statusritasi.text as statusritasi',
            "$this->table.nominal",
            "parameter.text as statusaktif",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'dataritasi.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusritasi with (readuncommitted)"), 'dataritasi.statusritasi', 'statusritasi.id');
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('statusritasi')->nullable();
            $table->double('nominal', 15, 2)->nullable();
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
            'statusritasi',
            'nominal',
            'statusaktif',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

        return  $temp;
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
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'statusritasi') {
                            $query = $query->where('statusritasi.text', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'dataritasi') {
                            $query = $query->where('nominal', 'LIKE', "%$filters[data]%");
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
                            } elseif ($filters['field'] == 'statusritasi') {
                                $query = $query->orWhere('statusritasi.text', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'dataritasi') {
                                $query = $query->orWhere('nominal', 'LIKE', "%$filters[data]%");
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

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $dataRitasi = DataRitasi::find($data['Id'][$i]);

            $dataRitasi->statusaktif = $statusnonaktif->id;
            $dataRitasi->modifiedby = auth('api')->user()->name;
            $dataRitasi->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($dataRitasi->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($dataRitasi->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF DATA RITASI',
                    'idtrans' => $dataRitasi->id,
                    'nobuktitrans' => $dataRitasi->id,
                    'aksi' => $aksi,
                    'datajson' => $dataRitasi->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $dataRitasi;
    }
}
