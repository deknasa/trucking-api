<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSupirDetail extends Model
{
    use HasFactory;

    protected $table = 'absensisupirdetail';

    protected $fillable = [
        "absensi_id",
        "nobukti",
        "trado_id",
        "supir_id",
        "jam",
        "keterangan",
    ];

    public function absensiSupirHeader()
    {
        return $this->belongsToMany(AbsensiSupirHeader::class);
    }
}
