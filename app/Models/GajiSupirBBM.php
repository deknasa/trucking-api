<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GajiSupirBBM extends MyModel
{
    use HasFactory;

    protected $table = 'gajisupirbbm';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function findAll($nobukti)
    {
        $query = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
            ->select(
                'penerimaantrucking_nobukti'
            )
            ->where('gajisupir_nobukti', $nobukti)->first();
        if ($query != null) {

            $deposito = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                ->where('nobukti', $query->penerimaantrucking_nobukti);

            return $deposito->first();
        }
    }

    public function processStore(array $data): GajiSupirBBM
    {
        $gajiSupirBBM = new GajiSupirBBM();
        $gajiSupirBBM->gajisupir_id = $data['gajisupir_id'];
        $gajiSupirBBM->gajisupir_nobukti = $data['gajisupir_nobukti'];
        $gajiSupirBBM->penerimaantrucking_nobukti = $data['penerimaantrucking_nobukti'];
        $gajiSupirBBM->pengeluarantrucking_nobukti = $data['pengeluarantrucking_nobukti'];
        $gajiSupirBBM->supir_id = $data['supir_id'];
        $gajiSupirBBM->nominal = $data['nominal'];
        $gajiSupirBBM->modifiedby = auth('api')->user()->user;

        if (!$gajiSupirBBM->save()) {
            throw new \Exception('Error storing gaji supir bbm.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $gajiSupirBBM->getTable(),
            'postingdari' => 'ENTRY GAJI SUPIR BBM',
            'idtrans' => $gajiSupirBBM->id,
            'nobuktitrans' => $gajiSupirBBM->id,
            'aksi' => 'ENTRY',
            'datajson' => $gajiSupirBBM->toArray(),
        ]);

        return $gajiSupirBBM;
    }

    
    public function processDestroy($id, $postingDari = ''): GajiSupirBBM
    {
        $gajiSupirBBM = new GajiSupirBBM();
        $gajiSupirBBM = $gajiSupirBBM->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($gajiSupirBBM->getTable()),
            'postingdari' => $postingDari,
            'idtrans' => $gajiSupirBBM->id,
            'nobuktitrans' => $gajiSupirBBM->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $gajiSupirBBM->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $gajiSupirBBM;
    }
}
