<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeAkuntansi extends MyModel
{
    use HasFactory;

    protected $table = 'typeakuntansi';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
