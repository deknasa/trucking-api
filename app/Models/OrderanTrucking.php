<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderanTrucking extends MyModel
{
    use HasFactory;

    protected $table = 'orderantrucking';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function get()
    {
        $this->setRequestParameters();
        $query = DB::table($this->table)
            ->select(
                'orderantrucking.id',
                'orderantrucking.nobukti',
                'orderantrucking.tglbukti',
                'container.keterangan as container_id',
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'pelanggan.namapelanggan as pelanggan_id',
                'tarif.tujuan as tarif_id',
                'orderantrucking.nominal',
                'orderantrucking.nojobemkl',
                'orderantrucking.nocont',
                'orderantrucking.noseal',
                'orderantrucking.nojobemkl2',
                'orderantrucking.nocont2',
                'orderantrucking.noseal2',
                'parameter.text as statuslangsir',
                'param2.text as statusperalihan',
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
            )
            ->leftJoin('tarif', 'orderantrucking.tarif_id', '=', 'tarif.id')
            ->leftJoin('container', 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin('agen', 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin('jenisorder', 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin('pelanggan', 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin('parameter', 'orderantrucking.statuslangsir', '=', 'parameter.id')
            ->leftJoin('parameter AS param2', 'orderantrucking.statusperalihan', '=', 'param2.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


        $data = $query->get();

        return $data;
    }

    public function find($id)
    {
        $query = DB::table('orderantrucking')
            ->select(
                'orderantrucking.id',
                'orderantrucking.nobukti',
                'orderantrucking.tglbukti',
                'orderantrucking.container_id',
                'container.keterangan as container',
                'orderantrucking.agen_id',
                'agen.namaagen as agen',
                'orderantrucking.jenisorder_id',
                'jenisorder.keterangan as jenisorder',
                'orderantrucking.pelanggan_id',
                'pelanggan.namapelanggan as pelanggan',
                'orderantrucking.tarif_id',
                'tarif.tujuan as tarif',
                'orderantrucking.nominal',
                'orderantrucking.nojobemkl',
                'orderantrucking.nocont',
                'orderantrucking.noseal',
                'orderantrucking.nojobemkl2',
                'orderantrucking.nocont2',
                'orderantrucking.noseal2',
                'orderantrucking.statuslangsir',
                'orderantrucking.statusperalihan',
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
            )
            ->leftJoin('tarif', 'orderantrucking.tarif_id', '=', 'tarif.id')
            ->leftJoin('container', 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin('agen', 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin('jenisorder', 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin('pelanggan', 'orderantrucking.pelanggan_id', '=', 'pelanggan.id');

            $data = $query->first();

        return $data;
    }

    public function agen() {
        return $this->belongsTo(Agen::class, 'agen_id');
    }

    public function container() {
        return $this->belongsTo(Container::class, 'container_id');
    }

    public function jenisorder() {
        return $this->belongsTo(JenisOrder::class, 'jenisorder_id');
    }

    public function pelanggan() {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function tarif() {
        return $this->belongsTo(Tarif::class, 'tarif_id');
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
            "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'container.keterangan as container_id',
            'agen.namaagen as agen_id',
            'jenisorder.keterangan as jenisorder_id',
            'pelanggan.namapelanggan as pelanggan_id',
            'tarif.tujuan as tarif_id',
            $this->table.nominal,
            $this->table.nojobemkl,
            $this->table.nocont,
            $this->table.noseal,
            $this->table.nojobemkl2,
            $this->table.nocont2,
            $this->table.noseal2,
            'parameter.text as statuslangsir',
            'param2.text as statusperalihan',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
        ->leftJoin('tarif', 'orderantrucking.tarif_id', '=', 'tarif.id')
        ->leftJoin('container', 'orderantrucking.container_id', '=', 'container.id')
        ->leftJoin('agen', 'orderantrucking.agen_id', '=', 'agen.id')
        ->leftJoin('jenisorder', 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
        ->leftJoin('pelanggan', 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
        ->leftJoin('parameter', 'orderantrucking.statuslangsir', '=', 'parameter.id')
        ->leftJoin('parameter AS param2', 'orderantrucking.statusperalihan', '=', 'param2.id');

    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('container_id', 1000)->default('');
            $table->string('agen_id', 1000)->default('');
            $table->string('jenisorder_id', 1000)->default('');
            $table->string('pelanggan_id', 1000)->default('');
            $table->string('tarif_id', 1000)->default('');
            $table->string('nominal', 1000)->default('');
            $table->string('nojobemkl', 1000)->default('');
            $table->string('nocont', 1000)->default('');
            $table->string('noseal', 1000)->default('');
            $table->string('nojobemkl2', 1000)->default('');
            $table->string('nocont2', 1000)->default('');
            $table->string('noseal2', 1000)->default('');
            $table->string('statuslangsir', 1000)->default('');
            $table->string('statusperalihan', 1000)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','container_id','agen_id','jenisorder_id','pelanggan_id','tarif_id','nominal','nojobemkl','nocont','noseal','nojobemkl2','nocont2','noseal2','statuslangsir','statusperalihan','modifiedby','created_at','updated_at'],$models);


        return  $temp;         

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
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuslangsir') {
                            $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'statusperalihan') {
                            $query = $query->where('param2.text', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'agen_id') {
                            $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'pelanggan_id') {
                            $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'container_id') {
                            $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'tarif_id') {
                            $query = $query->where('tarif.tujuan', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'jenisorder_id') {
                            $query = $query->where('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' .$filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuslangsir') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'statusperalihan') {
                            $query = $query->orWhere('param2.text', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'agen_id') {
                            $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'pelanggan_id') {
                            $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'container_id') {
                            $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'tarif_id') {
                            $query = $query->orWhere('tarif.tujuan', 'LIKE', "%$filters[data]%");
                        } elseif($filters['field'] == 'jenisorder_id') {
                            $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($this->table . '.' .$filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

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