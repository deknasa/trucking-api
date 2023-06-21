<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GajiSupirDeposito extends MyModel
{
    use HasFactory;

    protected $table = 'gajisupirdeposito';

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
        $query = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
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

    public function processStore(array $data): GajiSupirDeposito
    {
        $gajiSupirDeposito = new GajiSupirDeposito();
        $gajiSupirDeposito->gajisupir_id = $data['gajisupir_id'];
        $gajiSupirDeposito->gajisupir_nobukti = $data['gajisupir_nobukti'];
        $gajiSupirDeposito->penerimaantrucking_nobukti = $data['penerimaantrucking_nobukti'];
        $gajiSupirDeposito->pengeluarantrucking_nobukti = $data['pengeluarantrucking_nobukti'];
        $gajiSupirDeposito->supir_id = $data['supir_id'];
        $gajiSupirDeposito->nominal = $data['nominal'];
        $gajiSupirDeposito->modifiedby = auth('api')->user()->user;

        if (!$gajiSupirDeposito->save()) {
            throw new \Exception('Error storing gaji supir deposito.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $gajiSupirDeposito->getTable(),
            'postingdari' => 'ENTRY GAJI SUPIR DEPOSITO',
            'idtrans' => $gajiSupirDeposito->id,
            'nobuktitrans' => $gajiSupirDeposito->id,
            'aksi' => 'ENTRY',
            'datajson' => $gajiSupirDeposito->toArray(),
        ]);

        return $gajiSupirDeposito;
    }

    public function processDestroy($id, $postingDari = ''): GajiSupirDeposito
    {
        $gajiSupirDeposito = new GajiSupirDeposito();
        $gajiSupirDeposito = $gajiSupirDeposito->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($gajiSupirDeposito->getTable()),
            'postingdari' => $postingDari,
            'idtrans' => $gajiSupirDeposito->id,
            'nobuktitrans' => $gajiSupirDeposito->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $gajiSupirDeposito->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $gajiSupirDeposito;
    }
}
