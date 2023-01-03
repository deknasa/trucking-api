<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PenerimaanTruckingDetail extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaantruckingdetail';

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
       

        $query = DB::table('penerimaantruckingdetail')->from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
        ->select(
            'penerimaantruckingdetail.penerimaantruckingheader_id',
            'penerimaantruckingdetail.nominal',
            'penerimaantruckingdetail.pengeluarantruckingheader_nobukti',

            'supir.namasupir as supir',
            'supir.id as supir_id'
        )
            ->leftJoin('supir', 'penerimaantruckingdetail.supir_id','supir.id')
            ->where('penerimaantruckingdetail.penerimaantruckingheader_id', '=', $id);
            

        $data = $query->get();

        return $data;
    }        
}
