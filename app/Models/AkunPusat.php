<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'akunpusat.*',
            'parameter_statusaktif.text as statusaktif',
            'parameter_statuscoa.text as statuscoa',
            'parameter_statusaccountpayable.text as statusaccountpayable',
            'parameter_statusneraca.text as statusneraca',
            'parameter_statuslabarugi.text as statuslabarugi'
        )
            ->leftJoin('parameter as parameter_statusaktif', 'akunpusat.statusaktif', '=', 'parameter_statusaktif.id')
            ->leftJoin('parameter as parameter_statuscoa', 'akunpusat.statuscoa', '=', 'parameter_statuscoa.id')
            ->leftJoin('parameter as parameter_statusaccountpayable', 'akunpusat.statusaccountpayable', '=', 'parameter_statusaccountpayable.id')
            ->leftJoin('parameter as parameter_statusneraca', 'akunpusat.statusneraca', '=', 'parameter_statusneraca.id')
            ->leftJoin('parameter as parameter_statuslabarugi', 'akunpusat.statuslabarugi', '=', 'parameter_statuslabarugi.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
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
                        $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
