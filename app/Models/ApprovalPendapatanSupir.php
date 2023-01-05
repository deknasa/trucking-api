<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class ApprovalPendapatanSupir extends MyModel
{
    use HasFactory;

    protected $table = 'pendapatansupirheader';
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
        $periode = request()->periode ?? date('m-Y');
        $approve = request()->approve ?? 0;
        $approval = 0;

        if($approve == 3) {
            $approval = 4;
        }
        if($approve == 4){
            $approval = 3;
        }
        $month = substr($periode,0,2);
        $year = substr($periode,3);

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'pendapatansupirheader.id',
                'pendapatansupirheader.nobukti',
                'pendapatansupirheader.tglbukti',
                'bank.namabank as bank_id',
                'pendapatansupirheader.keterangan',
                'pendapatansupirheader.tgldari',
                'pendapatansupirheader.tglsampai',
                'parameter.memo as statusapproval',
                'pendapatansupirheader.userapproval',
                DB::raw('(case when (year(pendapatansupirheader.tglapproval) <= 2000) then null else pendapatansupirheader.tglapproval end ) as tglapproval'),
                'pendapatansupirheader.periode',
                'pendapatansupirheader.modifiedby',
                'pendapatansupirheader.created_at',
                'pendapatansupirheader.updated_at'
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pendapatansupirheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pendapatansupirheader.statusapproval', 'parameter.id')
            ->whereRaw("pendapatansupirheader.statusapproval = $approval")
            ->whereRaw("MONTH(pendapatansupirheader.tglbukti) = $month")
            ->whereRaw("YEAR(pendapatansupirheader.tglbukti) = $year");
        

        
        $this->totalRows = $query->count();
        
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


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
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else{
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

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
