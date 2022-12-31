<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PendapatanSupirDetail extends Model
{
    use HasFactory;

    protected $table = 'pendapatansupirdetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function findUpdate($id)
    {
        $query = DB::table('pendapatansupirdetail')->from(DB::raw("pendapatansupirdetail with (readuncommitted)"))
        ->select(
            'pendapatansupirdetail.supir_id',
            'supir.namasupir as supir',
            'pendapatansupirdetail.nominal',
            'pendapatansupirdetail.keterangan'
        )
        ->leftJoin(DB::raw("supir with (readuncommitted)"),'pendapatansupirdetail.supir_id','supir.id')
        ->where('pendapatansupirdetail.pendapatansupir_id', $id)
        ->get();

        return $query;
    }
}
