<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PiutangDetail extends MyModel
{
    use HasFactory;

    protected $table = 'piutangdetail';

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
                'header.id as id_header',
                'header.nobukti as nobukti_header',
                'header.tglbukti as tgl_header',
                'header.keterangan as keterangan_header',
                'header.invoice_nobukti as invoice_nobukti',
                'agen.namaagen as agen_id',
                 $this->table . '.keterangan as keterangan_detail',
                 $this->table . '.nominal',
                 $this->table . '.invoice_nobukti as invoice_nobukti_detail'
            )
                ->leftJoin('piutangheader as header', 'header.id',  $this->table . '.piutang_id')
                ->leftJoin('agen', 'header.agen_id','agen.id');

            $query->where($this->table . '.piutang_id', '=', request()->piutang_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.keterangan',
                $this->table . '.invoice_nobukti',
                $this->table . '.nominal');

            $query->where($this->table . '.piutang_id', '=', request()->piutang_id);

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
