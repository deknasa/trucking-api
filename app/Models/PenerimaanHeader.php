<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PenerimaanHeader extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaanheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function penerimaandetail() {
        return $this->hasMany(penerimaandetail::class, 'penerimaan_id');
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'penerimaanheader.id',
            'penerimaanheader.nobukti',
            'penerimaanheader.tglbukti',

            'pelanggan.namapelanggan as pelanggan_id',

            'penerimaanheader.keterangan',
            'penerimaanheader.postingdari',
            'penerimaanheader.statusapproval',
            'penerimaanheader.diterimadari',
            'penerimaanheader.tgllunas',

            'cabang.namacabang as cabang_id',
            'bank.namabank as bank_id',
            
            'statuskas.text as statuskas',
            'statusapproval.text as statusapproval',
            'statusberkas.text as statusberkas',

            // 'users.name as userapproval',
            'penerimaanheader.userapproval',
            'penerimaanheader.tglapproval',
            'penerimaanheader.userberkas',

            'penerimaanheader.noresi',
            'penerimaanheader.tglberkas',
            'penerimaanheader.modifiedby',
            'penerimaanheader.created_at',
            'penerimaanheader.updated_at'

        )
        ->leftJoin('pelanggan', 'penerimaanheader.pelanggan_id', 'pelanggan.id')
       ->leftJoin('bank', 'penerimaanheader.bank_id', 'bank.id')
        ->leftJoin('cabang', 'penerimaanheader.cabang_id', 'cabang.id')
        ->leftJoin('parameter as statuskas' , 'penerimaanheader.statuskas', 'statuskas.id')
        ->leftJoin('parameter as statusapproval' , 'penerimaanheader.statusapproval', 'statusapproval.id')
        ->leftJoin('parameter as statusberkas' , 'penerimaanheader.statusberkas', 'statusberkas.id');
        // ->leftJoin('users' , 'penerimaanheader.userapproval', 'users.id');


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
                         if ($filters['field'] == 'pelanggan_id') {
                            $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                         if ($filters['field'] == 'pelanggan_id') {
                            $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
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
