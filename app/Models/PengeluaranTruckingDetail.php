<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PengeluaranTruckingDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluarantruckingdetail';

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
                'supir.namasupir as supir_id',
                $this->table . '.penerimaantruckingheader_nobukti',
                $this->table . '.nominal',
                $this->table . '.orderantrucking_nobukti',
                $this->table . '.keterangan',
                $this->table . '.invoice_nobukti',
            )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), $this->table . '.supir_id', 'supir.id');

            $query->where($this->table . '.pengeluarantruckingheader_id', '=', request()->pengeluarantruckingheader_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.nominal',
                $this->table . '.keterangan',
                $this->table . '.invoice_nobukti',
                $this->table . '.pengeluaranstok_nobukti',
                $this->table . '.stok_id',
                'stok.namastok as stok',
                $this->table . '.qty',
                $this->table . '.harga',
                // 'pengeluaranstokheader.id as pengeluaranstokheader_id',
                $this->table . '.orderantrucking_nobukti',
                DB::raw("container.keterangan as container"),
                'supir.namasupir as supir_id',
                $this->table . '.penerimaantruckingheader_nobukti',
            )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), $this->table . '.supir_id', 'supir.id')
                ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'pengeluarantruckingdetail.orderantrucking_nobukti', 'ot.nobukti')
                ->leftJoin(DB::raw("stok with (readuncommitted)"), 'pengeluarantruckingdetail.stok_id', 'stok.id')

                ->leftJoin(DB::raw("container with (readuncommitted)"), 'ot.container_id', 'container.id');

    
            $query->where($this->table . '.pengeluarantruckingheader_id', '=', request()->pengeluarantruckingheader_id);

            $this->sort($query);
            $this->filter($query);

            $this->totalNominal = $query->sum($this->table.'.nominal');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->paginate($query);
        }

        return $query->get();
    }

    public function getAll($id)
    {


        $query = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(
                'pengeluarantruckingdetail.pengeluarantruckingheader_id',
                'pengeluarantruckingdetail.nominal',
                'pengeluarantruckingdetail.keterangan',
                'pengeluarantruckingdetail.penerimaantruckingheader_nobukti',
                'pengeluarantruckingdetail.pengeluaranstok_nobukti',
                'pengeluarantruckingdetail.stok_id',
                'stok.namastok as stok',
                'pengeluarantruckingdetail.qty',
                'pengeluarantruckingdetail.harga',
                'pengeluaranstokheader.id as pengeluaranstokheader_id',
                DB::raw("pengeluarantruckingdetail.id as id_detail"),
                DB::raw("pengeluarantruckingdetail.invoice_nobukti as noinvoice_detail"),
                DB::raw("pengeluarantruckingdetail.orderantrucking_nobukti as nojobtrucking_detail"),
                DB::raw("container.keterangan as container_detail"),
                DB::raw("pengeluarantruckingdetail.nominal as nominal_detail"),
                DB::raw("
                    (SELECT MAX(qty)
                    FROM pengeluaranstokdetail
                    WHERE stok_id = [pengeluarantruckingdetail].[stok_id]
                    ) AS maxqty
                "),
                               
                'supir.namasupir as supir',
                'supir.id as supir_id'
                )
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'pengeluarantruckingdetail.orderantrucking_nobukti', 'ot.nobukti')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'ot.container_id', 'container.id')    
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
            ->leftJoin(DB::raw("stok with (readuncommitted)"), 'pengeluarantruckingdetail.stok_id', 'stok.id')
            ->leftJoin(DB::raw("pengeluaranstokheader with (readuncommitted)"), 'pengeluaranstokheader.nobukti', 'pengeluarantruckingdetail.pengeluaranstok_nobukti')
            ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);


        $data = $query->get();

        return $data;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else {
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
                            if ($filters['field'] == 'supir_id') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'supir_id') {
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
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


            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
    public function processStore(PengeluaranTruckingHeader $pengeluaranTruckingHeader, array $data): PengeluaranTruckingDetail
    {
        $pengeluaranTruckingDetail = new PengeluaranTruckingDetail();
        $pengeluaranTruckingDetail->pengeluarantruckingheader_id = $data['pengeluarantruckingheader_id'];
        $pengeluaranTruckingDetail->nobukti = $data['nobukti'];
        $pengeluaranTruckingDetail->supir_id = $data['supir_id'];
        $pengeluaranTruckingDetail->penerimaantruckingheader_nobukti = $data['penerimaantruckingheader_nobukti']??"";
        $pengeluaranTruckingDetail->stok_id = $data['stok_id'] ?? 0;
        $pengeluaranTruckingDetail->pengeluaranstok_nobukti = $data['pengeluaranstok_nobukti'] ?? "";
        $pengeluaranTruckingDetail->qty = $data['qty'] ?? 0;
        $pengeluaranTruckingDetail->harga = $data['harga'] ?? 0;
        $pengeluaranTruckingDetail->trado_id = $data['trado_id'] ?? 0;
        $pengeluaranTruckingDetail->keterangan = $data['keterangan'];
        $pengeluaranTruckingDetail->invoice_nobukti = $data['invoice_nobukti'];
        $pengeluaranTruckingDetail->orderantrucking_nobukti = $data['orderantrucking_nobukti'];
        $pengeluaranTruckingDetail->nominal = $data['nominal'];
        $pengeluaranTruckingDetail->modifiedby = auth('api')->user()->name;
        
        if (!$pengeluaranTruckingDetail->save()) {
            throw new \Exception("Error storing pengeluaran Trucking Detail.");
        }
        return $pengeluaranTruckingDetail;
    }
            
}
