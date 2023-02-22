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

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "$this->table.pengembaliankasgantung_id",
                "$this->table.nobukti",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.coa",
            );
            $query->where($this->table . '.pengembaliankasgantung_id', '=', request()->pengembaliankasgantung_id);

        }else {
            $query->select(
                "$this->table.pengembaliankasgantung_id",
                "$this->table.nobukti",
                "$this->table.kasgantung_nobukti",
                "$this->table.nominal",
                "$this->table.keterangan",
                "akunpusat.keterangancoa as coa",
            )
            // ->leftJoin("pengeluaranstok","pengeluaranstokheader.pengeluaranstok_id","pengeluaranstok.id")
            ->leftJoin("akunpusat", "$this->table.coa", "akunpusat.coa");
            $query->where($this->table . '.pengembaliankasgantung_id', '=', request()->pengembaliankasgantung_id);

            $this->totalRows = $query->count();
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

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
