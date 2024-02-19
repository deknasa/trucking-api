<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MainAkunPusat extends MyModel
{
    use HasFactory;

    protected $table = 'mainakunpusat';

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

        // dd(request()->offset);
        $level = request()->level ?? '';
        $potongan = request()->potongan ?? '';

        $aktif = request()->aktif ?? '';
        $isParent = request()->isParent ?? '';



        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'mainakunpusat.id',
                'mainakunpusat.coa',
                'mainakunpusat.keterangancoa',
                'typeakuntansi.kodetype as type',
                'mainakunpusat.level',
                'mainakunpusat.parent',
                'akuntansi.kodeakuntansi as akuntansi',
                'parameter_statusaktif.memo as statusaktif',
                'parameter_statusparent.memo as statusparent',
                'parameter_statusneraca.memo as statusneraca',
                'parameter_statuslabarugi.memo as statuslabarugi',
                'mainakunpusat.modifiedby',
                'mainakunpusat.created_at',
                'mainakunpusat.updated_at',
                DB::raw("'Laporan Kode Perkiraan' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("(trim(mainakunpusat.coa)+' - '+trim(mainakunpusat.keterangancoa)) as kodeket"),
            )

            ->leftJoin(DB::raw("typeakuntansi with (readuncommitted)"), 'mainakunpusat.type_id', 'typeakuntansi.id')
            ->leftJoin(DB::raw("akuntansi with (readuncommitted)"), 'mainakunpusat.akuntansi_id', 'akuntansi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'mainakunpusat.statusaktif', '=', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statusparent with (readuncommitted)"), 'mainakunpusat.statusparent', '=', 'parameter_statusparent.id')
            ->leftJoin(DB::raw("parameter as parameter_statusneraca with (readuncommitted)"), 'mainakunpusat.statusneraca', '=', 'parameter_statusneraca.id')
            ->leftJoin(DB::raw("parameter as parameter_statuslabarugi with (readuncommitted)"), 'mainakunpusat.statuslabarugi', '=', 'parameter_statuslabarugi.id');



        $this->filter($query);

        if ($level != '') {
            if ($level == '3') {
                $query->whereRaw(DB::raw("right(mainakunpusat.coa,3)<>'.00'"));
            } else {
                $query->where('mainakunpusat.level', '=', $level);
            }
        }
        if ($potongan != '') {
            $temp = implode(',', $this->TempParameter());

            $query->whereRaw("mainakunpusat.coa in ($temp)");
        }
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('mainakunpusat.statusaktif', '=', $statusaktif->id);
        }
        if ($isParent != '') {

            $query->whereRaw("RIGHT(mainakunpusat.coa, 3) = '.00'");
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();

        return $data;
    }

    public function cekValidasi($id)
    {
        $getCoa = DB::table("mainakunpusat")->from(DB::raw("mainakunpusat with (readuncommitted)"))->where('id', $id)->first();
        $coa = $getCoa->coa;

        // $parent = DB::table('mainakunpusat')
        //     ->from(
        //         DB::raw("mainakunpusat as a with (readuncommitted)")
        //     )
        //     ->select(
        //         'a.parent'
        //     )
        //     ->where('a.parent', '=', $coa)
        //     ->first();

        // if (isset($parent)) {
        //     $data = [
        //         'kondisi' => true,
        //         'keterangan' => 'main akun pusat',
        //         'kodeerror' => 'SATL'
        //     ];
        //     goto selesai;
        // }

        $akunPusat = DB::table('akunpusat')
            ->from(
                DB::raw("akunpusat as a with (readuncommitted)")
            )
            ->select(
                'a.coamain'
            )
            ->where('a.coamain', '=', $coa)
            ->first();

        if (isset($akunPusat)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'akun pusat',
                'kodeerror' => 'SATL'
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

    public function TempParameter()
    {
        $parameter = Parameter::from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('kelompok', 'JURNAL POTONGAN')->get();
        $coa = [];
        foreach ($parameter as $key => $value) {
            $memo = json_decode($value->memo, true);
            $coa[] = "'" . $memo['JURNAL'] . "'";
        }
        return $coa;
    }
    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusparent')->nullable();
            $table->string('statusparentnama')->nullable();
            $table->unsignedBigInteger('statuslabarugi')->nullable();
            $table->string('statuslabaruginama')->nullable();
            $table->unsignedBigInteger('statusneraca')->nullable();
            $table->string('statusneracanama')->nullable();
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama')->nullable();
        });
        // statusparent
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS PARENT')
            ->where('subgrp', '=', 'STATUS PARENT')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusparent = $status->id ?? 0;
        $textdefaultstatusparent = $status->text ?? "";

        // statuslabarugi
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS LABA RUGI')
            ->where('subgrp', '=', 'STATUS LABA RUGI')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuslabarugi = $status->id ?? 0;
        $textdefaultstatuslabarugi = $status->text ?? "";

        // statusneraca
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS NERACA')
            ->where('subgrp', '=', 'STATUS NERACA')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusneraca = $status->id ?? 0;
        $textdefaultstatusneraca = $status->text ?? "";

        // statusaktif
        $status = Parameter::from(
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

        $iddefaultstatusaktif = $status->id ?? 0;
        $textdefaultstatusaktif = $status->text ?? "";

        DB::table($tempdefault)->insert(
            [
                "statusparent" => $iddefaultstatusparent,
                "statusparentnama"=>$textdefaultstatusparent,
                "statuslabarugi" => $iddefaultstatuslabarugi,
                "statuslabaruginama"=>$textdefaultstatuslabarugi,
                "statusneraca" => $iddefaultstatusneraca,
                "statusneracanama"=>$textdefaultstatusneraca,
                "statusaktif" => $iddefaultstatusaktif,
                "statusaktifnama"=>$textdefaultstatusaktif,
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                "statusparent",
                "statusparentnama",
                "statuslabarugi",
                "statuslabaruginama",
                "statusneraca",
                "statusneracanama",
                "statusaktif",
                "statusaktifnama",
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
                $this->table.coa,
                $this->table.keterangancoa,
                'typeakuntansi.kodetype as type',
                $this->table.level,
                'parameter_statusaktif.text as statusaktif',
                $this->table.parent,
                'akuntansi.kodeakuntansi as akuntansi',
                'parameter_statusparent.text as statusparent',
                'parameter_statusneraca.text as statusneraca',
                'parameter_statuslabarugi.text as statuslabarugi',
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
            ")
            )
            ->leftJoin(DB::raw("typeakuntansi with (readuncommitted)"), 'mainakunpusat.type_id', 'typeakuntansi.id')
            ->leftJoin(DB::raw("akuntansi with (readuncommitted)"), 'mainakunpusat.akuntansi_id', 'akuntansi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'mainakunpusat.statusaktif', '=', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statusparent with (readuncommitted)"), 'mainakunpusat.statusparent', '=', 'parameter_statusparent.id')
            ->leftJoin(DB::raw("parameter as parameter_statusneraca with (readuncommitted)"), 'mainakunpusat.statusneraca', '=', 'parameter_statusneraca.id')
            ->leftJoin(DB::raw("parameter as parameter_statuslabarugi with (readuncommitted)"), 'mainakunpusat.statuslabarugi', '=', 'parameter_statuslabarugi.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('keterangancoa', 1000)->nullable();
            $table->string('type', 1000)->nullable();
            $table->bigInteger('level')->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('parent', 1000)->nullable();
            $table->string('akuntansi', 1000)->nullable();
            $table->string('statusparent', 1000)->nullable();
            $table->string('statusneraca', 1000)->nullable();
            $table->string('statuslabarugi', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'coa', 'keterangancoa', 'type', 'level', 'statusaktif', 'parent', 'akuntansi', 'statusparent', 'statusneraca', 'statuslabarugi', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function findAll($id)
    {
        $query = DB::table("mainakunpusat")->from(DB::raw("mainakunpusat with (readuncommitted)"))
            ->select(
                'mainakunpusat.id',
                'mainakunpusat.coa',
                'mainakunpusat.keterangancoa',
                'mainakunpusat.type',
                'mainakunpusat.type_id',                
                DB::raw('(case when (mainakunpusat.akuntansi_id = 0) then null else mainakunpusat.akuntansi_id end ) as akuntansi_id'),     
                'akuntansi.kodeakuntansi as akuntansi',
                'mainakunpusat.parent',
                DB::raw("(trim(parent.coa)+' - '+trim(parent.keterangancoa)) as parentnama"),
                'mainakunpusat.statusparent',
                'statusparent.text as statusparentnama',
                'mainakunpusat.statusneraca',
                'statusneraca.text as statusneracanama',
                'mainakunpusat.statuslabarugi',
                'statuslabarugi.text as statuslabaruginama',
                'mainakunpusat.statusaktif',
                'statusaktif.text as statusaktifnama',
            )
            ->leftJoin(DB::raw("parameter as statusparent with (readuncommitted)"), 'mainakunpusat.statusparent', '=', 'statusparent.id')
            ->leftJoin(DB::raw("parameter as statusneraca with (readuncommitted)"), 'mainakunpusat.statusneraca', '=', 'statusneraca.id')
            ->leftJoin(DB::raw("parameter as statuslabarugi with (readuncommitted)"), 'mainakunpusat.statuslabarugi', '=', 'statuslabarugi.id')
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'mainakunpusat.statusaktif', '=', 'statusaktif.id')
            ->leftJoin(DB::raw("typeakuntansi with (readuncommitted)"), 'mainakunpusat.type_id', 'typeakuntansi.id')
            ->leftJoin(DB::raw("akuntansi with (readuncommitted)"), 'mainakunpusat.akuntansi_id', 'akuntansi.id')
            ->leftJoin(DB::raw("mainakunpusat as parent with (readuncommitted)"), 'mainakunpusat.parent', 'parent.coa')
            ->where('mainakunpusat.id', $id)
            ->first();

        return $query;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'akuntansi') {
            return $query->orderBy('akuntansi.kodeakuntansi', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'type') {
            return $query->orderBy('typeakuntansi.kodetype', $this->params['sortOrder']);
        }
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusparent') {
                            $query = $query->where('parameter_statusparent.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusneraca') {
                            $query = $query->where('parameter_statusneraca.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuslabarugi') {
                            $query = $query->where('parameter_statuslabarugi.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } elseif ($filters['field'] == 'type') {
                            $query = $query->where('typeakuntansi.kodetype', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'akuntansi') {
                            $query = $query->where('akuntansi.kodeakuntansi', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kodeket') {
                            $query = $query->whereRaw("(trim(mainakunpusat.coa)+' - '+trim(mainakunpusat.keterangancoa)) LIKE '%$filters[data]%'");
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
                                $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'statusparent') {
                                $query = $query->orWhere('parameter_statusparent.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusneraca') {
                                $query = $query->orWhere('parameter_statusneraca.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuslabarugi') {
                                $query = $query->orWhere('parameter_statuslabarugi.text', '=', "$filters[data]");
                            } elseif ($filters['field'] == 'type') {
                                $query = $query->orWhere('typeakuntansi.kodetype', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'akuntansi') {
                                $query = $query->orWhere('akuntansi.kodeakuntansi', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'kodeket') {
                                $query = $query->OrwhereRaw("(trim(mainakunpusat.coa)+' - '+trim(mainakunpusat.keterangancoa)) LIKE '%$filters[data]%'");
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

    public function processStore(array $data): MainAkunPusat
    {
        if ($data['parent'] == null) {
            $parent = $data['coa'];
            $level = 1;
        } else {
            $parent = $data['parent'];
            $getLevel = DB::table("mainakunpusat")->from(DB::raw("mainakunpusat with (readuncommitted)"))->where('coa', $parent)->first();
            $level = $getLevel->level + 1;
        }
        $mainAkunPusat = new MainAkunPusat();
        $mainAkunPusat->coa = $data['coa'];
        $mainAkunPusat->keterangancoa = $data['keterangancoa'];
        $mainAkunPusat->type_id = $data['type_id'];
        $mainAkunPusat->type = $data['type'];
        $mainAkunPusat->level = $level;
        $mainAkunPusat->parent = $parent;
        $mainAkunPusat->akuntansi_id = $data['akuntansi_id'];
        $mainAkunPusat->statusparent = $data['statusparent'];
        $mainAkunPusat->statusneraca = $data['statusneraca'];
        $mainAkunPusat->statuslabarugi = $data['statuslabarugi'];
        $mainAkunPusat->statusaktif = $data['statusaktif'];
        $mainAkunPusat->modifiedby = auth('api')->user()->name;
        $mainAkunPusat->info = html_entity_decode(request()->info);

        if (!$mainAkunPusat->save()) {
            throw new \Exception("Error storing main akun pusat.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($mainAkunPusat->getTable()),
            'postingdari' => 'ENTRY MAIN AKUN PUSAT',
            'idtrans' => $mainAkunPusat->id,
            'nobuktitrans' => $mainAkunPusat->id,
            'aksi' => 'ENTRY',
            'datajson' => $mainAkunPusat->toArray(),
            'modifiedby' => $mainAkunPusat->modifiedby
        ]);

        return $mainAkunPusat;
    }

    public function processUpdate(MainAkunPusat $mainAkunPusat, array $data): MainAkunPusat
    {
        
        $parent = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PARENT')->where('text', 'PARENT')->first();
        if ($data['statusparent'] == $parent->id) {
            $parent = $data['coa'];
            $level = 1;
        } else {
            $parent = $data['parent'];
            $getLevel = DB::table("mainakunpusat")->from(DB::raw("mainakunpusat with (readuncommitted)"))->where('coa', $parent)->first();
            $level = $getLevel->level + 1;
        }
        $mainAkunPusat->coa = $data['coa'];
        $mainAkunPusat->keterangancoa = $data['keterangancoa'];
        $mainAkunPusat->type_id = $data['type_id'];
        $mainAkunPusat->type = $data['type'];
        $mainAkunPusat->level = $level;
        $mainAkunPusat->parent = $parent;
        $mainAkunPusat->akuntansi_id = $data['akuntansi_id'];
        $mainAkunPusat->statusparent = $data['statusparent'];
        $mainAkunPusat->statusneraca = $data['statusneraca'];
        $mainAkunPusat->statuslabarugi = $data['statuslabarugi'];
        $mainAkunPusat->statusaktif = $data['statusaktif'];
        $mainAkunPusat->modifiedby = auth('api')->user()->name;
        $mainAkunPusat->info = html_entity_decode(request()->info);

        if (!$mainAkunPusat->save()) {
            throw new \Exception("Error update main akun pusat.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($mainAkunPusat->getTable()),
            'postingdari' => 'EDIT MAIN AKUN PUSAT',
            'idtrans' => $mainAkunPusat->id,
            'nobuktitrans' => $mainAkunPusat->id,
            'aksi' => 'EDIT',
            'datajson' => $mainAkunPusat->toArray(),
            'modifiedby' => $mainAkunPusat->modifiedby
        ]);

        return $mainAkunPusat;
    }

    public function processDestroy($id): MainAkunPusat
    {
        $mainAkunPusat = new MainAkunPusat();
        $mainAkunPusat = $mainAkunPusat->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($mainAkunPusat->getTable()),
            'postingdari' => 'DELETE MAIN AKUN PUSAT',
            'idtrans' => $mainAkunPusat->id,
            'nobuktitrans' => $mainAkunPusat->id,
            'aksi' => 'DELETE',
            'datajson' => $mainAkunPusat->toArray(),
            'modifiedby' => $mainAkunPusat->modifiedby
        ]);

        return $mainAkunPusat;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $mainakunpusat = MainAkunPusat::find($data['Id'][$i]);

            $mainakunpusat->statusaktif = $statusnonaktif->id;
            $mainakunpusat->modifiedby = auth('api')->user()->name;
            $mainakunpusat->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($mainakunpusat->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($mainakunpusat->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF KODE PERKIRAAN PUSAT',
                    'idtrans' => $mainakunpusat->id,
                    'nobuktitrans' => $mainakunpusat->id,
                    'aksi' => $aksi,
                    'datajson' => $mainakunpusat->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $mainakunpusat;
    }
}
