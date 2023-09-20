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
                "trado.kodetrado as trado_id",
                "$this->table.servicein_nobukti",
                "$this->table.keterangan",
            )
                ->leftJoin(DB::raw("serviceoutheader as header with (readuncommitted)"), "header.id", "$this->table.serviceout_id")
                ->leftJoin(DB::raw("trado with (readuncommitted)"), "header.trado_id", "trado.id");
            $query->where($this->table . ".servicein_id", "=", request()->servicein_id);

            $serviceOutDetail = $query->get();
            $query->where($this->table . ".serviceout_id", "=", request()->serviceout_id);
        } else {
            $query->select(
                "$this->table.servicein_nobukti",
                "$this->table.keterangan",
            );
            $query->where($this->table . ".serviceout_id", "=", request()->serviceout_id);
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

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'mekanik_id') {
                                $query = $query->where('mekanik.namamekanik', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'mekanik_id') {
                                $query = $query->orWhere('mekanik.namamekanik', 'LIKE', "%$filters[data]%");
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
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(ServiceOutHeader $serviceOutHeader, array $data): ServiceOutDetail
    {
        $serviceoutdetail = new ServiceOutDetail();
        $serviceoutdetail->serviceout_id = $data['serviceout_id'];
        $serviceoutdetail->nobukti = $data['nobukti'];
        $serviceoutdetail->servicein_nobukti = $data['servicein_nobukti'];
        $serviceoutdetail->keterangan = $data['keterangan'];
        $serviceoutdetail->modifiedby = auth('api')->user()->name;
        $serviceoutdetail->info = html_entity_decode(request()->info);

        if (!$serviceoutdetail->save()) {
            throw new \Exception("Error storing service in detail.");
        }

        return $serviceoutdetail;
    }
}
