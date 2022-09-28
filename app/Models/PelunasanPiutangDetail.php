<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PelunasanPiutangDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pelunasanpiutangdetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function findAll($id) {
      
        $query = DB::table('pelunasanpiutangdetail')->select(
            

            'pelunasanpiutangdetail.pelanggan_id as pelanggan_id',
            'pelunasanpiutangdetail.agen_id as agendetail_id',
            
            'pelanggan.namapelanggan as pelanggan',
            'agen.namaagen as agendetail'
        )
            ->leftJoin('pelanggan', 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
            ->leftJoin('agen', 'pelunasanpiutangdetail.agen_id', 'agen.id')
            ->where('pelunasanpiutangdetail.pelunasanpiutang_id', '=', $id);

        $data = $query->first();

        return $data;
    }

    

    
}
