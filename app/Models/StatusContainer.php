<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusContainer extends MyModel
{
    use HasFactory;

    protected $table = 'statuscontainer';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
