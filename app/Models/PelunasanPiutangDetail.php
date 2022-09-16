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
            'pelunasanpiutangdetail.id',
            'pelunasanpiutangdetail.keterangan',
            'pelunasanpiutangdetail.piutang_nobukti',

            'pelunasanpiutangdetail.pelanggan_id',
            'pelunasanpiutangdetail.agen_id',
            
            'pelanggan.namapelanggan as pelanggan',
            'agen.namaagen as agen'
        )
            ->leftJoin('pelanggan', 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
            ->leftJoin('agen', 'pelunasanpiutangdetail.agen_id', 'agen.id')
            ->where('pelunasanpiutangdetail.pelunasanpiutang_id', '=', $id);

        $data = $query->get();

        return $data;
    }
}
