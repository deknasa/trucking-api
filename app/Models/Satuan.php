<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Satuan extends MyModel
{
    use HasFactory;

    protected $table = 'satuan';

    // protected $casts = [
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ];

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

        $query = DB::table($this->table)->select(
            'satuan.id',
            'satuan.satuan',
            'parameter.memo as statusaktif',
            'satuan.modifiedby',
            'satuan.created_at',
            'satuan.updated_at',
            DB::raw("'Laporan Satuan' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
        )
            ->leftJoin('parameter', 'satuan.statusaktif', '=', 'parameter.id');




        $this->filter($query);

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('satuan.statusaktif', '=', $statusaktif->id);
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
    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.satuan,
            'parameter.text as statusaktif',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )->leftJoin('parameter', 'satuan.statusaktif', '=', 'parameter.id');
    }

    public function findAll($id)
    {
        $this->setRequestParameters();

        $data = satuan::from(DB::raw("satuan with (readuncommitted)"))
            ->select(
                'satuan.id',
                'satuan.satuan',
                'parameter.text as statusaktifnama',
                'satuan.statusaktif',
                'satuan.modifiedby',
                'satuan.created_at',
                'satuan.updated_at',
            )
            ->leftJoin('parameter', 'satuan.statusaktif', '=', 'parameter.id')
            ->where('satuan.id', $id)->first();



        return $data;
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('satuan', 50)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'satuan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                            // $query = $query->where('satuan.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('satuan' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
                                // $query = $query->orWhere('satuan.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw('satuan' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data): Satuan
    {
        $satuan = new Satuan();
        $satuan->satuan = $data['satuan'];
        $satuan->statusaktif = $data['statusaktif'];
        $satuan->modifiedby = auth('api')->user()->name;
        // $satuan->info = html_entity_decode(request()->info);

        if (!$satuan->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($satuan->getTable()),
            'postingdari' => 'ENTRY SATUAN',
            'idtrans' => $satuan->id,
            'nobuktitrans' => $satuan->id,
            'aksi' => 'ENTRY',
            'datajson' => $satuan->toArray(),
            'modifiedby' => $satuan->modifiedby
        ]);

        return $satuan;
    }

    public function processUpdate(Satuan $satuan, array $data): Satuan
    {
        $satuan->satuan = $data['satuan'];
        $satuan->statusaktif = $data['statusaktif'];
        $satuan->modifiedby = auth('api')->user()->name;
        // $satuan->info = html_entity_decode(request()->info);

        if (!$satuan->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($satuan->getTable()),
            'postingdari' => 'EDIT SATUAN',
            'idtrans' => $satuan->id,
            'nobuktitrans' => $satuan->id,
            'aksi' => 'EDIT',
            'datajson' => $satuan->toArray(),
            'modifiedby' => $satuan->modifiedby
        ]);

        return $satuan;
    }

    public function processDestroy($id): Satuan
    {
        $satuan = new Satuan();
        $satuan = $satuan->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($satuan->getTable()),
            'postingdari' => 'DELETE SATUAN',
            'idtrans' => $satuan->id,
            'nobuktitrans' => $satuan->id,
            'aksi' => 'DELETE',
            'datajson' => $satuan->toArray(),
            'modifiedby' => $satuan->modifiedby
        ]);

        return $satuan;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $satuan = Satuan::find($data['Id'][$i]);

            $satuan->statusaktif = $statusnonaktif->id;
            $satuan->modifiedby = auth('api')->user()->name;
            $satuan->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($satuan->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($satuan->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF SATUAN',
                    'idtrans' => $satuan->id,
                    'nobuktitrans' => $satuan->id,
                    'aksi' => $aksi,
                    'datajson' => $satuan->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $satuan;
    }
}
