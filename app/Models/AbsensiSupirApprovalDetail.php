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
                "trado.kodetrado as trado",
                "b.uangjalan as uangjalan",
                "supirutama.namasupir as supir",
                "supirserap.namasupir as supirserap",

            )
            ->leftJoin("absensisupirapprovalheader", "$this->table.absensisupirapproval_id", "absensisupirapprovalheader.id")
            ->join(DB::raw("absensisupirdetail as b with(readuncommitted)"), function ($join) {
                $join->on('absensisupirapprovalheader.absensisupir_nobukti', '=', 'b.nobukti');
                $join->on('absensisupirapprovaldetail.trado_id', '=', 'b.trado_id');
                $join->on('absensisupirapprovaldetail.supir_id', '=', 'b.supir_id');
            })
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
                "trado.kodetrado as trado",
                "b.uangjalan as uangjalan",
                "supirutama.namasupir as supir",
                "supirserap.namasupir as supirserap",
            )

            ->leftJoin("absensisupirapprovalheader", "$this->table.absensisupirapproval_id", "absensisupirapprovalheader.id")
            ->join(DB::raw("absensisupirdetail as b with(readuncommitted)"), function ($join) {
                $join->on('absensisupirapprovalheader.absensisupir_nobukti', '=', 'b.nobukti');
                $join->on($this->table.'.trado_id', '=', 'b.trado_id');
                $join->on($this->table.'.supir_id', '=', 'b.supir_id');
            })
            ->leftJoin("trado", "$this->table.trado_id", "trado.id")
            ->leftJoin("supir as supirutama", "$this->table.supir_id", "supirutama.id")
            ->leftJoin("supir as supirserap", "$this->table.supirserap_id", "supirserap.id");

            

            $query->where( $this->table.".absensisupirapproval_id", "=", request()->absensisupirapproval_id);

            $this->filter($query);
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
                    'trado.kodetrado as trado',
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
        if($this->params['sortIndex'] == 'trado'){
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if($this->params['sortIndex'] == 'supir'){
            return $query->orderBy('supirutama.namasupir', $this->params['sortOrder']);
        } else if($this->params['sortIndex'] == 'supirserap'){
            return $query->orderBy('supirserap.namasupir', $this->params['sortOrder']);
        }else{
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                   
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'trado') {
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir') {
                                $query = $query->where('supirutama.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supirserap') {
                                $query = $query->where('supirserap.namasupir', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'trado') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir') {
                                $query = $query->orWhere('supirutama.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supirserap') {
                                $query = $query->orWhere('supirserap.namasupir', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });
                    break;
                default:

                    break;
            }


            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }


    public function processStore(AbsensiSupirApprovalHeader $absensiSupirApprovalHeader, array $data) :AbsensiSupirApprovalDetail
    {
        $absensiSupirApprovalDetail = new AbsensiSupirApprovalDetail();
        $absensiSupirApprovalDetail->absensisupirapproval_id = $data['absensisupirapproval_id'];
        $absensiSupirApprovalDetail->nobukti = $data['nobukti'];
        $absensiSupirApprovalDetail->trado_id = $data['trado_id'];
        $absensiSupirApprovalDetail->supir_id = $data['supir_id'] ?? '';
        $absensiSupirApprovalDetail->statusjeniskendaraan = $data['statusjeniskendaraan'];
        $absensiSupirApprovalDetail->modifiedby = $data['modifiedby'];

        if (!$absensiSupirApprovalDetail->save()) {
            throw new \Exception("Gagal menyimpan absensi Approval supir detail.");
        }
        
        return $absensiSupirApprovalDetail;
    }
}
