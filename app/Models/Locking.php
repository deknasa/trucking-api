<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Locking extends Model
{
    use HasFactory;

    protected $table = 'locks';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getEditing($table,$id)
    {
        $getLocking = DB::table('locks')->from(DB::raw("locks with (readuncommitted)"))
            ->select(
                'id',
                'table',
                'tableid',
                'editing_by',
                'editing_at',
            )
            ->where('table', '=', $table)
            ->where('tableid', '=', $id)
            ->first();

        return $getLocking;
    }
}
