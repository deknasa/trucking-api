<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Helpers\App;
use Illuminate\Database\Schema\Blueprint;

class UpahSupir extends MyModel
{
    use HasFactory;

    protected $table = 'upahsupir';

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

    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }


    public function get()
    {
        $this->setRequestParameters();

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'UpahSupirController';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '';

        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) ;

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );

            Schema::create($temtabel, function (Blueprint $table) {
                $table->bigInteger('id')->nullable();
                $table->longText('parent_id')->nullable();
                $table->longText('tarif')->nullable();
                $table->longText('kotadari_id')->nullable();
                $table->longText('kotasampai_id')->nullable();
                $table->longText('penyesuaian')->nullable();
                $table->longText('jarak')->nullable();
                $table->longText('zona_id')->nullable()->nullable();
                $table->longText('statusaktif')->nullable();
                $table->longText('statusaktif_text')->nullable();
                $table->bigInteger('statusaktif_id')->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->longText('statusluarkota')->nullable();
                $table->longText('gambar')->nullable();
                $table->longText('keterangan')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->longText('modifiedby')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->longText('judulLaporan')->nullable();
                $table->longText('judul')->nullable();
            });

            $tempParent = DB::table($this->table)->from(DB::raw("upahsupir with (readuncommitted)"))
                ->select(
                    'upahsupir.id',
                    'upahsupir.parent_id',
                    'kota.keterangan'
                )
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'kota.id', 'upahsupir.kotasampai_id');

            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('keterangan')->nullable();
            });
            DB::table($temp)->insertUsing(["id", 'parent_id', 'keterangan'], $tempParent);

            $query = DB::table($this->table)->from(DB::raw("upahsupir with (readuncommitted)"))
                ->select(
                    'upahsupir.id',
                    'parent.keterangan as parent_id',
                    'tarif.tujuan as tarif',
                    'kotadari.keterangan as kotadari_id',
                    'kotasampai.keterangan as kotasampai_id',
                    'upahsupir.penyesuaian',
                    DB::raw("CONCAT(upahsupir.jarak, ' KM') as jarak"),
                    'zona.keterangan as zona_id',
                    'parameter.memo as statusaktif',
                    'parameter.text as statusaktif_text',
                    'upahsupir.statusaktif as statusaktif_id',
                    'upahsupir.tglmulaiberlaku',
                    // 'upahsupir.tglakhirberlaku',
                    'statusluarkota.memo as statusluarkota',
                    'upahsupir.gambar',
                    'upahsupir.keterangan',
                    'upahsupir.created_at',
                    'upahsupir.modifiedby',
                    'upahsupir.updated_at',
                    DB::raw("'Laporan Upah Supir' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul")
                )
                ->leftJoin(DB::raw("$temp as parent with (readuncommitted)"), 'parent.id', '=', 'upahsupir.parent_id')
                ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', '=', 'tarif.id')
                ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
                ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'upahsupir.statusaktif', 'parameter.id')
                ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'upahsupir.statusluarkota', 'statusluarkota.id')
                ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahsupir.zona_id', 'zona.id');

            DB::table($temtabel)->insertUsing([
                'id',
                'parent_id',
                'tarif',
                'kotadari_id',
                'kotasampai_id',
                'penyesuaian',
                'jarak',
                'zona_id',
                'statusaktif',
                'statusaktif_text',
                'statusaktif_id',
                'tglmulaiberlaku',
                'statusluarkota',
                'gambar',
                'keterangan',
                'created_at',
                'modifiedby',
                'updated_at',
                'judulLaporan',
                'judul',
            ], $query);
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            $temtabel = $querydata->namatabel;
        }

        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.parent_id',
                'a.tarif',
                'a.kotadari_id',
                'a.kotasampai_id',
                'a.penyesuaian',
                'a.jarak',
                'a.zona_id',
                'a.statusaktif',
                'a.tglmulaiberlaku',
                'a.statusluarkota',
                'a.gambar',
                'a.keterangan',
                'a.created_at',
                'a.modifiedby',
                'a.updated_at',
                'a.judulLaporan',
                'a.judul',
            );




        $this->filter($query);
       
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('a.statusaktif_id', '=', $statusaktif->id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        
        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
       

        return $data;
    }
    public function findAll($id)
    {

        $tempParent = DB::table('upahsupir')->from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'upahsupir.id',
                'upahsupir.parent_id',
                'kota.keterangan'
            )
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'kota.id', 'upahsupir.kotasampai_id');
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('keterangan')->nullable();
        });
        DB::table($temp)->insertUsing(["id", 'parent_id', 'keterangan'], $tempParent);

        $query = DB::table('upahsupir')->select(
            'upahsupir.id',
            DB::raw("(case when upahsupir.parent_id=0 then null else upahsupir.parent_id end) as parent_id"),
            'parent.keterangan as parent',
            DB::raw("(case when upahsupir.tarif_id=0 then null else upahsupir.tarif_id end) as tarif_id"),
            DB::raw("TRIM(tarif.tujuan) as tarif"),
            'upahsupir.kotadari_id',
            DB::raw("TRIM(kotadari.keterangan) as kotadari"),
            'upahsupir.keterangan',
            'upahsupir.penyesuaian',
            'upahsupir.kotasampai_id',
            DB::raw("TRIM(kotasampai.keterangan) as kotasampai"),
            'upahsupir.jarak',
            'zona.keterangan as zona',
            DB::raw("(case when upahsupir.zona_id=0 then null else upahsupir.zona_id end) as zona_id"),
            'upahsupir.statusaktif',

            'upahsupir.tglmulaiberlaku',
            // 'upahsupir.tglakhirberlaku',
            'upahsupir.statusluarkota',
            'statusluarkota.text as statusluarkotas',
            'upahsupir.gambar',

            'upahsupir.modifiedby',
            'upahsupir.updated_at'
        )
            ->leftJoin(DB::raw("$temp as parent with (readuncommitted)"), 'parent.id', '=', 'upahsupir.parent_id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahsupir.zona_id', 'zona.id')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'upahsupir.statusluarkota', 'statusluarkota.id')

            ->where('upahsupir.id', $id);

        $data = $query->first();
        return $data;
    }
    public function upahsupirRincian()
    {
        return $this->hasMany(upahsupirRincian::class, 'upahsupir_id');
    }

    public function default()
    {
        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->unsignedBigInteger('statusluarkota')->nullable();
            $table->unsignedBigInteger('statussimpankandang')->nullable();
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusaktif = $status->id ?? 0;

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'UPAH SUPIR LUAR KOTA')
            ->where('subgrp', '=', 'UPAH SUPIR LUAR KOTA')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusluarkota = $status->id ?? 0;

        $iddefaultstatusluarkota =  $status->id;

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS SIMPAN KANDANG')
            ->where('subgrp', '=', 'STATUS SIMPAN KANDANG')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusSimpanKandang = $status->id ?? 0;

        DB::table($tempdefault)->insert(
            ["statusaktif" => $iddefaultstatusaktif, "statusluarkota" => $iddefaultstatusluarkota, "statussimpankandang" => $iddefaultstatusSimpanKandang]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusluarkota',
                'statussimpankandang'
            );

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    {

        return $query->select(
            DB::raw(
                "$this->table.id,
                '$this->table.parent_id',
                kotadari.keterangan as kotadari_id,
                kotasampai.keterangan as kotasampai_id,
                '$this->table.penyesuaian',
                zona.keterangan as zona_id,
                $this->table.jarak,
                $this->table.statusaktif,
                $this->table.tglmulaiberlaku,
                $this->table.statusluarkota,

                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )

        )
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahsupir.zona_id', 'zona.id');
    }

    public function createTemp(string $modelTable)
    {

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('parent_id')->nullable();
            $table->string('kotadari_id')->nullable();
            $table->string('kotasampai_id')->nullable();
            $table->string('penyesuaian')->nullable();
            $table->string('zona_id')->nullable()->nullable();
            $table->double('jarak', 15, 2)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            // $table->date('tglakhirberlaku')->nullable();
            $table->integer('statusluarkota')->length(11)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'parent_id', 'kotadari_id', 'kotasampai_id', 'penyesuaian', 'zona_id', 'jarak', 'statusaktif', 'tglmulaiberlaku', 'statusluarkota', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'kotadari_id') {
            return $query->orderBy('a.kotadari_id', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kotasampai_id') {
            return $query->orderBy('a.kotasampai_id', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'zona_id') {
            return $query->orderBy('a.zona_id', $this->params['sortOrder']);
        } else {
            return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('a.statusaktif_text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'parent_id') {
                            $query = $query->where('a.parent_id', '=', $filters['data']);
                        } elseif ($filters['field'] == 'statusluarkota') {
                            $query = $query->where('a.statusluarkota', '=', $filters['data']);
                        } else if ($filters['field'] == 'kotadari_id') {
                            $query = $query->where('a.kotadari_id', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kotasampai_id') {
                            $query = $query->where('a.kotasampai_id', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->where('a.zona_id', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'jarak') {
                            $query = $query->whereRaw("a.jarak LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('a.statusaktif_text', '=', $filters['data']);
                            } elseif ($filters['field'] == 'statusluarkota') {
                                $query = $query->orWhere('a.statusluarkota', '=', $filters['data']);
                            } else if ($filters['field'] == 'kotadari_id') {
                                $query = $query->orWhere('a.kotadari_id', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'parent_id') {
                                $query = $query->orWhere('a.parent_id', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kotasampai_id') {
                                $query = $query->orWhere('a.kotasampai_id', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'zona_id') {
                                $query = $query->orWhere('a.zona_id', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'jarak') {
                                $query = $query->orWhereRaw("a.jarak LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function cekValidasi($id)
    {
        $rekap = DB::table('tarif')
            ->from(
                DB::raw("tarif as a with (readuncommitted)")
            )
            ->select(
                'a.upahsupir_id'
            )
            ->where('a.upahsupir_id', '=', $id)
            ->first();
        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'tarif',
                'kodeerror' => 'SATL'
            ];
            goto selesai;
        }
        $sp = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.upah_id'
            )
            ->where('a.upah_id', '=', $id)
            ->first();
        if (isset($sp)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'surat pengantar',
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
    public function validasiUpahSupirInputTrip($dari, $sampai, $container, $statusContainer)
    {
        $query = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
            ->join(DB::raw("upahsupirrincian with (readuncommitted)"), 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
            ->where('upahsupir.kotadari_id', $dari)
            ->where('upahsupir.kotasampai_id', $sampai)
            ->where('upahsupirrincian.container_id', $container)
            ->where('upahsupirrincian.statuscontainer_id', $statusContainer)
            ->where('upahsupirrincian.nominalsupir', '!=', '0')
            ->first();

        return $query;
    }

    private function deleteFiles(UpahSupir $upahsupir)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoUpahSupir = [];
        $photoUpahSupir = json_decode($upahsupir->gambar, true);
        if ($photoUpahSupir) {
            foreach ($photoUpahSupir as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoUpahSupir[] = "upahsupir/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoUpahSupir);
        }
    }

    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            $storedFile = Storage::putFileAs($destinationFolder, $file, $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    public function processStore(array $data): UpahSupir
    {
        try {
            $group = 'STATUS SIMPAN KANDANG';
            $text = 'SIMPAN KANDANG';

            $statusSimpanKandang = DB::table('parameter')
                ->where('grp', $group)
                ->where('text', $text)
                ->first();

            $kandang = DB::table("kota")->from(DB::raw("kota with (readuncommitted)"))
                ->where('kodekota', 'KANDANG')
                ->first();
            $belawan = DB::table("kota")->from(DB::raw("kota with (readuncommitted)"))
                ->where('kodekota', 'BELAWAN')
                ->first();

            $upahsupir = new UpahSupir();
            $upahsupir->kotadari_id = $data['kotadari_id'];
            $upahsupir->parent_id = $data['parent_id'] ?? 0;
            $upahsupir->tarif_id = $data['tarif_id'] ?? 0;
            $upahsupir->kotasampai_id = $data['kotasampai_id'];
            $upahsupir->penyesuaian = $data['penyesuaian'];
            $upahsupir->jarak = $data['jarak'];
            $upahsupir->zona_id = ($data['zona_id'] == null) ? 0 : $data['zona_id'] ?? 0;
            $upahsupir->statusaktif = $data['statusaktif'];
            $upahsupir->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
            $upahsupir->statussimpankandang = $data['statussimpankandang'];
            $upahsupir->statusluarkota = $data['statusluarkota'] ?? '';
            $upahsupir->keterangan = $data['keterangan'] ?? '';
            $upahsupir->modifiedby = auth('api')->user()->user;
            $this->deleteFiles($upahsupir);

            if (array_key_exists('gambar', $data)) {
                $upahsupir->gambar = $this->storeFiles($data['gambar'], 'upahsupir');
            } else {
                $upahsupir->gambar = '';
            }

            if (!$upahsupir->save()) {
                throw new \Exception("Error storing upah supir.");
            }

            $storedLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($upahsupir->getTable()),
                'postingdari' => 'ENTRY UPAH SUPIR',
                'idtrans' => $upahsupir->id,
                'nobuktitrans' => $upahsupir->id,
                'aksi' => 'ENTRY',
                'datajson' => $upahsupir->toArray(),
                'modifiedby' => $upahsupir->modifiedby
            ]);

            $detaillog = [];
            for ($i = 0; $i < count($data['nominalsupir']); $i++) {
                $upahsupirDetail = (new UpahSupirRincian())->processStore($upahsupir, [
                    'upahsupir_id' => $upahsupir->id,
                    'container_id' => $data['container_id'][$i],
                    'statuscontainer_id' => $data['statuscontainer_id'][$i],
                    'nominalsupir' => $data['nominalsupir'][$i],
                    'nominalkenek' => $data['nominalkenek'][$i] ?? 0,
                    'nominalkomisi' => $data['nominalkomisi'][$i] ?? 0,
                    'nominaltol' =>  $data['nominaltol'][$i] ?? 0,
                    'liter' => $data['liter'][$i] ?? 0
                ]);

                $detaillog[] = $upahsupirDetail->toArray();
            }
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($upahsupirDetail->getTable()),
                'postingdari' => 'ENTRY UPAH SUPIR RINCIAN',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $upahsupir->id,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->user,
            ]);

            if ($data['statussimpankandang'] == $statusSimpanKandang->id) {
                $getBelawanKandang = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                    ->select('id', 'jarak')
                    ->where('kotadari_id', $belawan->id)
                    ->where('kotasampai_id', $kandang->id)
                    ->first();

                $getRincianBelawanKandang = DB::table("upahsupirrincian")->from(DB::raw("upahsupirrincian with (readuncommitted)"))
                    ->where('upahsupir_id', $getBelawanKandang->id)
                    ->get();
                $jarakKandang = $data['jarak'] - $getBelawanKandang->jarak;

                $upahsupirKandang = new UpahSupir();
                $upahsupirKandang->kotadari_id = $kandang->id;
                $upahsupirKandang->parent_id = $kandang->parent_id ?? 0;
                $upahsupirKandang->tarif_id = $kandang->tarif_id ?? 0;
                $upahsupirKandang->kotasampai_id = $data['kotasampai_id'];
                $upahsupirKandang->penyesuaian = $data['penyesuaian'];
                $upahsupirKandang->jarak = ($jarakKandang < 0) ? 0 : $jarakKandang;
                $upahsupirKandang->zona_id = ($data['zona_id'] == null) ? 0 : $data['zona_id'] ?? 0;
                $upahsupirKandang->statusaktif = $data['statusaktif'];
                $upahsupirKandang->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
                $upahsupirKandang->statussimpankandang = $data['statussimpankandang'];
                $upahsupirKandang->keterangan = $data['keterangan'];
                $upahsupirKandang->modifiedby = auth('api')->user()->user;
                $this->deleteFiles($upahsupirKandang);
                if (array_key_exists('gambar', $data)) {
                    $upahsupirKandang->gambar = $this->storeFiles($data['gambar'], 'upahsupir');
                } else {
                    $upahsupirKandang->gambar = '';
                }
                $upahsupirKandang->save();

                $logTrailKandang = (new LogTrail())->processStore([
                    'namatabel' => strtoupper($upahsupirKandang->getTable()),
                    'postingdari' => 'ENTRY UPAH SUPIR',
                    'idtrans' => $upahsupirKandang->id,
                    'nobuktitrans' => $upahsupirKandang->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $upahsupirKandang->toArray(),
                    'modifiedby' => auth('api')->user()->user,
                ]);

                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($data['nominalsupir']); $i++) {
                    $nomSupir = ($data['nominalsupir'][$i] == 0) ? 0 : $data['nominalsupir'][$i] - $getRincianBelawanKandang[$i]->nominalsupir;
                    $nomKenek = ($data['nominalkenek'][$i] == 0) ? 0 : $data['nominalkenek'][$i] - $getRincianBelawanKandang[$i]->nominalkenek;
                    $nomKomisi = ($data['nominalkomisi'][$i] == 0) ? 0 : $data['nominalkomisi'][$i] - $getRincianBelawanKandang[$i]->nominalkomisi;
                    $nomTol = ($data['nominaltol'][$i] == 0) ? 0 : $data['nominaltol'][$i] - $getRincianBelawanKandang[$i]->nominaltol;
                    $liter = ($data['liter'][$i] == 0) ? 0 : $data['liter'][$i] - $getRincianBelawanKandang[$i]->liter;

                    $upahsupirDetail = (new UpahSupirRincian())->processStore($upahsupir, [
                        'upahsupir_id' => $upahsupirKandang->id,
                        'container_id' => $data['container_id'][$i],
                        'statuscontainer_id' => $data['statuscontainer_id'][$i],
                        'nominalsupir' => ($nomSupir < 0) ? 0 : $nomSupir,
                        'nominalkenek' => ($nomKenek < 0) ? 0 : $nomKenek,
                        'nominalkomisi' => ($nomKomisi < 0) ? 0 : $nomKomisi,
                        'nominaltol' => ($nomTol < 0) ? 0 : $nomTol,
                        'liter' => ($liter < 0) ? 0 : $liter,
                    ]);
                    $detaillog[] = $upahsupirDetail->toArray();
                }
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($upahsupirDetail->getTable()),
                    'postingdari' => 'ENTRY UPAH SUPIR RINCIAN',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $upahsupirKandang->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->user,
                ]);
            }
            return $upahsupir;
        } catch (\Throwable $th) {
            $this->deleteFiles($upahsupir);
            throw $th;
        }
    }

    public function processUpdate(UpahSupir $upahsupir, array $data): UpahSupir
    {
        try {
            $upahsupir->kotadari_id = $data['kotadari_id'];
            $upahsupir->parent_id = $data['parent_id'] ?? 0;
            $upahsupir->tarif_id = $data['tarif_id'] ?? 0;
            $upahsupir->kotasampai_id = $data['kotasampai_id'];
            $upahsupir->penyesuaian = $data['penyesuaian'];
            $upahsupir->jarak = $data['jarak'];
            $upahsupir->zona_id = ($data['zona_id'] == null) ? 0 : $data['zona_id'] ?? 0;
            $upahsupir->statusaktif = $data['statusaktif'];
            $upahsupir->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
            $upahsupir->keterangan = $data['keterangan'];
            $upahsupir->modifiedby = auth('api')->user()->user;

            $this->deleteFiles($upahsupir);
            if (array_key_exists('gambar', $data)) {
                $upahsupir->gambar = $this->storeFiles($data['gambar'], 'upahsupir');
            } else {
                $upahsupir->gambar = '';
            }
            if (!$upahsupir->save()) {
                throw new \Exception("Error updating upah supir.");
            }

            $storedLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($upahsupir->getTable()),
                'postingdari' => 'EDIT UPAH SUPIR',
                'idtrans' => $upahsupir->id,
                'nobuktitrans' => $upahsupir->id,
                'aksi' => 'EDIT',
                'datajson' => $upahsupir->toArray(),
                'modifiedby' => $upahsupir->modifiedby
            ]);

            UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->delete();
            /* Store detail */
            $detaillog = [];
            for ($i = 0; $i < count($data['nominalsupir']); $i++) {
                $upahsupirDetail = (new UpahSupirRincian())->processStore($upahsupir, [
                    'upahsupir_id' => $upahsupir->id,
                    'container_id' => $data['container_id'][$i],
                    'statuscontainer_id' => $data['statuscontainer_id'][$i],
                    'nominalsupir' => $data['nominalsupir'][$i],
                    'nominalkenek' => $data['nominalkenek'][$i] ?? 0,
                    'nominalkomisi' => $data['nominalkomisi'][$i] ?? 0,
                    'nominaltol' =>  $data['nominaltol'][$i] ?? 0,
                    'liter' => $data['liter'][$i] ?? 0,
                ]);
                $detaillog[] = $upahsupirDetail->toArray();
            }
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($upahsupirDetail->getTable()),
                'postingdari' => 'EDIT UPAH SUPIR RINCIAN',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $upahsupir->id,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->user,
            ]);

            return $upahsupir;
        } catch (\Throwable $th) {
            $this->deleteFiles($upahsupir);
            throw $th;
        }
    }

    public function processDestroy($id): UpahSupir
    {
        $getDetail = UpahSupirRincian::lockForUpdate()->where('upahsupir_id', $id)->get();

        $upahSupir = new UpahSupir();
        $upahSupir = $upahSupir->lockAndDestroy($id);

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($upahSupir->getTable()),
            'postingdari' => 'DELETE UPAH SUPIR',
            'idtrans' => $upahSupir->id,
            'nobuktitrans' => $upahSupir->id,
            'aksi' => 'DELETE',
            'datajson' => $upahSupir->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        $logTrailUpahSupirRincian = (new LogTrail())->processStore([
            'namatabel' => 'UPAHSUPIRRINCIAN',
            'postingdari' => 'DELETE UPAH SUPIR RINCIAN',
            'idtrans' => $storedLogTrail['id'],
            'nobuktitrans' => $upahSupir->id,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $upahSupir;
    }
}
