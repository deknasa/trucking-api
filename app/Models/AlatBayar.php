<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\RestrictDeletion;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlatBayar extends MyModel
{
    use HasFactory, RestrictDeletion;

    protected $table = 'alatbayar';

    // protected $casts = [
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
            'alatbayar.id',
            'alatbayar.kodealatbayar',
            'alatbayar.namaalatbayar',
            'alatbayar.keterangan',
            'parameter_statuslangsunggcair.text as statuslangsunggcair',
            'parameter_statusdefault.text as statusdefault',
            'bank.namabank as bank_id',
            'alatbayar.modifiedby',
            'alatbayar.created_at',
            'alatbayar.updated_at'
        )
            ->leftJoin('bank', 'alatbayar.bank_id', 'bank.id')
            ->leftJoin('parameter as parameter_statuslangsunggcair', 'alatbayar.statuslangsunggcair', 'parameter_statuslangsunggcair.id')
            ->leftJoin('parameter as parameter_statusdefault', 'alatbayar.statusdefault', 'parameter_statusdefault.id');

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
                        if ($filters['field'] == 'statuslangsunggcair') {
                            $query = $query->where('parameter_statuslangsunggcair.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusdefault') {
                            $query = $query->where('parameter_statusdefault.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuslangsunggcair') {
                            $query = $query->orWhere('parameter_statuslangsunggcair.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusdefault') {
                            $query = $query->orWhere('parameter_statusdefault.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
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
