<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanTrucking extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaantrucking';

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

        $penerimaanTrucking = DB::table('penerimaantruckingheader')
            ->from(
                DB::raw("penerimaantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaantrucking_id'
            )
            ->where('a.penerimaantrucking_id', '=', $id)
            ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Trucking',
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
                'memo',
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

    public function get()
    {
        $this->setRequestParameters();
        $roleinput = request()->roleinput ?? '';
        $isLookup = request()->isLookup ?? '';
        $user_id = auth('api')->user()->id ?? 0;

        // $temprole = '##temprole' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($temprole, function ($table) {
        //     $table->bigInteger('aco_id')->nullable();
        // });

        // $queryaco = db::table("useracl")->from(db::raw("useracl a with (readuncommitted)"))
        //     ->select('a.aco_id')
        //     ->join(db::raw("penerimaantrucking b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
        //     ->where('a.user_id', $user_id);

        // DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


        // $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
        //     ->select('a.aco_id')
        //     ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
        //     ->join(db::raw("penerimaantrucking c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
        //     ->leftjoin(db::raw($temprole . " d "), 'a.aco_id', 'd.aco_id')
        //     ->where('b.user_id', $user_id)
        //     ->whereRaw("isnull(d.aco_id,0)=0");

        // DB::table($temprole)->insertUsing(['aco_id'], $queryrole);



        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $cabang_id = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'ID CABANG')
            ->where('subgrp', 'ID CABANG')
            ->first()->text ?? '';

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'penerimaantrucking.id',
                'penerimaantrucking.kodepenerimaan',
                'penerimaantrucking.keterangan',
                'penerimaantrucking.coadebet',
                'penerimaantrucking.coakredit',
                'penerimaantrucking.coapostingdebet',
                'penerimaantrucking.coapostingkredit',
                'debet.keterangancoa as coadebet_keterangan',
                'kredit.keterangancoa as coakredit_keterangan',
                'postingdebet.keterangancoa as coapostingdebet_keterangan',
                'postingkredit.keterangancoa as coapostingkredit_keterangan',
                'parameter.memo as format',
                'penerimaantrucking.created_at',
                'penerimaantrucking.modifiedby',
                'penerimaantrucking.updated_at',
                'statusaktif.memo as statusaktif',
                DB::raw("'Laporan Penerimaan Trucking' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("akunpusat as debet  with (readuncommitted)"), "penerimaantrucking.coadebet", "debet.coa")
            ->leftJoin(DB::raw("akunpusat as kredit  with (readuncommitted)"), "penerimaantrucking.coakredit", "kredit.coa")
            ->leftJoin(DB::raw("akunpusat as postingdebet  with (readuncommitted)"), "penerimaantrucking.coapostingdebet", "postingdebet.coa")
            ->leftJoin(DB::raw("akunpusat as postingkredit  with (readuncommitted)"), "penerimaantrucking.coapostingkredit", "postingkredit.coa")
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'penerimaantrucking.format', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'penerimaantrucking.statusaktif', '=', 'statusaktif.id')
            ->where('penerimaantrucking.cabang_id', $cabang_id);


        $this->filter($query);

        if ($roleinput != '') {
            $getParam = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'ID CABANG')->first();
            $query->where('penerimaantrucking.cabang_id', $getParam->text)
                ->where('penerimaantrucking.statusaktif', 1);
        }
        if ($isLookup != '') {
            $temprole = '##temprole' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temprole, function ($table) {
                $table->bigInteger('aco_id')->nullable();
            });

            $queryaco = db::table("useracl")->from(db::raw("useracl a with (readuncommitted)"))
                ->select('a.aco_id')
                ->join(db::raw("penerimaantrucking b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
                ->where('a.user_id', $user_id);
            DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


            $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
                ->select('a.aco_id')
                ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
                ->join(db::raw("penerimaantrucking c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
                ->leftjoin(db::raw($temprole . " d "), 'a.aco_id', 'd.aco_id')
                ->where('b.user_id', $user_id)
                ->whereRaw("isnull(d.aco_id,0)=0")
                ->where('c.cabang_id', $cabang_id);

            DB::table($temprole)->insertUsing(['aco_id'], $queryrole);
            $query
                ->join($temprole, 'penerimaantrucking.aco_id', $temprole . '.aco_id');
        }
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
            ->join(db::raw("penerimaantrucking b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
            ->where('a.user_id', $user_id);

        DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


        $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
            ->select('a.aco_id')
            ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
            ->join(db::raw("penerimaantrucking c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
            ->leftjoin(db::raw($temprole . " d "), 'a.aco_id', 'd.aco_id')
            ->where('b.user_id', $user_id)
            ->whereRaw("isnull(d.aco_id,0)=0");

        DB::table($temprole)->insertUsing(['aco_id'], $queryrole);

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'penerimaantrucking.id',
                'penerimaantrucking.kodepenerimaan',
                'penerimaantrucking.keterangan',
            );

        if ($roleinput != '') {
            $query->join(db::raw($temprole . " d "), 'penerimaantrucking.aco_id', 'd.aco_id');
        }
        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table('penerimaantrucking')->from(DB::raw("penerimaantrucking with (readuncommitted)"))
            ->select(
                'penerimaantrucking.id',
                'penerimaantrucking.kodepenerimaan',
                'penerimaantrucking.keterangan',
                'penerimaantrucking.coadebet',
                'debet.keterangancoa as coadebetKeterangan',
                'penerimaantrucking.coakredit',
                'kredit.keterangancoa as coakreditKeterangan',
                'penerimaantrucking.coapostingdebet',
                'postingdebet.keterangancoa as coapostingdebetKeterangan',
                'penerimaantrucking.coapostingkredit',
                'postingkredit.keterangancoa as coapostingkreditKeterangan',
                'penerimaantrucking.format',
                'penerimaantrucking.statusaktif'
            )
            ->leftJoin(DB::raw("akunpusat as debet  with (readuncommitted)"), "penerimaantrucking.coadebet", "debet.coa")
            ->leftJoin(DB::raw("akunpusat as kredit  with (readuncommitted)"), "penerimaantrucking.coakredit", "kredit.coa")
            ->leftJoin(DB::raw("akunpusat as postingdebet  with (readuncommitted)"), "penerimaantrucking.coapostingdebet", "postingdebet.coa")
            ->leftJoin(DB::raw("akunpusat as postingkredit  with (readuncommitted)"), "penerimaantrucking.coapostingkredit", "postingkredit.coa")
            ->where('penerimaantrucking.id', $id);

        return $query->first();
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.kodepenerimaan,
                 $this->table.keterangan,
                 debet.keterangancoa as coadebet_keterangan,
                 kredit.keterangancoa as coakredit_keterangan,
                 postingdebet.keterangancoa as coapostingdebet_keterangan,
                 postingkredit.keterangancoa as coapostingkredit_keterangan,
                 parameter.text as format,
                 statusaktif.memo as statusaktif,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )->join('parameter', 'penerimaantrucking.format', 'parameter.id')
            ->leftJoin(DB::raw("akunpusat as debet  with (readuncommitted)"), "penerimaantrucking.coadebet", "debet.coa")
            ->leftJoin(DB::raw("akunpusat as kredit  with (readuncommitted)"), "penerimaantrucking.coakredit", "kredit.coa")
            ->leftJoin(DB::raw("akunpusat as postingdebet  with (readuncommitted)"), "penerimaantrucking.coapostingdebet", "postingdebet.coa")
            ->leftJoin(DB::raw("akunpusat as postingkredit  with (readuncommitted)"), "penerimaantrucking.coapostingkredit", "postingkredit.coa")
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'penerimaantrucking.statusaktif', '=', 'statusaktif.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodepenerimaan', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('coadebet_keterangan', 1000)->nullable();
            $table->string('coakredit_keterangan', 1000)->nullable();
            $table->string('coapostingdebet_keterangan', 1000)->nullable();
            $table->string('coapostingkredit_keterangan', 1000)->nullable();
            $table->string('format', 1000)->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'kodepenerimaan', 'keterangan', 'coadebet_keterangan', 'coakredit_keterangan', 'coapostingdebet_keterangan', 'coapostingkredit_keterangan', 'format', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'coadebet_keterangan') {
            return $query->orderBy('debet.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coakredit_keterangan') {
            return $query->orderBy('kredit.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coapostingdebet_keterangan') {
            return $query->orderBy('postingdebet.keterangancoa ', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coapostingkredit_keterangan') {
            return $query->orderBy('postingkredit.keterangancoa ', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'format') {
                                $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusaktif') {
                                $query = $query->where('statusaktif.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'coadebet_keterangan') {
                                $query = $query->where('debet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit_keterangan') {
                                $query = $query->where('kredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coapostingdebet_keterangan') {
                                $query = $query->where('postingdebet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coapostingkredit_keterangan') {
                                $query = $query->where('postingkredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'format') {
                                    $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'statusaktif') {
                                    $query = $query->orWhere('statusaktif.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'coadebet_keterangan') {
                                    $query = $query->orWhere('debet.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coakredit_keterangan') {
                                    $query = $query->orWhere('kredit.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coapostingdebet_keterangan') {
                                    $query = $query->orWhere('postingdebet.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coapostingkredit_keterangan') {
                                    $query = $query->orWhere('postingkredit.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data): PenerimaanTrucking
    {
        $cabang_id = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->select('text')
        ->where('grp', 'ID CABANG')
        ->where('subgrp', 'ID CABANG')
        ->first()->text ?? '';

        $penerimaanTrucking = new PenerimaanTrucking();
        $penerimaanTrucking->kodepenerimaan = $data['kodepenerimaan'];
        $penerimaanTrucking->keterangan = $data['keterangan'] ?? '';
        $penerimaanTrucking->coadebet = $data['coadebet'] ?? '';
        $penerimaanTrucking->coakredit = $data['coakredit'] ?? '';
        $penerimaanTrucking->coapostingdebet = $data['coapostingdebet'] ?? '';
        $penerimaanTrucking->coapostingkredit = $data['coapostingkredit'] ?? '';
        $penerimaanTrucking->format = $data['format'];
        $penerimaanTrucking->statusaktif = $data['statusaktif'];
        $penerimaanTrucking->cabang_id = $cabang_id;
        $penerimaanTrucking->tas_id = $data['tas_id'] ?? '';
        $penerimaanTrucking->modifiedby = auth('api')->user()->name;
        $penerimaanTrucking->info = html_entity_decode(request()->info);

        if (!$penerimaanTrucking->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanTrucking->getTable()),
            'postingdari' => 'ENTRY PENERIMAAN TRUCKING',
            'idtrans' => $penerimaanTrucking->id,
            'nobuktitrans' => $penerimaanTrucking->id,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanTrucking->toArray(),
            'modifiedby' => $penerimaanTrucking->modifiedby
        ]);
        // $request->sortname = $request->sortname ?? 'id';
        // $request->sortorder = $request->sortorder ?? 'asc';

        return $penerimaanTrucking;
    }
    public function processUpdate(PenerimaanTrucking $penerimaanTrucking, array $data): PenerimaanTrucking
    {
        $penerimaanTrucking->kodepenerimaan = $data['kodepenerimaan'];
        $penerimaanTrucking->keterangan = $data['keterangan'] ?? '';
        $penerimaanTrucking->coadebet = $data['coadebet'] ?? '';
        $penerimaanTrucking->coakredit = $data['coakredit'] ?? '';
        $penerimaanTrucking->coapostingdebet = $data['coapostingdebet'] ?? '';
        $penerimaanTrucking->coapostingkredit = $data['coapostingkredit'] ?? '';
        $penerimaanTrucking->format = $data['format'];
        $penerimaanTrucking->statusaktif = $data['statusaktif'];
        $penerimaanTrucking->modifiedby = auth('api')->user()->name;

        if (!$penerimaanTrucking->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanTrucking->getTable()),
            'postingdari' => 'EDIT PENERIMAAN TRUCKING',
            'idtrans' => $penerimaanTrucking->id,
            'nobuktitrans' => $penerimaanTrucking->id,
            'aksi' => 'EDIT',
            'datajson' => $penerimaanTrucking->toArray(),
            'modifiedby' => $penerimaanTrucking->modifiedby
        ]);

        return $penerimaanTrucking;
    }
    public function processDestroy($id): PenerimaanTrucking
    {
        $penerimaanTrucking = new PenerimaanTrucking();
        $penerimaanTrucking = $penerimaanTrucking->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanTrucking->getTable()),
            'postingdari' => 'DELETE PENERIMAAN TRUCKING',
            'idtrans' => $penerimaanTrucking->id,
            'nobuktitrans' => $penerimaanTrucking->id,
            'aksi' => 'DELETE',
            'datajson' => $penerimaanTrucking->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $penerimaanTrucking;
    }
    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $penerimaanTrucking = PenerimaanTrucking::find($data['Id'][$i]);

            $penerimaanTrucking->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($penerimaanTrucking->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($penerimaanTrucking->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF PENERIMAAN TRUCKING',
                    'idtrans' => $penerimaanTrucking->id,
                    'nobuktitrans' => $penerimaanTrucking->id,
                    'aksi' => $aksi,
                    'datajson' => $penerimaanTrucking->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $penerimaanTrucking;
    }
}
