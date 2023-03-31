<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvoiceDetail extends MyModel
{
    use HasFactory;

    protected $table = 'invoicedetail';

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
                'header.id as id_header',
                'header.nobukti as nobukti_header',
                'header.tglbukti',
                'header.nominal as nominal_header',
                'agen.namaagen as agen',
                'cabang.namacabang as cabang',
                $this->table . '.orderantrucking_nobukti',
                $this->table . '.nominal as nominal_detail',
                'suratpengantar.nocont',
                'suratpengantar.tglsp',
                'suratpengantar.keterangan',
                'kota.keterangan as tujuan',
                $this->table . '.invoice_id'
            )
                ->distinct($this->table . '.orderantrucking_nobukti')
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), $this->table . '.orderantrucking_nobukti', 'suratpengantar.jobtrucking')
                ->leftJoin(DB::raw("invoiceheader as header with (readuncommitted)"), 'header.id', $this->table . '.invoice_id')
                ->leftJoin(DB::raw("agen with (readuncommitted)"), 'header.agen_id', 'agen.id')
                ->leftJoin(DB::raw("cabang with (readuncommitted)"), 'header.cabang_id', 'cabang.id')
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'suratpengantar.sampai_id', 'kota.id');


            $query->where($this->table . '.invoice_id', '=', request()->invoice_id);
        } else if (isset(request()->forExport) && request()->forExport) {
            $query->select(
                'suratpengantar.tglsp',
                'agen.namaagen as agen_id',
                'kota.keterangan as tujuan',
                'suratpengantar.nocont',
                $this->table . '.nominal as omset',
                $this->table . '.keterangan as keterangan_detail'
            )

                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), $this->table . '.suratpengantar_nobukti', 'suratpengantar.nobukti')
                ->leftJoin(DB::raw("agen with (readuncommitted)"), 'suratpengantar.agen_id', 'agen.id')
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'suratpengantar.sampai_id', 'kota.id');

            $query->where($this->table . '.invoice_id', '=', request()->invoice_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.keterangan',
                $this->table . '.nominal',
                $this->table . '.nominalretribusi',
                $this->table . '.orderantrucking_nobukti',
                $this->table . '.suratpengantar_nobukti',
            );

            $this->sort($query);
            $query->where($this->table . '.invoice_id', '=', request()->invoice_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('nominal');
            $this->totalRetribusi = $query->sum('nominalretribusi');
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
