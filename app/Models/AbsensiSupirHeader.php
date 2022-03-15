<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'absensisupirheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    
    protected $casts = [
        'tgl' => 'date:d-m-Y',
    ];

    public function absensiSupirDetail() {
        return $this->hasMany(AbsensiSupirDetail::class, 'absensi_id');
    }
}
