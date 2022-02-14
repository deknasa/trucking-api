<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

