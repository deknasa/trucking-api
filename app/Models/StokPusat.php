<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StokPusat extends MyModel
{
    use HasFactory;

    protected $table = 'stokpusat';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();
        $query = DB::table("stokpusat")->from(DB::raw("stokpusat with (readuncommitted)"))
            ->select(
                'stokpusat.id',
                'stokpusat.namastok',
                'kelompok.kodekelompok as kelompok',
                'stokpusat.modifiedby',
                'stokpusat.created_at',
                'stokpusat.updated_at'
            )
            ->leftJoin(DB::raw("kelompok with (readuncommitted)"), 'stokpusat.kelompok_id', 'kelompok.id');

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
        if ($this->params['sortIndex'] == 'kelompok') {
            return $query->orderBy('kelompok.kodekelompok', $this->params['sortOrder']);
        }
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'kelompok') {
                            $query = $query->where('kelompok.kodekelompok','LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%' escape '|'");
                        } else {
                            // $query = $query->whereRaw($this->table . ".".  $filters['field'] ." LIKE '%".str_replace($filters['data'],'[','|[') ."%' escape '|'");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'kelompok') {
                                $query = $query->orWhere('kelompok.kodekelompok','LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    });
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
