<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class JurnalUmumHeader extends MyModel
{
    use HasFactory;

    protected $table = 'jurnalumumheader';

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

        $lennobukti = 3;

        $query = DB::table($this->table)
            ->select(

                'jurnalumumheader.id',
                'jurnalumumheader.nobukti',
                'jurnalumumheader.tglbukti',
                'jurnalumumheader.keterangan',
                'jurnalumumheader.postingdari',
                'jurnalumumheader.statusapproval',
                'jurnalumumheader.userapproval',
                DB::raw('(case when (year(jurnalumumheader.tglapproval) <= 2000) then null else jurnalumumheader.tglapproval end ) as tglapproval'),
                'jurnalumumheader.modifiedby',
                'jurnalumumheader.created_at',
                'jurnalumumheader.updated_at',
                'statusapproval.text as statusapproval'
            )
            ->leftJoin('parameter as statusapproval', 'jurnalumumheader.statusapproval', 'statusapproval.id');

        // ->where(DB::raw('LEFT(nobukti,'. $lennobukti.')'),  '=', 'KGT');


        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


        $data = $query->get();

        return $data;
    }
    public function jurnalumumdetail()
    {
        return $this->hasMany(JurnalUmumDetail::class, 'jurnalumum_id');
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
            "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            $this->table.keterangan,
            $this->table.postingdari,
            'statusapproval.text as statusapproval',
            $this->table.userapproval,
            $this->table.tglapproval,
            $this->table.modifiedby,
            $this->table.updated_at"
            )
        )
        ->leftJoin('parameter as statusapproval', 'jurnalumumheader.statusapproval', 'statusapproval.id');

    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('postingdari', 1000)->default('');
            $table->string('statusapproval', 1000)->default('');
            $table->string('userapproval', 1000)->default('');
            $table->string('tglapproval', 1000)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','keterangan','postingdari','statusapproval','userapproval','tglapproval','modifiedby','updated_at'],$models);


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
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', 'LIKE', "%$filters[data]%");
                        } else{
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('statusapproval.text', 'LIKE', "%$filters[data]%");
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
