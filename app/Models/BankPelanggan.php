<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BankPelanggan extends MyModel
{
    use HasFactory;

    protected $table = 'bankpelanggan';

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
        $penerimaanDetail = DB::table('penerimaandetail')
            ->from(
                DB::raw("penerimaandetail as a with (readuncommitted)")
            )
            ->select(
                'a.bankpelanggan_id'
            )
            ->where('a.bankpelanggan_id', '=', $id)
            ->first();
        if (isset($penerimaanDetail)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan',
            ];
            goto selesai;
        }

        $penerimaanGiroDetail = DB::table('penerimaangirodetail')
            ->from(
                DB::raw("penerimaangirodetail as a with (readuncommitted)")
            )
            ->select(
                'a.bankpelanggan_id'
            )
            ->where('a.bankpelanggan_id', '=', $id)
            ->first();
        if (isset($penerimaanGiroDetail)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Giro',
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

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'bankpelanggan.id',
                'bankpelanggan.kodebank',
                'bankpelanggan.namabank',
                'bankpelanggan.keterangan',
                'parameter.memo as statusaktif',
                'bankpelanggan.modifiedby',
                'bankpelanggan.created_at',
                'bankpelanggan.updated_at',
                DB::raw("'Laporan Bank Pelanggan' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'bankpelanggan.statusaktif', '=', 'parameter.id');

        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('bankpelanggan.statusaktif', '=', $statusaktif->id);
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

        $data = BankPelanggan::from(DB::raw("bankpelanggan with (readuncommitted)"))
            ->select(
                'bankpelanggan.id',
                'bankpelanggan.kodebank',
                'bankpelanggan.namabank',
                'bankpelanggan.keterangan',
                'bankpelanggan.statusaktif',
                'parameter.text as statusaktifnama',
                'bankpelanggan.modifiedby',
                'bankpelanggan.created_at',
                'bankpelanggan.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'bankpelanggan.statusaktif', '=', 'parameter.id')
            ->where('bankpelanggan.id', $id)->first();

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
                'memo',
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
                $this->table.kodebank,
                $this->table.namabank,
                $this->table.keterangan,
                'parameter.text as statusaktif',
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
            ")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'bankpelanggan.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodebank', 1000)->nullable();
            $table->string('namabank', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodebank', 'namabank', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);

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
                            $query = $query->where('parameter.text', '=', $filters['data']);
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
                                $query = $query->orWhere('parameter.text', '=', $filters['data']);
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

    public function processStore(array $data, BankPelanggan $bankpelanggan): BankPelanggan
    {
        // $bankpelanggan = new BankPelanggan();
        $bankpelanggan->kodebank = $data['kodebank'];
        $bankpelanggan->namabank = $data['namabank'];
        $bankpelanggan->keterangan = $data['keterangan'] ?? '';
        $bankpelanggan->statusaktif = $data['statusaktif'];
        $bankpelanggan->modifiedby = auth('api')->user()->name;
        $bankpelanggan->info = html_entity_decode(request()->info);
        $bankpelanggan->tas_id = $data['tas_id'] ?? '';

        if (!$bankpelanggan->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($bankpelanggan->getTable()),
            'postingdari' => 'ENTRY BANK PELANGGAN',
            'idtrans' => $bankpelanggan->id,
            'nobuktitrans' => $bankpelanggan->id,
            'aksi' => 'ENTRY',
            'datajson' => $bankpelanggan->toArray(),
            'modifiedby' => $bankpelanggan->modifiedby
        ]);

        return $bankpelanggan;
    }

    public function processUpdate(BankPelanggan $bankpelanggan, array $data): BankPelanggan
    {
        $bankpelanggan->kodebank = $data['kodebank'];
        $bankpelanggan->namabank = $data['namabank'];
        $bankpelanggan->keterangan = $data['keterangan'];
        $bankpelanggan->statusaktif = $data['statusaktif'];
        $bankpelanggan->modifiedby = auth('api')->user()->name;
        $bankpelanggan->info = html_entity_decode(request()->info);

        if (!$bankpelanggan->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($bankpelanggan->getTable()),
            'postingdari' => 'EDIT BankPelangganController',
            'idtrans' => $bankpelanggan->id,
            'nobuktitrans' => $bankpelanggan->id,
            'aksi' => 'EDIT',
            'datajson' => $bankpelanggan->toArray(),
            'modifiedby' => $bankpelanggan->modifiedby
        ]);

        return $bankpelanggan;
    }

    public function processDestroy(BankPelanggan $bankPelanggan): BankPelanggan
    {
        // $bankPelanggan = new BankPelanggan();
        $bankPelanggan = $bankPelanggan->lockAndDestroy($bankPelanggan->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($bankPelanggan->getTable()),
            'postingdari' => 'DELETE BANKPELANGGAN',
            'idtrans' => $bankPelanggan->id,
            'nobuktitrans' => $bankPelanggan->id,
            'aksi' => 'DELETE',
            'datajson' => $bankPelanggan->toArray(),
            'modifiedby' => $bankPelanggan->modifiedby
        ]);

        return $bankPelanggan;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $bankPelanggan = $this->where('id',$data['Id'][$i])->first();

            $bankPelanggan->statusaktif = $statusnonaktif->id;
            $bankPelanggan->modifiedby = auth('api')->user()->name;
            $bankPelanggan->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($bankPelanggan->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($bankPelanggan->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF Bank Pelanggan ',
                    'idtrans' => $bankPelanggan->id,
                    'nobuktitrans' => $bankPelanggan->id,
                    'aksi' => $aksi,
                    'datajson' => $bankPelanggan->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $bankPelanggan;
    }
}
