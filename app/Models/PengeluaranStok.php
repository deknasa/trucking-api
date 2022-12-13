<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengeluaranStok extends MyModel
{
    use HasFactory;

    protected $table = 'PengeluaranStok';

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

        // $query = DB::table($this->table); 
        // $query = $this->selectColumns($query);

        $query = DB::table($this->table)->select(
            'pengeluaranstok.id',
            'pengeluaranstok.kodepengeluaran',
            'pengeluaranstok.keterangan',
            'pengeluaranstok.coa',
            'parameterstatusformat.memo as statusformat',
            'parameterstatushitungstok.memo as statushitungstok',
            'pengeluaranstok.modifiedby',
            'pengeluaranstok.created_at',
            'pengeluaranstok.updated_at'
        )
        ->leftJoin('parameter as parameterstatusformat', 'pengeluaranstok.statusformat', '=', 'parameterstatusformat.id')
        ->leftJoin('parameter as parameterstatushitungstok', 'pengeluaranstok.statushitungstok', '=', 'parameterstatushitungstok.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, 10000);

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->longText('kodepengeluaran')->default('');
            $table->longText('keterangan')->default('');
            $table->string('coa',50)->default('');
            $table->unsignedBigInteger('statusformat')->default(0);
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
            'kodepengeluaran',
            'keterangan',
            'coa',
            'statusformat',
            'statushitungstok',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

        return  $temp;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.kodepengeluaran",
            "$this->table.keterangan",
            "$this->table.coa",
            "$this->table.statusformat",
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
                            $query = $query->where('parameterstatushitungstok.text', '=', "$filters[data]");
                        }else{
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");                         
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statushitungstok') {
                            $query = $query->orWhere('parameterstatushitungstok.text', '=', "$filters[data]");
                        }else{
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
        $data = $query->where("$this->table.id",$id)->first();
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
