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
}
