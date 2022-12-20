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
            'stokpersediaan.qty',
            'stokpersediaan.modifiedby'
        )
        ->leftJoin('stok','stokpersediaan.stok_id', 'stok.id');
        
            
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $parameter = Parameter::where('id', request()->keterangan)->first();
        if($parameter->text == 'GUDANG'){
            $gudang_id = request()->data;
            $query->where('stokpersediaan.gudang_id', $gudang_id);
        } 
        if($parameter->text == 'TRADO'){
            $trado_id = request()->data;
            $query->where('stokpersediaan.trado_id', $trado_id);
        } 
        if($parameter->text == 'GANDENGAN'){
            $gandengan_id = request()->data;
            $query->where('stokpersediaan.gandengan_id', $gandengan_id);
        }
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
                        if ($filters['field'] == 'stok_id') {
                            $query = $query->where('stok.namastok', 'LIKE', "%$filters[data]%");
                        } else{
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                        
                            if ($filters['field'] == 'stok_id') {
                                $query = $query->orWhere('stok.namastok', 'LIKE', "%$filters[data]%");
                            } else {
                                $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }    
}
