<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PiutangDetail extends MyModel
{
    use HasFactory;

    protected $table = 'piutangdetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    
    public function findUpdate($id) {
        $detail = DB::table('piutangdetail')->select(
            'nominal',
            'keterangan'
        )->where('piutang_id', $id)->get();

        return $detail;
    }
}
