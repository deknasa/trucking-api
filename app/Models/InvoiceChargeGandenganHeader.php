<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class InvoiceChargeGandenganHeader extends Model
{
    use HasFactory;

    protected $table = 'invoicechargegandenganheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];


    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
        
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'invoicechargegandenganheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusformat with (readuncommitted)"), 'invoicechargegandenganheader.statusformat', 'statusformat.id')
            ->leftJoin(DB::raw("parameter as cetak with (readuncommitted)"), 'invoicechargegandenganheader.statuscetak', 'cetak.id')

            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoicechargegandenganheader.agen_id', 'agen.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }
    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglproses",
                "$this->table.tglbukti",
                "$this->table.agen_id",
                "$this->table.nominal",
                'parameter.memo as statusapproval',
                "$this->table.userapproval",
                DB::raw('(case when (year(invoiceextraheader.tglapproval) <= 2000) then null else invoiceextraheader.tglapproval end ) as tglapproval'),
                "$this->table.created_at",
                "$this->table.updated_at",
                "cetak.memo as statuscetak",

                "$this->table.modifiedby",
                "statusformat.memo as  statusformat_memo",
                "pelanggan.namapelanggan as  pelanggan",
                "agen.namaagen as  agen",
            );
    }
}



