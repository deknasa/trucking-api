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
        $Acos->idheader = $data['idheader'];
        $Acos->keterangan = $data['keterangan'];
        $Acos->modifiedby = $data['modifiedby'];

        if (!$Acos->save()) {
            throw new \Exception("Error storing aco.");
        }

        return $Acos;
    }

    public function processUpdate($id, array $data): Acos
    {
        $acos = Acos::findOrFail($id);
        $acos->keterangan = $data['keterangan'];
        $acos->modifiedby = $data['modifiedby'];

        if (!$acos->save()) {
            throw new \Exception("Error updating aco.");
        }

        return $acos;
    }
}
