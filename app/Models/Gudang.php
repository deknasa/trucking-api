<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Gudang extends MyModel
{
    use HasFactory;

    protected $table = 'gudang';

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
        $penerimaanStok = DB::table('penerimaanstokheader')
            ->from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.gudang_id'
            )
            ->where('a.gudang_id', '=', $id)
            ->first();

        if (isset($penerimaanStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Stok',
            ];


            goto selesai;
        }

        $pengeluaranStok = DB::table('pengeluaranstokheader')
            ->from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.gudang_id'
            )
            ->where('a.gudang_id', '=', $id)
            ->first();
        if (isset($pengeluaranStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Stok',
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
        $penerimaanStokPg = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $pengeluaranStokSpk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $penerimaanstok = request()->penerimaanstok_id ?? '';
        $pengeluaranstok = request()->pengeluaranstok_id ?? '';

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'gudang.id',
                'gudang.gudang',
                'parameter.memo as statusaktif',
                'parameter.text as statusaktifnama',
                'gudang.modifiedby',
                'gudang.created_at',
                'gudang.updated_at',
                DB::raw("'Laporan Gudang' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'gudang.statusaktif', '=', 'parameter.id');




        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('gudang.statusaktif', '=', $statusaktif->id);
        }
        // dd($penerimaanStokPg)
        if ($penerimaanstok == $penerimaanStokPg->text) {
            $gudangdari_id = request()->gudangdari_id ?? 0;
            $gudangke_id = request()->gudangke_id ?? 0;
            $gudangKantor = Gudang::from(DB::raw("gudang with (readuncommitted)"))
                ->select('id')
                ->where('gudang', 'GUDANG PIHAK III');

            $gudangKantorid = Gudang::from(DB::raw("gudang with (readuncommitted)"))
                ->select('id')
                ->where('gudang', 'GUDANG PIHAK III')
                ->first();

            if (request()->gudangdarike == "ke") {
                $gudangKantor = $gudangKantor->orWhere('gudang', 'GUDANG KANTOR');
                $query->whereraw("gudang.id not in(" . $gudangKantorid->id . "," . $gudangdari_id . ")");
            }
            $gudangKantor = $gudangKantor->get();
            if (request()->gudangdarike == "dari") {
                $query->whereraw("gudang.id not in(" . $gudangke_id . ")");
            //     // $query->where('gudang.id','<>', $gudangKantorid->id);

            }
        }

        if ($pengeluaranstok == $pengeluaranStokSpk->text) {
            $namaGudang = ['GUDANG KANTOR', 'GUDANG PIHAK III', 'GUDANG SEMENTARA'];
            $query->whereNotIn('gudang.gudang', $namaGudang);
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
                'gudang.id',
                'gudang.gudang',
                'parameter.id as statusaktif',
                'parameter.text as statusaktifnama',
                'gudang.modifiedby',
                'gudang.created_at',
                'gudang.updated_at',
            )
            ->where('gudang.id', $id)
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'gudang.statusaktif', '=', 'parameter.id');
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
                'id',
                'text'
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

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.gudang,
            'parameter.text as statusaktif',
           
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )

            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'gudang.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('gudang', 100)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'gudang', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
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
                            // $query = $query->where('gudang.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('gudang' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
                                // $query = $query->orWhere('gudang.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw('gudang' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
    public function processStore(array $data, Gudang $gudang): Gudang
    {
        // $gudang = new Gudang();
        $gudang->gudang = $data['gudang'];
        $gudang->statusaktif = $data['statusaktif'];
        $gudang->tas_id = $data['tas_id'];
        $gudang->modifiedby = auth('api')->user()->name;
        $gudang->info = html_entity_decode(request()->info);

        if (!$gudang->save()) {
            throw new \Exception('Error storing gudang.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($gudang->getTable()),
            'postingdari' => 'ENTRY GUDANG',
            'idtrans' => $gudang->id,
            'nobuktitrans' => $gudang->id,
            'aksi' => 'ENTRY',
            'datajson' => $gudang->toArray(),
            'modifiedby' => $gudang->modifiedby
        ]);

        // proses stok persediaan tidak ada lagi

        // $param1 = $gudang->id;
        // $param2 = $gudang->modifiedby;

        // $statushitungstok = DB::table('parameter')->from(
        //     DB::raw("parameter with (readuncommitted)")
        // )
        //     ->select(
        //         'id'
        //     )
        //     ->where('grp', '=', 'STATUS HITUNG STOK')
        //     ->where('subgrp', '=', 'STATUS HITUNG STOK')
        //     ->where('text', '=', 'HITUNG STOK')
        //     ->first();

        // $tempmasuk = '##tempmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempmasuk, function ($table) {
        //     $table->unsignedBigInteger('stok_id')->nullable();
        //     $table->unsignedBigInteger('gudang_id')->nullable();
        //     $table->double('qty', 15, 2)->nullable();
        // });


        // $querymasuk = DB::table('penerimaanstokdetail')->from(
        //     DB::raw("penerimaanstokdetail as a with (readuncommitted)")
        // )
        //     ->select(
        //         'a.stok_id',
        //         'b.gudang_id',
        //         DB::raw("sum(a.qty) as qty"),
        //     )
        //     ->join(DB::raw("penerimaanstokheader as b"), 'a.penerimaanstokheader_id', 'b.id')
        //     ->join(DB::raw("penerimaanstok as c"), 'b.penerimaanstok_id', 'c.id')
        //     ->where('c.statushitungstok', '=', $statushitungstok->id)
        //     ->whereRaw("isnull(b.gudang_id,0)<>0")
        //     ->groupby('a.stok_id', 'b.gudang_id');

        // DB::table($tempmasuk)->insertUsing([
        //     'stok_id',
        //     'gudang_id',
        //     'qty',
        // ], $querymasuk);

        // $tempkeluar = '##tempkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempkeluar, function ($table) {
        //     $table->unsignedBigInteger('stok_id')->nullable();
        //     $table->unsignedBigInteger('gudang_id')->nullable();
        //     $table->double('qty', 15, 2)->nullable();
        // });


        // $querykeluar = DB::table('pengeluaranstokdetail')->from(
        //     DB::raw("pengeluaranstokdetail as a with (readuncommitted)")
        // )
        //     ->select(
        //         'a.stok_id',
        //         'b.gudang_id',
        //         DB::raw("sum(a.qty) as qty"),
        //     )
        //     ->join(DB::raw("pengeluaranstokheader as b"), 'a.pengeluaranstokheader_id', 'b.id')
        //     ->join(DB::raw("pengeluaranstok as c"), 'b.pengeluaranstok_id', 'c.id')
        //     ->where('c.statushitungstok', '=', $statushitungstok->id)
        //     ->whereRaw("isnull(b.gudang_id,0)<>0")
        //     ->groupby('a.stok_id', 'b.gudang_id');

        // DB::table($tempkeluar)->insertUsing([
        //     'stok_id',
        //     'gudang_id',
        //     'qty',
        // ], $querykeluar);

        // $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
        //     ->select(DB::raw(
        //         "stok.id as stok_id,"
        //             . $param1 . "  as gudang_id,
        //         0 as trado_id,
        //         0 as gandengan_id,
        //         (isnull(b.qty,0)-isnull(C.Qty,0)) as qty,'"
        //             . $param2 . "' as modifiedby"
        //     ))
        //     ->leftjoin('stokpersediaan', function ($join) use ($param1) {
        //         $join->on('stokpersediaan.stok_id', '=', 'stok.id');
        //         $join->on('stokpersediaan.gudang_id', '=', DB::raw("'" . $param1 . "'"));
        //     })
        //     ->leftjoin(DB::raw($tempmasuk . " as b"), 'stok.id', 'b.stok_id')
        //     ->leftjoin(DB::raw($tempkeluar . " as c"), 'stok.id', 'c.stok_id')

        //     ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);

        // // dd($stokgudang->get());
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
        //     throw new \Exception('Error store stok persediaan.');
        // }

        // (new LogTrail())->processStore([
        //     'namatabel' => strtoupper($stokpersediaan->getTable()),
        //     'postingdari' => 'STOK PERSEDIAAN',
        //     'idtrans' => $gudang->id,
        //     'nobuktitrans' => $gudang->id,
        //     'aksi' => 'EDIT',
        //     'datajson' => json_encode($detaillogtrail),
        //     'modifiedby' => $gudang->modifiedby
        // ]);

        return $gudang;
    }

    public function processUpdate(Gudang $gudang, array $data): Gudang
    {
        $gudang->gudang = $data['gudang'];
        $gudang->statusaktif = $data['statusaktif'];
        $gudang->modifiedby = auth('api')->user()->name;
        $gudang->info = html_entity_decode(request()->info);

        if (!$gudang->save()) {
            throw new \Exception('Error updating gudang.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($gudang->getTable()),
            'postingdari' => 'EDIT GUDANG',
            'idtrans' => $gudang->id,
            'nobuktitrans' => $gudang->id,
            'aksi' => 'EDIT',
            'datajson' => $gudang->toArray(),
            'modifiedby' => $gudang->modifiedby
        ]);
        // $param1 = $gudang->id;
        // $param2 = $gudang->modifiedby;

        // $statushitungstok = DB::table('parameter')->from(
        //     DB::raw("parameter with (readuncommitted)")
        // )
        //     ->select(
        //         'id'
        //     )
        //     ->where('grp', '=', 'STATUS HITUNG STOK')
        //     ->where('subgrp', '=', 'STATUS HITUNG STOK')
        //     ->where('text', '=', 'HITUNG STOK')
        //     ->first();

        // $tempmasuk = '##tempmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempmasuk, function ($table) {
        //     $table->unsignedBigInteger('stok_id')->nullable();
        //     $table->unsignedBigInteger('gudang_id')->nullable();
        //     $table->double('qty', 15, 2)->nullable();
        // });


        // $querymasuk = DB::table('penerimaanstokdetail')->from(
        //     DB::raw("penerimaanstokdetail as a with (readuncommitted)")
        // )
        //     ->select(
        //         'a.stok_id',
        //         'b.gudang_id',
        //         DB::raw("sum(a.qty) as qty"),
        //     )
        //     ->join(DB::raw("penerimaanstokheader as b"), 'a.penerimaanstokheader_id', 'b.id')
        //     ->join(DB::raw("penerimaanstok as c"), 'b.penerimaanstok_id', 'c.id')
        //     ->where('c.statushitungstok', '=', $statushitungstok->id)
        //     ->whereRaw("isnull(b.gudang_id,0)<>0")
        //     ->groupby('a.stok_id', 'b.gudang_id');

        // DB::table($tempmasuk)->insertUsing([
        //     'stok_id',
        //     'gudang_id',
        //     'qty',
        // ], $querymasuk);

        // $tempkeluar = '##tempkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempkeluar, function ($table) {
        //     $table->unsignedBigInteger('stok_id')->nullable();
        //     $table->unsignedBigInteger('gudang_id')->nullable();
        //     $table->double('qty', 15, 2)->nullable();
        // });


        // $querykeluar = DB::table('pengeluaranstokdetail')->from(
        //     DB::raw("pengeluaranstokdetail as a with (readuncommitted)")
        // )
        //     ->select(
        //         'a.stok_id',
        //         'b.gudang_id',
        //         DB::raw("sum(a.qty) as qty"),
        //     )
        //     ->join(DB::raw("pengeluaranstokheader as b"), 'a.pengeluaranstokheader_id', 'b.id')
        //     ->join(DB::raw("pengeluaranstok as c"), 'b.pengeluaranstok_id', 'c.id')
        //     ->where('c.statushitungstok', '=', $statushitungstok->id)
        //     ->whereRaw("isnull(b.gudang_id,0)<>0")
        //     ->groupby('a.stok_id', 'b.gudang_id');

        // DB::table($tempkeluar)->insertUsing([
        //     'stok_id',
        //     'gudang_id',
        //     'qty',
        // ], $querykeluar);

        // $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
        //     ->select(DB::raw(
        //         "stok.id as stok_id,"
        //             . $param1 . "  as gudang_id,
        //         0 as trado_id,
        //         0 as gandengan_id,
        //         (isnull(b.qty,0)-isnull(C.Qty,0)) as qty,'"
        //             . $param2 . "' as modifiedby"
        //     ))
        //     ->leftjoin('stokpersediaan', function ($join) use ($param1) {
        //         $join->on('stokpersediaan.stok_id', '=', 'stok.id');
        //         $join->on('stokpersediaan.gudang_id', '=', DB::raw("'" . $param1 . "'"));
        //     })
        //     ->leftjoin(DB::raw($tempmasuk . " as b"), 'stok.id', 'b.stok_id')
        //     ->leftjoin(DB::raw($tempkeluar . " as c"), 'stok.id', 'c.stok_id')

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
        //     if (!$stokpersediaan->save()) {
        //         throw new \Exception('Error store stok persediaan.');
        //     }
        //     $detaillogtrail[] = $stokpersediaan->toArray();
        // }

        // if ($dataexist == true) {
        //     (new LogTrail())->processStore([
        //         'namatabel' => strtoupper($stokpersediaan->getTable()),
        //         'postingdari' => 'STOK PERSEDIAAN',
        //         'idtrans' => $gudang->id,
        //         'nobuktitrans' => $gudang->id,
        //         'aksi' => 'EDIT',
        //         'datajson' => json_encode($detaillogtrail),
        //         'modifiedby' => $gudang->modifiedby
        //     ]);
        // }

        return $gudang;
    }

    public function processDestroy(Gudang $gudang): Gudang
    {
        // $gudang = new Gudang();
        $gudang = $gudang->lockAndDestroy($gudang->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($gudang->getTable()),
            'postingdari' => 'DELETE GUDANG',
            'idtrans' => $gudang->id,
            'nobuktitrans' => $gudang->id,
            'aksi' => 'DELETE',
            'datajson' => $gudang->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $gudang;
    }

    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $gudang = Gudang::find($data['Id'][$i]);

            $gudang->statusaktif = $statusnonaktif->id;
            $gudang->modifiedby = auth('api')->user()->name;
            $gudang->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($gudang->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($gudang->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF GUDANG',
                    'idtrans' => $gudang->id,
                    'nobuktitrans' => $gudang->id,
                    'aksi' => $aksi,
                    'datajson' => $gudang->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $gudang;
    }
}
