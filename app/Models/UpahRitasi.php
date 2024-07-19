<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpahRitasi extends MyModel
{
    use HasFactory;

    protected $table = 'upahritasi';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }


    public function cekValidasi($id)
    {
        $upahRitasi = DB::table("upahritasi")->from(DB::raw("upahritasi with (readuncommitted)"))->where('id', $id)->first();

        $rekap = DB::table('ritasi')
            ->from(
                DB::raw("ritasi as a with (readuncommitted)")
            )
            ->where('a.dari_id', '=', $upahRitasi->kotadari_id)
            ->where('a.sampai_id', '=', $upahRitasi->kotasampai_id)
            ->first();

        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'ritasi',
                'kodeerror' => 'SATL'
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

        $query = DB::table($this->table)->from(DB::raw("upahritasi with (readuncommitted)"))
            ->select(
                'upahritasi.id',
                'kotadari.keterangan as kotadari_id',
                'kotasampai.keterangan as kotasampai_id',
                DB::raw("CONCAT(upahritasi.jarak, ' KM') as jarak"),
                'upahritasi.parent_id',
                'upahritasi.nominalsupir',
                // 'zona.keterangan as zona_id',
                'parameter.memo as statusaktif',
                'upahritasi.tglmulaiberlaku',
                // 'upahritasi.tglakhirberlaku',
                // 'statusluarkota.text as statusluarkota',
                'upahritasi.created_at',
                'upahritasi.modifiedby',
                'upahritasi.updated_at'
            )
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahritasi.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahritasi.kotasampai_id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'upahritasi.statusaktif', 'parameter.id');
        // ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'upahritasi.statusluarkota', 'statusluarkota.id');

        // ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahritasi.zona_id', 'zona.id');

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('upahritasi.statusaktif', '=', $statusaktif->id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }
    public function findAll($id)
    {
        $query = DB::table('upahritasi')->from(DB::raw("upahritasi with (readuncommitted)"))
            ->select(
                'upahritasi.id',
                'upahritasi.kotadari_id',
                'kotadari.keterangan as kotadari',

                'upahritasi.kotasampai_id',
                'kotasampai.keterangan as kotasampai',

                'upahritasi.jarak',
                'upahritasi.nominalsupir',
                'upahritasi.parent_id',
                // 'upahritasi.zona_id',
                // 'zona.keterangan as zona',

                'upahritasi.statusaktif',

                'upahritasi.tglmulaiberlaku',
                // 'upahritasi.tglakhirberlaku',
                // 'upahritasi.statusluarkota',
                // 'statusluarkota.text as statusluarkotas',
                'parameter.text as statusaktifnama',
                'upahritasi.modifiedby',
                'upahritasi.updated_at'
            )
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahritasi.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahritasi.kotasampai_id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kotasampai.statusaktif', '=', 'parameter.id')
            // ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahritasi.zona_id', 'zona.id')
            // ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'upahritasi.statusluarkota', 'statusluarkota.id')
            ->where('upahritasi.id', $id);

        $data = $query->first();
        return $data;
    }
    public function upahritasiRincian()
    {
        return $this->hasMany(upahritasiRincian::class, 'upahritasi_id');
    }


    public function default()
    {
        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama', 300)->nullable();
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(
            ["statusaktif" => $status->id ?? 0, "statusaktifnama" => $status->text ?? ""]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama'
            );

        $data = $query->first();
        return $data;
    }

    public function triplookup() {
        return [
            [
                "jenisritasi_id"=> "1" ,
                "jenisritasi"=> "PULANG RANGKA" ,
                "ritasidari_id"=> "2" ,
                "ritasidari"=> "BELAWAN RANGKA" ,
                "ritasike_id"=> "3" ,
                "ritasike"=> "KIM (KANDANG)" 
            ],
            [
                "jenisritasi_id"=> "2" ,
                "jenisritasi"=> "TURUN RANGKA" ,
                "ritasidari_id"=> "3" ,
                "ritasidari"=> "KIM (KANDANG)" ,
                "ritasike_id"=> "2" ,
                "ritasike"=> "BELAWAN RANGKA" 
            ]
        ];
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                kotadari.keterangan as kotadari_id,
                kotasampai.keterangan as kotasampai_id,
                $this->table.jarak,
                $this->table.statusaktif,
                $this->table.tglmulaiberlaku,

                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )

        )
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahritasi.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahritasi.kotasampai_id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kotasampai.statusaktif', '=', 'parameter.id');
        // ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahritasi.zona_id', 'zona.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kotadari_id')->nullable();
            $table->string('kotasampai_id')->nullable();
            // $table->string('zona_id')->nullable();
            $table->double('jarak', 15, 2)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            // $table->date('tglakhirberlaku')->nullable();
            // $table->integer('statusluarkota')->length(11)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kotadari_id', 'kotasampai_id', 'jarak', 'statusaktif', 'tglmulaiberlaku',  'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'kotadari_id') {
            return $query->orderBy('kotadari.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kotasampai_id') {
            return $query->orderBy('kotasampai.keterangan', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
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
                                // } elseif ($filters['field'] == 'statusluarkota') {
                                //     $query = $query->where('statusluarkota.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'kotadari_id') {
                                $query = $query->where('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kotasampai_id') {
                                $query = $query->where('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'zona_id') {
                                //     $query = $query->where('zona.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'jarak') {
                                $query = $query->whereRaw("format($this->table.jarak, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
                                    // } elseif ($filters['field'] == 'statusluarkota') {
                                    // $query = $query->orWhere('statusluarkota.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'kotadari_id') {
                                    $query = $query->orWhere('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'kotasampai_id') {
                                    $query = $query->orWhere('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                                    // } else if ($filters['field'] == 'zona_id') {
                                    //     $query = $query->orWhere('zona.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'jarak') {
                                    $query = $query->orWhereRaw("format($this->table.jarak, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglmulaiberlaku') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data): UpahRitasi
    {
        $upahritasi = new UpahRitasi();
        $upahritasi->parent_id = $data['parent_id'] ?? 0;
        $upahritasi->kotadari_id = $data['kotadari_id'];
        $upahritasi->kotasampai_id = $data['kotasampai_id'];
        $upahritasi->jarak = $data['jarak'];
        $upahritasi->nominalsupir = $data['nominalsupir'];
        $upahritasi->statusaktif = $data['statusaktif'];
        $upahritasi->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
        $upahritasi->modifiedby = auth('api')->user()->user;
        $upahritasi->info = html_entity_decode(request()->info);

        if (!$upahritasi->save()) {
            throw new \Exception('Error storing upah ritasi.');
        }

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($upahritasi->getTable()),
            'postingdari' => 'ENTRY UPAH RITASI',
            'idtrans' => $upahritasi->id,
            'nobuktitrans' => $upahritasi->id,
            'aksi' => 'ENTRY',
            'datajson' => $upahritasi->toArray(),
            'modifiedby' => $upahritasi->modifiedby
        ]);

        $detaillog = [];

        for ($i = 0; $i < count($data['container_id']); $i++) {

            $upahritasiDetail = (new UpahRitasiRincian())->processStore($upahritasi, [
                'upahritasi_id' => $upahritasi->id,
                'container_id' => $data['container_id'][$i],
                'liter' => $data['liter'][$i] ?? 0,
            ]);
            $detaillog[] = $upahritasiDetail->toArray();
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($upahritasiDetail->getTable()),
            'postingdari' => 'ENTRY UPAH RITASI RINCIAN',
            'idtrans' =>  $storedLogTrail['id'],
            'nobuktitrans' => $upahritasi->id,
            'aksi' => 'ENTRY',
            'datajson' => $detaillog,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $upahritasi;
    }

    public function processUpdate(UpahRitasi $upahritasi, array $data): UpahRitasi
    {
        $upahritasi->parent_id = $data['parent_id'] ?? 0;
        $upahritasi->kotadari_id = $data['kotadari_id'];
        $upahritasi->kotasampai_id = $data['kotasampai_id'];
        $upahritasi->jarak = $data['jarak'];
        $upahritasi->nominalsupir = $data['nominalsupir'];
        $upahritasi->statusaktif = $data['statusaktif'];
        $upahritasi->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
        $upahritasi->modifiedby = auth('api')->user()->user;
        $upahritasi->info = html_entity_decode(request()->info);

        if (!$upahritasi->save()) {
            throw new \Exception("Error updating service in header.");
        }

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($upahritasi->getTable()),
            'postingdari' => 'EDIT UPAH RITASI',
            'idtrans' => $upahritasi->id,
            'nobuktitrans' => $upahritasi->id,
            'aksi' => 'EDIT',
            'datajson' => $upahritasi->toArray(),
            'modifiedby' => $upahritasi->modifiedby
        ]);

        UpahRitasiRincian::where('upahritasi_id', $upahritasi->id)->delete();

        $detaillog = [];
        for ($i = 0; $i < count($data['container_id']); $i++) {
            $upahsupirDetail = (new UpahRitasiRincian())->processStore($upahritasi, [
                'upahritasi_id' => $upahritasi->id,
                'container_id' => $data['container_id'][$i],
                'liter' => $data['liter'][$i] ?? 0,
            ]);

            $detaillog[] = $upahsupirDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($upahsupirDetail->getTable()),
            'postingdari' => 'EDIT UPAH RITASI RINCIAN',
            'idtrans' =>  $storedLogTrail['id'],
            'nobuktitrans' => $upahritasi->id,
            'aksi' => 'EDIT',
            'datajson' => $detaillog,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $upahritasi;
    }

    public function processDestroy($id): UpahRitasi
    {
        $getDetail = UpahRitasiRincian::where('upahritasi_id', $id)->get();
        $upahRitasi = new UpahRitasi();
        $upahRitasi = $upahRitasi->lockAndDestroy($id);

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($upahRitasi->getTable()),
            'postingdari' => 'DELETE UPAH RITASI',
            'idtrans' => $upahRitasi->id,
            'nobuktitrans' => $upahRitasi->id,
            'aksi' => 'DELETE',
            'datajson' => $upahRitasi->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        // DELETE UPAH RITASI RINCIAN

        $logTrailUpahRitasiRincian = (new LogTrail())->processStore([
            'namatabel' => 'UPAHRITASIRINCIAN',
            'postingdari' => 'DELETE UPAH RITASI RINCIAN',
            'idtrans' => $storedLogTrail['id'],
            'nobuktitrans' => $upahRitasi->id,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        return $upahRitasi;
    }
    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $upahRitasi = UpahRitasi::find($data['Id'][$i]);

            $upahRitasi->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($upahRitasi->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($upahRitasi->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF UPAH RITASI',
                    'idtrans' => $upahRitasi->id,
                    'nobuktitrans' => $upahRitasi->id,
                    'aksi' => $aksi,
                    'datajson' => $upahRitasi->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $upahRitasi;
    }
}
