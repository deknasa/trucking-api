<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PenerimaanGiroDetail extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaangirodetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function findAll($id)
    {
        $detail = DB::table('penerimaangirodetail')->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
        ->select(
            'penerimaangirodetail.nowarkat','penerimaangirodetail.tgljatuhtempo','penerimaangirodetail.nominal','penerimaangirodetail.coadebet','penerimaangirodetail.keterangan','penerimaangirodetail.bank_id','bank.namabank as bank', 'penerimaangirodetail.pelanggan_id','pelanggan.namapelanggan as pelanggan', 'penerimaangirodetail.invoice_nobukti', 'penerimaangirodetail.bankpelanggan_id','bankpelanggan.namabank as bankpelanggan','penerimaangirodetail.pelunasanpiutang_nobukti','penerimaangirodetail.bulanbeban','penerimaangirodetail.jenisbiaya'
        )
        ->leftJoin(DB::raw("bank with (readuncommitted)"),'penerimaangirodetail.bank_id','bank.id')
        ->leftJoin(DB::raw("pelanggan with (readuncommitted)"),'penerimaangirodetail.pelanggan_id','pelanggan.id')
        ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"),'penerimaangirodetail.bankpelanggan_id','bankpelanggan.id')
        ->where('penerimaangirodetail.penerimaangiro_id',$id)
        ->get();

        return $detail;
    }
    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                'header.nobukti',
                'header.tglbukti',
                'ph.namapelanggan as pelangganheader',
                'header.tgllunas',
                'header.diterimadari',
                $this->table . '.nowarkat',
                $this->table . '.tgljatuhtempo',
                $this->table . '.coadebet',
                $this->table . '.coakredit',
                'bank.namabank as bank_id',
                'bankpelanggan.namabank as bankpelanggan_id',
                $this->table . '.invoice_nobukti',
                $this->table . '.pelunasanpiutang_nobukti',
                $this->table . '.jenisbiaya',
                $this->table . '.bulanbeban',
                $this->table . '.keterangan',
                $this->table . '.nominal'
            ) 
            ->leftJoin(DB::raw("penerimaangiroheader as header with (readuncommitted)"),'header.id',$this->table . '.penerimaangiro_id')
            ->leftJoin(DB::raw("pelanggan as ph with (readuncommitted)"), 'header.pelanggan_id', 'ph.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), $this->table . '.bank_id', 'bank.id')
            ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), $this->table . '.bankpelanggan_id', 'bankpelanggan.id');

            $query->where($this->table . '.penerimaangiro_id', '=', request()->penerimaangiro_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.nowarkat',
                $this->table . '.tgljatuhtempo',
                'coadebet.keterangancoa as coadebet',
                'coakredit.keterangancoa as coakredit',
                'bank.namabank as bank_id',
                'bankpelanggan.namabank as bankpelanggan_id',
                $this->table . '.invoice_nobukti',
                $this->table . '.pelunasanpiutang_nobukti',
                $this->table . '.jenisbiaya',
                $this->table . '.bulanbeban',
                $this->table . '.keterangan',
                $this->table . '.nominal'
            )
            ->leftJoin(DB::raw("akunpusat as coadebet with (readuncommitted)"), $this->table . '.coadebet', 'coadebet.coa')
            ->leftJoin(DB::raw("akunpusat as coakredit with (readuncommitted)"), $this->table . '.coakredit', 'coakredit.coa')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), $this->table . '.bank_id', 'bank.id')
            ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), $this->table . '.bankpelanggan_id', 'bankpelanggan.id');

            $query->where($this->table . '.penerimaangiro_id', '=', request()->penerimaangiro_id);

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
