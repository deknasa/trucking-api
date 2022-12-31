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

    public function getAll($id)
    {

        $query = DB::table('hutangbayardetail')->from(DB::raw("hutangbayardetail with (readuncommitted)"))
        ->select(
            'hutangbayardetail.nominal',
            'hutangbayardetail.hutang_nobukti',
            'hutangbayardetail.cicilan',
            'hutangbayardetail.tglcair',
            'hutangbayardetail.potongan',
            'hutangbayardetail.keterangan',

            'alatbayar.namaalatbayar as alatbayar',
            'alatbayar.id as alatbayar_id',
           
        )
            ->leftJoin('alatbayar', 'hutangbayardetail.alatbayar_id', 'alatbayar.id')

            ->where('hutangbayar_id', '=', $id);

        $data = $query->get();

        return $data;
    }
}
