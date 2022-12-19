<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;



class ServiceOutHeader extends MyModel
{
    use HasFactory;

    protected $table = 'serviceoutheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function serviceoutdetail()
    {
        return $this->hasMany(ServiceOutDetail::class, 'serviceout_id');
    }

    public function get()
    {
        $this->setRequestParameters();
        $query = DB::table($this->table)->select(
            'serviceoutheader.id',
            'serviceoutheader.nobukti',
            'serviceoutheader.tglbukti',

            'trado.keterangan as trado_id',
            'statuscetak.memo as statuscetak',

            'serviceoutheader.tglkeluar',
            'serviceoutheader.keterangan',
            'serviceoutheader.modifiedby',
            'serviceoutheader.created_at',
            'serviceoutheader.updated_at'

            )
            ->leftJoin('parameter as statuscetak' , 'serviceoutheader.statuscetak', 'statuscetak.id')
            ->leftJoin('trado', 'serviceoutheader.trado_id', 'trado.id');

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

        $query = DB::table('serviceoutheader')->select(
            'serviceoutheader.id',
            'serviceoutheader.nobukti',
            'serviceoutheader.tglbukti',
            'serviceoutheader.trado_id',

            'trado.keterangan as trado',
            'statuscetak.memo as statuscetak',

            'serviceoutheader.tglkeluar',
            'serviceoutheader.keterangan',
            'serviceoutheader.modifiedby',
            'serviceoutheader.created_at',
            'serviceoutheader.updated_at'

        )
        ->leftJoin('parameter as statuscetak' , 'serviceoutheader.statuscetak', 'statuscetak.id')
        ->leftJoin('trado', 'serviceoutheader.trado_id', 'trado.id')
        ->where('serviceoutheader.id', $id);
        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'trado.keterangan as trado_id',
            $this->table.tglkeluar,
            $this->table.keterangan,
            'statuscetak.memo as statuscetak',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )
        ->leftJoin('parameter as statuscetak' , 'serviceoutheader.statuscetak', 'statuscetak.id')
         ->leftJoin('trado', 'serviceoutheader.trado_id', 'trado.id');

    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('trado_id')->default('');
            $table->date('tglkeluar')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->string('statuscetak', 50)->default('');

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
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti',  'trado_id','tglkeluar','keterangan','statuscetak', 'modifiedby', 'created_at', 'updated_at'], $models);


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
        if (request()->cetak && request()->periode) {
            $query->where('serviceoutheader.statuscetak','<>', request()->cetak)
                  ->whereYear('serviceoutheader.tglbukti','=', request()->year)
                  ->whereMonth('serviceoutheader.tglbukti','=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
