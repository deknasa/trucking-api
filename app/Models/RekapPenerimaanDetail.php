<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RekapPenerimaanDetail extends MyModel
{
    use HasFactory;

    protected $table = 'rekappenerimaandetail';

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
                'penerimaandetail.coakredit',
                DB::raw('MAX(akunpusat.keterangancoa) AS keterangancoa'),
                DB::raw("sum(penerimaandetail.nominal) as nominal"),
                DB::raw("'' as keterangan"),
            )
            ->leftJoin(DB::raw("penerimaandetail with (readuncommitted)"), $this->table . '.penerimaan_nobukti', 'penerimaandetail.nobukti')
             ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'penerimaandetail.coakredit', 'akunpusat.coa')
            ->groupBy('penerimaandetail.coakredit');
                
            $query->where($this->table . ".rekappenerimaan_id", "=", request()->rekappenerimaan_id);

        } else {
            $query->select(
                "$this->table.id",
                "$this->table.rekappenerimaan_id",
                "$this->table.nobukti",
                "$this->table.penerimaan_nobukti",
                "$this->table.tgltransaksi",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.modifiedby",
                db::raw("cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaanheader"),
            db::raw("cast(cast(format((cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaanheader"), 
            'penerimaanheader.bank_id as penerimaanbank_id',

            )
            ->leftJoin(DB::raw("penerimaanheader with (readuncommitted)"), 'rekappenerimaandetail.penerimaan_nobukti', '=', 'penerimaanheader.nobukti')
            ->leftJoin('rekappenerimaanheader', "$this->table.rekappenerimaan_id", 'rekappenerimaanheader.id');
            // ->leftJoin('penerimaanheader', "$this->table.penerimaan_nobukti", 'penerimaanheader.nobukti');
            $query->where($this->table . ".rekappenerimaan_id", "=", request()->rekappenerimaan_id);
            $this->totalNominal = $query->sum('rekappenerimaandetail.nominal');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
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

    public function processStore(RekapPenerimaanHeader $rekapPenerimaanHeader, array $data): RekapPenerimaanDetail
    {
        $rekapPenerimaanDetail = new RekapPenerimaanDetail();
        $rekapPenerimaanDetail->rekappenerimaan_id = $rekapPenerimaanHeader->id;
        $rekapPenerimaanDetail->nobukti = $rekapPenerimaanHeader->nobukti;
        $rekapPenerimaanDetail->tgltransaksi =  date('Y-m-d', strtotime($data['tgltransaksi_detail']));
        $rekapPenerimaanDetail->penerimaan_nobukti = $data['penerimaan_nobukti'];
        $rekapPenerimaanDetail->nominal = $data['nominal'];
        $rekapPenerimaanDetail->keterangan = $data['keterangandetail'];
        $rekapPenerimaanDetail->modifiedby = auth('api')->user()->name;
        $rekapPenerimaanDetail->info = html_entity_decode(request()->info);

        if (!$rekapPenerimaanDetail->save()) {
            throw new \Exception("Error storing rekap penerimaan detail.");
        }
        
        return $rekapPenerimaanDetail;

    }

}
