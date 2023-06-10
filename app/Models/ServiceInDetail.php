<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ServiceInDetail extends MyModel
{
    use HasFactory;

    protected $table = 'serviceindetail';

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
                "header.tglmasuk as tglmasuk",
                "trado.kodetrado as trado_id",
                "karyawan.namakaryawan as karyawan_id",
                "$this->table.keterangan",
                "$this->table.nobukti"
            )

            ->leftJoin("serviceinheader as header", "header.id", "$this->table.servicein_id")
            ->leftJoin("trado", "header.trado_id", "trado.id")
            ->leftJoin("karyawan", "$this->table.karyawan_id", "karyawan.id");
            $query->where($this->table . ".servicein_id", "=", request()->servicein_id);

        }else {
            $query->select(
                "karyawan.namakaryawan as karyawan_id",
                "$this->table.keterangan",
                "$this->table.nobukti"
            )
            ->leftJoin(DB::raw("karyawan with (readuncommitted)"), "$this->table.karyawan_id", "karyawan.id");
            $query->where($this->table . ".servicein_id", "=", request()->servicein_id);

            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);

        }
        return $query->get();
        
    }
    function getAll($id)
    {
        $query = DB::table('serviceindetail')->from(DB::raw("serviceindetail with (readuncommitted)"))
        ->select(
            // 'serviceindetail.nobukti',
            'serviceindetail.karyawan_id',
            'karyawan.namakaryawan as karyawan',

            'serviceindetail.keterangan',

        )
            ->leftJoin('karyawan', 'serviceindetail.karyawan_id', 'karyawan.id')
            ->where('servicein_id', '=', $id);

        $data = $query->get();

        return $data;
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'karyawan_id') {
                                $query = $query->where('karyawan.namakaryawan', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'karyawan_id') {
                                $query = $query->orWhere('karyawan.namakaryawan', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });
                    break;
                default:

                    break;
            }
        }
    }

    public function sort($query)
    {
        if($this->params['sortIndex'] == 'karyawan_id') {
            return $query->orderBy('karyawan.namakaryawan', $this->params['sortOrder']);
        }else{   
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(ServiceInHeader $serviceInHeader, array $data): ServiceInDetail
    {
        $serviceInDetail = new ServiceInDetail();
        $serviceInDetail->servicein_id = $serviceInHeader->id;
        $serviceInDetail->nobukti = $serviceInHeader->nobukti;
        $serviceInDetail->karyawan_id =  $data['karyawan_id'];
        $serviceInDetail->keterangan = $data['keterangan'];
        $serviceInDetail->modifiedby = auth('api')->user()->name;
        
        if (!$serviceInDetail->save()) {
            throw new \Exception("Error storing service in detail.");
        }

        return $serviceInDetail;
    }
}
