<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalBukaCetak extends MyModel
{
    use HasFactory;

    public function processStore(array $data)
    {
        $table = Parameter::where('text', $data['table'])->first();
        foreach ($data['tableId'] as $tableId) {
            $resultData[] = $this->bukaCetak($tableId, $table);
        }
        return $resultData;
    }

    public function bukaCetak($id, $table)
    {
        $backSlash = " \ ";

        $model = 'App\Models' . trim($backSlash) . $table->text;
        $data = app($model)->findOrFail($id);
        $statusCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
        $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

        if ($data->statuscetak == $statusCetak->id) {
            $data->statuscetak = $statusBelumCetak->id;
        // } else {
        //     $data->statuscetak = $statusCetak->id;
        }

        $data->tglbukacetak = date('Y-m-d H:i:s');
        $data->userbukacetak = auth('api')->user()->name;
        $data->info = html_entity_decode(request()->info);
        if (!$data->save()) {
            throw new \Exception('Error Buka Cetak.');
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($data->getTable()),
            'postingdari' => "BUKA/BELUM CETAK $table->text",
            'idtrans' => $data->id,
            'nobuktitrans' => $data->nobukti,
            'aksi' => 'BUKA/BELUM CETAK',
            'datajson' => $data->toArray(),
            'modifiedby' => auth('api')->user()->name,
        ]);
        return $data;
    }
}
