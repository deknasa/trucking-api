<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalKirimBerkas extends Model
{
    use HasFactory;
    public function processStore(array $data)
    {
        $table = Parameter::where('text', $data['table'])->first();
        foreach ($data['tableId'] as $tableId) {
            $resultData[] = $this->bukaKirimBerkas($tableId, $table);
        }
        return $resultData;
    }

    public function bukaKirimBerkas($id, $table)
    {
        $backSlash = " \ ";

        $model = 'App\Models' . trim($backSlash) . $table->text;
        $data = app($model)->findOrFail($id);
        $statusKirimBerkas = Parameter::where('grp', '=', 'STATUSKIRIMBERKAS')->where('text', '=', 'KIRIM BERKAS')->first();
        $statusBelumKirimBerkas = Parameter::where('grp', '=', 'STATUSKIRIMBERKAS')->where('text', '=', 'BELUM KIRIM BERKAS')->first();

        if ($data->statuskirimberkas == $statusKirimBerkas->id) {
            $data->statuskirimberkas = $statusBelumKirimBerkas->id;
        // } else {
        //     $data->statuskirimberkas = $statusKirimBerkas->id;
        }

        $data->tglbukakirimberkas = date('Y-m-d', time());
        $data->userbukakirimberkas = auth('api')->user()->name;
        $data->info = html_entity_decode(request()->info);
        if (!$data->save()) {
            throw new \Exception('Error Buka Kirim Berkas.');
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($data->getTable()),
            'postingdari' => "KIRIM BERKAS/BELUM KIRIM BERKAS $table->text",
            'idtrans' => $data->id,
            'nobuktitrans' => $data->nobukti,
            'aksi' => 'KIRIM BERKAS/BELUM KIRIM BERKAS',
            'datajson' => $data->toArray(),
            'modifiedby' => auth('api')->user()->name,
        ]);
        return $data;
    }    
}
