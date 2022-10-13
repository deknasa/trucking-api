<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengeluaranStokDetail extends MyModel
{
    use HasFactory;

    protected $table = 'PengeluaranStokDetail';

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
        $query = DB::table('PengeluaranStokDetail');
        $query = $query->select(
            'PengeluaranStokDetail.Pengeluaranstokheader_id',
            'PengeluaranStokDetail.nobukti',
            'stok.namastok as stok',
            'PengeluaranStokDetail.stok_id',
            'PengeluaranStokDetail.qty',
            'PengeluaranStokDetail.harga',
            'PengeluaranStokDetail.persentasediscount',
            'PengeluaranStokDetail.nominaldiscount',
            'PengeluaranStokDetail.total',
            'PengeluaranStokDetail.keterangan',
            'PengeluaranStokDetail.vulkanisirke',
            'PengeluaranStokDetail.modifiedby',
        )
        ->leftJoin('stok','PengeluaranStokDetail.stok_id','stok.id');

        $data = $query->where("Pengeluaranstokheader_id",$id)->get();

        return $data;
    }

}
