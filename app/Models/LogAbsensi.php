<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAbsensi extends MyModel
{
    use HasFactory;
    protected $table = 'logabsensi';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
