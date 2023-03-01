<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PengeluaranDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluarandetail';

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
                "header.transferkeac",
                "header.transferkean",
                "header.transferkebank",
                "pelanggan.namapelanggan as pelanggan",
                "bank.namabank as bank",
                "$this->table.nowarkat",
                "$this->table.tgljatuhtempo",
                "$this->table.nominal",
                "$this->table.keterangan",
                DB::raw("(case when year(isnull($this->table.bulanbeban,'1900/1/1'))=1900 then null else $this->table.bulanbeban end) as bulanbeban"),
                "$this->table.coadebet",
                "$this->table.coakredit",
                "alatbayar.namaalatbayar as alatbayar_id"

            )
                ->leftJoin(DB::raw("pengeluaranheader as header with (readuncommitted)"), "header.id", "$this->table.pengeluaran_id")
                ->leftJoin(DB::raw("bank with (readuncommitted)"), "bank.id", "=", "header.bank_id")
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), "pelanggan.id", "=", "header.pelanggan_id")
                ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), "alatbayar.id", "=", "header.alatbayar_id");
            $query->where($this->table . ".pengeluaran_id", "=", request()->pengeluaran_id);

            $pengeluaranDetail = $query->get();
        } else {

            $query->select(
                "$this->table.pengeluaran_id",
                "$this->table.nobukti",
                "$this->table.nowarkat",
                "$this->table.nominal",
                "$this->table.keterangan",
                DB::raw("(case when year(isnull($this->table.bulanbeban,'1900/1/1'))<2000 then null else $this->table.bulanbeban end) as bulanbeban"),
                DB::raw("(case when year(isnull($this->table.tgljatuhtempo,'1900/1/1'))<2000 then null else $this->table.tgljatuhtempo end) as tgljatuhtempo"),
                "debet.keterangancoa as coadebet",
                "kredit.keterangancoa as coakredit",

            )
                ->leftJoin(DB::raw("akunpusat as debet with (readuncommitted)"), "$this->table.coadebet", "debet.coa")
                ->leftJoin(DB::raw("akunpusat as kredit with (readuncommitted)"), "$this->table.coakredit", "kredit.coa");

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            
            $query->where($this->table . ".pengeluaran_id", "=", request()->pengeluaran_id);
            $this->filter($query);
            $this->paginate($query);

        }
        return $query->get();
    }
    public function findAll($id)
    {
        $query =  DB::table("pengeluarandetail")->from(DB::raw("pengeluarandetail with (readuncommitted)"))
            ->select(
                'pengeluarandetail.nowarkat',
                'pengeluarandetail.tgljatuhtempo',
                'pengeluarandetail.keterangan',
                'pengeluarandetail.nominal',
                'pengeluarandetail.coadebet',
                'akunpusat.keterangancoa as ketcoadebet',
                DB::raw("(case when year(cast(pengeluarandetail.bulanbeban as datetime))='1900' then '' else format(pengeluarandetail.bulanbeban,'yyyy-MM-dd') end) as bulanbeban"),
            )
            ->join(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarandetail.coadebet', 'akunpusat.coa')
            ->where("pengeluarandetail.pengeluaran_id", $id);

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
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coadebet') {
                                $query = $query->where('debet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->where('kredit.keterangancoa', 'LIKE', "%$filters[data]%");
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

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
