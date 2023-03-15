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
                'invoicechargegandengandetail.id',
                'header.nobukti as nobukti_header',
                'header.tglbukti',
                'header.nominal as nominal_header',
                'invoicechargegandengandetail.jobtrucking',
                'invoicechargegandengandetail.tgltrip',
                'invoicechargegandengandetail.jumlahhari',
                'invoicechargegandengandetail.nominal',
                'invoicechargegandengandetail.trado_id',
                'trado.kodetrado as nopolisi',
                'invoicechargegandengandetail.keterangan',
            )
            ->leftJoin(DB::raw("invoicechargegandenganheader as header with (readuncommitted)"), 'header.id', 'invoicechargegandengandetail.invoicechargegandengan_id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'trado.id', 'invoicechargegandengandetail.trado_id');



            $query->where($this->table . '.invoicechargegandengan_id', '=', request()->invoicechargegandengan_id);
        } else {
            $query->select(
                'invoicechargegandengandetail.id',
                'header.nobukti as nobukti_header',
                'header.tglbukti',
                'header.nominal as nominal_header',
                'invoicechargegandengandetail.jobtrucking',
                'invoicechargegandengandetail.tgltrip',
                'invoicechargegandengandetail.jumlahhari',
                'invoicechargegandengandetail.nominal',
                'invoicechargegandengandetail.trado_id',
                'trado.kodetrado as nopolisi',
                'invoicechargegandengandetail.keterangan',
            )
            ->leftJoin(DB::raw("invoicechargegandenganheader as header with (readuncommitted)"), 'header.id', 'invoicechargegandengandetail.invoicechargegandengan_id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'trado.id', 'invoicechargegandengandetail.trado_id');


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
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

}
