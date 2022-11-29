<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PengembalianKasBankDetail extends MyModel
{
    use HasFactory;

    protected $table = 'PengembalianKasBankDetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getAll($id)
    {
        $query = DB::table('pengembaliankasbankdetail');
        $query = $query->select(
            'pengembaliankasbankheader.nobukti',
                    'pengembaliankasbankheader.tglbukti',
                    'pengembaliankasbankheader.dibayarke',
                    'pengembaliankasbankheader.keterangan as keteranganheader',
                    'pengembaliankasbankheader.transferkeac',
                    'pengembaliankasbankheader.transferkean',
                    'pengembaliankasbankheader.transferkebank',
                    'bank.namabank as bank',
                    'pengembaliankasbankdetail.nowarkat',
                    'pengembaliankasbankdetail.tgljatuhtempo',
                    'pengembaliankasbankdetail.nominal',
                    'pengembaliankasbankdetail.keterangan',
                    'pengembaliankasbankdetail.bulanbeban',
                    'pengembaliankasbankdetail.coadebet',
                    'pengembaliankasbankdetail.coakredit',
                    'alatbayar.namaalatbayar as alatbayar',
                    'pengembaliankasbankdetail.alatbayar_id'
        )
        ->leftJoin('pengembaliankasbankheader', 'pengembaliankasbankdetail.pengembaliankasbank_id', 'pengembaliankasbankheader.id')
        ->leftJoin('bank', 'bank.id', '=', 'pengembaliankasbankheader.bank_id')
        ->leftJoin('alatbayar', 'alatbayar.id', '=', 'pengembaliankasbankdetail.alatbayar_id');

        $data = $query->where("pengembaliankasbank_id",$id)->get();

        return $data;
    }
}
