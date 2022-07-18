<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class UpahRitasi extends MyModel
{
    use HasFactory;

    protected $table = 'upahritasi';

    protected $casts = [
        'tglmulaiberlaku' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function upahritasiRincian() {
        return $this->hasMany(UpahRitasiRincian::class, 'upahritasi_id');
    }

    public function kota() {
        return $this->belongsTo(Kota::class, 'kota_id');
    }

    public function zona() {
        return $this->belongsTo(Zona::class, 'zona_id');
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'upahritasi.id',
            'kotadari.keterangan as kotadari_id',
            'kotasampai.keterangan as kotasampai_id',
            'upahritasi.jarak',
            'zona.zona as zona_id',
            'parameter.text as statusaktif',
            'upahritasi.tglmulaiberlaku',
            'param.text as statusluarkota',
            'upahritasi.modifiedby',
            'upahritasi.created_at',
            'upahritasi.updated_at'
        )
            ->join('kota as kotadari', 'kotadari.id', '=', 'upahritasi.kotadari_id')
            ->join('kota as kotasampai', 'kotasampai.id', '=', 'upahritasi.kotasampai_id')
            ->join('zona', 'zona.id', '=', 'upahritasi.zona_id')
            ->leftJoin('parameter', 'upahritasi.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as param', 'upahritasi.statusluarkota', '=', 'param.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.zona', '=', $filters['data']);
                        } else if ($filters['field'] == 'kotadari_id') {
                            $query = $query->where('kotadari.keterangan', '=', $filters['data']);
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->orWhere('zona.zona', '=', $filters['data']);
                        } else if ($filters['field'] == 'kotadari_id') {
                            $query = $query->orWhere('kotadari.keterangan', '=', $filters['data']);
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

}
