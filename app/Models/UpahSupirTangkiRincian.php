<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpahSupirTangkiRincian extends MyModel
{
    use HasFactory;

    protected $table = 'upahsupirtangkirincian';

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

        $query = DB::table($this->table)->from(DB::raw("$this->table as detail with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {

            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();

            $query->select(
                'kotadari.keterangan as kotadari',
                'kotasampai.keterangan as kotasampai',
                'header.jarak',
                'triptangki.keterangan as triptangki_id',
                'header.tglmulaiberlaku',
                'detail.nominalsupir',
                DB::raw("'Laporan Upah Supir Tangki' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
                ->leftJoin(DB::raw("upahsupirtangki as header with (readuncommitted)"), 'header.id', 'detail.upahsupirtangki_id')
                ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'header.kotadari_id')
                ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'header.kotasampai_id')
                ->leftJoin(DB::raw("triptangki with (readuncommitted)"), 'triptangki.id', 'detail.triptangki_id');

            $upahsupir = $query->get();
        } else {
            $query->select(
                'triptangki.keterangan as triptangki_id',
                'detail.nominalsupir',
            )
                ->leftJoin(DB::raw("triptangki with (readuncommitted)"), 'triptangki.id', 'detail.triptangki_id');
            $query->where('detail.upahsupirtangki_id', '=', request()->upahsupirtangki_id);
            $this->sort($query);
            $this->filter($query);
            $this->totalNominal = $query->sum('detail.nominalsupir');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }
    
    public function getAll($id)
    {
        $query = DB::table('upahsupirtangkirincian')->from(DB::raw("upahsupirtangkirincian with (readuncommitted)"))
            ->select(
                'upahsupirtangkirincian.triptangki_id',
                'triptangki.kodetangki as triptangki',
                'upahsupirtangkirincian.nominalsupir',
            )
            ->leftJoin('triptangki', 'triptangki.id', 'upahsupirtangkirincian.triptangki_id')
            ->where('upahsupirtangki_id', '=', $id)
            ->orderBy('triptangki.id', 'asc');


        $data = $query->get();


        return $data;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'triptangki_id') {
            return $query->orderBy('triptangki.keterangan', $this->params['sortOrder']);
        } else {
            return $query->orderBy('detail.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'triptangki_id') {
                                $query = $query->where('triptangki.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominalsupir') {
                                $query = $query->whereRaw("format(detail.nominalsupir, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where('detail.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'triptangki_id') {
                                $query = $query->orWhere('triptangki.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominalsupir') {
                                $query = $query->orWhereRaw("format(detail.nominalsupir, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere('detail.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
    public function processStore(UpahSupirTangki $upahsupir, array $data): UpahSupirTangkiRincian
    {
        $upahSupirRincian = new UpahSupirTangkiRincian();
        $upahSupirRincian->upahsupirtangki_id = $data['upahsupirtangki_id'];
        $upahSupirRincian->triptangki_id = $data['triptangki_id'];
        $upahSupirRincian->nominalsupir = $data['nominalsupir'];
        $upahSupirRincian->modifiedby = auth('api')->user()->name;
        $upahSupirRincian->info = html_entity_decode(request()->info);

        if (!$upahSupirRincian->save()) {
            throw new \Exception("Error storing upah supir tangki detail.");
        }

        return $upahSupirRincian;
    }
    
    public function setUpRow()
    {
        $query = DB::table('triptangki')->select(
            'triptangki.kodetangki as triptangki',
            'triptangki.id as triptangki_id',
            db::Raw("0 as nominalsupir"),
        )
        ->where('triptangki.statusaktif',1)
            ->orderBy('triptangki.kodetangki', 'asc');

        return $query->get();
    }

    
    public function listpivot($dari, $sampai)
    {
        $query = DB::table("upahsupirtangkirincian")->from(DB::raw("upahsupirtangkirincian as detail with (readuncommitted)"))->select(
            'header.id as id_header',
            'header.penyesuaian',
            'header.tglmulaiberlaku',
            'header.jarak',
            'dari.kodekota as dari',
            'sampai.kodekota as sampai',
            'triptangki.keterangan as triptangki',
            'detail.nominalsupir'
        )
            ->leftJoin('upahsupirtangki as header', 'header.id',  'detail.upahsupirtangki_id')
            ->leftJoin('kota as dari', 'header.kotadari_id', 'dari.id')
            ->leftJoin('kota as sampai', 'header.kotasampai_id', 'sampai.id')
            ->leftJoin('triptangki', 'detail.triptangki_id', 'triptangki.id');
            $query->whereBetween('header.tglmulaiberlaku', [$dari, $sampai]);

        return $query->get();
    }
}
