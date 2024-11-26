<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stevebauman\Location\Facades\Location;

class Error extends MyModel
{
    use HasFactory;

    protected $table = 'error';

    protected $casts = [
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

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
        ->select(
            'id',
            'kodeerror',
            'keterangan',
            'modifiedby',
            'created_at',
            'updated_at',
            DB::raw("'Laporan Error' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
        );

        $this->totalRows = $query->count();
        dd($this->totalRows);
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->selectColumns($query);
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            DB::raw(
                "$this->table.id,
                $this->table.kodeerror,
                $this->table.keterangan,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at",
            )
        );
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodeerror', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodeerror', 'keterangan', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                        if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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

    public function processStore(array $data): Error
    {
        $error = new Error();
        $error->kodeerror = $data['kodeerror'];
        $error->keterangan = $data['keterangan'];
        $error->modifiedby = auth('api')->user()->user;
        $error->info = html_entity_decode(request()->info);

        if (!$error->save()) {
            throw new \Exception('Error storing error.');
        }
        // $ip = request()->ip();
        // $location = Location::get($ip);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($error->getTable()),
                'postingdari' => 'ENTRY ERROR',
                'idtrans' => $error->id,
                'nobuktitrans' => $error->id,
                'aksi' => 'ENTRY',
                'datajson' => $error->toArray(),
                'modifiedby' => $error->modifiedby
        ]);

        return $error;
    }

    public function processUpdate(Error $error, array $data): Error
    {
        $error->kodeerror = $data['kodeerror'];
        $error->keterangan = $data['keterangan'];
        $error->modifiedby = auth('api')->user()->user;
        $error->info = html_entity_decode(request()->info);
       
        if (!$error->save()) {
            throw new \Exception('Error updating cabang.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($error->getTable()),
            'postingdari' => 'EDIT ERROR',
            'idtrans' => $error->id,
            'nobuktitrans' => $error->id,
            'aksi' => 'EDIT',
            'datajson' => $error->toArray(),
            'modifiedby' => $error->modifiedby
        ]);

        return $error;
    }

    public function processDestroy($id): Error
    {
        $error = new Error();
        $error = $error->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($error->getTable()),
            'postingdari' => 'DELETE ERROR',
            'idtrans' => $error->id,
            'nobuktitrans' => $error->id,
            'aksi' => 'DELETE',
            'datajson' => $error->toArray(),
            'modifiedby' => $error->modifiedby
        ]);

        return $error;
    }

    public function cekKeteranganError($kode) {
        $query = DB::table('error')
        ->select(
            
            DB::raw("ltrim(rtrim(keterangan)) as keterangan")
        )
        ->where('kodeerror', '=',$kode)
        ->first();

        $keterangan=$query->keterangan ?? '';

        return $keterangan;
    }
}
