<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PengembalianKasBankDetail extends MyModel
{
    use HasFactory;

    protected $table = 'PengembalianKasBankDetail';

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

        if (isset(request()->forReport) && request()->forReport) {

            $query->select(
                "header.nobukti",
                "header.tglbukti",
                "header.dibayarke",
                "header.keterangan as keteranganheader",
                "header.transferkeac",
                "header.transferkean",
                "header.transferkebank",
                
                "bank.namabank as bank",
                "$this->table.nowarkat",
                "$this->table.tgljatuhtempo",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.bulanbeban",
                "$this->table.coadebet",
                "$this->table.coakredit",
                "alatbayar.namaalatbayar as alatbayar_id"

            )
            ->join("pengeluaranheader as header","header.id","$this->table.pengembaliankasbank_id")
            ->leftJoin("bank", "bank.id", "=", "header.bank_id")
            
            ->leftJoin("alatbayar", "alatbayar.id", "=", "$this->table.alatbayar_id");
            $query->where($this->table . ".pengembaliankasbank_id", "=", request()->pengembaliankasbank_id);

        }else {
            $query->select(
                "$this->table.pengembaliankasbank_id",
                "$this->table.nobukti",
                "$this->table.nowarkat",
                "$this->table.tgljatuhtempo",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.bulanbeban",
                "$this->table.coadebet",
                "$this->table.coakredit",
                "alatbayar.namaalatbayar as alatbayar_id",

            )
            ->leftJoin("alatbayar", "alatbayar.id", "=", "$this->table.alatbayar_id");
            
            $query->where($this->table . ".pengembaliankasbank_id", "=", request()->pengembaliankasbank_id);
            $this->totalNominal = $query->sum('nominal');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->sort($query);
            $this->paginate($query);
        }
        return $query->get();
    }

    public function getAll($id)
    {
        $query = DB::table('pengembaliankasbankdetail');
        $query = $query->select(
            'pengembaliankasbankheader.nobukti',
                    'pengembaliankasbankheader.tglbukti',
                    'pengembaliankasbankheader.dibayarke',
                    'pengembaliankasbankheader.keterangan as keteranganheader',
                    'pengembaliankasbankheader.transferkeac',
                    'pengembaliankasbankheader.transferkean',
                    'pengembaliankasbankheader.transferkebank',
                    'bank.namabank as bank',
                    'pengembaliankasbankdetail.nowarkat',
                    'pengembaliankasbankdetail.tgljatuhtempo',
                    'pengembaliankasbankdetail.nominal',
                    'pengembaliankasbankdetail.keterangan',
                    'pengembaliankasbankdetail.bulanbeban',
                    'pengembaliankasbankdetail.coadebet',
                    'pengembaliankasbankdetail.coakredit',
                    'alatbayar.namaalatbayar as alatbayar',
                    'pengembaliankasbankdetail.alatbayar_id'
        )
        ->leftJoin('pengembaliankasbankheader', 'pengembaliankasbankdetail.pengembaliankasbank_id', 'pengembaliankasbankheader.id')
        ->leftJoin('bank', 'bank.id', '=', 'pengembaliankasbankheader.bank_id')
        ->leftJoin('alatbayar', 'alatbayar.id', '=', 'pengembaliankasbankdetail.alatbayar_id');

        $data = $query->where("pengembaliankasbank_id",$id)->get();

        return $data;
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'alatbayar_id') {
                                $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'alatbayar_id') {
                                $query = $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
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
}
