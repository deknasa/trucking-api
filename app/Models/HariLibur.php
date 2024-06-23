<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HariLibur extends MyModel
{
    use HasFactory;

    protected $table = 'harilibur';

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


        $aktif = request()->aktif ?? '';
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                "$this->table.id",
                "$this->table.tgl",
                "$this->table.keterangan",
                "parameter.memo as statusaktif",
                "$this->table.modifiedby",
                "$this->table.created_at",
                "$this->table.updated_at",
                DB::raw("'Laporan Hari Libur' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'harilibur.statusaktif', 'parameter.id');


        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('harilibur.statusaktif', '=', $statusaktif->id);
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
            ->where('DEFAULT', '=', 'YA')
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
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw("
            $this->table.id,
            $this->table.tgl,
            $this->table.keterangan,
            parameter.text as statusaktif,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at
            ")
            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'harilibur.statusaktif', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->date('tgl')->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'tgl', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
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
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data, HariLibur $hariLibur): HariLibur
    {
        // $hariLibur = new HariLibur();
        $hariLibur->tgl = date('Y-m-d', strtotime($data['tgl']));
        $hariLibur->keterangan = $data['keterangan'] ?? '';
        $hariLibur->statusaktif = $data['statusaktif'];
        $hariLibur->modifiedby = auth('api')->user()->name;
        $hariLibur->info = html_entity_decode(request()->info);
        $hariLibur->tas_id = $data['tas_id'] ?? '';
        $hariLibur->modifiedby = auth('api')->user()->user;        

        if (!$hariLibur->save()) {
            throw new \Exception('Error storing hari libur.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($hariLibur->getTable()),
            'postingdari' => 'ENTRY HARI LIBUR',
            'idtrans' => $hariLibur->id,
            'nobuktitrans' => $hariLibur->id,
            'aksi' => 'ENTRY',
            'datajson' => $hariLibur->toArray(),
            'modifiedby' => $hariLibur->modifiedby
        ]);

        return $hariLibur;
    }

    public function processUpdate(HariLibur $harilibur, array $data): HariLibur
    {
        $harilibur->tgl = date('Y-m-d', strtotime($data['tgl']));
        $harilibur->keterangan = $data['keterangan'] ?? '';
        $harilibur->statusaktif = $data['statusaktif'];
        $harilibur->modifiedby = auth('api')->user()->user;
        $harilibur->info = html_entity_decode(request()->info);

        if (!$harilibur->save()) {
            throw new \Exception('Error updating hari libur.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $harilibur->getTable(),
            'postingdari' => 'EDIT HARI LIBUR',
            'idtrans' => $harilibur->id,
            'nobuktitrans' => $harilibur->id,
            'aksi' => 'EDIT',
            'datajson' => $harilibur->toArray(),
            'modifiedby' => $harilibur->modifiedby
        ]);

        return $harilibur;
    }

    public function processDestroy(Harilibur $harilibur): HariLibur
    {
        // $harilibur = new Harilibur();
        $harilibur = $harilibur->lockAndDestroy($harilibur->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($harilibur->getTable()),
                'postingdari' => 'DELETE HARI LIBUR',
                'idtrans' => $harilibur->id,
                'nobuktitrans' => $harilibur->id,
                'aksi' => 'DELETE',
                'datajson' => $harilibur->toArray(),
                'modifiedby' => auth('api')->user()->name
        ]);

        return $harilibur;
    }
}
