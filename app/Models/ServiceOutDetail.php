<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ServiceOutDetail extends MyModel
{
    use HasFactory;

    protected $table = 'serviceoutdetail';

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
        $query = DB::table('serviceoutdetail')->from(DB::raw("serviceoutdetail with (readuncommitted)"))
        ->select(
            'serviceoutdetail.nobukti',
            'serviceoutdetail.keterangan',
            'serviceinheader.nobukti as servicein_nobukti',
        )
            ->leftJoin(DB::raw("serviceinheader with (readuncommitted)"), 'serviceoutdetail.servicein_nobukti', 'serviceinheader.nobukti')

            ->where('serviceout_id', '=', $id);

        $data = $query->get();
        return $data;
    }
}
