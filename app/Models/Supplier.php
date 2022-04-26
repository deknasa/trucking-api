<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends MyModel
{
    use HasFactory;

    protected $table = 'supplier';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
