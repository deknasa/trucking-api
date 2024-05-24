<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengeluaranTrucking extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluarantrucking';

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

        $pengeluaranTrucking = DB::table('pengeluarantruckingheader')
            ->from(
                DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluarantrucking_id'
            )
            ->where('a.pengeluarantrucking_id', '=', $id)
            ->first();
        if (isset($pengeluaranTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Trucking',
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
        //     ->join(db::raw("pengeluarantrucking b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
        //     ->where('a.user_id', $user_id);

        // DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


        // $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
        //     ->select('a.aco_id')
        //     ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
        //     ->join(db::raw("pengeluarantrucking c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
        //     ->leftjoin(db::raw($temprole ." d "), 'a.aco_id', 'd.aco_id')
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
                'pengeluarantrucking.id',
                'pengeluarantrucking.kodepengeluaran',
                'pengeluarantrucking.keterangan',
                'pengeluarantrucking.coadebet',
                'pengeluarantrucking.coakredit',
                'pengeluarantrucking.coapostingdebet',
                'pengeluarantrucking.coapostingkredit',
                'debet.keterangancoa as coadebet_keterangan',
                'kredit.keterangancoa as coakredit_keterangan',
                'postingdebet.keterangancoa as coapostingdebet_keterangan',
                'postingkredit.keterangancoa as coapostingkredit_keterangan',
                'parameter.memo as format',
                'pengeluarantrucking.created_at',
                'pengeluarantrucking.modifiedby',
                'pengeluarantrucking.updated_at',
                'statusaktif.memo as statusaktif',
                DB::raw("'Laporan Pengeluaran Trucking' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )

            ->leftJoin(DB::raw("akunpusat as debet  with (readuncommitted)"), "pengeluarantrucking.coadebet", "debet.coa")
            ->leftJoin(DB::raw("akunpusat as kredit  with (readuncommitted)"), "pengeluarantrucking.coakredit", "kredit.coa")
            ->leftJoin(DB::raw("akunpusat as postingdebet  with (readuncommitted)"), "pengeluarantrucking.coapostingdebet", "postingdebet.coa")
            ->leftJoin(DB::raw("akunpusat as postingkredit  with (readuncommitted)"), "pengeluarantrucking.coapostingkredit", "postingkredit.coa")
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pengeluarantrucking.format', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'pengeluarantrucking.statusaktif', '=', 'statusaktif.id')
            ->where('pengeluarantrucking.cabang_id', $cabang_id);


        $this->filter($query);

        if ($roleinput != '') {
            $getParam = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'ID CABANG')->first();
            $query->where('pengeluarantrucking.cabang_id', $getParam->text)
                ->where('pengeluarantrucking.statusaktif', 1);
        }
        if ($isLookup != '') {
            $temprole = '##temprole' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temprole, function ($table) {
                $table->bigInteger('aco_id')->nullable();
            });

            $queryaco = db::table("useracl")->from(db::raw("useracl a with (readuncommitted)"))
                ->select('a.aco_id')
                ->join(db::raw("pengeluarantrucking b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
                ->where('a.user_id', $user_id);
            DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


            $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
                ->select('a.aco_id')
                ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
                ->join(db::raw("pengeluarantrucking c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
                ->leftjoin(db::raw($temprole . " d "), 'a.aco_id', 'd.aco_id')
                ->where('b.user_id', $user_id)
                ->whereRaw("isnull(d.aco_id,0)=0")
                ->where('c.cabang_id', $cabang_id);

            DB::table($temprole)->insertUsing(['aco_id'], $queryrole);
            $query
                ->join($temprole, 'pengeluarantrucking.aco_id', $temprole . '.aco_id');
        }
        // if ($roleinput != '') {
        //     $query->join(db::raw($temprole ." d "), 'pengeluarantrucking.aco_id', 'd.aco_id');

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
            ->join(db::raw("pengeluarantrucking b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
            ->where('a.user_id', $user_id);

        DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


        $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
            ->select('a.aco_id')
            ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
            ->join(db::raw("pengeluarantrucking c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
            ->leftjoin(db::raw($temprole . " d "), 'a.aco_id', 'd.aco_id')
            ->where('b.user_id', $user_id)
            ->whereRaw("isnull(d.aco_id,0)=0");

        DB::table($temprole)->insertUsing(['aco_id'], $queryrole);
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'pengeluarantrucking.id',
                'pengeluarantrucking.kodepengeluaran',
                'pengeluarantrucking.keterangan',
            );
        if ($roleinput != '') {
            $query->join(db::raw($temprole . " d "), 'pengeluarantrucking.aco_id', 'd.aco_id');
        }

        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
            ->select(
                'pengeluarantrucking.id',
                'pengeluarantrucking.kodepengeluaran',
                'pengeluarantrucking.keterangan',
                'pengeluarantrucking.coadebet',
                'debet.keterangancoa as coadebetKeterangan',
                'pengeluarantrucking.coakredit',
                'kredit.keterangancoa as coakreditKeterangan',
                'pengeluarantrucking.coapostingdebet',
                'postingdebet.keterangancoa as coapostingdebetKeterangan',
                'pengeluarantrucking.coapostingkredit',
                'postingkredit.keterangancoa as coapostingkreditKeterangan',
                'pengeluarantrucking.format',
                'pengeluarantrucking.statusaktif'
            )
            ->leftJoin(DB::raw("akunpusat as debet  with (readuncommitted)"), "pengeluarantrucking.coadebet", "debet.coa")
            ->leftJoin(DB::raw("akunpusat as kredit  with (readuncommitted)"), "pengeluarantrucking.coakredit", "kredit.coa")
            ->leftJoin(DB::raw("akunpusat as postingdebet  with (readuncommitted)"), "pengeluarantrucking.coapostingdebet", "postingdebet.coa")
            ->leftJoin(DB::raw("akunpusat as postingkredit  with (readuncommitted)"), "pengeluarantrucking.coapostingkredit", "postingkredit.coa")
            ->where('pengeluarantrucking.id', $id);

        return $query->first();
    }
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.kodepengeluaran,
                 $this->table.keterangan,
                 debet.keterangancoa as coadebet_keterangan,
                 kredit.keterangancoa as coakredit_keterangan,
                 postingdebet.keterangancoa as coapostingdebet_keterangan,
                 postingkredit.keterangancoa as coapostingkredit_keterangan,
                 parameter.text as format,
                 statusaktif.text as statusaktif,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )->join('parameter', 'pengeluarantrucking.format', 'parameter.id')

            ->leftJoin(DB::raw("akunpusat as debet  with (readuncommitted)"), "pengeluarantrucking.coadebet", "debet.coa")
            ->leftJoin(DB::raw("akunpusat as kredit  with (readuncommitted)"), "pengeluarantrucking.coakredit", "kredit.coa")
            ->leftJoin(DB::raw("akunpusat as postingdebet  with (readuncommitted)"), "pengeluarantrucking.coapostingdebet", "postingdebet.coa")
            ->leftJoin(DB::raw("akunpusat as postingkredit  with (readuncommitted)"), "pengeluarantrucking.coapostingkredit", "postingkredit.coa")
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'pengeluarantrucking.statusaktif', '=', 'statusaktif.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodepengeluaran', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodepengeluaran', 'keterangan', 'coadebet_keterangan', 'coakredit_keterangan', 'coapostingdebet_keterangan', 'coapostingkredit_keterangan', 'format', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'coadebet_keterangan') {
            return $query->orderBy('debet.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coakredit_keterangan') {
            return $query->orderBy('kredit.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coapostingdebet_keterangan') {
            return $query->orderBy('postingdebet.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coapostingkredit_keterangan') {
            return $query->orderBy('postingkredit.keterangancoa', $this->params['sortOrder']);
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
                            } else if ($filters['field'] == 'coadebet_keterangan') {
                                $query = $query->where('debet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit_keterangan') {
                                $query = $query->where('kredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coapostingdebet_keterangan') {
                                $query = $query->where('postingdebet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coapostingkredit_keterangan') {
                                $query = $query->where('postingkredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusaktif') {
                                $query = $query->where('statusaktif.text', '=', "$filters[data]");
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
                                    $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coadebet_keterangan') {
                                    $query->orWhere('debet.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coakredit_keterangan') {
                                    $query->orWhere('kredit.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coapostingdebet_keterangan') {
                                    $query->orWhere('postingdebet.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coapostingkredit_keterangan') {
                                    $query->orWhere('postingkredit.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'statusaktif') {
                                    $query = $query->orWhere('statusaktif.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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


    public function processStore(array $data): PengeluaranTrucking
    {
        $cabang_id = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'ID CABANG')
            ->where('subgrp', 'ID CABANG')
            ->first()->text ?? '';
            
        $pengeluaranTrucking = new PengeluaranTrucking();
        $pengeluaranTrucking->kodepengeluaran = $data['kodepengeluaran'];
        $pengeluaranTrucking->keterangan = $data['keterangan'] ?? '';
        $pengeluaranTrucking->coadebet = $data['coadebet'] ?? '';;
        $pengeluaranTrucking->coakredit = $data['coakredit'] ?? '';;
        $pengeluaranTrucking->coapostingdebet = $data['coapostingdebet'];
        $pengeluaranTrucking->coapostingkredit = $data['coapostingkredit'];
        $pengeluaranTrucking->format = $data['format'];
        $pengeluaranTrucking->cabang_id = $cabang_id;
        $pengeluaranTrucking->tas_id = $data['tas_id'] ?? '';
        $pengeluaranTrucking->statusaktif = $data['statusaktif'];
        $pengeluaranTrucking->modifiedby = auth('api')->user()->name;
        $pengeluaranTrucking->info = html_entity_decode(request()->info);

        // TOP:

        if (!$pengeluaranTrucking->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranTrucking->getTable()),
            'postingdari' => 'ENTRY PENGELUARAN TRUCKING',
            'idtrans' => $pengeluaranTrucking->id,
            'nobuktitrans' => $pengeluaranTrucking->id,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranTrucking->toArray(),
            'modifiedby' => $pengeluaranTrucking->modifiedby
        ]);

        return $pengeluaranTrucking;
    }

    public function processUpdate(PengeluaranTrucking $pengeluaranTrucking, array $data): PengeluaranTrucking
    {
        $pengeluaranTrucking->kodepengeluaran = $data['kodepengeluaran'];
        $pengeluaranTrucking->keterangan = $data['keterangan'];
        $pengeluaranTrucking->coadebet = $data['coadebet'] ?? '';
        $pengeluaranTrucking->coakredit = $data['coakredit'] ?? '';
        $pengeluaranTrucking->coapostingdebet = $data['coapostingdebet'] ?? '';
        $pengeluaranTrucking->coapostingkredit = $data['coapostingkredit'] ?? '';
        $pengeluaranTrucking->format = $data['format'];
        $pengeluaranTrucking->statusaktif = $data['statusaktif'];
        $pengeluaranTrucking->modifiedby = auth('api')->user()->name;
        $pengeluaranTrucking->info = html_entity_decode(request()->info);

        if (!$pengeluaranTrucking->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranTrucking->getTable()),
            'postingdari' => 'EDIT PENGELUARAN TRUCKING',
            'idtrans' => $pengeluaranTrucking->id,
            'nobuktitrans' => $pengeluaranTrucking->id,
            'aksi' => 'EDIT',
            'datajson' => $pengeluaranTrucking->toArray(),
            'modifiedby' => $pengeluaranTrucking->modifiedby
        ]);

        return $pengeluaranTrucking;
    }

    public function processDestroy($id): PengeluaranTrucking
    {
        $pengeluaranTrucking = new PengeluaranTrucking();
        $pengeluaranTrucking = $pengeluaranTrucking->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranTrucking->getTable()),
            'postingdari' => 'DELETE PENGELUARAN TRUCKING',
            'idtrans' => $pengeluaranTrucking->id,
            'nobuktitrans' => $pengeluaranTrucking->id,
            'aksi' => 'DELETE',
            'datajson' => $pengeluaranTrucking->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $pengeluaranTrucking;
    }
    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $pengeluaranTrucking = PengeluaranTrucking::find($data['Id'][$i]);

            $pengeluaranTrucking->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($pengeluaranTrucking->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($pengeluaranTrucking->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF PENGELUARAN TRUCKING',
                    'idtrans' => $pengeluaranTrucking->id,
                    'nobuktitrans' => $pengeluaranTrucking->id,
                    'aksi' => $aksi,
                    'datajson' => $pengeluaranTrucking->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $pengeluaranTrucking;
    }
}
