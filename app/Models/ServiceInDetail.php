<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ServiceInDetail extends MyModel
{
    use HasFactory;

    protected $table = 'serviceindetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    function getAll($id)
    {
        $query = DB::table('serviceindetail')->select(
            // 'serviceindetail.nobukti',
            'mekanik.id as mekanik_id',
            'mekanik.namamekanik as mekanik',

            'serviceindetail.keterangan',

        )
            ->leftJoin('mekanik', 'serviceindetail.mekanik_id', 'mekanik.id')
            ->where('servicein_id', '=', $id);

        $data = $query->get();

        return $data;
    }
}
