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

    public function get()
    {

        $level =request()->level ?? '';

        $this->setRequestParameters();

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
                'akunpusat.modifiedby',
                'akunpusat.created_at',
                'akunpusat.updated_at',
                'parameter_statusaktif.memo as statusaktif',
                'parameter_statuscoa.memo as statuscoa',
                'parameter_statusaccountpayable.memo as statusaccountpayable',
                'parameter_statusneraca.memo as statusneraca',
                'parameter_statuslabarugi.memo as statuslabarugi'
            )
            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'akunpusat.statusaktif', '=', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statuscoa with (readuncommitted)"), 'akunpusat.statuscoa', '=', 'parameter_statuscoa.id')
            ->leftJoin(DB::raw("parameter as parameter_statusaccountpayable with (readuncommitted)"), 'akunpusat.statusaccountpayable', '=', 'parameter_statusaccountpayable.id')
            ->leftJoin(DB::raw("parameter as parameter_statusneraca with (readuncommitted)"), 'akunpusat.statusneraca', '=', 'parameter_statusneraca.id')
            ->leftJoin(DB::raw("parameter as parameter_statuslabarugi with (readuncommitted)"), 'akunpusat.statuslabarugi', '=', 'parameter_statuslabarugi.id');
            if ($level!='') {
                $query->where('akunpusat.level','=',$level);
                // dd($query->get());
            }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statuscoa')->default(0);
            $table->unsignedBigInteger('statusaccountpayable')->default(0);
            $table->unsignedBigInteger('statuslabarugi')->default(0);
            $table->unsignedBigInteger('statusneraca')->default(0);
            $table->unsignedBigInteger('statusaktif')->default(0);
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
            ->where('default','=','YA')
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
            ->where('default','=','YA')
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
            $table->bigInteger('id')->default('0');
            $table->string('coa', 1000)->default('');
            $table->string('keterangancoa', 1000)->default('');
            $table->string('type', 1000)->default('');
            $table->bigInteger('level')->default('');
            $table->string('statusaktif', 1000)->default('');
            $table->string('parent', 1000)->default('');
            $table->string('statuscoa', 1000)->default('');
            $table->string('statusaccountpayable', 1000)->default('');
            $table->string('statusneraca', 1000)->default('');
            $table->string('statuslabarugi', 1000)->default('');
            $table->string('coamain', 1000)->default('');
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
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
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuscoa') {
                            $query = $query->orWhere('parameter_statuscoa.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusaccountpayable') {
                            $query = $query->orWhere('parameter_statusaccountpayable.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusneraca') {
                            $query = $query->orWhere('parameter_statusneraca.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuslabarugi') {
                            $query = $query->orWhere('parameter_statuslabarugi.text', '=', "$filters[data]");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
