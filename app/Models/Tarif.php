<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Tarif extends MyModel
{
    use HasFactory;

    protected $table = 'tarif';

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

    public function cekvalidasihapus($id)
    {
        $orderanTrucking = DB::table('orderantrucking')
            ->from(
                DB::raw("orderantrucking as a with (readuncommitted)")
            )
            ->select(
                'a.tarif_id'
            )
            ->where('a.tarif_id', '=', $id)
            ->first();
        if (isset($orderanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Orderan Trucking',
            ];
            goto selesai;
        }
        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.tarif_id'
            )
            ->where('a.tarif_id', '=', $id)
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

        $aktif = request()->aktif ?? '';

        $tempUpahsupir = $this->tempUpahsupir();
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'tarif.id',
                'parent.tujuan as parent_id',
                "B.kotasampai_id as upahsupir_id",
                'tarif.tujuan',
                'tarif.penyesuaian',
                'parameter.memo as statusaktif',
                'sistemton.memo as statussistemton',
                'kota.kodekota as kota_id',
                'tarif.kota_id as kotaId',
                'zona.zona as zona_id',
                'tarif.tglmulaiberlaku',
                'p.memo as statuspenyesuaianharga',
                'tarif.keterangan',
                'tarif.modifiedby',
                'tarif.created_at',
                'tarif.updated_at',
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tarif.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin(DB::raw("$tempUpahsupir as B with (readuncommitted)"), 'tarif.upahsupir_id', '=', "B.id")
            ->leftJoin(DB::raw("tarif as parent with (readuncommitted)"), 'tarif.parent_id', '=', 'parent.id')
            ->leftJoin(DB::raw("parameter AS p with (readuncommitted)"), 'tarif.statuspenyesuaianharga', '=', 'p.id')
            ->leftJoin(DB::raw("parameter AS sistemton with (readuncommitted)"), 'tarif.statussistemton', '=', 'sistemton.id');

        $this->filter($query);

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('tarif.statusaktif', '=', $statusaktif->id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);

        $this->paginate($query);

        $data = $query->get();

        return $data;
    }
    public function tempUpahsupir()
    {
        $tempUpahsupir = '##tempupah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = UpahSupir::from(DB::raw("upahsupir with (readuncommitted)"))
            ->select('upahsupir.id as id', 'kota.keterangan as kotasampai_id')
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kota.id');

        Schema::create($tempUpahsupir, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kotasampai_id', 200)->nullable();
        });
        DB::table($tempUpahsupir)->insertUsing(['id', 'kotasampai_id'], $fetch);

        return $tempUpahsupir;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
             $this->table.tujuan,
             $this->table.penyesuaian,
             parameter.text as statusaktif,
             $this->table.statussistemton,
             kota.kodekota as kota_id,
             zona.zona as zona_id,
             $this->table.tglmulaiberlaku,
             p.text as statuspenyesuaianharga,
             $this->table.modifiedby,
             $this->table.keterangan,
             $this->table.created_at,
             $this->table.updated_at"
            )
        )
            ->leftJoin('parameter', 'tarif.statusaktif', '=', 'parameter.id')
            ->leftJoin('kota', 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin('zona', 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin('parameter AS p', 'tarif.statuspenyesuaianharga', '=', 'p.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('tujuan', 200)->nullable();
            $table->string('penyesuaian', 200)->nullable();
            $table->string('statusaktif')->nullable();
            $table->integer('statussistemton')->length(11)->nullable();
            $table->string('kota_id')->nullable()->nullable();
            $table->string('zona_id')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->string('statuspenyesuaianharga')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'tujuan', 'penyesuaian',  'statusaktif',  'statussistemton', 'kota_id', 'zona_id',  'tglmulaiberlaku', 'statuspenyesuaianharga', 'keterangan', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->unsignedBigInteger('statussistemton')->nullable();
            $table->unsignedBigInteger('statuspenyesuaianharga')->nullable();
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusaktif = $status->id ?? 0;

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'SISTEM TON')
            ->where('subgrp', '=', 'SISTEM TON')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatussistemton = $status->id ?? 0;

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'PENYESUAIAN HARGA')
            ->where('subgrp', '=', 'PENYESUAIAN HARGA')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuspenyesuaianharga = $status->id ?? 0;

        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $iddefaultstatusaktif,
                "statussistemton" => $iddefaultstatussistemton,
                "statuspenyesuaianharga" => $iddefaultstatuspenyesuaianharga
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statussistemton',
                'statuspenyesuaianharga',
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        $tempUpahsupir = (new static)->tempUpahsupir();
        $query = Tarif::from(DB::raw("tarif with (readuncommitted)"))
            ->select(
                'tarif.id',
                DB::raw("(case when tarif.parent_id=0 then null else tarif.parent_id end) as parent_id"),
                'parent.tujuan as parent',
                DB::raw("(case when tarif.upahsupir_id=0 then null else tarif.upahsupir_id end) as upahsupir_id"),
                "$tempUpahsupir.kotasampai_id as upahsupir",
                'tarif.tujuan',
                'tarif.penyesuaian',
                'tarif.statusaktif',
                'tarif.statussistemton',
                DB::raw("(case when tarif.kota_id=0 then null else tarif.kota_id end) as kota_id"),
                'kota.keterangan as kota',
                DB::raw("(case when tarif.zona_id=0 then null else tarif.zona_id end) as zona_id"),
                'zona.keterangan as zona',
                'tarif.tglmulaiberlaku',
                'tarif.statuspenyesuaianharga',
                'tarif.keterangan'
            )
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin(DB::raw("tarif as parent with (readuncommitted)"), 'tarif.parent_id', '=', 'parent.id')
            ->leftJoin(DB::raw("$tempUpahsupir with (readuncommitted)"), 'tarif.upahsupir_id', '=', "$tempUpahsupir.id")

            ->where('tarif.id', $id);

        $data = $query->first();
        return $data;
    }


    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'parent_id') {
            return $query->orderBy('parent.tujuan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'upahsupir_id') {
            return $query->orderBy('B.kotasampai_id', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kota_id') {
            return $query->orderBy('kota.kodekota', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'zona_id') {
            return $query->orderBy('zona.zona', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'container_id') {
                            $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'parent_id') {
                            $query = $query->where('parent.tujuan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'upahsupir_id') {
                            $query = $query->where('B.kotasampai_id', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kota_id') {
                            $query = $query->where('kota.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'keterangan_id') {
                            $query = $query->where('keterangan.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'statuspenyesuaianharga') {
                            $query = $query->where('p.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'statussistemton') {
                            $query = $query->where('sistemton.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where('tarif.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('tarif' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } elseif ($filters['field'] == 'container_id') {
                                $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'parent_id') {
                                $query = $query->orWhere('parent.tujuan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'upahsupir_id') {
                                $query = $query->orWhere('kotasampai_id', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'kota_id') {
                                $query = $query->orWhere('kota.keterangan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'zona_id') {
                                $query = $query->orWhere('zona.keterangan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'statuspenyesuaianharga') {
                                $query = $query->orWhere('p.text', '=', "$filters[data]");
                            } elseif ($filters['field'] == 'statussistemton') {
                                $query = $query->orWhere('sistemton.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere('tarif.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw('tarif' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function cekValidasi($id)
    {
        $rekap = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.tarif_id'
            )
            ->leftJoin(DB::raw("tarifrincian b with (readuncommitted)"), 'a.tarif_id', '=', 'b.id')
            ->where('b.tarif_id', '=', $id)
            ->first();


        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'surat pengantar',
                'kodeerror' => 'SATL'
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

    public function processStore(array $data): Tarif
    {
        $tarif = new Tarif();
        $tarif->parent_id = $data['parent_id'] ?? '';
        $tarif->upahsupir_id = $data['upahsupir_id'] ?? '';
        $tarif->tujuan = $data['tujuan'];
        $tarif->penyesuaian = $data['penyesuaian'];
        $tarif->statusaktif = $data['statusaktif'];
        $tarif->statussistemton = $data['statussistemton'];
        $tarif->kota_id = $data['kota_id'];
        $tarif->zona_id = $data['zona_id'] ?? '';
        $tarif->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
        $tarif->statuspenyesuaianharga = $data['statuspenyesuaianharga'];
        $tarif->keterangan = $data['keterangan'];
        $tarif->modifiedby = auth('api')->user()->user;

        if (!$tarif->save()) {
            throw new \Exception("Error storing tarif.");
        }

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($tarif->getTable()),
            'postingdari' => 'ENTRY TARIF',
            'idtrans' => $tarif->id,
            'nobuktitrans' => $tarif->id,
            'aksi' => 'ENTRY',
            'datajson' => $tarif->toArray(),
            'modifiedby' => $tarif->modifiedby
        ]);

        $detaillog = [];
        for ($i = 0; $i < count($data['container_id']); $i++) {

            $datadetails = (new TarifRincian())->processStore($tarif, [
                'tarif_id' => $tarif->id,
                'container_id' => $data['container_id'][$i],
                'nominal' => $data['nominal'][$i],
            ]);

            $detaillog[] = $datadetails->toArray();
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($datadetails->getTable()),
            'postingdari' => 'ENTRY UPAH SUPIR RINCIAN',
            'idtrans' =>  $storedLogTrail['id'],
            'nobuktitrans' => $tarif->id,
            'aksi' => 'ENTRY',
            'datajson' => $detaillog,
            'modifiedby' => auth('api')->user()->user
        ]);

        return $tarif;
    }

    public function processUpdate(Tarif $tarif, array $data): Tarif
    {
        $tarif->parent_id = $data['parent_id'] ?? '';
        $tarif->upahsupir_id = $data['upahsupir_id'] ?? '';
        $tarif->tujuan = $data['tujuan'];
        $tarif->penyesuaian = $data['penyesuaian'];
        $tarif->statusaktif = $data['statusaktif'];
        $tarif->statussistemton = $data['statussistemton'];
        $tarif->kota_id = $data['kota_id'];
        $tarif->zona_id = $data['zona_id'] ?? '';
        $tarif->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
        $tarif->statuspenyesuaianharga = $data['statuspenyesuaianharga'];
        $tarif->keterangan = $data['keterangan'];
        $tarif->modifiedby = auth('api')->user()->user;

        if (!$tarif->save()) {
            throw new \Exception("Error updating tarif.");
        }

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($tarif->getTable()),
            'postingdari' => 'EDIT TARIF',
            'idtrans' => $tarif->id,
            'nobuktitrans' => $tarif->id,
            'aksi' => 'EDIT',
            'datajson' => $tarif->toArray(),
            'modifiedby' => $tarif->modifiedby
        ]);

        $detaillog = [];
        for ($i = 0; $i < count($data['container_id']); $i++) {
            $datadetails = (new TarifRincian())->processUpdate($tarif, [
                'tarif_id' => $tarif->id,
                'detail_id' => $data['detail_id'][$i],
                'container_id' => $data['container_id'][$i],
                'nominal' => $data['nominal'][$i],
            ]);

            $detaillog[] = $datadetails->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($datadetails->getTable()),
            'postingdari' => 'ENTRY UPAH SUPIR RINCIAN',
            'idtrans' =>  $storedLogTrail['id'],
            'nobuktitrans' => $tarif->id,
            'aksi' => 'ENTRY',
            'datajson' => $detaillog,
        ]);

        return $tarif;
    }

    public function processDestroy($id): Tarif
    {
        $tarif = new Tarif();
        $tarif = $tarif->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tarif->getTable()),
            'postingdari' => 'DELETE TARIF',
            'idtrans' => $tarif->id,
            'nobuktitrans' => $tarif->id,
            'aksi' => 'DELETE',
            'datajson' => $tarif->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $tarif;
    }
}
