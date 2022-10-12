<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class UpahSupirRincian extends MyModel
{
    use HasFactory;

    protected $table = 'upahsupirrincian';

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
        $query = DB::table('upahsupirrincian')->select(
            'upahsupirrincian.container_id',
            'upahsupirrincian.statuscontainer_id',
            'upahsupirrincian.nominalsupir',
            'upahsupirrincian.nominalkenek',
            'upahsupirrincian.nominalkomisi',
            'upahsupirrincian.nominaltol',
            'upahsupirrincian.liter',
        )
            ->leftJoin('container', 'container.id', 'upahsupirrincian.container_id')
            ->leftJoin('statuscontainer', 'statuscontainer.id', 'upahsupirrincian.statuscontainer_id')
            ->where('upahsupir_id', '=', $id);


        $data = $query->get();

        return $data;
    }
}
