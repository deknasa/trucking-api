<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanTripTrado extends MyModel
{
    use HasFactory;

    protected $table = '';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];



    public function getReport($sampai, $dari)
    {
        $sampai = date("Y-m-d", strtotime($sampai));
        // data coba coba
        $query = DB::table('penerimaantruckingdetail')->from(
            DB::raw("penerimaantruckingdetail with (readuncommitted)")
        )->select(
            'penerimaantruckingdetail.id',
            'supir.namasupir',
            'penerimaantruckingdetail.nominal',
        )
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'penerimaantruckingdetail.supir_id', 'supir.id')
        ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.penerimaantruckingheader_id', 'penerimaantruckingheader.id')
        ->where('penerimaantruckingheader.tglbukti','<=',$sampai);

        $data = $query->get();
        return $data;
    }
}
