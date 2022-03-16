<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agen extends MyModel
{
    use HasFactory;

    protected $table = 'agen';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
