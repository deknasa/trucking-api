<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotaDebetDetail extends MyModel
{
    use HasFactory;

    protected $table = 'notadebetdetail';

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
                "$this->table.invoice_nobukti",
                "$this->table.lebihbayar",
                "$this->table.keterangan",
                "akunpusat.keterangancoa as coalebihbayar",

            )
                ->leftJoin('notadebetheader as header', 'header.id', $this->table . '.notadebet_id')
                ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), "$this->table.coalebihbayar", 'akunpusat.coa');

            $query->where($this->table . ".notadebet_id", "=", request()->notadebet_id);
        } else {

            $query->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglterima",
                "$this->table.invoice_nobukti",
                "$this->table.nominal",
                "$this->table.nominalbayar",
                "$this->table.lebihbayar",
                "$this->table.keterangan",
                "akunpusat.keterangancoa as coalebihbayar",
                "$this->table.modifiedby",
                db::raw("cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceheader"),
                db::raw("cast(cast(format((cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceheader"), 
                db::raw("cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceextraheader"),
                db::raw("cast(cast(format((cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceextraheader"), 
            )

                ->leftJoin(DB::raw("invoiceheader as invoice with (readuncommitted)"), 'notadebetdetail.invoice_nobukti', '=', 'invoice.nobukti')
                ->leftJoin(DB::raw("invoiceextraheader as invoiceextra with (readuncommitted)"), 'notadebetdetail.invoice_nobukti', '=', 'invoiceextra.nobukti')
                ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), "$this->table.coalebihbayar", 'akunpusat.coa');

            $this->sort($query);

            $query->where($this->table . ".notadebet_id", "=", request()->notadebet_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('notadebetdetail.nominal');
            $this->totalNominalBayar = $query->sum('notadebetdetail.nominalbayar');
            $this->totalLebihBayar = $query->sum('notadebetdetail.lebihbayar');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }
        return $query->get();
    }

    public function findAll($id)
    {
        $query = DB::table("notadebetdetail")->from(DB::raw("notadebetdetail with (readuncommitted)"))
        ->select('keterangan', 'lebihbayar')
        ->where('notadebet_id', $id)
        ->get();
        return $query;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'coalebihbayar') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coalebihbayar') {
                                $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal' || $filters['field'] == 'nominalbayar' || $filters['field'] == 'lebihbayar') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglterima') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coalebihbayar') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal' || $filters['field'] == 'nominalbayar' || $filters['field'] == 'lebihbayar') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglterima') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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

    public function processStore(NotaDebetHeader $notaDebetHeader, array $data): NotaDebetDetail
    {
        $notaDebetDetail = new NotaDebetDetail();
        $notaDebetDetail->notadebet_id = $notaDebetHeader->id;
        $notaDebetDetail->nobukti = $notaDebetHeader->nobukti;
        $notaDebetDetail->tglterima = $notaDebetHeader->tglbukti;
        $notaDebetDetail->invoice_nobukti = $data['invoice_nobukti'];
        $notaDebetDetail->nominal = $data['nominal'];
        $notaDebetDetail->nominalbayar = $data['nominalbayar'];
        $notaDebetDetail->lebihbayar = $data['lebihbayar'];
        $notaDebetDetail->keterangan = $data['keterangandetail'];
        $notaDebetDetail->coalebihbayar = $data['coalebihbayar'];
        $notaDebetDetail->modifiedby = auth('api')->user()->name;
        $notaDebetDetail->info = html_entity_decode(request()->info);

        if (!$notaDebetDetail->save()) {
            throw new \Exception("Error storing nota debet Detail.");
        }

        return $notaDebetDetail;
    }
}
