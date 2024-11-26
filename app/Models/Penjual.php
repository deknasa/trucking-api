<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class Penjual extends MyModel
{
    use HasFactory;

    protected $table = 'penjual';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get(){
        $this->setRequestParameters();


        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        // dd(request());
        $query = DB::table($this->table)->select(
            'penjual.id',
            'penjual.namapenjual',
            'penjual.alamat',
            'penjual.nohp',
            'ketcoa.keterangancoa as coa',
            'parameter_statusaktif.memo as statusaktif',
            'parameter_statusaktif.text as statusaktifText',
            'penjual.modifiedby',
            'penjual.created_at',
            'penjual.updated_at',
            DB::raw("'Laporan Penjual' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
        ->leftJoin('parameter as parameter_statusaktif', 'penjual.statusaktif', '=', 'parameter_statusaktif.id')
        ->leftJoin('akunpusat as ketcoa', 'penjual.coa', '=', 'ketcoa.coa');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();
            
        return $data;
    }

    public function select2Coa($request){
        $q = $request->q;

        $coa = AkunPusat::from(db::Raw('akunpusat with (readuncommitted)'))
        ->select('coa', 'keterangancoa')
        ->where('keterangancoa', 'like', '%'.$q.'%')
        ->get();

        return $coa;
    }

    public function select2StatusAktif($request){
        $q = $request->q;

        $statusaktif = Parameter::from(db::Raw("parameter with (readuncommitted)"))
        ->select('id', 'text')
        ->where('grp', '=', 'STATUS AKTIF')
        ->where('subgrp', '=', 'STATUS AKTIF')
        ->where('text', 'like', '%'.$q.'%')
        ->get();

        return $statusaktif;
    }

    public function sort($query){
        if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('ketcoa.keterangancoa', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = []){
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter_statusaktif.text', '=', $filters['data']);    
                        } else if ($filters['field'] == 'coa') {
                            $query = $query->where('ketcoa.keterangancoa', 'LIKE', '%' . $filters['data'] . '%');
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'coa') {
                                $query = $query->orWhere('ketcoa.keterangancoa', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function paginate($query){
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }


    public function processStore(array $data, Penjual $penjual): Penjual {
        $penjual->namapenjual = trim($data['namapenjual']);
        $penjual->alamat = $data['alamat'];
        $penjual->nohp = $data['nohp'];
        $penjual->coa = $data['coa'];
        $penjual->statusaktif = $data['statusaktif'];
        $penjual->modifiedby = auth('api')->user()->name;

        if (!$penjual->save()) {
            throw new \Exception("Error storing service in header");
        }
        // dd($penjual);
        return $penjual;
    }

    public function getById($id){
        $query = DB::table($this->table)->select(
            'penjual.id',
            'penjual.namapenjual',
            'penjual.alamat',
            'penjual.nohp',
            'ketcoa.coa as coa',
            'ketcoa.keterangancoa as coaText',
            'parameter_statusaktif.id as statusaktif',
            'parameter_statusaktif.text as statusaktifText',
            'penjual.modifiedby',
            'penjual.created_at',   
            'penjual.updated_at'
        )
        ->leftJoin('akunpusat as ketcoa', 'penjual.coa', '=', 'ketcoa.coa')
        ->leftJoin('parameter as parameter_statusaktif', 'penjual.statusaktif', '=', 'parameter_statusaktif.id')
        ->where('penjual.id', $id);
        
        $data = $query->first();
        return $data;
    }

    public function processUpdate(Penjual $penjual, array $data): Penjual {
        $penjual->namapenjual = trim($data['namapenjual']);
        $penjual->alamat = $data['alamat'];
        $penjual->nohp = $data['nohp'];
        $penjual->coa = $data['coa'];
        $penjual->statusaktif = $data['statusaktif'];
        $penjual->modifiedby = auth('api')->user()->name;

        if (!$penjual->save()) {
            throw new \Exception("Error update service in header.");
        }

        return $penjual;
    }

    public function processDestroy(Penjual $penjual): Penjual {
        $penjual = $penjual->lockAndDestroy($penjual->id);

        $this->setRequestParameters();
        $params = $this->params;
        // dd($this->params);

        return $penjual;

        // return [
        //     "res" => $penjual,
        //     "params" => $params
        // ];
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.namapenjual,
            $this->table.alamat,
            $this->table.nohp,
            $this->table.coa,
            'parameter_statusaktif.text as statusaktif',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at
            ")

        )
            ->leftJoin('parameter as parameter_statusaktif', "penjual.statusaktif", '=', 'parameter_statusaktif.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->longText('namapenjual')->nullable();
            $table->longText('alamat')->nullable();
            $table->string('nohp', 50)->nullable();
            $table->string('coa', 150)->nullable();
            $table->string('statusaktif')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        $this->setRequestParameters();
        // dd($this->params);
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        
        // dd($models->get());
        DB::table($temp)->insertUsing(['id', 'namapenjual', 'alamat', 'nohp', 'coa',  'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);
        // $a = DB::table($temp)->get();
        // dd($a);
        return  $temp;
    }

}
