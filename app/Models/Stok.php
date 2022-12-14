<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Stok extends MyModel
{
    use HasFactory;

    protected $table = 'stok';

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

        $query = DB::table($this->table)->select(
            'stok.id',
            'stok.namastok',
            'parameter.memo as statusaktif',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'stok.modifiedby',
            'jenistrado.keterangan as jenistrado',
            'kelompok.keterangan as kelompok',
            'subkelompok.keterangan as subkelompok',
            'kategori.keterangan as kategori',
            'merk.keterangan as merk',
            )
            ->leftJoin('jenistrado','stok.jenistrado_id', 'jenistrado.id')
            ->leftJoin('kelompok','stok.kelompok_id', 'kelompok.id')
            ->leftJoin('subkelompok','stok.subkelompok_id', 'subkelompok.id')
            ->leftJoin('kategori','stok.kategori_id', 'kategori.id')
            ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
            ->leftJoin('merk','stok.merk_id', 'merk.id');
            
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
                        if ($filters['field'] == 'jenistrado') {
                            $query = $query->where('jenistrado.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'kelompok') {
                            $query = $query->where('kelompok.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'subkelompok') {
                            $query = $query->where('subkelompok.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'kategori') {
                            $query = $query->where('kategori.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'merk') {
                            $query = $query->where('merk.keterangan', '=', "$filters[data]");
                        } else {
                            $query = $query->where($this->table . '.' .$filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'jenistrado') {
                            $query = $query->orWhere('jenistrado.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'kelompok') {
                            $query = $query->orWhere('kelompok.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'subkelompok') {
                            $query = $query->orWhere('subkelompok.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'kategori') {
                            $query = $query->orWhere('kategori.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'merk') {
                            $query = $query->orWhere('merk.keterangan', '=', "$filters[data]");
                        } else {
                            $query = $query->orWhere($this->table . '.' .$filters['field'], 'LIKE', "%$filters[data]%");
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