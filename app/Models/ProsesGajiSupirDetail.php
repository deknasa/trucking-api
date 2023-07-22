<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProsesGajiSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'prosesgajisupirdetail';

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
                $this->table . '.nominal',
                $this->table . '.keterangan as keterangan_detail',
                'prosesgajisupirheader.keterangan',
            )
                ->leftJoin(DB::raw("prosesgajisupirheader with (readuncommitted)"), $this->table . '.prosesgajisupir_id', 'prosesgajisupirheader.id');
            $query->where($this->table . '.prosesgajisupir_id', '=', request()->prosesgajisupir_id);
        } else {
            $query->select(
                $this->table . '.gajisupir_nobukti',
                'supir.namasupir as supir_id',
                'trado.kodetrado as trado_id',
                'gajisupirheader.total',
                'gajisupirheader.uangjalan',
                'gajisupirheader.bbm',
                'gajisupirheader.uangmakanharian',
                'gajisupirheader.potonganpinjaman',
                'gajisupirheader.potonganpinjamansemua',
                'gajisupirheader.deposito',
                'gajisupirheader.komisisupir',
                'gajisupirheader.tolsupir',
            )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), $this->table . '.supir_id', 'supir.id')
                ->leftJoin(DB::raw("trado with (readuncommitted)"), $this->table . '.trado_id', 'trado.id')
                ->leftJoin(DB::raw("gajisupirheader with (readuncommitted)"), $this->table . '.gajisupir_nobukti', 'gajisupirheader.nobukti');

            $this->sort($query);
            $query->where($this->table . '.prosesgajisupir_id', '=', request()->prosesgajisupir_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('gajisupirheader.total');
            $this->totalUangJalan = $query->sum('gajisupirheader.uangjalan');
            $this->totalBBM = $query->sum('gajisupirheader.bbm');
            $this->totalUangMakan = $query->sum('gajisupirheader.uangmakanharian');
            $this->totalPinjaman = $query->sum('gajisupirheader.potonganpinjaman');
            $this->totalPinjamanSemua = $query->sum('gajisupirheader.potonganpinjamansemua');
            $this->totalDeposito = $query->sum('gajisupirheader.deposito');
            $this->totalKomisi = $query->sum('gajisupirheader.komisisupir');
            $this->totalTol = $query->sum('gajisupirheader.tolsupir');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'trado_id') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'total') {
            return $query->orderBy('gajisupirheader.total', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'uangjalan') {
            return $query->orderBy('gajisupirheader.uangjalan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'bbm') {
            return $query->orderBy('gajisupirheader.bbm', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'uangmakanharian') {
            return $query->orderBy('gajisupirheader.uangmakanharian', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'potonganpinjaman') {
            return $query->orderBy('gajisupirheader.potonganpinjaman', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'potonganpinjamansemua') {
            return $query->orderBy('gajisupirheader.potonganpinjamansemua', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'deposito') {
            return $query->orderBy('gajisupirheader.deposito', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'komisisupir') {
            return $query->orderBy('gajisupirheader.komisisupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tolsupir') {
            return $query->orderBy('gajisupirheader.tolsupir', $this->params['sortOrder']);
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
                            } else if ($filters['field'] == 'trado_id') {
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->whereRaw("format(gajisupirheader.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->whereRaw("format(gajisupirheader.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'bbm') {
                                $query = $query->whereRaw("format(gajisupirheader.bbm, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangmakanharian') {
                                $query = $query->whereRaw("format(gajisupirheader.uangmakanharian, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjaman') {
                                $query = $query->whereRaw("format(gajisupirheader.potonganpinjaman, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjamansemua') {
                                $query = $query->whereRaw("format(gajisupirheader.potonganpinjamansemua, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'deposito') {
                                $query = $query->whereRaw("format(gajisupirheader.deposito, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'komisisupir') {
                                $query = $query->whereRaw("format(gajisupirheader.komisisupir, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tolsupir') {
                                $query = $query->whereRaw("format(gajisupirheader.tolsupir, '#,#0.00') LIKE '%$filters[data]%'");
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
                            } else if ($filters['field'] == 'trado_id') {
                                $query = $query->orWhere('trado.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->orWhereRaw("format(gajisupirheader.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->orWhereRaw("format(gajisupirheader.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'bbm') {
                                $query = $query->orWhereRaw("format(gajisupirheader.bbm, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangmakanharian') {
                                $query = $query->orWhereRaw("format(gajisupirheader.uangmakanharian, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjaman') {
                                $query = $query->orWhereRaw("format(gajisupirheader.potonganpinjaman, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjamansemua') {
                                $query = $query->orWhereRaw("format(gajisupirheader.potonganpinjamansemua, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'deposito') {
                                $query = $query->orWhereRaw("format(gajisupirheader.deposito, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'komisisupir') {
                                $query = $query->orWhereRaw("format(gajisupirheader.komisisupir, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tolsupir') {
                                $query = $query->orWhereRaw("format(gajisupirheader.tolsupir, '#,#0.00') LIKE '%$filters[data]%'");
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

    public function processStore(ProsesGajiSupirHeader $prosesGajiSupirHeader, array $data): ProsesGajiSupirDetail
    {
        $prosesGajiSupirDetail = new ProsesGajiSupirDetail();
        $prosesGajiSupirDetail->prosesgajisupir_id = $prosesGajiSupirHeader->id;
        $prosesGajiSupirDetail->nobukti = $prosesGajiSupirHeader->nobukti;
        $prosesGajiSupirDetail->gajisupir_nobukti = $data['gajisupir_nobukti'];
        $prosesGajiSupirDetail->supir_id = $data['supir_id'];
        $prosesGajiSupirDetail->trado_id = $data['trado_id'];
        $prosesGajiSupirDetail->nominal = $data['nominal'];
        $prosesGajiSupirDetail->keterangan = $data['keterangan'];
        $prosesGajiSupirDetail->modifiedby = auth('api')->user()->name;
        
        if (!$prosesGajiSupirDetail->save()) {
            throw new \Exception("Error storing Proses Gaji Supir Detail.");
        }

        return $prosesGajiSupirDetail;
    }
}
