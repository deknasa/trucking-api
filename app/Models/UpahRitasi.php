<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class UpahRitasi extends MyModel
{
    use HasFactory;

    protected $table = 'upahritasi';

    protected $casts = [
        'tglmulaiberlaku' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function upahritasiRincian()
    {
        return $this->hasMany(UpahRitasiRincian::class, 'upahritasi_id');
    }

    public function kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }

    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'upahritasi.id',
            'kotadari.keterangan as kotadari_id',
            'kotasampai.keterangan as kotasampai_id',
            'upahritasi.jarak',
            'zona.zona as zona_id',
            'parameter.text as statusaktif',
            'upahritasi.tglmulaiberlaku',
            'param.text as statusluarkota',
            'upahritasi.modifiedby',
            'upahritasi.created_at',
            'upahritasi.updated_at'
        )
            ->join('kota as kotadari', 'kotadari.id', '=', 'upahritasi.kotadari_id')
            ->join('kota as kotasampai', 'kotasampai.id', '=', 'upahritasi.kotasampai_id')
            ->join('zona', 'zona.id', '=', 'upahritasi.zona_id')
            ->leftJoin('parameter', 'upahritasi.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as param', 'upahritasi.statusluarkota', '=', 'param.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
            "$this->table.id,
            'kotadari.keterangan as kotadari_id',
            'kotasampai.keterangan as kotasampai_id',
            $this->table.jarak
            'zona.zona as zona_id',
            'parameter.text as statusaktif',
            $this->table.tglmulaiberlaku,
            'param.text as statusluarkota',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at,
            $this->table.statusformat"
            )

        )
            ->join('kota as kotadari', 'kotadari.id', '=', 'upahritasi.kotadari_id')
            ->join('kota as kotasampai', 'kotasampai.id', '=', 'upahritasi.kotasampai_id')
            ->join('zona', 'zona.id', '=', 'upahritasi.zona_id')
            ->leftJoin('parameter', 'upahritasi.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as param', 'upahritasi.statusluarkota', '=', 'param.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->unsignedBigInteger('kotadari_id')->default('0');
            $table->unsignedBigInteger('kotasampai_id')->default('0');
            $table->double('jarak', 15, 2)->default('0');
            $table->unsignedBigInteger('zona_id')->default('0');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->date('tglmulaiberlaku')->default('1900/1/1');
            $table->integer('statusluarkota')->length(11)->default('0');
            $table->string('modifiedby', 50)->Default('');
            $table->timestamps();

            $table->string('modifiedby', 50)->default('');
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
        DB::table($temp)->insertUsing(['id', 'kotadari_id', 'kotasampai_id', 'jarak', 'zona_id', 'statusaktif', 'statusluarkota', 'modifiedby', 'created_at', 'updated_at', 'statusformat'], $models);

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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.zona', '=', $filters['data']);
                        } else if ($filters['field'] == 'kotadari_id') {
                            $query = $query->where('kotadari.keterangan', '=', $filters['data']);
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->orWhere('zona.zona', '=', $filters['data']);
                        } else if ($filters['field'] == 'kotadari_id') {
                            $query = $query->orWhere('kotadari.keterangan', '=', $filters['data']);
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
