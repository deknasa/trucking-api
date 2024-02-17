<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Kelompok extends MyModel
{
    use HasFactory;

    protected $table = 'kelompok';

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
                'a.kelompok_id'
            )
            ->where('a.kelompok_id', '=', $id)
            ->first();
        if (isset($stok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Stok',
            ];


            goto selesai;
        }

        $subKelompok = DB::table('subkelompok')
            ->from(
                DB::raw("subkelompok as a with (readuncommitted)")
            )
            ->select(
                'a.kelompok_id'
            )
            ->where('a.kelompok_id', '=', $id)
            ->first();
        if (isset($subKelompok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Sub Kelompok',
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
                'kelompok.id',
                'kelompok.kodekelompok',
                'kelompok.keterangan',
                'parameter.memo as statusaktif',
                'parameter.text as statusaktifnama',
                'kelompok.modifiedby',
                'kelompok.created_at',
                'kelompok.updated_at',
                DB::raw("'Laporan Kelompok' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kelompok.statusaktif', '=', 'parameter.id');




        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('kelompok.statusaktif', '=', $statusaktif->id);
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
            $table->string('statusaktifnama')->nullable();
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
        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id,"statusaktifnama" => $statusaktif->text]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama',
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function findAll($id) {
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'kelompok.id',
                'kelompok.kodekelompok',
                'kelompok.keterangan',
                'parameter.id as statusaktif',
                'parameter.text as statusaktifnama',
                'kelompok.modifiedby',
                'kelompok.created_at',
                'kelompok.updated_at',
            )
            ->where('kelompok.id',$id)
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kelompok.statusaktif', '=', 'parameter.id');
            return $query->first();

    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "   $this->table.id,
                $this->table.kodekelompok,
                $this->table.keterangan,
                $this->table.statusaktif,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
                )
            );
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodekelompok', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->bigInteger('statusaktif')->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodekelompok', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where('kelompok.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('kelompok' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere('kelompok.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw('kelompok' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
    public function processStore(array $data): Kelompok
    {
        $kelompok = new Kelompok();
        $kelompok->kodekelompok = $data['kodekelompok'];
        $kelompok->keterangan = $data['keterangan'] ?? '';
        $kelompok->statusaktif = $data['statusaktif'];
        $kelompok->modifiedby = auth('api')->user()->name;
        $kelompok->info = html_entity_decode(request()->info);

        if (!$kelompok->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kelompok->getTable()),
            'postingdari' => 'ENTRY KELOMPOK',
            'idtrans' => $kelompok->id,
            'nobuktitrans' => $kelompok->id,
            'aksi' => 'ENTRY',
            'datajson' => $kelompok->toArray(),
            'modifiedby' => $kelompok->modifiedby
        ]);

        return $kelompok;
    }

    public function processUpdate(Kelompok $kelompok, array $data): Kelompok
    {
        $kelompok->kodekelompok = $data['kodekelompok'];
        $kelompok->keterangan = $data['keterangan'] ?? '';
        $kelompok->statusaktif = $data['statusaktif'];
        $kelompok->modifiedby = auth('api')->user()->name;
        $kelompok->info = html_entity_decode(request()->info);

        if (!$kelompok->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kelompok->getTable()),
            'postingdari' => 'EDIT KELOMPOK',
            'idtrans' => $kelompok->id,
            'nobuktitrans' => $kelompok->id,
            'aksi' => 'EDIT',
            'datajson' => $kelompok->toArray(),
            'modifiedby' => $kelompok->modifiedby
        ]);

        return $kelompok;
    }

    public function processDestroy($id): Kelompok
    {
        $kelompok = new Kelompok();
        $kelompok = $kelompok->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kelompok->getTable()),
            'postingdari' => 'DELETE KELOMPOK',
            'idtrans' => $kelompok->id,
            'nobuktitrans' => $kelompok->id,
            'aksi' => 'DELETE',
            'datajson' => $kelompok->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $kelompok;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $kelompok = Kelompok::find($data['Id'][$i]);

            $kelompok->statusaktif = $statusnonaktif->id;
            $kelompok->modifiedby = auth('api')->user()->name;
            $kelompok->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($kelompok->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($kelompok->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF KELOMPOK',
                    'idtrans' => $kelompok->id,
                    'nobuktitrans' => $kelompok->id,
                    'aksi' => $aksi,
                    'datajson' => $kelompok->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $kelompok;
    }
}
