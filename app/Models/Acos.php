<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acos extends Model
{
    use HasFactory;

    protected $table = 'acos';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
