<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKasHarian extends Model
{
    use HasFactory;

    public function getReport($periode){
        dd($periode);
    }
}


