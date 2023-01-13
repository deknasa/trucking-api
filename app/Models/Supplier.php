<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Supplier extends MyModel
{
    use HasFactory;

    protected $table = 'supplier';

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
            // "$this->table.*",
            'supplier.id',
            'supplier.namasupplier',
            'supplier.namasupplier',
            'supplier.namakontak',
            'supplier.alamat',
            'supplier.kota',
            'supplier.kodepos',
            'supplier.notelp1',
            'supplier.notelp2',
            'supplier.email',

            'parameter_statusaktif.memo as statusaktif',
            'supplier.web',
            'supplier.namapemilik',
            'supplier.jenisusaha',
            'supplier.bank',
            'supplier.rekeningbank',
            'supplier.namarekening',
            'supplier.jabatan',

            'parameter_statusdaftarharga.memo as statusdaftarharga',
            'supplier.kategoriusaha',

            'supplier.modifiedby',
            'supplier.created_at',
            'supplier.updated_at'

        )
            ->leftJoin('parameter as parameter_statusaktif', "supplier.statusaktif", '=', 'parameter_statusaktif.id')
            ->leftJoin('parameter as parameter_statusdaftarharga', "supplier.statusdaftarharga", '=', 'parameter_statusdaftarharga.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->default(0);
            $table->unsignedBigInteger('statusdaftarharga')->default(0);
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'memo',
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF');

        $datadetail = json_decode($status->get(), true);

        $iddefaultstatusaktif = 0;
        foreach ($datadetail as $item) {
            $memo = json_decode($item['memo'], true);
            $default = $memo['DEFAULT'];
            if ($default == "YA") {
                $iddefaultstatusaktif = $item['id'];
                break;
            }
        }

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'memo',
                'id'
            )
            ->where('grp', '=', 'STATUS DAFTAR HARGA')
            ->where('subgrp', '=', 'STATUS DAFTAR HARGA');

        $datadetail = json_decode($status->get(), true);

        $iddefaultstatusdaftarharga = 0;
        foreach ($datadetail as $item) {
            $memo = json_decode($item['memo'], true);
            $default = $memo['DEFAULT'];

            if ($default == "YA") {
                $iddefaultstatusdaftarharga = $item['id'];
                break;
            }
        }

        DB::table($tempdefault)->insert(
            ["statusaktif" => $iddefaultstatusaktif,"statusdaftarharga" => $iddefaultstatusdaftarharga]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusdaftarharga',
            );

        $data = $query->first();
        
        return $data;
    }

    public function find($id)
    {
        $query = DB::table('supplier')->select(
            'supplier.id',
            'supplier.namasupplier',
            'supplier.namasupplier',
            'supplier.namakontak',
            'supplier.alamat',
            'supplier.kota',
            'supplier.kodepos',
            'supplier.notelp1',
            'supplier.notelp2',
            'supplier.email',

            'supplier.statusaktif',
            'supplier.web',
            'supplier.namapemilik',
            'supplier.jenisusaha',
            'supplier.bank',
            'supplier.rekeningbank',
            'supplier.namarekening',
            'supplier.jabatan',

            'supplier.statusdaftarharga',
            'supplier.kategoriusaha',

            'supplier.modifiedby',
            'supplier.created_at',
            'supplier.updated_at'

        )
        ->where('supplier.id', $id);

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.namasupplier,
            $this->table.namakontak,
            $this->table.alamat,
            $this->table.kota,
            $this->table.kodepos,
            $this->table.notelp1,
            $this->table.notelp2,
            $this->table.email,
            $this->table.statusaktif,

            $this->table.web,
            $this->table.namapemilik,
            $this->table.jenisusaha,
            $this->table.bank,
            $this->table.rekeningbank,
            $this->table.namarekening,
            $this->table.jabatan,
            $this->table.statusdaftarharga,
            $this->table.kategoriusaha,

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

            );
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->longText('namasupplier')->default('');
            $table->string('namakontak', 150)->default('');
            $table->longText('alamat')->default('');
            $table->string('kota', 150)->default('');
            $table->string('kodepos', 50)->default('');
            $table->string('notelp1', 50)->default('');
            $table->string('notelp2', 50)->default('');
            $table->string('email', 50)->default('');
            $table->string('statusaktif')->length(11)->default('0');
            $table->string('web', 50)->default('');
            $table->string('namapemilik', 150)->default('');
            $table->string('jenisusaha', 150)->default('');
            $table->string('bank', 150)->default('');
            $table->string('rekeningbank', 150)->default('');
            $table->string('namarekening', 150)->default('');
            $table->string('jabatan', 150)->default('');
            $table->string('statusdaftarharga')->length(11)->default('0');
            $table->string('kategoriusaha', 150)->default('');
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
        DB::table($temp)->insertUsing(['id', 'namasupplier', 'namakontak',  'alamat', 'kota', 'kodepos', 'notelp1', 'notelp2', 'email',  'statusaktif', 'web', 'namapemilik', 'jenisusaha', 'bank', 'rekeningbank',  'namarekening', 'jabatan', 'statusdaftarharga', 'kategoriusaha', 'modifiedby', 'created_at', 'updated_at'], $models);

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
                        } else if ($filters['field'] == 'statusdaftarharga') {
                            $query = $query->where('parameter_statusdaftarharga.text', '=', $filters['data']);
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusdaftarharga') {
                            $query = $query->orWhere('parameter_statusdaftarharga.text', '=', $filters['data']);
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
