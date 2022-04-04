<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkunPusat extends MyModel
{
    use HasFactory;

    protected $table = 'akunpusat';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
