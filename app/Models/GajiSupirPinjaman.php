<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GajiSupirPinjaman extends MyModel
{
    use HasFactory;

    protected $table = 'gajisupirpinjaman';

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
        $query = GajiSupirPinjaman::from(DB::raw("gajisupirpinjaman with (readuncommitted)"))
            ->select(
                'pengeluarantrucking_nobukti'
            )
            ->where('gajisupir_nobukti', $nobukti)->first();
        if ($query != null) {

            $deposito = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                ->where('nobukti', $query->pengeluarantrucking_nobukti);

            return $deposito->first();
        }
    }
}
