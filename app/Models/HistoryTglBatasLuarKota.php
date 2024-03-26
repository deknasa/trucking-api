<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryTglBatasLuarKota extends Model
{
    use HasFactory;
    
    protected $table = 'historytglbatasluarkota';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(array $data)
    {
        $history = new HistoryTglBatasLuarKota();
        $history->supir_id = $data['supir_id'];
        $history->tglbataslama = date('Y-m-d', strtotime($data['tglbataslama']));
        $history->tglbatasbaru = date('Y-m-d', strtotime($data['tglbatasbaru']));
        $history->statusluarkota = $data['statusluarkota'];
        $history->info = html_entity_decode(request()->info);
        $history->modifiedby = auth('api')->user()->user;
        if (!$history->save()) {
            throw new \Exception('Error storing history tgl batas luar kota.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $history->getTable(),
            'postingdari' => 'ENTRY HISTORY TGL BATAS LUAR KOTA',
            'idtrans' => $history->id,
            'nobuktitrans' => $history->id,
            'aksi' => 'ENTRY',
            'datajson' => $history->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $history;
    }
}
