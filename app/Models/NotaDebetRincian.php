<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaDebetRincian extends MyModel
{
    use HasFactory;
    
    protected $table = 'notadebetrincian';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(NotaDebetHeader $notaDebetHeader, array $data): NotaDebetRincian
    {
        $notaDebetRincian = new NotaDebetRincian();
        $notaDebetRincian->notadebet_id = $notaDebetHeader->id;
        $notaDebetRincian->nobukti = $notaDebetHeader->nobukti;
        $notaDebetRincian->tglterima = $notaDebetHeader->tglbukti;
        $notaDebetRincian->agen_id = $data['agen_id'];
        $notaDebetRincian->pelanggan_id = $data['pelanggan_id'];
        $notaDebetRincian->nominal = $data['nominal'];
        $notaDebetRincian->modifiedby = auth('api')->user()->name;

        if (!$notaDebetRincian->save()) {
            throw new \Exception("Error storing nota debet rincian.");
        }

        return $notaDebetRincian;
    }
}
