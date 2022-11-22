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
        $query = DB::table($this->table)->select(
            'serviceinheader.id',
            'serviceinheader.nobukti',
            'serviceinheader.tglbukti',

            'trado.keterangan as trado_id',

            'serviceinheader.tglmasuk',
            'serviceinheader.keterangan',
            'serviceinheader.modifiedby',
            'serviceinheader.created_at',
            'serviceinheader.updated_at'

        )
        ->leftJoin('trado', 'serviceinheader.trado_id', 'trado.id');

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

        $query = DB::table('serviceinheader')->select(
            'serviceinheader.id',
            'serviceinheader.nobukti',
            'serviceinheader.tglbukti',
            'serviceinheader.trado_id',

            'trado.keterangan as trado',

            'serviceinheader.tglmasuk',
            'serviceinheader.keterangan',
            'serviceinheader.modifiedby',
            'serviceinheader.created_at',
            'serviceinheader.updated_at'

        )
        ->leftJoin('trado', 'serviceinheader.trado_id', 'trado.id')
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
            'trado.keterangan as trado_id',
            $this->table.tglmasuk,
            $this->table.keterangan,

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
            
        )
        ->leftJoin('trado', 'serviceinheader.trado_id', 'trado.id');

    }

    public function createTemp(string $modelTable)
    {//sesuaikan dengan column index
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('trado_id')->default('0');
            $table->date('tglmasuk')->default('1900/1/1');
            $table->longText('keterangan')->default('');

            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti',  'trado_id', 'tglmasuk', 'keterangan', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
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
                         if ($filters['field'] == 'trado_id') {
                            $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                         if ($filters['field'] == 'trado_id') {
                            $query = $query->orWhere('trado.keterangan', 'LIKE', "%$filters[data]%");
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
