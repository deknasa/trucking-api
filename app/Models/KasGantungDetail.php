<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KasGantungDetail extends MyModel
{
    use HasFactory;

    protected $table = 'kasgantungdetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ]; 

    public function find($id) {
        $query = DB::table('kasgantungdetail')->select(
            'keterangan',
            'nominal',
        )
            ->where('kasgantung_id', '=', $id);

        $detail = $query->get();

        return $detail;
    }
}
