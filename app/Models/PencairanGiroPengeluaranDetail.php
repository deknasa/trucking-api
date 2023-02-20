<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PencairanGiroPengeluaranDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluarandetail';

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

        $query->select(
            $this->table . '.nobukti',
            $this->table . '.nowarkat',
            $this->table . '.tgljatuhtempo', 
            $this->table . '.nominal',
            'coadebet.keterangancoa as coadebet',
            'coakredit.keterangancoa as coakredit',
            $this->table . '.keterangan',
            DB::raw("(case when (year($this->table.bulanbeban) <= 2000) then null else $this->table.bulanbeban end ) as bulanbeban"),
        )
        ->leftJoin('akunpusat as coadebet',$this->table.'.coadebet','coadebet.coa')
        ->leftJoin('akunpusat as coakredit',$this->table.'.coakredit','coakredit.coa');

        $query->where($this->table . '.pengeluaran_id', '=', request()->pengeluaran_id);

        $this->totalNominal = $query->sum('nominal');
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

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
