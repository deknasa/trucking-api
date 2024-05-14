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
                "debet.keterangancoa as coadebet",
                "$this->table.bank",
                "$this->table.tgljatuhtempo",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.noinvoice as invoice_nobukti",
            )
                ->leftJoin(DB::raw("pengeluaranheader as header with (readuncommitted)"), "header.id", "$this->table.pengeluaran_id")
                ->leftJoin(DB::raw("akunpusat as debet with (readuncommitted)"), "debet.coa", "$this->table.coadebet");
            $query->where($this->table . ".pengeluaran_id", "=", request()->pengeluaran_id);

            $pengeluaranDetail = $query->get();
        } else {

            $query->select(
                "$this->table.pengeluaran_id",
                "$this->table.nobukti",
                "$this->table.nowarkat",
                "$this->table.nominal",
                "$this->table.keterangan",
                "$this->table.noinvoice",
                "$this->table.bank",
                DB::raw("(case when year(isnull($this->table.bulanbeban,'1900/1/1'))<2000 then null else $this->table.bulanbeban end) as bulanbeban"),
                DB::raw("(case when year(isnull($this->table.tgljatuhtempo,'1900/1/1'))<2000 then null else $this->table.tgljatuhtempo end) as tgljatuhtempo"),
                "debet.keterangancoa as coadebet",
                "kredit.keterangancoa as coakredit",

            )
                ->leftJoin(DB::raw("akunpusat as debet with (readuncommitted)"), "$this->table.coadebet", "debet.coa")
                ->leftJoin(DB::raw("akunpusat as kredit with (readuncommitted)"), "$this->table.coakredit", "kredit.coa");

            
            $this->sort($query);

            $query->where($this->table . ".pengeluaran_id", "=", request()->pengeluaran_id);
            $this->filter($query);
            
            $this->totalNominal = $query->sum('nominal');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

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
                'pengeluarandetail.noinvoice',
                'pengeluarandetail.bank',
                'akunpusat.keterangancoa as ketcoadebet',
                DB::raw("(case when year(cast(pengeluarandetail.bulanbeban as datetime))='1900' then '' else format(pengeluarandetail.bulanbeban,'yyyy-MM-dd') end) as bulanbeban"),
            )
            ->join(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarandetail.coadebet', 'akunpusat.coa')
            ->where("pengeluarandetail.pengeluaran_id", $id)
            ->orderBy('pengeluarandetail.id');

        $data = $query->get();

        return $data;
    }


    public function sort($query)
    {
        if($this->params['sortIndex'] == 'coadebet'){
            return $query->orderBy('debet.keterangancoa', $this->params['sortOrder']);
        } else if($this->params['sortIndex'] == 'coakredit'){
            return $query->orderBy('kredit.keterangancoa', $this->params['sortOrder']);
        }else{
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
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
                            }  else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'bulanbeban' || $filters['field'] == 'tgljatuhtempo') {
                                $query = $query->whereRaw("format((case when year(isnull($this->table.".$filters['field'].",'1900/1/1'))<2000 then null else pengeluarandetail.".$filters['field']." end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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
                            }  else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'bulanbeban' || $filters['field'] == 'tgljatuhtempo') {
                                $query = $query->orWhereRaw("format((case when year(isnull($this->table.".$filters['field'].",'1900/1/1'))<2000 then null else pengeluarandetail.".$filters['field']." end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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


    public function processStore(PengeluaranHeader $pengeluaranHeader, array $data): PengeluaranDetail
    {
        $pengeluaranDetail = new PengeluaranDetail();
        $pengeluaranDetail->pengeluaran_id = $data['pengeluaran_id'];
        $pengeluaranDetail->nobukti = $data['nobukti'];
        $pengeluaranDetail->nowarkat = $data['nowarkat'] ?? '';
        $pengeluaranDetail->tgljatuhtempo = $data['tgljatuhtempo'] ?? '';
        $pengeluaranDetail->nominal = $data['nominal'] ?? '';
        $pengeluaranDetail->coadebet = $data['coadebet'] ?? '';
        $pengeluaranDetail->coakredit = $data['coakredit'] ?? '';
        $pengeluaranDetail->keterangan = $data['keterangan'] ?? '';
        $pengeluaranDetail->noinvoice = $data['noinvoice'] ?? '';
        $pengeluaranDetail->bank = $data['bank'] ?? '';
        $pengeluaranDetail->modifiedby = $data['modifiedby'];
       
        $pengeluaranDetail->save();
        
        if (!$pengeluaranDetail->save()) {
            throw new \Exception("Error storing Pengeluaran Detail.");
        }

        return $pengeluaranDetail;
    }

    
    public function getProsesKBTAbsensi($nobukti) {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("absensisupirapprovalproses as proses with (readuncommitted) "));

        $query->select(
            "$this->table.pengeluaran_id",
            "$this->table.nobukti",
            "$this->table.nowarkat",
            "$this->table.nominal",
            "$this->table.keterangan",
            "$this->table.noinvoice",
            "$this->table.bank",
            DB::raw("(case when year(isnull($this->table.bulanbeban,'1900/1/1'))<2000 then null else $this->table.bulanbeban end) as bulanbeban"),
            DB::raw("(case when year(isnull($this->table.tgljatuhtempo,'1900/1/1'))<2000 then null else $this->table.tgljatuhtempo end) as tgljatuhtempo"),
            "debet.keterangancoa as coadebet",
            "kredit.keterangancoa as coakredit",

        )
        ->leftJoin(DB::raw("$this->table with (readuncommitted)"), 'proses.pengeluaran_nobukti',$this->table.'.nobukti')
        ->leftJoin(DB::raw("akunpusat as debet with (readuncommitted)"), "$this->table.coadebet", "debet.coa")
        ->leftJoin(DB::raw("akunpusat as kredit with (readuncommitted)"), "$this->table.coakredit", "kredit.coa");
        

        
        $this->sort($query);

        $query->where( "proses.nobukti", "=", $nobukti);
        $this->filter($query);
        
        $this->totalNominal = $query->sum($this->table .'.nominal');
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);
        return $query->get();
    }
}
