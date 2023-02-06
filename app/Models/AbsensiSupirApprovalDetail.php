<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class AbsensiSupirApprovalDetail extends MyModel
{
    use HasFactory;

    protected $table = 'absensisupirapprovaldetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function getAll($id)
    {
        $query = DB::table('absensisupirapprovaldetail')->from(DB::raw("absensisupirapprovaldetail as detail with (readuncommitted)"))
            ->select(
                'detail.absensisupirapproval_id',
                    'detail.nobukti',
                    'detail.trado_id',
                    'detail.supir_id',
                    'detail.supirserap_id',
                    'detail.modifiedby',
                    'trado.keterangan as trado',
                    'supirutama.namasupir as supir',
                    'supirserap.namasupir as supirserap',

            )
            ->leftJoin('absensisupirapprovalheader', 'detail.absensisupirapproval_id', 'absensisupirapprovalheader.id')
            ->leftJoin('trado', 'detail.trado_id', 'trado.id')
            ->leftJoin('supir as supirutama', 'detail.supir_id', 'supirutama.id')
            ->leftJoin('supir as supirserap', 'detail.supirserap_id', 'supirserap.id')
            ->where('detail.absensisupirapproval_id', '=', $id);
        $data = $query->get();


        return $data;
    }
}
