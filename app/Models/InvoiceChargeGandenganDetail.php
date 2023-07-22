<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class InvoiceChargeGandenganDetail extends MyModel
{
    use HasFactory;
    protected $table = 'invoicechargegandengandetail';

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
            $query->select(
                DB::raw("gandengan.kodegandengan as gandengan"),
                'invoicechargegandengandetail.jobtrucking',
                'invoicechargegandengandetail.tgltrip as dari',
                'invoicechargegandengandetail.tglakhir as sampai',
                'invoicechargegandengandetail.jenisorder as orderan',
                'invoicechargegandengandetail.jumlahhari',
                'invoicechargegandengandetail.nominal',
                'invoicechargegandengandetail.namagudang',
            )
            ->leftJoin(DB::raw("gandengan with (readuncommitted)"), 'invoicechargegandengandetail.gandengan_id', 'gandengan.id');
            $query->where($this->table . '.invoicechargegandengan_id', '=', request()->invoicechargegandengan_id);
        } else if (isset(request()->forExport) && request()->forExport) {
            $query->select(
                'invoicechargegandengandetail.id',
                'header.nobukti as nobukti_header',
                'header.tglbukti',
                'header.nominal as nominal_header',
                DB::raw("gandengan.kodegandengan as gandengan"),
                'invoicechargegandengandetail.jobtrucking',
                'invoicechargegandengandetail.tgltrip',
                'invoicechargegandengandetail.tglakhir',
                'invoicechargegandengandetail.jenisorder as orderan',
                'invoicechargegandengandetail.jumlahhari',
                'invoicechargegandengandetail.nominal',
                'invoicechargegandengandetail.namagudang',
                'invoicechargegandengandetail.keterangan',
            )
            ->leftJoin(DB::raw("invoicechargegandenganheader as header with (readuncommitted)"), 'header.id', 'invoicechargegandengandetail.invoicechargegandengan_id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'trado.id', 'invoicechargegandengandetail.trado_id')
            ->leftJoin(DB::raw("gandengan with (readuncommitted)"), 'invoicechargegandengandetail.gandengan_id', 'gandengan.id');
            $query->where($this->table . '.invoicechargegandengan_id', '=', request()->invoicechargegandengan_id);
        }
        else {
            $query->select(
                'invoicechargegandengandetail.nobukti',
                'invoicechargegandengandetail.jobtrucking',
                'invoicechargegandengandetail.tgltrip',
                'invoicechargegandengandetail.tglakhir',
                'invoicechargegandengandetail.jumlahhari',
                'invoicechargegandengandetail.jenisorder',
                'invoicechargegandengandetail.namagudang',
                'invoicechargegandengandetail.nominal',
                'invoicechargegandengandetail.trado_id',
                'trado.kodetrado as nopolisi',
                'invoicechargegandengandetail.gandengan_id',
                'gandengan.kodegandengan as gandengan',
                'invoicechargegandengandetail.keterangan',
            )
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'trado.id', 'invoicechargegandengandetail.trado_id')
            ->leftJoin(DB::raw("gandengan with (readuncommitted)"), 'gandengan.id', 'invoicechargegandengandetail.gandengan_id');


            $this->sort($query);
            $query->where($this->table . '.invoicechargegandengan_id', '=', request()->invoicechargegandengan_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('invoicechargegandengandetail.nominal');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function sort($query)
    {
        if($this->params['sortIndex'] == 'nopolisi'){
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        }if($this->params['sortIndex'] == 'gandengan'){
            return $query->orderBy('gandengan.kodegandengan', $this->params['sortOrder']);
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
                            if ($filters['field'] == 'nopolisi') {
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gandengan') {
                                $query = $query->where('gandengan.kodegandengan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgltrip' || $filters['field'] == 'tglakhir') {
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
                            if ($filters['field'] == 'nopolisi') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gandengan') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgltrip' || $filters['field'] == 'tglakhir') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else{
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

    public function processStore(InvoiceChargeGandenganHeader $invoiceChargeGandenganHeader, array $data): InvoiceChargeGandenganDetail
    {
        $invoiceChargeGandenganDetail = new InvoiceChargeGandenganDetail();
        $invoiceChargeGandenganDetail->invoicechargegandengan_id = $invoiceChargeGandenganHeader->id;
        $invoiceChargeGandenganDetail->nobukti = $invoiceChargeGandenganHeader->nobukti;
        $invoiceChargeGandenganDetail->jobtrucking = $data['jobtrucking_detail'];
        $invoiceChargeGandenganDetail->trado_id = $data['trado_id'];
        $invoiceChargeGandenganDetail->gandengan_id = $data['gandengan_id'];
        $invoiceChargeGandenganDetail->tgltrip = $data['tgltrip_detail'];
        $invoiceChargeGandenganDetail->tglakhir = $data['tglkembali_detail'];
        $invoiceChargeGandenganDetail->jumlahhari = $data['jumlahhari_detail'];
        $invoiceChargeGandenganDetail->nominal = $data['nominal_detail'];
        $invoiceChargeGandenganDetail->total = $data['nominal_detail'];
        $invoiceChargeGandenganDetail->jenisorder = $data['jenisorder_detail'];
        $invoiceChargeGandenganDetail->namagudang = $data['namagudang_detail'];
        $invoiceChargeGandenganDetail->keterangan = $data['keterangan_detail'];
        $invoiceChargeGandenganDetail->modifiedby = auth('api')->user()->name;
        
        if (!$invoiceChargeGandenganDetail->save()) {
            throw new \Exception("Error storing invoice charge gandengan detail.");
        }

        return $invoiceChargeGandenganDetail;
    }
}
