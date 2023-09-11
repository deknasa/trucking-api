<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutupBuku extends Model
{
    use HasFactory;

    public function processStore(array $data)
    {
        $tgltutupbuku = date('Y-m-d', strtotime($data['tgltutupbuku']));
        $parameter = Parameter::where('grp', 'TUTUP BUKU')->where('subgrp', 'TUTUP BUKU')->first();
        
        $parameter->text = $tgltutupbuku;
        $parameter->modifiedby = auth('api')->user()->name;
        if (!$parameter->save()) {
            throw new \Exception("Error update tutup buku.");
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($parameter->getTable()),
            'postingdari' => 'TUTUP BUKU',
            'idtrans' => $parameter->id,
            'nobuktitrans' => $parameter->id,
            'aksi' => 'EDIT',
            'datajson' => $parameter->toArray(),
            'modifiedby' => $parameter->modifiedby
        ]);
        
        return $parameter;
    }
}
