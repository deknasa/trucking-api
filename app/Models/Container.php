<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Container extends MyModel
{
    use HasFactory;

    protected $table = 'container';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function cekvalidasihapus($id)
    {
        // cek sudah ada container


        $tarif = DB::table('tarifrincian')
            ->from(
                DB::raw("tarifrincian as a with (readuncommitted)")
            )
            ->select(
                'a.container_id'
            )
            ->where('a.container_id', '=', $id)
            ->first();

        if (isset($tarif)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Tarif',
            ];

            goto selesai;
        }

        $upahSupir = DB::table('upahsupirrincian')
            ->from(
                DB::raw("upahsupirrincian as a with (readuncommitted)")
            )
            ->select(
                'a.container_id'
            )
            ->where('a.container_id', '=', $id)
            ->first();

        if (isset($upahSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Supir',
            ];

            goto selesai;
        }

        $upahRitasi = DB::table('upahritasirincian')
            ->from(
                DB::raw("upahritasirincian as a with (readuncommitted)")
            )
            ->select(
                'a.container_id'
            )
            ->where('a.container_id', '=', $id)
            ->first();

        if (isset($upahRitasi)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Ritasi',
            ];

            goto selesai;
        }

        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.container_id'
            )
            ->where('a.container_id', '=', $id)
            ->first();

        if (isset($suratPengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];

            goto selesai;
        }

        $orderanTrucking = DB::table('orderantrucking')
            ->from(
                DB::raw("orderantrucking as a with (readuncommitted)")
            )
            ->select(
                'a.container_id'
            )
            ->where('a.container_id', '=', $id)
            ->first();

        if (isset($orderanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Orderan Trucking',
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
                'container.id',
                'container.kodecontainer',
                'container.keterangan',
                'container.nominalsumbangan',
                'parameter.memo as statusaktif',
                'container.modifiedby',
                'container.created_at',
                'container.updated_at',
                DB::raw("'Laporan Container' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'container.statusaktif', '=', 'parameter.id');




        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('container.statusaktif', '=', $statusaktif->id);
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
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.kodecontainer,
            $this->table.keterangan,
            'parameter.text as statusaktif',
            $this->table.nominalsumbangan,

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )

            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'container.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodecontainer', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('statusaktif', 500)->nullable();
            $table->bigInteger('nominalsumbangan')->nullable();

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
        DB::table($temp)->insertUsing(['id', 'kodecontainer', 'keterangan', 'statusaktif', 'nominalsumbangan', 'modifiedby', 'created_at', 'updated_at'], $models);

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
                                $query = $query->where('parameter.text', '=', "$filters[data]");
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
                                    $query = $query->orWhere('parameter.text', '=', "$filters[data]");
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

    public function processStore(array $data, Container $container): Container
    {
        // $container = new Container();
        $container->kodecontainer = strtoupper($data['kodecontainer']);
        $container->keterangan = strtoupper($data['keterangan']) ?? '';
        $container->nominalsumbangan = $data['nominalsumbangan'];
        $container->statusaktif = $data['statusaktif'];
        $container->tas_id = $data['tas_id'] ?? '';
        $container->modifiedby = auth('api')->user()->user;
        $container->info = html_entity_decode(request()->info);

        if (!$container->save()) {
            throw new \Exception('Error storing container.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($container->getTable()),
            'postingdari' => 'ENTRY CONTAINER',
            'idtrans' => $container->id,
            'nobuktitrans' => $container->id,
            'aksi' => 'ENTRY',
            'datajson' => $container->toArray(),
            'modifiedby' => $container->modifiedby
        ]);

        return $container;
    }

    public function processUpdate(Container $container, array $data): Container
    {
        $container->kodecontainer = $data['kodecontainer'];
        $container->keterangan = $data['keterangan'] ?? '';
        $container->nominalsumbangan = $data['nominalsumbangan'];
        $container->statusaktif = $data['statusaktif'];
        $container->modifiedby = auth('api')->user()->user;
        $container->info = html_entity_decode(request()->info);

        if (!$container->save()) {
            throw new \Exception('Error updating conta$container.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($container->getTable()),
            'postingdari' => 'EDIT CONTAINER',
            'idtrans' => $container->id,
            'nobuktitrans' => $container->id,
            'aksi' => 'EDIT',
            'datajson' => $container->toArray(),
            'modifiedby' => $container->modifiedby
        ]);

        return $container;
    }

    public function processDestroy(Container $container): Container
    {
        // $container = new Container();
        $container = $container->lockAndDestroy($container->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($container->getTable()),
            'postingdari' => 'DELETE CONTAINER',
            'idtrans' => $container->id,
            'nobuktitrans' => $container->id,
            'aksi' => 'DELETE',
            'datajson' => $container->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $container;
    }
    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $container = Container::find($data['Id'][$i]);

            $container->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($container->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($container->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF CONTAINER',
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
