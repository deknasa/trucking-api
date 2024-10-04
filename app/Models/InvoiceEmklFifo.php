<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceEmklFifo extends Model
{
    use HasFactory;
    protected $table = 'invoiceemklfifo';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];
    public function processStore(array $data)
    {
        $InvoiceEmklFifo = new InvoiceEmklFifo();
        $InvoiceEmklFifo->nobukti = $data['nobukti'];
        $InvoiceEmklFifo->jobemkl_nobukti = $data['jobemkl_nobukti'];
        $InvoiceEmklFifo->status = $data['status'];
        $InvoiceEmklFifo->nominal = $data['nominal'];
        $InvoiceEmklFifo->nominalpelunasan = $data['nominalpelunasan'];
        $InvoiceEmklFifo->coadebet = $data['coadebet'];
        $InvoiceEmklFifo->biayaemkl_id = $data['biayaemkl_id'] ?? 0;
        $InvoiceEmklFifo->modifiedby = auth('api')->user()->name;
        $InvoiceEmklFifo->info = html_entity_decode(request()->info);

        if (!$InvoiceEmklFifo->save()) {
            throw new \Exception("Error storing invoice detail fifo.");
        }

        return $InvoiceEmklFifo;
    }
}
