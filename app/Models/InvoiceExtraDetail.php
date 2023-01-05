<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceExtraDetail extends MyModel
{
    use HasFactory;

    protected $table = 'invoiceextradetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];
    public function getAll($id)
    {
        $query = DB::table($this->table);
        $query = $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            "$this->table.invoiceextra_id",
            "$this->table.nobukti",
            "$this->table.nominal",
            "$this->table.keterangan",
            "$this->table.modifiedby"
        )

            ->leftJoin(DB::raw("invoiceextraheader with (readuncommitted)"), 'invoiceextradetail.invoiceextra_id', 'invoiceextraheader.id');
        $data = $query->where("invoiceextradetail.invoiceextra_id", $id)->get();

        return $data;
    }
}
