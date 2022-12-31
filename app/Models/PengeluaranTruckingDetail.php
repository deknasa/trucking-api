<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PengeluaranTruckingDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluarantruckingdetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];  
    public function getAll($id)
    {
       

        $query = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
        ->select(
            'pengeluarantruckingdetail.pengeluarantruckingheader_id',
            'pengeluarantruckingdetail.nominal',
            'pengeluarantruckingdetail.penerimaantruckingheader_nobukti',

            'supir.namasupir as supir',
            'supir.id as supir_id'
        )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id','supir.id')
            ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);
            

        $data = $query->get();

        return $data;
    }    
}
