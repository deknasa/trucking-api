<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Parameter extends MyModel
{
    use HasFactory;

    protected $table = 'parameter';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}

