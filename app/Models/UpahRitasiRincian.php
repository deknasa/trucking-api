<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class UpahRitasiRincian extends MyModel
{
    use HasFactory;

    protected $table = 'upahritasirincian';

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
        $query = DB::table('upahritasirincian')->select(
            'upahritasirincian.container_id',
            'container.keterangan as container',
            'upahritasirincian.statuscontainer_id',
            'statuscontainer.keterangan as statuscontainer',
            'upahritasirincian.nominalsupir',
            'upahritasirincian.nominalkenek',
            'upahritasirincian.nominalkomisi',
            'upahritasirincian.nominaltol',
            'upahritasirincian.liter',
        )
            ->leftJoin('container', 'container.id', 'upahritasirincian.container_id')
            ->leftJoin('statuscontainer', 'statuscontainer.id', 'upahritasirincian.statuscontainer_id')
            ->where('upahritasi_id', '=', $id);


        $data = $query->get();

        return $data;
    }
}
