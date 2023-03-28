<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class HutangDetail extends MyModel
{
    use HasFactory;

    protected $table = 'hutangdetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    public function getAll($id)
    {


        $query = DB::table('hutangdetail')->from(DB::raw("hutangdetail with (readuncommitted)"))
            ->select(
                'hutangdetail.total',
                'hutangdetail.cicilan',
                'hutangdetail.totalbayar',
                'hutangdetail.tgljatuhtempo',
                'hutangdetail.keterangan',
            )
            ->where('hutang_id', '=', $id);


        $data = $query->get();

        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                'header.nobukti',
                'header.tglbukti',
                'pelanggan.namapelanggan as pelanggan_id',
                'header.coa',
                'header.keterangan as keteranganheader',
                'header.total as totalheader',
                'supplier.namasupplier as supplier_id',
                $this->table . '.tgljatuhtempo',
                $this->table . '.total',
                $this->table . '.keterangan'
            )->leftJoin(DB::raw("hutangheader as header with (readuncommitted)"), 'header.id', $this->table . '.hutang_id')
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'header.pelanggan_id', 'pelanggan.id')
                ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'header.supplier_id', 'supplier.id');

            $query->where($this->table . '.hutang_id', '=', request()->hutang_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.tgljatuhtempo',
                $this->table . '.total',
                $this->table . '.keterangan',
            );
            $this->sort($query, $this->table);
            $query->where($this->table . '.hutang_id', '=', request()->hutang_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('total');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }


    public function getHistory()
    {
        $this->setRequestParameters();

        $hutang = DB::table("hutangheader")->from(DB::raw("hutangheader with (readuncommitted)"))->where('id', request()->hutang_id)->first();
        if ($hutang != null) {

            $query = DB::table("hutangbayardetail")->from(DB::raw("hutangbayardetail with (readuncommitted)"));

            $query->select(
                'hutangbayardetail.nobukti as nobukti_bayar',
                'hutangbayardetail.hutang_nobukti',
                'hutangbayardetail.keterangan as keterangan_bayar',
                'hutangbayardetail.nominal as nominal_bayar',
                'hutangbayardetail.potongan',
            );

            $query->where('hutangbayardetail.hutang_nobukti', '=', $hutang->nobukti);

            $this->totalNominal = $query->sum('nominal');
            $this->totalPotongan = $query->sum('potongan');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query, 'hutangbayardetail');
            $this->filter($query);
            $this->paginate($query);


            return $query->get();
        } else {
            $this->totalNominal = 0;
            $this->totalPotongan = 0;
            $this->totalRows = 0;
        }
    }
    public function sort($query, $table)
    {
        if ($this->params['sortIndex'] == 'nobukti_bayar') {
            return $query->orderBy($table . '.nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'keterangan_bayar') {
            return $query->orderBy($table . '.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'potongan') {
            return $query->orderBy($table . '.potongan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominal_bayar') {
            return $query->orderBy($table . '.nominal', $this->params['sortOrder']);
        } else {
            return $query->orderBy($table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'hutang_nobukti') {
                                $query = $query->where('hutangbayardetail.hutang_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_bayar') {
                                $query = $query->where('hutangbayardetail.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nobukti_bayar') {
                                $query = $query->where('hutangbayardetail.nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal_bayar') {
                                $query = $query->where('hutangbayardetail.nominal', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'potongan') {
                                $query = $query->where('hutangbayardetail.potongan', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'hutang_nobukti') {
                                $query = $query->orWhere('hutangbayardetail.hutang_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_bayar') {
                                $query = $query->orWhere('hutangbayardetail.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nobukti_bayar') {
                                $query = $query->orWhere('hutangbayardetail.nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal_bayar') {
                                $query = $query->orWhere('hutangbayardetail.nominal', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'potongan') {
                                $query = $query->orWhere('hutangbayardetail.potongan', 'LIKE', "%$filters[data]%");
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
