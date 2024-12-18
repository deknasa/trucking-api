<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PengembalianKasBankDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pengembaliankasbankdetail';

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
                ->join("pengeluaranheader as header", "header.id", "$this->table.pengembaliankasbank_id")
                ->leftJoin("bank", "bank.id", "=", "header.bank_id")

                ->leftJoin("alatbayar", "alatbayar.id", "=", "$this->table.alatbayar_id");
            $query->where($this->table . ".pengembaliankasbank_id", "=", request()->pengembaliankasbank_id);
        } else {
            $query->select(
                "$this->table.pengembaliankasbank_id",
                "$this->table.nobukti",
                "$this->table.nowarkat",
                "$this->table.tgljatuhtempo",
                "$this->table.nominal",
                "$this->table.keterangan",
                DB::raw("(case when year(isnull($this->table.bulanbeban,'1900/1/1'))<2000 then null else $this->table.bulanbeban end) as bulanbeban"),
                "debet.keterangancoa as coadebet",
                "kredit.keterangancoa as coakredit",

            )
                ->leftJoin(DB::raw("akunpusat as debet with (readuncommitted)"), "$this->table.coadebet", "debet.coa")
                ->leftJoin(DB::raw("akunpusat as kredit with (readuncommitted)"), "$this->table.coakredit", "kredit.coa");

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
            'pengembaliankasbankdetail.nowarkat',
            'pengembaliankasbankdetail.tgljatuhtempo',
            'pengembaliankasbankdetail.nominal',
            'pengembaliankasbankdetail.keterangan',
            DB::raw("(case when year(cast(pengembaliankasbankdetail.bulanbeban as datetime))='1900' then '' else format(pengembaliankasbankdetail.bulanbeban,'yyyy-MM-dd') end) as bulanbeban"),
                
            'pengembaliankasbankdetail.coadebet',
            'debet.keterangancoa as ketcoadebet',
            'pengembaliankasbankdetail.coakredit',
            'kredit.keterangancoa as ketcoakredit',
        ) 
        ->leftJoin(DB::raw("akunpusat as debet with (readuncommitted)"), "pengembaliankasbankdetail.coadebet", "debet.coa")
        ->leftJoin(DB::raw("akunpusat as kredit with (readuncommitted)"), "pengembaliankasbankdetail.coakredit", "kredit.coa");

        $data = $query->where("pengembaliankasbank_id", $id)->get();

        return $data;
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coadebet') {
                                $query = $query->where('debet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->where('kredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'bulanbeban' || $filters['field'] == 'tgljatuhtempo') {
                                $query = $query->whereRaw("format((case when year(isnull($this->table.".$filters['field'].",'1900/1/1'))<2000 then null else pengembaliankasbankdetail.".$filters['field']." end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coadebet') {
                                $query = $query->orWhere('debet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->orWhere('kredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'bulanbeban' || $filters['field'] == 'tgljatuhtempo') {
                                $query = $query->orWhereRaw("format((case when year(isnull($this->table.".$filters['field'].",'1900/1/1'))<2000 then null else pengembaliankasbankdetail.".$filters['field']." end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
