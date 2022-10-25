<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengembalianKasGantungDetail extends MyModel
{
    use HasFactory;
    protected $table = 'pengembaliankasgantungdetail';

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
        $query = DB::table('pengembaliankasgantungdetail');
        $query = $query->select(
            'pengembaliankasgantungdetail.pengembaliankasgantung_id',
            'pengembaliankasgantungdetail.nobukti',
            'pengembaliankasgantungdetail.nominal',
            'pengembaliankasgantungdetail.coa',
        )
        ->leftJoin('pengembaliankasgantungheader', 'pengembaliankasgantungdetail.pengembaliankasgantung_id', 'pengembaliankasgantungheader.id');

        $data = $query->where("pengembaliankasgantung_id",$id)->get();

        return $data;
    }
}
