<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ServiceOutDetail extends MyModel
{
    use HasFactory;

    protected $table = 'serviceoutdetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "header.id as id_header",
                "header.nobukti as nobukti_header",
                "header.tglbukti as tgl_header",
                "header.keterangan as keterangan_header",
                "header.tglkeluar as tglkeluar",
                "trado.keterangan as trado_id",
                "$this->table.servicein_nobukti",
                "$this->table.keterangan",
            )
                ->leftJoin(DB::raw("serviceoutheader as header with (readuncommitted)"), "header.id", "$this->table.serviceout_id")
                ->leftJoin(DB::raw("trado with (readuncommitted)"), "header.trado_id", "trado.id");
                $query->where($this->table . ".servicein_id", "=", request()->servicein_id);

            $serviceOutDetail = $query->get();
            $query->where($this->table . ".serviceout_id", "=", request()->serviceout_id);

        }
        else{
            $query->select(
                "$this->table.servicein_nobukti",
                "$this->table.keterangan",
            );
            $query->where($this->table . ".serviceout_id", "=", request()->serviceout_id);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }
        return $query->get();

    }

    function getAll($id)
    {
        $query = DB::table('serviceoutdetail')->from(DB::raw("serviceoutdetail with (readuncommitted)"))
        ->select(
            'serviceoutdetail.nobukti',
            'serviceoutdetail.keterangan',
            'serviceinheader.nobukti as servicein_nobukti',
        )
            ->leftJoin(DB::raw("serviceinheader with (readuncommitted)"), 'serviceoutdetail.servicein_nobukti', 'serviceinheader.nobukti')

            ->where('serviceout_id', '=', $id);

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
