<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class acl extends Model
{
    use HasFactory;

    protected $table = 'acl';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
