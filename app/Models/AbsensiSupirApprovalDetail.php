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

    public function get(){

        $this->setRequestParameters();

        $query = DB::table("$this->table")->from(DB::raw("$this->table with (readuncommitted)"));
        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "$this->table.absensisupirapproval_id",
                "$this->table.nobukti",
                "$this->table.trado_id",
                "$this->table.supir_id",
                "$this->table.supirserap_id",
                "$this->table.modifiedby",
                "trado.keterangan as trado",
                "supirutama.namasupir as supir",
                "supirserap.namasupir as supirserap",

            )
            ->leftJoin("absensisupirapprovalheader", "$this->table.absensisupirapproval_id", "absensisupirapprovalheader.id")
            ->leftJoin("trado", "$this->table.trado_id", "trado.id")
            ->leftJoin("supir as supirutama", "$this->table.supir_id", "supirutama.id")
            ->leftJoin("supir as supirserap", "$this->table.supirserap_id", "supirserap.id");
            $query->where( $this->table.".absensisupirapproval_id", "=", request()->absensisupirapproval_id);

        }else{
            $query->select(
                "$this->table.absensisupirapproval_id",
                "$this->table.nobukti",
                "$this->table.trado_id",
                "$this->table.supir_id",
                "$this->table.supirserap_id",
                "$this->table.modifiedby",
                "trado.keterangan as trado",
                "supirutama.namasupir as supir",
                "supirserap.namasupir as supirserap",

            )

            ->leftJoin("absensisupirapprovalheader", "$this->table.absensisupirapproval_id", "absensisupirapprovalheader.id")
            ->leftJoin("trado", "$this->table.trado_id", "trado.id")
            ->leftJoin("supir as supirutama", "$this->table.supir_id", "supirutama.id")
            ->leftJoin("supir as supirserap", "$this->table.supirserap_id", "supirserap.id");

            $query->where( $this->table.".absensisupirapproval_id", "=", request()->absensisupirapproval_id);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            
            
            $this->sort($query);
            $this->paginate($query);
        }

        return $query->get();
    }

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

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
