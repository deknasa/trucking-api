<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PengeluaranHeader extends MyModel
{
        use HasFactory;
    
        protected $table = 'pengeluaranheader';
    
        protected $casts = [
            'created_at' => 'date:d-m-Y H:i:s',
            'updated_at' => 'date:d-m-Y H:i:s'
        ];
    
        protected $guarded = [
            'id',
            'created_at',
            'updated_at',
        ];
    
        public function pengeluarandetail() {
            return $this->hasMany(pengeluarandetail::class, 'pengeluaran_id');
        }
    
        public function get()
        {
            $this->setRequestParameters();
    
            $query = DB::table($this->table)->select(
                'pengeluaranheader.id',
                'pengeluaranheader.nobukti',
                'pengeluaranheader.tglbukti',

                'pelanggan.namapelanggan as pelanggan_id',

                'pengeluaranheader.keterangan',
                'pengeluaranheader.postingdari',
                'pengeluaranheader.statusapproval',
                'pengeluaranheader.dibayarke',
                'cabang.namacabang as cabang_id',
                'bank.namabank as bank_id',
                
                'statusjenistransaksi.text as statusjenistransaksi',
                'statusapproval.text as statusapproval',
                'pengeluaranheader.transferkeac',
                'pengeluaranheader.transferkean',
                'pengeluaranheader.transferkebank',

                'pengeluaranheader.modifiedby',
                'pengeluaranheader.created_at',
                'pengeluaranheader.updated_at'
    
            )
            ->leftJoin('pelanggan', 'pengeluaranheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin('cabang', 'pengeluaranheader.cabang_id', 'cabang.id')
            ->leftJoin('bank', 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin('parameter as statusapproval' , 'pengeluaranheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statusjenistransaksi' , 'pengeluaranheader.statusjenistransaksi', 'statusjenistransaksi.id');
    
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
