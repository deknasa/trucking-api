<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Cabang extends MyModel
{
    use HasFactory;

    protected $table = 'cabang';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';
        $transferCoa = request()->transferCoa ?? '';
        $emkl = request()->emkl ?? '';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'cabang.id',
                'cabang.kodecabang',
                'cabang.namacabang',
                'parameter.memo as statusaktif',
                'statuskoneksi.memo as statuskoneksi_memo',
                'cabang.modifiedby',
                'cabang.created_at',
                'cabang.updated_at',
                DB::raw("'Laporan Cabang' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter as statuskoneksi with (readuncommitted)"), 'cabang.statuskoneksi', 'statuskoneksi.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'cabang.statusaktif', 'parameter.id');



            $this->filter($query);

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('cabang.statusaktif', '=', $statusaktif->id);
        }
        if ($transferCoa != '') {
            $query->where('cabang.kodecabang', '!=', 'PST');
        }
        if($emkl=='emkl'){
            $query->whereRaw("isnull(cabang.pelanggan_id,0) != 0");
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

        // $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempdefault, function ($table) {
        //     $table->unsignedBigInteger('statusaktif')->nullable();
        // });

        // $statusaktif=Parameter::from (
        //     db::Raw("parameter with (readuncommitted)")
        // )
        // ->select (
        //     'memo',
        //     'id'
        // )
        // ->where('grp','=','STATUS AKTIF')
        // ->where('subgrp','=','STATUS AKTIF');

        // $datadetail = json_decode($statusaktif->get(), true);

        // $iddefault=0;
        // foreach ($datadetail as $item) {
        //     $memo = json_decode($item['memo'], true);
        //     $default=$memo['DEFAULT'];
        //     if ($default=="YA") {
        //         $iddefault=$item['id'];
        //         DB::table($tempdefault)->insert(["statusaktif" => $iddefault]);
        //     } 
        // }



        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama', 300)->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('DEFAULT', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id, "statusaktifnama" => $statusaktif->text]);




        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))
            ->select(
                'cabang.id',
                'cabang.kodecabang',
                'cabang.namacabang',
                'cabang.statusaktif',
                'cabang.memo',
                'parameter.text as statusaktifnama',
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'cabang.statusaktif', 'parameter.id')
            ->where('cabang.id', $id)
            ->first();

        return $query;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            "$this->table.kodecabang",
            "$this->table.namacabang",
            "parameter.text as statusaktif",
            'statuskoneksi.memo as statuskoneksi_memo',
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        )
        ->leftJoin(DB::raw("parameter as statuskoneksi with (readuncommitted)"), 'cabang.statuskoneksi', 'statuskoneksi.id')
        ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'cabang.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodecabang')->nullable();
            $table->string('namacabang')->nullable();
            $table->string('statusaktif')->nullable();
            $table->string('statuskoneksi_memo')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });


        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'kodecabang',
            'namacabang',
            'statusaktif',
            'statuskoneksi_memo',
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
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->where('parameter.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuskoneksi_memo') {
                                $query = $query->where('statuskoneksi.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->whereRaw($this->table . ".".  $filters['field'] ." LIKE '%".str_replace($filters['data'],'[','|[') ."%' escape '|'");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusaktif') {
                                    $query = $query->orWhere('parameter.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuskoneksi_memo') {
                                    $query = $query->orWhere('statuskoneksi.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->OrwhereRaw('cabang' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                               
                                    // $query = $query->OrwhereRaw($this->table . ".".  $filters['field'] ." LIKE '%".str_replace($filters['data'],'[','|[') ."%' escape '|'");
                                    // $query = $query->orWhereRaw($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                }
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

    public function processStore(array $data, Cabang $cabang): Cabang
    {
        // $cabang = new Cabang();
        $cabang->kodecabang = $data['kodecabang'];
        $cabang->namacabang = $data['namacabang'];
        $cabang->statusaktif = $data['statusaktif'];
        $cabang->tas_id = $data['tas_id'] ?? '';
        $cabang->modifiedby = auth('api')->user()->user;
        $cabang->info = html_entity_decode(request()->info);
        $detailmemo = [];
        for ($i = 0; $i < count($data['key']); $i++) {
            $value = ($data['value'][$i] != null) ? $data['value'][$i] : "";
            $datadetailmemo = [
                $data['key'][$i] => $value,
            ];
            $detailmemo = array_merge($detailmemo, $datadetailmemo);
        }
        $cabang->memo = json_encode($detailmemo);
        if (!$cabang->save()) {
            throw new \Exception('Error storing cabang.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $cabang->getTable(),
            'postingdari' => 'ENTRY CABANG',
            'idtrans' => $cabang->id,
            'nobuktitrans' => $cabang->id,
            'aksi' => 'ENTRY',
            'datajson' => $cabang->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        // $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        // $data['tas_id'] = $cabang->id;

        // if ($cekStatusPostingTnl->text == 'POSTING TNL') {
        //     $this->saveToTnl('cabang', 'add', $data);
        // }

        return $cabang;
    }

    public function processUpdate(Cabang $cabang, array $data): Cabang
    {
        $cabang->kodecabang = $data['kodecabang'];
        $cabang->namacabang = $data['namacabang'];
        $cabang->statusaktif = $data['statusaktif'];
        $cabang->modifiedby = auth('api')->user()->user;
        $cabang->info = html_entity_decode(request()->info);
        $detailmemo = [];
        for ($i = 0; $i < count($data['key']); $i++) {
            $value = ($data['value'][$i] != null) ? $data['value'][$i] : "";
            $datadetailmemo = [
                $data['key'][$i] => $value,
            ];
            $detailmemo = array_merge($detailmemo, $datadetailmemo);
        }
        $cabang->memo = json_encode($detailmemo);
        if (!$cabang->save()) {
            throw new \Exception('Error updating cabang.');
        }
        
        (new LogTrail())->processStore([
            'namatabel' => $cabang->getTable(),
            'postingdari' => 'EDIT CABANG',
            'idtrans' => $cabang->id,
            'nobuktitrans' => $cabang->id,
            'aksi' => 'EDIT',
            'datajson' => $cabang->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $cabang;
    }

    public function processDestroy(Cabang $cabang): Cabang
    {
        $cabang = $cabang->lockAndDestroy($cabang->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($cabang->getTable()),
            'postingdari' => 'DELETE CABANG',
            'idtrans' => $cabang->id,
            'nobuktitrans' => $cabang->id,
            'aksi' => 'DELETE',
            'datajson' => $cabang->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $cabang;
    }

    public function procesApprovalKonensi(Cabang $cabang): Cabang
    {
        $statusOnLine = Parameter::where('grp', '=', 'STATUS KONEKSI')->where('text', '=', 'ONLINE')->first();
        $statusOffLine = Parameter::where('grp', '=', 'STATUS KONEKSI')->where('text', '=', 'OFFLINE')->first();

        if ($cabang->statuskoneksi == $statusOffLine->id) {
            $cabang->statuskoneksi = $statusOnLine->id;
        } else {
            $cabang->statuskoneksi = $statusOffLine->id;
        }

        if (!$cabang->save()) {
            throw new \Exception('Error Setting Koneksi.');
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($cabang->getTable()),
            'postingdari' => "STATUS KONEKSI CABANG $cabang->namacabang",
            'idtrans' => $cabang->id,
            'nobuktitrans' => $cabang->id,
            'aksi' => 'STATUS KONEKSI CABANG',
            'datajson' => $cabang->toArray(),
            'modifiedby' => auth('api')->user()->name,
        ]);
        return $cabang;
    }
    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $cabang = Cabang::find($data['Id'][$i]);

            $cabang->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($cabang->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($cabang->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF CABANG',
                    'idtrans' => $cabang->id,
                    'nobuktitrans' => $cabang->id,
                    'aksi' => $aksi,
                    'datajson' => $cabang->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $cabang;
    }
}
