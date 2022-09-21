<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class HutangDetail extends MyModel
{
    use HasFactory;

    protected $table = 'hutangdetail';

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
       

        $query = DB::table('hutangdetail')->select(
            'hutangdetail.total',
            'hutangdetail.cicilan',
            'hutangdetail.totalbayar',            
            'hutangdetail.tgljatuhtempo',
            
            'supplier.namasupplier as supplier',
            'supplier.id as supplier_id',
            //'supplier.namasupplier as supplier',
            //'supplier.id as supplier_id',
            'hutangdetail.keterangan',
        )
            ->leftJoin('supplier', 'hutangdetail.supplier_id','supplier.id')
            ->where('hutang_id', '=', $id);
            

        $data = $query->get();

        return $data;
    } 
}
