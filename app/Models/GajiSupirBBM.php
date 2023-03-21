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
}
