<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends MyModel
{
    use HasFactory;

    protected $table = 'cabang';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

}
