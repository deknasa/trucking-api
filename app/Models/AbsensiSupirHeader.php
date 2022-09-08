<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AbsensiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'absensisupirheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tgl' => 'date:d-m-Y',
    ];

    public function absensiSupirDetail()
    {
        return $this->hasMany(AbsensiSupirDetail::class, 'absensi_id');
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->selectColumns($query);
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "$this->table.keterangan",
            "$this->table.kasgantung_nobukti",
            "$this->table.nominal",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        );
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, 10000);

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->string('tglbukti', 1000)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('kasgantung_nobukti', 1000)->default('');
            $table->string('nominal', 1000)->default('');
            $table->string('modifiedby', 1000)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'keterangan',
            'kasgantung_nobukti',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter_statusapproval.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statustas') {
                            $query = $query->where('parameter_statustas.text', '=', $filters['data']);
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('parameter_statusapproval.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statustas') {
                            $query = $query->orWhere('parameter_statustas.text', '=', $filters['data']);
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
