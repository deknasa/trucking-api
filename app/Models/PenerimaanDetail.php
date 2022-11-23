<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        $detail = PenerimaanDetail::select('penerimaandetail.coadebet','penerimaandetail.tgljatuhtempo','penerimaandetail.nowarkat','penerimaandetail.bankpelanggan_id', 'bankpelanggan.namabank as bankpelanggan', 'penerimaandetail.keterangan', 'penerimaandetail.nominal','penerimaandetail.invoice_nobukti','penerimaandetail.jenisbiaya','penerimaandetail.pelunasanpiutang_nobukti','penerimaandetail.bulanbeban')
        ->join('bankpelanggan','penerimaandetail.bankpelanggan_id','bankpelanggan.id')
        ->where('penerimaandetail.penerimaan_id',$id)
        ->get();

        return $detail;
    }
}
