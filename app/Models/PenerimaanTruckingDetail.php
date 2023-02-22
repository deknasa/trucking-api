<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PenerimaanTruckingDetail extends MyModel
{
    use HasFactory;

    protected $table = "penerimaantruckingdetail";

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

        if (isset(request()->penerimaantruckingheader_id)) {
            $query->where("$this->table.penerimaantruckingheader_id", request()->penerimaantruckingheader_id);
        }

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "header.nobukti",
                "header.tglbukti",
                "header.coa",
                "header.penerimaan_nobukti",
                "bank.namabank as bank",
                "penerimaantrucking.keterangan as penerimaantrucking",
                "supir.namasupir as supir_id",
                "$this->table.pengeluarantruckingheader_nobukti",
                "$this->table.nominal"
            )
            ->leftJoin(DB::raw("penerimaantruckingheader as header with (readuncommitted)"),"header.id","$this->table.penerimaantruckingheader_id")
            ->leftJoin(DB::raw("penerimaantrucking with (readuncommitted)"), "header.penerimaantrucking_id","penerimaantrucking.id")
            ->leftJoin(DB::raw("bank with (readuncommitted)"), "header.bank_id", "bank.id")
            ->leftJoin(DB::raw("supir with (readuncommitted)"), "$this->table.supir_id", "supir.id");

        } else {
            $query->select(
                "$this->table.nobukti",
                "$this->table.nominal",

                "supir.namasupir as supir_id",
                "$this->table.pengeluarantruckingheader_nobukti",
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), "$this->table.supir_id", "supir.id");
            
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }
        return $query->get();
    }

    public function getAll($id)
    {
       

        $query = DB::table("penerimaantruckingdetail")->from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
        ->select(
            "penerimaantruckingdetail.penerimaantruckingheader_id",
            "penerimaantruckingdetail.nominal",
            "penerimaantruckingdetail.pengeluarantruckingheader_nobukti",

            "supir.namasupir as supir",
            "supir.id as supir_id"
        )
            ->leftJoin("supir", "penerimaantruckingdetail.supir_id","supir.id")
            ->where("penerimaantruckingdetail.penerimaantruckingheader_id", "=", $id);
            

        $data = $query->get();

        return $data;
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
