<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPengantarBiayaTambahan extends MyModel
{
    use HasFactory;

    protected $table = 'suratpengantarbiayatambahan';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    public function processStore(SuratPengantar $suratPengantar, array $data): SuratPengantarBiayaTambahan
    {
        $suratpengantarbiayatambahan = new SuratPengantarBiayaTambahan();
        $suratpengantarbiayatambahan->suratpengantar_id = $suratPengantar->id;
        $suratpengantarbiayatambahan->keteranganbiaya = $data['keteranganbiaya'];
        $suratpengantarbiayatambahan->nominal = $data['nominal'];
        $suratpengantarbiayatambahan->nominaltagih = $data['nominaltagih'];
        $suratpengantarbiayatambahan->modifiedby = auth('api')->user()->name;;

        if (!$suratpengantarbiayatambahan->save()) {
            throw new \Exception("Error storing surat pengantar biaya tambahan.");
        }

        return $suratpengantarbiayatambahan;
    }
}
