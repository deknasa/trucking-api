<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PenerimaanTruckingHeader extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaantruckingheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function penerimaantruckingdetail() {
        return $this->hasMany(penerimaantruckingdetail::class, 'penerimaantrucking_id');
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'penerimaantruckingheader.id',
            'penerimaantruckingheader.nobukti',
            'penerimaantruckingheader.tglbukti',
            'penerimaantruckingheader.keterangan',

            'bank.namabank as bank_id',
            'penerimaantrucking.kodepenerimaan as penerimaantrucking_id',
            'penerimaanheader.nobukti as penerimaan_nobukti',
            'penerimaanheader.tglbukti as penerimaan_tgl',

            'akunpusat.coa as coa',
        )
            ->leftJoin('bank', 'penerimaantruckingheader.bank_id', 'bank.id')
            ->join('penerimaantrucking', 'penerimaantrucking.id', '=', 'penerimaantruckingheader.penerimaantrucking_id')
            ->leftJoin('penerimaanheader', 'penerimaantruckingheader.penerimaan_nobukti', 'penerimaanheader.nobukti')
            ->leftJoin('akunpusat', 'penerimaantruckingheader.coa', 'akunpusat.coa')
          //  ->leftJoin('penerimaanheader', 'penerimaantruckingheader.penerimaan_tgl', 'penerimaanheader.tglbukti')

           // ->leftJoin('parameter as statuskas', 'penerimaantruckingheader.statuskas', 'statuskas.id')
          //  ->leftJoin('parameter as statusapproval', 'penerimaantruckingheader.statusapproval', 'statusapproval.id')
            ;


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
                         if ($filters['field'] == 'penerimaantrucking_id') {
                            $query = $query->where('kodepenerimaan.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                         if ($filters['field'] == 'penerimaantrucking_id') {
                            $query = $query->orWhere('kodepenerimaan.keterangan', 'LIKE', "%$filters[data]%");
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
