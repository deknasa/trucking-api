<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Method extends MyModel
{
    use HasFactory;
    
    protected $table = 'method';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

}
