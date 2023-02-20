<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PelunasanPiutangDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pelunasanpiutangdetail';

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
                'header.keterangan as keterangan_header',
                'bank.namabank as bank',
                'agen.namaagen as agen',
                $this->table . '.nominal',
                $this->table . '.keterangan as keterangan_detail',
                $this->table . '.nominal',
                $this->table . '.piutang_nobukti',
                $this->table . '.tglcair',
                $this->table . '.tgljt',
            )
                ->leftJoin(DB::raw("pelunasanpiutangheader as header with (readuncommitted)"), 'header.id', $this->table . '.pelunasanpiutang_id')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
                ->leftJoin(DB::raw("agen with (readuncommitted)"), 'header.agen_id', 'agen.id');


            $query->where($this->table . '.pelunasanpiutang_id', '=', request()->pelunasanpiutang_id);
        } else {
            $query->select(
                $this->table .'.nobukti',
                $this->table .'.nominal',
                $this->table .'.keterangan',
                $this->table .'.piutang_nobukti',
                $this->table .'.nominallebihbayar',
                $this->table .'.potongan',
                $this->table .'.keteranganpotongan',
                'akunpusat.keterangancoa as coapotongan',
                $this->table .'.invoice_nobukti'
            )
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), $this->table .'.coapotongan', 'akunpusat.coa');

            $query->where($this->table . '.pelunasanpiutang_id', '=', request()->pelunasanpiutang_id);

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
