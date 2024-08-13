<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Tujuan extends MyModel
{
    use HasFactory;

    protected $table = 'tujuan';

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
                'tujuan.id',
                'tujuan.kodetujuan',
                'tujuan.keterangan',
                'parameter.memo as statusaktif',
                'tujuan.modifiedby',
                'tujuan.created_at',
                'tujuan.updated_at',
                DB::raw("'Laporan Tujuan' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tujuan.statusaktif', '=', 'parameter.id');




        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('tujuan.statusaktif', '=', $statusaktif->id);
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

        $data = Tujuan::from(DB::raw("tujuan with (readuncommitted)"))
            ->select(
                'tujuan.id',
                'tujuan.kodetujuan as tujuan',
                'tujuan.keterangan',
                'tujuan.statusaktif',
                'parameter.text as statusaktifnama',
                'tujuan.modifiedby',
                'tujuan.created_at',
                'tujuan.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tujuan.statusaktif', '=', 'parameter.id')
            ->where('tujuan.id', $id)->first();

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
        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id ?? 0, "statusaktifnama" => $statusaktif->text ?? ""]);

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
            $this->table.kodetujuan,
            $this->table.keterangan,

            'parameter.text as statusaktif',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )
            ->leftJoin('parameter', 'tujuan.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodetujuan')->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodetujuan', 'keterangan', 'statusaktif',  'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        $index = $this->params['sortIndex'];
        if ($this->params['sortIndex'] == "tujuan") {
            $index = "kodetujuan";
        }
        return $query->orderBy($this->table . '.' .  $index, $this->params['sortOrder']);
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
                                // $query = $query->where('tujuan.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw('tujuan' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
                                    // $query = $query->orWhere('tujuan.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw('tujuan' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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


    public function processStore(array $data, Tujuan $tujuan): Tujuan
    {
        $tujuan->kodetujuan = $data['tujuan'];
        $tujuan->statusaktif = $data['statusaktif'];
        $tujuan->keterangan = $data['keterangan'] ?? '';
        $tujuan->tas_id = $data['tas_id'] ?? '';
        $tujuan->modifiedby = auth('api')->user()->user;
        $tujuan->info = html_entity_decode(request()->info);
        $data['sortname'] = $data['sortname'] ?? 'id';
        $data['sortorder'] = $data['sortorder'] ?? 'asc';

        if (!$tujuan->save()) {
            throw new \Exception('Error storing tujuan.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tujuan->getTable()),
            'postingdari' => 'ENTRY TUJUAN',
            'idtrans' => $tujuan->id,
            'nobuktitrans' => $tujuan->id,
            'aksi' => 'ENTRY',
            'datajson' => $tujuan->toArray(),
            'modifiedby' => $tujuan->modifiedby
        ]);

        return $tujuan;
    }

    public function processUpdate(Tujuan $tujuan, array $data): Tujuan
    {
        $tujuan->kodetujuan = $data['tujuan'];
        $tujuan->keterangan = $data['keterangan'] ?? '';
        $tujuan->statusaktif = $data['statusaktif'];
        $tujuan->info = html_entity_decode(request()->info);

        if (!$tujuan->save()) {
            throw new \Exception('Error updating tujuan.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tujuan->getTable()),
            'postingdari' => 'EDIT ZONA',
            'idtrans' => $tujuan->id,
            'nobuktitrans' => $tujuan->id,
            'aksi' => 'EDIT',
            'datajson' => $tujuan->toArray(),
            'modifiedby' => $tujuan->modifiedby
        ]);

        return $tujuan;
    }

    public function processDestroy(Tujuan $tujuan): Tujuan
    {
        // $tujuan = new Tujuan();
        $tujuan = $tujuan->lockAndDestroy($tujuan->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tujuan->getTable()),
            'postingdari' => 'DELETE ZONA',
            'idtrans' => $tujuan->id,
            'nobuktitrans' => $tujuan->id,
            'aksi' => 'DELETE',
            'datajson' => $tujuan->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $tujuan;
    }

    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $tujuan = Tujuan::find($data['Id'][$i]);

            $tujuan->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($tujuan->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($tujuan->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF ZONA',
                    'idtrans' => $tujuan->id,
                    'nobuktitrans' => $tujuan->id,
                    'aksi' => $aksi,
                    'datajson' => $tujuan->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $tujuan;
    }

    public function processApprovalaktif(array $data)
    {
        $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $tujuan = Tujuan::find($data['Id'][$i]);

            $tujuan->statusaktif = $statusaktif->id;
            $aksi = $statusaktif->text;

            if ($tujuan->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($tujuan->getTable()),
                    'postingdari' => 'APPROVAL AKTIF ZONA',
                    'idtrans' => $tujuan->id,
                    'nobuktitrans' => $tujuan->id,
                    'aksi' => $aksi,
                    'datajson' => $tujuan->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $tujuan;
    }
}
