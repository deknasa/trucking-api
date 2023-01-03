<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PengeluaranDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluarandetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function findAll($id)
    {
        $query =  DB::table('pengeluarandetail')->from(DB::raw("pengeluarandetail with (readuncommitted)"))
        ->select(
            'pengeluarandetail.alatbayar_id',
            'alatbayar.namaalatbayar as alatbayar',
            'pengeluarandetail.nowarkat',
            'pengeluarandetail.tgljatuhtempo',
            'pengeluarandetail.keterangan',
            'pengeluarandetail.nominal',
            'pengeluarandetail.coadebet',
            'pengeluarandetail.bulanbeban'
        )
        ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluarandetail.alatbayar_id', 'alatbayar.id')
        ->where('pengeluarandetail.pengeluaran_id',$id);

        $data = $query->get();

        return $data;
    }
}
