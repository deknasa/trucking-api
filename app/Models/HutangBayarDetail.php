<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class HutangBayarDetail extends MyModel
{
    use HasFactory;

    protected $table = 'hutangbayardetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function getAll($id)
    {

        $query = DB::table('hutangbayardetail')->from(DB::raw("hutangbayardetail with (readuncommitted)"))
        ->select(
            'hutangbayardetail.nominal',
            'hutangbayardetail.hutang_nobukti',
            'hutangbayardetail.cicilan',
            'hutangbayardetail.potongan',
            'hutangbayardetail.keterangan',
           
        )

            ->where('hutangbayar_id', '=', $id);

        $data = $query->get();

        return $data;
    }
    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                'header.nobukti',
                'header.tglbukti',
                'header.keterangan as keteranganheader',
                'header.pengeluaran_nobukti',
                'header.coa',
                'bank.namabank as bank',
                'supplier.namasupplier as supplier',
                'pelanggan.namapelanggan as pelanggan',
                $this->table . '.nominal',
                $this->table . '.keterangan',
                'header.tglcair',
                $this->table . '.potongan',
                $this->table . '.hutang_nobukti',
                'alatbayar.namaalatbayar as alatbayar_id',

            )
                ->leftJoin(DB::raw("hutangbayarheader as header with (readuncommitted)"), 'header.id', $this->table . '.hutangbayar_id')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
                ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'header.supplier_id', 'supplier.id')
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'header.pelanggan_id', 'pelanggan.id')
                ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'header.alatbayar_id', 'alatbayar.id');

            $query->where($this->table . '.hutangbayar_id', '=', request()->hutangbayar_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.nominal',
                $this->table . '.keterangan',
                $this->table . '.potongan',
                $this->table . '.hutang_nobukti'
            );

            $query->where($this->table . '.hutangbayar_id', '=', request()->hutangbayar_id);

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
