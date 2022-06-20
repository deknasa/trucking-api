<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPengantar extends MyModel
{
    use HasFactory;

    protected $table = 'suratpengantar';

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'tglsp' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function suratpengantarBiaya() {
        return $this->hasMany(SuratPengantarBiayaTambahan::class, 'suratpengantar_id');
    }
}
