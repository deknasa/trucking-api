<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengeluaranStok extends MyModel
{
    use HasFactory;

    protected $table = 'PengeluaranStok';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasihapus($id)
    {

        $pengeluaranStok = DB::table('pengeluaranstokheader')
            ->from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluaranstok_id'
            )
            ->where('a.pengeluaranstok_id', '=', $id)
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

        // $roleinput = request()->roleinput ?? '';
        // $user_id = auth('api')->user()->id ?? 0;

        // $temprole = '##temprole' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($temprole, function ($table) {
        //     $table->bigInteger('aco_id')->nullable();
        // });

        // $queryaco = db::table("useracl")->from(db::raw("useracl a with (readuncommitted)"))
        //     ->select('a.aco_id')
        //     ->join(db::raw("pengeluaranstok b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
        //     ->where('a.user_id', $user_id);

        // DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


        // $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
        //     ->select('a.aco_id')
        //     ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
        //     ->join(db::raw("pengeluaranstok c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
        //     ->leftjoin(db::raw($temprole . " d "), 'a.aco_id', 'd.aco_id')
        //     ->where('b.user_id', $user_id)
        //     ->whereRaw("isnull(d.aco_id,0)=0");

        // DB::table($temprole)->insertUsing(['aco_id'], $queryrole);


        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        // $query = DB::table($this->table); 
        // $query = $this->selectColumns($query);

        $query = DB::table($this->table)->select(
            'pengeluaranstok.id',
            'pengeluaranstok.kodepengeluaran',
            'pengeluaranstok.keterangan',
            'akunpusat.keterangancoa as coa',
            'parameterformat.memo as format',
            'parameterformat.text as formattext',
            'parameterformat.id as formatid',
            'parameterstatushitungstok.memo as statushitungstok',
            'parameterstatushitungstok.text as statushitungstoktext',
            'parameterstatushitungstok.id as statushitungstokid',
            'pengeluaranstok.modifiedby',
            'pengeluaranstok.created_at',
            'pengeluaranstok.updated_at',
            DB::raw("'Laporan Pengeluaran Stok' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
            ->leftJoin('akunpusat', 'pengeluaranstok.coa', '=', 'akunpusat.coa')
            ->leftJoin('parameter as parameterformat', 'pengeluaranstok.format', '=', 'parameterformat.id')
            ->leftJoin('parameter as parameterstatushitungstok', 'pengeluaranstok.statushitungstok', '=', 'parameterstatushitungstok.id');

        $this->filter($query);

        $roleinput = request()->roleinput ?? '';
        if ($roleinput != '') {
            $getParam = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp','ID CABANG')->first();
            $query->where('pengeluaranstok.cabang_id', $getParam->text)
            ->where('pengeluaranstok.statusaktif', 1);
        }
        // if ($roleinput != '') {
        //     $query->join(db::raw($temprole . " d "), 'pengeluaranstok.aco_id', 'd.aco_id');
        // }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function acos()
    {
        $roleinput = request()->roleinput ?? '';
        $user_id = auth('api')->user()->id ?? 0;

        $temprole = '##temprole' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprole, function ($table) {
            $table->bigInteger('aco_id')->nullable();
        });

        $queryaco = db::table("useracl")->from(db::raw("useracl a with (readuncommitted)"))
            ->select('a.aco_id')
            ->join(db::raw("pengeluaranstok b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
            ->where('a.user_id', $user_id);

        DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


        $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
            ->select('a.aco_id')
            ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
            ->join(db::raw("pengeluaranstok c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
            ->leftjoin(db::raw($temprole . " d "), 'a.aco_id', 'd.aco_id')
            ->where('b.user_id', $user_id)
            ->whereRaw("isnull(d.aco_id,0)=0");

        DB::table($temprole)->insertUsing(['aco_id'], $queryrole);
        $query = DB::table($this->table)->select(
            'pengeluaranstok.id',
            'pengeluaranstok.kodepengeluaran',
            'pengeluaranstok.keterangan',
            'pengeluaranstok.coa',
        );

        if ($roleinput != '') {
            $query->join(db::raw($temprole . " d "), 'pengeluaranstok.aco_id', 'd.aco_id');
        }
        $data = $query->get();

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statushitungstok')->nullable();
            $table->string('statushitungstoknama')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS HITUNG STOK')
            ->where('subgrp', '=', 'STATUS HITUNG STOK')
            ->where('default', '=', 'YA')
            ->first();
        DB::table($tempdefault)->insert(["statushitungstok" => $statusaktif->id,"statushitungstoknama" => $statusaktif->text]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statushitungstok',
                'statushitungstoknama'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->longText('kodepengeluaran')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('coa', 50)->nullable();
            $table->unsignedBigInteger('format')->nullable();
            $table->integer('statushitungstok')->length(11)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->increments('position');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);

        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'kodepengeluaran',
            'keterangan',
            'coa',
            'format',
            'statushitungstok',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

        return  $temp;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.kodepengeluaran",
            "$this->table.keterangan",
            "$this->table.coa",
            "$this->table.format",
            "$this->table.statushitungstok",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at"
        );
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
                        if ($filters['field'] == 'statushitungstok') {
                            $query = $query->where('parameterstatushitungstok.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");                         
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statushitungstok') {
                            $query = $query->orWhere('parameterstatushitungstok.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }

    public function find($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)
            ->from(
                DB::raw($this->table . " with (readuncommitted)")
            )
            ->select(
                "$this->table.id",
                "$this->table.kodepengeluaran",
                "$this->table.keterangan",
                "$this->table.coa",
                "$this->table.format",
                "$this->table.statushitungstok",
                "$this->table.modifiedby",
                "$this->table.created_at",
                "$this->table.updated_at",
                "akunpusat.keterangancoa",
                'format.text as formatnama',
                'statushitungstok.text as statushitungstoknama'
            )
            ->leftJoin('parameter as format', 'pengeluaranstok.format', '=', 'format.id')
            ->leftJoin('parameter as statushitungstok', 'pengeluaranstok.statushitungstok', '=', 'statushitungstok.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluaranstok.coa', 'akunpusat.coa');
        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data): PengeluaranStok
    {
        $pengeluaranStok = new PengeluaranStok();
        $pengeluaranStok->kodepengeluaran = $data['kodepengeluaran'];
        $pengeluaranStok->keterangan = $data['keterangan'] ?? '';
        $pengeluaranStok->coa = $data['coa'];
        $pengeluaranStok->format = $data['format'];
        $pengeluaranStok->statushitungstok = $data['statushitungstok'];
        $pengeluaranStok->modifiedby = auth('api')->user()->name;
        $pengeluaranStok->info = html_entity_decode(request()->info);

        if (!$pengeluaranStok->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranStok->getTable()),
            'postingdari' => 'ENTRY PENERIMAAN STOK',
            'idtrans' => $pengeluaranStok->id,
            'nobuktitrans' => $pengeluaranStok->id,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranStok->toArray(),
            'modifiedby' => $pengeluaranStok->modifiedby
        ]);

        return $pengeluaranStok;
    }

    public function processUpdate(PengeluaranStok $pengeluaranStok, array $data): PengeluaranStok
    {
        $pengeluaranStok->kodepengeluaran = $data['kodepengeluaran'];
        $pengeluaranStok->keterangan = $data['keterangan'] ?? '';
        $pengeluaranStok->coa = $data['coa'];
        $pengeluaranStok->format = $data['format'];
        $pengeluaranStok->statushitungstok = $data['statushitungstok'];
        $pengeluaranStok->modifiedby = auth('api')->user()->name;
        $pengeluaranStok->info = html_entity_decode(request()->info);

        if (!$pengeluaranStok->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranStok->getTable()),
            'postingdari' => 'EDIT PENERIMAAN STOK',
            'idtrans' => $pengeluaranStok->id,
            'nobuktitrans' => $pengeluaranStok->id,
            'aksi' => 'EDIT',
            'datajson' => $pengeluaranStok->toArray(),
            'modifiedby' => $pengeluaranStok->modifiedby
        ]);

        return $pengeluaranStok;
    }

    public function processDestroy($id): PengeluaranStok
    {
        $pengeluaranStok = new PengeluaranStok();
        $pengeluaranStok = $pengeluaranStok->lockAndDestroy($id);


        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranStok->getTable()),
            'postingdari' => 'DELETE PENERIMAAN STOK',
            'idtrans' => $pengeluaranStok->id,
            'nobuktitrans' => $pengeluaranStok->id,
            'aksi' => 'DELETE',
            'datajson' => $pengeluaranStok->toArray(),
            'modifiedby' => $pengeluaranStok->modifiedby
        ]);

        return $pengeluaranStok;
    }
}
