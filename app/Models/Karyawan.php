<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Karyawan extends MyModel
{
    use HasFactory;
    protected $table = 'karyawan';

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
        $aktif = request()->aktif ?? '';
        $staff = request()->staff ?? '';

        $query = DB::table($this->table)->from(DB::raw("karyawan with (readuncommitted)"))
            ->select(
                'karyawan.id',
                'karyawan.namakaryawan',
                'karyawan.keterangan',
                'statusaktif.memo as statusaktif',
                'statusstaff.memo as statusstaff',
                'karyawan.modifiedby',
                'karyawan.created_at',
                'karyawan.updated_at'
            )
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'karyawan.statusaktif', 'statusaktif.id')
            ->leftJoin(DB::raw("parameter as statusstaff with (readuncommitted)"), 'karyawan.statusstaff', 'statusstaff.id');

        $this->filter($query);

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('karyawan.statusaktif', '=', $statusaktif->id);
        }
        if ($staff == 'MEKANIK') {
            $statusstaff = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS STAFF')
                ->where('text', '=', 'MEKANIK')
                ->first();

            $query->where('karyawan.statusstaff', '=', $statusstaff->id);
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
            $table->unsignedBigInteger('statusstaff')->nullable();
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

        $statusstaff = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS STAFF')
            ->where('subgrp', '=', 'STATUS STAFF')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id, "statusstaff" => $statusstaff->id]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusstaff'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function cekvalidasihapus($id)
    {

        $serviceIn = DB::table('serviceindetail')
            ->from(
                DB::raw("serviceindetail as a with (readuncommitted)")
            )
            ->select(
                'a.karyawan_id'
            )
            ->where('a.karyawan_id', '=', $id)
            ->first();
        if (isset($serviceIn)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Service In',
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

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            DB::raw(
                "$this->table.id,
                $this->table.namakaryawan,
                $this->table.keterangan,
                'parameter.text as statusaktif',
                'statusstaff.text as statusstaff',
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'karyawan.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusstaff with (readuncommitted)"), 'karyawan.statusstaff', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('namakaryawan', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('statusstaff', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'namakaryawan', 'keterangan', 'statusaktif','statusstaff', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                            $query = $query->where('statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusstaff') {
                            $query = $query->where('statusstaff.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('statusaktif.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statusstaff') {
                                $query = $query->orWhere('statusstaff.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                            }
                        }
                    });
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
