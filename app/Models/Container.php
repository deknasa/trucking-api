<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Container extends MyModel
{
    use HasFactory;

    protected $table = 'container';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function cekvalidasihapus($id)
    {
        // cek sudah ada container


        $tarif = DB::table('tarifrincian')
            ->from(
                DB::raw("tarifrincian as a with (readuncommitted)")
            )
            ->select(
                'a.container_id'
            )
            ->where('a.container_id', '=', $id)
            ->first();

        if (isset($tarif)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Tarif',
            ];

            goto selesai;
        }

        $upahSupir = DB::table('upahsupirrincian')
            ->from(
                DB::raw("upahsupirrincian as a with (readuncommitted)")
            )
            ->select(
                'a.container_id'
            )
            ->where('a.container_id', '=', $id)
            ->first();

        if (isset($upahSupir)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Supir',
            ];

            goto selesai;
        }

        $upahRitasi = DB::table('upahritasirincian')
            ->from(
                DB::raw("upahritasirincian as a with (readuncommitted)")
            )
            ->select(
                'a.container_id'
            )
            ->where('a.container_id', '=', $id)
            ->first();
            
        if (isset($upahRitasi)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Ritasi',
            ];

            goto selesai;
        }
        
        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.container_id'
            )
            ->where('a.container_id', '=', $id)
            ->first();
            
        if (isset($suratPengantar)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];

            goto selesai;
        }

        $orderanTrucking = DB::table('orderantrucking')
            ->from(
                DB::raw("orderantrucking as a with (readuncommitted)")
            )
            ->select(
                'a.container_id'
            )
            ->where('a.container_id', '=', $id)
            ->first();
            
        if (isset($orderanTrucking)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Orderan Trucking',
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

        $query = Container::from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'container.id',
                'container.kodecontainer',
                'container.keterangan',
                'parameter.memo as statusaktif',
                'container.modifiedby',
                'container.created_at',
                'container.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'container.statusaktif', '=', 'parameter.id');




        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('container.statusaktif', '=', $statusaktif->id);
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
            $table->unsignedBigInteger('statusaktif')->default(0);
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

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.kodecontainer,
            $this->table.keterangan,
            'parameter.text as statusaktif',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )

            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'container.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('kodecontainer', 50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('statusaktif', 500)->default('');

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
        DB::table($temp)->insertUsing(['id', 'kodecontainer', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);

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
                            $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'id') {
                            $query = $query->where('container.id', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'modifiedby') {
                            $query = $query->where('container.modifiedby', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'created_at') {
                            $query = $query->where('container.created_at', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'updated_at') {
                            $query = $query->where('container.updated_at', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'id') {
                            $query = $query->orWhereRaw("(container.id like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(container.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%')");
                        } elseif ($filters['field'] == 'modifiedby') {
                            $query = $query->orWhere('container.modifiedby', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'created_at') {
                            $query = $query->orWhere('container.created_at', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($filters['field'], 'LIKE', "%$filters[data]%");
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
