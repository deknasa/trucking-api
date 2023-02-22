<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapPenerimaanDetail extends MyModel
{
    use HasFactory;

    protected $table = 'rekappenerimaandetail';

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
                "$this->table.id",
                "$this->table.rekappenerimaan_id",
                "$this->table.nobukti",
                "$this->table.penerimaan_nobukti",
                "$this->table.tgltransaksi",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.modifiedby",
            );
            $query->where($this->table . ".rekappenerimaan_id", "=", request()->rekappenerimaan_id);

        } else {
            $query->select(
                "$this->table.id",
                "$this->table.rekappenerimaan_id",
                "$this->table.nobukti",
                "$this->table.penerimaan_nobukti",
                "$this->table.tgltransaksi",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.modifiedby",
            )
            ->leftJoin('rekappenerimaanheader', "$this->table.rekappenerimaan_id", 'rekappenerimaanheader.id')
            ->leftJoin('penerimaanheader', "$this->table.penerimaan_nobukti", 'penerimaanheader.nobukti');
            $query->where($this->table . ".rekappenerimaan_id", "=", request()->rekappenerimaan_id);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }
        return $query->get();
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
