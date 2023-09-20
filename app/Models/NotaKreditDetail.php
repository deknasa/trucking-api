<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotaKreditDetail extends MyModel
{
    use HasFactory;

    protected $table = 'notakreditdetail';

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
                "$this->table.penyesuaian",
                "$this->table.keterangan",
                "$this->table.coaadjust",
                "akunpusat.keterangancoa as coaadjust",
            )
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), "$this->table.coaadjust", 'akunpusat.coa');

            $query->where($this->table . ".notakredit_id", "=", request()->notakredit_id);
        } else {

            $query->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglterima",
                "$this->table.invoice_nobukti",
                "$this->table.nominal",
                "$this->table.nominalbayar",
                "$this->table.penyesuaian",
                "$this->table.keterangan",
                "akunpusat.keterangancoa as coaadjust",
                "$this->table.modifiedby", 
                db::raw("cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceheader"),
                db::raw("cast(cast(format((cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceheader"), 
                db::raw("cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceextraheader"),
                db::raw("cast(cast(format((cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceextraheader"), 
            )

            ->leftJoin(DB::raw("invoiceheader as invoice with (readuncommitted)"), 'notakreditdetail.invoice_nobukti', '=', 'invoice.nobukti')
            ->leftJoin(DB::raw("invoiceextraheader as invoiceextra with (readuncommitted)"), 'notakreditdetail.invoice_nobukti', '=', 'invoiceextra.nobukti')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), "$this->table.coaadjust", 'akunpusat.coa');
            
            $this->sort($query);

            $query->where($this->table . ".notakredit_id", "=", request()->notakredit_id);
            $this->filter($query);
            
            $this->totalNominal = $query->sum('notakreditdetail.nominal');
            $this->totalNominalBayar = $query->sum('notakreditdetail.nominalbayar');
            $this->totalPenyesuaian = $query->sum('notakreditdetail.penyesuaian');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }
        return $query->get();
    }
    
    public function findAll($id)
    {
        $query = DB::table("notakreditdetail")->from(DB::raw("notakreditdetail with (readuncommitted)"))
        ->select('keterangan', 'penyesuaian')
        ->where('notakredit_id', $id)
        ->get();
        return $query;
    }

    public function sort($query)
    {
        if($this->params['sortIndex'] == 'coaadjust'){
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        }else{
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
                            if ($filters['field'] == 'coaadjust') {
                                $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal' || $filters['field'] == 'nominalbayar' || $filters['field'] == 'penyesuaian') {
                                $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglterima') {
                                $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coaadjust') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal' || $filters['field'] == 'nominalbayar' || $filters['field'] == 'penyesuaian') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglterima') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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
    public function processStore(NotaKreditHeader $notaKreditHeader, array $data): NotaKreditDetail
    {
        $notaKreditDetail = new NotaKreditDetail();
        $notaKreditDetail->notakredit_id = $notaKreditHeader->id;
        $notaKreditDetail->nobukti = $notaKreditHeader->nobukti;
        $notaKreditDetail->tglterima = $notaKreditHeader->tglterima;
        $notaKreditDetail->invoice_nobukti = $data['invoice_nobukti'];
        $notaKreditDetail->nominal = $data['nominal'];
        $notaKreditDetail->nominalbayar = $data['nominalbayar'];
        $notaKreditDetail->penyesuaian = $data['penyesuaian'];
        $notaKreditDetail->keterangan = $data['keterangandetail'];
        $notaKreditDetail->coaadjust = $data['coaadjust'];
        $notaKreditDetail->modifiedby = auth('api')->user()->name;
        $notaKreditDetail->info = html_entity_decode(request()->info);

        if (!$notaKreditDetail->save()) {
            throw new \Exception("Error storing nota kredit Detail.");
        }

        return $notaKreditDetail;
    }
}
