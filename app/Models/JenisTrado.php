<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JenisTrado extends MyModel
{
    use HasFactory;

    protected $table = 'jenistrado';

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

        $stok = DB::table('stok')
            ->from(
                DB::raw("stok as a with (readuncommitted)")
            )
            ->select(
                'a.jenistrado_id'
            )
            ->where('a.jenistrado_id', '=', $id)
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
                'jenistrado.id',
                'jenistrado.kodejenistrado',
                'jenistrado.keterangan',
                'parameter.memo as statusaktif',
                'jenistrado.modifiedby',
                'jenistrado.created_at',
                'jenistrado.updated_at',
                DB::raw("'Laporan Jenis Trado' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'jenistrado.statusaktif', '=', 'parameter.id');




        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('jenistrado.statusaktif', '=', $statusaktif->id);
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
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.kodejenistrado,
            $this->table.keterangan,
            'parameter.text as statusaktif',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'jenistrado.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodejenistrado', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodejenistrado', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                                // $query = $query->where('jenistrado.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw('jenistrado' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
                                    // $query = $query->orWhere('jenistrado.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw('jenistrado' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data, JenisTrado $jenistrado): JenisTrado
    {
        // $jenistrado = new jenistrado();
        $jenistrado->kodejenistrado = $data['kodejenistrado'];
        $jenistrado->statusaktif = $data['statusaktif'];
        $jenistrado->keterangan = $data['keterangan'] ?? '';
        $jenistrado->modifiedby = auth('api')->user()->name;
        $jenistrado->info = html_entity_decode(request()->info);
        $jenistrado->tas_id = $data['tas_id'] ?? '';
        $data['sortname'] = $data['sortname'] ?? 'id';
        $data['sortorder'] = $data['sortorder'] ?? 'asc';
        

        TOP:
        if (!$jenistrado->save()) {
            throw new \Exception('Error storing jenis trado.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jenistrado->getTable()),
            'postingdari' => 'ENTRY JENIS TRADO',
            'idtrans' => $jenistrado->id,
            'nobuktitrans' => $jenistrado->id,
            'aksi' => 'ENTRY',
            'datajson' => $jenistrado->toArray(),
            'modifiedby' => $jenistrado->modifiedby
        ]);

        return $jenistrado;
    }

    public function processUpdate(JenisTrado $jenistrado, array $data): JenisTrado
    {
        $jenistrado->kodejenistrado = $data['kodejenistrado'];
        $jenistrado->keterangan = $data['keterangan'] ?? '';
        $jenistrado->statusaktif =  $data['statusaktif'];
        $jenistrado->info = html_entity_decode(request()->info);

        if (!$jenistrado->save()) {
            throw new \Exception('Error updating jenis trado.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jenistrado->getTable()),
            'postingdari' => 'EDIT JENIS TRADO',
            'idtrans' => $jenistrado->id,
            'nobuktitrans' => $jenistrado->id,
            'aksi' => 'EDIT',
            'datajson' => $jenistrado->toArray(),
            'modifiedby' => $jenistrado->modifiedby
        ]);

        return $jenistrado;
    }

    public function processDestroy(JenisTrado $jenistrado): JenisTrado
    {
        // $jenistrado = new JenisTrado();
        $jenistrado = $jenistrado->lockAndDestroy($jenistrado->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jenistrado->getTable()),
            'postingdari' => 'DELETE JENIS TRADO',
            'idtrans' => $jenistrado->id,
            'nobuktitrans' => $jenistrado->id,
            'aksi' => 'DELETE',
            'datajson' => $jenistrado->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $jenistrado;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $jenisTrado = JenisTrado::find($data['Id'][$i]);

            $jenisTrado->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($jenisTrado->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($jenisTrado->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF JENIS TRADO',
                    'idtrans' => $jenisTrado->id,
                    'nobuktitrans' => $jenisTrado->id,
                    'aksi' => $aksi,
                    'datajson' => $jenisTrado->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $jenisTrado;
    }
}
