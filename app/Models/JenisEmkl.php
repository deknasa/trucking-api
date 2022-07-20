<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class JenisEmkl extends MyModel
{
    use HasFactory;

    protected $table = 'jenisemkl';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    // protected $casts = [
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ];

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'jenisemkl.id',
            'jenisemkl.kodejenisemkl',
            'jenisemkl.keterangan',
            'parameter.text as statusaktif',
            'jenisemkl.modifiedby',
            'jenisemkl.created_at',
            'jenisemkl.updated_at'
        )
            ->leftJoin('parameter', 'jenisemkl.statusaktif', '=', 'parameter.id');

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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'id') {
                            $query = $query->where('jenisemkl.id', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'modifiedby') {
                            $query = $query->where('jenisemkl.modifiedby', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'created_at') {
                            $query = $query->where('jenisemkl.created_at', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'updated_at') {
                            $query = $query->where('jenisemkl.updated_at', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'id') {
                            $query = $query->orWhere('jenisemkl.id', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'modifiedby') {
                            $query = $query->orWhere('jenisemkl.modifiedby', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'created_at') {
                            $query = $query->orWhere('jenisemkl.created_at', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'updated_at') {
                            $query = $query->orWhere('jenisemkl.updated_at', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($filters['field'], 'LIKE', "%$filters[data]%");
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
