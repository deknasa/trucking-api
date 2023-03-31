<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PemutihanSupirDetail extends MyModel
{
    use HasFactory;
    protected $table = 'pemutihansupirdetail';

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
                'header.nobukti',
                'header.tglbukti',
                'supir.namasupir as supir_id',
                'header.pengeluaransupir',
                'bank.namabank as bank',
                'header.penerimaan_nobukti',
                'header.coa',
                $this->table . '.nobukti as nobukti_detail',
                $this->table . '.pengeluarantrucking_nobukti',
                $this->table . '.nominal'
            ) 
            ->leftJoin(DB::raw("pemutihansupir as header with (readuncommitted)"),'header.id',$this->table . '.pemutihansupir_id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'header.supir_id', 'supir.id');

            $query->where($this->table . '.pemutihansupir_id', '=', request()->pemutihansupir_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.pengeluarantrucking_nobukti',
                $this->table . '.nominal',
                'parameter.text as statusposting'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), $this->table . '.statusposting', 'parameter.id');

            $query->where($this->table . '.pemutihansupir_id', '=', request()->pemutihansupir_id);

            $this->sort($query);
            $this->filter($query);

            $this->totalNominal = $query->sum('nominal');
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
                            if ($filters['field'] == 'statusposting') {
                                $query = $query->where('parameter.text', '=', "$filters[data]");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusposting') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
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
}
