<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\RestrictDeletion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Agen extends MyModel
{
    use HasFactory, RestrictDeletion;

    protected $table = 'agen';

    protected $casts = [
        'tglapproval' => 'date:d-m-Y',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function isDeletable()
    {
        $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();

        return $this->statusapproval != $statusApproval->id;
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'agen.id',
            'agen.kodeagen',
            'agen.namaagen',
            'agen.keterangan',
            'parameter.memo as statusaktif',
            'agen.namaperusahaan',
            'agen.alamat',
            'agen.notelp',
            'agen.nohp',
            'agen.contactperson',
            'agen.top',
            'statusapproval.memo as statusapproval',
            'agen.userapproval',
            'agen.tglapproval',
            'statustas.memo as statustas',
            'agen.jenisemkl',
            'agen.created_at',
            'agen.modifiedby',
            'agen.updated_at'
        )
            ->leftJoin('parameter', 'agen.statusaktif', 'parameter.id')
            ->leftJoin('parameter as statusapproval', 'agen.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statustas', 'agen.statustas', 'statustas.id');



        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

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
            "$this->table.kodeagen",
            "$this->table.namaagen",
            "$this->table.keterangan",
            "parameter_statusaktif.text as statusaktif",
            "$this->table.namaperusahaan",
            "$this->table.alamat",
            "$this->table.notelp",
            "$this->table.nohp",
            "$this->table.contactperson",
            "$this->table.top",
            "parameter_statusapproval.text as statusapproval",
            "$this->table.userapproval",
            "$this->table.tglapproval",
            "parameter_statustas.text as statustas",
            "jenisemkl.keterangan as jenisemkl",
            "$this->table.created_at",
            "$this->table.updated_at",
            "$this->table.modifiedby",
        )
            ->leftJoin("parameter as parameter_statusaktif", "agen.statusaktif", "parameter_statusaktif.id")
            ->leftJoin("parameter as parameter_statusapproval", "agen.statusapproval", "parameter_statusapproval.id")
            ->leftJoin("parameter as parameter_statustas", "agen.statustas", "parameter_statustas.id")
            ->leftJoin("jenisemkl", "agen.jenisemkl", "jenisemkl.id");
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, 10000);

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('kodeagen', 1000)->default('');
            $table->string('namaagen', 1000)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('statusaktif', 1000)->default('');
            $table->string('namaperusahaan', 1000)->default('');
            $table->string('alamat', 1000)->default('');
            $table->string('notelp', 1000)->default('');
            $table->string('nohp', 1000)->default('');
            $table->string('contactperson', 1000)->default('');
            $table->string('top', 1000)->default('');
            $table->string('statusapproval', 1000)->default('');
            $table->string('userapproval', 1000)->default('');
            $table->string('tglapproval', 1000)->default('');
            $table->string('statustas', 1000)->default('');
            $table->string('jenisemkl', 1000)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->string('modifiedby', 50)->default('');
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'kodeagen',
            'namaagen',
            'keterangan',
            'statusaktif',
            'namaperusahaan',
            'alamat',
            'notelp',
            'nohp',
            'contactperson',
            'top',
            'statusapproval',
            'userapproval',
            'tglapproval',
            'statustas',
            'jenisemkl',
            'created_at',
            'updated_at',
            'modifiedby'
        ], $models);

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
