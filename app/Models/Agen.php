<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agen extends MyModel
{
    use HasFactory;

    protected $table = 'agen';
    
    protected $casts = [
        'tglapproval' => 'date:d-m-Y',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
