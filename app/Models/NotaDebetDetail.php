<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotaDebetDetail extends MyModel
{
    use HasFactory;

    protected $table = 'notadebetdetail';

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

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "header.nobukti as nobukti_header",
                "header.tglbukti",
                "$this->table.nobukti",
                "$this->table.tglterima",
                "$this->table.invoice_nobukti",
                "$this->table.nominal",
                "$this->table.nominalbayar",
                "$this->table.lebihbayar",
                "$this->table.keterangan",
                "akunpusat.keterangancoa as coalebihbayar",
                "$this->table.modifiedby"

            )
            ->leftJoin('notadebetheader as header', 'header.id', $this->table.'.notadebet_id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), "$this->table.coalebihbayar", 'akunpusat.coa');

            $query->where($this->table . ".notadebet_id", "=", request()->notadebet_id);
        } else {

            $query->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglterima",
                "$this->table.invoice_nobukti",
                "$this->table.nominal",
                "$this->table.nominalbayar",
                "$this->table.lebihbayar",
                "$this->table.keterangan",
                "akunpusat.keterangancoa as coalebihbayar",
                "$this->table.modifiedby"
            )
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), "$this->table.coalebihbayar", 'akunpusat.coa');
            
            $this->sort($query);

            $query->where($this->table . ".notadebet_id", "=", request()->notadebet_id);
            $this->filter($query);
            
            $this->totalNominal = $query->sum('nominal');
            $this->totalNominalBayar = $query->sum('nominalbayar');
            $this->totalLebihBayar = $query->sum('lebihbayar');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }
        return $query->get();
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
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coalebihbayar') {
                                $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coalebihbayar') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
