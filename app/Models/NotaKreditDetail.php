<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotaKreditDetail extends MyModel
{
    use HasFactory;

    protected $table = 'notakreditdetail';

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
                "$this->table.id",
                "$this->table.notakredit_id",
                "$this->table.nobukti",
                "$this->table.tglterima",
                "$this->table.invoice_nobukti",
                "$this->table.nominal",
                "$this->table.nominalbayar",
                "$this->table.penyesuaian",
                "$this->table.keterangan",
                "$this->table.coaadjust",
                "$this->table.modifiedby"

            )
            ->leftJoin('notakreditheader as header', 'header.id', $this->table.'.notakredit_id');

            $query->where($this->table . ".notakredit_id", "=", request()->notakredit_id);
        } else {

            $query->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglterima",
                "$this->table.invoice_nobukti",
                "$this->table.nominal",
                "$this->table.nominalbayar",
                "$this->table.penyesuaian",
                "$this->table.keterangan",
                "akunpusat.keterangancoa as coaadjust",
                "$this->table.modifiedby"
            )
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), "$this->table.coaadjust", 'akunpusat.coa');
            
            $this->sort($query);

            $query->where($this->table . ".notakredit_id", "=", request()->notakredit_id);
            $this->filter($query);
            
            $this->totalNominal = $query->sum('nominal');
            $this->totalNominalBayar = $query->sum('nominalbayar');
            $this->totalPenyesuaian = $query->sum('penyesuaian');
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
                            if ($filters['field'] == 'coaadjust') {
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
                            if ($filters['field'] == 'coaadjust') {
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
