<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GajiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'gajisupirheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];      

    public function get() {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'gajisupirheader.id',
            'gajisupirheader.nobukti',
            'gajisupirheader.tglbukti',
            'supir.namasupir as supir_id',
            'gajisupirheader.keterangan',
            'gajisupirheader.nominal',
            'gajisupirheader.tgldari',
            'gajisupirheader.tglsampai',
            'gajisupirheader.total',
            'gajisupirheader.modifiedby',
            'gajisupirheader.created_at',
            'gajisupirheader.updated_at',
        )
            ->leftJoin('supir', 'gajisupirheader.supir_id', 'supir.id');
            
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }
    public function findAll($id) {
        
        $query = DB::table('gajisupirheader')->select(
            'gajisupirheader.*',
            'supir.namasupir as supir',

        )
            ->leftJoin('supir', 'gajisupirheader.supir_id', 'supir.id')
            ->where('gajisupirheader.id', $id);
            
        $data = $query->first();

        return $data;
    }

    public function getTrip($supirId, $tglDari, $tglSampai) {
        $query = DB::table('suratpengantar')->select(
            'suratpengantar.id',
            'suratpengantar.nobukti',
            'suratpengantar.tglbukti',
            'suratpengantar.trado_id',
            'trado.keterangan as trado',
            'suratpengantar.dari_id',
            'kotaDari.keterangan as dari',
            'suratpengantar.sampai_id',
            'kotaSampai.keterangan as sampai',
            'suratpengantar.nocont',
            'suratpengantar.nosp',
            'suratpengantar.gajisupir',
            'suratpengantar.gajikenek',
        )
        ->join('kota as kotaDari','suratpengantar.dari_id','kotaDari.id')
        ->join('kota as kotaSampai','suratpengantar.sampai_id','kotaSampai.id')
        ->join('trado','suratpengantar.trado_id','trado.id')
        ->where('supir_id',$supirId)
        ->where('tglbukti', $tglDari)
        ->where('tglbukti', $tglSampai);

        $data = $query->get();
        return $data;
    }

    public function getEditTrip($gajiId) {
        $query = DB::table('gajisupirdetail')->select(
            'suratpengantar.id',
            'gajisupirdetail.suratpengantar_nobukti as nobukti',
            'suratpengantar.tglbukti',
            'trado.keterangan as trado',
            'kotaDari.keterangan as dari',
            'kotaSampai.keterangan as sampai',
            'suratpengantar.nocont',
            'suratpengantar.nosp',
            'gajisupirdetail.gajisupir',
            'gajisupirdetail.gajikenek',
        )
        ->join('suratpengantar','gajisupirdetail.suratpengantar_nobukti','suratpengantar.nobukti')
        ->join('kota as kotaDari','suratpengantar.dari_id','kotaDari.id')
        ->join('kota as kotaSampai','suratpengantar.sampai_id','kotaSampai.id')
        ->join('trado','suratpengantar.trado_id','trado.id')
        ->where('gajisupirdetail.gajisupir_id',$gajiId);

        $data = $query->get();
        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw("
            $this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'supir.namasupir as supir_id',
            $this->table.keterangan,
            $this->table.nominal,
            $this->table.tgldari,
            $this->table.tglsampai,
            $this->table.total,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at
            ")
        )
            ->leftJoin('supir', 'gajisupirheader.supir_id', 'supir.id');
            
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table){
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('supir_id', 1000)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->bigInteger('nominal')->default('0');
            $table->date('tgldari')->default('');
            $table->date('tglsampai')->default('');
            $table->bigInteger('total')->default('0');
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','supir_id','keterangan','nominal','tgldari','tglsampai','total','modifiedby','created_at','updated_at'], $models);

        return $temp;
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
                        if ($filters['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        }else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'supir_id') {
                            $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else { 
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
