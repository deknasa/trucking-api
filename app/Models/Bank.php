<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Bank extends MyModel
{
    use HasFactory;

    protected $table = 'bank';

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
            'bank.id',
            'bank.kodebank',
            'bank.namabank',
            'bank.coa',
            'bank.tipe',
            'parameter.text as statusaktif',
            'kodepenerimaan.text as kodepenerimaan',
            'kodepengeluaran.text as kodepengeluaran',
            'bank.modifiedby',
            'bank.created_at',
            'bank.updated_at'
        )
            ->leftJoin('parameter', 'bank.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as kodepenerimaan', 'bank.kodepenerimaan', '=', 'kodepenerimaan.id')
            ->leftJoin('parameter as kodepengeluaran', 'bank.kodepengeluaran', '=', 'kodepengeluaran.id');

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
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'kodepenerimaan') {
                            $query = $query->where('kodepenerimaan.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'kodepengeluaran') {
                            $query = $query->where('kodepengeluaran.text', '=', $filters['data']);
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'kodepenerimaan') {
                            $query = $query->orWhere('kodepenerimaan.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'kodepengeluaran') {
                            $query = $query->orWhere('kodepengeluaran.text', '=', $filters['data']);
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
