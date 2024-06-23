<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Akuntansi extends MyModel
{
    use HasFactory;

    protected $table = 'akuntansi';

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
                'akuntansi.id',
                'akuntansi.kodeakuntansi',
                'akuntansi.keterangan',
                'parameter.memo as statusaktif',
                'akuntansi.modifiedby',
                'akuntansi.created_at',
                'akuntansi.updated_at',
                DB::raw("'Laporan Akuntansi' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'akuntansi.statusaktif', 'parameter.id');

            
            $this->filter($query);
            

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('akuntansi.statusaktif', '=', $statusaktif->id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        

        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
        return $data;
       

    }

    public function findAll($id)
    {
        return $akuntansi = Akuntansi::select('akuntansi.*','parameter.text as statusaktifnama',)
        ->leftJoin('parameter', 'akuntansi.statusaktif', '=', 'parameter.id')
        ->where('akuntansi.id', $id)->first();
    }
    
    public function default()
    {
        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama')->nullable();
        });
        
        $statusaktif = Parameter::from(db::Raw("parameter with (readuncommitted)"))->select(
            'id',
            'text'
        )
        ->where('grp', '=', 'STATUS AKTIF')
        ->where('subgrp', '=', 'STATUS AKTIF')
        ->where('default', '=', 'YA')
        ->first();
        
        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id,"statusaktifnama" => $statusaktif->text]);

        $query = DB::table($tempdefault)->from(DB::raw($tempdefault))
            ->select(
                'statusaktif',
                'statusaktifnama',
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
            "$this->table.kodeakuntansi",
            "$this->table.keterangan",
            "parameter.text as statusaktif",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'akuntansi.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodeakuntansi', 100)->nullable(); 
            $table->longText('keterangan')->nullable();
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
            'kodeakuntansi',
            'keterangan',
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
                        } elseif ($filters['field'] == 'akuntansi') {
                            $query = $query->where('kodeakuntansi', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'akuntansi') {
                            $query = $query->where('keterangan', 'LIKE', "%$filters[data]%");
                        }elseif ($filters['field'] == 'akuntansi') {
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
                            } elseif ($filters['field'] == 'akuntansi') {
                                $query = $query->orWhere('kodeakuntansi', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'akuntansi') {
                                $query = $query->orWhere('keterangan', 'LIKE', "%$filters[data]%");
                            }else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
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

    public function processStore(array $data, Akuntansi $akuntansi): Akuntansi
    {
        // $akuntansi = new Akuntansi();
        $akuntansi->kodeakuntansi = $data['kodeakuntansi'];
        $akuntansi->keterangan = $data['keterangan'];
        $akuntansi->statusaktif = $data['statusaktif'];
        $akuntansi->modifiedby = auth('api')->user()->user;
        $akuntansi->info = html_entity_decode(request()->info);
        $akuntansi->tas_id = $data['tas_id'] ?? '';

        if (!$akuntansi->save()) {
            throw new \Exception('Error storing akuntansi.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $akuntansi->getTable(),
            'postingdari' => 'ENTRY akuntansi',
            'idtrans' => $akuntansi->id,
            'nobuktitrans' => $akuntansi->id,
            'aksi' => 'ENTRY',
            'datajson' => $akuntansi->toArray(),
        ]);

        return $akuntansi;
    }

    public function processUpdate(Akuntansi $akuntansi, array $data): Akuntansi
    {
        $akuntansi->kodeakuntansi = $data['kodeakuntansi'];
        $akuntansi->keterangan = $data['keterangan'];
        $akuntansi->statusaktif = $data['statusaktif'];
        $akuntansi->modifiedby = auth('api')->user()->user;
        $akuntansi->info = html_entity_decode(request()->info);

        if (!$akuntansi->save()) {
            throw new \Exception('Error updating akuntansi.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $akuntansi->getTable(),
            'postingdari' => 'EDIT akuntansi',
            'idtrans' => $akuntansi->id,
            'nobuktitrans' => $akuntansi->id,
            'aksi' => 'EDIT',
            'datajson' => $akuntansi->toArray(),
        ]);

        return $akuntansi;
    }

    public function processDestroy(Akuntansi $akuntansi): Akuntansi
    {
        // $akuntansi = new Akuntansi();
        $akuntansi = $akuntansi->lockAndDestroy($akuntansi->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($akuntansi->getTable()),
            'postingdari' => 'DELETE akuntansi',
            'idtrans' => $akuntansi->id,
            'nobuktitrans' => $akuntansi->id,
            'aksi' => 'DELETE',
            'datajson' => $akuntansi->toArray(),
        ]);

        return $akuntansi;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $akuntansi = Akuntansi::find($data['Id'][$i]);

            $akuntansi->statusaktif = $statusnonaktif->id;
            $akuntansi->modifiedby = auth('api')->user()->name;
            $akuntansi->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($akuntansi->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($akuntansi->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF akuntansi',
                    'idtrans' => $akuntansi->id,
                    'nobuktitrans' => $akuntansi->id,
                    'aksi' => $aksi,
                    'datajson' => $akuntansi->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $akuntansi;
    }
}
