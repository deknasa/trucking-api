<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trado extends Model
{
    use HasFactory;

    protected $table = 'trado';

    public function absensiSupir() {
        return $this->belongsToMany(AbsensiSupirDetail::class);
    }
}
