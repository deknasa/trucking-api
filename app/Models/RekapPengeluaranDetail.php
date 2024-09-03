<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RekapPengeluaranDetail extends MyModel
{
    use HasFactory;

    protected $table = 'rekappengeluarandetail';

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

        if (isset(request()->id)) {
            $query->where("$this->table.id", request()->id);
        }

        if (isset(request()->rekappengeluaran_id)) {
            $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));
            $query->where("$this->table.rekappengeluaran_id", request()->rekappengeluaran_id);
        }

        // if (count(request()->whereIn) > 0) {
        //     $query->whereIn('rekappengeluaran_id', request()->whereIn);
        // }
        if (isset(request()->forReport) && request()->forReport) {
            $cetakanBank1 = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('id')->where('grp', 'FORMAT CETAKAN BANK')->where('subgrp', 'FORMAT CETAKAN BANK 1')->first();
            if ($cetakanBank1->id ==request()->formatcetakan) {
                $query->select(
                    'pengeluarandetail.coadebet',
                    DB::raw('MAX(akunpusat.keterangancoa) AS keterangancoa'),
                    DB::raw("sum(pengeluarandetail.nominal) as nominal"),
                    DB::raw("'' as keterangan"),
                )
                ->leftJoin(DB::raw("pengeluarandetail with (readuncommitted)"), $this->table . '.pengeluaran_nobukti', 'pengeluarandetail.nobukti')
                ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarandetail.coadebet', 'akunpusat.coa')
                ->groupBy('pengeluarandetail.coadebet');
            }else {
                $query->select(
                    'pengeluarandetail.coadebet',
                    "pengeluarandetail.nobukti",
                    DB::raw('MAX(akunpusat.keterangancoa) AS keterangancoa'),
                    DB::raw("sum(pengeluarandetail.nominal) as nominal"),
                    "pengeluarandetail.keterangan",
                )
                ->leftJoin(DB::raw("pengeluarandetail with (readuncommitted)"), $this->table . '.pengeluaran_nobukti', 'pengeluarandetail.nobukti')
                ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarandetail.coadebet', 'akunpusat.coa')
                ->groupBy('pengeluarandetail.coadebet','pengeluarandetail.nobukti','pengeluarandetail.keterangan');
            }


        } else {
                
            $query->select(
                "$this->table.id",
                "$this->table.rekappengeluaran_id",
                "$this->table.nobukti",
                "$this->table.pengeluaran_nobukti",
                "$this->table.tgltransaksi",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.modifiedby",
                db::raw("cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                db::raw("cast(cast(format((cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"), 
                db::raw("pengeluaranheader.bank_id as pengeluaranbank_id"),

            )
            ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'rekappengeluarandetail.pengeluaran_nobukti', '=', 'pengeluaranheader.nobukti')
            ->leftJoin("rekappengeluaranheader", "$this->table.rekappengeluaran_id", "rekappengeluaranheader.id");
            // ->leftJoin("pengeluaranheader", "$this->table.pengeluaran_nobukti", "pengeluaranheader.nobukti");
            $this->sort($query);
            $this->filter($query);
            $this->totalNominal = $query->sum('rekappengeluarandetail.nominal');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->paginate($query);
        }
        return $query->get();
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            }else{
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            } 
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
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
    public function processStore(RekapPengeluaranHeader $rekapPengeluaranHeader, array $data): RekapPengeluaranDetail
    {
        $rekapPengeluaranDetail = new RekapPengeluaranDetail();
        $rekapPengeluaranDetail->rekappengeluaran_id = $rekapPengeluaranHeader->id;
        $rekapPengeluaranDetail->nobukti = $rekapPengeluaranHeader->nobukti;
        $rekapPengeluaranDetail->tgltransaksi =  date('Y-m-d',strtotime($data['tgltransaksi']));
        $rekapPengeluaranDetail->pengeluaran_nobukti = $data['pengeluaran_nobukti'];
        $rekapPengeluaranDetail->nominal = $data['nominal'];
        $rekapPengeluaranDetail->keterangan = $data['keterangan'];
        $rekapPengeluaranDetail->modifiedby = auth('api')->user()->name;
        $rekapPengeluaranDetail->info = html_entity_decode(request()->info);
        
        if (!$rekapPengeluaranDetail->save()) {
            throw new \Exception("Error storing rekap pengeluaran detail.");
        }

        return $rekapPengeluaranDetail;
    }
}
