<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HutangExtraDetail extends MyModel
{
    use HasFactory;

    protected $table = 'hutangextradetail';

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
                'header.nobukti',
                'header.tglbukti',
                'header.hutang_nobukti',
                'pelanggan.namapelanggan as pelanggan_id',
                'header.coa',
                'header.keterangan as keteranganheader',
                'header.total as totalheader',
                'supplier.namasupplier as supplier_id',
                DB::raw("(case when year(isnull($this->table.tgljatuhtempo,'1900/1/1'))<2000 then null else $this->table.tgljatuhtempo end) as tgljatuhtempo"),
                $this->table . '.total',
                $this->table . '.keterangan'
            )->leftJoin(DB::raw("hutangextraheader as header with (readuncommitted)"), 'header.id', $this->table . '.hutangextra_id')
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'header.pelanggan_id', 'pelanggan.id')
                ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'header.supplier_id', 'supplier.id');

            $query->where($this->table . '.hutangextra_id', '=', request()->hutangextra_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                DB::raw("(case when year(isnull($this->table.tgljatuhtempo,'1900/1/1'))<2000 then null else $this->table.tgljatuhtempo end) as tgljatuhtempo"),
                $this->table . '.total',
                $this->table . '.keterangan',
            );
            $this->sort($query, $this->table);
            $query->where($this->table . '.hutangextra_id', '=', request()->hutangextra_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('total');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function findAll($id)
    {
        $query = DB::table('hutangextradetail')->from(DB::raw("hutangextradetail with (readuncommitted)"))
            ->select(
                'hutangextradetail.total',
                'hutangextradetail.tgljatuhtempo',
                'hutangextradetail.keterangan',
            )
            ->where('hutangextra_id', '=', $id);


        $data = $query->get();

        return $data;
    }


    public function sort($query, $table)
    {
        return $query->orderBy($table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'total') {
                                $query = $query->whereRaw("format(hutangextradetail.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgljatuhtempo') {
                                $query = $query->whereRaw("format(hutangextradetail." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'total') {
                                $query = $query->orWhereRaw("format(hutangextradetail.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgljatuhtempo') {
                                $query = $query->orWhereRaw("format(hutangextradetail." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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

    
    public function processStore(HutangExtraHeader $hutangExtraHeader, array $data): HutangExtraDetail
    {
        $hutangExtraDetail = new HutangExtraDetail();
        $hutangExtraDetail->hutangextra_id = $hutangExtraHeader->id;
        $hutangExtraDetail->nobukti = $hutangExtraHeader->nobukti;
        $hutangExtraDetail->tgljatuhtempo = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $hutangExtraDetail->total = $data['total'];
        $hutangExtraDetail->cicilan = $data['cicilan'];
        $hutangExtraDetail->totalbayar = $data['totalbayar'];
        $hutangExtraDetail->keterangan = $data['keterangan'];
        $hutangExtraDetail->modifiedby = $data['modifiedby'];
       
        $hutangExtraDetail->save();
        
        if (!$hutangExtraDetail->save()) {
            throw new \Exception("Error storing Hutang Extra Detail.");
        }

        return $hutangExtraDetail;
    }

}
