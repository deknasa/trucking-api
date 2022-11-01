<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class ProsesGajiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'prosesgajisupirheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get() {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'prosesgajisupirheader.id',
            'prosesgajisupirheader.nobukti',
            'prosesgajisupirheader.tglbukti',
            'prosesgajisupirheader.keterangan',
            'prosesgajisupirheader.tgldari',
            'prosesgajisupirheader.tglsampai',
            'parameter.text as statusapproval',
            'prosesgajisupirheader.userapproval',
            'prosesgajisupirheader.tglapproval',
            'prosesgajisupirheader.periode',
            'prosesgajisupirheader.modifiedby',
            'prosesgajisupirheader.created_at',
            'prosesgajisupirheader.updated_at',
        )
        ->join('parameter','prosesgajisupirheader.statusapproval','parameter.id');
            
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getRic($dari, $sampai) 
    {
        $query = DB::table('gajisupirheader')
                ->select('gajisupirheader.id','gajisupirheader.nobukti','gajisupirheader.tglbukti','supir.namasupir','gajisupirheader.keterangan','gajisupirheader.tgldari','gajisupirheader.tglsampai','gajisupirheader.nominal')
                ->join('supir','gajisupirheader.supir_id','supir.id')
                ->where('gajisupirheader.tgldari','>=', $dari)
                ->where('gajisupirheader.tglsampai','<=', $sampai);

        $data = $query->get();
        return $data;
    }

    public function getEdit($gajiId) {
        $query = DB::table('prosesgajisupirdetail')->select(
            'gajisupirheader.id',
            'prosesgajisupirdetail.gajisupir_nobukti as nobukti',
            'gajisupirheader.tglbukti',
            'supir.namasupir',
            'prosesgajisupirdetail.keterangan',
            'gajisupirheader.tgldari',
            'gajisupirheader.tglsampai',
            'gajisupirheader.nominal'
        )
        ->join('gajisupirheader','prosesgajisupirdetail.gajisupir_nobukti','gajisupirheader.nobukti')
        ->join('supir','gajisupirheader.supir_id','supir.id')
        ->where('prosesgajisupirdetail.prosesgajisupir_id',$gajiId);

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
            $this->table.keterangan,
            $this->table.tgldari,
            $this->table.tglsampai,
            'parameter.text as statusapproval',
            $this->table.userapproval,
            $this->table.tglapproval,
            $this->table.periode,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at
            ")
        )
        ->join('parameter','prosesgajisupirheader.statusapproval','parameter.id');
            
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table){
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('keterangan', 1000)->default('');
            $table->date('tgldari')->default('');
            $table->date('tglsampai')->default('');
            $table->string('statusapproval', 1000)->default('');
            $table->string('userapproval', 1000)->default('');
            $table->date('tglapproval')->default('');
            $table->date('periode')->default('');
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
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','keterangan','tgldari','tglsampai','statusapproval','userapproval','tglapproval','periode','modifiedby','created_at','updated_at'], $models);

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
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                        }else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
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
