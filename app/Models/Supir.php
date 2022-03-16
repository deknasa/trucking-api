<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supir extends MyModel
{
    use HasFactory;

    protected $table = 'supir';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
