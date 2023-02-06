<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanStok extends MyModel
{
    use HasFactory;

    protected $table = 'PenerimaanStok';

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

        $penerimaanStok = DB::table('penerimaanstokheader')
            ->from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaanstok_id'
            )
            ->where('a.penerimaanstok_id', '=', $id)
            ->first();
        if (isset($penerimaanStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Stok',
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

        // $query = DB::table($this->table); 

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            'penerimaanstok.id',
            'penerimaanstok.kodepenerimaan',
            'penerimaanstok.keterangan',
            'penerimaanstok.coa',
            'parameterformat.memo as format',
            'parameterformat.text as formattext',
            'parameterformat.id as formatid',
            'parameterstatushitungstok.memo as statushitungstok',
            'parameterstatushitungstok.text as statushitungstoktext',
            'parameterstatushitungstok.id as statushitungstokid',
            'penerimaanstok.modifiedby',
            'penerimaanstok.created_at',
            'penerimaanstok.updated_at'
        )
            ->leftJoin(DB::raw("parameter as parameterformat with (readuncommitted)"), 'penerimaanstok.format', '=', 'parameterformat.id')
            ->leftJoin(DB::raw("parameter as parameterstatushitungstok with (readuncommitted)"), 'penerimaanstok.statushitungstok', '=', 'parameterstatushitungstok.id');

        // $query = $this->selectColumns($query);

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
            $table->unsignedBigInteger('statushitungstok')->default(0);
        });

        $statusaktif=Parameter::from (
            db::Raw("parameter with (readuncommitted)")
        )
        ->select (
            'id'
        )
        ->where('grp','=','STATUS HITUNG STOK')
        ->where('subgrp','=','STATUS HITUNG STOK')
        ->where('default','=','YA')
        ->first();
        
        DB::table($tempdefault)->insert(["statushitungstok" => $statusaktif->id]);
        
        $query=DB::table($tempdefault)->from(
            DB::raw($tempdefault )
        )
            ->select(
                'statushitungstok');

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->longText('kodepenerimaan')->default('');
            $table->longText('keterangan')->default('');
            $table->string('coa', 50)->default('');
            $table->unsignedBigInteger('format')->default(0);
            $table->integer('statushitungstok')->length(11)->default(0);
            $table->string('modifiedby', 50)->default('');
            $table->increments('position');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'kodepenerimaan',
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
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "$this->table.id",
                "$this->table.kodepenerimaan",
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
                            $query = $query->where('parameterstatushitungstok.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'formattext') {
                            $query = $query->where('parameterformat.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'formatid') {
                            $query = $query->where('parameterformat.id', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statushitungstok') {
                            $query = $query->orWhere('parameterstatushitungstok.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'formattext') {
                            $query = $query->orWhere('parameterformat.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'formatid') {
                            $query = $query->orWhere('parameterformat.id', 'LIKE', "%$filters[data]%");
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

    public function find($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query);
        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
