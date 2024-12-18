<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Zona extends MyModel
{
    use HasFactory;

    protected $table = 'zona';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasihapus($id)
    {
        $supir = DB::table('supir')
            ->from(
                DB::raw("supir as a with (readuncommitted)")
            )
            ->select(
                'a.zona_id'
            )
            ->where('a.zona_id', '=', $id)
            ->first();
        if (isset($supir)) {

            $data = [
                'kondisi' => true,
                'keterangan' => 'Supir',
            ];
            goto selesai;
        }

        $tarif = DB::table('tarif')
            ->from(
                DB::raw("tarif as a with (readuncommitted)")
            )
            ->select(
                'a.zona_id'
            )
            ->where('a.zona_id', '=', $id)
            ->first();
        if (isset($tarif)) {

            $data = [
                'kondisi' => true,
                'keterangan' => 'Tarif',
            ];
            goto selesai;
        }
        $kota = DB::table('kota')
            ->from(
                DB::raw("kota as a with (readuncommitted)")
            )
            ->select(
                'a.zona_id'
            )
            ->where('a.zona_id', '=', $id)
            ->first();
        if (isset($kota)) {

            $data = [
                'kondisi' => true,
                'keterangan' => 'kota',
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
                'zona.id',
                'zona.zona',
                'zona.keterangan',
                'parameter.memo as statusaktif',
                'zona.modifiedby',
                'zona.created_at',
                'zona.updated_at',
                DB::raw("'Laporan Zona' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'zona.statusaktif', '=', 'parameter.id');




        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('zona.statusaktif', '=', $statusaktif->id);
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

        $data = Zona::from(DB::raw("zona with (readuncommitted)"))
            ->select(
                'zona.id',
                'zona.zona',
                'zona.keterangan',
                'zona.statusaktif',
                'parameter.text as statusaktifnama',
                'zona.modifiedby',
                'zona.created_at',
                'zona.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'zona.statusaktif', '=', 'parameter.id')
            ->where('zona.id', $id)->first();

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
            $this->table.zona,
            $this->table.keterangan,

            'parameter.text as statusaktif',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )
            ->leftJoin('parameter', 'zona.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->longText('zona')->nullable();
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
        DB::table($temp)->insertUsing(['id', 'zona', 'keterangan', 'statusaktif',  'modifiedby', 'created_at', 'updated_at'], $models);


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
                                // $query = $query->where('zona.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw('zona' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
                                    // $query = $query->orWhere('zona.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw('zona' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data, Zona $zona): Zona
    {
        // $zona = new Zona();
        $zona->zona = $data['zona'];
        $zona->statusaktif = $data['statusaktif'];
        $zona->keterangan = $data['keterangan'] ?? '';
        $zona->tas_id = $data['tas_id'] ?? '';
        $zona->modifiedby = auth('api')->user()->user;
        $zona->info = html_entity_decode(request()->info);
        $data['sortname'] = $data['sortname'] ?? 'id';
        $data['sortorder'] = $data['sortorder'] ?? 'asc';

        if (!$zona->save()) {
            throw new \Exception('Error storing zona.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($zona->getTable()),
            'postingdari' => 'ENTRY ZONA',
            'idtrans' => $zona->id,
            'nobuktitrans' => $zona->id,
            'aksi' => 'ENTRY',
            'datajson' => $zona->toArray(),
            'modifiedby' => $zona->modifiedby
        ]);

        return $zona;
    }

    public function processUpdate(Zona $zona, array $data): Zona
    {
        $zona->zona = $data['zona'];
        $zona->keterangan = $data['keterangan'] ?? '';
        $zona->statusaktif = $data['statusaktif'];
        $zona->info = html_entity_decode(request()->info);

        if (!$zona->save()) {
            throw new \Exception('Error updating zona.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($zona->getTable()),
            'postingdari' => 'EDIT ZONA',
            'idtrans' => $zona->id,
            'nobuktitrans' => $zona->id,
            'aksi' => 'EDIT',
            'datajson' => $zona->toArray(),
            'modifiedby' => $zona->modifiedby
        ]);

        return $zona;
    }

    public function processDestroy(Zona $zona): Zona
    {
        // $zona = new Zona();
        $zona = $zona->lockAndDestroy($zona->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($zona->getTable()),
            'postingdari' => 'DELETE ZONA',
            'idtrans' => $zona->id,
            'nobuktitrans' => $zona->id,
            'aksi' => 'DELETE',
            'datajson' => $zona->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $zona;
    }

    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $zona = Zona::find($data['Id'][$i]);

            $zona->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($zona->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($zona->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF ZONA',
                    'idtrans' => $zona->id,
                    'nobuktitrans' => $zona->id,
                    'aksi' => $aksi,
                    'datajson' => $zona->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $zona;
    }

    public function processApprovalaktif(array $data)
    {
        $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $zona = Zona::find($data['Id'][$i]);

            $zona->statusaktif = $statusaktif->id;
            $aksi = $statusaktif->text;

            if ($zona->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($zona->getTable()),
                    'postingdari' => 'APPROVAL AKTIF ZONA',
                    'idtrans' => $zona->id,
                    'nobuktitrans' => $zona->id,
                    'aksi' => $aksi,
                    'datajson' => $zona->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $zona;
    }
}
