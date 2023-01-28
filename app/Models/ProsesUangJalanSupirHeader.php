<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProsesUangJalanSupirHeader extends MyModel
{
    use HasFactory;
    protected $table = 'prosesuangjalansupirheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();


        $query = DB::table($this->table)->from(DB::raw("prosesuangjalansupirheader with (readuncommitted)"))
            ->select(
                'prosesuangjalansupirheader.id',
                'prosesuangjalansupirheader.nobukti',
                'prosesuangjalansupirheader.tglbukti',
                'prosesuangjalansupirheader.absensisupir_nobukti',
                'prosesuangjalansupirheader.nominaluangjalan',
                'prosesuangjalansupirheader.userapproval',
                DB::raw('(case when (year(prosesuangjalansupirheader.tglapproval) <= 2000) then null else prosesuangjalansupirheader.tglapproval end ) as tglapproval'),
                'statusapproval.memo as statusapproval',
                'trado.keterangan as trado_id',
                'supir.namasupir as supir_id',
                'prosesuangjalansupirheader.modifiedby',
                'prosesuangjalansupirheader.created_at',
                'prosesuangjalansupirheader.updated_at'
            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'prosesuangjalansupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'prosesuangjalansupirheader.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'prosesuangjalansupirheader.supir_id', 'supir.id');


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
                 $this->table.absensisupir_nobukti,
                 $this->table.trado_id,
                 $this->table.supir_id,
                 $this->table.nominaluangjalan,
                 $this->table.statusapproval,
                 $this->table.modifiedby,
                 $this->table.updated_at"
            )
        );
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('absensisupir_nobukti', 1000)->default('');
            $table->bigInteger('trado_id')->default('0');
            $table->bigInteger('supir_id')->default('0');
            $table->float('nominaluangjalan')->default('');
            $table->bigInteger('statusapproval')->default('0');
            $table->string('modifiedby')->default();
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'absensisupir_nobukti', 'trado_id', 'supir_id', 'nominaluangjalan', 'statusapproval', 'modifiedby', 'updated_at'], $models);

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
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'trado_id') {
                            $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'trado_id') {
                            $query = $query->orWhere('trado.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supir_id') {
                            $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
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
