<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QtyTambahGantiOli extends MyModel
{
    use HasFactory;

    protected $table = 'qtytambahgantioli';

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
            $islookup = request()->isLookup ?? false;
            $stok_id = request()->stok_id ?? 0;
            $statusoli = request()->statusoli ?? '';

            // dd($stok_id);


        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'qtytambahgantioli.id',
                'qtytambahgantioli.keterangan',
                'qtytambahgantioli.qty',
                'parameter.memo as statusaktif',
                'parameteroli.memo as statusoli',
                'parameterservicerutin.memo as statusservicerutin',
                'qtytambahgantioli.modifiedby',
                'qtytambahgantioli.created_at',
                'qtytambahgantioli.updated_at',
                DB::raw("'Laporan Qty Tambah Ganti Oli' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'qtytambahgantioli.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter  as parameteroli with (readuncommitted)"), 'qtytambahgantioli.statusoli', '=', 'parameteroli.id')
            ->leftJoin(DB::raw("parameter  as parameterservicerutin with (readuncommitted)"), 'qtytambahgantioli.statusservicerutin', '=', 'parameterservicerutin.id');




        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('qtytambahgantioli.statusaktif', '=', $statusaktif->id);
        }

        if ($islookup == true) {
            $queryservicerutin=db::table('stok')->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("isnull(a.statusservicerutin,0) as statusservicerutin")
            )
            ->where('a.id',$stok_id)
            ->first();
            
            if (isset($queryservicerutin)) {
                $statusservicerutin=$queryservicerutin->statusservicerutin ?? 0;

                $query->where('qtytambahgantioli.statusservicerutin', $statusservicerutin);                
                $query->where('qtytambahgantioli.statusoli', $statusoli);                
            }
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
            $table->unsignedBigInteger('statusoli')->nullable();
            $table->unsignedBigInteger('statusservicerutin')->nullable();
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

            $statusoli = Parameter::from(
                db::Raw("parameter with (readuncommitted)")
            )
                ->select(
                    'id'
                )
                ->where('grp', '=', 'STATUS OLI')
                ->where('subgrp', '=', 'STATUS OLI')
                ->where('default', '=', 'YA')
                ->first();
                

                $statusservicerutin = Parameter::from(
                    db::Raw("parameter with (readuncommitted)")
                )
                    ->select(
                        'id'
                    )
                    ->where('grp', '=', 'STATUS SERVICE RUTIN')
                    ->where('subgrp', '=', 'STATUS SERVICE RUTIN')
                    ->where('default', '=', 'YA')
                    ->first();

                    
        DB::table($tempdefault)->insert([
            "statusaktif" => $statusaktif->id,
            "statusoli" => $statusoli->id,
            "statusservicerutin" => $statusservicerutin->id,
                
            ]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusoli',
                'statusservicerutin',
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
            $this->table.keterangan,
            'parameter.text as statusaktif',
            'parameteroli.text as statusoli',
            'parameterservicerutin.text as statusservicerutin',
            $this->table.qty,

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )

            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'qtytambahgantioli.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as parameteroli with (readuncommitted)"), 'qtytambahgantioli.statusoli', '=', 'parameteroli.id')
            ->leftJoin(DB::raw("parameter as parameterservicerutin with (readuncommitted)"), 'qtytambahgantioli.statusservicerutin', '=', 'parameterservicerutin.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('statusaktif', 500)->nullable();
            $table->string('statusoli', 500)->nullable();
            $table->string('statusservicerutin', 500)->nullable();
            $table->double('qty',15,2)->nullable();

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
        DB::table($temp)->insertUsing(['id', 'keterangan', 'statusaktif','statusoli','statusservicerutin', 'qty', 'modifiedby', 'created_at', 'updated_at'], $models);

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
                            } else if ($filters['field'] == 'statusoli') {
                                $query = $query->where('parameteroli.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusservicerutin') {
                                $query = $query->where('parameterservicerutin.text', '=', "$filters[data]");
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
                                } else if ($filters['field'] == 'statusoli') {
                                    $query = $query->orwhere('parameteroli.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusservicerutin') {
                                    $query = $query->orwhere('parameterservicerutin.text', '=', "$filters[data]");
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

    public function processStore(array $data): QtyTambahGantiOli
    {
        $qtytambahgantioli = new QtyTambahGantiOli();
        $qtytambahgantioli->keterangan = strtoupper($data['keterangan']) ?? '';
        $qtytambahgantioli->qty = $data['qty'];
        $qtytambahgantioli->statusaktif = $data['statusaktif'];
        $qtytambahgantioli->statusoli = $data['statusoli'];
        $qtytambahgantioli->statusservicerutin = $data['statusservicerutin'];
        $qtytambahgantioli->tas_id = $data['tas_id'] ?? '';
        $qtytambahgantioli->modifiedby = auth('api')->user()->user;
        $qtytambahgantioli->info = html_entity_decode(request()->info);

        if (!$qtytambahgantioli->save()) {
            throw new \Exception('Error storing Qty Tambah Ganti Oli.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($qtytambahgantioli->getTable()),
            'postingdari' => 'ENTRY QTY TAMBAH GANTI OLI',
            'idtrans' => $qtytambahgantioli->id,
            'nobuktitrans' => $qtytambahgantioli->id,
            'aksi' => 'ENTRY',
            'datajson' => $qtytambahgantioli->toArray(),
            'modifiedby' => $qtytambahgantioli->modifiedby
        ]);

        return $qtytambahgantioli;
    }

    public function processUpdate(QtyTambahGantiOli $qtytambahgantioli, array $data): QtyTambahGantiOli
    {
        $qtytambahgantioli->keterangan = $data['keterangan'] ?? '';
        $qtytambahgantioli->qty = $data['qty'];
        $qtytambahgantioli->statusaktif = $data['statusaktif'];
        $qtytambahgantioli->statusoli = $data['statusoli'];
        $qtytambahgantioli->statusservicerutin = $data['statusservicerutin'];
        $qtytambahgantioli->modifiedby = auth('api')->user()->user;
        $qtytambahgantioli->info = html_entity_decode(request()->info);

        if (!$qtytambahgantioli->save()) {
            throw new \Exception('Error updating Qty Tambah Ganti Oli.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($qtytambahgantioli->getTable()),
            'postingdari' => 'EDIT QTY TAMBAH GANTI OLI',
            'idtrans' => $qtytambahgantioli->id,
            'nobuktitrans' => $qtytambahgantioli->id,
            'aksi' => 'EDIT',
            'datajson' => $qtytambahgantioli->toArray(),
            'modifiedby' => $qtytambahgantioli->modifiedby
        ]);

        return $qtytambahgantioli;
    }

    public function processDestroy($id): QtyTambahGantiOli
    {
        $qtytambahgantioli = new QtyTambahGantiOli();
        $qtytambahgantioli = $qtytambahgantioli->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($qtytambahgantioli->getTable()),
            'postingdari' => 'DELETE QTY TAMBAH GANTI OLI',
            'idtrans' => $qtytambahgantioli->id,
            'nobuktitrans' => $qtytambahgantioli->id,
            'aksi' => 'DELETE',
            'datajson' => $qtytambahgantioli->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $qtytambahgantioli;
    }
    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $qtytambahgantioli = QtyTambahGantiOli::find($data['Id'][$i]);

            $qtytambahgantioli->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($qtytambahgantioli->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($qtytambahgantioli->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF QTY TAMBAH GANTI OLI',
                    'idtrans' => $qtytambahgantioli->id,
                    'nobuktitrans' => $qtytambahgantioli->id,
                    'aksi' => $aksi,
                    'datajson' => $qtytambahgantioli->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $qtytambahgantioli;
    }

}
