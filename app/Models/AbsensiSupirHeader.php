<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSupirHeader extends Model
{
    use HasFactory;

    protected $table = 'absensisupirheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function absensiSupirDetail() {
        return $this->hasMany(AbsensiSupirDetail::class, 'absensi_id');
    }
}
