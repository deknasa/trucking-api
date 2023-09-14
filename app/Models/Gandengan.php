<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Gandengan extends MyModel
{
    use HasFactory;

    protected $table = 'gandengan';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasihapus($id)
    {
        // cek sudah ada absensi


        $pengeluaranStok = DB::table('pengeluaranstokheader')
            ->from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.gandengan_id'
            )
            ->where('a.gandengan_id', '=', $id)
            ->first();
        if (isset($pengeluaranStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Stok',
            ];
            goto selesai;
        }
        $penerimaanStok = DB::table('penerimaanstokheader')
            ->from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.gandengan_id'
            )
            ->where('a.gandengan_id', '=', $id)
            ->first();
        if (isset($penerimaanStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Stok',
            ];
            goto selesai;
        }


        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.gandengan_id'
            )
            ->where('a.gandengan_id', '=', $id)
            ->first();
        if (isset($suratPengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
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
        $asal = request()->asal ?? '';

        if ($asal == 'YA') {
            $statusGandengan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS GANDENGAN')->where('text', 'TINGGAL CONTAINER')->first();
            $belawan = DB::table("kota")->from(DB::raw("kota with (readuncommitted)"))->where('kodekota', 'BELAWAN')->first();

            $tempStatus = '##tempstatus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempStatus, function ($table) {
                $table->unsignedBigInteger('id');
                $table->string('jobtrucking');
            });

            $getStatus = DB::table("gandengan")->from(DB::raw("gandengan with (readuncommitted)"))
                    ->select('gandengan.id','suratpengantar.jobtrucking')
                    ->join(DB::raw("suratpengantar with (readuncommitted)"), 'suratpengantar.gandengan_id', 'gandengan.id')
                    ->where('suratpengantar.statusgandengan', $statusGandengan->id);

            DB::table($tempStatus)->insertUsing([
                'id',
                'jobtrucking'
            ], $getStatus);

            $tempJob = '##tempjob' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempJob, function ($table) {
                $table->string('jobtrucking');
            });

            $getJob = DB::table("$tempStatus")->from(DB::raw("$tempStatus as a with (readuncommitted)"))
                    ->select('a.jobtrucking')
                    ->join(DB::raw("suratpengantar as b with (readuncommitted)"), 'a.jobtrucking', 'b.jobtrucking')
                    ->where('b.sampai_id', $belawan->id);

            DB::table($tempJob)->insertUsing([
                'jobtrucking'
            ], $getJob);

            $tempAll = '##tempall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempAll, function ($table) {
                $table->unsignedBigInteger('id');
                $table->string('jobtrucking');
            });

            $getAll = DB::table("$tempStatus")->from(DB::raw("$tempStatus as a with (readuncommitted)"))
                    ->select('a.*')
                    ->leftJoin(DB::raw("$tempJob as b with (readuncommitted)"), 'a.jobtrucking', 'b.jobtrucking')
                    ->whereRaw("ISNULL(b.jobtrucking, '') = ''");

            DB::table($tempAll)->insertUsing([
                'id',
                'jobtrucking'
            ], $getAll);
        }

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'gandengan.id',
                'gandengan.kodegandengan',
                'gandengan.keterangan',
                'trado.kodetrado as trado',
                'gandengan.jumlahroda',
                'gandengan.jumlahbanserap',
                'parameter.memo as statusaktif',
                'gandengan.modifiedby',
                'gandengan.created_at',
                'gandengan.updated_at',
                DB::raw("'Laporan Gandengan' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'gandengan.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'gandengan.trado_id', '=', 'trado.id');



        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('gandengan.statusaktif', '=', $statusaktif->id);
        }

        if ($asal == 'YA') {
            $query->whereRaw("gandengan.id in (select id from $tempAll)");
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

    public function find($id)
    {
        $this->setRequestParameters();
     
        $data = DB::table("gandengan")->from(DB::raw("gandengan with (readuncommitted)"))
        
            ->select(
                'gandengan.id',
                'gandengan.kodegandengan',
                'gandengan.keterangan',
                'gandengan.trado_id',
                'trado.kodetrado as trado',
                'gandengan.jumlahroda',
                'gandengan.jumlahbanserap',
                'gandengan.statusaktif',
                'gandengan.modifiedby',
                'gandengan.created_at',
                'gandengan.updated_at',
            )
            
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'gandengan.trado_id', '=', 'trado.id')
            ->where('gandengan.id', $id)
            ->first();

            // dd("her");

        return $data;
    }


    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "$this->table.id",
                "$this->table.kodegandengan",
                "$this->table.keterangan",
                "$this->table.trado_id",
                "$this->table.jumlahroda",
                "$this->table.jumlahbanserap",
                "parameter.text as statusaktif",
                "$this->table.modifiedby",
                "$this->table.created_at",
                "$this->table.updated_at",
            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'gandengan.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodegandengan', 500)->nullable();
            $table->string('keterangan', 500)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->integer('jumlahroda')->length(11)->nullable();
            $table->integer('jumlahbanserap')->length(11)->nullable();
            $table->string('statusaktif', 500)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'kodegandengan',
            'keterangan',
            'trado_id',
            'jumlahroda',
            'jumlahbanserap',
            'statusaktif',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

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
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'trado') {
                            $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'gandengan') {
                            $query = $query->where('jumlahroda', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'gandengan') {
                            $query = $query->where('jumlahbanserap', 'LIKE', "%$filters[data]%");
                        }  else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
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
                            } elseif ($filters['field'] == 'trado') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'gandengan') {
                                $query = $query->orWhere('jumlahroda', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'gandengan') {
                                $query = $query->orWhere('jumlahbanserap', 'LIKE', "%$filters[data]%");
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

    public function processStore(array $data): Gandengan
    {
        $gandengan = new Gandengan();
        $gandengan->kodegandengan = $data['kodegandengan'];
        $gandengan->keterangan = $data['keterangan'] ?? '';
        $gandengan->trado_id = $data['trado_id'] ?? '';
        $gandengan->jumlahroda = $data['jumlahroda'];
        $gandengan->jumlahbanserap = $data['jumlahbanserap'];
        $gandengan->statusaktif = $data['statusaktif'];
        $gandengan->modifiedby = auth('api')->user()->name;

        if (!$gandengan->save()) {
            throw new \Exception('Error storing gandengan.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($gandengan->getTable()),
            'postingdari' => 'ENTRY GANDENGAN',
            'idtrans' => $gandengan->id,
            'nobuktitrans' => $gandengan->id,
            'aksi' => 'ENTRY',
            'datajson' => $gandengan->toArray(),
            'modifiedby' => $gandengan->modifiedby
        ]);

        // $param1 = $gandengan->id;
        // $param2 = $gandengan->modifiedby;
        // $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
        //     ->select(DB::raw(
        //         "stok.id as stok_id,
        //         0  as gudang_id,
        //     0 as trado_id,"
        //             . $param1 . " as gandengan_id,
        //     0 as qty,'"
        //             . $param2 . "' as modifiedby"
        //     ))
        //     ->leftjoin('stokpersediaan', function ($join) use ($param1) {
        //         $join->on('stokpersediaan.stok_id', '=', 'stok.id');
        //         $join->on('stokpersediaan.gandengan_id', '=', DB::raw("'" . $param1 . "'"));
        //     })
        //     ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);

        // $datadetail = json_decode($stokgudang->get(), true);

        // $dataexist = $stokgudang->exists();
        // $detaillogtrail = [];
        // foreach ($datadetail as $item) {

        //     $stokpersediaan = new StokPersediaan();
        //     $stokpersediaan->stok_id = $item['stok_id'];
        //     $stokpersediaan->gudang_id = $item['gudang_id'];
        //     $stokpersediaan->trado_id = $item['trado_id'];
        //     $stokpersediaan->gandengan_id = $item['gandengan_id'];
        //     $stokpersediaan->qty = $item['qty'];
        //     $stokpersediaan->modifiedby = $item['modifiedby'];
        //     $stokpersediaan->save();
        //     $detaillogtrail[] = $stokpersediaan->toArray();
        // }

        // if (!$dataexist == true) {
        //     throw new \Exception('Error storing gandengan.');
        // }

        // (new LogTrail())->processStore([
        //     'namatabel' => strtoupper($stokpersediaan->getTable()),
        //     'postingdari' => 'STOK PERSEDIAAN',
        //     'idtrans' => $gandengan->id,
        //     'nobuktitrans' => $gandengan->id,
        //     'aksi' => 'EDIT',
        //     'datajson' => json_encode($detaillogtrail),
        //     'modifiedby' => $gandengan->modifiedby
        // ]);

        return $gandengan;
    }

    public function processUpdate(Gandengan $gandengan, array $data): Gandengan
    {
        $gandengan->kodegandengan = $data['kodegandengan'];
        $gandengan->keterangan = $data['keterangan'] ?? '';
        $gandengan->trado_id = $data['trado_id'] ?? '';
        $gandengan->jumlahroda = $data['jumlahroda'];
        $gandengan->jumlahbanserap = $data['jumlahbanserap'];
        $gandengan->statusaktif = $data['statusaktif'];
        $gandengan->modifiedby = auth('api')->user()->user;

        if (!$gandengan->save()) {
            throw new \Exception('Error updating gandengan.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($gandengan->getTable()),
            'postingdari' => 'EDIT GANDENGAN',
            'idtrans' => $gandengan->id,
            'nobuktitrans' => $gandengan->id,
            'aksi' => 'EDIT',
            'datajson' => $gandengan->toArray(),
            'modifiedby' => $gandengan->modifiedby
        ]);

        // $param1 = $gandengan->id;
        // $param2 = $gandengan->modifiedby;
        // $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
        //     ->select(DB::raw(
        //         "stok.id as stok_id,
        //         0  as gudang_id,
        //     0 as trado_id,"
        //             . $param1 . " as gandengan_id,
        //     0 as qty,'"
        //             . $param2 . "' as modifiedby"
        //     ))
        //     ->leftjoin('stokpersediaan', function ($join) use ($param1) {
        //         $join->on('stokpersediaan.stok_id', '=', 'stok.id');
        //         $join->on('stokpersediaan.gandengan_id', '=', DB::raw("'" . $param1 . "'"));
        //     })
        //     ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);

        // $datadetail = json_decode($stokgudang->get(), true);
        // $dataexist = $stokgudang->exists();
        // $detaillogtrail = [];
        // foreach ($datadetail as $item) {
        //     $stokpersediaan = new StokPersediaan();
        //     $stokpersediaan->stok_id = $item['stok_id'];
        //     $stokpersediaan->gudang_id = $item['gudang_id'];
        //     $stokpersediaan->trado_id = $item['trado_id'];
        //     $stokpersediaan->gandengan_id = $item['gandengan_id'];
        //     $stokpersediaan->qty = $item['qty'];
        //     $stokpersediaan->modifiedby = $item['modifiedby'];
        //     if (!$stokpersediaan->save()) {
        //         throw new \Exception('Error store stok persediaan.');
        //     }
        //     $detaillogtrail[] = $stokpersediaan->toArray();
        // }

        // if ($dataexist == true) {
        //     (new LogTrail())->processStore([
        //         'namatabel' => strtoupper($stokpersediaan->getTable()),
        //         'postingdari' => 'STOK PERSEDIAAN',
        //         'idtrans' => $gandengan->id,
        //         'nobuktitrans' => $gandengan->id,
        //         'aksi' => 'EDIT',
        //         'datajson' => json_encode($detaillogtrail),
        //         'modifiedby' => $gandengan->modifiedby
        //     ]);
        // }

        return $gandengan;
    }

    public function processDestroy($id): Gandengan
    {
        $gandengan = new Gandengan();
        $gandengan = $gandengan->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($gandengan->getTable()),
            'postingdari' => 'DELETE GANDENGAN',
            'idtrans' => $gandengan->id,
            'nobuktitrans' => $gandengan->id,
            'aksi' => 'DELETE',
            'datajson' => $gandengan->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $gandengan;
    }
}
