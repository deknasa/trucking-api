<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalOpname extends MyModel
{
    use HasFactory;

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(array $data)
    {
        $statusopname = $data['statusopname'];
        $parameter = Parameter::where('grp', 'OPNAME STOK')->where('subgrp', 'OPNAME STOK')->first();
        
        $parameter->text = $statusopname;
        $parameter->modifiedby = auth('api')->user()->name;
        $parameter->info = html_entity_decode(request()->info);
        if (!$parameter->save()) {
            throw new \Exception("Error update stok opname.");
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($parameter->getTable()),
            'postingdari' => 'APPROVAL STOK OPNAME',
            'idtrans' => $parameter->id,
            'nobuktitrans' => $parameter->id,
            'aksi' => 'UN/APPROVAL',
            'datajson' => $parameter->toArray(),
            'modifiedby' => $parameter->modifiedby
        ]);

        return $parameter;
    }
}
