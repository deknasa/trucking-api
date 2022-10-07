<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HutangHeader extends MyModel
{
    use HasFactory;

    protected $table = 'hutangheader';

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
            'hutangheader.id',
            'hutangheader.nobukti',
            'hutangheader.tglbukti',
            'hutangheader.keterangan',

            'akunpusat.coa as akunpusat',
            'pelanggan.namapelanggan as pelanggan_id',

            // 'hutangheader.coa',
            //'hutangheader.pelanggan_id',
            'hutangheader.total',

            'hutangheader.modifiedby',
            'hutangheader.updated_at'
        )
            ->leftJoin('akunpusat', 'hutangheader.coa', 'akunpusat.coa')
            ->leftJoin('pelanggan', 'hutangheader.pelanggan_id', 'pelanggan.id');

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
        $query = DB::table('hutangheader')->select(
            'hutangheader.id',
            'hutangheader.nobukti',
            'hutangheader.tglbukti',
            'hutangheader.keterangan',
            'akunpusat.coa as akunpusat',
            // 'akunpusat.coa as coa',
            'pelanggan.namapelanggan as pelanggan',
            'pelanggan.id as pelanggan_id',

            'hutangheader.total',

            'hutangheader.modifiedby',
            'hutangheader.updated_at'
        )
            ->leftJoin('akunpusat', 'hutangheader.coa', 'akunpusat.coa')
            ->leftJoin('pelanggan', 'hutangheader.pelanggan_id', 'pelanggan.id')

            ->where('hutangheader.id', $id);

        $data = $query->first();
        return $data;
    }
    public function hutangdetail()
    {
        return $this->hasMany(HutangDetail::class, 'hutang_id');
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 $this->table.keterangan,
                 'akunpusat.coa as akunpusat',
                 'pelanggan.namapelanggan as pelanggan_id',
                 $this->table.total,

                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at,
                 $this->table.statusformat"
            )

        )->leftJoin('akunpusat', 'hutangheader.coa', 'akunpusat.coa')
        ->leftJoin('pelanggan', 'hutangheader.pelanggan_id', 'pelanggan.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->string('coa', 50)->default('');
            $table->string('pelanggan_id', 50)->default('');
            $table->double('total', 15, 2)->default(0);
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->bigInteger('statusformat')->default('');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'keterangan', 'coa', 'pelanggan_id', 'total', 'modifiedby', 'created_at', 'updated_at', 'statusformat'], $models);

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
                        $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
