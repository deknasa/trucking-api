<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PencairanGiroPengeluaranDetail extends MyModel
{
    use HasFactory;

    protected $anotherTable = 'pengeluarandetail';
    protected $table = 'pencairangiropengeluarandetail';

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

        $query = DB::table($this->anotherTable)->from(DB::raw("$this->anotherTable with (readuncommitted)"));

        $query->select(
            $this->anotherTable . '.nobukti',
            $this->anotherTable . '.nowarkat',
            $this->anotherTable . '.tgljatuhtempo', 
            $this->anotherTable . '.nominal',
            'coadebet.keterangancoa as coadebet',
            'coakredit.keterangancoa as coakredit',
            $this->anotherTable . '.keterangan',
            DB::raw("(case when (year($this->anotherTable.bulanbeban) <= 2000) then null else $this->anotherTable.bulanbeban end ) as bulanbeban"),
        )
        ->leftJoin('akunpusat as coadebet',$this->anotherTable.'.coadebet','coadebet.coa')
        ->leftJoin('akunpusat as coakredit',$this->anotherTable.'.coakredit','coakredit.coa');

        $this->sort($query);
        $query->where($this->anotherTable . '.pengeluaran_id', '=', request()->pengeluaran_id);
        $this->filter($query);

        $this->totalNominal = $query->sum('nominal');
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);

        return $query->get();
    }

    public function sort($query)
    {
        if($this->params['sortIndex'] == 'coadebet'){
            return $query->orderBy('coadebet.keterangancoa', $this->params['sortOrder']);
        } else if($this->params['sortIndex'] == 'coakredit'){
            return $query->orderBy('coakredit.keterangancoa', $this->params['sortOrder']);
        }else{
            return $query->orderBy($this->anotherTable . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coadebet') {
                                $query = $query->where('coadebet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->where('coakredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->anotherTable.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->anotherTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coadebet') {
                                $query = $query->orWhere('coadebet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->orWhere('coakredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->anotherTable.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->anotherTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
