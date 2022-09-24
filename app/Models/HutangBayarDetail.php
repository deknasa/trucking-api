<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class HutangBayarDetail extends MyModel
{
    use HasFactory;

    protected $table = 'hutangbayardetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ]; 

     
    // public function findUpdate($id) {
    //     $detail = DB::table('hutangbayardetail')->select(
    //         'nominal',
    //         'keterangan'
    //     )->where('hutangbayar_id', $id)->get();

    //     return $detail;
    // }

    public function getAll($id)
    {
       
        $query = DB::table('hutangbayardetail')->select(
            'hutangbayardetail.nominal',
            'hutangbayardetail.hutang_nobukti',
            'hutangbayardetail.cicilan',
            'hutangbayardetail.tglcair',
            'hutangbayardetail.potongan',
            
            'alatbayar.namaalatbayar as alatbayar',
            // 'alatbayar.namaalatbayar as alatbayar',
            'alatbayar.id as alatbayar_id',

            // 'hutangheader.nobukti as supplier',
            // 'supplier.id as supplier_id',

            'hutangbayardetail.keterangan',
        )
            ->leftJoin('alatbayar', 'hutangbayardetail.alatbayar_id','alatbayar.id')
            
            ->where('hutangbayar_id', '=', $id);

        $data = $query->get();

        return $data;
    } 
}
