<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainAkunPusat extends MyModel
{
    use HasFactory;

    protected $table = 'maintypeakuntansi';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
