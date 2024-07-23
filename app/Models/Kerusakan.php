<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Kerusakan extends MyModel
{
    use HasFactory;

    protected $table = 'kerusakan';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    // protected $casts = [
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ]; 
    public function cekvalidasihapus($id)
    {

        $pengeluaranStok = DB::table('pengeluaranstokheader')
            ->from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.kerusakan_id'
            )
            ->where('a.kerusakan_id', '=', $id)
            ->first();
        if (isset($pengeluaranStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Stok',
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
                'kerusakan.id',
                'kerusakan.keterangan',
                'parameter.memo as statusaktif',
                'kerusakan.modifiedby',
                'kerusakan.created_at',
                'kerusakan.updated_at',
                DB::raw("'Laporan Kerusakan' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kerusakan.statusaktif', '=', 'parameter.id');


        $this->filter($query);

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('kerusakan.statusaktif', '=', $statusaktif->id);
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
        $this->setRequestParameters();

        $data = Kerusakan::from(DB::raw("kerusakan with (readuncommitted)"))
            ->select(
                'kerusakan.id',
                'kerusakan.keterangan',
                'kerusakan.statusaktif',
                'parameter.text as statusaktifnama',
                'kerusakan.modifiedby',
                'kerusakan.created_at',
                'kerusakan.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kerusakan.statusaktif', '=', 'parameter.id')
            ->where('kerusakan.id', $id)->first();

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
    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.keterangan,
            'parameter.text as statusaktif',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin('parameter', 'kerusakan.statusaktif', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('statusaktif', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                                // $query = $query->where('kerusakan.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw('kerusakan' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
                                    // $query = $query->orWhere('kerusakan.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw('kerusakan' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data, Kerusakan $kerusakan): Kerusakan
    {
        // $kerusakan = new Kerusakan();
        $kerusakan->keterangan = $data['keterangan'] ?? '';
        $kerusakan->statusaktif = $data['statusaktif'];
        $kerusakan->modifiedby = auth('api')->user()->user;
        $kerusakan->tas_id = $data['tas_id'] ?? '';
        $kerusakan->info = html_entity_decode(request()->info);
        $data['sortname'] = $data['sortname'] ?? 'id';
        $data['sortorder'] = $data['sortorder'] ?? 'asc';

        if (!$kerusakan->save()) {
            throw new \Exception('Error storing kerusakan.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kerusakan->getTable()),
            'postingdari' => 'ENTRY KERUSAKAN',
            'idtrans' => $kerusakan->id,
            'nobuktitrans' => $kerusakan->id,
            'aksi' => 'ENTRY',
            'datajson' => $kerusakan->toArray(),
            'modifiedby' => $kerusakan->modifiedby
        ]);

        return $kerusakan;
    }

    public function processUpdate(Kerusakan $kerusakan, array $data): Kerusakan
    {
        $kerusakan->keterangan = $data['keterangan'] ?? '';
        $kerusakan->statusaktif = $data['statusaktif'];
        $kerusakan->modifiedby = auth('api')->user()->user;
        $kerusakan->info = html_entity_decode(request()->info);

        if (!$kerusakan->save()) {
            throw new \Exception('Error updating kerusakan.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kerusakan->getTable()),
            'postingdari' => 'EDIT KERUSAKAN',
            'idtrans' => $kerusakan->id,
            'nobuktitrans' => $kerusakan->id,
            'aksi' => 'EDIT',
            'datajson' => $kerusakan->toArray(),
            'modifiedby' => $kerusakan->modifiedby
        ]);

        return $kerusakan;
    }

    public function processDestroy(Kerusakan $kerusakan): Kerusakan
    {
        // $kerusakan = new Kerusakan();
        $kerusakan = $kerusakan->lockAndDestroy($kerusakan->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kerusakan->getTable()),
            'postingdari' => 'DELETE KERUSAKAN',
            'idtrans' => $kerusakan->id,
            'nobuktitrans' => $kerusakan->id,
            'aksi' => 'DELETE',
            'datajson' => $kerusakan->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $kerusakan;
    }
    
    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $kerusakan = Kerusakan::find($data['Id'][$i]);

            $kerusakan->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($kerusakan->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($kerusakan->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF KERUSAKAN',
                    'idtrans' => $kerusakan->id,
                    'nobuktitrans' => $kerusakan->id,
                    'aksi' => $aksi,
                    'datajson' => $kerusakan->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $kerusakan;
    }

    public function processApprovalaktif(array $data)
    {
        $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $kerusakan = Kerusakan::find($data['Id'][$i]);

            $kerusakan->statusaktif = $statusaktif->id;
            $aksi = $statusaktif->text;

            if ($kerusakan->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($kerusakan->getTable()),
                    'postingdari' => 'APPROVAL AKTIF KERUSAKAN',
                    'idtrans' => $kerusakan->id,
                    'nobuktitrans' => $kerusakan->id,
                    'aksi' => $aksi,
                    'datajson' => $kerusakan->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $kerusakan;
    }
}
