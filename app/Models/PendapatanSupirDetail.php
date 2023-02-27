<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PendapatanSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pendapatansupirdetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function findUpdate($id)
    {
        $query = DB::table('pendapatansupirdetail')->from(DB::raw("pendapatansupirdetail with (readuncommitted)"))
        ->select(
            'pendapatansupirdetail.supir_id',
            'supir.namasupir as supir',
            'pendapatansupirdetail.nominal',
            'pendapatansupirdetail.keterangan'
        )
        ->leftJoin(DB::raw("supir with (readuncommitted)"),'pendapatansupirdetail.supir_id','supir.id')
        ->where('pendapatansupirdetail.pendapatansupir_id', $id)
        ->get();

        return $query;
    }
    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                'header.nobukti',
                'header.tglbukti',
                'bank.namabank as bank',
                'header.tgldari',
                'header.tglsampai',
                'header.periode',
                'supir.namasupir as supir_id',
                $this->table . '.keterangan',
                $this->table . '.nominal'
            ) 
            ->leftJoin(DB::raw("pendapatansupirheader as header with (readuncommitted)"),'header.id',$this->table . '.pendapatansupir_id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), $this->table . '.supir_id', 'supir.id');

            $query->where($this->table . '.pendapatansupir_id', '=', request()->pendapatansupir_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                'supir.namasupir as supir_id',
                $this->table . '.keterangan',
                $this->table . '.nominal'
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), $this->table . '.supir_id', 'supir.id');

            $query->where($this->table . '.pendapatansupir_id', '=', request()->pendapatansupir_id);

            $this->totalNominal = $query->sum('nominal');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }

        return $query->get();
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
