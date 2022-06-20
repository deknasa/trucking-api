<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpahRitasi extends MyModel
{
    use HasFactory;

    protected $table = 'upahritasi';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function upahritasiRincian() {
        return $this->hasMany(UpahRitasiRincian::class, 'upahritasi_id');
    }

    public function kota() {
        return $this->belongsTo(Kota::class, 'kota_id');
    }

    public function zona() {
        return $this->belongsTo(Zona::class, 'zona_id');
    }
}
