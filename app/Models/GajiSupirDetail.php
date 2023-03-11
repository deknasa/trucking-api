<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GajiSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'gajisupirdetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];


    public function get()
    {

        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                'header.id',
                'header.nobukti',
                'header.tglbukti',
                'header.nominal',
                'supir.namasupir as supir',
                $this->table . '.suratpengantar_nobukti',
                'suratpengantar.tglsp',
                'suratpengantar.nosp',
                'suratpengantar.nocont',
                'sampai.keterangan as sampai',
                'dari.keterangan as dari',
                $this->table . '.gajisupir',
                $this->table . '.gajikenek',

            )
                ->leftJoin(DB::raw("gajisupirheader as header with (readuncommitted)"), 'header.id', $this->table . '.gajisupir_id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'header.supir_id', 'supir.id')
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), $this->table . '.suratpengantar_nobukti', 'suratpengantar.nobukti')
                ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'suratpengantar.dari_id', 'dari.id')
                ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'sampai.id');

            $query->where($this->table . ".gajisupir_id", "=", request()->gajisupir_id);
        } else {

            $query->select(
                $this->table . '.nobukti',
                $this->table . '.suratpengantar_nobukti',
                'suratpengantar.tglsp',
                'suratpengantar.nosp',
                'suratpengantar.nocont',
                'sampai.keterangan as sampai',
                'dari.keterangan as dari',
                $this->table . '.gajisupir',
                $this->table . '.gajikenek',
                $this->table . '.komisisupir',

            )
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), $this->table . '.suratpengantar_nobukti', 'suratpengantar.nobukti')
                ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'suratpengantar.dari_id', 'dari.id')
                ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'sampai.id');

            $this->sort($query);

            $query->where($this->table . ".gajisupir_id", "=", request()->gajisupir_id);
            $this->filter($query);

            $this->totalGajiSupir = $query->sum($this->table . '.gajisupir');
            $this->totalGajiKenek = $query->sum($this->table . '.gajikenek');
            $this->totalKomisiSupir = $query->sum($this->table . '.komisisupir');
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
                            if ($filters['field'] == 'tglsp') {
                                $query = $query->where('suratpengantar.tglsp', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nosp') {
                                $query = $query->where('suratpengantar.nosp', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nocont') {
                                $query = $query->where('suratpengantar.nocont', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'sampai') {
                                $query = $query->where('sampai.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'dari') {
                                $query = $query->where('dari.keterangan', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":

                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'tglsp') {
                                $query = $query->orWhere('suratpengantar.tglsp', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nosp') {
                                $query = $query->orWhere('suratpengantar.nosp', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nocont') {
                                $query = $query->orWhere('suratpengantar.nocont', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'sampai') {
                                $query = $query->orWhere('sampai.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'dari') {
                                $query = $query->orWhere('dari.keterangan', 'LIKE', "%$filters[data]%");
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
