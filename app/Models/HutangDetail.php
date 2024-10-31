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
                DB::raw("(case when year(isnull($this->table.tgljatuhtempo,'1900/1/1'))<2000 then null else $this->table.tgljatuhtempo end) as tgljatuhtempo"),
                $this->table . '.total',
                $this->table . '.keterangan'
            );
                
            $query->where($this->table . '.hutang_id', '=', request()->hutang_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                DB::raw("(case when year(isnull($this->table.tgljatuhtempo,'1900/1/1'))<2000 then null else $this->table.tgljatuhtempo end) as tgljatuhtempo"),
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

    
    public function getHutangFromHutangExtra($nobukti)
    {
        $this->setRequestParameters();
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                $this->table . '.nobukti',
                DB::raw("(case when year(isnull($this->table.tgljatuhtempo,'1900/1/1'))<2000 then null else $this->table.tgljatuhtempo end) as tgljatuhtempo"),
                $this->table . '.total',
                $this->table . '.keterangan',
            );

        $this->sort($query, 'hutangdetail');
        $query->where($this->table . '.nobukti', '=',$nobukti);
        $this->filter($query);
        $this->totalNominal = $query->sum('total');
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);

        return $query->get();
    }


    public function getHistory()
    {
        $this->setRequestParameters();

        $hutang = DB::table("hutangheader")->from(DB::raw("hutangheader with (readuncommitted)"))->where('id', request()->hutang_id)->first();
        if ($hutang != null) {

            $query = DB::table("pelunasanhutangdetail")->from(DB::raw("pelunasanhutangdetail with (readuncommitted)"));

            $query->select(
                'pelunasanhutangdetail.nobukti as nobukti_bayar',
                'pelunasanhutangdetail.hutang_nobukti',
                'pelunasanhutangdetail.keterangan as keterangan_bayar',
                'pelunasanhutangdetail.nominal as nominal_bayar',
                'pelunasanhutangdetail.potongan',
                db::raw("cast((format(pelunasanhutangheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpelunasan"),
                db::raw("cast(cast(format((cast((format(pelunasanhutangheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpelunasan"),
                
            )
            ->join(DB::raw("pelunasanhutangheader with (readuncommitted)"), 'pelunasanhutangheader.nobukti', 'pelunasanhutangdetail.nobukti');

            $query->where('pelunasanhutangdetail.hutang_nobukti', '=', $hutang->nobukti);

            $this->totalNominal = $query->sum('nominal');
            $this->totalPotongan = $query->sum('potongan');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query, 'pelunasanhutangdetail');
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
        } else if ($this->params['sortIndex'] == 'nominal_bayar') {
            return $query->orderBy($table . '.nominal', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'potongan') {
            return $query->orderBy($table . '.potongan', $this->params['sortOrder']);
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
                                $query = $query->where('pelunasanhutangdetail.hutang_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_bayar') {
                                $query = $query->where('pelunasanhutangdetail.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nobukti_bayar') {
                                $query = $query->where('pelunasanhutangdetail.nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal_bayar') {
                                $query = $query->whereRaw("format(pelunasanhutangdetail.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potongan') {
                                $query = $query->whereRaw("format(pelunasanhutangdetail.potongan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->whereRaw("format(hutangdetail.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgljatuhtempo') {
                                $query = $query->whereRaw("format(hutangdetail." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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
                                $query = $query->orWhere('pelunasanhutangdetail.hutang_nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_bayar') {
                                $query = $query->orWhere('pelunasanhutangdetail.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nobukti_bayar') {
                                $query = $query->orWhere('pelunasanhutangdetail.nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal_bayar') {
                                $query = $query->orWhereRaw("format(pelunasanhutangdetail.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potongan') {
                                $query = $query->orWhereRaw("format(pelunasanhutangdetail.potongan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->orWhereRaw("format(hutangdetail.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgljatuhtempo') {
                                $query = $query->orWhereRaw("format(hutangdetail." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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

    public function processStore(HutangHeader $hutangHeader, array $data): HutangDetail
    {
        $hutangdetail = new HutangDetail();
        $hutangdetail->hutang_id = $data['hutang_id'];
        $hutangdetail->nobukti = $data['nobukti'];
        $hutangdetail->tgljatuhtempo = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $hutangdetail->total = $data['total'];
        $hutangdetail->cicilan = $data['cicilan'];
        $hutangdetail->totalbayar = $data['totalbayar'];
        $hutangdetail->keterangan = $data['keterangan'];
        $hutangdetail->modifiedby = $data['modifiedby'];
       
        $hutangdetail->save();
        
        if (!$hutangdetail->save()) {
            throw new \Exception("Error storing Hutang Detail.");
        }

        return $hutangdetail;
    }

}
