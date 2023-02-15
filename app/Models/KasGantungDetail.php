<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KasGantungDetail extends MyModel
{
    use HasFactory;

    protected $table = 'kasgantungdetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function findUpdate($id)
    {
        $query = DB::table('kasgantungdetail')->from(DB::raw('kasgantungdetail with (readuncommitted)'))->select(
            'keterangan',
            'nominal',
        )
            ->where('kasgantung_id', '=', $id);

        $detail = $query->get();

        return $detail;
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select([
                'header.id as id',
                'header.nobukti as nobukti_header',
                'header.tglbukti as tgl_header',
                'penerima.namapenerima as penerima_id',
                'bank.namabank as bank_id',
                'header.pengeluaran_nobukti',
                'header.coakaskeluar',
                'header.tglkaskeluar',
                $this->table . '.keterangan as keterangan_detail',
                $this->table . '.nominal',
                $this->table . '.coa',
                $this->table . '.kasgantung_id'
            ])
                ->leftjoin(DB::raw("kasgantungheader as header with (readuncommitted)"), 'header.id', $this->table . '.kasgantung_id')
                ->leftjoin(DB::raw("penerima with (readuncommitted)"), 'header.penerima_id', 'penerima.id')
                ->leftjoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id');

            $query->where($this->table . '.kasgantung_id', '=', request()->kasgantung_id);
        } else {
            $query
                ->select([
                    $this->table . '.keterangan',
                    $this->table . '.nominal',
                    $this->table . '.nobukti',
                    'akunpusat.keterangancoa as coa',
                ])
                ->leftJoin(
                    DB::raw("akunpusat with (readuncommitted)"),
                    $this->table . '.coa',
                    'akunpusat.coa'
                );

            $query->where($this->table . '.kasgantung_id', '=', request()->kasgantung_id);

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
