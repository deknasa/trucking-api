<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CcEmail extends MyModel
{
    use HasFactory;


    protected $table = 'ccemail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
