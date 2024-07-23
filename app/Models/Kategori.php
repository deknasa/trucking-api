<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Kategori extends MyModel
{
    use HasFactory;

    protected $table = 'kategori';

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

        $stok = DB::table('stok')
            ->from(
                DB::raw("stok as a with (readuncommitted)")
            )
            ->select(
                'a.kategori_id'
            )
            ->where('a.kategori_id', '=', $id)
            ->first();
        if (isset($stok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Stok',
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
                'kategori.id',
                'kategori.kodekategori',
                'kategori.keterangan',
                'parameter.memo as status',
                'p.keterangan as subkelompok',
                'kategori.modifiedby',
                'kategori.created_at',
                'kategori.updated_at',
                DB::raw("'Laporan Kategori' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kategori.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("subkelompok AS p with (readuncommitted)"), 'kategori.subkelompok_id', '=', 'p.id');

            if (request()->subkelompok) {
                $query->where('kategori.subkelompok_id','=',request()->subkelompok);
            }

        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('kategori.statusaktif', '=', $statusaktif->id);
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

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id ?? 0, "statusaktifnama" =>$statusaktif->text ?? ""]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama'
            );

        $data = $query->first();
        return $data;
    }
    public function find($id)
    {
        $this->setRequestParameters();

        $data = Kategori::from(DB::raw("kategori with (readuncommitted)"))
            ->select(
                'kategori.id',
                'kategori.kodekategori',
                'kategori.keterangan',
                'kategori.subkelompok_id',
                'p.keterangan as subkelompok',
                'kategori.statusaktif',
                'parameter.text as statusaktifnama',
                'kategori.modifiedby',
                'kategori.created_at',
                'kategori.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kategori.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("subkelompok AS p with (readuncommitted)"), 'kategori.subkelompok_id', '=', 'p.id')
            ->where('kategori.id', $id)->first();

        return $data;
    }
    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.kodekategori,
            $this->table.keterangan,
            'parameter.text as statusaktif',
            'p.keterangan as subkelompok',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kategori.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("subkelompok AS p with (readuncommitted)"), 'kategori.subkelompok_id', '=', 'p.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodekategori', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('subkelompok', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodekategori', 'keterangan', 'statusaktif', 'subkelompok', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'subkelompok') {
            return $query->orderBy('p.keterangan', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'status') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'subkelompok') {
                            $query = $query->where('p.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'subkelompok_id') {
                            $query = $query->where('kategori.subkelompok_id', '=', "$filters[data]");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where('kategori.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('kategori' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'status') {
                                $query = $query->orWhere('parameter.text', '=', $filters['data']);
                            } elseif ($filters['field'] == 'subkelompok') {
                                $query = $query->orWhere('p.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere('kategori.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw('kategori' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data, Kategori $kategori): Kategori
    {
        // $kategori = new Kategori();
        $kategori->kodekategori = $data['kodekategori'];
        $kategori->keterangan = $data['keterangan'] ?? '';
        $kategori->subkelompok_id = $data['subkelompok_id'];
        $kategori->statusaktif = $data['statusaktif'];
        $kategori->tas_id = $data['tas_id'];
        $kategori->modifiedby = auth('api')->user()->name;
        $kategori->info = html_entity_decode(request()->info);

        if (!$kategori->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kategori->getTable()),
            'postingdari' => 'ENTRY KATEGORI',
            'idtrans' => $kategori->id,
            'nobuktitrans' => $kategori->id,
            'aksi' => 'ENTRY',
            'datajson' => $kategori->toArray(),
            'modifiedby' => $kategori->modifiedby
        ]);

        return $kategori;
    }

    public function processUpdate(Kategori $kategori, array $data): Kategori
    {
        $kategori->kodekategori = $data['kodekategori'];
        $kategori->keterangan = $data['keterangan'] ?? '';
        $kategori->subkelompok_id = $data['subkelompok_id'];
        $kategori->statusaktif = $data['statusaktif'];
        $kategori->info = html_entity_decode(request()->info);

        if (!$kategori->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kategori->getTable()),
            'postingdari' => 'EDIT KATEGORI',
            'idtrans' => $kategori->id,
            'nobuktitrans' => $kategori->id,
            'aksi' => 'EDIT',
            'datajson' => $kategori->toArray(),
            'modifiedby' => $kategori->modifiedby
        ]);

        return $kategori;
    }

    public function processDestroy(Kategori $kategori): Kategori
    {
        // $kategori = new Kategori();
        $kategori = $kategori->lockAndDestroy($kategori->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kategori->getTable()),
            'postingdari' => 'DELETE KATEGORI',
            'idtrans' => $kategori->id,
            'nobuktitrans' => $kategori->id,
            'aksi' => 'DELETE',
            'datajson' => $kategori->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $kategori;
    }

    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $kategori = Kategori::find($data['Id'][$i]);

            $kategori->statusaktif = $statusnonaktif->id;
            $kategori->modifiedby = auth('api')->user()->name;
            $kategori->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($kategori->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($kategori->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF KATEGORI',
                    'idtrans' => $kategori->id,
                    'nobuktitrans' => $kategori->id,
                    'aksi' => $aksi,
                    'datajson' => $kategori->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $kategori;
    }

    public function processApprovalaktif(array $data)
    {
        $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $kategori = Kategori::find($data['Id'][$i]);

            $kategori->statusaktif = $statusaktif->id;
            $kategori->modifiedby = auth('api')->user()->name;
            $kategori->info = html_entity_decode(request()->info);
            $aksi = $statusaktif->text;

            if ($kategori->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($kategori->getTable()),
                    'postingdari' => 'APPROVAL AKTIF KATEGORI',
                    'idtrans' => $kategori->id,
                    'nobuktitrans' => $kategori->id,
                    'aksi' => $aksi,
                    'datajson' => $kategori->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $kategori;
    }
}
