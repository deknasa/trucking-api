<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderanTrucking extends MyModel
{
    use HasFactory;

    protected $table = 'orderantrucking';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
