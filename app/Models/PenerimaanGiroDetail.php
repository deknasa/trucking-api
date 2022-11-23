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
        $detail = DB::table('penerimaangirodetail')->select(
            'penerimaangirodetail.nowarkat','penerimaangirodetail.tgljatuhtempo','penerimaangirodetail.nominal','penerimaangirodetail.coadebet','penerimaangirodetail.keterangan','penerimaangirodetail.bank_id','bank.namabank as bank', 'penerimaangirodetail.pelanggan_id','pelanggan.namapelanggan as pelanggan', 'penerimaangirodetail.invoice_nobukti', 'penerimaangirodetail.bankpelanggan_id','bankpelanggan.namabank as bankpelanggan','penerimaangirodetail.pelunasanpiutang_nobukti','penerimaangirodetail.bulanbeban','penerimaangirodetail.jenisbiaya'
        )
        ->leftJoin('bank','penerimaangirodetail.bank_id','bank.id')
        ->leftJoin('pelanggan','penerimaangirodetail.pelanggan_id','pelanggan.id')
        ->leftJoin('bankpelanggan','penerimaangirodetail.bankpelanggan_id','bankpelanggan.id')
        ->where('penerimaangirodetail.penerimaangiro_id',$id)
        ->get();

        return $detail;
    }
}
