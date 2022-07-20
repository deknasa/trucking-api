<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Tarif extends MyModel
{
    use HasFactory;

    protected $table = 'tarif';

    // protected $casts = [
    //     'tglberlaku' => 'date:d-m-Y',
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'tarif.id',
            'tarif.tujuan',
            'container.keterangan as container_id',
            'tarif.nominal',
            'parameter.text as statusaktif',
            'tarif.tujuanasal',
            'tarif.sistemton',
            'kota.kodekota as kota_id',
            'zona.zona as zona_id',
            'tarif.nominalton',
            'tarif.tglberlaku',
            'p.text as statuspenyesuaianharga',
            'tarif.modifiedby',
            'tarif.created_at',
            'tarif.updated_at'
        )
            ->leftJoin('parameter', 'tarif.statusaktif', '=', 'parameter.id')
            ->leftJoin('container', 'tarif.container_id', '=', 'container.id')
            ->leftJoin('kota', 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin('zona', 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin('parameter AS p', 'tarif.statuspenyesuaianharga', '=', 'p.id');

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
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } elseif($filters['field'] == 'container_id') {
                            $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                        }else {
                            $query = $query->where('tarif.'.$filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'container_id') {
                            $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere('tarif.'.$filters['field'], 'LIKE', "%$filters[data]%");
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
