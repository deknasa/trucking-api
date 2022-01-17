<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class logtrail extends Model
{
    use HasFactory;

    
    protected $table = 'logtrail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
