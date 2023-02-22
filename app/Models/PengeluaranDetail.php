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
            'pengeluarandetail.nowarkat',
            'pengeluarandetail.tgljatuhtempo',
            'pengeluarandetail.keterangan',
            'pengeluarandetail.nominal',
            'pengeluarandetail.coadebet',
            DB::raw("(case when year(cast(pengeluarandetail.bulanbeban as datetime))='1900' then '' else format(pengeluarandetail.bulanbeban,'yyyy-MM-dd') end) as bulanbeban"),
        )
        ->where('pengeluarandetail.pengeluaran_id',$id);

        $data = $query->get();

        return $data;
    }
}
