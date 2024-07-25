<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BiayaExtraSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'biayaextrasupirdetail';
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
        $query->select(
            "$this->table.keteranganbiaya",
            "$this->table.nominal",
            "$this->table.nominaltagih"
        )->where($this->table . ".biayaextrasupir_id", "=", request()->biayaextrasupir_id);

        $this->filter($query);
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);


        return $query->get();
    }
    function findAll($id)
    {
        $query = DB::table('biayaextrasupirdetail')->from(DB::raw("biayaextrasupirdetail with (readuncommitted)"))
            ->select(
                'keteranganbiaya',
                'nominal',
                'nominaltagih',
            )
            ->where('biayaextrasupir_id', '=', $id);

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
                            if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format(biayaextrasupirdetail.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else  if ($filters['field'] == 'nominaltagih') {
                                $query = $query->whereRaw("format(biayaextrasupirdetail.nominaltagih, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format(biayaextrasupirdetail.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominaltagih') {
                                $query = $query->orWhereRaw("format(biayaextrasupirdetail.nominaltagih, '#,#0.00') LIKE '%$filters[data]%'");
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

    public function processStore(BiayaExtraSupirHeader $biayaExtraSupirHeader, array $data): BiayaExtraSupirDetail
    {
        $BiayaExtraSupirDetail = new BiayaExtraSupirDetail();
        $BiayaExtraSupirDetail->nobukti = $biayaExtraSupirHeader->nobukti;
        $BiayaExtraSupirDetail->biayaextrasupir_id = $biayaExtraSupirHeader->id;
        $BiayaExtraSupirDetail->keteranganbiaya =  $data['keteranganbiaya'];
        $BiayaExtraSupirDetail->nominal = $data['nominal'];
        $BiayaExtraSupirDetail->nominaltagih = $data['nominaltagih'];
        $BiayaExtraSupirDetail->modifiedby = auth('api')->user()->name;
        $BiayaExtraSupirDetail->info = html_entity_decode(request()->info);

        if (!$BiayaExtraSupirDetail->save()) {
            throw new \Exception("Error storing biaya extra supir detail.");
        }

        return $BiayaExtraSupirDetail;
    }
}
