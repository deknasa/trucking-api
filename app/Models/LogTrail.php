<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogTrail extends MyModel
{
    use HasFactory;

    protected $table = 'logtrail';

    protected $casts = [
        'datajson' => 'array'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
