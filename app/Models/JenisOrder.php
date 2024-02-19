<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JenisOrder extends MyModel
{
    use HasFactory;

    protected $table = 'jenisorder';

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

        $orderanTrucking = DB::table('orderantrucking')
            ->from(
                DB::raw("orderantrucking as a with (readuncommitted)")
            )
            ->select(
                'a.jenisorder_id'
            )
            ->where('a.jenisorder_id', '=', $id)
            ->first();
        if (isset($orderanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Orderan Trucking',
            ];
            goto selesai;
        }

        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.jenisorder_id'
            )
            ->where('a.jenisorder_id', '=', $id)
            ->first();
        if (isset($suratPengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];
            goto selesai;
        }

        $invoiceHeader = DB::table('invoiceheader')
            ->from(
                DB::raw("invoiceheader as a with (readuncommitted)")
            )
            ->select(
                'a.jenisorder_id'
            )
            ->where('a.jenisorder_id', '=', $id)
            ->first();
        if (isset($invoiceHeader)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Invoice',
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
                'jenisorder.id',
                'jenisorder.kodejenisorder',
                'jenisorder.keterangan',
                'parameter.memo as statusaktif',
                'jenisorder.modifiedby',
                'jenisorder.created_at',
                'jenisorder.updated_at',
                DB::raw("'Laporan Jenis Order' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'jenisorder.statusaktif', '=', 'parameter.id');




        $this->filter($query);

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('jenisorder.statusaktif', '=', $statusaktif->id);
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
                'jenisorder.id',
                'jenisorder.kodejenisorder',
                'jenisorder.keterangan',
                'jenisorder.statusaktif',
                'parameter.text as statusaktifnama',
                'jenisorder.modifiedby',
                'jenisorder.created_at',
                'jenisorder.updated_at',
            )
            ->where('jenisorder.id', $id)
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'jenisorder.statusaktif', '=', 'parameter.id');
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
            $this->table.kodejenisorder,
            $this->table.keterangan,
            'parameter.text as statusaktif',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'jenisorder.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodejenisorder', 50)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodejenisorder',  'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                                // $query = $query->where('jenisorder.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw('jenisorder' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
                                    // $query = $query->orWhere('jenisorder.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw('jenisorder' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data): JenisOrder
    {
        $jenisorder = new JenisOrder();
        $jenisorder->kodejenisorder = $data['kodejenisorder'];
        $jenisorder->statusaktif = $data['statusaktif'];
        $jenisorder->keterangan = $data['keterangan'] ?? '';
        $jenisorder->modifiedby = auth('api')->user()->name;
        $jenisorder->info = html_entity_decode(request()->info);
        // $request->sortname = $request->sortname ?? 'id';
        // $request->sortorder = $request->sortorder ?? 'asc';

        if (!$jenisorder->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jenisorder->getTable()),
            'postingdari' => 'ENTRY JENIS ORDER',
            'idtrans' => $jenisorder->id,
            'nobuktitrans' => $jenisorder->id,
            'aksi' => 'ENTRY',
            'datajson' => $jenisorder->toArray(),
            'modifiedby' => $jenisorder->modifiedby
        ]);

        return $jenisorder;
    }

    public function processUpdate(JenisOrder $jenisorder, array $data): JenisOrder
    {
        $jenisorder->kodejenisorder = $data['kodejenisorder'];
        $jenisorder->keterangan = $data['keterangan'] ?? '';
        $jenisorder->statusaktif = $data['statusaktif'];
        $jenisorder->modifiedby = auth('api')->user()->name;
        $jenisorder->info = html_entity_decode(request()->info);


        if (!$jenisorder->save()) {
            throw new \Exception("Error update service in header.");
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jenisorder->getTable()),
            'postingdari' => 'EDIT JENIS ORDER',
            'idtrans' => $jenisorder->id,
            'nobuktitrans' => $jenisorder->id,
            'aksi' => 'EDIT',
            'datajson' => $jenisorder->toArray(),
            'modifiedby' => $jenisorder->modifiedby
        ]);

        return $jenisorder;
    }

    public function processDestroy($id): JenisOrder
    {
        $jenisOrder = new JenisOrder();
        $jenisOrder = $jenisOrder->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jenisOrder->getTable()),
            'postingdari' => 'DELETE JENIS ORDER',
            'idtrans' => $jenisOrder->id,
            'nobuktitrans' => $jenisOrder->id,
            'aksi' => 'DELETE',
            'datajson' => $jenisOrder->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $jenisOrder;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $jenisorder = JenisOrder::find($data['Id'][$i]);

            $jenisorder->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($jenisorder->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($jenisorder->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF JENIS ORDER',
                    'idtrans' => $jenisorder->id,
                    'nobuktitrans' => $jenisorder->id,
                    'aksi' => $aksi,
                    'datajson' => $jenisorder->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $jenisorder;
    }
}
