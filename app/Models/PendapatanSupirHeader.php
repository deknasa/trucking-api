<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PendapatanSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'pendapatansupirheader';

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
        $query = DB::table($this->table)->select(
            'pendapatansupirheader.id',
            'pendapatansupirheader.nobukti',
            'pendapatansupirheader.tglbukti',
            'bank.namabank as bank_id',
            'pendapatansupirheader.keterangan',
            'pendapatansupirheader.tgldari',
            'pendapatansupirheader.tglsampai',
            'statusapproval.memo as statusapproval',
            'pendapatansupirheader.userapproval',
            DB::raw('(case when (year(pendapatansupirheader.tglapproval) <= 2000) then null else pendapatansupirheader.tglapproval end ) as tglapproval'),
            DB::raw('(case when (year(pendapatansupirheader.tglbukacetak) <= 2000) then null else pendapatansupirheader.tglbukacetak end ) as tglbukacetak'),
            'statuscetak.memo as statuscetak',
            'pendapatansupirheader.userbukacetak',
            'pendapatansupirheader.jumlahcetak',
            'pendapatansupirheader.periode',
            'pendapatansupirheader.modifiedby',
            'pendapatansupirheader.created_at',
            'pendapatansupirheader.updated_at'
        )
            ->leftJoin('bank', 'pendapatansupirheader.bank_id', 'bank.id')
            ->leftJoin('parameter as statusapproval', 'pendapatansupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'pendapatansupirheader.statuscetak', 'statuscetak.id');
          
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findUpdate($id)
    {
        $data = DB::table('pendapatansupirheader')->select(
            'pendapatansupirheader.id',
            'pendapatansupirheader.nobukti',
            'pendapatansupirheader.tglbukti',
            'pendapatansupirheader.bank_id',
            'bank.namabank as bank',
            'pendapatansupirheader.keterangan',
            'pendapatansupirheader.tgldari',
            'pendapatansupirheader.tglsampai',
            'pendapatansupirheader.periode',
            'pendapatansupirheader.statuscetak',
        )
        ->leftJoin('bank','pendapatansupirheader.bank_id','bank.id')
        ->where('pendapatansupirheader.id', $id)
        ->first();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 'bank.namabank as bank_id', 
                 $this->table.keterangan,
                 $this->table.tgldari,
                 $this->table.tglsampai,
                'parameter.text as statusapproval',
                 $this->table.userapproval,
                 $this->table.tglapproval,
                 'statuscetak.text as statuscetak',
                 $this->table.userbukacetak,
                 $this->table.tglbukacetak,
                 $this->table.jumlahcetak,
                 $this->table.periode,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
            ->leftJoin('bank', 'pendapatansupirheader.bank_id', 'bank.id')
            ->leftJoin('parameter as statusapproval', 'pendapatansupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'pendapatansupirheader.statuscetak', 'statuscetak.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('bank_id', 1000)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->date('tgldari')->default('');
            $table->date('tglsampai')->default('');
            $table->string('statusapproval')->default('');
            $table->string('userapproval')->default('');
            $table->date('tglapproval')->default('');
            $table->string('statuscetak',1000)->default('');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
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
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti','bank_id', 'keterangan', 'tgldari', 'tglsampai', 'statusapproval', 'userapproval','tglapproval','statuscetak','userbukacetak','tglbukacetak','jumlahcetak','periode', 'modifiedby','created_at', 'updated_at'], $models);

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
                        if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
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
        if (request()->cetak && request()->periode) {
            $query->where('pendapatansupirheader.statuscetak','<>', request()->cetak)
                  ->whereYear('pendapatansupirheader.tglbukti','=', request()->year)
                  ->whereMonth('pendapatansupirheader.tglbukti','=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
