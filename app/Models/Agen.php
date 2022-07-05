<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\RestrictDeletion;
use Illuminate\Support\Facades\DB;

class Agen extends MyModel
{
    use HasFactory, RestrictDeletion;

    protected $table = 'agen';

    protected $casts = [
        'tglapproval' => 'date:d-m-Y',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function isDeletable()
    {
        $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();

        return $this->statusapproval != $statusApproval->id;
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'agen.*',
            'parameter_statusaktif.text as statusaktif',
            'parameter_statusapproval.text as statusapproval',
            'parameter_statustas.text as statustas'
        )
            ->leftJoin('parameter as parameter_statusaktif', 'agen.statusaktif', 'parameter_statusaktif.id')
            ->leftJoin('parameter as parameter_statusapproval', 'agen.statusapproval', 'parameter_statusapproval.id')
            ->leftJoin('parameter as parameter_statustas', 'agen.statustas', 'parameter_statustas.id');

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
                            $query = $query->where('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter_statusapproval.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statustas') {
                            $query = $query->where('parameter_statustas.text', '=', $filters['data']);
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('parameter_statusapproval.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statustas') {
                            $query = $query->orWhere('parameter_statustas.text', '=', $filters['data']);
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
