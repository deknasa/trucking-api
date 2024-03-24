<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryTglJatuhTempoGiro extends Model
{
    use HasFactory;
    
    protected $table = 'historytgljatuhtempogiro';

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
        $history = new HistoryTglJatuhTempoGiro();
        $history->nobukti = $data['nobukti'];
        $history->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $history->tgljatuhtempo = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $history->tgljatuhtempolama = date('Y-m-d', strtotime($data['tgljatuhtempolama']));
        $history->info = html_entity_decode(request()->info);
        $history->modifiedby = auth('api')->user()->user;
        if (!$history->save()) {
            throw new \Exception('Error storing history tgl jatuh tempo giro.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $history->getTable(),
            'postingdari' => 'ENTRY HISTORY TGL JATUH TEMPO GIRO',
            'idtrans' => $history->id,
            'nobuktitrans' => $history->id,
            'aksi' => 'ENTRY',
            'datajson' => $history->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $history;
    }
}
