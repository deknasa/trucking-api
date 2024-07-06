<?php

namespace App\Models;

use App\Helpers\App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class UpahSupirTangki extends MyModel
{
    use HasFactory;

    protected $table = 'upahsupirtangki';

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

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'UpahSupirTangkiController';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        // dd(request()->isParent);

        $aktif = request()->aktif ?? '';
        $isParent = request()->isParent ?? false;

        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

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
                $table->longText('tariftangki')->nullable();
                $table->longText('kotadari_id')->nullable();
                $table->longText('kotasampai_id')->nullable();
                $table->longText('penyesuaian')->nullable();
                $table->longText('jarak')->nullable();
                $table->longText('statusaktif')->nullable();
                $table->longText('statusaktif_text')->nullable();
                $table->bigInteger('statusaktif_id')->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->longText('gambar')->nullable();
                $table->longText('keterangan')->nullable();
                $table->longText('modifiedby')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->longText('judulLaporan')->nullable();
                $table->longText('judul')->nullable();
            });

            $tempParent = DB::table($this->table)->from(DB::raw("upahsupirtangki with (readuncommitted)"))
                ->select(
                    'upahsupirtangki.id',
                    'upahsupirtangki.parent_id',
                    'kota.keterangan'
                )
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'kota.id', 'upahsupirtangki.kotasampai_id');

            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('keterangan')->nullable();
            });
            DB::table($temp)->insertUsing(["id", 'parent_id', 'keterangan'], $tempParent);

            $query = DB::table($this->table)->from(DB::raw("upahsupirtangki with (readuncommitted)"))
                ->select(
                    'upahsupirtangki.id',
                    'parent.keterangan as parent_id',
                    'tariftangki.tujuan as tariftangki',
                    'kotadari.keterangan as kotadari_id',
                    'kotasampai.keterangan as kotasampai_id',
                    'upahsupirtangki.penyesuaian',
                    DB::raw("CONCAT(upahsupirtangki.jarak, ' KM') as jarak"),
                    'parameter.memo as statusaktif',
                    'parameter.text as statusaktif_text',
                    'upahsupirtangki.statusaktif as statusaktif_id',
                    'upahsupirtangki.tglmulaiberlaku',
                    'upahsupirtangki.gambar',
                    'upahsupirtangki.keterangan',
                    'upahsupirtangki.modifiedby',
                    'upahsupirtangki.created_at',
                    'upahsupirtangki.updated_at',
                    DB::raw("'Laporan Upah Supir Tangki' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul")
                )
                ->leftJoin(DB::raw("$temp as parent with (readuncommitted)"), 'parent.id', '=', 'upahsupirtangki.parent_id')
                ->leftJoin(DB::raw("tariftangki with (readuncommitted)"), 'upahsupirtangki.tariftangki_id', '=', 'tariftangki.id')
                ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupirtangki.kotadari_id')
                ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupirtangki.kotasampai_id')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'upahsupirtangki.statusaktif', 'parameter.id');
            DB::table($temtabel)->insertUsing([
                'id',
                'parent_id',
                'tariftangki',
                'kotadari_id',
                'kotasampai_id',
                'penyesuaian',
                'jarak',
                'statusaktif',
                'statusaktif_text',
                'statusaktif_id',
                'tglmulaiberlaku',
                'gambar',
                'keterangan',
                'modifiedby',
                'created_at',
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
        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.parent_id',
                'a.tariftangki',
                'a.kotadari_id',
                'a.kotasampai_id',
                'a.penyesuaian',
                'a.jarak',
                'a.statusaktif',
                'a.tglmulaiberlaku',
                'a.gambar',
                'a.keterangan',
                'a.created_at',
                'a.modifiedby',
                'a.updated_at',
                'a.judulLaporan',
                'a.judul',
            );


        // dd($query->get());

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
        // dd($isParent);
        if ($isParent == true) {
            // dump($isParent);
            // dd($isParent == true);
            $query->whereRaw("isnull(a.penyesuaian,'')=''");
        } else {
            $query->whereRaw("1=1");
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
        return $data;
    }

    public function getLookup()
    {

        $this->setRequestParameters();

        $proses = request()->proses ?? 'reload';
        $tglbukti = date('Y-m-d', strtotime(request()->tglbukti)) ?? '1900-01-01';
        $user = auth('api')->user()->name;
        $class = 'UpahSupirTangkiLookupController';
        $aktif = request()->aktif ?? '';
        $statusPenyesuaian = request()->statuspenyesuaian;
        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

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

            Schema::create($temtabel, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('upah_id')->nullable();
                $table->unsignedBigInteger('tarif_id')->nullable();
                $table->string('tarif', 200)->nullable();
                $table->unsignedBigInteger('kotadari_id')->nullable();
                $table->string('kotadari', 200)->nullable();
                $table->unsignedBigInteger('kotasampai_id')->nullable();
                $table->string('kotasampai', 200)->nullable();
                $table->string('penyesuaian', 200)->nullable();
                $table->double('jarak', 15, 2)->nullable();
                $table->longText('statusaktif')->nullable();
                $table->longText('statusaktif_text')->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->string('modifiedby', 50)->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
                $table->longText('kotadarisampai')->nullable();
                $table->string('zonadari', 200)->nullable();
                $table->string('zonasampai', 200)->nullable();
                $table->string('container', 200)->nullable();
                $table->string('statuscontainer', 200)->nullable();
                $table->double('nominalsupir', 15, 2)->nullable();
                $table->double('nominalkenek', 15, 2)->nullable();
                $table->double('nominalkomisi', 15, 2)->nullable();
                $table->double('omset', 15, 2)->nullable();
            });

            $temptariftangki = '##temptariftangki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temptariftangki, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->string('tujuan', 200)->nullable();
                $table->double('omset', 15, 2)->nullable();
            });
            $querytarif = db::table('tariftangki')->from(db::raw("tariftangki a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan',
                    "a.nominal as omset"
                )
                ->whereRaw("cast('" . $tglbukti . "' as datetime)>=a.tglmulaiberlaku")
                ->where('a.statusaktif', 1)
                ->orderby('a.id', 'asc');

            DB::table($temptariftangki)->insertUsing([
                'id',
                'tujuan',
                'omset'
            ],  $querytarif);

            $queryupahsupir = db::table('upahsupirtangki')->from(db::raw("upahsupirtangki a with (readuncommitted)"))
                ->select(
                    DB::raw("row_number() Over(Order By a.id) as id"),
                    'a.id as upah_id',
                    'tariftangki.id as tarif_id',
                    'tariftangki.tujuan as tarif',
                    'a.kotadari_id',
                    'kotadari.kodekota as kotadari',
                    'a.kotasampai_id',
                    'kotasampai.kodekota as kotasampai',
                    'a.penyesuaian',
                    'a.jarak',
                    'parameter.memo as statusaktif',
                    'parameter.text as statusaktif_text',
                    'a.tglmulaiberlaku',
                    'a.modifiedby',
                    'a.created_at',
                    'a.updated_at',
                    DB::raw("(trim(kotadari.kodekota)+' - '+trim(kotasampai.kodekota)) as kotadarisampai"),

                    DB::raw("null as zonadari"),
                    DB::raw("null as zonasampai"),
                    DB::raw("null as container"),
                    DB::raw("null as statuscontainer"),
                    DB::raw("0 as nominalsupir"),
                    DB::raw("0 as nominalkenek"),
                    DB::raw("0 as nominalkomisi"),
                    db::raw("isnull(tariftangki.omset,0) as omset"),
                )
                ->join(DB::raw("$temptariftangki as tariftangki with (readuncommitted)"), 'a.tariftangki_id', 'tariftangki.id')
                ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'a.kotadari_id')
                ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'a.kotasampai_id')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'a.statusaktif', 'parameter.id')
                ->whereRaw("cast('" . $tglbukti . "' as datetime)>=a.tglmulaiberlaku")
                ->where('a.statusaktif', '=', 1)
                ->orderby('a.id', 'asc');


            DB::table($temtabel)->insertUsing([
                'id',
                'upah_id',
                'tarif_id',
                'tarif',
                'kotadari_id',
                'kotadari',
                'kotasampai_id',
                'kotasampai',
                'penyesuaian',
                'jarak',
                'statusaktif',
                'statusaktif_text',
                'tglmulaiberlaku',
                'modifiedby',
                'created_at',
                'updated_at',
                'kotadarisampai',
                'zonadari',
                'zonasampai',
                'container',
                'statuscontainer',
                'nominalsupir',
                'nominalkenek',
                'nominalkomisi',
                'omset',
            ], $queryupahsupir);
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
                'a.upah_id',
                'a.tarif_id',
                'a.tarif',
                'a.kotadari_id',
                'a.kotadari',
                'a.kotasampai_id',
                'a.kotasampai',
                'a.penyesuaian',
                'a.jarak',
                'a.statusaktif',
                'a.statusaktif_text',
                'a.tglmulaiberlaku',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.kotadarisampai',
                'a.omset',
            );
            
            if($statusPenyesuaian == 662){
                $query->whereRaw("isnull(a.penyesuaian,'') != ''");
            }else{
                $query->whereRaw("isnull(a.penyesuaian,'') = ''");
            }

        $this->filter($query);
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

        DB::table($tempdefault)->insert(
            ["statusaktif" => $iddefaultstatusaktif]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
            );

        $data = $query->first();

        return $data;
    }
    public function selectColumns()
    {
        $temtabel = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temtabel, function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->longText('parent_id')->nullable();
            $table->longText('tariftangki')->nullable();
            $table->longText('kotadari_id')->nullable();
            $table->longText('kotasampai_id')->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->longText('jarak')->nullable();
            $table->longText('statusaktif')->nullable();
            $table->longText('statusaktif_text')->nullable();
            $table->bigInteger('statusaktif_id')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('keterangan')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('updated_at')->nullable();
        });


        $tempParent = DB::table("upahsupirtangki")->from(DB::raw("upahsupirtangki with (readuncommitted)"))
            ->select(
                'upahsupirtangki.id',
                'upahsupirtangki.parent_id',
                'kota.keterangan'
            )
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'kota.id', 'upahsupirtangki.kotasampai_id');

        $temp = '##tempParent' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('keterangan')->nullable();
        });
        DB::table($temp)->insertUsing(["id", 'parent_id', 'keterangan'], $tempParent);


        $query = DB::table($this->table)->from(DB::raw("upahsupirtangki with (readuncommitted)"))
            ->select(
                'upahsupirtangki.id',
                'parent.keterangan as parent_id',
                'tariftangki.tujuan as tariftangki',
                'kotadari.keterangan as kotadari_id',
                'kotasampai.keterangan as kotasampai_id',
                'upahsupirtangki.penyesuaian',
                DB::raw("CONCAT(upahsupirtangki.jarak, ' KM') as jarak"),
                'parameter.memo as statusaktif',
                'parameter.text as statusaktif_text',
                'upahsupirtangki.statusaktif as statusaktif_id',
                'upahsupirtangki.tglmulaiberlaku',
                'upahsupirtangki.gambar',
                'upahsupirtangki.keterangan',
                'upahsupirtangki.created_at',
                'upahsupirtangki.modifiedby',
                'upahsupirtangki.updated_at',
            )
            ->leftJoin(DB::raw("$temp as parent with (readuncommitted)"), 'parent.id', '=', 'upahsupirtangki.parent_id')
            ->leftJoin(DB::raw("tariftangki with (readuncommitted)"), 'upahsupirtangki.tariftangki_id', '=', 'tariftangki.id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupirtangki.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupirtangki.kotasampai_id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'upahsupirtangki.statusaktif', 'parameter.id');

        DB::table($temtabel)->insertUsing([
            'id',
            'parent_id',
            'tariftangki',
            'kotadari_id',
            'kotasampai_id',
            'penyesuaian',
            'jarak',
            'statusaktif',
            'statusaktif_text',
            'statusaktif_id',
            'tglmulaiberlaku',
            'gambar',
            'keterangan',
            'created_at',
            'modifiedby',
            'updated_at',
        ], $query);

        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.parent_id',
                'a.tariftangki',
                'a.kotadari_id',
                'a.kotasampai_id',
                'a.penyesuaian',
                'a.jarak',
                'a.statusaktif',
                'a.statusaktif_text',
                'a.statusaktif_id',
                'a.tglmulaiberlaku',
                'a.gambar',
                'a.keterangan',
                'a.created_at',
                'a.modifiedby',
                'a.updated_at',
            );


        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->longText('parent_id')->nullable();
            $table->longText('tariftangki')->nullable();
            $table->longText('kotadari_id')->nullable();
            $table->longText('kotasampai_id')->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->longText('jarak')->nullable();
            $table->longText('statusaktif')->nullable();
            $table->longText('statusaktif_text')->nullable();
            $table->bigInteger('statusaktif_id')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('keterangan')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        $this->setRequestParameters();
        $query = $this->selectColumns();
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'parent_id',
            'tariftangki',
            'kotadari_id',
            'kotasampai_id',
            'penyesuaian',
            'jarak',
            'statusaktif',
            'statusaktif_text',
            'statusaktif_id',
            'tglmulaiberlaku',
            'gambar',
            'keterangan',
            'created_at',
            'modifiedby',
            'updated_at',
        ], $models);
        return $temp;
    }


    public function findAll($id)
    {

        $tempParent = DB::table('upahsupirtangki')->from(DB::raw("upahsupirtangki with (readuncommitted)"))
            ->select(
                'upahsupirtangki.id',
                'upahsupirtangki.parent_id',
                'kota.keterangan'
            )
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'kota.id', 'upahsupirtangki.kotasampai_id');
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('keterangan')->nullable();
        });
        DB::table($temp)->insertUsing(["id", 'parent_id', 'keterangan'], $tempParent);

        $query = DB::table('upahsupirtangki')->select(
            'upahsupirtangki.id',
            DB::raw("(case when upahsupirtangki.parent_id=0 then null else upahsupirtangki.parent_id end) as parent_id"),
            'parent.keterangan as parent',
            DB::raw("(case when upahsupirtangki.tariftangki_id=0 then null else upahsupirtangki.tariftangki_id end) as tariftangki_id"),
            DB::raw("(trim(tariftangki.tujuan)+ (CASE WHEN isnull(tariftangki.penyesuaian,'')='' then '' ELSE ' - '+trim(tariftangki.penyesuaian) end))  as tariftangki"),
            DB::raw("(case when upahsupirtangki.kotadari_id=0 then null else upahsupirtangki.kotadari_id end) as kotadari_id"),
            DB::raw("TRIM(kotadari.keterangan) as kotadari"),
            'upahsupirtangki.keterangan',
            'upahsupirtangki.penyesuaian',
            DB::raw("(case when upahsupirtangki.kotasampai_id=0 then null else upahsupirtangki.kotasampai_id end) as kotasampai_id"),
            DB::raw("TRIM(kotasampai.keterangan) as kotasampai"),

            'upahsupirtangki.jarak',
            'upahsupirtangki.statusaktif',
            'upahsupirtangki.tglmulaiberlaku',
            'upahsupirtangki.gambar',
            'upahsupirtangki.modifiedby',
            'upahsupirtangki.updated_at'
        )
            ->leftJoin(DB::raw("$temp as parent with (readuncommitted)"), 'parent.id', '=', 'upahsupirtangki.parent_id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupirtangki.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupirtangki.kotasampai_id')
            ->leftJoin(DB::raw("tariftangki with (readuncommitted)"), 'upahsupirtangki.tariftangki_id', 'tariftangki.id')

            ->where('upahsupirtangki.id', $id);

        $data = $query->first();
        return $data;
    }

    public function sort($query)
    {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('a.statusaktif_text', '=', $filters['data']);
                        } else if ($filters['field'] == 'jarak') {
                            $query = $query->whereRaw("a.jarak LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'check') {
                            $query = $query->whereRaw('1 = 1');
                        } else if ($filters['field'] == 'nominalsupir' || $filters['field'] == 'nominalkenek' || $filters['field'] == 'nominalkomisi' || $filters['field'] == 'omset') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('a.statusaktif_text', '=', $filters['data']);
                            } else if ($filters['field'] == 'jarak') {
                                $query = $query->orWhereRaw("a.jarak LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'check') {
                                $query = $query->whereRaw('1 = 1');
                            } else if ($filters['field'] == 'nominalsupir' || $filters['field'] == 'nominalkenek' || $filters['field'] == 'nominalkomisi' || $filters['field'] == 'omset') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
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

        $sp = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.upahsupirtangki_id'
            )
            ->where('a.upahsupirtangki_id', '=', $id)
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
    private function deleteFiles(UpahSupirTangki $upahsupir)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoUpahSupir = [];
        $photoUpahSupir = json_decode($upahsupir->gambar, true);
        if ($photoUpahSupir) {
            foreach ($photoUpahSupir as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoUpahSupir[] = "upahsupirtangki/$sizeType$path";
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

    public function processStore(array $data): UpahSupirTangki
    {
        try {
            $upahsupir = new UpahSupirTangki();
            $upahsupir->parent_id = $data['parent_id'] ?? 0;
            $upahsupir->tariftangki_id = $data['tariftangki_id'] ?? 0;
            $upahsupir->kotadari_id = $data['kotadari_id'] ?? 0;
            $upahsupir->kotasampai_id = $data['kotasampai_id'] ?? 0;
            $upahsupir->penyesuaian = $data['penyesuaian'];
            $upahsupir->jarak = $data['jarak'];
            $upahsupir->statusaktif = $data['statusaktif'];
            $upahsupir->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
            $upahsupir->keterangan = $data['keterangan'] ?? '';
            $upahsupir->modifiedby = auth('api')->user()->user;
            $upahsupir->info = html_entity_decode(request()->info);
            $this->deleteFiles($upahsupir);
            if (array_key_exists('gambar', $data)) {
                if ($data['from'] != '') {
                    $upahsupir->gambar = $this->storeFilesBase64($data['gambar'], 'upahsupirtangki');
                } else {
                    $upahsupir->gambar = $this->storeFiles($data['gambar'], 'upahsupirtangki');
                }
            } else {
                $upahsupir->gambar = '';
            }

            if (!$upahsupir->save()) {
                throw new \Exception("Error storing upah supir tangki.");
            }

            $storedLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($upahsupir->getTable()),
                'postingdari' => 'ENTRY UPAH SUPIR TANGKI',
                'idtrans' => $upahsupir->id,
                'nobuktitrans' => $upahsupir->id,
                'aksi' => 'ENTRY',
                'datajson' => $upahsupir->toArray(),
                'modifiedby' => $upahsupir->modifiedby
            ]);

            $detaillog = [];
            for ($i = 0; $i < count($data['nominalsupir']); $i++) {
                $upahsupirDetail = (new UpahSupirTangkiRincian())->processStore($upahsupir, [
                    'upahsupirtangki_id' => $upahsupir->id,
                    'triptangki_id' => $data['triptangki_id'][$i],
                    'nominalsupir' => $data['nominalsupir'][$i],
                ]);

                $detaillog[] = $upahsupirDetail->toArray();
            }
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($upahsupirDetail->getTable()),
                'postingdari' => 'ENTRY UPAH SUPIR TANGKI RINCIAN',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $upahsupir->id,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->user,
            ]);

            return $upahsupir;
        } catch (\Throwable $th) {
            $this->deleteFiles($upahsupir);
            throw $th;
        }
    }

    public function processUpdate(UpahSupirTangki $upahsupir, array $data): UpahSupirTangki
    {
        try {
            $upahsupir->parent_id = $data['parent_id'] ?? 0;
            $upahsupir->tariftangki_id = $data['tariftangki_id'] ?? 0;
            $upahsupir->kotadari_id = $data['kotadari_id'] ?? 0;
            $upahsupir->kotasampai_id = $data['kotasampai_id'] ?? 0;
            $upahsupir->penyesuaian = $data['penyesuaian'];
            $upahsupir->jarak = $data['jarak'];
            $upahsupir->statusaktif = $data['statusaktif'];
            $upahsupir->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
            $upahsupir->keterangan = $data['keterangan'];
            $upahsupir->modifiedby = auth('api')->user()->user;
            $upahsupir->info = html_entity_decode(request()->info);

            $this->deleteFiles($upahsupir);
            if (array_key_exists('gambar', $data)) {
                $upahsupir->gambar = $this->storeFiles($data['gambar'], 'upahsupirtangki');
            } else {
                $upahsupir->gambar = '';
            }
            if (!$upahsupir->save()) {
                throw new \Exception("Error updating upah supir tangki.");
            }

            $storedLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($upahsupir->getTable()),
                'postingdari' => 'EDIT UPAH SUPIR TANGKI',
                'idtrans' => $upahsupir->id,
                'nobuktitrans' => $upahsupir->id,
                'aksi' => 'EDIT',
                'datajson' => $upahsupir->toArray(),
                'modifiedby' => $upahsupir->modifiedby
            ]);

            UpahSupirTangkiRincian::where('upahsupirtangki_id', $upahsupir->id)->delete();
            /* Store detail */
            $detaillog = [];
            for ($i = 0; $i < count($data['nominalsupir']); $i++) {
                $upahsupirDetail = (new UpahSupirTangkiRincian())->processStore($upahsupir, [
                    'upahsupirtangki_id' => $upahsupir->id,
                    'triptangki_id' => $data['triptangki_id'][$i],
                    'nominalsupir' => $data['nominalsupir'][$i],
                ]);
                $detaillog[] = $upahsupirDetail->toArray();
            }
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($upahsupirDetail->getTable()),
                'postingdari' => 'EDIT UPAH SUPIR TANGKI RINCIAN',
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


    public function processDestroy($id): UpahSupirTangki
    {
        $getDetail = UpahSupirTangkiRincian::lockForUpdate()->where('upahsupirtangki_id', $id)->get();

        $upahSupir = new UpahSupirTangki();
        $upahSupir = $upahSupir->lockAndDestroy($id);

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($upahSupir->getTable()),
            'postingdari' => 'DELETE UPAH SUPIR TANGKI',
            'idtrans' => $upahSupir->id,
            'nobuktitrans' => $upahSupir->id,
            'aksi' => 'DELETE',
            'datajson' => $upahSupir->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        $logTrailUpahSupirRincian = (new LogTrail())->processStore([
            'namatabel' => 'UPAHSUPIRRINCIAN',
            'postingdari' => 'DELETE UPAH SUPIR TANGKI RINCIAN',
            'idtrans' => $storedLogTrail['id'],
            'nobuktitrans' => $upahSupir->id,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $upahSupir;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $UpahSupir = UpahSupirTangki::find($data['Id'][$i]);

            $UpahSupir->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            // dd($UpahSupir);
            if ($UpahSupir->save()) {

                (new LogTrail())->processStore([

                    'namatabel' => strtoupper($UpahSupir->getTable()),
                    'postingdari' => 'APPROVAL UPAH SUPIR TANGKI',
                    'idtrans' => $UpahSupir->id,
                    'nobuktitrans' => $UpahSupir->id,
                    'aksi' => $aksi,
                    'datajson' => $UpahSupir->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $UpahSupir;
    }


    public function processUpdateTarif(array $data)
    {
        // dd($upahsupir);
        // dd($data['id']);
        $upahsupir = UpahSupirTangki::find($data['id']);
        try {
            $upahsupir->tariftangki_id = $data['tariftangki_id'] ?? 0;
            $upahsupir->modifiedby = auth('api')->user()->user;
            $upahsupir->info = html_entity_decode(request()->info);

            if (!$upahsupir->save()) {
                throw new \Exception("Error updating upah supir tangki.");
            }

            $storedLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($upahsupir->getTable()),
                'postingdari' => 'EDIT TARIF TANGKI',
                'idtrans' => $upahsupir->id,
                'nobuktitrans' => $upahsupir->id,
                'aksi' => 'EDIT',
                'datajson' => $upahsupir->toArray(),
                'modifiedby' => $upahsupir->modifiedby
            ]);

            return $upahsupir;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getRincian($idtrip, $upah_id, $tarif_id, $triptangki_id)
    {
        $query = DB::table("upahsupirtangkirincian")->from(DB::raw("upahsupirtangkirincian with (readuncommitted)"))
        ->where('upahsupirtangki_id', $upah_id)
        ->where('triptangki_id', $triptangki_id)
        ->first();
        $getTarif = DB::table("tariftangki")->from(DB::raw("tariftangki with (readuncommitted)"))->where('id', $tarif_id)->first();
        $data = [
            'nominalkenek' => 0,
            'nominalkomisi' => 0,
            'nominalsupir' => $query->nominalsupir,
            'nominaltarif' => $getTarif->nominal,
        ];

        return $data;
    }
}
