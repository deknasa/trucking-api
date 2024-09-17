<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceEmklDetail extends MyModel
{
    use HasFactory;
    protected $table = 'invoiceemkldetail';

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

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $getinfoinvoice = DB::table("invoiceemklheader")->from(db::raw("invoiceemklheader as a with (readuncommitted)"))
                ->select(db::raw("a.pengeluaranheader_nobukti,statusformatreimbursement.text as statusreimbursement"))
                ->join(DB::raw("parameter as statusformatreimbursement with (readuncommitted)"), 'a.statusformatreimbursement', 'statusformatreimbursement.id')
                ->where('a.id', request()->invoiceemkl_id)
                ->first();
            if ($getinfoinvoice->statusreimbursement == 'YA') {
                $query = db::table("pengeluarandetail")->from(db::raw("pengeluarandetail with (readuncommitted)"))
                    ->select('keterangan', 'nominal')
                    ->where('nobukti', $getinfoinvoice->pengeluaranheader_nobukti)
                    ->orderBy('id', 'asc');
            } else {
                $query->select(db::raw("STRING_AGG(b.nocont +' / '+b.noseal, ', ') as keterangan, sum(invoiceemkldetail.nominal) as nominal,sum(invoiceemkldetail.nominal)*0.011 as ppn,  sum(invoiceemkldetail.nominal) + sum(invoiceemkldetail.nominal)*0.011 as total"))
                    ->join(db::raw("jobemkl as b with (readuncommitted)"), 'invoiceemkldetail.jobemkl_nobukti', 'b.nobukti')
                    ->where('invoiceemkldetail.invoiceemkl_id', request()->invoiceemkl_id);
            }
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.jobemkl_nobukti',
                $this->table . '.nominal',
                'container.keterangan as container_id',
            )
                ->leftJoin(DB::raw("container with (readuncommitted)"), 'invoiceemkldetail.container_id', 'container.id');

            $this->sort($query);
            $query->where($this->table . '.invoiceemkl_id', '=', request()->invoiceemkl_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('invoiceemkldetail.nominal');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }
        return $query->get();
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
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'nominal' || $filters['field'] == 'nominalextra' || $filters['field'] == 'nominalretribusi' || $filters['field'] == 'total') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'nominal' || $filters['field'] == 'nominalextra' || $filters['field'] == 'nominalretribusi' || $filters['field'] == 'total') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
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
    public function processStore(InvoiceEmklHeader $invoiceHeader, array $data): InvoiceEmklDetail
    {
        $invoiceDetail = new InvoiceEmklDetail();
        $invoiceDetail->invoiceemkl_id = $invoiceHeader->id;
        $invoiceDetail->nobukti = $invoiceHeader->nobukti;
        $invoiceDetail->nominal = $data['nominal'];
        $invoiceDetail->jobemkl_nobukti = $data['jobemkl_nobukti'];
        $invoiceDetail->container_id = $data['container_id'];
        $invoiceDetail->keterangan = $data['keterangan'];
        $invoiceDetail->coadebet = $data['coadebet'];
        $invoiceDetail->coakredit = $data['coakredit'];
        $invoiceDetail->selisih = $data['selisih'];
        $invoiceDetail->modifiedby = auth('api')->user()->name;
        $invoiceDetail->info = html_entity_decode(request()->info);

        if (!$invoiceDetail->save()) {
            throw new \Exception("Error storing invoice detail.");
        }

        return $invoiceDetail;
    }
}