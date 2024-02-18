<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AkunPusat extends MyModel
{
    use HasFactory;

    protected $table = 'akunpusat';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasihapus($id)
    {
        $jurnalUmumDetail = DB::table('jurnalumumdetail')
            ->from(
                DB::raw("jurnalumumdetail as a with (readuncommitted)")
            )
            ->select(
                'a.coa'
            )
            ->where('a.coa', '=', $id)
            ->first();
        if (isset($jurnalUmumDetail)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'COA',
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

    public function cekValidasi($id)
    {
        $getCoa = DB::table("akunpusat")->from(DB::raw("akunpusat with (readuncommitted)"))->where('id', $id)->first();
        $coa = $getCoa->coa;

        $parent = DB::table('jurnalumumdetail')
            ->from(
                DB::raw("jurnalumumdetail as a with (readuncommitted)")
            )
            ->select(
                'a.coa'
            )
            ->where('a.coa', '=', $coa)
            ->first();

        if (isset($parent)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'jurnal umum',
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
        $supplier = request()->supplier ?? '';
        $isParent = request()->isParent ?? '';

        $aktif = request()->aktif ?? '';
        $KeteranganCoa = request()->keterangancoa ?? '';



        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'akunpusat.id',
                'akunpusat.coa',
                'akunpusat.keterangancoa',
                'typeakuntansi.kodetype as type',
                'akunpusat.level',
                'akunpusat.parent',
                'akunpusat.coamain',
                'akuntansi.kodeakuntansi as akuntansi',
                'parameter_statusaktif.memo as statusaktif',
                'parameter_statusparent.memo as statusparent',
                'parameter_statusneraca.memo as statusneraca',
                'parameter_statuslabarugi.memo as statuslabarugi',
                'akunpusat.modifiedby',
                'akunpusat.created_at',
                'akunpusat.updated_at',
                DB::raw("'Laporan Kode Perkiraan' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                DB::raw("(trim(akunpusat.coa)+' - '+trim(akunpusat.keterangancoa)) as kodeket"),
            )
            ->leftJoin(DB::raw("typeakuntansi with (readuncommitted)"), 'akunpusat.type_id', 'typeakuntansi.id')
            ->leftJoin(DB::raw("akuntansi with (readuncommitted)"), 'akunpusat.akuntansi_id', 'akuntansi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'akunpusat.statusaktif', '=', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statusparent with (readuncommitted)"), 'akunpusat.statusparent', '=', 'parameter_statusparent.id')
            ->leftJoin(DB::raw("parameter as parameter_statusneraca with (readuncommitted)"), 'akunpusat.statusneraca', '=', 'parameter_statusneraca.id')
            ->leftJoin(DB::raw("parameter as parameter_statuslabarugi with (readuncommitted)"), 'akunpusat.statuslabarugi', '=', 'parameter_statuslabarugi.id');



        $this->filter($query);

        if ($level != '') {
            if ($level == '3') {
                $query->whereRaw(DB::raw("right(akunpusat.coa,3)<>'.00'"));
            } else {
                $query->where('akunpusat.level', '=', $level);
            }
        }
        if ($potongan != '') {
            $temp = implode(',', $this->TempParameter());

            $query->whereRaw("akunpusat.coa in ($temp)");
        }

        if ($KeteranganCoa != '') {

            $query->whereRaw("akunpusat.keterangancoa like'%" . $KeteranganCoa . "%'");
        }

        if ($supplier != '') {
            $temp = implode(',', $this->TempParameterSupplier());

            $query->whereRaw("akunpusat.coa in ($temp)");
        }
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('akunpusat.statusaktif', '=', $statusaktif->id);
        }

        
        if ($isParent != '') {

            $query->whereRaw("RIGHT(akunpusat.coa, 3) = '.00'");
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();

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

    public function TempParameterSupplier()
    {
        $parameter = Parameter::from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('kelompok', 'JURNAL SUPPLIER')->get();
        $coa = [];
        foreach ($parameter as $key => $value) {
            $memo = json_decode($value->memo, true);
            $coa[] = "'" . trim($memo['JURNAL']) . "'";
        }
        // dd($coa);
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
                $this->table.coamain,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
            ")
            )
            ->leftJoin(DB::raw("typeakuntansi with (readuncommitted)"), 'akunpusat.type_id', 'typeakuntansi.id')
            ->leftJoin(DB::raw("akuntansi with (readuncommitted)"), 'akunpusat.akuntansi_id', 'akuntansi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'akunpusat.statusaktif', '=', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statusparent with (readuncommitted)"), 'akunpusat.statusparent', '=', 'parameter_statusparent.id')
            ->leftJoin(DB::raw("parameter as parameter_statusneraca with (readuncommitted)"), 'akunpusat.statusneraca', '=', 'parameter_statusneraca.id')
            ->leftJoin(DB::raw("parameter as parameter_statuslabarugi with (readuncommitted)"), 'akunpusat.statuslabarugi', '=', 'parameter_statuslabarugi.id');
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
            $table->string('coamain', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'coa', 'keterangancoa', 'type', 'level', 'statusaktif', 'parent', 'akuntansi', 'statusparent', 'statusneraca', 'statuslabarugi', 'coamain', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function findAll($id)
    {
        $query = DB::table("akunpusat")->from(DB::raw("akunpusat with (readuncommitted)"))
            ->select(
                'akunpusat.id',
                'akunpusat.coa',
                'akunpusat.keterangancoa',
                'typeakuntansi.kodetype as type',
                'akunpusat.type_id',
                DB::raw('(case when (akunpusat.akuntansi_id = 0) then null else akunpusat.akuntansi_id end ) as akuntansi_id'),            
                'akuntansi.kodeakuntansi as akuntansi',
                'akunpusat.parent',
                DB::raw("(trim(parent.coa)+' - '+trim(parent.keterangancoa)) as parentnama"),
                'akunpusat.statusparent',
                'statusparent.text as statusparentnama',
                'akunpusat.statusneraca',
                'statusneraca.text as statusneracanama',
                'akunpusat.statuslabarugi',
                'statuslabarugi.text as statuslabaruginama',
                'akunpusat.statusaktif',
                'statusaktif.text as statusaktifnama',
                'akunpusat.level',
                'akunpusat.coamain',
                DB::raw("(trim(main.coa)+' - '+trim(main.keterangancoa)) as coamainket"),
            )
            ->leftJoin(DB::raw("parameter as statusparent with (readuncommitted)"), 'akunpusat.statusparent', '=', 'statusparent.id')
            ->leftJoin(DB::raw("parameter as statusneraca with (readuncommitted)"), 'akunpusat.statusneraca', '=', 'statusneraca.id')
            ->leftJoin(DB::raw("parameter as statuslabarugi with (readuncommitted)"), 'akunpusat.statuslabarugi', '=', 'statuslabarugi.id')
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'akunpusat.statusaktif', '=', 'statusaktif.id')
            ->leftJoin(DB::raw("typeakuntansi with (readuncommitted)"), 'akunpusat.type_id', 'typeakuntansi.id')
            ->leftJoin(DB::raw("akuntansi with (readuncommitted)"), 'akunpusat.akuntansi_id', 'akuntansi.id')
            ->leftJoin(DB::raw("akunpusat as parent with (readuncommitted)"), 'akunpusat.parent', 'parent.coa')
            ->leftJoin(DB::raw("mainakunpusat as main with (readuncommitted)"), 'akunpusat.coamain', 'main.coa')
            ->where('akunpusat.id', $id)
            ->first();

        return $query;
    }

    public function getTransferData($id)
    {
        $query = DB::table("akunpusat")->from(DB::raw("akunpusat with (readuncommitted)"))->where('id', $id)->first();
        return $query;
    }

    public function checkTransferData($coa)
    {
        $query = DB::table("akunpusat")->from(DB::raw("akunpusat with (readuncommitted)"))->where('coa', $coa)->first();
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
                        if ($filters['field'] != '') {
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
                                $query = $query->whereRaw("(trim(akunpusat.coa)+' - '+trim(akunpusat.keterangancoa)) LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
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
                                    $query = $query->OrwhereRaw("(trim(akunpusat.coa)+' - '+trim(akunpusat.keterangancoa)) LIKE '%$filters[data]%'");
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

    public function processStore(array $data): AkunPusat
    {
        $level = $data['level'] ?? 0;
        if ($data['parent'] == null) {
            $parent = $data['coa'];
            $level = 1;
        } else {
            $parent = $data['parent'];
            if ($level == 0) {
                $getLevel = DB::table("akunpusat")->from(DB::raw("akunpusat with (readuncommitted)"))->where('coa', $parent)->first();
                $level = $getLevel->level + 1;
            }
        }
        $akunPusat = new AkunPusat();
        $akunPusat->coa = $data['coa'];
        $akunPusat->keterangancoa = $data['keterangancoa'];
        $akunPusat->type_id = $data['type_id'];
        $akunPusat->type = $data['type'];
        $akunPusat->level = $level;
        $akunPusat->parent = $parent;
        $akunPusat->akuntansi_id = $data['akuntansi_id'];
        $akunPusat->statusparent = $data['statusparent'];
        $akunPusat->statusneraca = $data['statusneraca'];
        $akunPusat->statuslabarugi = $data['statuslabarugi'];
        $akunPusat->coamain = $data['coamain'];
        $akunPusat->statusaktif = $data['statusaktif'];
        $akunPusat->modifiedby = auth('api')->user()->name;
        $akunPusat->info = html_entity_decode(request()->info);

        if (!$akunPusat->save()) {
            throw new \Exception("Error storing akun pusat.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($akunPusat->getTable()),
            'postingdari' => 'ENTRY AKUN PUSAT',
            'idtrans' => $akunPusat->id,
            'nobuktitrans' => $akunPusat->id,
            'aksi' => 'ENTRY',
            'datajson' => $akunPusat->toArray(),
            'modifiedby' => $akunPusat->modifiedby
        ]);

        return $akunPusat;
    }

    public function processUpdate(AkunPusat $akunPusat, array $data): AkunPusat
    {
        $parent = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PARENT')->where('text', 'PARENT')->first();
        if ($data['statusparent'] == $parent->id) {
            $parent = $data['coa'];
            $level = 1;
        } else {
            $parent = $data['parent'];
            $getLevel = DB::table("akunpusat")->from(DB::raw("akunpusat with (readuncommitted)"))->where('coa', $parent)->first();
            if ($parent == $data['coa']) {
                $level = $getLevel->level;
            } else {
                $level = $getLevel->level + 1;
            }
        }
        $akunPusat->coa = $data['coa'];
        $akunPusat->keterangancoa = $data['keterangancoa'];
        $akunPusat->type_id = $data['type_id'];
        $akunPusat->type = $data['type'];
        $akunPusat->level = $level;
        $akunPusat->parent = $parent;
        $akunPusat->akuntansi_id = $data['akuntansi_id'];
        $akunPusat->statusparent = $data['statusparent'];
        $akunPusat->statusneraca = $data['statusneraca'];
        $akunPusat->statuslabarugi = $data['statuslabarugi'];
        $akunPusat->statusaktif = $data['statusaktif'];
        $akunPusat->coamain = $data['coamain'];
        $akunPusat->modifiedby = auth('api')->user()->name;
        $akunPusat->info = html_entity_decode(request()->info);

        if (!$akunPusat->save()) {
            throw new \Exception("Error update akun pusat.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($akunPusat->getTable()),
            'postingdari' => 'EDIT AKUN PUSAT',
            'idtrans' => $akunPusat->id,
            'nobuktitrans' => $akunPusat->id,
            'aksi' => 'EDIT',
            'datajson' => $akunPusat->toArray(),
            'modifiedby' => $akunPusat->modifiedby
        ]);

        return $akunPusat;
    }

    public function processDestroy($id): AkunPusat
    {
        $akunPusat = new AkunPusat();
        $akunPusat = $akunPusat->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($akunPusat->getTable()),
            'postingdari' => 'DELETE AKUN PUSAT',
            'idtrans' => $akunPusat->id,
            'nobuktitrans' => $akunPusat->id,
            'aksi' => 'DELETE',
            'datajson' => $akunPusat->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $akunPusat;
    }
    public function processDeleteCoa($coa): AkunPusat
    {
        $akunPusat = new AkunPusat();
        $getCoa = DB::table("akunpusat")->from(DB::raw("akunpusat with (readuncommitted)"))->where('coa', $coa)->first();
        if ($getCoa != null) {

            $akunPusat = $akunPusat->lockAndDestroy($coa, 'coa');

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($akunPusat->getTable()),
                'postingdari' => 'DELETE AKUN PUSAT',
                'idtrans' => $akunPusat->id,
                'nobuktitrans' => $akunPusat->id,
                'aksi' => 'DELETE',
                'datajson' => $akunPusat->toArray(),
                'modifiedby' => auth('api')->user()->name
            ]);
        }
        return $akunPusat;
    }
}
