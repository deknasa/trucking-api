<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProsesGajiSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'prosesgajisupirdetail';

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
                'supir.namasupir as supir_id',
                'trado.keterangan as trado_id',
                $this->table . '.gajisupir_nobukti',
                $this->table . '.nominal',
                $this->table . '.keterangan as keterangan_detail'
            )
            ->leftJoin(DB::raw("prosesgajisupirheader as header with (readuncommitted)"),'header.id',$this->table . '.prosesgajisupir_id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"),$this->table . '.supir_id','supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"),$this->table . '.trado_id','trado.id');

            $query->where($this->table . '.prosesgajisupir_id', '=', request()->prosesgajisupir_id);
        } else {
            $query->select(
                $this->table . '.gajisupir_nobukti',
                'supir.namasupir as supir_id',
                'trado.keterangan as trado_id',
                $this->table . '.keterangan',
                $this->table . '.nominal',
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"),$this->table . '.supir_id','supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"),$this->table . '.trado_id','trado.id');
            $query->where($this->table . '.prosesgajisupir_id', '=', request()->prosesgajisupir_id);

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

