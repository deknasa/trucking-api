<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Supir extends MyModel
{
    use HasFactory;

    protected $table = 'supir';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'supir.id',
            'supir.namasupir',
            'supir.tgllahir',
            'supir.alamat',
            'supir.kota',
            'supir.telp',
            'parameter.text as statusaktif',
            'supir.nominaldepositsa',
            // 'supir.tglmasuk',
            'supirlama.namasupir as supirold_id',
            'supir.nosim',
            'supir.tglterbitsim',
            'supir.tglexpsim',
            'supir.keterangan',
            'supir.noktp',
            'supir.nokk',
            'statusadaupdategambar.text as statusadaupdategambar',
            'statusluarkota.text as statuslluarkota',
            'statuszonatertentu.text as statuszonatertentu',
            'zona.zona as zona_id',
            'supir.photosupir',
            'supir.photoktp',
            'supir.photosim',
            'supir.photokk',
            'supir.photoskck',
            'supir.photodomisili',
            'supir.keteranganresign',
            'supir.statusblacklist',
            'supir.tglberhentisupir',
            'supir.modifiedby',
            'supir.created_at',
            'supir.updated_at'
        )
            ->leftJoin('zona', 'zona.id', '=', 'supir.zona_id')
            ->leftJoin('parameter', 'supir.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as statusadaupdategambar', 'supir.statusadaupdategambar', '=', 'statusadaupdategambar.id')
            ->leftJoin('parameter as statusluarkota', 'supir.statuslluarkota', '=', 'statusluarkota.id')
            ->leftJoin('parameter as statuszonatertentu', 'supir.statuszonatertentu', '=', 'statuszonatertentu.id')
            ->leftJoin('supir as supirlama', 'supir.supirold_id', '=', 'supirlama.id');

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
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }

                        // else if ($filters['field'] == 'statusapproval') {
                        //     $query = $query->where('parameter_statusapproval.text', '=', $filters['data']);
                        // } else if ($filters['field'] == 'statustas') {
                        //     $query = $query->where('parameter_statustas.text', '=', $filters['data']);
                        // } 
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }

                        // else if ($filters['field'] == 'statusapproval') {
                        //     $query = $query->orWhere('parameter_statusapproval.text', '=', $filters['data']);
                        // } else if ($filters['field'] == 'statustas') {
                        //     $query = $query->orWhere('parameter_statustas.text', '=', $filters['data']);
                        // } 
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
