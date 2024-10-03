<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Helpers\App;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

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

    public $detailTasId;

    public function kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }

    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }


    public function get($model = 0)
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
                $table->longText('tarif')->nullable();
                $table->longText('kotadari_id')->nullable();
                $table->longText('kotasampai_id')->nullable();
                $table->longText('kotasampaiid')->nullable();
                $table->longText('zonadari_id')->nullable();
                $table->longText('zonasampai_id')->nullable();
                $table->longText('penyesuaian')->nullable();
                $table->longText('jarak')->nullable();
                $table->longText('jarakfullempty')->nullable();
                $table->longText('zona_id')->nullable()->nullable();
                $table->longText('statusaktif')->nullable();
                $table->longText('statusaktif_text')->nullable();
                $table->bigInteger('statusaktif_id')->nullable();
                $table->longText('statusupahzona')->nullable();
                $table->longText('statusupahzona_text')->nullable();
                $table->bigInteger('statusupahzona_id')->nullable();
                $table->longText('statuspostingtnl')->nullable();
                $table->longText('statuspostingtnl_text')->nullable();
                $table->bigInteger('statuspostingtnl_id')->nullable();
                $table->date('tglmulaiberlaku')->nullable();
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
                    db::raw("tarif.tujuan + (case when isnull(tarif.penyesuaian,'')='' then '' else ' ('+tarif.penyesuaian+')' end) as tarif"),
                    'kotadari.kodekota as kotadari_id',
                    'kotasampai.kodekota as kotasampai_id',
                    'kotasampai.id as kotasampaiid',
                    'zonadari.zona as zonadari_id',
                    'zonasampai.zona as zonasampai_id',
                    'upahsupir.penyesuaian',
                    DB::raw("CONCAT(upahsupir.jarak, ' KM') as jarak"),
                    DB::raw("CONCAT(upahsupir.jarakfullempty, ' KM') as jarakfullempty"),
                    'zona.keterangan as zona_id',
                    'parameter.memo as statusaktif',
                    'parameter.text as statusaktif_text',
                    'upahsupir.statusaktif as statusaktif_id',

                    'statusupahzona.memo as statusupahzona',
                    'statusupahzona.text as statusupahzona_text',
                    'upahsupir.statusupahzona as statusupahzona_id',

                    'statuspostingtnl.memo as statuspostingtnl',
                    'statuspostingtnl.text as statuspostingtnl_text',
                    'upahsupir.statuspostingtnl as statuspostingtnl_id',
                    'upahsupir.tglmulaiberlaku',
                    // 'upahsupir.tglakhirberlaku',
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
                ->leftJoin(DB::raw("zona as zonadari with (readuncommitted)"), 'zonadari.id', '=', 'upahsupir.zonadari_id')
                ->leftJoin(DB::raw("zona as zonasampai with (readuncommitted)"), 'zonasampai.id', '=', 'upahsupir.zonasampai_id')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'upahsupir.statusaktif', 'parameter.id')
                ->leftJoin(DB::raw("parameter as statusupahzona with (readuncommitted)"), 'upahsupir.statusupahzona', 'statusupahzona.id')
                ->leftJoin(DB::raw("parameter as statuspostingtnl with (readuncommitted)"), 'upahsupir.statuspostingtnl', 'statuspostingtnl.id')
                ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahsupir.zona_id', 'zona.id');

            DB::table($temtabel)->insertUsing([
                'id',
                'parent_id',
                'tarif',
                'kotadari_id',
                'kotasampai_id',
                'kotasampaiid',
                'zonadari_id',
                'zonasampai_id',
                'penyesuaian',
                'jarak',
                'jarakfullempty',
                'zona_id',
                'statusaktif',
                'statusaktif_text',
                'statusaktif_id',
                'statusupahzona',
                'statusupahzona_text',
                'statusupahzona_id',
                'statuspostingtnl',
                'statuspostingtnl_text',
                'statuspostingtnl_id',
                'tglmulaiberlaku',
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
                'a.tarif',
                'a.kotadari_id',
                'a.kotasampai_id',
                'a.kotasampaiid',
                'a.zonadari_id',
                'a.zonasampai_id',
                'a.penyesuaian',
                'a.jarak',
                'a.jarakfullempty',
                'a.zona_id',
                'a.statusaktif',
                'a.statusupahzona',
                'a.statuspostingtnl',
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
            $query->where('a.penyesuaian', '');
        } else {
            $query->whereRaw("1=1");
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);
        if ($model == 1) {
            $data = $query;
        } else {
            $data = $query->get();
        }
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
            DB::raw("(trim(tarif.tujuan)+ (CASE WHEN isnull(tarif.penyesuaian,'')='' then '' ELSE ' - '+trim(tarif.penyesuaian) end))  as tarif"),
            DB::raw("(case when upahsupir.kotadari_id=0 then null else upahsupir.kotadari_id end) as kotadari_id"),
            DB::raw("TRIM(kotadari.keterangan) as kotadari"),
            'upahsupir.keterangan',
            'upahsupir.penyesuaian',
            DB::raw("(case when upahsupir.kotasampai_id=0 then null else upahsupir.kotasampai_id end) as kotasampai_id"),
            DB::raw("TRIM(kotasampai.keterangan) as kotasampai"),

            DB::raw("(case when upahsupir.zonadari_id=0 then null else upahsupir.zonadari_id end) as zonadari_id"),
            DB::raw("TRIM(zonadari.zona) as zonadari"),
            DB::raw("(case when upahsupir.zonasampai_id=0 then null else upahsupir.zonasampai_id end) as zonasampai_id"),
            DB::raw("TRIM(zonasampai.zona) as zonasampai"),
            'upahsupir.jarak',
            'upahsupir.jarakfullempty',
            'zona.keterangan as zona',
            DB::raw("(case when upahsupir.zona_id=0 then null else upahsupir.zona_id end) as zona_id"),
            'upahsupir.statusaktif',
            'statusaktif.text as statusaktifnama',
            'upahsupir.statusupahzona',
            'upahsupir.statuspostingtnl',
            'upahsupir.statussimpankandang',

            'upahsupir.tglmulaiberlaku',
            // 'upahsupir.tglakhirberlaku',
            'upahsupir.statusluarkota',
            'statusluarkota.text as statusluarkotas',
            'upahsupir.statuslangsir',
            'statuslangsir.text as statuslangsirnama',
            'upahsupir.gambar',

            DB::raw("upahsupir.tarifmuatan_id"),
            DB::raw("(trim(tarifmuatan.tujuan)+' - '+trim(tarifmuatan.penyesuaian)) as tarifmuatan"),
            DB::raw("upahsupir.tarifbongkaran_id"),
            DB::raw("(trim(tarifbongkaran.tujuan)+' - '+trim(tarifbongkaran.penyesuaian)) as tarifbongkaran"),
            DB::raw("upahsupir.tarifexport_id"),
            DB::raw("(trim(tarifexport.tujuan)+' - '+trim(tarifexport.penyesuaian)) as tarifexport"),
            DB::raw("upahsupir.tarifimport_id"),
            DB::raw("(trim(tarifimport.tujuan)+' - '+trim(tarifimport.penyesuaian)) as tarifimport"),
            'upahsupir.modifiedby',
            'upahsupir.updated_at'
        )
            ->leftJoin(DB::raw("$temp as parent with (readuncommitted)"), 'parent.id', '=', 'upahsupir.parent_id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
            ->leftJoin(DB::raw("zona as zonadari with (readuncommitted)"), 'zonadari.id', '=', 'upahsupir.zonadari_id')
            ->leftJoin(DB::raw("zona as zonasampai with (readuncommitted)"), 'zonasampai.id', '=', 'upahsupir.zonasampai_id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahsupir.zona_id', 'zona.id')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("tarif as tarifmuatan with (readuncommitted)"), 'upahsupir.tarifmuatan_id', 'tarifmuatan.id')
            ->leftJoin(DB::raw("tarif as tarifbongkaran with (readuncommitted)"), 'upahsupir.tarifbongkaran_id', 'tarifbongkaran.id')
            ->leftJoin(DB::raw("tarif as tarifexport with (readuncommitted)"), 'upahsupir.tarifexport_id', 'tarifexport.id')
            ->leftJoin(DB::raw("tarif as tarifimport with (readuncommitted)"), 'upahsupir.tarifimport_id', 'tarifimport.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'upahsupir.statusluarkota', 'statusluarkota.id')
            ->leftJoin(DB::raw("parameter as statuslangsir with (readuncommitted)"), 'upahsupir.statuslangsir', 'statuslangsir.id')
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'upahsupir.statusaktif', 'statusaktif.id')

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
            $table->string('statusaktifnama', 300)->nullable();
            $table->unsignedBigInteger('statusluarkota')->nullable();
            $table->unsignedBigInteger('statussimpankandang')->nullable();
            $table->unsignedBigInteger('statusupahzona')->nullable();
            $table->unsignedBigInteger('statuspostingtnl')->nullable();
            $table->unsignedBigInteger('statuslangsir')->nullable();
            $table->string('statuslangsirnama', 300)->nullable();
        });

        $statusAktif = Parameter::from(
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

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS UPAH ZONA')
            ->where('subgrp', '=', 'STATUS UPAH ZONA')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusUpahZona = $status->id ?? 0;
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS POSTING TNL')
            ->where('subgrp', '=', 'STATUS POSTING TNL')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusPostingTnl = $status->id ?? 0;

        $statuslangsir = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS LANGSIR')
            ->where('subgrp', '=', 'STATUS LANGSIR')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $statusAktif->id ?? 0,
                "statusaktifnama" => $statusAktif->text ?? "",
                "statusluarkota" => $iddefaultstatusluarkota,
                "statussimpankandang" => $iddefaultstatusSimpanKandang,
                "statusupahzona" => $iddefaultstatusUpahZona,
                "statuspostingtnl" => $iddefaultstatusPostingTnl,
                "statuslangsir" => $statuslangsir->id ?? 0,
                "statuslangsirnama" => $statuslangsir->text ?? ''
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama',
                'statusluarkota',
                'statussimpankandang',
                'statusupahzona',
                'statuspostingtnl',
                'statuslangsir',
                'statuslangsirnama',
            );

        $data = $query->first();

        return $data;
    }

    // public function selectColumns($query)
    // {
    //     return $query->select(
    //         'a.id',
    //         'a.parent_id',
    //         'a.tarif',
    //         'a.kotadari_id',
    //         'a.kotasampai_id',
    //         'a.penyesuaian',
    //         'a.jarak',
    //         'a.zona_id',
    //         'a.statusaktif',
    //         'a.tglmulaiberlaku',
    //         'a.statusluarkota',
    //         'a.keterangan',
    //         'a.created_at',
    //         'a.modifiedby',
    //         'a.updated_at',
    //     );
    // }

    // public function createTemp(string $modelTable)
    // {

    //     $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

    //     Schema::create($temp, function (Blueprint $table) {
    //         $table->bigInteger('id')->nullable();
    //         $table->longText('parent_id')->nullable();
    //         $table->longText('tarif')->nullable();
    //         $table->longText('kotadari_id')->nullable();
    //         $table->longText('kotasampai_id')->nullable();
    //         $table->longText('penyesuaian')->nullable();
    //         $table->longText('jarak')->nullable();
    //         $table->longText('zona_id')->nullable()->nullable();
    //         $table->longText('statusaktif')->nullable();
    //         $table->date('tglmulaiberlaku')->nullable();
    //         $table->longText('statusluarkota')->nullable();
    //         $table->longText('keterangan')->nullable();
    //         $table->dateTime('created_at')->nullable();
    //         $table->longText('modifiedby')->nullable();
    //         $table->dateTime('updated_at')->nullable();
    //         $table->increments('position');
    //     });

    //     $this->setRequestParameters();
    //     $user = auth('api')->user()->name;
    //     $class = 'UpahSupirController';
    //     $querydata = DB::table('listtemporarytabel')->from(
    //         DB::raw("listtemporarytabel with (readuncommitted)")
    //     )
    //         ->select(
    //             'namatabel',
    //         )
    //         ->where('class', '=', $class)
    //         ->where('modifiedby', '=', $user)
    //         ->first();

    //     $temtabel = $querydata->namatabel;
    //     $query = DB::table($temtabel)->from(DB::raw("$temtabel as a with (readuncommitted)"));
    //     $query = $this->selectColumns($query);
    //     $this->sort($query);
    //     $models = $this->filter($query);
    //     DB::table($temp)->insertUsing([
    //         'id',
    //         'parent_id',
    //         'tarif',
    //         'kotadari_id',
    //         'kotasampai_id',
    //         'penyesuaian',
    //         'jarak',
    //         'zona_id',
    //         'statusaktif',
    //         'tglmulaiberlaku',
    //         'statusluarkota',
    //         'keterangan',
    //         'created_at',
    //         'modifiedby',
    //         'updated_at',
    //     ], $models);

    //     return $temp;
    // }

    public function selectColumns()
    {
        $temtabel = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temtabel, function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->longText('parent_id')->nullable();
            $table->longText('tarif')->nullable();
            $table->longText('kotadari_id')->nullable();
            $table->longText('kotasampai_id')->nullable();
            $table->longText('zonadari_id')->nullable();
            $table->longText('zonasampai_id')->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->longText('jarak')->nullable();
            $table->longText('jarakfullempty')->nullable();
            $table->longText('zona_id')->nullable()->nullable();
            $table->longText('statusaktif')->nullable();
            $table->longText('statusaktif_text')->nullable();
            $table->bigInteger('statusaktif_id')->nullable();
            $table->longText('statusupahzona')->nullable();
            $table->longText('statusupahzona_text')->nullable();
            $table->bigInteger('statusupahzona_id')->nullable();
            $table->longText('statuspostingtnl')->nullable();
            $table->longText('statuspostingtnl_text')->nullable();
            $table->bigInteger('statuspostingtnl_id')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('keterangan')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('updated_at')->nullable();
        });


        $tempParent = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'upahsupir.id',
                'upahsupir.parent_id',
                'kota.keterangan'
            )
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'kota.id', 'upahsupir.kotasampai_id');

        $temp = '##tempParent' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
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
                'zonadari.zona as zonadari_id',
                'zonasampai.zona as zonasampai_id',
                'upahsupir.penyesuaian',
                DB::raw("CONCAT(upahsupir.jarak, ' KM') as jarak"),
                DB::raw("CONCAT(upahsupir.jarakfullempty, ' KM') as jarakfullempty"),
                'zona.keterangan as zona_id',
                'parameter.memo as statusaktif',
                'parameter.text as statusaktif_text',
                'upahsupir.statusaktif as statusaktif_id',

                'statusupahzona.memo as statusupahzona',
                'statusupahzona.text as statusupahzona_text',
                'upahsupir.statusupahzona as statusupahzona_id',

                'statuspostingtnl.memo as statuspostingtnl',
                'statuspostingtnl.text as statuspostingtnl_text',
                'upahsupir.statuspostingtnl as statuspostingtnl_id',
                'upahsupir.tglmulaiberlaku',
                // 'upahsupir.tglakhirberlaku',
                'upahsupir.gambar',
                'upahsupir.keterangan',
                'upahsupir.created_at',
                'upahsupir.modifiedby',
                'upahsupir.updated_at',
            )
            ->leftJoin(DB::raw("$temp as parent with (readuncommitted)"), 'parent.id', '=', 'upahsupir.parent_id')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
            ->leftJoin(DB::raw("zona as zonadari with (readuncommitted)"), 'zonadari.id', '=', 'upahsupir.zonadari_id')
            ->leftJoin(DB::raw("zona as zonasampai with (readuncommitted)"), 'zonasampai.id', '=', 'upahsupir.zonasampai_id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'upahsupir.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusupahzona with (readuncommitted)"), 'upahsupir.statusupahzona', 'statusupahzona.id')
            ->leftJoin(DB::raw("parameter as statuspostingtnl with (readuncommitted)"), 'upahsupir.statuspostingtnl', 'statuspostingtnl.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahsupir.zona_id', 'zona.id');

        DB::table($temtabel)->insertUsing([
            'id',
            'parent_id',
            'tarif',
            'kotadari_id',
            'kotasampai_id',
            'zonadari_id',
            'zonasampai_id',
            'penyesuaian',
            'jarak',
            'jarakfullempty',
            'zona_id',
            'statusaktif',
            'statusaktif_text',
            'statusaktif_id',
            'statusupahzona',
            'statusupahzona_text',
            'statusupahzona_id',
            'statuspostingtnl',
            'statuspostingtnl_text',
            'statuspostingtnl_id',
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
                'a.tarif',
                'a.kotadari_id',
                'a.kotasampai_id',
                'a.zonadari_id',
                'a.zonasampai_id',
                'a.penyesuaian',
                'a.jarak',
                'a.jarakfullempty',
                'a.zona_id',
                'a.statusaktif',
                'a.statusaktif_text',
                'a.statusaktif_id',
                'a.statusupahzona',
                'a.statusupahzona_text',
                'a.statusupahzona_id',
                'a.statuspostingtnl',
                'a.statuspostingtnl_text',
                'a.statuspostingtnl_id',
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
            $table->longText('tarif')->nullable();
            $table->longText('kotadari_id')->nullable();
            $table->longText('kotasampai_id')->nullable();
            $table->longText('zonadari_id')->nullable();
            $table->longText('zonasampai_id')->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->longText('jarak')->nullable();
            $table->longText('jarakfullempty')->nullable();
            $table->longText('zona_id')->nullable()->nullable();
            $table->longText('statusaktif')->nullable();
            $table->longText('statusaktif_text')->nullable();
            $table->bigInteger('statusaktif_id')->nullable();
            $table->longText('statusupahzona')->nullable();
            $table->longText('statusupahzona_text')->nullable();
            $table->bigInteger('statusupahzona_id')->nullable();
            $table->longText('statuspostingtnl')->nullable();
            $table->longText('statuspostingtnl_text')->nullable();
            $table->bigInteger('statuspostingtnl_id')->nullable();
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
            'tarif',
            'kotadari_id',
            'kotasampai_id',
            'zonadari_id',
            'zonasampai_id',
            'penyesuaian',
            'jarak',
            'jarakfullempty',
            'zona_id',
            'statusaktif',
            'statusaktif_text',
            'statusaktif_id',
            'statusupahzona',
            'statusupahzona_text',
            'statusupahzona_id',
            'statuspostingtnl',
            'statuspostingtnl_text',
            'statuspostingtnl_id',
            'tglmulaiberlaku',
            'gambar',
            'keterangan',
            'created_at',
            'modifiedby',
            'updated_at',
        ], $models);
        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'kotadari_id') {
            return $query->orderBy('a.kotadari_id', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kotasampai_id') {
            return $query->orderBy('a.kotasampai_id', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'zonadari_id') {
            return $query->orderBy('a.zonadari_id', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'zonasampai_id') {
            return $query->orderBy('a.zonasampai_id', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'zona_id') {
            return $query->orderBy('a.zona_id', $this->params['sortOrder']);
        } else {
            return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function sortForPosition($query)
    {
        if ($this->params['sortIndex'] == 'kotadari_id') {
            return $query->orderBy('kotadari.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kotasampai_id') {
            return $query->orderBy('kotasampai.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'zonadari_id') {
            return $query->orderBy('zonadari.zona', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'zonasampai_id') {
            return $query->orderBy('zonasampai.zona', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'zona_id') {
            return $query->orderBy('zona.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tarif') {
            return $query->orderBy('tarif.tujuan', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('a.statusaktif_text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusupahzona') {
                            $query = $query->where('a.statusupahzona_text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuspostingtnl') {
                            $query = $query->where('a.statuspostingtnl_text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'parent_id') {
                            $query = $query->where('a.parent_id', '=', $filters['data']);
                        } else if ($filters['field'] == 'kotadari_id') {
                            $query = $query->where('a.kotadari_id', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kotasampai_id') {
                            $query = $query->where('a.kotasampai_id', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'zonadari_id') {
                            $query = $query->where('a.zonadari_id', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'zonasampai_id') {
                            $query = $query->where('a.zonasampai_id', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->where('a.zona_id', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'jarak') {
                            $query = $query->whereRaw("a.jarak LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'check') {
                            $query = $query->whereRaw('1 = 1');
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
                            } elseif ($filters['field'] == 'statusupahzona') {
                                $query = $query->orWhere('a.statusupahzona', '=', $filters['data']);
                            } elseif ($filters['field'] == 'statuspostingtnl') {
                                $query = $query->orWhere('a.statuspostingtnl', '=', $filters['data']);
                            } else if ($filters['field'] == 'kotadari_id') {
                                $query = $query->orWhere('a.kotadari_id', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'zonadari_id') {
                                $query = $query->orWhere('a.zonadari_id', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'parent_id') {
                                $query = $query->orWhere('a.parent_id', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kotasampai_id') {
                                $query = $query->orWhere('a.kotasampai_id', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'zonasampai_id') {
                                $query = $query->orWhere('a.zonasampai_id', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'zona_id') {
                                $query = $query->orWhere('a.zona_id', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'jarak') {
                                $query = $query->orWhereRaw("a.jarak LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'check') {
                                $query = $query->whereRaw('1 = 1');
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

    public function filterForPosition($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'parent_id') {
                            $query = $query->where('parent.keterangan', '=', $filters['data']);
                        } elseif ($filters['field'] == 'statusupahzona') {
                            $query = $query->where('statusupahzona.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'kotadari_id') {
                            $query = $query->where('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kotasampai_id') {
                            $query = $query->where('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'zonadari_id') {
                            $query = $query->where('zonadari.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'zonasampai_id') {
                            $query = $query->where('zonasampai.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tarif') {
                            $query = $query->where('tarif.tujuan', 'LIKE', "%$filters[data]%");
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

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter.text', '=', $filters['data']);
                            } elseif ($filters['field'] == 'statusupahzona') {
                                $query = $query->orWhere('statusupahzona.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'kotadari_id') {
                                $query = $query->orWhere('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'zonadari_id') {
                                $query = $query->orWhere('zonadari.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'parent_id') {
                                $query = $query->orWhere('parent.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kotasampai_id') {
                                $query = $query->orWhere('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'zonasampai_id') {
                                $query = $query->orWhere('zonasampai.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'zona_id') {
                                $query = $query->orWhere('zona.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tarif') {
                                $query = $query->orWhere('tarif.tujuan', 'LIKE', "%$filters[data]%");
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

    private function deleteFiles(UpahSupir $upahsupir, $from = null)
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
            if ($from) {
                Storage::disk('toTnl')->delete($relatedPhotoUpahSupir);
            }
        }
    }

    private function storeFilesBase64(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            // $originalFileName = hash('sha256', $file);

            $randomValue = substr($originalFileName, rand(0, strlen($originalFileName) - 10), 10) . '.jpg';
            $imageData = base64_decode($file);

            $storedFile = Storage::disk('toTnl')->putFileAs($destinationFolder, $file, $originalFileName);
            $pathDestination = Storage::disk('toTnl')->getDriver()->getAdapter()->applyPathPrefix(null);
            $resizedFiles = App::imageResize($pathDestination . $destinationFolder . '/', $pathDestination . $destinationFolder . '/' . $originalFileName, $originalFileName);

            // $storedFile = Storage::put($destinationFolder . '/' . $randomValue, $imageData);
            // $resizedFiles = App::imageResize(storage_path("app/$destinationFolder/"), storage_path("app/upahsupir/$randomValue"), $randomValue);
            $storedFiles[] = $randomValue;
        }

        return json_encode($storedFiles);
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
    public function processStore(array $data, UpahSupir $upahsupir, $connecTnl = null): UpahSupir
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

            if ($belawan) {
                if ($belawan->id == $data['kotadari_id']) {
                    $data['statussimpankandang'] = $statusSimpanKandang->id;
                }
            }

            /**
             * 
             if ($data['from'] != '') {
                 if ($data['tarif_id'] != 0) {
 
                     $getTarif = DB::table("tarif")->from(DB::raw("tarif with (readuncommitted)"))
                         ->where('kota_id', $data['kotasampai_id'])
                         ->where('penyesuaian', trim(strtoupper($data['penyesuaian'])))
                         ->first();
 
                     if ($getTarif != '') {
                         $data['tarif_id'] = $getTarif->id;
                     }
                 }
                 if ($data['tarifmuatan_id'] != 0) {
 
                     $getTarif = DB::table("tarif")->from(DB::raw("tarif with (readuncommitted)"))
                         ->where('kota_id', $data['kotasampai_id'])
                         ->where('penyesuaian', trim(strtoupper($data['penyesuaian'])))
                         ->where('jenisorder_id', 1)
                         ->first();
 
                     if ($getTarif != '') {
                         $data['tarifmuatan_id'] = $getTarif->id;
                     }
                 }
                 if ($data['tarifbongkaran_id'] != 0) {
 
                     $getTarif = DB::table("tarif")->from(DB::raw("tarif with (readuncommitted)"))
                         ->where('kota_id', $data['kotasampai_id'])
                         ->where('penyesuaian', trim(strtoupper($data['penyesuaian'])))
                         ->where('jenisorder_id', 2)
                         ->first();
 
                     if ($getTarif != '') {
                         $data['tarifbongkaran_id'] = $getTarif->id;
                     }
                 }
                 if ($data['tarifimport_id'] != 0) {
 
                     $getTarif = DB::table("tarif")->from(DB::raw("tarif with (readuncommitted)"))
                         ->where('kota_id', $data['kotasampai_id'])
                         ->where('penyesuaian', trim(strtoupper($data['penyesuaian'])))
                         ->where('jenisorder_id', 3)
                         ->first();
 
                     if ($getTarif != '') {
                         $data['tarifimport_id'] = $getTarif->id;
                     }
                 }
                 if ($data['tarifexport_id'] != 0) {
 
                     $getTarif = DB::table("tarif")->from(DB::raw("tarif with (readuncommitted)"))
                         ->where('kota_id', $data['kotasampai_id'])
                         ->where('penyesuaian', trim(strtoupper($data['penyesuaian'])))
                         ->where('jenisorder_id', 4)
                         ->first();
 
                     if ($getTarif != '') {
                         $data['tarifexport_id'] = $getTarif->id;
                     }
                 }
             }
             *
             */
            // $upahsupir = new UpahSupir();
            $upahsupir->kotadari_id = $data['kotadari_id'] ?? 0;
            $upahsupir->parent_id = $data['parent_id'] ?? 0;
            $upahsupir->tarif_id = $data['tarif_id'] ?? 0;
            $upahsupir->tarifmuatan_id = $data['tarifmuatan_id'] ?? 0;
            $upahsupir->tarifbongkaran_id = $data['tarifbongkaran_id'] ?? 0;
            $upahsupir->tarifimport_id = $data['tarifimport_id'] ?? 0;
            $upahsupir->tarifexport_id = $data['tarifexport_id'] ?? 0;
            $upahsupir->kotasampai_id = $data['kotasampai_id'] ?? 0;
            $upahsupir->penyesuaian = $data['penyesuaian'];
            $upahsupir->jarak = $data['jarak'];
            $upahsupir->jarakfullempty = $data['jarakfullempty'];
            $upahsupir->zona_id = ($data['zona_id'] == null) ? 0 : $data['zona_id'] ?? 0;
            $upahsupir->statusaktif = $data['statusaktif'];
            $upahsupir->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
            $upahsupir->zonadari_id = $data['zonadari_id'] ?? 0;
            $upahsupir->zonasampai_id = $data['zonasampai_id'] ?? 0;
            $upahsupir->statuspostingtnl = $data['statuspostingtnl'];
            $upahsupir->statusupahzona = $data['statusupahzona'];
            $upahsupir->statussimpankandang = $data['statussimpankandang'];
            $upahsupir->statuslangsir = $data['statuslangsir'];
            $upahsupir->statusluarkota = $data['statusluarkota'] ?? '';
            $upahsupir->keterangan = $data['keterangan'] ?? '';
            $upahsupir->modifiedby = auth('api')->user()->user;
            $upahsupir->info = html_entity_decode(request()->info);
            $upahsupir->tas_id = $data['tas_id'];
            $this->deleteFiles($upahsupir, $data['from'] != '');
            if (array_key_exists('gambar', $data)) {
                if ($data['from'] != '') {
                    $upahsupir->gambar = $this->storeFilesBase64($data['gambar'], 'upahsupir');
                } else {
                    $upahsupir->gambar = $this->storeFiles($data['gambar'], 'upahsupir');
                }
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
                $upahsupirDetail = new UpahSupirRincian();
                if ($connecTnl) {
                    $upahsupirDetail->setConnection('srvtnl');
                }

                $datadetail = [
                    'upahsupir_id' => $upahsupir->id,
                    'container_id' => $data['container_id'][$i],
                    'statuscontainer_id' => $data['statuscontainer_id'][$i],
                    'nominalsupir' => $data['nominalsupir'][$i],
                    'nominalkenek' => $data['nominalkenek'][$i] ?? 0,
                    'nominalkomisi' => $data['nominalkomisi'][$i] ?? 0,
                    'nominaltol' =>  $data['nominaltol'][$i] ?? 0,
                    'liter' => $data['liter'][$i] ?? 0,
                    'tas_id' => $data['detail_tas_id'][$i] ?? 0,
                ];


                $upahsupirDetail = $upahsupirDetail->processStore($datadetail, $upahsupirDetail, $connecTnl);
                $detaillog[] = $upahsupirDetail->toArray();
                $upahsupir->detailTasId[] = $upahsupirDetail->id;
            }
            $logtrail = new LogTrail();
            if ($connecTnl) {
                $logtrail->setConnection('srvtnl');
            }
            $logtrail->processStore([
                'namatabel' => strtoupper($upahsupirDetail->getTable()),
                'postingdari' => 'ENTRY UPAH SUPIR RINCIAN',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $upahsupir->id,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->user,
            ]);

            if ($data['statussimpankandang'] == $statusSimpanKandang->id) {
                $upahsupirKandang = DB::table("upahsupir");
                if ($connecTnl) {
                    $upahsupirKandang->connection('srvtnl');
                }
                $getBelawanKandang = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                    ->select('id', 'jarak')
                    ->where('kotadari_id', $belawan->id)
                    ->where('kotasampai_id', $kandang->id)
                    ->first();

                $upahsupirrincianKandang = DB::table("upahsupirrincian");
                if ($connecTnl) {
                    $upahsupirrincianKandang->connection('srvtnl');
                }
                $getRincianBelawanKandang = DB::table("upahsupirrincian")->from(DB::raw("upahsupirrincian with (readuncommitted)"))
                    ->where('upahsupir_id', $getBelawanKandang->id)
                    ->get();
                $jarakKandang = $data['jarak'] - $getBelawanKandang->jarak;
                $jarakKandangFullEmpty = $jarakKandang * 2;

                $upahsupirKandang = new UpahSupir();
                $upahsupirKandang->kotadari_id = $kandang->id;
                $upahsupirKandang->parent_id = $upahsupir->parent_id ?? 0;
                $upahsupirKandang->tarif_id = $upahsupir->tarif_id ?? 0;
                $upahsupirKandang->kotasampai_id = $data['kotasampai_id'];
                $upahsupirKandang->penyesuaian = $data['penyesuaian'];
                $upahsupirKandang->jarak = ($jarakKandang < 0) ? 0 : $jarakKandang;
                $upahsupirKandang->zona_id = ($data['zona_id'] == null) ? 0 : $data['zona_id'] ?? 0;
                $upahsupirKandang->zonadari_id =  ($data['zonadari_id'] == null) ? 0 : $data['zonadari_id'] ?? 0;
                $upahsupirKandang->zonasampai_id =  ($data['zonadari_id'] == null) ? 0 : $data['zonasampai_id'] ?? 0;
                $upahsupirKandang->statusaktif = $data['statusaktif'];
                $upahsupirKandang->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
                $upahsupirKandang->statussimpankandang = $data['statussimpankandang'];
                $upahsupirKandang->statuslangsir = $data['statuslangsir'];
                $upahsupirKandang->statusupahzona = $data['statusupahzona'];
                $upahsupirKandang->statusluarkota = $data['statusluarkota'] ?? '';
                $upahsupirKandang->jarakfullempty = $jarakKandangFullEmpty;
                $upahsupirKandang->tarifmuatan_id = $data['tarifmuatan_id'] ?? 0;
                $upahsupirKandang->tarifbongkaran_id = $data['tarifbongkaran_id'] ?? 0;
                $upahsupirKandang->tarifimport_id = $data['tarifimport_id'] ?? 0;
                $upahsupirKandang->tarifexport_id = $data['tarifexport_id'] ?? 0;
                $upahsupirKandang->keterangan = $data['keterangan'];
                $upahsupirKandang->modifiedby = auth('api')->user()->user;
                $upahsupirKandang->info = html_entity_decode(request()->info);
                $this->deleteFiles($upahsupirKandang, $data['from'] != '');
                if (array_key_exists('gambar', $data)) {
                    // $upahsupirKandang->gambar = $this->storeFiles($data['gambar'], 'upahsupir');
                    if ($data['from'] != '') {
                        $upahsupirKandang->gambar = $this->storeFilesBase64($data['gambar'], 'upahsupir');
                    } else {
                        $upahsupirKandang->gambar = $this->storeFiles($data['gambar'], 'upahsupir');
                    }
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

                    $upahsupirDetail = new UpahSupirRincian();
                    if ($connecTnl) {
                        $upahsupirDetail->setConnection('srvtnl');
                    }
                    $datadetail = [
                        'upahsupir_id' => $upahsupirKandang->id,
                        'container_id' => $data['container_id'][$i],
                        'statuscontainer_id' => $data['statuscontainer_id'][$i],
                        'nominalsupir' => ($nomSupir < 0) ? 0 : $nomSupir,
                        'nominalkenek' => ($nomKenek < 0) ? 0 : $nomKenek,
                        'nominalkomisi' => ($nomKomisi < 0) ? 0 : $nomKomisi,
                        'nominaltol' => ($nomTol < 0) ? 0 : $nomTol,
                        'liter' => ($liter < 0) ? 0 : $liter,
                        'tas_id' => $getRincianBelawanKandang[$i]->tas_id ?? 0,
                    ];


                    $upahsupirDetail = $upahsupirDetail->processStore($datadetail, $upahsupirDetail, $connecTnl);

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
            // $statusTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'POSTING TNL')->first();
            // if ($data['statuspostingtnl'] == $statusTnl->id) {
            //     $statusBukanTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'TIDAK POSTING TNL')->first();
            //     // posting ke tnl
            //     $data['statuspostingtnl'] = $statusBukanTnl->id;

            //     $postingTNL = $this->postingTnl($data, $upahsupir->gambar);
            //     if ($postingTNL['statuscode'] != 201) {
            //         if ($postingTNL['statuscode'] == 422) {
            //             throw new \Exception($postingTNL['data']['errors']['penyesuaian'][0] . ' di TNL');
            //         } else {
            //             throw new \Exception($postingTNL['data']['message']);
            //         }
            //     }
            // }

            return $upahsupir;
        } catch (\Throwable $th) {
            $this->deleteFiles($upahsupir, array_key_exists('from', $data));
            throw $th;
        }
    }

    public function processUpdate(UpahSupir $upahsupir, array $data, $connecTnl = null): UpahSupir
    {
        try {
            $upahsupir->kotadari_id = $data['kotadari_id'] ?? 0;
            $upahsupir->parent_id = $data['parent_id'] ?? 0;
            $upahsupir->tarif_id = $data['tarif_id'] ?? 0;
            $upahsupir->tarifmuatan_id = $data['tarifmuatan_id'] ?? 0;
            $upahsupir->tarifbongkaran_id = $data['tarifbongkaran_id'] ?? 0;
            $upahsupir->tarifimport_id = $data['tarifimport_id'] ?? 0;
            $upahsupir->tarifexport_id = $data['tarifexport_id'] ?? 0;
            $upahsupir->kotasampai_id = $data['kotasampai_id'] ?? 0;
            $upahsupir->penyesuaian = $data['penyesuaian'];
            $upahsupir->zonadari_id = $data['zonadari_id'] ?? 0;
            $upahsupir->zonasampai_id = $data['zonasampai_id'] ?? 0;
            $upahsupir->statusupahzona = $data['statusupahzona'];
            $upahsupir->statuslangsir = $data['statuslangsir'];
            $upahsupir->jarak = $data['jarak'];
            $upahsupir->jarakfullempty = $data['jarakfullempty'];
            $upahsupir->zona_id = ($data['zona_id'] == null) ? 0 : $data['zona_id'] ?? 0;
            $upahsupir->statusaktif = $data['statusaktif'];
            // $upahsupir->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
            $upahsupir->keterangan = $data['keterangan'];
            $upahsupir->modifiedby = auth('api')->user()->user;
            $upahsupir->info = html_entity_decode(request()->info);

            $this->deleteFiles($upahsupir, array_key_exists('from', $data));
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
            $upahsupirDetail = new UpahSupirRincian();
            if ($connecTnl) {
                $upahsupirDetail->setConnection('srvtnl');
            }
            $upahsupirDetail->where('upahsupir_id', $upahsupir->id)->delete();
            /* Store detail */
            $detaillog = [];
            for ($i = 0; $i < count($data['nominalsupir']); $i++) {
                $upahsupirDetail = new UpahSupirRincian();
                if ($connecTnl) {
                    $upahsupirDetail->setConnection('srvtnl');
                }
                $datadetail = [
                    'upahsupir_id' => $upahsupir->id,
                    'container_id' => $data['container_id'][$i],
                    'statuscontainer_id' => $data['statuscontainer_id'][$i],
                    'nominalsupir' => $data['nominalsupir'][$i],
                    'nominalkenek' => $data['nominalkenek'][$i] ?? 0,
                    'nominalkomisi' => $data['nominalkomisi'][$i] ?? 0,
                    'nominaltol' =>  $data['nominaltol'][$i] ?? 0,
                    'liter' => $data['liter'][$i] ?? 0,
                    'tas_id' => $data['detail_tas_id'][$i] ?? 0,
                ];


                $upahsupirDetail = $upahsupirDetail->processStore($datadetail, $upahsupirDetail);
                $detaillog[] = $upahsupirDetail->toArray();
                $upahsupir->detailTasId[] = $upahsupirDetail->id;
            }
            $logtrail = new LogTrail();
            if ($connecTnl) {
                $logtrail->setConnection('srvtnl');
            }
            $logtrail->processStore([
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
            $this->deleteFiles($upahsupir, array_key_exists('from', $data));
            throw $th;
        }
    }

    public function processDestroy(UpahSupir $upahSupir): UpahSupir
    {
        // $getDetail = UpahSupirRincian::lockForUpdate()->where('upahsupir_id', $id)->get();

        // $upahSupir = new UpahSupir();
        $upahSupir = $upahSupir->lockAndDestroy($upahSupir->id);

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($upahSupir->getTable()),
            'postingdari' => 'DELETE UPAH SUPIR',
            'idtrans' => $upahSupir->id,
            'nobuktitrans' => $upahSupir->id,
            'aksi' => 'DELETE',
            'datajson' => $upahSupir->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        // $logTrailUpahSupirRincian = (new LogTrail())->processStore([
        //     'namatabel' => 'UPAHSUPIRRINCIAN',
        //     'postingdari' => 'DELETE UPAH SUPIR RINCIAN',
        //     'idtrans' => $storedLogTrail['id'],
        //     'nobuktitrans' => $upahSupir->id,
        //     'aksi' => 'DELETE',
        //     'datajson' => $getDetail->toArray(),
        //     'modifiedby' => auth('api')->user()->name
        // ]);

        return $upahSupir;
    }


    public function postingTnl($data, $gambar)
    {
        $gambar = json_decode($gambar);
        // dd(storage_path("app/upahsupir/".$gambar[0]));
        // $data['gambar'] = $data['gambartnl'];
        // dd($data['gambar']);
        $server = config('app.server_jkt');
        // $getToken = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        //     'Accept' => 'application/json'
        // ])
        //     ->post($server . 'truckingtnl-api/public/api/token', [
        //         'user' => 'ADMIN',
        //         'password' => config('app.password_tnl'),
        //         'ipclient' => '',
        //         'ipserver' => '',
        //         'latitude' => '',
        //         'longitude' => '',
        //         'browser' => '',
        //         'os' => '',
        //     ]);
        // dd($getToken, $server . 'truckingtnl-api/public/api/token');
        $accessTokenTnl = $data['accessTokenTnl'] ?? '';
        $access_token = $accessTokenTnl;
        if ($accessTokenTnl != '') {
            $imageBase64 = [];
            foreach ($gambar as $imagePath) {
                $imageBase64[] = base64_encode(file_get_contents(storage_path("app/upahsupir/" . $imagePath)));
            }
            $data['gambar'] = $imageBase64;
            $data['from'] = 'jkt';
            $transferUpahSupir = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ])

                ->post($server . "truckingtnl-api/public/api/upahsupir", $data);

            $tesResp = $transferUpahSupir->toPsrResponse();
            $response = [
                'statuscode' => $tesResp->getStatusCode(),
                'data' => $transferUpahSupir->json(),
            ];
            $dataResp = $transferUpahSupir->json();
            if ($tesResp->getStatusCode() != 201) {
                if ($tesResp->getStatusCode() == 422) {
                    throw new \Exception($dataResp['errors']['penyesuaian'][0] . ' di TNL');
                } else {
                    throw new \Exception($dataResp['message']);
                }
            }
            return $response;
        } else {
            throw new \Exception("server tidak bisa diakses");
        }
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $UpahSupir = UpahSupir::find($data['Id'][$i]);

            $UpahSupir->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            // dd($UpahSupir);
            if ($UpahSupir->save()) {

                (new LogTrail())->processStore([

                    'namatabel' => strtoupper($UpahSupir->getTable()),
                    'postingdari' => 'APPROVAL UPAH SUPIR',
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
    public function processApprovalaktif(array $data)
    {

        $parameter = new Parameter();
        $statusaktif = $parameter->cekId('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF') ?? 0;
        $statusnonaktif = $parameter->cekId('STATUS AKTIF', 'STATUS AKTIF', 'NON AKTIF') ?? 0;

        for ($i = 0; $i < count($data['Id']); $i++) {
            $UpahSupir = UpahSupir::find($data['Id'][$i]);
            $statusaktif_id = $UpahSupir->statusaktif ?? 0;
            if ($statusaktif_id == $statusaktif) {
                $UpahSupir->statusaktif = $statusnonaktif;
                $aksi = 'NON AKTIF';
            } else {
                $UpahSupir->statusaktif = $statusaktif;
                $aksi = 'AKTIF';
            }

            // dd($UpahSupir);
            if ($UpahSupir->save()) {

                (new LogTrail())->processStore([

                    'namatabel' => strtoupper($UpahSupir->getTable()),
                    'postingdari' => 'APPROVAL UPAH SUPIR',
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

    public function getRincian($statuskandang, $upah_id, $container_id, $statuscontainer_id)
    {
        if ($statuscontainer_id != '' && $container_id != '' && $upah_id != '') {

            $parameter = new Parameter();
            $idstatuskandang = $parameter->cekId('STATUS KANDANG', 'STATUS KANDANG', 'KANDANG') ?? 0;
            $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;
            // $idpelabuhan = $parameter->cekText('PELABUHAN CABANG', 'PELABUHAN CABANG') ?? 0;
            $statuspelabuhan = $parameter->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN', 'PELABUHAN') ?? 0;
            $idpelabuhan = db::table("kota")->from(db::raw("kota a with (readuncommitted)"))
                ->select(
                    db::raw("STRING_AGG(id,',') as id"),
                )
                ->where('a.statuspelabuhan', $statuspelabuhan)
                ->first()->id ?? 1;


            $upahsupirkandnag = db::table("upahsupir")->from(db::raw("upahsupir a with (readuncommitted)"))
                ->select(
                    'b.id',
                    'a.kotadari_id',
                    'a.kotasampai_id',
                    'b.upahsupir_id',
                    'b.container_id',
                    'b.statuscontainer_id',
                    'b.nominalsupir',
                    'b.nominalkenek',
                    'b.nominalkomisi',
                    'b.nominaltol',
                    'b.liter',
                    'b.tas_id',
                    'b.info',
                    'b.modifiedby',
                )
                ->join(db::raw("upahsupirrincian b with (readuncommitted)"), 'a.id', 'b.upahsupir_id')
                ->whereraw("a.kotadari_id in (" . $idpelabuhan . ")")
                ->where('a.kotasampai_id', $idkandang)
                ->where('b.container_id', $container_id)
                ->where('b.statuscontainer_id', $statuscontainer_id)
                ->whereraw("isnull(a.penyesuaian,'')=''");

            $tempupahsupirkandang = '##tempupahsupirkandang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempupahsupirkandang, function ($table) {
                $table->bigInteger('id')->nullable();
                $table->unsignedBigInteger('kotadari_id')->nullable();
                $table->unsignedBigInteger('kotasampai_id')->nullable();
                $table->unsignedBigInteger('upahsupir_id')->nullable();
                $table->unsignedBigInteger('container_id')->nullable();
                $table->unsignedBigInteger('statuscontainer_id')->nullable();
                $table->double('nominalsupir', 15, 2)->nullable();
                $table->double('nominalkenek', 15, 2)->nullable();
                $table->double('nominalkomisi', 15, 2)->nullable();
                $table->double('nominaltol', 15, 2)->nullable();
                $table->double('liter', 15, 2)->nullable();
                $table->unsignedBigInteger('tas_id')->nullable();
                $table->longText('info')->nullable();
                $table->string('modifiedby', 50)->nullable();
            });

            DB::table($tempupahsupirkandang)->insertUsing([
                'id',
                'kotadari_id',
                'kotasampai_id',
                'upahsupir_id',
                'container_id',
                'statuscontainer_id',
                'nominalsupir',
                'nominalkenek',
                'nominalkomisi',
                'nominaltol',
                'liter',
                'tas_id',
                'info',
                'modifiedby',
            ],  $upahsupirkandnag);

            $querynominal = db::table($tempupahsupirkandang)->from(db::raw($tempupahsupirkandang . " a"))
                ->select(
                    'a.nominalsupir',
                    'a.nominalkenek',
                    'a.nominalkomisi',
                )->first();

            if (isset($querynominal)) {
                $nominalsupirkandang = $querynominal->nominalsupir ?? 0;
                $nominalkenekkandang = $querynominal->nominalkenek ?? 0;
                $nominalkomisikandang = $querynominal->nominalkomisi ?? 0;
            } else {
                $nominalsupirkandang = 0;
                $nominalkenekkandang = 0;
                $nominalkomisikandang = 0;
            }

            $query = DB::table("upahsupirrrincian")->from(DB::raw("upahsupirrincian with (readuncommitted)"))
                ->where('upahsupir_id', $upah_id)
                ->where('statuscontainer_id', $statuscontainer_id)
                ->where('container_id', $container_id)
                ->first();
            if ($statuskandang == $idstatuskandang) {
                $data = [
                    'nominalkenek' => $query->nominalkenek - $nominalkenekkandang,
                    'nominalkomisi' => $query->nominalkomisi - $nominalkomisikandang,
                    'nominalsupir' => $query->nominalsupir - $nominalsupirkandang,
                ];
            } else {

                $data = [
                    'nominalkenek' => $query->nominalkenek,
                    'nominalkomisi' => $query->nominalkomisi,
                    'nominalsupir' => $query->nominalsupir,
                ];
            }
            return $data;
        } else {
            return [];
        }
    }

    public function processUpdateTarif(array $data)
    {
        // dd($upahsupir);
        // dd($data['id']);
        $upahsupir = UpahSupir::find($data['id']);
        try {
            if ($data['jenisorder_id'] != 0) {
                if ($data['jenisorder_id'] == 1) {
                    $upahsupir->tarifmuatan_id = $data['tarif_id'] ?? 0;
                    $upahsupir->modifiedby = auth('api')->user()->user;
                    $upahsupir->info = html_entity_decode(request()->info);
                }
                if ($data['jenisorder_id'] == 2) {
                    $upahsupir->tarifbongkaran_id = $data['tarif_id'] ?? 0;
                    $upahsupir->modifiedby = auth('api')->user()->user;
                    $upahsupir->info = html_entity_decode(request()->info);
                }
                if ($data['jenisorder_id'] == 3) {
                    $upahsupir->tarifimport_id = $data['tarif_id'] ?? 0;
                    $upahsupir->modifiedby = auth('api')->user()->user;
                    $upahsupir->info = html_entity_decode(request()->info);
                }
                if ($data['jenisorder_id'] == 4) {
                    $upahsupir->tarifexport_id = $data['tarif_id'] ?? 0;
                    $upahsupir->modifiedby = auth('api')->user()->user;
                    $upahsupir->info = html_entity_decode(request()->info);
                }
            } else {
                $upahsupir->tarif_id = $data['tarif_id'] ?? 0;
                $upahsupir->modifiedby = auth('api')->user()->user;
                $upahsupir->info = html_entity_decode(request()->info);
            }
            if (!$upahsupir->save()) {
                throw new \Exception("Error updating upah supir.");
            }
            $storedLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($upahsupir->getTable()),
                'postingdari' => 'EDIT UPAH DARI TARIF',
                'idtrans' => $upahsupir->id,
                'nobuktitrans' => $upahsupir->id,
                'aksi' => 'EDIT',
                'datajson' => $upahsupir->toArray(),
                'modifiedby' => $upahsupir->modifiedby
            ]);

            $isKandang = (new Parameter())->cekText('KANDANG', 'KANDANG');

            if ($isKandang != '0') {
                $query = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                    ->where('kotadari_id', $isKandang)
                    ->where('kotasampai_id', $upahsupir->kotasampai_id)
                    ->where('penyesuaian', $upahsupir->penyesuaian)
                    ->first();

                if ($query != '') {

                    $upahsupir = UpahSupir::find($query->id);
                    $upahsupir->tarif_id = $data['tarif_id'] ?? 0;
                    $upahsupir->modifiedby = auth('api')->user()->user;
                    $upahsupir->info = html_entity_decode(request()->info);
                    if (!$upahsupir->save()) {
                        throw new \Exception("Error updating upah supir.");
                    }
                    $storedLogTrail = (new LogTrail())->processStore([
                        'namatabel' => strtoupper($upahsupir->getTable()),
                        'postingdari' => 'EDIT UPAH DARI TARIF',
                        'idtrans' => $upahsupir->id,
                        'nobuktitrans' => $upahsupir->id,
                        'aksi' => 'EDIT',
                        'datajson' => $upahsupir->toArray(),
                        'modifiedby' => $upahsupir->modifiedby
                    ]);
                }
            }


            return $upahsupir;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
