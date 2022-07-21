<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class KasGantungHeader extends MyModel
{
    use HasFactory;

    protected $table = 'kasgantungheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    // protected $casts = [
    //     'tgl' => 'date:d-m-Y',
    //     'tglkaskeluar' => 'date:d-m-Y',
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ];     

    public function kasgantungDetail() {
        return $this->hasMany(KasGantungDetail::class, 'kasgantung_id');
    }

    public function bank() {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function penerima() {
        return $this->belongsTo(Penerima::class, 'penerima_id');
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'kasgantungheader.id',
            'kasgantungheader.nobukti',
            'kasgantungheader.tglbukti',
            'penerima.namapenerima as penerima_id',
            'kasgantungheader.keterangan',
            'bank.namabank as bank_id',
            'kasgantungheader.pengeluaran_nobukti',
            'kasgantungheader.coakaskeluar',
            'kasgantungheader.tglkaskeluar',
            'kasgantungheader.modifiedby',
            'kasgantungheader.created_at',
            'kasgantungheader.updated_at'
        )
            ->leftJoin('penerima', 'kasgantungheader.penerima_id', 'penerima.id')
            ->leftJoin('bank', 'kasgantungheader.bank_id', 'bank.id');

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
                        if ($filters['field'] == 'penerima_id') {
                            $query = $query->where('penerima.namapenerima', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'penerima_id') {
                            $query = $query->orWhere('penerima.namapenerima', '=', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
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
