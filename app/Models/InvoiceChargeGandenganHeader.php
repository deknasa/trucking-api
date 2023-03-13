<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceChargeGandenganHeader extends MyModel
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
                "$this->table.tglbukti",
                "$this->table.tglproses",
                "$this->table.agen_id",
                "$this->table.nominal",
                "$this->table.statusapproval",
                "$this->table.userapproval",
                "$this->table.statusformat",
                "$this->table.statuscetak",
                "$this->table.userbukacetak",
                "$this->table.tglbukacetak",
                "$this->table.jumlahcetak",
                "$this->table.modifiedby",
                "agen.namaagen as  agen",
                'parameter.memo as statusapproval',
                "cetak.memo as statuscetak",
                "statusformat.memo as  statusformat_memo",
                "$this->table.created_at",
                "$this->table.updated_at",
                DB::raw('(case when (year(invoicechargegandenganheader.tglapproval) <= 2000) then null else invoicechargegandenganheader.tglapproval end ) as tglapproval'),

                
                
            );
    }
    public function find($id)
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

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
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
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }
                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }
                    break;
                default:
                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        if (request()->approve && request()->periode) {
            $query->where('invoicechargegandenganheader.statusapproval', '<>', request()->approve)
                ->whereYear('invoicechargegandenganheader.tglbukti', '=', request()->year)
                ->whereMonth('invoicechargegandenganheader.tglbukti', '=', request()->month);
            return $query;
        }
        if (request()->cetak && request()->periode) {
            $query->where('invoicechargegandenganheader.statuscetak', '<>', request()->cetak)
                ->whereYear('invoicechargegandenganheader.tglbukti', '=', request()->year)
                ->whereMonth('invoicechargegandenganheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}



