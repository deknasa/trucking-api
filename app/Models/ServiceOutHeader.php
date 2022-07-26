<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ServiceOutHeader extends MyModel
{
    use HasFactory;

    protected $table = 'ServiceOutHeader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function serviceoutdetail() {
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
            'serviceoutheader.tglkeluar',
            'serviceoutheader.keterangan',
            'serviceoutheader.modifiedby',
            'serviceoutheader.created_at',
            'serviceoutheader.updated_at'

        )
            ->join('trado', 'trado.id', '=', 'serviceoutheader.trado_id');

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
