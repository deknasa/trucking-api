<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PengeluaranTruckingDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluarantruckingdetail';

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
                'header.nobukti',
                'header.tglbukti',
                'header.coa',
                'header.pengeluaran_nobukti',
                'bank.namabank as bank',
                'pengeluarantrucking.keterangan as pengeluarantrucking',
                'supir.namasupir as supir_id',
                $this->table . '.penerimaantruckingheader_nobukti',
                $this->table . '.nominal'
            ) 
            ->leftJoin(DB::raw("pengeluarantruckingheader as header with (readuncommitted)"),'header.id',$this->table . '.pengeluarantruckingheader_id')
            ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'header.pengeluarantrucking_id','pengeluarantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), $this->table . '.supir_id', 'supir.id');

            $query->where($this->table . '.pengeluarantruckingheader_id', '=', request()->pengeluarantruckingheader_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.nominal',
    
                'supir.namasupir as supir_id',
                $this->table . '.penerimaantruckingheader_nobukti',
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), $this->table . '.supir_id', 'supir.id');

            $query->where($this->table . '.pengeluarantruckingheader_id', '=', request()->pengeluarantruckingheader_id);

            $this->sort($query);
            $this->filter($query);

            $this->totalNominal = $query->sum('nominal');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->paginate($query);
            
        }

        return $query->get();
    }
    
    public function getAll($id)
    {
       

        $query = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
        ->select(
            'pengeluarantruckingdetail.pengeluarantruckingheader_id',
            'pengeluarantruckingdetail.nominal',
            'pengeluarantruckingdetail.penerimaantruckingheader_nobukti',

            'supir.namasupir as supir',
            'supir.id as supir_id'
        )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id','supir.id')
            ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);
            

        $data = $query->get();

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
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'supir_id') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'supir_id') {
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
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
