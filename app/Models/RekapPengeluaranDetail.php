<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RekapPengeluaranDetail extends MyModel
{
    use HasFactory;

    protected $table = 'rekappengeluarandetail';

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

        if (isset(request()->id)) {
            $query->where("$this->table.id", request()->id);
        }

        if (isset(request()->rekappengeluaran_id)) {
            $query->where("$this->table.rekappengeluaran_id", request()->rekappengeluaran_id);
        }

        // if (count(request()->whereIn) > 0) {
        //     $query->whereIn('rekappengeluaran_id', request()->whereIn);
        // }
        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "$this->table.id",
                "$this->table.rekappengeluaran_id",
                "$this->table.nobukti",
                "$this->table.pengeluaran_nobukti",
                "$this->table.tgltransaksi",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.modifiedby",
            );
        } else {
                
            $query->select(
                "$this->table.id",
                "$this->table.rekappengeluaran_id",
                "$this->table.nobukti",
                "$this->table.pengeluaran_nobukti",
                "$this->table.tgltransaksi",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.modifiedby",
            )
            ->leftJoin("rekappengeluaranheader", "$this->table.rekappengeluaran_id", "rekappengeluaranheader.id")
            ->leftJoin("pengeluaranheader", "$this->table.pengeluaran_nobukti", "pengeluaranheader.nobukti");
            $this->totalNominal = $query->sum('nominal');
            $this->filter($query);
        }
        return $query->get();
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            
                        }
                    });
                    break;
                default:

                    break;
            }
        }
    }
                
    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
