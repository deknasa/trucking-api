<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Stok extends MyModel
{
    use HasFactory;

    protected $table = 'stok';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasihapus($id)
    {

        $pengeluaranStok = DB::table('pengeluaranstokdetail')
            ->from(
                DB::raw("pengeluaranstokdetail as a with (readuncommitted)")
            )
            ->select(
                'a.stok_id'
            )
            ->where('a.stok_id', '=', $id)
            ->first();
        if (isset($pengeluaranStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Stok',
            ];


            goto selesai;
        }

        $penerimaanStok = DB::table('penerimaanstokdetail')
            ->from(
                DB::raw("penerimaanstokdetail as a with (readuncommitted)")
            )
            ->select(
                'a.stok_id'
            )
            ->where('a.stok_id', '=', $id)
            ->first();
        if (isset($penerimaanStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Stok',
            ];


            goto selesai;
        }


        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];

        selesai:
        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';


        $query = DB::table($this->table)->select(
            'stok.id',
            'stok.namastok',
            'parameter.memo as statusaktif',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'stok.modifiedby',
            'jenistrado.keterangan as jenistrado',
            'kelompok.keterangan as kelompok',
            'subkelompok.keterangan as subkelompok',
            'kategori.keterangan as kategori',
            'merk.keterangan as merk',
            'stok.created_at',
            'stok.updated_at',
        )
            ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
            ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
            ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
            ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
            ->leftJoin('merk', 'stok.merk_id', 'merk.id');




        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('stok.statusaktif', '=', $statusaktif->id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);        
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function findAll($id)
    {
        $data = DB::table('stok')->select(
            'stok.id',
            'stok.namastok',
            'stok.statusaktif',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'stok.modifiedby',
            'stok.jenistrado_id',
            'stok.kelompok_id',
            'stok.subkelompok_id',
            'stok.kategori_id',
            'stok.merk_id',
            'jenistrado.keterangan as jenistrado',
            'kelompok.keterangan as kelompok',
            'subkelompok.keterangan as subkelompok',
            'kategori.keterangan as kategori',
            'merk.keterangan as merk',
        )
            ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
            ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
            ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
            ->leftJoin('merk', 'stok.merk_id', 'merk.id')
            ->where('stok.id', $id)
            ->first();

        return $data;
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->unsignedBigInteger('jenistrado_id')->nullable();
            $table->unsignedBigInteger('kelompok_id')->nullable();
            $table->unsignedBigInteger('subkelompok_id')->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->unsignedBigInteger('merk_id')->nullable();
            $table->string('namastok', 200)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->double('qtymin', 15, 2)->nullable();
            $table->double('qtymax', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('namaterpusat')->nullable();

            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->select(
            'id',
            'jenistrado_id',
            'kelompok_id',
            'subkelompok_id',
            'kategori_id',
            'merk_id',
            'namastok',
            'statusaktif',
            'qtymin',
            'qtymax',
            'keterangan',
            'gambar',
            'namaterpusat',
            'modifiedby',
            'created_at',
            'updated_at'
        );
        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'jenistrado_id',
            'kelompok_id',
            'subkelompok_id',
            'kategori_id',
            'merk_id',
            'namastok',
            'statusaktif',
            'qtymin',
            'qtymax',
            'keterangan',
            'gambar',
            'namaterpusat',
            'modifiedby',
            'created_at',
            'updated_at'
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
                        if ($filters['field'] == 'jenistrado') {
                            $query = $query->where('jenistrado.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'kelompok') {
                            $query = $query->where('kelompok.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'subkelompok') {
                            $query = $query->where('subkelompok.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'kategori') {
                            $query = $query->where('kategori.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'merk') {
                            $query = $query->where('merk.keterangan', '=', "$filters[data]");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'jenistrado') {
                            $query = $query->orWhere('jenistrado.keterangan', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'id') {
                            $query = $query->orWhereRaw("(stok.id like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(stok.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%')");
                        } else if ($filters['field'] == 'kelompok') {
                            $query = $query->orWhere('kelompok.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'subkelompok') {
                            $query = $query->orWhere('subkelompok.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'kategori') {
                            $query = $query->orWhere('kategori.keterangan', '=', "$filters[data]");
                        } else if ($filters['field'] == 'merk') {
                            $query = $query->orWhere('merk.keterangan', '=', "$filters[data]");
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
