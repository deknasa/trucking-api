<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengembalianKasGantungDetail extends MyModel
{
    use HasFactory;
    protected $table = 'pengembaliankasgantungdetail';

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
        if (isset(request()->pengembaliankasgantung_id)) {
            $query->where("$this->table.pengembaliankasgantung_id", request()->pengembaliankasgantung_id);
        }
        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "header.id",
                "header.nobukti",
                "header.tglbukti",
                "header.coakasmasuk",
                "header.tgldari",
                "header.tglsampai",
                "header.penerimaan_nobukti",
                "header.postingdari",
                "header.tglkasmasuk",
                "bank.namabank as bank",
                "$this->table.pengembaliankasgantung_id",
                "$this->table.nobukti",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.coa",
            )
                ->leftJoin(DB::raw("pengembaliankasgantungheader as header with (readuncommitted)"), "header.id", "$this->table.pengembaliankasgantung_id")
                ->leftJoin(DB::raw("bank with (readuncommitted)"), "header.bank_id", "bank.id");

        } else {
            $query->select(
                "$this->table.pengembaliankasgantung_id",
                "$this->table.nobukti",
                "$this->table.kasgantung_nobukti",
                "$this->table.nominal",
                "$this->table.keterangan",
                "akunpusat.keterangancoa as coa",
            )
            ->leftJoin("akunpusat", "$this->table.coa", "akunpusat.coa");
            $query->where($this->table . '.pengembaliankasgantung_id', '=', request()->pengembaliankasgantung_id);

            $this->totalRows = $query->count();
            $this->totalNominal = $query->sum('nominal');
            $this->filter($query);
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }
        return $query->get();
    }

    public function getAll($id)
    {
        $query = DB::table('pengembaliankasgantungdetail');
        $query = $query->select(
            'pengembaliankasgantungdetail.pengembaliankasgantung_id',
            'pengembaliankasgantungdetail.nobukti',
            'pengembaliankasgantungdetail.nominal',
            'pengembaliankasgantungdetail.coa',
        )
        ->leftJoin('pengembaliankasgantungheader', 'pengembaliankasgantungdetail.pengembaliankasgantung_id', 'pengembaliankasgantungheader.id');

        $data = $query->where("pengembaliankasgantung_id",$id)->get();

        return $data;
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coa') {
                                $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coa') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
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
        if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        }else{
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
