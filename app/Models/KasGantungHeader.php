<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasGantungHeader extends MyModel
{
    use HasFactory;

    protected $table = 'kasgantungheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];     

    public function kasgantungDetail() {
        return $this->hasMany(KasGantungDetail::class, 'kasgantung_id');
    }

    public function bank() {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function penerima() {
        return $this->belongsTo(Penerima::class, 'penerima_id');
    }
}
