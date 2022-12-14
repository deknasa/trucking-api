<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class stokpersediaan extends MyModel
{
    use HasFactory;

    protected $table = 'stokpersediaan';

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
            'stokpersediaan.id',
            'stok.namastok as stok_id',
            'gudang.gudang as gudang_id',
            'trado.keterangan as trado_id',
            'gandengan.keterangan as gandengan_id',
            'stokpersediaan.qty',
            'stokpersediaan.modifiedby'
            )
            ->leftJoin('stok','stokpersediaan.stok_id', 'stok.id')
            ->leftJoin('gudang','stokpersediaan.gudang_id', 'gudang.id')
            ->leftJoin('trado','stokpersediaan.trado_id', 'trado.id')
            ->leftJoin('gandengan','stokpersediaan.gandengan_id', 'gandengan.id');
            
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
