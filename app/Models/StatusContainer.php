<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class StatusContainer extends MyModel
{
    use HasFactory;

    protected $table = 'statuscontainer';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasihapus($id)
    {
        $upahSupirRincian = DB::table('upahsupirrincian')
            ->from(
                DB::raw("upahsupirrincian as a with (readuncommitted)")
            )
            ->select(
                'a.statuscontainer_id'
            )
            ->where('a.statuscontainer_id', '=', $id)
            ->first();
        if (isset($upahSupirRincian)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Supir',
            ];
            goto selesai;
        }

        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.statuscontainer_id'
            )
            ->where('a.statuscontainer_id', '=', $id)
            ->first();
        if (isset($suratPengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
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

    public function get()
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '';
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'statuscontainer.id',
                'statuscontainer.kodestatuscontainer',
                'statuscontainer.keterangan',

                'parameter.memo as statusaktif',

                'statuscontainer.modifiedby',
                'statuscontainer.created_at',
                'statuscontainer.updated_at',
                DB::raw("'Laporan Status Container' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'statuscontainer.statusaktif', '=', 'parameter.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('statuscontainer.statusaktif', '=', $statusaktif->id);
        }
        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {
        $this->setRequestParameters();

        $data = StatusContainer::from(DB::raw("statuscontainer with (readuncommitted)"))
            ->select(
                'statuscontainer.id',
                'statuscontainer.kodestatuscontainer',
                'statuscontainer.keterangan',
                'statuscontainer.statusaktif',
                'parameter.text as statusaktifnama',
                'statuscontainer.modifiedby',
                'statuscontainer.created_at',
                'statuscontainer.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'statuscontainer.statusaktif', '=', 'parameter.id')
            ->where('statuscontainer.id', $id)->first();

        return $data;
    }

    public function default()
    {
        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama', 300)->nullable();
        });

        $statusaktif = Parameter::from(
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

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id, "statusaktifnama" =>$statusaktif->text ?? ""]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.kodestatuscontainer,
            $this->table.keterangan,
            
            'parameter.text as statusaktif',
            
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )
            ->leftJoin('parameter', 'statuscontainer.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodestatuscontainer', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('statusaktif', 500)->nullable();

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
        DB::table($temp)->insertUsing(['id', 'kodestatuscontainer',  'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->where('parameter.text', '=', $filters['data']);
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

    public function processStore(array $data, StatusContainer $statusContainer): StatusContainer
    {
        // $statusContainer = new StatusContainer();
        $statusContainer->kodestatuscontainer = $data['kodestatuscontainer'];
        $statusContainer->keterangan = $data['keterangan'] ?? '';
        $statusContainer->statusaktif = $data['statusaktif'];
        $statusContainer->tas_id = $data['tas_id'] ?? '';
        $statusContainer->modifiedby = auth('api')->user()->user;
        $statusContainer->info = html_entity_decode(request()->info);
        $data['sortname'] = $data['sortname'] ?? 'id';
        $data['sortorder'] = $data['sortorder'] ?? 'asc';

        if (!$statusContainer->save()) {
            throw new \Exception('Error storing status container.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($statusContainer->getTable()),
            'postingdari' => 'ENTRY STATUS CONTAINER',
            'idtrans' => $statusContainer->id,
            'nobuktitrans' => $statusContainer->id,
            'aksi' => 'ENTRY',
            'datajson' => $statusContainer->toArray(),
            'modifiedby' => $statusContainer->modifiedby
        ]);

        return $statusContainer;
    }

    public function processUpdate(StatusContainer $statusContainer, array $data): StatusContainer
    {
        $statusContainer->kodestatuscontainer = $data['kodestatuscontainer'];
        $statusContainer->keterangan = $data['keterangan'] ?? '';
        $statusContainer->statusaktif = $data['statusaktif'];
        $statusContainer->modifiedby = auth('api')->user()->user;
        $statusContainer->info = html_entity_decode(request()->info);

        if (!$statusContainer->save()) {
            throw new \Exception('Error updating sta$statusContainer.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($statusContainer->getTable()),
            'postingdari' => 'EDIT STATUS CONTAINER',
            'idtrans' => $statusContainer->id,
            'nobuktitrans' => $statusContainer->id,
            'aksi' => 'EDIT',
            'datajson' => $statusContainer->toArray(),
            'modifiedby' => $statusContainer->modifiedby
        ]);

        return $statusContainer;
    }

    public function processDestroy(StatusContainer $statusContainer): StatusContainer
    {
        // $statusContainer = new StatusContainer();
        $statusContainer = $statusContainer->lockAndDestroy($statusContainer->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($statusContainer->getTable()),
            'postingdari' => 'DELETE STATUS CONTAINER',
            'idtrans' => $statusContainer->id,
            'nobuktitrans' => $statusContainer->id,
            'aksi' => 'DELETE',
            'datajson' => $statusContainer->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $statusContainer;
    }

    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $container = StatusContainer::find($data['Id'][$i]);

            $container->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($container->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($container->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF STATUS CONTAINER',
                    'idtrans' => $container->id,
                    'nobuktitrans' => $container->id,
                    'aksi' => $aksi,
                    'datajson' => $container->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $container;
    }

    public function processApprovalaktif(array $data)
    {
        $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $container = StatusContainer::find($data['Id'][$i]);

            $container->statusaktif = $statusaktif->id;
            $aksi = $statusaktif->text;

            if ($container->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($container->getTable()),
                    'postingdari' => 'APPROVAL AKTIF STATUS CONTAINER',
                    'idtrans' => $container->id,
                    'nobuktitrans' => $container->id,
                    'aksi' => $aksi,
                    'datajson' => $container->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $container;
    }
}
