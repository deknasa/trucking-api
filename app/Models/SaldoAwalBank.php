<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaldoAwalBank extends MyModel
{
    use HasFactory;

    protected $table = 'saldoawalbank';
}
