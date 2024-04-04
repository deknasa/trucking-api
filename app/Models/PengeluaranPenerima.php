<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PengeluaranPenerima extends Model
{
    use HasFactory;
    protected $table = 'pengeluaranpenerima';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];


    public function findAll($id)
    {
        $query = DB::table("pengeluaranpenerima")->from(DB::raw("pengeluaranpenerima"))
            ->select('penerima_id')
            ->where('pengeluaran_id', $id)
            ->get();
        return $query;
    }
    public function processStore(array $data)
    {
        $pengeluaranPenerima = new PengeluaranPenerima();
        $pengeluaranPenerima->pengeluaran_id = $data['pengeluaran_id'];
        $pengeluaranPenerima->nobukti = $data['nobukti'];
        $pengeluaranPenerima->penerima_id = $data['penerima_id'];
        $pengeluaranPenerima->modifiedby = auth('api')->user()->name;

        $pengeluaranPenerima->save();

        if (!$pengeluaranPenerima->save()) {
            throw new \Exception("Error storing Pengeluaran Penerima.");
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranPenerima->getTable()),
            'postingdari' => strtoupper('ENTRY PENGELUARAN KAS/BANK'),
            'idtrans' =>  $pengeluaranPenerima->pengeluaran_id,
            'nobuktitrans' => $pengeluaranPenerima->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranPenerima->toArray(),
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $pengeluaranPenerima;
    }
}
