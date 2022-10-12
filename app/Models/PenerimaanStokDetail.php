<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanStokDetail extends MyModel
{
    use HasFactory;

    protected $table = 'PenerimaanStokDetail';

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
        $query = DB::table('PenerimaanStokDetail');
        $query = $query->select(
            'PenerimaanStokDetail.penerimaanstokheader_id',
            'PenerimaanStokDetail.nobukti',
            'stok.namastok as stok',
            'PenerimaanStokDetail.stok_id',
            'PenerimaanStokDetail.qty',
            'PenerimaanStokDetail.harga',
            'PenerimaanStokDetail.persentasediscount',
            'PenerimaanStokDetail.nominaldiscount',
            'PenerimaanStokDetail.total',
            'PenerimaanStokDetail.keterangan',
            'PenerimaanStokDetail.vulkanisirke',
            'PenerimaanStokDetail.modifiedby',
        )
        ->leftJoin('stok','penerimaanstokdetail.stok_id','stok.id');

        $data = $query->where("penerimaanstokheader_id",$id)->get();

        return $data;
    }        
}
