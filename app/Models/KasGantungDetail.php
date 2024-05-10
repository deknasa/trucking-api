<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KasGantungDetail extends MyModel
{
    use HasFactory;

    protected $table = 'kasgantungdetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function findUpdate($id)
    {
        $query = DB::table('kasgantungdetail')->from(DB::raw('kasgantungdetail with (readuncommitted)'))->select(
            'keterangan',
            'nominal',
        )
            ->where('kasgantung_id', '=', $id);

        $detail = $query->get();

        return $detail;
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select([
                'header.id as id',
                'header.nobukti as nobukti_header',
                'header.tglbukti as tgl_header',
                'penerima.namapenerima as penerima_id',
                'bank.namabank as bank_id',
                'header.pengeluaran_nobukti',
                'header.coakaskeluar',
                'header.tglkaskeluar',
                $this->table . '.keterangan as keterangan_detail',
                $this->table . '.nominal',
                $this->table . '.coa',
                'akunpusat.keterangancoa as keterangancoa',
                $this->table . '.kasgantung_id'
            ])
                ->leftjoin(DB::raw("kasgantungheader as header with (readuncommitted)"), 'header.id', $this->table . '.kasgantung_id')
                ->leftjoin(DB::raw("penerima with (readuncommitted)"), 'header.penerima_id', 'penerima.id')
                ->leftjoin(DB::raw("akunpusat with (readuncommitted)"), $this->table .'.coa', 'akunpusat.coa')
                ->leftjoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id');

            $query->where($this->table . '.kasgantung_id', '=', request()->kasgantung_id);
        } else {
            $query
                ->select([
                    $this->table . '.keterangan',
                    $this->table . '.nominal',
                    $this->table . '.nobukti',
                    'akunpusat.keterangancoa as coa',
                ])
                ->leftJoin(
                    DB::raw("akunpusat with (readuncommitted)"),
                    $this->table . '.coa',
                    'akunpusat.coa'
                );

            $query->where($this->table . '.kasgantung_id', '=', request()->kasgantung_id);

            $this->sort($query);
            $this->filter($query);
            $this->paginate($query);
            $this->totalNominal = $query->sum('nominal');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

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
                            if ($filters['field'] == 'coa') {
                                $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
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
                            if ($filters['field'] == 'coa') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
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

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getKgtAbsensi($nobuktiAbsensi) {

        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("absensisupirproses as proses with (readuncommitted) "));

        $query
        ->select([
            $this->table . '.keterangan',
            $this->table . '.nominal',
            $this->table . '.nobukti',
            'akunpusat.keterangancoa as coa',
        ])

        ->leftJoin(DB::raw("kasgantungdetail with (readuncommitted)"), $this->table.'.nobukti','proses.kasgantung_nobukti')
        ->leftJoin(
            DB::raw("akunpusat with (readuncommitted)"),
            $this->table . '.coa',
            'akunpusat.coa'
        );
        
        $query->where('proses.nobukti', '=', $nobuktiAbsensi);
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $this->totalNominal = $query->sum($this->table . '.nominal');
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        
        return $query->get();
    }

    public function processStore(KasgantungHeader $kasGantungHeader, array $data) : KasGantungDetail
    {
        $kasgantungDetail = new KasGantungDetail();
        $kasgantungDetail->kasgantung_id = $kasGantungHeader->id;
        $kasgantungDetail->nobukti = $data['nobukti'];
        $kasgantungDetail->nominal = $data['nominal'];
        $kasgantungDetail->coa = $data['coa'];
        $kasgantungDetail->keterangan = $data['keterangan'];

        $kasgantungDetail->modifiedby = auth('api')->user()->name;
        $kasgantungDetail->info = html_entity_decode(request()->info);

        if (!$kasgantungDetail->save()) {
            throw new \Exception("Error storing kas gantung detail.");
        }

        return $kasgantungDetail;
        
    }
}
