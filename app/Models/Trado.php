<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Trado extends MyModel
{
    use HasFactory;

    protected $table = 'trado';

    public function absensiSupir() {
        return $this->belongsToMany(AbsensiSupirDetail::class);
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'trado.*',
            'parameter_statusaktif.text as statusaktif',
            'parameter_statusstandarisasi.text as statusstandarisasi',
            'parameter_statusmutasi.text as statusmutasi',
            'parameter_statusvalidasikendaraan.text as statusvalidasikendaraan',
            'mandor.namamandor as mandor_id',
        )
            ->leftJoin('parameter as parameter_statusaktif', 'trado.statusaktif', 'parameter_statusaktif.id')
            ->leftJoin('parameter as parameter_statusstandarisasi', 'trado.statusstandarisasi', 'parameter_statusstandarisasi.id')
            ->leftJoin('parameter as parameter_statusmutasi', 'trado.statusmutasi', 'parameter_statusmutasi.id')
            ->leftJoin('parameter as parameter_statusvalidasikendaraan', 'trado.statusvalidasikendaraan', 'parameter_statusvalidasikendaraan.id')
            ->leftJoin('mandor', 'trado.mandor_id', 'mandor.id');

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
                        } else if ($filters['field'] == 'statusstandarisasi') {
                            $query = $query->where('parameter_statusstandarisasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusmutasi') {
                            $query = $query->where('parameter_statusmutasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusvalidasikendaraan') {
                            $query = $query->where('parameter_statusvalidasikendaraan.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'mandor_id') {
                            $query = $query->where('mandor.namamandor', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusstandarisasi') {
                            $query = $query->orWhere('parameter_statusstandarisasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusmutasi') {
                            $query = $query->orWhere('parameter_statusmutasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusvalidasikendaraan') {
                            $query = $query->orWhere('parameter_statusvalidasikendaraan.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'mandor_id') {
                            $query = $query->orWhere('mandor.namamandor', 'LIKE', "%$filters[data]%");
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
