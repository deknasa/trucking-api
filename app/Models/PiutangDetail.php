<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PiutangDetail extends MyModel
{
    use HasFactory;

    protected $table = 'piutangdetail';

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
                'header.id as id_header',
                'header.nobukti as nobukti_header',
                'header.tglbukti as tgl_header',
                'header.keterangan as keterangan_header',
                'header.invoice_nobukti as invoice_nobukti',
                'agen.namaagen as agen_id',
                $this->table . '.keterangan as keterangan_detail',
                $this->table . '.nominal',
                $this->table . '.invoice_nobukti as invoice_nobukti_detail'
            )
                ->leftJoin('piutangheader as header', 'header.id',  $this->table . '.piutang_id')
                ->leftJoin('agen', 'header.agen_id', 'agen.id');

            $query->where($this->table . '.piutang_id', '=', request()->piutang_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.keterangan',
                $this->table . '.invoice_nobukti',
                $this->table . '.nominal'
            );

            $this->sort($query, 'piutangdetail');
            $query->where($this->table . '.piutang_id', '=', request()->piutang_id);
            $this->filter($query);
            $this->totalNominal = $query->sum('nominal');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function getHistory()
    {
        $this->setRequestParameters();

        $piutang = DB::table("piutangheader")->from(DB::raw("piutangheader with (readuncommitted)"))->where('id', request()->piutang_id)->first();
        if ($piutang != null) {

            $query = DB::table("pelunasanpiutangdetail")->from(DB::raw("pelunasanpiutangdetail with (readuncommitted)"));

            $query->select(
                'pelunasanpiutangdetail.nobukti as nobukti_pelunasan',
                'pelunasanpiutangdetail.piutang_nobukti',
                'pelunasanpiutangdetail.keterangan as keterangan_pelunasan',
                'pelunasanpiutangdetail.invoice_nobukti as invoice_pelunasan',
                'pelunasanpiutangdetail.nominal as nominal_pelunasan',
                'pelunasanpiutangdetail.potongan',
                'pelunasanpiutangdetail.nominallebihbayar',
            );

            $query->where('pelunasanpiutangdetail.piutang_nobukti', '=', $piutang->nobukti);

            $this->totalNominal = $query->sum('nominal');
            $this->totalPotongan = $query->sum('potongan');
            $this->totalNominalLebih = $query->sum('nominallebihbayar');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query, 'pelunasanpiutangdetail');
            $this->filter($query);
            $this->paginate($query);


            return $query->get();
        } else {
            $this->totalNominal = 0;
            $this->totalPotongan = 0;
            $this->totalNominalLebih = 0;
        }
    }

    public function sort($query, $table)
    {
        if ($this->params['sortIndex'] == 'nobukti_pelunasan') {
            return $query->orderBy($table . '.nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'keterangan_pelunasan') {
            return $query->orderBy($table . '.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'invoice_pelunasan') {
            return $query->orderBy($table . '.invoice_nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominal_pelunasan') {
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
                            if ($filters['field'] == 'nobukti_pelunasan') {
                                $query = $query->where('pelunasanpiutangdetail.nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'piutang_nobukti') {
                                $query = $query->where('pelunasanpiutangdetail.piutang_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_pelunasan') {
                                $query = $query->where('pelunasanpiutangdetail.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'invoice_pelunasan') {
                                $query = $query->where('pelunasanpiutangdetail.invoice_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal_pelunasan') {
                                $query = $query->where('pelunasanpiutangdetail.nominal', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'potongan') {
                                $query = $query->where('pelunasanpiutangdetail.potongan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominallebihbayar') {
                                $query = $query->where('pelunasanpiutangdetail.nominallebihbayar', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'nobukti_pelunasan') {
                                $query = $query->orWhere('pelunasanpiutangdetail.nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'piutang_nobukti') {
                                $query = $query->orWhere('pelunasanpiutangdetail.piutang_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_pelunasan') {
                                $query = $query->orWhere('pelunasanpiutangdetail.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'invoice_pelunasan') {
                                $query = $query->orWhere('pelunasanpiutangdetail.invoice_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal_pelunasan') {
                                $query = $query->orWhere('pelunasanpiutangdetail.nominal', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'potongan') {
                                $query = $query->orWhere('pelunasanpiutangdetail.potongan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominallebihbayar') {
                                $query = $query->orWhere('pelunasanpiutangdetail.nominallebihbayar', 'LIKE', "%$filters[data]%");
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
