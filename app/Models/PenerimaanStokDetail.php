<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanStokDetail extends MyModel
{
    use HasFactory;

    protected $table = "PenerimaanStokDetail";

    protected $casts = [
        "created_at" => "date:d-m-Y H:i:s",
        "updated_at" => "date:d-m-Y H:i:s"
    ];

    protected $guarded = [
        "id",
        "created_at",
        "updated_at",
    ];
    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->id)) {
            $query->where("$this->table.id", request()->id);
        }

        if (isset(request()->penerimaanstokheader_id)) {
            $query->where("$this->table.penerimaanstokheader_id", request()->penerimaanstokheader_id);
        }
        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "$this->table.penerimaanstokheader_id",
                "$this->table.nobukti",
                "stok.namastok as stok",
                "$this->table.stok_id",
                "$this->table.qty",
                "$this->table.harga",
                "$this->table.persentasediscount",
                "$this->table.nominaldiscount",
                "$this->table.total",
                "$this->table.keterangan",
                "$this->table.vulkanisirke",
                "$this->table.modifiedby",
            );
            $totalRows =  $query->count();
            $penerimaanStokDetail = $query->get();
        }else{
            $query->select(
                "$this->table.penerimaanstokheader_id",
                "$this->table.nobukti",
                "$this->table.stok_id",
                "stok.namastok as stok",
                "$this->table.qty",
                "$this->table.harga",
                "$this->table.persentasediscount",
                "$this->table.nominaldiscount",
                "$this->table.total",
                "$this->table.keterangan",
                "$this->table.vulkanisirke",
                "$this->table.modifiedby",
            )
            ->leftJoin("penerimaanstokheader", "$this->table.penerimaanstokheader_id", "penerimaanstokheader.id")
            ->leftJoin("stok", "$this->table.stok_id", "stok.id");
            $this->totalNominal = $query->sum('total');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }
        return $query->get();
    }

    public function getAll($id)
    {
        $query = DB::table("PenerimaanStokDetail");
        $query = $query->select(
            "PenerimaanStokDetail.penerimaanstokheader_id",
            "PenerimaanStokDetail.nobukti",
            "stok.namastok as stok",
            "PenerimaanStokDetail.stok_id",
            "PenerimaanStokDetail.qty",
            "PenerimaanStokDetail.harga",
            "PenerimaanStokDetail.persentasediscount",
            "PenerimaanStokDetail.nominaldiscount",
            "PenerimaanStokDetail.total",
            "PenerimaanStokDetail.keterangan",
            "PenerimaanStokDetail.vulkanisirke",
            "PenerimaanStokDetail.modifiedby",
        )
        ->leftJoin("stok","penerimaanstokdetail.stok_id","stok.id");

        $data = $query->where("penerimaanstokheader_id",$id)->get();

        return $data;
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'stok') {
                                $query = $query->where('stok.namastok', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'stok') {
                                $query = $query->orWhere('stok.namastok', 'LIKE', "%$filters[data]%");
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
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
