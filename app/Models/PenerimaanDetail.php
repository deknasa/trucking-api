<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanDetail extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaandetail';

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
                "header.nobukti",
                "header.tglbukti",
                "header.tgllunas",
                "bank.namabank as bank",
                "$this->table.nowarkat",
                "$this->table.tgljatuhtempo",
                "$this->table.nominal",
                "$this->table.keterangan as keterangan_detail",
                "bd.namabank as bank_detail",
                "$this->table.invoice_nobukti",
                "bpd.namabank as bankpelanggan_detail",
                DB::raw("(case when year(isnull($this->table.bulanbeban,'1900/1/1'))=1900 then null else $this->table.bulanbeban end) as bulanbeban"),
                "$this->table.coakredit",
                "$this->table.coadebet",

            )
                ->leftJoin(DB::raw("penerimaanheader as header with (readuncommitted)"), "header.id", "$this->table.penerimaan_id")
                ->leftJoin(DB::raw("bank with (readuncommitted)"), "bank.id", "header.bank_id")
                ->leftJoin(DB::raw("bank as bd with (readuncommitted)"), "bd.id", "=", "$this->table.bank_id")
                ->leftJoin(DB::raw("bankpelanggan as bpd with (readuncommitted)"), "bpd.id", "=", "$this->table.bankpelanggan_id");
                $query->where($this->table . ".penerimaan_id", "=", request()->penerimaan_id);

            $penerimaanDetail = $query->get();
        }else {
            $query->select(
                "$this->table.nobukti",
                "$this->table.nowarkat",
                "$this->table.tgljatuhtempo",
                "$this->table.nominal",
                "$this->table.keterangan",
                "bank.namabank as bank_id",
                "$this->table.invoice_nobukti",
                "bankpelanggan.namabank as bankpelanggan_id", ///

                "$this->table.pelunasanpiutang_nobukti",
                DB::raw("(case when year(isnull($this->table.bulanbeban,'1900/1/1'))=1900 then null else $this->table.bulanbeban end) as bulanbeban"),
                "a.keterangancoa as coadebet",
                "b.keterangancoa as coakredit",

            )
                ->leftJoin(DB::raw("bank with (readuncommitted)"), "bank.id", "=", "$this->table.bank_id")
                ->leftJoin(DB::raw("akunpusat as a with (readuncommitted)"), "a.coa", "=", "$this->table.coadebet")
                ->leftJoin(DB::raw("akunpusat as b with (readuncommitted)"), "b.coa", "=", "$this->table.coakredit")
                ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), "bankpelanggan.id", "=", "$this->table.bankpelanggan_id");
                $query->where($this->table . ".penerimaan_id", "=", request()->penerimaan_id);

                $this->totalRows = $query->count();
                $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
    
                $this->sort($query);
                $this->paginate($query);

        }
        return $query->get();

    }

    public function findAll($id)
    {
        $detail = DB::table("penerimaandetail")
            ->select(
                'penerimaandetail.coadebet',
                'penerimaandetail.tgljatuhtempo',
                'penerimaandetail.nowarkat',
                'penerimaandetail.bankpelanggan_id',
                'bankpelanggan.namabank as bankpelanggan',
                'penerimaandetail.keterangan',
                'penerimaandetail.nominal',
                'penerimaandetail.invoice_nobukti',
                'penerimaandetail.pelunasanpiutang_nobukti',
                // DB::raw("penerimaandetail.bulanbeban as bulanbeban"),
                DB::raw("(case when year(cast(penerimaandetail.bulanbeban as datetime))='1900' then '' else format(penerimaandetail.bulanbeban,'yyyy-MM-dd') end) as bulanbeban"),
            )
            ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), 'penerimaandetail.bankpelanggan_id', 'bankpelanggan.id')
            ->where('penerimaandetail.penerimaan_id', $id)
            ->get();

        //  dd($detail);

        return $detail;
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
