<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class AbsensiSupirApprovalDetail extends MyModel
{
    use HasFactory;

    protected $table = 'absensisupirapprovaldetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function getAll($id)
    {
        $query = DB::table('absensisupirapprovaldetail')->from(DB::raw("absensisupirapprovaldetail as a with (readuncommitted)"))
            ->select(
                'a.supir_id',
                'a.trado_id',
            )
            ->where('a.absensisupirapproval_id', '=', $id);
        $data = $query->get();


        return $data;
    }
}
