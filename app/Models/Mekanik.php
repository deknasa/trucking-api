<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mekanik extends MyModel
{
    use HasFactory;

<<<<<<< HEAD
    protected $table = 'Mekanik';
=======
    protected $table = 'mekanik';
>>>>>>> 45bc0d5a7d263f6ec185c4c06e9fc88025a55e7c

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
<<<<<<< HEAD
    ]; 
=======
    ];  
>>>>>>> 45bc0d5a7d263f6ec185c4c06e9fc88025a55e7c
}
