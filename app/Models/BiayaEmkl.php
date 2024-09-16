<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class BiayaEmkl extends MyModel
{
    use HasFactory;

    protected $table = 'biayaemkl';

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

        $orderanTrucking = DB::table('jobemklrincianbiaya')
            ->from(
                DB::raw("jobemklrincianbiaya as a with (readuncommitted)")
            )
            ->select(
                'a.biayaemkl_id'
            )
            ->where('a.biayaemkl_id', '=', $id)
            ->first();
        if (isset($orderanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Job EMkl',
            ];
            goto selesai;
        }

        $suratPengantar = DB::table('invoiceemkldetailrincianbiaya')
            ->from(
                DB::raw("invoiceemkldetailrincianbiaya as a with (readuncommitted)")
            )
            ->select(
                'a.biayaemkl_id'
            )
            ->where('a.biayaemkl_id', '=', $id)
            ->first();
        if (isset($suratPengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Invoice Emkl',
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
                'biayaemkl.id',
                'biayaemkl.kodebiayaemkl',
                'biayaemkl.keterangan',
                'parameter.memo as statusaktif',
                'biayaemkl.modifiedby',
                'biayaemkl.created_at',
                'biayaemkl.updated_at',
                DB::raw("'Laporan Biaya Emkl' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'biayaemkl.statusaktif', '=', 'parameter.id');




        $this->filter($query);

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('biayaemkl.statusaktif', '=', $statusaktif->id);
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
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'biayaemkl.id',
                'biayaemkl.kodebiayaemkl',
                'biayaemkl.keterangan',
                'biayaemkl.statusaktif',
                'parameter.text as statusaktifnama',
                'biayaemkl.modifiedby',
                'biayaemkl.created_at',
                'biayaemkl.updated_at',
            )
            ->where('biayaemkl.id', $id)
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'biayaemkl.statusaktif', '=', 'parameter.id');
        return $query->first();
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
                'memo',
                'text',
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();
        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id, "statusaktifnama" => $statusaktif->text]);

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

        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.kodebiayaemkl,
            $this->table.keterangan,
            'parameter.text as statusaktif',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'biayaemkl.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodebiayaemkl', 50)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodebiayaemkl',  'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                                // $query = $query->where('biayaemkl.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw('biayaemkl' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
                                    // $query = $query->orWhere('biayaemkl.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw('biayaemkl' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data, BiayaEmkl $biayaemkl): BiayaEmkl
    {
        $biayaemkl->kodebiayaemkl = $data['kodebiayaemkl'];
        $biayaemkl->statusaktif = $data['statusaktif'];
        $biayaemkl->keterangan = $data['keterangan'] ?? '';
        $biayaemkl->modifiedby = auth('api')->user()->name;
        $biayaemkl->info = html_entity_decode(request()->info);
        // $request->sortname = $request->sortname ?? 'id';
        // $request->sortorder = $request->sortorder ?? 'asc';

        if (!$biayaemkl->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($biayaemkl->getTable()),
            'postingdari' => 'ENTRY BIAYA EMKL',
            'idtrans' => $biayaemkl->id,
            'nobuktitrans' => $biayaemkl->id,
            'aksi' => 'ENTRY',
            'datajson' => $biayaemkl->toArray(),
            'modifiedby' => $biayaemkl->modifiedby
        ]);

        return $biayaemkl;
    }

    public function processUpdate(BiayaEmkl $biayaemkl, array $data): BiayaEmkl
    {
        $biayaemkl->kodebiayaemkl = $data['kodebiayaemkl'];
        $biayaemkl->keterangan = $data['keterangan'] ?? '';
        $biayaemkl->statusaktif = $data['statusaktif'];
        $biayaemkl->modifiedby = auth('api')->user()->name;
        $biayaemkl->info = html_entity_decode(request()->info);


        if (!$biayaemkl->save()) {
            throw new \Exception("Error update service in header.");
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($biayaemkl->getTable()),
            'postingdari' => 'EDIT BIAYA EMKL',
            'idtrans' => $biayaemkl->id,
            'nobuktitrans' => $biayaemkl->id,
            'aksi' => 'EDIT',
            'datajson' => $biayaemkl->toArray(),
            'modifiedby' => $biayaemkl->modifiedby
        ]);

        return $biayaemkl;
    }

    public function processDestroy(BiayaEmkl $biayaEmkl): BiayaEmkl
    {
        // $biayaEmkl = new biayaEmkl();
        $biayaEmkl = $biayaEmkl->lockAndDestroy($biayaEmkl->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($biayaEmkl->getTable()),
            'postingdari' => 'DELETE BIAYA EMKL',
            'idtrans' => $biayaEmkl->id,
            'nobuktitrans' => $biayaEmkl->id,
            'aksi' => 'DELETE',
            'datajson' => $biayaEmkl->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $biayaEmkl;
    }

    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $biayaemkl = BiayaEmkl::find($data['Id'][$i]);

            $biayaemkl->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($biayaemkl->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($biayaemkl->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF BIAYA EMKL',
                    'idtrans' => $biayaemkl->id,
                    'nobuktitrans' => $biayaemkl->id,
                    'aksi' => $aksi,
                    'datajson' => $biayaemkl->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $biayaemkl;
    }

    public function processApprovalaktif(array $data)
    {
        $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $biayaemkl = BiayaEmkl::find($data['Id'][$i]);

            $biayaemkl->statusaktif = $statusaktif->id;
            $aksi = $statusaktif->text;

            if ($biayaemkl->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($biayaemkl->getTable()),
                    'postingdari' => 'APPROVAL AKTIF BIAYA EMKL',
                    'idtrans' => $biayaemkl->id,
                    'nobuktitrans' => $biayaemkl->id,
                    'aksi' => $aksi,
                    'datajson' => $biayaemkl->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $biayaemkl;
    }

}
