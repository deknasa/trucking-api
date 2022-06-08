<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderanTrucking extends MyModel
{
    use HasFactory;

    protected $table = 'orderantrucking';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function agen() {
        return $this->belongsTo(Agen::class, 'agen_id');
    }

    public function container() {
        return $this->belongsTo(Container::class, 'container_id');
    }

    public function jenisorder() {
        return $this->belongsTo(JenisOrder::class, 'jenisorder_id');
    }

    public function pelanggan() {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function tarif() {
        return $this->belongsTo(Tarif::class, 'tarif_id');
    }
    
}
