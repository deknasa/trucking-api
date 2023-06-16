<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogTrail extends MyModel
{
    use HasFactory;

    protected $table = 'logtrail';
    protected $toUppercase = false;
    protected $casts = [
        'datajson' => 'array'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(array $data): LogTrail
    {
        $logTrail = new LogTrail();
        //dd($data['namatabel']);
        $logTrail->namatabel = strtoupper($data['namatabel']);
        $logTrail->postingdari = $data['postingdari'];
        $logTrail->idtrans = $data['idtrans'];
        $logTrail->nobuktitrans = $data['nobuktitrans'];
        $logTrail->aksi = $data['aksi'];
        $logTrail->datajson = $data['datajson'];
        $logTrail->modifiedby = auth('api')->user()->user;

        if (!$logTrail->save()) {
            throw new \Exception("Error storing log trail.");
        }
        
        return $logTrail;
    }
}
