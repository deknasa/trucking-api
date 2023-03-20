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
                'header.nobukti',
                'header.tglbukti',
                'supir.namasupir as supir_id',
                'trado.kodetrado as trado_id',
                $this->table . '.gajisupir_nobukti',
                $this->table . '.nominal',
                $this->table . '.keterangan as keterangan_detail'
            )
            ->leftJoin(DB::raw("prosesgajisupirheader as header with (readuncommitted)"),'header.id',$this->table . '.prosesgajisupir_id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"),$this->table . '.supir_id','supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"),$this->table . '.trado_id','trado.id');

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
            ->leftJoin(DB::raw("supir with (readuncommitted)"),$this->table . '.supir_id','supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"),$this->table . '.trado_id','trado.id')
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
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
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
                                $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->where('gajisupirheader.total', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->where('gajisupirheader.uangjalan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bbm') {
                                $query = $query->where('gajisupirheader.bbm', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangmakanharian') {
                                $query = $query->where('gajisupirheader.uangmakanharian', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'potonganpinjaman') {
                                $query = $query->where('gajisupirheader.potonganpinjaman', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'potonganpinjamansemua') {
                                $query = $query->where('gajisupirheader.potonganpinjamansemua', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'deposito') {
                                $query = $query->where('gajisupirheader.deposito', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'komisisupir') {
                                $query = $query->where('gajisupirheader.komisisupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tolsupir') {
                                $query = $query->where('gajisupirheader.tolsupir', 'LIKE', "%$filters[data]%");
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
                            }  else if ($filters['field'] == 'total') {
                                $query = $query->orWhere('gajisupirheader.total', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->orWhere('gajisupirheader.uangjalan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bbm') {
                                $query = $query->orWhere('gajisupirheader.bbm', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangmakanharian') {
                                $query = $query->orWhere('gajisupirheader.uangmakanharian', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'potonganpinjaman') {
                                $query = $query->orWhere('gajisupirheader.potonganpinjaman', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'potonganpinjamansemua') {
                                $query = $query->orWhere('gajisupirheader.potonganpinjamansemua', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'deposito') {
                                $query = $query->orWhere('gajisupirheader.deposito', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'komisisupir') {
                                $query = $query->orWhere('gajisupirheader.komisisupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tolsupir') {
                                $query = $query->orWhere('gajisupirheader.tolsupir', 'LIKE', "%$filters[data]%");
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

