<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReminderEmail extends MyModel
{
    use HasFactory;

    protected $table = 'reminderemail';

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
            DB::raw("reminderemail with (readuncommitted)")
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
            DB::raw("reminderemail with (readuncommitted)")
        );

        $query = $this->selectColumns($query);
        $query->where('reminderemail.id', $id);

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            'reminderemail.id',
            'reminderemail.keterangan',
            'reminderemail.statusaktif',
            'statusaktif.memo as statusaktif_memo',
            'reminderemail.modifiedby',
            'reminderemail.created_at',
            'reminderemail.updated_at'

        )
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'reminderemail.statusaktif', 'statusaktif.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('keterangan', 50)->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('statusaktif_memo', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'keterangan', 'statusaktif', 'statusaktif_memo', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                            if ($filters['field'] == 'statusaktif_memo') {
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
                                if ($filters['field'] == 'statusaktif_memo') {
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

    public function processStore(array $data, ReminderEmail $reminderemail): ReminderEmail
    {
        // $reminderemail = new ReminderEmail();
        // dd($reminderemail);
        $reminderemail->keterangan = $data['keterangan'];
        $reminderemail->statusaktif = $data['statusaktif'];
        $reminderemail->tas_id = $data['tas_id'] ?? '';
        $reminderemail->modifiedby = auth('api')->user()->user;
        $reminderemail->info = html_entity_decode(request()->info);
        // $detailmemo = [];
        // for ($i = 0; $i < count($data['key']); $i++) {
        //     $value = ($data['value'][$i] != null) ? $data['value'][$i] : "";
        //     $datadetailmemo = [
        //         $data['key'][$i] => $value,
        //     ];
        //     $detailmemo = array_merge($detailmemo, $datadetailmemo);
        // }
        // $reminderemail->memo = json_encode($detailmemo);
        // dd('test');
        if (!$reminderemail->save()) {
            throw new \Exception('Error storing Reminder Email.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $reminderemail->getTable(),
            'postingdari' => 'ENTRY Reminder Email',
            'idtrans' => $reminderemail->id,
            'nobuktitrans' => $reminderemail->id,
            'aksi' => 'ENTRY',
            'datajson' => $reminderemail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        // $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        // $data['tas_id'] = $reminderemail->id;

        // if ($cekStatusPostingTnl->text == 'POSTING TNL') {
        //     $this->saveToTnl('ReminderEmail', 'add', $data);
        // }

        return $reminderemail;
    }

    public function processUpdate(ReminderEmail $reminderemail, array $data): ReminderEmail
    {
        $reminderemail->keterangan = $data['keterangan'];
        $reminderemail->statusaktif = $data['statusaktif'];
        $reminderemail->modifiedby = auth('api')->user()->user;
        $reminderemail->info = html_entity_decode(request()->info);
        // $detailmemo = [];
        // for ($i = 0; $i < count($data['key']); $i++) {
        //     $value = ($data['value'][$i] != null) ? $data['value'][$i] : "";
        //     $datadetailmemo = [
        //         $data['key'][$i] => $value,
        //     ];
        //     $detailmemo = array_merge($detailmemo, $datadetailmemo);
        // }
        // $reminderemail->memo = json_encode($detailmemo);
        // dd('test');
        if (!$reminderemail->save()) {
            throw new \Exception('Error updating Reminder Email.');
        }
        
        (new LogTrail())->processStore([
            'namatabel' => $reminderemail->getTable(),
            'postingdari' => 'EDIT Reminder Email',
            'idtrans' => $reminderemail->id,
            'nobuktitrans' => $reminderemail->id,
            'aksi' => 'EDIT',
            'datajson' => $reminderemail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $reminderemail;
    }


    public function processDestroy(ReminderEmail $reminderEmail): ReminderEmail
    {
        $reminderEmail->lockAndDestroy($reminderEmail->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($reminderEmail->getTable()),
            'postingdari' => 'DELETE Reminder Email',
            'idtrans' => $reminderEmail->id,
            'nobuktitrans' => $reminderEmail->id,
            'aksi' => 'DELETE',
            'datajson' => $reminderEmail->toArray(),
            'modifiedby' => $reminderEmail->modifiedby
        ]);

        return $reminderEmail;
    }


    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $reminderEmail = ReminderEmail::find($data['Id'][$i]);

            $reminderEmail->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($reminderEmail->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($reminderEmail->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF REMINDER EMAIL',
                    'idtrans' => $reminderEmail->id,
                    'nobuktitrans' => $reminderEmail->id,
                    'aksi' => $aksi,
                    'datajson' => $reminderEmail->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $reminderEmail;
    }
}
