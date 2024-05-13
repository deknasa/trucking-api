<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GajisUpirUangJalan extends MyModel
{
    use HasFactory;
    protected $table = 'gajisupiruangjalan';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function processStore(array $data): GajisUpirUangJalan
    {
        $gajiSupirUangJalan = new GajisUpirUangJalan();
        $gajiSupirUangJalan->gajisupir_id = $data['gajisupir_id'];
        $gajiSupirUangJalan->gajisupir_nobukti = $data['gajisupir_nobukti'];
        $gajiSupirUangJalan->absensisupir_nobukti = $data['absensisupir_nobukti'];
        $gajiSupirUangJalan->supir_id = $data['supir_id'];
        $gajiSupirUangJalan->trado_id = $data['trado_id'];
        $gajiSupirUangJalan->nominal = $data['nominal'];
        $gajiSupirUangJalan->statusjeniskendaraan = $data['statusjeniskendaraan'];
        $gajiSupirUangJalan->kasgantung_nobukti = $data['kasgantung_nobukti'];

        if (!$gajiSupirUangJalan->save()) {
            throw new \Exception('Error storing gaji supir uang jalan.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $gajiSupirUangJalan->getTable(),
            'postingdari' => 'ENTRY GAJI SUPIR UANG JALAN',
            'idtrans' => $gajiSupirUangJalan->id,
            'nobuktitrans' => $gajiSupirUangJalan->id,
            'aksi' => 'ENTRY',
            'datajson' => $gajiSupirUangJalan->toArray(),
        ]);

        return $gajiSupirUangJalan;
    }
    
    public function processDestroy($id, $postingDari = ''): GajisUpirUangJalan
    {
        $gajiSupirUangJalan = new GajisUpirUangJalan();
        $gajiSupirUangJalan = $gajiSupirUangJalan->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($gajiSupirUangJalan->getTable()),
            'postingdari' => $postingDari,
            'idtrans' => $gajiSupirUangJalan->id,
            'nobuktitrans' => $gajiSupirUangJalan->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $gajiSupirUangJalan->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $gajiSupirUangJalan;
    }
}
