<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSupirDetail extends Model
{
    use HasFactory;

    protected $table = 'absensisupirdetail';

    protected $fillable = [
        "absen_id",
        "nobukti",
        "trado_id",
        "supir_id",
        "jam",
        "uangjalan",
        "keterangan",
        "modifiedby",
    ];

    public function absensiSupirHeader()
    {
        return $this->belongsToMany(AbsensiSupirHeader::class);
    }

    public function trado() {
        return $this->belongsTo(Trado::class, 'trado_id');
    }

    public function supir() {
        return $this->belongsTo(Supir::class, 'supir_id');
    }

    public function absenTrado() {
        return $this->belongsTo(AbsenTrado::class, 'absen_id');
    }
}
