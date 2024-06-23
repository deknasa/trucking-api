<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CcEmail extends MyModel
{
    use HasFactory;


    protected $table = 'ccemail';

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

        $query = DB::table($this->table)->from(
            DB::raw("ccemail with (readuncommitted)")
        );

        $query = $this->selectColumns($query);

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

        $query = DB::table($this->table)->from(
            DB::raw("ccemail with (readuncommitted)")
        );

        $query = $this->selectColumns($query);
        $query->where('ccemail.id', $id);

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            'ccemail.id',
            'ccemail.nama',
            'ccemail.email',
            'ccemail.statusaktif',
            'statusaktif.memo as statusaktif_memo',
            'ccemail.karyawan_id',
            'karyawan.namakaryawan',
            'ccemail.reminderemail_id',
            'reminderemail.keterangan as reminderemail',
            'ccemail.modifiedby',
            'ccemail.created_at',
            'ccemail.updated_at'

        )
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'ccemail.statusaktif', 'statusaktif.id')
            ->leftJoin(DB::raw("karyawan with (readuncommitted)"), 'ccemail.karyawan_id', 'karyawan.id')
            ->leftJoin(DB::raw("reminderemail with (readuncommitted)"), 'ccemail.reminderemail_id', 'reminderemail.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nama', 50)->nullable();
            $table->string('email', 50)->unique();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('statusaktif_memo', 1000)->nullable();
            $table->string('karyawan_id', 50)->nullable();
            $table->string('namakaryawan', 50)->nullable();
            $table->string('reminderemail_id', 50)->nullable();
            $table->string('reminderemail', 50)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'nama', 'email', 'statusaktif', 'statusaktif_memo', 'karyawan_id', 'namakaryawan', 'reminderemail_id', 'reminderemail', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'statusaktif_memo') {
            return $query->orderBy('statusaktif.text', $this->params['sortOrder']);
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
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'namakaryawan') {
                                $query = $query->where('karyawan.namakaryawan', '=', $filters['data']);
                            } else if ($filters['field'] == 'reminderemail') {
                                $query = $query->where('reminderemail.keterangan', '=', $filters['data']);
                            } else if ($filters['field'] == 'statusaktif_memo') {
                                $query = $query->where('statusaktif.text', '=', $filters['data']);
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'namakaryawan') {
                                    $query = $query->orWhere('karyawan.namakaryawan', '=', "$filters[data]");
                                } else if ($filters['field'] == 'reminderemail') {
                                    $query = $query->orWhere('reminderemail.keterangan', '=', $filters['data']);
                                } else if ($filters['field'] == 'statusaktif_memo') {
                                    $query = $query->orWhere('statusaktif.text', '=', $filters['data']);
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


    public function processStore(array $data, CcEmail $ccEmail): CcEmail
    {
        // $ccEmail = new CcEmail();
        $ccEmail->nama = $data['nama'];
        $ccEmail->email = $data['email'];
        $ccEmail->statusaktif = $data['statusaktif'];
        $ccEmail->reminderemail_id = $data['reminderemail_id'];
        $ccEmail->tas_id = $data['tas_id'] ?? '';
        $ccEmail->modifiedby = auth('api')->user()->name;
        $ccEmail->info = html_entity_decode(request()->info);

        if (!$ccEmail->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($ccEmail->getTable()),
            'postingdari' => 'ENTRY To Email',
            'idtrans' => $ccEmail->id,
            'nobuktitrans' => $ccEmail->id,
            'aksi' => 'ENTRY',
            'datajson' => $ccEmail->toArray(),
            'modifiedby' => $ccEmail->modifiedby
        ]);

        return $ccEmail;
    }

    public function processUpdate(CcEmail $ccEmail, array $data): CcEmail
    {
        $ccEmail->nama = $data['nama'];
        $ccEmail->email = $data['email'];
        $ccEmail->statusaktif = $data['statusaktif'];
        $ccEmail->reminderemail_id = $data['reminderemail_id'];
        $ccEmail->modifiedby = auth('api')->user()->name;
        $ccEmail->info = html_entity_decode(request()->info);

        if (!$ccEmail->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($ccEmail->getTable()),
            'postingdari' => 'EDIT TO Email',
            'idtrans' => $ccEmail->id,
            'nobuktitrans' => $ccEmail->id,
            'aksi' => 'EDIT',
            'datajson' => $ccEmail->toArray(),
            'modifiedby' => $ccEmail->modifiedby
        ]);

        return $ccEmail;
    }

    public function processDestroy(CcEmail $ccEmail): CcEmail
    {
        $ccEmail->lockAndDestroy($ccEmail->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($ccEmail->getTable()),
            'postingdari' => 'DELETE SATUAN',
            'idtrans' => $ccEmail->id,
            'nobuktitrans' => $ccEmail->id,
            'aksi' => 'DELETE',
            'datajson' => $ccEmail->toArray(),
            'modifiedby' => $ccEmail->modifiedby
        ]);

        return $ccEmail;
    }
    
    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $ccEmail = CcEmail::find($data['Id'][$i]);

            $ccEmail->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($ccEmail->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($ccEmail->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF CC EMAIL',
                    'idtrans' => $ccEmail->id,
                    'nobuktitrans' => $ccEmail->id,
                    'aksi' => $aksi,
                    'datajson' => $ccEmail->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $ccEmail;
    }
}
