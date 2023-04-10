<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PindahBuku extends MyModel
{
    use HasFactory;
    protected $table = 'pindahbuku';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->string('alatbayar', 255)->nullable();
        });

        $statusdefault = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS DEFAULT')
            ->where('subgrp', '=', 'STATUS DEFAULT')
            ->where('text', '=', 'DEFAULT')
            ->first();

        $alatbayardefault = $statusdefault->id ?? 0;

        $alatbayar = DB::table('alatbayar')->from(
            DB::raw('alatbayar with (readuncommitted)')
        )
            ->select(
                'id as alatbayar_id',
                'namaalatbayar as alatbayar',

            )
            ->where('statusdefault', '=', $alatbayardefault)
            ->first();


        DB::table($tempdefault)->insert(
            ["alatbayar_id" => $alatbayar->alatbayar_id, "alatbayar" => $alatbayar->alatbayar]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'alatbayar_id',
                'alatbayar',
            );
        $data = $query->first();

        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(
            DB::raw("pindahbuku with (readuncommitted)")
        )
            ->select(
                'pindahbuku.id',
                'pindahbuku.nobukti',
                'pindahbuku.tglbukti',
                'bankdari.namabank as bankdari',
                'bankke.namabank as bankke',
                'coadebet.keterangancoa as coadebet',
                'coakredit.keterangancoa as coakredit',
                'alatbayar.namaalatbayar as alatbayar',
                'pindahbuku.nowarkat',
                'pindahbuku.tgljatuhtempo',
                'pindahbuku.nominal',
                'pindahbuku.keterangan',
                'pindahbuku.modifiedby',
                'pindahbuku.created_at',
                'pindahbuku.updated_at'
            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("bank as bankdari with (readuncommitted)"), 'pindahbuku.bankdari_id', 'bankdari.id')
            ->leftJoin(DB::raw("bank as bankke with (readuncommitted)"), 'pindahbuku.bankke_id', 'bankke.id')
            ->leftJoin(DB::raw("akunpusat as coadebet with (readuncommitted)"), 'pindahbuku.coadebet', 'coadebet.coa')
            ->leftJoin(DB::raw("akunpusat as coakredit with (readuncommitted)"), 'pindahbuku.coakredit', 'coakredit.coa')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pindahbuku.alatbayar_id', 'alatbayar.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 'bankdari.namabank as bankdari',
                'bankke.namabank as bankke',
                'coadebet.keterangancoa as coadebet',
                'coakredit.keterangancoa as coakredit',
                'alatbayar.namaalatbayar as alatbayar',
                 $this->table.nowarkat,
                 $this->table.tgljatuhtempo,
                 $this->table.nominal,
                 $this->table.keterangan,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("bank as bankdari with (readuncommitted)"), 'pindahbuku.bankdari_id', 'bankdari.id')
            ->leftJoin(DB::raw("bank as bankke with (readuncommitted)"), 'pindahbuku.bankke_id', 'bankke.id')
            ->leftJoin(DB::raw("akunpusat as coadebet with (readuncommitted)"), 'pindahbuku.coadebet', 'coadebet.coa')
            ->leftJoin(DB::raw("akunpusat as coakredit with (readuncommitted)"), 'pindahbuku.coakredit', 'coakredit.coa')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pindahbuku.alatbayar_id', 'alatbayar.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('bankdari', 1000)->nullable();
            $table->string('bankke', 1000)->nullable();
            $table->string('coadebet', 1000)->nullable();
            $table->string('coakredit', 1000)->nullable();
            $table->string('alatbayar', 1000)->nullable();
            $table->string('nowarkat', 1000)->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->float('nominal')->nullable();
            $table->string('keterangan')->nullable();
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
        $models = $query
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'bankdari', 'bankke', 'coadebet', 'coakredit', 'alatbayar', 'nowarkat','tgljatuhtempo','nominal','keterangan', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function findAll($id){
        $query = DB::table($this->table)->from(DB::raw("pindahbuku with (readuncommitted)"))
        ->select(
            'pindahbuku.id',
            'pindahbuku.nobukti',
            'pindahbuku.tglbukti',
            'pindahbuku.bankdari_id',
            'bankdari.namabank as bankdari',
            'pindahbuku.bankke_id',
            'bankke.namabank as bankke',
            'pindahbuku.alatbayar_id',
            'alatbayar.namaalatbayar as alatbayar',
            'pindahbuku.nowarkat',
            'pindahbuku.tgljatuhtempo',
            'pindahbuku.nominal',
            'pindahbuku.keterangan'
        )
        ->leftJoin(DB::raw("bank as bankdari with (readuncommitted)"), 'pindahbuku.bankdari_id', 'bankdari.id')
        ->leftJoin(DB::raw("bank as bankke with (readuncommitted)"), 'pindahbuku.bankke_id', 'bankke.id')
        ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pindahbuku.alatbayar_id', 'alatbayar.id')
        ->where('pindahbuku.id', $id);

        return $query->first();
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'bankdari') {
            return $query->orderBy('bankdari.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'bankke') {
            return $query->orderBy('bankke.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coadebet') {
            return $query->orderBy('coadebet.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coakredit') {
            return $query->orderBy('coakredit.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'alatbayar') {
            return $query->orderBy('alatbayar.namaalatbayar', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'bankdari') {
                            $query = $query->where('bankdari.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bankke') {
                            $query = $query->where('bankke.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coadebet') {
                            $query = $query->where('coadebet.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coakredit') {
                            $query = $query->where('coakredit.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'alatbayar') {
                            $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'bankdari') {
                                $query = $query->orWhere('bankdari.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bankke') {
                                $query = $query->orWhere('bankke.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coadebet') {
                                $query = $query->orWhere('coadebet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->orWhere('coakredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'alatbayar') {
                                $query = $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });
                    break;
                default:

                    break;
            }
        }

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
