<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServiceInHeader extends MyModel
{
    use HasFactory;

    protected $table = 'serviceinheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function serviceindetail() {
        return $this->hasMany(ServiceInDetail::class, 'servicein_id');
    }

    public function get()
    {
        $this->setRequestParameters();
        $query = DB::table($this->table)->from(
            DB::raw("serviceinheader with (readuncommitted)")
        )
        ->select(
            'serviceinheader.id',
            'serviceinheader.nobukti',
            'serviceinheader.tglbukti',

            'trado.kodetrado as trado_id',
            'statuscetak.memo as statuscetak',

            'serviceinheader.tglmasuk',
            'serviceinheader.modifiedby',
            'serviceinheader.created_at',
            'serviceinheader.updated_at'

        )
        ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)") , 'serviceinheader.statuscetak', 'statuscetak.id')
        ->leftJoin(DB::raw("trado with (readuncommitted)"), 'serviceinheader.trado_id', 'trado.id');
        if (request()->tgldari) {
            $query->whereBetween('serviceinheader.tglbukti', [date('Y-m-d',strtotime(request()->tgldari )), date('Y-m-d',strtotime(request()->tglsampai ))]);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {

        $query = DB::table('serviceinheader')->from(DB::raw("serviceinheader with (readuncommitted)"))
        ->select(
            'serviceinheader.id',
            'serviceinheader.nobukti',
            'serviceinheader.tglbukti',
            'serviceinheader.trado_id',
            'statuscetak.memo as statuscetak',

            'trado.kodetrado as trado',

            'serviceinheader.tglmasuk',
            'serviceinheader.modifiedby',
            'serviceinheader.created_at',
            'serviceinheader.updated_at'

        )
        ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'serviceinheader.statuscetak', 'statuscetak.id')
        ->leftJoin(DB::raw("trado with (readuncommitted)"), 'serviceinheader.trado_id', 'trado.id')
        ->where('serviceinheader.id', $id);

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    {//sesuaikan dengan createtemp
    
        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'trado.kodetrado as trado_id',
            $this->table.tglmasuk,
            'statuscetak.memo as statuscetak',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
            
        )
        ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'serviceinheader.statuscetak', 'statuscetak.id')
        ->leftJoin(DB::raw("trado with (readuncommitted)"), 'serviceinheader.trado_id', 'trado.id');

    }

    public function createTemp(string $modelTable)
    {//sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->string('trado_id')->nullable();
            $table->date('tglmasuk')->nullable();
            $table->string('statuscetak',1000)->nullable();

            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d',strtotime(request()->tgldari )), date('Y-m-d',strtotime(request()->tglsampai ))]);
        }
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti',  'trado_id', 'tglmasuk', 'statuscetak', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if($this->params['sortIndex'] == 'trado_id'){
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        }else{
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                         if ($filters['field'] == 'trado_id') {
                            $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglmasuk') {
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
                             if ($filters['field'] == 'trado_id') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglmasuk') {
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

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        if (request()->cetak && request()->periode) {
            $query->where('serviceinheader.statuscetak','<>', request()->cetak)
                  ->whereYear('serviceinheader.tglbukti','=', request()->year)
                  ->whereMonth('serviceinheader.tglbukti','=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

}
