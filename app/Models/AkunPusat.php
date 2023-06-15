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



        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'akunpusat.id',
                'akunpusat.coa',
                'akunpusat.keterangancoa',
                'akunpusat.type',
                'akunpusat.level',
                'akunpusat.parent',
                'akunpusat.coamain',
                'parameter_statusaktif.memo as statusaktif',
                'parameter_statuscoa.memo as statuscoa',
                'parameter_statusaccountpayable.memo as statusaccountpayable',
                'parameter_statusneraca.memo as statusneraca',
                'parameter_statuslabarugi.memo as statuslabarugi',
                'akunpusat.modifiedby',
                'akunpusat.created_at',
                'akunpusat.updated_at',
                DB::raw("'Laporan Kode Perkiraan' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul")
            )

            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'akunpusat.statusaktif', '=', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statuscoa with (readuncommitted)"), 'akunpusat.statuscoa', '=', 'parameter_statuscoa.id')
            ->leftJoin(DB::raw("parameter as parameter_statusaccountpayable with (readuncommitted)"), 'akunpusat.statusaccountpayable', '=', 'parameter_statusaccountpayable.id')
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
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('akunpusat.statusaktif', '=', $statusaktif->id);
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
    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statuscoa')->nullable();
            $table->unsignedBigInteger('statusaccountpayable')->nullable();
            $table->unsignedBigInteger('statuslabarugi')->nullable();
            $table->unsignedBigInteger('statusneraca')->nullable();
            $table->unsignedBigInteger('statusaktif')->nullable();
        });
        // COA
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS COA')
            ->where('subgrp', '=', 'STATUS COA')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuscoa = $status->id ?? 0;

        // statusaccountpayable
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS ACCOUNT PAYABLE')
            ->where('subgrp', '=', 'STATUS ACCOUNT PAYABLE')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusaccountpayable = $status->id ?? 0;

        // statuslabarugi
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS LABA RUGI')
            ->where('subgrp', '=', 'STATUS LABA RUGI')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuslabarugi = $status->id ?? 0;

        // statusneraca
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS NERACA')
            ->where('subgrp', '=', 'STATUS NERACA')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusneraca = $status->id ?? 0;

        // statusaktif
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusaktif = $status->id ?? 0;

        DB::table($tempdefault)->insert(
            [
                "statuscoa" => $iddefaultstatuscoa,
                "statusaccountpayable" => $iddefaultstatusaccountpayable,
                "statuslabarugi" => $iddefaultstatuslabarugi,
                "statusneraca" => $iddefaultstatusneraca,
                "statusaktif" => $iddefaultstatusaktif,
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statuscoa',
                'statusaccountpayable',
                'statuslabarugi',
                'statusneraca',
                'statusaktif'
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
                $this->table.type,
                $this->table.level,
                'parameter_statusaktif.text as statusaktif',
                $this->table.parent,
                'parameter_statuscoa.text as statuscoa',
                'parameter_statusaccountpayable.text as statusaccountpayable',
                'parameter_statusneraca.text as statusneraca',
                'parameter_statuslabarugi.text as statuslabarugi',
                $this->table.coamain,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
            ")
            )
            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'akunpusat.statusaktif', '=', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statuscoa with (readuncommitted)"), 'akunpusat.statuscoa', '=', 'parameter_statuscoa.id')
            ->leftJoin(DB::raw("parameter as parameter_statusaccountpayable with (readuncommitted)"), 'akunpusat.statusaccountpayable', '=', 'parameter_statusaccountpayable.id')
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
            $table->string('statuscoa', 1000)->nullable();
            $table->string('statusaccountpayable', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'coa', 'keterangancoa', 'type', 'level', 'statusaktif', 'parent', 'statuscoa', 'statusaccountpayable', 'statusneraca', 'statuslabarugi', 'coamain', 'modifiedby', 'created_at', 'updated_at'], $models);

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
                            $query = $query->where('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuscoa') {
                            $query = $query->where('parameter_statuscoa.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusaccountpayable') {
                            $query = $query->where('parameter_statusaccountpayable.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusneraca') {
                            $query = $query->where('parameter_statusneraca.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuslabarugi') {
                            $query = $query->where('parameter_statuslabarugi.text', '=', "$filters[data]");
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
                                $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'statuscoa') {
                                $query = $query->orWhere('parameter_statuscoa.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusaccountpayable') {
                                $query = $query->orWhere('parameter_statusaccountpayable.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusneraca') {
                                $query = $query->orWhere('parameter_statusneraca.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuslabarugi') {
                                $query = $query->orWhere('parameter_statuslabarugi.text', '=', "$filters[data]");
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

    public function processStore(array $data): AkunPusat
    {
        $akunPusat = new AkunPusat();
        $akunPusat->coa = $data['coa'];
        $akunPusat->keterangancoa = $data['keterangancoa'];
        $akunPusat->type = $data['type'];
        $akunPusat->level = $data['level'];
        $akunPusat->parent = $data['parent'];
        $akunPusat->statuscoa = $data['statuscoa'];
        $akunPusat->statusaccountpayable = $data['statusaccountpayable'];
        $akunPusat->statusneraca = $data['statusneraca'];
        $akunPusat->statuslabarugi = $data['statuslabarugi'];
        $akunPusat->coamain = $data['coamain'];
        $akunPusat->statusaktif = $data['statusaktif'];
        $akunPusat->modifiedby = auth('api')->user()->name;

        if (!$akunPusat->save()) {
            throw new \Exception("Error storing service in header.");
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
        $akunPusat->coa = $data['coa'];
        $akunPusat->keterangancoa = $data['keterangancoa'];
        $akunPusat->type = $data['type'];
        $akunPusat->level = $data['level'];
        $akunPusat->parent = $data['parent'];
        $akunPusat->statuscoa = $data['statuscoa'];
        $akunPusat->statusaccountpayable = $data['statusaccountpayable'];
        $akunPusat->statusneraca = $data['statusneraca'];
        $akunPusat->statuslabarugi = $data['statuslabarugi'];
        $akunPusat->statusaktif = $data['statusaktif'];
        $akunPusat->coamain = $data['coamain'];
        $akunPusat->modifiedby = auth('api')->user()->name;

        if (!$akunPusat->save()) {
            throw new \Exception("Error update service in header.");
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
            'modifiedby' => $akunPusat->modifiedby
        ]);

        return $akunPusat;
    }
}
