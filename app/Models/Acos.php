<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Acos extends Model
{
    use HasFactory;

    protected $table = 'acos';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(array $data): Acos 
    {
        $Acos = new Acos();
        $Acos->class = $data['class'];
        $Acos->method = $data['method'];
        $Acos->nama = $data['nama'];
        $Acos->modifiedby = $data['modifiedby'];

        if (!$Acos->save()) {
            throw new \Exception("Error storing aco.");
        }
        
        return $Acos;
    }
}
