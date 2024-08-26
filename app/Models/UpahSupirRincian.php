<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\StatusContainer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UpahSupirRincian extends MyModel
{
    use HasFactory;

    protected $table = 'upahsupirrincian';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getAll($id)
    {
        $tempcontainer = '##tempcontainer' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempcontainer, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('kodecontainer')->nullable();
            $table->longtext('keterangan')->nullable();
            $table->index('id');

            $table->index('id', 'tempcontainer_id_index');
        });

        $tempstatuscontainer = '##tempstatuscontainer' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstatuscontainer, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('kodestatuscontainer')->nullable();
            $table->longtext('keterangan')->nullable();
            $table->index('id');

            $table->index('id', 'tempstatuscontainer_id_index');
        });


        $parameter = new Parameter();
        $statusaktif = $parameter->cekId('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF') ?? 0;



        $querycontainer = db::table("container")->from(db::raw("container a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.kodecontainer',
                'a.keterangan',
            )
            ->where('a.statusaktif', $statusaktif)
            ->orderby('a.id', 'asc');

        DB::table($tempcontainer)->insertUsing([
            'id',
            'kodecontainer',
            'a.keterangan',

        ],  $querycontainer);


        DB::table($tempcontainer)->insert([
            'id' => 0,
            'kodecontainer' => '',
            'keterangan' => '',
        ]);

        $querystatuscontainer = db::table("statuscontainer")->from(db::raw("statuscontainer a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.kodestatuscontainer',
                'a.keterangan',


            )
            ->where('a.statusaktif', $statusaktif)
            ->orderby('a.id', 'asc');

        DB::table($tempstatuscontainer)->insertUsing([
            'id',
            'kodestatuscontainer',
            'keterangan',
        ],  $querystatuscontainer);


        DB::table($tempstatuscontainer)->insert([
            'id' => 0,
            'kodestatuscontainer' => '',
        ]);

        $query = DB::table('upahsupirrincian')->from(DB::raw("upahsupirrincian with (readuncommitted)"))
            ->select(
                'upahsupirrincian.container_id',
                'container.kodecontainer as container',
                'upahsupirrincian.statuscontainer_id',
                'statuscontainer.kodestatuscontainer as statuscontainer',
                'upahsupirrincian.nominalsupir',
                'upahsupirrincian.nominalkenek',
                'upahsupirrincian.nominalkomisi',
                'upahsupirrincian.nominaltol',
                'upahsupirrincian.liter',
            )
            ->Join(db::raw($tempcontainer . " as container"), 'container.id', db::raw("isnull(upahsupirrincian.container_id,0)"))
            ->Join(db::raw($tempstatuscontainer . "  as statuscontainer"), 'statuscontainer.id', db::raw("isnull(upahsupirrincian.statuscontainer_id,0)"))
            ->where('upahsupir_id', '=', $id)
            ->orderBy('container.id', 'asc')
            ->orderBy('statuscontainer.kodestatuscontainer', 'desc');


        $data = $query->get();


        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $proses = request()->proses ?? 'reload';
        $tglbukti = date('Y-m-d', strtotime(request()->tglbukti)) ?? '1900-01-01';
        $user = auth('api')->user()->name;
        $class = 'UpahSupirRincianController';

        $aktif = request()->aktif ?? '';
        $container_id = request()->container_id ?? 0;
        $statuscontainer_id = request()->statuscontainer_id ?? 0;
        $statuskandang_id = request()->statuskandang_id ?? 0;
        $jenisorder_id = request()->jenisorder_id ?? 0;
        $statusUpahZona = request()->statusupahzona ?? 0;
        $statusPenyesuaian = request()->statuspenyesuaian ?? 0;
        $nobukti_tripasal = request()->nobukti_tripasal ?? '';
        $longtrip = request()->longtrip ?? 0;
        $statuslangsir = request()->statuslangsir ?? 0;
        $dari_id = request()->dari_id ?? 0;
        $sampai_id = request()->sampai_id ?? 0;

        $jenisorderanmuatan = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
            ->where('a.grp', 'JENIS ORDERAN MUATAN')
            ->where('a.subgrp', 'JENIS ORDERAN MUATAN')
            ->first()->id;

        $jenisorderanbongkaran = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
            ->where('a.grp', 'JENIS ORDERAN BONGKARAN')
            ->where('a.subgrp', 'JENIS ORDERAN BONGKARAN')
            ->first()->id;

        $jenisorderanimport = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
            ->where('a.grp', 'JENIS ORDERAN IMPORT')
            ->where('a.subgrp', 'JENIS ORDERAN IMPORT')
            ->first()->id;

        $jenisorderanexport = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
            ->where('a.grp', 'JENIS ORDERAN EXPORT')
            ->where('a.subgrp', 'JENIS ORDERAN EXPORT')
            ->first()->id;

        $getJenisOrderMuatan = DB::table("jenisorder")->from(DB::raw("jenisorder with (readuncommitted)"))
            ->select('id')
            ->where("kodejenisorder", 'MUAT')
            ->orWhere("kodejenisorder", 'EKS')
            ->get();

        $parameter = new Parameter();
        $idstatuskandang = $parameter->cekId('STATUS KANDANG', 'STATUS KANDANG', 'KANDANG') ?? 0;
        $idstatuslangsir = $parameter->cekId('STATUS LANGSIR', 'STATUS LANGSIR', 'LANGSIR') ?? 0;
        $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;
        // $idpelabuhan = $parameter->cekText('PELABUHAN CABANG', 'PELABUHAN CABANG') ?? 0;
        $statuspelabuhan = $parameter->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN', 'PELABUHAN') ?? 0;
        $idpelabuhan = db::table("kota")->from(db::raw("kota a with (readuncommitted)"))
            ->select(
                db::raw("STRING_AGG(id,',') as id"),
            )
            ->where('a.statuspelabuhan', $statuspelabuhan)
            ->first()->id ?? 1;

        // $kotakandang=db::table("kota")->from(db::raw("kota a with (readuncommitted)"))
        // -select(
        //     'a.kodekota as kota'
        // )
        // ->where('a.id',$idkandang)
        // ->first()->kota ?? '';
        if ($statuslangsir == $idstatuslangsir) {
            $temtabel = $this->upahStatusLangsir();
            goto selesai;
        }

        // $upahsupirkandnag = db::table("upahsupir")->from(db::raw("upahsupir a with (readuncommitted)"))
        //     ->select(
        //         'b.id',
        //         'a.kotadari_id',
        //         'a.kotasampai_id',
        //         'b.upahsupir_id',
        //         'b.container_id',
        //         'b.statuscontainer_id',
        //         'b.nominalsupir',
        //         'b.nominalkenek',
        //         'b.nominalkomisi',
        //         'b.nominaltol',
        //         'b.liter',
        //         'b.tas_id',
        //         'b.info',
        //         'b.modifiedby',
        //     )
        //     ->join(db::raw("upahsupirrincian b with (readuncommitted)"), 'a.id', 'b.upahsupir_id')
        //     ->where('a.kotadari_id', $idpelabuhan)
        //     ->where('a.kotasampai_id', $idkandang)
        //     ->where('b.container_id', $container_id)
        //     ->where('b.statuscontainer_id', $statuscontainer_id)
        //     ->whereraw("isnull(a.penyesuaian,'')=''");

        // $tempupahsupirkandang = '##tempupahsupirkandang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($tempupahsupirkandang, function ($table) {
        //     $table->bigInteger('id')->nullable();
        //     $table->unsignedBigInteger('kotadari_id')->nullable();
        //     $table->unsignedBigInteger('kotasampai_id')->nullable();
        //     $table->unsignedBigInteger('upahsupir_id')->nullable();
        //     $table->unsignedBigInteger('container_id')->nullable();
        //     $table->unsignedBigInteger('statuscontainer_id')->nullable();
        //     $table->double('nominalsupir', 15, 2)->nullable();
        //     $table->double('nominalkenek', 15, 2)->nullable();
        //     $table->double('nominalkomisi', 15, 2)->nullable();
        //     $table->double('nominaltol', 15, 2)->nullable();
        //     $table->double('liter', 15, 2)->nullable();
        //     $table->unsignedBigInteger('tas_id')->nullable();
        //     $table->longText('info')->nullable();
        //     $table->string('modifiedby', 50)->nullable();
        // });

        // DB::table($tempupahsupirkandang)->insertUsing([
        //     'id',
        //     'kotadari_id',
        //     'kotasampai_id',
        //     'upahsupir_id',
        //     'container_id',
        //     'statuscontainer_id',
        //     'nominalsupir',
        //     'nominalkenek',
        //     'nominalkomisi',
        //     'nominaltol',
        //     'liter',
        //     'tas_id',
        //     'info',
        //     'modifiedby',
        // ],  $upahsupirkandnag);

        // $querynominal = db::table($tempupahsupirkandang)->from(db::raw($tempupahsupirkandang . " a"))
        //     ->select(
        //         'a.nominalsupir',
        //         'a.nominalkenek',
        //         'a.nominalkomisi',
        //     )->first();

        // if (isset($querynominal)) {
        //     $nominalsupirkandang = $querynominal->nominalsupir ?? 0;
        //     $nominalkenekkandang = $querynominal->nominalkenek ?? 0;
        //     $nominalkomisikandang = $querynominal->nominalkomisi ?? 0;
        // } else {
        //     $nominalsupirkandang = 0;
        //     $nominalkenekkandang = 0;
        //     $nominalkomisikandang = 0;
        // }




        // dd(db::table($tempupahsupirkandang)->get());

        $tempkota = '##tempkota' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkota, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('kodekota')->nullable();
            $table->index('id');

            $table->index('id', 'tempkota_id_index');
        });


        $tempzona = '##tempzona' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempzona, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('zona')->nullable();
            $table->index('id');

            $table->index('id', 'tempzona_id_index');
        });


        $querykota = db::table("kota")->from(db::raw("kota a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.kodekota'
            )
            ->orderby('a.id', 'asc');

        DB::table($tempkota)->insertUsing([
            'id',
            'kodekota',
        ],  $querykota);


        DB::table($tempkota)->insert([
            'id' => 0,
            'kodekota' => '',
        ]);

        $queryzona = db::table("zona")->from(db::raw("zona a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.zona'
            )
            ->orderby('a.id', 'asc');

        DB::table($tempzona)->insertUsing([
            'id',
            'zona',
        ],  $queryzona);


        DB::table($tempzona)->insert([
            'id' => 0,
            'zona' => '',
        ]);


        $tempcontainer = '##tempcontainer' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempcontainer, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('kodecontainer')->nullable();
            $table->longtext('keterangan')->nullable();
            $table->index('id');

            $table->index('id', 'tempcontainer_id_index');
        });

        $tempstatuscontainer = '##tempstatuscontainer' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstatuscontainer, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('kodestatuscontainer')->nullable();
            $table->longtext('keterangan')->nullable();
            $table->index('id');

            $table->index('id', 'tempstatuscontainer_id_index');
        });
        $parameter = new Parameter();
        $statusaktif = $parameter->cekId('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF') ?? 0;



        $querycontainer = db::table("container")->from(db::raw("container a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.kodecontainer',
                'a.keterangan',
            )
            ->where('a.statusaktif', $statusaktif)
            ->orderby('a.id', 'asc');

        DB::table($tempcontainer)->insertUsing([
            'id',
            'kodecontainer',
            'a.keterangan',

        ],  $querycontainer);


        DB::table($tempcontainer)->insert([
            'id' => 0,
            'kodecontainer' => '',
            'keterangan' => '',
        ]);

        $querystatuscontainer = db::table("statuscontainer")->from(db::raw("statuscontainer a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.kodestatuscontainer',
                'a.keterangan',


            )
            ->where('a.statusaktif', $statusaktif)
            ->orderby('a.id', 'asc');

        DB::table($tempstatuscontainer)->insertUsing([
            'id',
            'kodestatuscontainer',
            'keterangan',
        ],  $querystatuscontainer);


        DB::table($tempstatuscontainer)->insert([
            'id' => 0,
            'kodestatuscontainer' => '',
        ]);



        $parameter = new Parameter();
        $statusaktif = $parameter->cekId('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF') ?? 0;


        $getJenisOrderMuatan = json_decode($getJenisOrderMuatan, true);
        foreach ($getJenisOrderMuatan as $item) {
            $dataMuatanEksport[] = $item['id'];
        }
        $temp = '##tempUpah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->integer('kotadari_id')->length(11)->nullable();
            $table->integer('kotasampai_id')->length(11)->nullable();
            $table->string('kotadari')->nullable();
            $table->string('kotasampai')->nullable();
            $table->integer('zonadari_id')->length(11)->nullable();
            $table->integer('zonasampai_id')->length(11)->nullable();
            $table->string('zonadari')->nullable();
            $table->string('zonasampai')->nullable();
            $table->integer('tarif_id')->nullable();
            $table->string('tarif')->nullable();
            $table->string('penyesuaian')->nullable();
            $table->double('jarak', 15, 2)->nullable();
            $table->double('omset', 15, 2)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });



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
                $table->longtext('kotadari_id')->nullable();
                $table->longtext('kotasampai_id')->nullable();
                $table->longtext('zonadari_id')->nullable();
                $table->longtext('zonasampai_id')->nullable();
                $table->longtext('tarif_id')->nullable();
                $table->longtext('tarif')->nullable();
                $table->longtext('kotadari')->nullable();
                $table->longtext('kotasampai')->nullable();
                $table->longtext('zonadari')->nullable();
                $table->longtext('zonasampai')->nullable();
                $table->longtext('penyesuaian')->nullable();
                $table->double('jarak', 15, 2)->nullable();
                $table->longtext('statusaktif')->nullable();
                $table->longtext('container')->nullable();
                $table->longtext('statuscontainer')->nullable();
                $table->double('omset', 15, 2)->nullable();
                $table->double('nominalsupir', 15, 2)->nullable();
                $table->double('nominalkenek', 15, 2)->nullable();
                $table->double('nominalkomisi', 15, 2)->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->longtext('modifiedby')->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
                $table->string('kotadarisampai')->nullable();
            });

            // dd('test');
            if ($longtrip == 65) {

                goto longtrip;
            }
            $temptarif = '##temptarif' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temptarif, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('upahsupir_id')->nullable();
                $table->string('tujuan', 200)->nullable();
                $table->string('penyesuaian', 200)->nullable();
                $table->integer('statusaktif')->nullable();
                $table->integer('statussistemton')->nullable();
                $table->unsignedBigInteger('kota_id')->nullable();
                $table->unsignedBigInteger('zona_id')->nullable();
                $table->unsignedBigInteger('jenisorder_id')->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->integer('statuspenyesuaianharga')->nullable();
                $table->integer('statuspostingtnl')->nullable();
                $table->longtext('keterangan')->nullable();
                $table->longtext('info')->nullable();
                $table->string('modifiedby', 50)->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
                $table->integer('tas_id')->nullable();
            });



            $querytarif = db::table('tarif')->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.parent_id',
                    'a.upahsupir_id',
                    'a.tujuan',
                    'a.penyesuaian',
                    'a.statusaktif',
                    'a.statussistemton',
                    'a.kota_id',
                    'a.zona_id',
                    'a.jenisorder_id',
                    'a.tglmulaiberlaku',
                    'a.statuspenyesuaianharga',
                    'a.statuspostingtnl',
                    'a.keterangan',
                    'a.info',
                    'a.modifiedby',
                    'a.created_at',
                    'a.updated_at',
                    'a.tas_id',
                )
                ->whereRaw("cast('" . $tglbukti . "' as datetime)>=a.tglmulaiberlaku")
                ->where('a.statusaktif', 1)
                // ->whereRaw("isnull(a.statuslangsir,'') != 79")
                ->orderby('a.id', 'asc');

            DB::table($temptarif)->insertUsing([
                'id',
                'parent_id',
                'upahsupir_id',
                'tujuan',
                'penyesuaian',
                'statusaktif',
                'statussistemton',
                'kota_id',
                'zona_id',
                'jenisorder_id',
                'tglmulaiberlaku',
                'statuspenyesuaianharga',
                'statuspostingtnl',
                'keterangan',
                'info',
                'modifiedby',
                'created_at',
                'updated_at',
                'tas_id',
            ],  $querytarif);

            $tempupahsupir = '##tempupahsupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempupahsupir, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('tarif_id')->nullable();
                $table->unsignedBigInteger('tarifmuatan_id')->nullable();
                $table->unsignedBigInteger('tarifbongkaran_id')->nullable();
                $table->unsignedBigInteger('tarifimport_id')->nullable();
                $table->unsignedBigInteger('tarifexport_id')->nullable();
                $table->unsignedBigInteger('kotadari_id')->nullable();
                $table->unsignedBigInteger('kotasampai_id')->nullable();
                $table->unsignedBigInteger('zonadari_id')->nullable();
                $table->unsignedBigInteger('zonasampai_id')->nullable();
                $table->string('penyesuaian', 200)->nullable();
                $table->double('jarak', 15, 2)->nullable();
                $table->double('jarakfullempty', 15, 2)->nullable();
                $table->unsignedBigInteger('zona_id')->nullable();
                $table->integer('statusaktif')->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->integer('statusluarkota')->nullable();
                $table->integer('statusupahzona')->nullable();
                $table->integer('statussimpankandang')->nullable();
                $table->integer('statuspostingtnl')->nullable();
                $table->longtext('keterangan')->nullable();
                $table->longtext('gambar')->nullable();
                $table->longtext('info')->nullable();
                $table->string('modifiedby', 50)->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
                $table->string('editing_by', 50)->nullable();
                $table->datetime('editing_at')->nullable();
                $table->integer('tas_id')->nullable();
            });


            // GET UPAH PELABUHAN - KANDANG
            if ($idkandang != 0) {
                $getUpahPelabuhanKandang = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                    ->whereraw("kotadari_id in (" . $idpelabuhan . ")")
                    ->where('kotasampai_id', $idkandang)
                    ->first();

                if (isset($getUpahPelabuhanKandang)) {
                    $queryGetTarifForPelabuhanKandang = db::table("tarif")->from(DB::raw("tarif a with (readuncommitted)"))
                        ->select(
                            DB::raw("'$getUpahPelabuhanKandang->id' as id"),
                            DB::raw("'$getUpahPelabuhanKandang->parent_id' as parent_id"),
                            DB::raw("a.id as tarif_id"),
                            DB::raw("a.id as tarifmuatan_id"),
                            DB::raw("a.id as tarifbongkaran_id"),
                            DB::raw("a.id as tarifimport_id"),
                            DB::raw("a.id as tarifiexport_id"),
                            DB::raw("'$getUpahPelabuhanKandang->kotadari_id' as kotadari_id"),
                            DB::raw("'$getUpahPelabuhanKandang->kotasampai_id' as kotasampai_id"),
                            DB::raw("'$getUpahPelabuhanKandang->zonadari_id' as zonadari_id"),
                            DB::raw("'$getUpahPelabuhanKandang->zonasampai_id' as zonasampai_id"),
                            DB::raw("a.penyesuaian as penyesuaian"),
                            DB::raw("'$getUpahPelabuhanKandang->jarak' as jarak"),
                            DB::raw("'$getUpahPelabuhanKandang->jarakfullempty' as jarakfullempty"),
                            DB::raw("'$getUpahPelabuhanKandang->zona_id' as zona_id"),
                            DB::raw("'$getUpahPelabuhanKandang->statusaktif' as statusaktif"),
                            DB::raw("'$getUpahPelabuhanKandang->tglmulaiberlaku' as tglmulaiberlaku"),
                            DB::raw("'$getUpahPelabuhanKandang->statusluarkota' as statusluarkota"),
                            DB::raw("'$getUpahPelabuhanKandang->statusupahzona' as statusupahzona"),
                            DB::raw("'$getUpahPelabuhanKandang->statussimpankandang' as statussimpankandang"),
                            DB::raw("'$getUpahPelabuhanKandang->statuspostingtnl' as statuspostingtnl"),
                            DB::raw("'$getUpahPelabuhanKandang->keterangan' as keterangan"),
                            DB::raw("'$getUpahPelabuhanKandang->gambar' as gambar"),
                            DB::raw("'$getUpahPelabuhanKandang->info' as info"),
                            DB::raw("'$getUpahPelabuhanKandang->modifiedby' as modifiedby"),
                            DB::raw("'$getUpahPelabuhanKandang->created_at' as created_at"),
                            DB::raw("'$getUpahPelabuhanKandang->updated_at' as updated_at"),
                            DB::raw("'$getUpahPelabuhanKandang->editing_by' as editing_by"),
                            DB::raw("'$getUpahPelabuhanKandang->editing_at' as editing_at"),
                            DB::raw("'$getUpahPelabuhanKandang->tas_id' as tas_id")
                        );
                    // ->whereRaw("isnull(a.statuslangsir,'') != 79");
                    if ($statusPenyesuaian == 662) {
                        $queryGetTarifForPelabuhanKandang->whereRaw("isnull(a.penyesuaian,'') != ''");
                    } else {
                        $queryGetTarifForPelabuhanKandang->whereRaw("isnull(a.penyesuaian,'') = ''");
                    }

                    DB::table($tempupahsupir)->insertUsing([
                        'id',
                        'parent_id',
                        'tarif_id',
                        'tarifmuatan_id',
                        'tarifbongkaran_id',
                        'tarifimport_id',
                        'tarifexport_id',
                        'kotadari_id',
                        'kotasampai_id',
                        'zonadari_id',
                        'zonasampai_id',
                        'penyesuaian',
                        'jarak',
                        'jarakfullempty',
                        'zona_id',
                        'statusaktif',
                        'tglmulaiberlaku',
                        'statusluarkota',
                        'statusupahzona',
                        'statussimpankandang',
                        'statuspostingtnl',
                        'keterangan',
                        'gambar',
                        'info',
                        'modifiedby',
                        'created_at',
                        'updated_at',
                        'editing_by',
                        'editing_at',
                        'tas_id',
                    ],  $queryGetTarifForPelabuhanKandang);
                }
            }


            // GET UPAH SUPIR PELABUHAN
            $queryupahsupir = db::table('upahsupir')->from(db::raw("upahsupir a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.parent_id',
                    'a.tarif_id',
                    'a.tarifmuatan_id',
                    'a.tarifbongkaran_id',
                    'a.tarifimport_id',
                    'a.tarifexport_id',
                    'a.kotadari_id',
                    'a.kotasampai_id',
                    'a.zonadari_id',
                    'a.zonasampai_id',
                    'a.penyesuaian',
                    'a.jarak',
                    'a.jarakfullempty',
                    'a.zona_id',
                    'a.statusaktif',
                    'a.tglmulaiberlaku',
                    'a.statusluarkota',
                    'a.statusupahzona',
                    'a.statussimpankandang',
                    'a.statuspostingtnl',
                    'a.keterangan',
                    'a.gambar',
                    'a.info',
                    'a.modifiedby',
                    'a.created_at',
                    'a.updated_at',
                    'a.editing_by',
                    'a.editing_at',
                    'a.tas_id',
                )
                ->whereRaw("cast('" . $tglbukti . "' as datetime)>=a.tglmulaiberlaku")
                ->orderby('a.id', 'asc')
                // ->whereRaw("isnull(a.statuslangsir,'') != 79")
                ->where('a.kotadari_id', 1);

            DB::table($tempupahsupir)->insertUsing([
                'id',
                'parent_id',
                'tarif_id',
                'tarifmuatan_id',
                'tarifbongkaran_id',
                'tarifimport_id',
                'tarifexport_id',
                'kotadari_id',
                'kotasampai_id',
                'zonadari_id',
                'zonasampai_id',
                'penyesuaian',
                'jarak',
                'jarakfullempty',
                'zona_id',
                'statusaktif',
                'tglmulaiberlaku',
                'statusluarkota',
                'statusupahzona',
                'statussimpankandang',
                'statuspostingtnl',
                'keterangan',
                'gambar',
                'info',
                'modifiedby',
                'created_at',
                'updated_at',
                'editing_by',
                'editing_at',
                'tas_id',
            ],  $queryupahsupir);

            // GET UPAH SUPIR KANDANG
            $queryupahsupir = db::table('upahsupir')->from(db::raw("upahsupir a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.parent_id',
                    'a.tarif_id',
                    'a.tarifmuatan_id',
                    'a.tarifbongkaran_id',
                    'a.tarifimport_id',
                    'a.tarifexport_id',
                    'a.kotadari_id',
                    'a.kotasampai_id',
                    'a.zonadari_id',
                    'a.zonasampai_id',
                    'a.penyesuaian',
                    'a.jarak',
                    'a.jarakfullempty',
                    'a.zona_id',
                    'a.statusaktif',
                    'a.tglmulaiberlaku',
                    'a.statusluarkota',
                    'a.statusupahzona',
                    'a.statussimpankandang',
                    'a.statuspostingtnl',
                    'a.keterangan',
                    'a.gambar',
                    'a.info',
                    'a.modifiedby',
                    'a.created_at',
                    'a.updated_at',
                    'a.editing_by',
                    'a.editing_at',
                    'a.tas_id',
                )
                ->whereRaw("cast('" . $tglbukti . "' as datetime)>=a.tglmulaiberlaku")
                ->orderby('a.id', 'asc')
                // ->whereRaw("isnull(a.statuslangsir,'') != 79")
                ->where('a.kotadari_id', $idkandang);

            DB::table($tempupahsupir)->insertUsing([
                'id',
                'parent_id',
                'tarif_id',
                'tarifmuatan_id',
                'tarifbongkaran_id',
                'tarifimport_id',
                'tarifexport_id',
                'kotadari_id',
                'kotasampai_id',
                'zonadari_id',
                'zonasampai_id',
                'penyesuaian',
                'jarak',
                'jarakfullempty',
                'zona_id',
                'statusaktif',
                'tglmulaiberlaku',
                'statusluarkota',
                'statusupahzona',
                'statussimpankandang',
                'statuspostingtnl',
                'keterangan',
                'gambar',
                'info',
                'modifiedby',
                'created_at',
                'updated_at',
                'editing_by',
                'editing_at',
                'tas_id',
            ],  $queryupahsupir);


            if (in_array($jenisorder_id, $dataMuatanEksport)) {
                $queryFull = DB::table("statuscontainer")->from(DB::raw("statuscontainer with (readuncommitted)"))->where('kodestatuscontainer', 'FULL')->first();
                // jika empty
                if ($statuscontainer_id == $queryFull->id) {
                    $getKota = DB::table($tempupahsupir)->from(DB::raw($tempupahsupir . " as upahsupir "))
                        ->select(
                            'upahsupir.id',
                            'upahsupir.kotasampai_id as kotadari_id',
                            'upahsupir.kotadari_id as kotasampai_id',
                            'kotasampai.kodekota as kotadari',
                            'kotadari.kodekota as kotasampai',
                            'upahsupir.zonasampai_id as zonadari_id',
                            'upahsupir.zonadari_id as zonasampai_id',
                            'zonasampai.zona as zonadari',
                            'zonadari.zona as zonasampai',
                            // 'upahsupir.tarif_id',
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.id,0)  
                                           when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . "then isnull(tarifbongkaran.id,0)  
                                           when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.id,0)  
                                           when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.id,0)  
                                           else  isnull(tarif.id,0) end) as tarif_id
                            "),
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.tujuan,0)  
                                           when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.tujuan,0)  
                                           when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.tujuan,0)  
                                           when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.tujuan,0)  
                                           else  isnull(tarif.tujuan,'') end) as tarif
                            "),
                            'upahsupir.penyesuaian',
                            'upahsupir.jarak',
                            DB::raw("0 as omset"),
                            'upahsupir.statusaktif',
                            'upahsupir.tglmulaiberlaku',
                            'upahsupir.modifiedby',
                            'upahsupir.created_at',
                            'upahsupir.updated_at'
                        )
                        ->Join(DB::raw($tempkota . " as kotadari with (readuncommitted)"), db::raw("isnull(upahsupir.kotadari_id,0)"), 'kotadari.id')
                        ->Join(DB::raw($tempkota . " as kotasampai with (readuncommitted)"), db::raw("isnull(upahsupir.kotasampai_id,0)"), 'kotasampai.id')
                        ->Join(DB::raw($tempzona . " as zonadari with (readuncommitted)"), db::raw("isnull(upahsupir.zonadari_id,0)"), 'zonadari.id')
                        ->Join(DB::raw($tempzona . " as zonasampai with (readuncommitted)"), db::raw("isnull(upahsupir.zonasampai_id,0)"), 'zonasampai.id')
                        ->leftJoin(DB::raw($temptarif . " as tarif "), 'upahsupir.tarif_id', 'tarif.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifmuatan "), 'upahsupir.tarifmuatan_id', 'tarifmuatan.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifbongkaran "), 'upahsupir.tarifbongkaran_id', 'tarifbongkaran.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifimport "), 'upahsupir.tarifimport_id', 'tarifimport.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifexport "), 'upahsupir.tarifexport_id', 'tarifexport.id');
                } else {

                    $getKota = DB::table($tempupahsupir)->from(DB::raw($tempupahsupir . " as upahsupir "))
                        ->select(
                            'upahsupir.id',
                            'upahsupir.kotadari_id',
                            'upahsupir.kotasampai_id',
                            'kotadari.kodekota as kotadari',
                            'kotasampai.kodekota as kotasampai',
                            'upahsupir.zonadari_id',
                            'upahsupir.zonasampai_id',
                            'zonadari.zona as zonadari',
                            'zonasampai.zona as zonasampai',
                            // 'upahsupir.tarif_id',
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.id,0)  
                            when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . "then isnull(tarifbongkaran.id,0)  
                            when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.id,0)  
                            when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.id,0)  
                            else  isnull(tarif.id,0) end) as tarif_id
                            "),
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.tujuan,0)  
                            when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.tujuan,0)  
                            when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.tujuan,0)  
                            when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.tujuan,0)  
                            else  isnull(tarif.tujuan,'') end) as tarif
                            "),
                            'upahsupir.penyesuaian',
                            'upahsupir.jarak',
                            DB::raw("0 as omset"),
                            'upahsupir.statusaktif',
                            'upahsupir.tglmulaiberlaku',
                            'upahsupir.modifiedby',
                            'upahsupir.created_at',
                            'upahsupir.updated_at'
                        )
                        // ->Join(DB::raw($tempkota . " as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                        // ->Join(DB::raw($tempkota . " as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                        // ->Join(DB::raw($tempzona . " as zonadari with (readuncommitted)"), 'upahsupir.zonadari_id', 'zonadari.id')
                        // ->Join(DB::raw($tempzona . " as zonasampai with (readuncommitted)"), 'upahsupir.zonasampai_id', 'zonasampai.id')
                        ->Join(DB::raw($tempkota . " as kotadari with (readuncommitted)"), db::raw("isnull(upahsupir.kotadari_id,0)"), 'kotadari.id')
                        ->Join(DB::raw($tempkota . " as kotasampai with (readuncommitted)"), db::raw("isnull(upahsupir.kotasampai_id,0)"), 'kotasampai.id')
                        ->Join(DB::raw($tempzona . " as zonadari with (readuncommitted)"), db::raw("isnull(upahsupir.zonadari_id,0)"), 'zonadari.id')
                        ->Join(DB::raw($tempzona . " as zonasampai with (readuncommitted)"), db::raw("isnull(upahsupir.zonasampai_id,0)"), 'zonasampai.id')

                        ->leftJoin(DB::raw($temptarif . " as tarif "), 'upahsupir.tarif_id', 'tarif.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifmuatan "), 'upahsupir.tarifmuatan_id', 'tarifmuatan.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifbongkaran "), 'upahsupir.tarifbongkaran_id', 'tarifbongkaran.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifimport "), 'upahsupir.tarifimport_id', 'tarifimport.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifexport "), 'upahsupir.tarifexport_id', 'tarifexport.id');
                }
                // dd($getKota->tosql());
                DB::table($temp)->insertUsing(['id', 'kotadari_id', 'kotasampai_id', 'kotadari', 'kotasampai', 'zonadari_id', 'zonasampai_id', 'zonadari', 'zonasampai', 'tarif_id', 'tarif', 'penyesuaian', 'jarak', 'omset', 'statusaktif', 'tglmulaiberlaku', 'modifiedby', 'created_at', 'updated_at'], $getKota);
            } else {

                $queryEmpty = DB::table("statuscontainer")->from(DB::raw("statuscontainer with (readuncommitted)"))->where('kodestatuscontainer', 'EMPTY')->first();
                // jika empty
                if ($statuscontainer_id == $queryEmpty->id) {

                    $getKota = DB::table($tempupahsupir)->from(DB::raw($tempupahsupir . " as upahsupir"))
                        ->select(
                            'upahsupir.id',
                            'upahsupir.kotasampai_id as kotadari_id',
                            'upahsupir.kotadari_id as kotasampai_id',
                            'kotasampai.kodekota as kotadari',
                            'kotadari.kodekota as kotasampai',
                            'upahsupir.zonasampai_id as zonadari_id',
                            'upahsupir.zonadari_id as zonasampai_id',
                            'zonasampai.zona as zonadari',
                            'zonadari.zona as zonasampai',
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.id,0)  
                            when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . "then isnull(tarifbongkaran.id,0)  
                            when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.id,0)  
                            when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.id,0)  
                            else  isnull(tarif.id,0) end) as tarif_id
                            "),
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.tujuan,0)  
                            when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.tujuan,0)  
                            when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.tujuan,0)  
                            when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.tujuan,0)  
                            else  isnull(tarif.tujuan,'') end) as tarif
                            "),
                            'upahsupir.penyesuaian',
                            'upahsupir.jarak',
                            DB::raw("0 as omset"),
                            'upahsupir.statusaktif',
                            'upahsupir.tglmulaiberlaku',
                            'upahsupir.modifiedby',
                            'upahsupir.created_at',
                            'upahsupir.updated_at'
                        )
                        // ->Join(DB::raw($tempkota . " as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                        // ->Join(DB::raw($tempkota . " as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                        // ->Join(DB::raw($tempzona . " as zonadari with (readuncommitted)"), 'upahsupir.zonadari_id', 'zonadari.id')
                        // ->Join(DB::raw($tempzona . " as zonasampai with (readuncommitted)"), 'upahsupir.zonasampai_id', 'zonasampai.id')
                        ->Join(DB::raw($tempkota . " as kotadari with (readuncommitted)"), db::raw("isnull(upahsupir.kotadari_id,0)"), 'kotadari.id')
                        ->Join(DB::raw($tempkota . " as kotasampai with (readuncommitted)"), db::raw("isnull(upahsupir.kotasampai_id,0)"), 'kotasampai.id')
                        ->Join(DB::raw($tempzona . " as zonadari with (readuncommitted)"), db::raw("isnull(upahsupir.zonadari_id,0)"), 'zonadari.id')
                        ->Join(DB::raw($tempzona . " as zonasampai with (readuncommitted)"), db::raw("isnull(upahsupir.zonasampai_id,0)"), 'zonasampai.id')

                        ->leftJoin(DB::raw($temptarif . " as tarif "), 'upahsupir.tarif_id', 'tarif.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifmuatan "), 'upahsupir.tarifmuatan_id', 'tarifmuatan.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifbongkaran "), 'upahsupir.tarifbongkaran_id', 'tarifbongkaran.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifimport "), 'upahsupir.tarifimport_id', 'tarifimport.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifexport "), 'upahsupir.tarifexport_id', 'tarifexport.id');
                } else {

                    $getKota = DB::table($tempupahsupir)->from(DB::raw($tempupahsupir . " as upahsupir"))
                        ->select(
                            'upahsupir.id',
                            'upahsupir.kotadari_id',
                            'upahsupir.kotasampai_id',
                            'kotadari.kodekota as kotadari',
                            'kotasampai.kodekota as kotasampai',
                            'upahsupir.zonadari_id',
                            'upahsupir.zonasampai_id',
                            'zonadari.zona as zonadari',
                            'zonasampai.zona as zonasampai',
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.id,0)  
                            when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . "then isnull(tarifbongkaran.id,0)  
                            when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.id,0)  
                            when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.id,0)  
                            else  isnull(tarif.id,0) end) as tarif_id
                            "),
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.tujuan,0)  
                            when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.tujuan,0)  
                            when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.tujuan,0)  
                            when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.tujuan,0)  
                            else  isnull(tarif.tujuan,'') end) as tarif
                             "),
                            'upahsupir.penyesuaian',
                            'upahsupir.jarak',
                            DB::raw("0 as omset"),
                            'upahsupir.statusaktif',
                            'upahsupir.tglmulaiberlaku',
                            'upahsupir.modifiedby',
                            'upahsupir.created_at',
                            'upahsupir.updated_at'
                        )
                        // ->Join(DB::raw($tempkota . " as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                        // ->Join(DB::raw($tempkota . " as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                        // ->Join(DB::raw($tempzona . " as zonadari with (readuncommitted)"), 'upahsupir.zonadari_id', 'zonadari.id')
                        // ->Join(DB::raw($tempzona . " as zonasampai with (readuncommitted)"), 'upahsupir.zonasampai_id', 'zonasampai.id')
                        ->Join(DB::raw($tempkota . " as kotadari with (readuncommitted)"), db::raw("isnull(upahsupir.kotadari_id,0)"), 'kotadari.id')
                        ->Join(DB::raw($tempkota . " as kotasampai with (readuncommitted)"), db::raw("isnull(upahsupir.kotasampai_id,0)"), 'kotasampai.id')
                        ->Join(DB::raw($tempzona . " as zonadari with (readuncommitted)"), db::raw("isnull(upahsupir.zonadari_id,0)"), 'zonadari.id')
                        ->Join(DB::raw($tempzona . " as zonasampai with (readuncommitted)"), db::raw("isnull(upahsupir.zonasampai_id,0)"), 'zonasampai.id')

                        ->leftJoin(DB::raw($temptarif . " as tarif "), 'upahsupir.tarif_id', 'tarif.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifmuatan "), 'upahsupir.tarifmuatan_id', 'tarifmuatan.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifbongkaran "), 'upahsupir.tarifbongkaran_id', 'tarifbongkaran.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifimport "), 'upahsupir.tarifimport_id', 'tarifimport.id')
                        ->leftJoin(DB::raw($temptarif . " as tarifexport "), 'upahsupir.tarifexport_id', 'tarifexport.id');
                }

                DB::table($temp)->insertUsing(['id', 'kotadari_id', 'kotasampai_id', 'kotadari', 'kotasampai', 'zonadari_id', 'zonasampai_id', 'zonadari', 'zonasampai', 'tarif_id', 'tarif', 'penyesuaian', 'jarak', 'omset', 'statusaktif', 'tglmulaiberlaku', 'modifiedby', 'created_at', 'updated_at'], $getKota);
            }
            // UNTUK TRIP NORMAL, YG TARIFNYA KOSONG DIHAPUS
            if ($longtrip == 66) {
                DB::table($temp)->where('tarif_id', 0)->delete();
            }

            // dd(DB::table($temp)->orderBy('id','desc')->get());

            $temptarif = '##tempTarif' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temptarif, function ($table) {
                $table->bigInteger('id')->nullable();
                $table->integer('kotadari_id')->length(11)->nullable();
                $table->integer('kotasampai_id')->length(11)->nullable();
                $table->string('kotadari')->nullable();
                $table->string('kotasampai')->nullable();
                $table->integer('zonadari_id')->length(11)->nullable();
                $table->integer('zonasampai_id')->length(11)->nullable();
                $table->string('zonadari')->nullable();
                $table->string('zonasampai')->nullable();
                $table->integer('tarif_id')->nullable();
                $table->string('tarif')->nullable();
                $table->string('penyesuaian')->nullable();
                $table->double('jarak', 15, 2)->nullable();
                $table->integer('statusaktif')->length(11)->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->string('modifiedby', 50)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->double('omset', 15, 2)->nullable();
            });

            $query = DB::table("tarifrincian")->from(DB::raw("tarifrincian with (readuncommitted)"))
                ->select(
                    'B.id',
                    'B.kotadari_id',
                    'B.kotasampai_id',
                    'B.kotadari',
                    'B.kotasampai',
                    'B.zonadari_id',
                    'B.zonasampai_id',
                    'B.zonadari',
                    'B.zonasampai',
                    'B.tarif_id',
                    'B.tarif',
                    'B.penyesuaian',
                    'B.jarak',
                    'B.statusaktif',
                    'B.tglmulaiberlaku',
                    'B.modifiedby',
                    'B.created_at',
                    'B.updated_at',
                    DB::raw("tarifrincian.nominal as omset")
                )
                ->leftJoin(DB::raw("$temp as B with (readuncommitted)"), 'B.tarif_id', 'tarifrincian.tarif_id')
                ->where('tarifrincian.container_id', $container_id);
            if ($longtrip == 66) {
                $query->where('tarifrincian.nominal', '!=', 0);
            }
            DB::table($temptarif)->insertUsing([
                'id',
                'kotadari_id',
                'kotasampai_id',
                'kotadari',
                'kotasampai',
                'zonadari_id',
                'zonasampai_id',
                'zonadari',
                'zonasampai',
                'tarif_id',
                'tarif',
                'penyesuaian',
                'jarak',
                'statusaktif',
                'tglmulaiberlaku',
                'modifiedby',
                'created_at',
                'updated_at',
                'omset',
            ], $query);
            // delete temp yg tarif_id=0
            // join tarif dengan $temp ke temp baru
            // join upah dengan temp dari tarif ke tamp upah baru
            // hasilnya baru di masukkan ke dalam temp fisik

            // dump(db::table($tempupahsupirkandang)->get());
            // dd(db::table($tempupahsupir)->get());

            // dd(db::table($temptarif)->get());

            $query = DB::table("upahsupirrincian")->from(DB::raw("upahsupirrincian with (readuncommitted)"))
                ->select(
                    'B.id',
                    'B.kotadari_id',
                    'B.kotasampai_id',
                    'B.zonadari_id',
                    'B.zonasampai_id',
                    'B.tarif_id',
                    'B.tarif',
                    'B.kotadari',
                    'B.kotasampai',
                    'B.zonadari',
                    'B.zonasampai',
                    'B.penyesuaian',
                    'B.jarak',
                    'parameter.memo as statusaktif',
                    'container.kodecontainer as container',
                    'statuscontainer.kodestatuscontainer as statuscontainer',
                    db::raw("isnull(B.omset,0) as omset"),
                    // db::raw("(upahsupirrincian.nominalsupir-
                    // (case when " . $statuskandang_id . "=" . $idstatuskandang . " then " . $nominalsupirkandang . " else  0 end))
                    // as nominalsupir"),
                    // db::raw("(upahsupirrincian.nominalkenek-
                    // (case when " . $statuskandang_id . "=" . $idstatuskandang . " then " . $nominalkenekkandang . " else  0 end))
                    // as nominalkenek"),
                    // db::raw("(upahsupirrincian.nominalkomisi-
                    // (case when " . $statuskandang_id . "=" . $idstatuskandang . " then " . $nominalkomisikandang . " else  0 end))
                    // as nominalkomisi"),

                    'upahsupirrincian.nominalsupir',
                    'upahsupirrincian.nominalkenek',
                    'upahsupirrincian.nominalkomisi',
                    'B.tglmulaiberlaku',
                    'B.modifiedby',
                    'B.created_at',
                    'B.updated_at',
                    DB::raw("(b.tarif + ' (' + trim(b.kotadari)+' - '+trim(b.kotasampai) + 
                        (case when isnull(b.penyesuaian,'') != '' then ') ' + b.penyesuaian else + ')' end)) as kotadarisampai"),

                );
            // ->Join(DB::raw($tempupahsupir . " as B1 "), 'B1.id', 'upahsupirrincian.upahsupir_id');
            if ($longtrip == 66) {
                $query->leftJoin(DB::raw("$temptarif as B with (readuncommitted)"), 'B.id', 'upahsupirrincian.upahsupir_id');
            } else {
                $query->leftJoin(DB::raw("$temp as B with (readuncommitted)"), 'B.id', 'upahsupirrincian.upahsupir_id');
            }
            $query->leftJoin(DB::raw("parameter with (readuncommitted)"), 'B.statusaktif', '=', 'parameter.id')
                ->Join(DB::raw($tempcontainer . " as container with (readuncommitted)"), db::raw("isnull(upahsupirrincian.container_id,0)"), 'container.id')
                ->Join(DB::raw($tempstatuscontainer . " as statuscontainer with (readuncommitted)"), db::raw("isnull(upahsupirrincian.statuscontainer_id,0)"), 'statuscontainer.id')
                // ->leftJoin(DB::raw("$tempupahsupirkandang as b2 with (readuncommitted)"), 'B2.kotadari_id', 'b1.kotadari_id')
                // ->leftJoin(DB::raw($tempupahsupirkandang . " as b2 "), function ($join) {
                //     $join->on('b1.kotadari_id', '=', 'b2.kotadari_id');
                //     // $join->on('b1.kotasampai_id', '=', 'b2.kotasampai_id');
                // })

                ->where('upahsupirrincian.nominalsupir', '!=', 0);

            if (($aktif == 'AKTIF')) {
                $statusaktif = Parameter::from(
                    DB::raw("parameter with (readuncommitted)")
                )
                    ->where('grp', '=', 'STATUS AKTIF')
                    ->where('text', '=', 'AKTIF')
                    ->first();

                $query->where('B.statusaktif', '=', $statusaktif->id);
            }
            if ($container_id > 0) {
                $query->where('upahsupirrincian.container_id', '=', $container_id);
            }
            if ($statuscontainer_id > 0) {
                $query->where('upahsupirrincian.statuscontainer_id', '=', $statuscontainer_id);
            }

            if ($longtrip == 66 && $nobukti_tripasal != '') {
                $tempSampai = '##tempSampai' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempSampai, function ($table) {
                    $table->integer('sampai_id')->length(11)->nullable();
                });
                $getSampai = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->select('sampai_id')->where('nobukti', $nobukti_tripasal)->first();
                // DB::table($tempSampai)->insertUsing([
                //     'sampai_id',
                // ], $getSampai);
                // dd( DB::table($tempSampai)->get(), $query->orderBy('upahsupirrincian.id','desc')->limit(10)->get());

                $query->where('B.kotadari_id', $getSampai->sampai_id);
            }
            // if ($longtrip == 65) {
            //     $query->whereRaw("((B.kotadari_id = $dari_id and B.kotasampai_id=$sampai_id) or (B.kotadari_id = $sampai_id or B.kotasampai_id=$dari_id))");
            // }

            DB::table($temtabel)->insertUsing([
                'id',
                'kotadari_id',
                'kotasampai_id',
                'zonadari_id',
                'zonasampai_id',
                'tarif_id',
                'tarif',
                'kotadari',
                'kotasampai',
                'zonadari',
                'zonasampai',
                'penyesuaian',
                'jarak',
                'statusaktif',
                'container',
                'statuscontainer',
                'omset',
                'nominalsupir',
                'nominalkenek',
                'nominalkomisi',
                'tglmulaiberlaku',
                'modifiedby',
                'created_at',
                'updated_at',
                'kotadarisampai',
            ], $query);

            longtrip:
            if ($longtrip == 65) {
                $getInfoZonaDari = DB::table("kota")->select(DB::raw("isnull(zona_id,0) as zona_id"))->from(DB::raw("kota with (readuncommitted)"))->where('id', $dari_id)->first();
                $getInfoZonaSampai = DB::table("kota")->select(DB::raw("isnull(zona_id,0) as zona_id"))->from(DB::raw("kota with (readuncommitted)"))->where('id', $sampai_id)->first();
                if ($getInfoZonaDari == null || $getInfoZonaSampai == null) {
                    goto getupahkota;
                }
                if ($getInfoZonaDari->zona_id != '0' && $getInfoZonaSampai->zona_id != '0') {
                    $zonaDari_id = $getInfoZonaDari->zona_id;
                    $zonaSampai_id = $getInfoZonaSampai->zona_id;

                    $tempKotaUpah = '##tempKotaUpah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempKotaUpah, function ($table) {
                        $table->bigInteger('id')->nullable();
                        $table->unsignedBigInteger('dari_id')->nullable();
                        $table->unsignedBigInteger('sampai_id')->nullable();
                        $table->unsignedBigInteger('zonadari_id')->nullable();
                        $table->unsignedBigInteger('zonasampai_id')->nullable();
                    });
                    $queryKotaUpah = DB::table("upahsupir")->from(DB::raw("upahsupir as a with (readuncommitted)"))
                        ->select(
                            'a.id',
                            DB::raw("$dari_id as dari_id"),
                            DB::raw("$sampai_id as sampai_id"),
                            DB::raw("$zonaDari_id as zonadari_id"),
                            DB::raw("$zonaSampai_id as zonasampai_id")
                        )
                        // ->whereRaw("isnull(a.statuslangsir,'') != 79")
                        ->whereRaw("((a.zonadari_id = $zonaDari_id and a.zonasampai_id=$zonaSampai_id) or (a.zonadari_id = $zonaSampai_id and a.zonasampai_id=$zonaDari_id))");

                    DB::table($tempKotaUpah)->insertUsing([
                        'id',
                        'dari_id',
                        'sampai_id',
                        'zonadari_id',
                        'zonasampai_id'
                    ], $queryKotaUpah);

                    $query = DB::table("upahsupirrincian")->from(DB::raw("upahsupirrincian as b with (readuncommitted)"))
                        ->select(
                            'a.id',
                            'kotaupah.dari_id as kotadari_id',
                            'kotaupah.sampai_id as kotasampai_id',
                            'zonadari.id as zonadari_id',
                            'zonasampai.id as zonasampai_id',
                            db::raw("0 as tarif_id"),
                            db::raw("'' as tarif"),
                            'dari.kodekota as kotadari',
                            'sampai.kodekota as kotasampai',
                            'zonadari.zona as zonadari',
                            'zonasampai.zona as zonasampai',
                            'a.penyesuaian',
                            'a.jarak',
                            'parameter.memo as statusaktif',
                            'container.kodecontainer as container',
                            'statuscontainer.kodestatuscontainer as statuscontainer',
                            db::raw("0 as omset"),
                            'b.nominalsupir',
                            'b.nominalkenek',
                            'b.nominalkomisi',
                            'a.tglmulaiberlaku',
                            'a.modifiedby',
                            'a.created_at',
                            'a.updated_at',
                            DB::raw("(trim(dari.kodekota)+' - '+trim(sampai.kodekota)) as kotadarisampai"),

                        )
                        ->join(DB::raw("upahsupir as a "), 'a.id', 'b.upahsupir_id')
                        ->join(DB::raw("$tempKotaUpah as kotaupah with (readuncommitted)"), 'a.id', 'kotaupah.id')
                        ->Join(DB::raw($tempkota . " as dari with (readuncommitted)"), db::raw("isnull(kotaupah.dari_id,0)"), 'dari.id')
                        ->Join(DB::raw($tempkota . " as sampai with (readuncommitted)"), db::raw("isnull(kotaupah.sampai_id,0)"), 'sampai.id')
                        ->Join(DB::raw($tempzona . " as zonadari with (readuncommitted)"), db::raw("isnull(kotaupah.zonadari_id,0)"), 'zonadari.id')
                        ->Join(DB::raw($tempzona . " as zonasampai with (readuncommitted)"), db::raw("isnull(kotaupah.zonasampai_id,0)"), 'zonasampai.id')


                        // ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'kotaupah.dari_id', 'dari.id')
                        // ->leftJoin(DB::raw("zona as zonadari with (readuncommitted)"), 'kotaupah.zonadari_id', 'zonadari.id')
                        // ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'kotaupah.sampai_id', 'sampai.id')
                        // ->leftJoin(DB::raw("zona as zonasampai with (readuncommitted)"), 'kotaupah.zonasampai_id', 'zonasampai.id')
                        ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'a.statusaktif', '=', 'parameter.id')
                        ->Join(DB::raw($tempcontainer . " as container with (readuncommitted)"), db::raw("isnull(b.container_id,0)"), 'container.id')
                        ->Join(DB::raw($tempstatuscontainer . " as statuscontainer with (readuncommitted)"), db::raw("isnull(b.statuscontainer_id,0)"), 'statuscontainer.id')

                        // ->leftJoin(DB::raw("container with (readuncommitted)"), 'b.container_id', 'container.id')
                        // ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'b.statuscontainer_id', 'statuscontainer.id')
                        ->where('b.nominalsupir', '!=', 0);

                    if (($aktif == 'AKTIF')) {
                        $statusaktif = Parameter::from(
                            DB::raw("parameter with (readuncommitted)")
                        )
                            ->where('grp', '=', 'STATUS AKTIF')
                            ->where('text', '=', 'AKTIF')
                            ->first();

                        $query->where('a.statusaktif', '=', $statusaktif->id);
                    }
                    if ($container_id > 0) {
                        $query->where('b.container_id', '=', $container_id);
                    }
                    if ($statuscontainer_id > 0) {
                        $query->where('b.statuscontainer_id', '=', $statuscontainer_id);
                    }
                    $query->whereRaw("((a.zonadari_id = $zonaDari_id and a.zonasampai_id=$zonaSampai_id) or (a.zonadari_id = $zonaSampai_id and a.zonasampai_id=$zonaDari_id))");
                } else {
                    getupahkota:
                    $tempKotaUpah = '##tempKotaUpah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempKotaUpah, function ($table) {
                        $table->bigInteger('id')->nullable();
                        $table->unsignedBigInteger('dari_id')->nullable();
                        $table->unsignedBigInteger('sampai_id')->nullable();
                    });
                    $queryKotaUpah = DB::table("upahsupir")->from(DB::raw("upahsupir as a with (readuncommitted)"))
                        ->select(
                            'a.id',
                            DB::raw("$dari_id as dari_id"),
                            DB::raw("$sampai_id as sampai_id")
                        )
                        // ->whereRaw("isnull(a.statuslangsir,'') != 79")
                        ->whereRaw("((a.kotadari_id = $dari_id and a.kotasampai_id=$sampai_id) or (a.kotadari_id = $sampai_id and a.kotasampai_id=$dari_id))");
                    DB::table($tempKotaUpah)->insertUsing([
                        'id',
                        'dari_id',
                        'sampai_id',
                    ], $queryKotaUpah);

                    $query = DB::table("upahsupirrincian")->from(DB::raw("upahsupirrincian as b with (readuncommitted)"))
                        ->select(
                            'a.id',
                            'kotaupah.dari_id as kotadari_id',
                            'kotaupah.sampai_id as kotasampai_id',
                            'zonadari.id as zonadari_id',
                            'zonasampai.id as zonasampai_id',
                            db::raw("0 as tarif_id"),
                            db::raw("'' as tarif"),
                            'dari.kodekota as kotadari',
                            'sampai.kodekota as kotasampai',
                            'dari.kodekota as kotadari',
                            'sampai.kodekota as kotasampai',
                            'a.penyesuaian',
                            'a.jarak',
                            'parameter.memo as statusaktif',
                            'container.kodecontainer as container',
                            'statuscontainer.kodestatuscontainer as statuscontainer',
                            db::raw("0 as omset"),
                            'b.nominalsupir',
                            'b.nominalkenek',
                            'b.nominalkomisi',
                            'a.tglmulaiberlaku',
                            'a.modifiedby',
                            'a.created_at',
                            'a.updated_at',
                            // DB::raw("(trim(dari.kodekota)+' - '+trim(sampai.kodekota)) as kotadarisampai"),
                            DB::raw("(b.tarif + ' (' + trim(b.kotadari)+' - '+trim(b.kotasampai) + 
                        (case when isnull(b.penyesuaian,'') != '' then ') ' + b.penyesuaian else + ')' end)) as kotadarisampai")

                        )
                        ->join(DB::raw("upahsupir as a "), 'a.id', 'b.upahsupir_id')
                        ->join(DB::raw("$tempKotaUpah as kotaupah with (readuncommitted)"), 'a.id', 'kotaupah.id')
                        ->Join(DB::raw($tempkota . " as dari with (readuncommitted)"), db::raw("isnull(kotaupah.dari_id,0)"), 'dari.id')
                        ->Join(DB::raw($tempkota . " as sampai with (readuncommitted)"), db::raw("isnull(kotaupah.sampai_id,0)"), 'sampai.id')
                        ->Join(DB::raw($tempzona . " as zonadari with (readuncommitted)"), db::raw("isnull(kotaupah.zonadari_id,0)"), 'zonadari.id')
                        ->Join(DB::raw($tempzona . " as zonasampai with (readuncommitted)"), db::raw("isnull(kotaupah.zonasampai_id,0)"), 'zonasampai.id')

                        // ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'kotaupah.dari_id', 'dari.id')
                        // ->leftJoin(DB::raw("zona as zonadari with (readuncommitted)"), 'dari.zona_id', 'zonadari.id')
                        // ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'kotaupah.sampai_id', 'sampai.id')
                        // ->leftJoin(DB::raw("zona as zonasampai with (readuncommitted)"), 'sampai.zona_id', 'zonasampai.id')
                        // ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'a.statusaktif', '=', 'parameter.id')
                        ->Join(DB::raw($tempcontainer . " as container with (readuncommitted)"), db::raw("isnull(b.container_id,0)"), 'container.id')
                        ->Join(DB::raw($tempstatuscontainer . " as statuscontainer with (readuncommitted)"), db::raw("isnull(b.statuscontainer_id,0)"), 'statuscontainer.id')

                        // ->leftJoin(DB::raw("container with (readuncommitted)"), 'b.container_id', 'container.id')
                        // ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'b.statuscontainer_id', 'statuscontainer.id')
                        ->where('b.nominalsupir', '!=', 0);

                    if (($aktif == 'AKTIF')) {
                        $statusaktif = Parameter::from(
                            DB::raw("parameter with (readuncommitted)")
                        )
                            ->where('grp', '=', 'STATUS AKTIF')
                            ->where('text', '=', 'AKTIF')
                            ->first();

                        $query->where('a.statusaktif', '=', $statusaktif->id);
                    }
                    if ($container_id > 0) {
                        $query->where('b.container_id', '=', $container_id);
                    }
                    if ($statuscontainer_id > 0) {
                        $query->where('b.statuscontainer_id', '=', $statuscontainer_id);
                    }
                    $query->whereRaw("((a.kotadari_id = $dari_id and a.kotasampai_id=$sampai_id) or (a.kotadari_id = $sampai_id and a.kotasampai_id=$dari_id))");
                }

                DB::table($temtabel)->insertUsing([
                    'id',
                    'kotadari_id',
                    'kotasampai_id',
                    'zonadari_id',
                    'zonasampai_id',
                    'tarif_id',
                    'tarif',
                    'kotadari',
                    'kotasampai',
                    'zonadari',
                    'zonasampai',
                    'penyesuaian',
                    'jarak',
                    'statusaktif',
                    'container',
                    'statuscontainer',
                    'omset',
                    'nominalsupir',
                    'nominalkenek',
                    'nominalkomisi',
                    'tglmulaiberlaku',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                    'kotadarisampai',
                ], $query);
            }
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

        selesai:

        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " a with (readuncommitted)")
        )
            ->select(
                DB::raw("row_number() Over(Order By a.id) as id"),
                'a.id as upah_id',
                'a.kotadari_id',
                'a.kotasampai_id',
                'a.zonadari_id',
                'a.zonasampai_id',
                'a.tarif_id',
                'a.tarif',
                'a.kotadari',
                'a.kotasampai',
                'a.zonadari',
                'a.zonasampai',
                'a.penyesuaian',
                'a.jarak',
                'a.statusaktif',
                'a.container',
                'a.statuscontainer',
                'a.omset',
                'a.nominalsupir',
                'a.nominalkenek',
                'a.nominalkomisi',
                'a.tglmulaiberlaku',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.kotadarisampai',
            );

        if ($longtrip == 66 && $nobukti_tripasal == '') {

            if ($statusPenyesuaian == 662) {
                $query->whereRaw("isnull(a.penyesuaian,'') != ''");
            } else {
                $query->whereRaw("isnull(a.penyesuaian,'') = ''");
            }
        }

        $this->sort($query);

        $this->filter($query);



        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function upahStatusLangsir()
    {
        $proses = request()->proses ?? 'reload';
        $tglbukti = date('Y-m-d', strtotime(request()->tglbukti)) ?? '1900-01-01';
        $user = auth('api')->user()->name;
        $class = 'UpahSupirRincianController';
        $dari_id = request()->dari_id ?? 0;
        $sampai_id = request()->sampai_id ?? 0;

        $aktif = request()->aktif ?? '';
        $container_id = request()->container_id ?? 0;
        $statuscontainer_id = request()->statuscontainer_id ?? 0;

        $temp = '##tempUpah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->integer('kotadari_id')->length(11)->nullable();
            $table->integer('kotasampai_id')->length(11)->nullable();
            $table->string('kotadari')->nullable();
            $table->string('kotasampai')->nullable();
            $table->integer('zonadari_id')->length(11)->nullable();
            $table->integer('zonasampai_id')->length(11)->nullable();
            $table->string('zonadari')->nullable();
            $table->string('zonasampai')->nullable();
            $table->integer('tarif_id')->nullable();
            $table->string('tarif')->nullable();
            $table->string('penyesuaian')->nullable();
            $table->double('jarak', 15, 2)->nullable();
            $table->double('omset', 15, 2)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
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
                $table->longtext('kotadari_id')->nullable();
                $table->longtext('kotasampai_id')->nullable();
                $table->longtext('zonadari_id')->nullable();
                $table->longtext('zonasampai_id')->nullable();
                $table->longtext('tarif_id')->nullable();
                $table->longtext('tarif')->nullable();
                $table->longtext('kotadari')->nullable();
                $table->longtext('kotasampai')->nullable();
                $table->longtext('zonadari')->nullable();
                $table->longtext('zonasampai')->nullable();
                $table->longtext('penyesuaian')->nullable();
                $table->double('jarak', 15, 2)->nullable();
                $table->longtext('statusaktif')->nullable();
                $table->longtext('container')->nullable();
                $table->longtext('statuscontainer')->nullable();
                $table->double('omset', 15, 2)->nullable();
                $table->double('nominalsupir', 15, 2)->nullable();
                $table->double('nominalkenek', 15, 2)->nullable();
                $table->double('nominalkomisi', 15, 2)->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->longtext('modifiedby')->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
                $table->string('kotadarisampai')->nullable();
            });


            $temptarif = '##temptarif' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temptarif, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('upahsupir_id')->nullable();
                $table->string('tujuan', 200)->nullable();
                $table->string('penyesuaian', 200)->nullable();
                $table->integer('statusaktif')->nullable();
                $table->integer('statussistemton')->nullable();
                $table->unsignedBigInteger('kota_id')->nullable();
                $table->unsignedBigInteger('zona_id')->nullable();
                $table->unsignedBigInteger('jenisorder_id')->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->integer('statuspenyesuaianharga')->nullable();
                $table->integer('statuspostingtnl')->nullable();
                $table->longtext('keterangan')->nullable();
                $table->longtext('info')->nullable();
                $table->string('modifiedby', 50)->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
                $table->integer('tas_id')->nullable();
            });



            $querytarif = db::table('tarif')->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.parent_id',
                    'a.upahsupir_id',
                    'a.tujuan',
                    'a.penyesuaian',
                    'a.statusaktif',
                    'a.statussistemton',
                    'a.kota_id',
                    'a.zona_id',
                    'a.jenisorder_id',
                    'a.tglmulaiberlaku',
                    'a.statuspenyesuaianharga',
                    'a.statuspostingtnl',
                    'a.keterangan',
                    'a.info',
                    'a.modifiedby',
                    'a.created_at',
                    'a.updated_at',
                    'a.tas_id',
                )
                ->whereRaw("cast('" . $tglbukti . "' as datetime)>=a.tglmulaiberlaku")
                ->where('a.statusaktif', 1)
                ->where('a.statuslangsir', 79)
                ->orderby('a.id', 'asc');

            DB::table($temptarif)->insertUsing([
                'id',
                'parent_id',
                'upahsupir_id',
                'tujuan',
                'penyesuaian',
                'statusaktif',
                'statussistemton',
                'kota_id',
                'zona_id',
                'jenisorder_id',
                'tglmulaiberlaku',
                'statuspenyesuaianharga',
                'statuspostingtnl',
                'keterangan',
                'info',
                'modifiedby',
                'created_at',
                'updated_at',
                'tas_id',
            ],  $querytarif);


            $tempupahsupir = '##tempupahsupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempupahsupir, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('tarif_id')->nullable();
                $table->unsignedBigInteger('kotadari_id')->nullable();
                $table->unsignedBigInteger('kotasampai_id')->nullable();
                $table->unsignedBigInteger('zonadari_id')->nullable();
                $table->unsignedBigInteger('zonasampai_id')->nullable();
                $table->string('penyesuaian', 200)->nullable();
                $table->double('jarak', 15, 2)->nullable();
                $table->double('jarakfullempty', 15, 2)->nullable();
                $table->unsignedBigInteger('zona_id')->nullable();
                $table->integer('statusaktif')->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->integer('statusluarkota')->nullable();
                $table->integer('statusupahzona')->nullable();
                $table->integer('statussimpankandang')->nullable();
                $table->integer('statuspostingtnl')->nullable();
                $table->longtext('keterangan')->nullable();
                $table->longtext('gambar')->nullable();
                $table->longtext('info')->nullable();
                $table->string('modifiedby', 50)->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
                $table->string('editing_by', 50)->nullable();
                $table->datetime('editing_at')->nullable();
                $table->integer('tas_id')->nullable();
            });

            $queryupahsupir = db::table('upahsupir')->from(db::raw("upahsupir a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.parent_id',
                    'a.tarif_id',
                    'a.kotadari_id',
                    'a.kotasampai_id',
                    'a.zonadari_id',
                    'a.zonasampai_id',
                    'a.penyesuaian',
                    'a.jarak',
                    'a.jarakfullempty',
                    'a.zona_id',
                    'a.statusaktif',
                    'a.tglmulaiberlaku',
                    'a.statusluarkota',
                    'a.statusupahzona',
                    'a.statussimpankandang',
                    'a.statuspostingtnl',
                    'a.keterangan',
                    'a.gambar',
                    'a.info',
                    'a.modifiedby',
                    'a.created_at',
                    'a.updated_at',
                    'a.editing_by',
                    'a.editing_at',
                    'a.tas_id',
                )
                ->whereRaw("cast('" . $tglbukti . "' as datetime)>=a.tglmulaiberlaku")
                ->where('a.statuslangsir', 79)
                ->whereRaw("((a.kotadari_id = $dari_id and a.kotasampai_id=$sampai_id) or (a.kotadari_id = $sampai_id and a.kotasampai_id=$dari_id))")
                ->orderby('a.id', 'asc');

            DB::table($tempupahsupir)->insertUsing([
                'id',
                'parent_id',
                'tarif_id',
                'kotadari_id',
                'kotasampai_id',
                'zonadari_id',
                'zonasampai_id',
                'penyesuaian',
                'jarak',
                'jarakfullempty',
                'zona_id',
                'statusaktif',
                'tglmulaiberlaku',
                'statusluarkota',
                'statusupahzona',
                'statussimpankandang',
                'statuspostingtnl',
                'keterangan',
                'gambar',
                'info',
                'modifiedby',
                'created_at',
                'updated_at',
                'editing_by',
                'editing_at',
                'tas_id',
            ],  $queryupahsupir);

            $tempKotaUpah = '##tempKotaUpah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempKotaUpah, function ($table) {
                $table->bigInteger('id')->nullable();
                $table->unsignedBigInteger('dari_id')->nullable();
                $table->unsignedBigInteger('sampai_id')->nullable();
            });
            $queryKotaUpah = DB::table("upahsupir")->from(DB::raw("upahsupir as a with (readuncommitted)"))
                ->select(
                    'a.id',
                    DB::raw("$dari_id as dari_id"),
                    DB::raw("$sampai_id as sampai_id")
                )->whereRaw("((a.kotadari_id = $dari_id and a.kotasampai_id=$sampai_id) or (a.kotadari_id = $sampai_id and a.kotasampai_id=$dari_id))")
                ->where('a.statuslangsir', 79);
            DB::table($tempKotaUpah)->insertUsing([
                'id',
                'dari_id',
                'sampai_id',
            ], $queryKotaUpah);

            $getKota = DB::table($tempupahsupir)->from(DB::raw($tempupahsupir . " as upahsupir"))
                ->select(
                    'upahsupir.id',
                    'kotaupah.dari_id as kotadari_id',
                    'kotaupah.sampai_id as kotasampai_id',
                    'dari.kodekota as kotadari',
                    'sampai.kodekota as kotasampai',
                    'zonadari.id as zonadari_id',
                    'zonasampai.id as zonasampai_id',
                    'zonadari.keterangan as zonadari',
                    'zonasampai.keterangan as zonasampai',
                    db::raw("isnull(tarif.id,0) as tarif_id"),
                    db::raw("isnull(tarif.tujuan,'') as tarif"),
                    'upahsupir.penyesuaian',
                    'upahsupir.jarak',
                    DB::raw("0 as omset"),
                    'upahsupir.statusaktif',
                    'upahsupir.tglmulaiberlaku',
                    'upahsupir.modifiedby',
                    'upahsupir.created_at',
                    'upahsupir.updated_at'
                )
                ->join(DB::raw("$tempKotaUpah as kotaupah with (readuncommitted)"), 'upahsupir.id', 'kotaupah.id')
                ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'kotaupah.dari_id', 'dari.id')
                ->leftJoin(DB::raw("zona as zonadari with (readuncommitted)"), 'dari.zona_id', 'zonadari.id')
                ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'kotaupah.sampai_id', 'sampai.id')
                ->leftJoin(DB::raw("zona as zonasampai with (readuncommitted)"), 'sampai.zona_id', 'zonasampai.id')
                ->leftJoin(DB::raw($temptarif . " as tarif "), 'upahsupir.tarif_id', 'tarif.id')
                ->whereRaw("((upahsupir.kotadari_id = $dari_id and upahsupir.kotasampai_id=$sampai_id) or (upahsupir.kotadari_id = $sampai_id and upahsupir.kotasampai_id=$dari_id))");

            DB::table($temp)->insertUsing(['id', 'kotadari_id', 'kotasampai_id', 'kotadari', 'kotasampai', 'zonadari_id', 'zonasampai_id', 'zonadari', 'zonasampai', 'tarif_id', 'tarif', 'penyesuaian', 'jarak', 'omset', 'statusaktif', 'tglmulaiberlaku', 'modifiedby', 'created_at', 'updated_at'], $getKota);
            $temptarif = '##tempTarif' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temptarif, function ($table) {
                $table->bigInteger('id')->nullable();
                $table->integer('kotadari_id')->length(11)->nullable();
                $table->integer('kotasampai_id')->length(11)->nullable();
                $table->string('kotadari')->nullable();
                $table->string('kotasampai')->nullable();
                $table->integer('zonadari_id')->length(11)->nullable();
                $table->integer('zonasampai_id')->length(11)->nullable();
                $table->string('zonadari')->nullable();
                $table->string('zonasampai')->nullable();
                $table->integer('tarif_id')->nullable();
                $table->string('tarif')->nullable();
                $table->string('penyesuaian')->nullable();
                $table->double('jarak', 15, 2)->nullable();
                $table->integer('statusaktif')->length(11)->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->string('modifiedby', 50)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->double('omset', 15, 2)->nullable();
            });

            $query = DB::table("tarifrincian")->from(DB::raw("tarifrincian with (readuncommitted)"))
                ->select(
                    'B.id',
                    'B.kotadari_id',
                    'B.kotasampai_id',
                    'B.kotadari',
                    'B.kotasampai',
                    'B.zonadari_id',
                    'B.zonasampai_id',
                    'B.zonadari',
                    'B.zonasampai',
                    'B.tarif_id',
                    'B.tarif',
                    'B.penyesuaian',
                    'B.jarak',
                    'B.statusaktif',
                    'B.tglmulaiberlaku',
                    'B.modifiedby',
                    'B.created_at',
                    'B.updated_at',
                    DB::raw("tarifrincian.nominal as omset")
                )
                ->join(DB::raw("$temp as B with (readuncommitted)"), 'B.tarif_id', 'tarifrincian.tarif_id')
                ->where('tarifrincian.container_id', $container_id)
                ->where('tarifrincian.nominal', '!=', 0);

            DB::table($temptarif)->insertUsing([
                'id',
                'kotadari_id',
                'kotasampai_id',
                'kotadari',
                'kotasampai',
                'zonadari_id',
                'zonasampai_id',
                'zonadari',
                'zonasampai',
                'tarif_id',
                'tarif',
                'penyesuaian',
                'jarak',
                'statusaktif',
                'tglmulaiberlaku',
                'modifiedby',
                'created_at',
                'updated_at',
                'omset',
            ], $query);

            $query = DB::table("upahsupirrincian")->from(DB::raw("upahsupirrincian with (readuncommitted)"))
                ->select(
                    'B.id',
                    'B.kotadari_id',
                    'B.kotasampai_id',
                    'B.zonadari_id',
                    'B.zonasampai_id',
                    'B.tarif_id',
                    'B.tarif',
                    'B.kotadari',
                    'B.kotasampai',
                    'B.zonadari',
                    'B.zonasampai',
                    'B.penyesuaian',
                    'B.jarak',
                    'parameter.memo as statusaktif',
                    'container.kodecontainer as container',
                    'statuscontainer.kodestatuscontainer as statuscontainer',
                    db::raw("isnull(B.omset,0) as omset"),
                    // db::raw("(upahsupirrincian.nominalsupir-
                    // (case when " . $statuskandang_id . "=" . $idstatuskandang . " then " . $nominalsupirkandang . " else  0 end))
                    // as nominalsupir"),
                    // db::raw("(upahsupirrincian.nominalkenek-
                    // (case when " . $statuskandang_id . "=" . $idstatuskandang . " then " . $nominalkenekkandang . " else  0 end))
                    // as nominalkenek"),
                    // db::raw("(upahsupirrincian.nominalkomisi-
                    // (case when " . $statuskandang_id . "=" . $idstatuskandang . " then " . $nominalkomisikandang . " else  0 end))
                    // as nominalkomisi"),

                    'upahsupirrincian.nominalsupir',
                    'upahsupirrincian.nominalkenek',
                    'upahsupirrincian.nominalkomisi',
                    'B.tglmulaiberlaku',
                    'B.modifiedby',
                    'B.created_at',
                    'B.updated_at',
                    DB::raw("(b.tarif + ' (' + trim(b.kotadari)+' - '+trim(b.kotasampai) + 
                        (case when isnull(b.penyesuaian,'') != '' then ') ' + b.penyesuaian else + ')' end)) as kotadarisampai"),

                )
                ->Join(DB::raw($tempupahsupir . " as B1 "), 'B1.id', 'upahsupirrincian.upahsupir_id')
                ->leftJoin(DB::raw("$temptarif as B with (readuncommitted)"), 'B.id', 'upahsupirrincian.upahsupir_id')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'B.statusaktif', '=', 'parameter.id')
                ->leftJoin(DB::raw("container with (readuncommitted)"), 'upahsupirrincian.container_id', 'container.id')
                ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'upahsupirrincian.statuscontainer_id', 'statuscontainer.id')

                ->where('upahsupirrincian.nominalsupir', '!=', 0);
            if (($aktif == 'AKTIF')) {
                $statusaktif = Parameter::from(
                    DB::raw("parameter with (readuncommitted)")
                )
                    ->where('grp', '=', 'STATUS AKTIF')
                    ->where('text', '=', 'AKTIF')
                    ->first();

                $query->where('B.statusaktif', '=', $statusaktif->id);
            }
            if ($container_id > 0) {
                $query->where('upahsupirrincian.container_id', '=', $container_id);
            }
            if ($statuscontainer_id > 0) {
                $query->where('upahsupirrincian.statuscontainer_id', '=', $statuscontainer_id);
            }
            DB::table($temtabel)->insertUsing([
                'id',
                'kotadari_id',
                'kotasampai_id',
                'zonadari_id',
                'zonasampai_id',
                'tarif_id',
                'tarif',
                'kotadari',
                'kotasampai',
                'zonadari',
                'zonasampai',
                'penyesuaian',
                'jarak',
                'statusaktif',
                'container',
                'statuscontainer',
                'omset',
                'nominalsupir',
                'nominalkenek',
                'nominalkomisi',
                'tglmulaiberlaku',
                'modifiedby',
                'created_at',
                'updated_at',
                'kotadarisampai',
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

        return $temtabel;
    }

    public function getValidasiUpahsupir($container_id, $statuscontainer_id, $id)
    {
        $statusaktif = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('text', '=', 'AKTIF')
            ->first();

        $query = UpahSupir::from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'upahsupir.id',
            )
            ->leftJoin(DB::raw("upahsupirrincian with (readuncommitted)"), 'upahsupir.id', '=', 'upahsupirrincian.upahsupir_id')
            ->whereRaw("upahsupir.id in ($id)")
            ->where('upahsupirrincian.container_id', '=', $container_id)
            ->where('upahsupirrincian.statuscontainer_id', '=', $statuscontainer_id)
            ->where('upahsupir.statusaktif', '=', $statusaktif->id);

        $data = $query->first();


        return $data;
    }
    public function getValidasiKota($kota_id, $id)
    {
        $statusaktif = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('text', '=', 'AKTIF')
            ->first();

        $query = UpahSupir::from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'upahsupir.id',
            )
            ->whereRaw("upahsupir.id in ($id)")
            ->where(function ($query) use ($kota_id) {
                $query->whereRaw("upahsupir.kotadari_id = $kota_id")
                    ->orWhereRaw("upahsupir.kotasampai_id = $kota_id");
            })
            ->where('upahsupir.statusaktif', '=', $statusaktif->id);

        $data = $query->first();


        return $data;
    }


    public function setUpRow()
    {
        $query = DB::table('statuscontainer')->select(
            'statuscontainer.kodestatuscontainer as statuscontainer',
            'statuscontainer.id as statuscontainer_id',
            'container.kodecontainer as container',
            'container.id as container_id',
            db::Raw("0 as nominalsupir"),
            db::Raw("0 as nominalkenek"),
            db::Raw("0 as nominalkomisi"),
            db::Raw("0 as nominaltol"),
            db::Raw("0 as liter")
        )
            ->crossJoin('container')
            ->orderBy('container.id', 'asc')
            ->orderBy('statuscontainer.kodestatuscontainer', 'desc');

        return $query->get();
    }
    public function setUpRowExcept($rincian)
    {
        $data = DB::table('statuscontainer')->select(
            'statuscontainer.kodestatuscontainer as statuscontainer',
            'statuscontainer.id as statuscontainer_id',
            'container.kodecontainer as container',
            'container.id as container_id'
        )->crossJoin('container')
            ->orderBy('container.id', 'asc')
            ->orderBy('statuscontainer.kodestatuscontainer', 'desc');

        $temp = '##tempcrossjoin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->increments('id');
            $table->string('statuscontainer')->nullable();
            $table->string('statuscontainerId')->nullable();
            $table->string('container')->nullable();
            $table->string('containerId')->nullable();
        });

        DB::table($temp)->insertUsing([
            "statuscontainer",
            "statuscontainerId",
            "container",
            "containerId"
        ], $data);

        //select yang sudah ada
        $except = DB::table($temp)->select(
            "$temp.id",
        );
        for ($i = 0; $i < count($rincian); $i++) {
            $except->orWhere(function ($query) use ($rincian, $i) {
                $query->where('containerId', $rincian[$i]['container_id'])
                    ->where('statuscontainerId', $rincian[$i]['statuscontainer_id']);
            });
        }

        foreach ($except->get() as $e) {
            $arr[] = $e->id;
        }

        //select semua keluali
        $query = DB::table($temp)->select(
            "$temp.id",
            "$temp.statuscontainer",
            "$temp.statuscontainerId as statuscontainer_id",
            "$temp.container",
            "$temp.containerId as container_id"
        )->whereNotIn('id', $arr);

        // ->whereRaw(" NOT EXIST  ( select $temp.statuscontainer, $temp.container from   [$temp]  WHERE (statuscontainer = 'empty' and container = '20`') or (statuscontainer = 'FULL' and container = '40`') ) ");
        // ->whereRaw("(statuscontainer = 'FULL' and container = '40`')");

        return $query->get();
    }

    public function listpivot()
    {

        $temphasilupah = '##temphasilupah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temphasilupah, function (Blueprint $table) {
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
            $table->longText('statusupahzona')->nullable();
            $table->longText('statuspostingtnl')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('keterangan')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->longText('judulLaporan')->nullable();
            $table->longText('judul')->nullable();
        });

        DB::table($temphasilupah)->insertUsing([
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
            'statusupahzona',
            'statuspostingtnl',
            'tglmulaiberlaku',
            'gambar',
            'keterangan',
            'created_at',
            'modifiedby',
            'updated_at',
            'judulLaporan',
            'judul',
        ], (new UpahSupir())->get(1));

        // dd(DB::table($temphasilupah)->get());

        $tempupahsupir = '##tempupahsupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempupahsupir, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->longtext('kotadari')->nullable();
            $table->longtext('kotasampai')->nullable();
            $table->longtext('penyesuaian')->nullable();
        });

        $querytempupahsupir = db::table($temphasilupah)->from(db::raw($temphasilupah . " a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.kotadari_id as kotadari',
                'a.kotasampai_id as kotasampai',
                'a.penyesuaian'
            );

        DB::table($tempupahsupir)->insertUsing([
            'id',
            'kotadari',
            'kotasampai',
            'penyesuaian',
        ], $querytempupahsupir);


        $tempupahsupirrincian = '##tempupahsupirrincian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempupahsupirrincian, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('upahsupir_id')->nullable();
            $table->longtext('container')->nullable();
            $table->longtext('statuscontainer')->nullable();
            $table->double('nominalsupir', 15, 2)->nullable();
            $table->double('nominalkenek', 15, 2)->nullable();
            $table->double('nominalkomisi', 15, 2)->nullable();
            $table->double('nominaltol', 15, 2)->nullable();
            $table->double('liter', 15, 2)->nullable();
        });


        $querytempupahsupirrincian = db::table("upahsupirrincian")->from(db::raw("upahsupirrincian a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.upahsupir_id',
                db::raw("replace(c.kodecontainer,'" . '"' . "','') as kodecontainer"),
                'd.kodestatuscontainer',
                'a.nominalsupir',
                'a.nominalkenek',
                'a.nominalkomisi',
                'a.nominaltol',
                'a.liter',
            )
            ->join(db::raw($tempupahsupir . " b "), 'a.upahsupir_id', 'b.id')
            ->join(db::raw("container c with (readuncommitted)"), 'a.container_id', 'c.id')
            ->join(db::raw("statuscontainer d with (readuncommitted)"), 'a.statuscontainer_id', 'd.id');

        // dd($querytempupahsupirrincian->get());
        DB::table($tempupahsupirrincian)->insertUsing([
            'id',
            'upahsupir_id',
            'container',
            'statuscontainer',
            'nominalsupir',
            'nominalkenek',
            'nominalkomisi',
            'nominaltol',
            'liter',
        ], $querytempupahsupirrincian);

        // dd('test');
        $tempupahsupirrinciandetail = '##tempupahsupirrinciandetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempupahsupirrinciandetail, function ($table) {
            $table->unsignedBigInteger('upahsupir_id')->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('nominalsupir', 15, 2)->nullable();
            $table->double('nominalkenek', 15, 2)->nullable();
            $table->double('nominalkomisi', 15, 2)->nullable();
            $table->double('nominaltol', 15, 2)->nullable();
            $table->double('liter', 15, 2)->nullable();
        });

        $querytempupahsupirrinciandetail = db::table($tempupahsupirrincian)->from(db::raw($tempupahsupirrincian . " a "))
            ->select(
                'a.upahsupir_id',
                db::raw("trim(a.container)+'_'+trim(a.statuscontainer) as keterangan"),
                'a.nominalsupir',
                'a.nominalkenek',
                'a.nominalkomisi',
                'a.nominaltol',
                'a.liter'
            );

        DB::table($tempupahsupirrinciandetail)->insertUsing([
            'upahsupir_id',
            'keterangan',
            'nominalsupir',
            'nominalkenek',
            'nominalkomisi',
            'nominaltol',
            'liter',
        ], $querytempupahsupirrinciandetail);
        // 


        $tempupahsupirrinciandetailsupir = '##tempupahsupirrinciandetailsupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempupahsupirrinciandetailsupir, function ($table) {
            $table->unsignedBigInteger('upahsupir_id')->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('nominalsupir', 15, 2)->nullable();
        });

        $tempupahsupirrinciandetailkenek = '##tempupahsupirrinciandetailkenek' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempupahsupirrinciandetailkenek, function ($table) {
            $table->unsignedBigInteger('upahsupir_id')->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('nominalkenek', 15, 2)->nullable();
        });

        $tempupahsupirrinciandetailkomisi = '##tempupahsupirrinciandetailkomisi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempupahsupirrinciandetailkomisi, function ($table) {
            $table->unsignedBigInteger('upahsupir_id')->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('nominalkomisi', 15, 2)->nullable();
        });

        $tempupahsupirrinciandetailtol = '##tempupahsupirrinciandetailtol' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempupahsupirrinciandetailtol, function ($table) {
            $table->unsignedBigInteger('upahsupir_id')->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('nominaltol', 15, 2)->nullable();
        });

        $tempupahsupirrinciandetailliter = '##tempupahsupirrinciandetailliter' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempupahsupirrinciandetailliter, function ($table) {
            $table->unsignedBigInteger('upahsupir_id')->nullable();
            $table->longtext('keterangan')->nullable();
            $table->double('liter', 15, 2)->nullable();
        });

        $querytempupahsupirrinciandetailsupir = db::table($tempupahsupirrinciandetail)->from(db::raw($tempupahsupirrinciandetail . " a"))
            ->select(
                'a.upahsupir_id',
                'a.keterangan',
                'a.nominalsupir',
            );

        DB::table($tempupahsupirrinciandetailsupir)->insertUsing([
            'upahsupir_id',
            'keterangan',
            'nominalsupir',
        ], $querytempupahsupirrinciandetailsupir);

        $querytempupahsupirrinciandetailkenek = db::table($tempupahsupirrinciandetail)->from(db::raw($tempupahsupirrinciandetail . " a"))
            ->select(
                'a.upahsupir_id',
                'a.keterangan',
                'a.nominalkenek',
            );

        DB::table($tempupahsupirrinciandetailkenek)->insertUsing([
            'upahsupir_id',
            'keterangan',
            'nominalkenek',
        ], $querytempupahsupirrinciandetailkenek);


        $querytempupahsupirrinciandetailkomisi = db::table($tempupahsupirrinciandetail)->from(db::raw($tempupahsupirrinciandetail . " a"))
            ->select(
                'a.upahsupir_id',
                'a.keterangan',
                'a.nominalkomisi',
            );

        DB::table($tempupahsupirrinciandetailkomisi)->insertUsing([
            'upahsupir_id',
            'keterangan',
            'nominalkomisi',
        ], $querytempupahsupirrinciandetailkomisi);


        $querytempupahsupirrinciandetailtol = db::table($tempupahsupirrinciandetail)->from(db::raw($tempupahsupirrinciandetail . " a"))
            ->select(
                'a.upahsupir_id',
                'a.keterangan',
                'a.nominaltol',
            );

        DB::table($tempupahsupirrinciandetailtol)->insertUsing([
            'upahsupir_id',
            'keterangan',
            'nominaltol',
        ], $querytempupahsupirrinciandetailtol);

        $querytempupahsupirrinciandetailliter = db::table($tempupahsupirrinciandetail)->from(db::raw($tempupahsupirrinciandetail . " a"))
            ->select(
                'a.upahsupir_id',
                db::raw("'Liter_'+a.keterangan as keterangan"),
                'a.liter',
            );

        DB::table($tempupahsupirrinciandetailliter)->insertUsing([
            'upahsupir_id',
            'keterangan',
            'liter',
        ], $querytempupahsupirrinciandetailliter);


        $tempketerangan = '##tempketerangan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempketerangan, function ($table) {
            $table->longtext('keterangan')->nullable();
        });

        $querytempketerangan = db::table($tempupahsupirrinciandetail)->from(db::raw($tempupahsupirrinciandetail . " a"))
            ->select(
                'a.keterangan',
            )
            ->groupBy('a.keterangan')
            ->OrderBy('a.keterangan');

        DB::table($tempketerangan)->insertUsing([
            'keterangan',
        ], $querytempketerangan);

        $columnsketerangan = db::table($tempketerangan)->from(db::raw($tempketerangan . " a"))
            ->select(
                db::raw("string_agg('['+cast(a.keterangan as nvarchar(max))+']',',') WITHIN GROUP (ORDER BY a.keterangan ASC) as keterangan")
            )->first()->keterangan ?? '';

        $columnsketeranganliter = db::table($tempketerangan)->from(db::raw($tempketerangan . " a"))
            ->select(
                db::raw("string_agg('['+cast('Liter_'+a.keterangan as nvarchar(max))+']',',') WITHIN GROUP (ORDER BY a.keterangan ASC) as keterangan")
            )->first()->keterangan ?? '';

        $columnstabel = db::table($tempketerangan)->from(db::raw($tempketerangan . " a"))
            ->select(
                db::raw("string_agg('['+cast(a.keterangan as nvarchar(max))+'] money',',') WITHIN GROUP (ORDER BY a.keterangan ASC) as keterangan")
            )->first()->keterangan ?? '';

        $columnstabelliter = db::table($tempketerangan)->from(db::raw($tempketerangan . " a"))
            ->select(
                db::raw("string_agg('['+cast('Liter_'+a.keterangan as nvarchar(max))+'] money',',') WITHIN GROUP (ORDER BY a.keterangan ASC) as keterangan")
            )->first()->keterangan ?? '';


        $user = auth('api')->user()->name;
        $class = 'UpahSupirrealsupirtController';


        $temtabelrealsupir = 'temprealsupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

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
                'namatabel' => $temtabelrealsupir,
                'modifiedby' => $user,
                'created_at' => date('Y/m/d H:i:s'),
                'updated_at' => date('Y/m/d H:i:s'),
            ]
        );

        $user = auth('api')->user()->name;
        $classliter = 'UpahSupirreallitertController';


        $temtabelrealliter = 'temprealliter' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

        $querydata = DB::table('listtemporarytabel')->from(
            DB::raw("listtemporarytabel a with (readuncommitted)")
        )
            ->select(
                'id',
                'class',
                'namatabel',
            )
            ->where('class', '=', $classliter)
            ->where('modifiedby', '=', $user)
            ->first();

        if (isset($querydata)) {
            Schema::dropIfExists($querydata->namatabel);
            DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
        }

        DB::table('listtemporarytabel')->insert(
            [
                'class' => $class,
                'namatabel' => $temtabelrealsupir,
                'modifiedby' => $user,
                'created_at' => date('Y/m/d H:i:s'),
                'updated_at' => date('Y/m/d H:i:s'),
            ]
        );

        // dd($columnsketeranganliter);
        // dd($temtabelrealsupir);

        // dd(db::table($tempupahsupirrinciandetailliter)->first());
        $createtable = 'create table ' . $temtabelrealsupir . '(upahsupir_id integer,' . $columnstabel . ')';
        // dd($createtable);
        DB::update($createtable);
        $createtable = 'create table ' . $temtabelrealliter . '(upahsupir_id integer,' . $columnstabelliter . ') ';
        DB::update($createtable);

        $nominalsupir = 'Nominal Supir';
        $query = "
        insert into " . $temtabelrealsupir . "(upahsupir_id," . $columnsketerangan . ") 
        SELECT  upahsupir_id," . $columnsketerangan . " FROM (SELECT a.upahsupir_id,a.keterangan,a.nominalsupir FROM " . $tempupahsupirrinciandetailsupir . " a ) AS SourceTable PIVOT (Max(nominalsupir) FOR keterangan IN (" . $columnsketerangan . ")) AS PivotTable
        ";

        DB::update($query);



        $liter = 'Liter';
        $query = "
        insert into " . $temtabelrealliter . "(upahsupir_id," . $columnsketeranganliter . ")
        SELECT  upahsupir_id,
        " . $columnsketeranganliter . "
        FROM
        (SELECT a.upahsupir_id,a.keterangan,a.liter
            FROM " . $tempupahsupirrinciandetailliter . " a 
            ) AS SourceTable
        PIVOT
        (
        Max(liter)
        FOR keterangan IN (" . $columnsketeranganliter . ")
        ) AS PivotTable
        ";
        // dd($query);
        DB::update($query);


        // dd(db::table($temtabelrealliter)->get());

        $query = db::table($tempupahsupir)->from(db::raw($tempupahsupir . " a"))
            ->select(
                'a.kotadari as dari',
                'a.kotasampai as tujuan',
                'a.penyesuaian as penyesuaian',
                'd.jarak as jarak',
                db::raw("b.*"),
                db::raw("c.*"),
            )
            ->join(db::raw($temtabelrealsupir . " b"), 'a.id', 'b.upahsupir_id')
            ->join(db::raw($temtabelrealliter . " c"), 'a.id', 'c.upahsupir_id')
            ->join(db::raw("upahsupir d with (readuncommitted)"), 'a.id', 'd.id')
            ->get();

        $querydata = DB::table('listtemporarytabel')->from(
            DB::raw("listtemporarytabel a with (readuncommitted)")
        )
            ->select(
                'id',
                'class',
                'namatabel',
            )
            ->where('class', '=', $classliter)
            ->where('modifiedby', '=', $user)
            ->first();

        if (isset($querydata)) {
            Schema::dropIfExists($querydata->namatabel);
            DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
        }

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

        return $query;
    }
    public function listpivotold()
    {
        $tempdatacs = '##tempdatacontainerstatus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatacs, function ($table) {
            $table->unsignedBigInteger('upah_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->unsignedBigInteger('statuscontainer_id')->nullable();
            $table->string('container', 1000)->nullable();
            $table->string('statuscontainer', 1000)->nullable();
        });

        $queryupah = (new UpahSupir())->get();
        // dd('test');
        $datadetailupah = json_decode($queryupah, true);
        foreach ($datadetailupah as $itema) {

            $querycs1 = DB::table('container')->from(DB::raw("container with (readuncommitted)"))
                ->select(
                    'container.id as container_id',
                    'container.keterangan as container',
                )
                ->orderBy('container.id', 'asc')
                ->get();

            $datadetailcs1 = json_decode($querycs1, true);

            foreach ($datadetailcs1 as $item) {

                $querycs2 = DB::table('statuscontainer')->from(DB::raw("statuscontainer with (readuncommitted)"))
                    ->select(
                        'statuscontainer.id as statuscontainer_id',
                        'statuscontainer.kodestatuscontainer as statuscontainer',
                    )
                    ->orderBy('statuscontainer.kodestatuscontainer', 'desc')
                    ->get();

                $datadetailcs2 = json_decode($querycs2, true);
                foreach ($datadetailcs2 as $item1) {

                    $values = array('upah_id' => $itema['id'], 'container_id' => $item['container_id'], 'statuscontainer_id' => $item1['statuscontainer_id'], 'container' => $item['container'], 'statuscontainer' => $item1['statuscontainer']);
                    DB::table($tempdatacs)->insert($values);
                }
            }
        }


        // dd(DB::table($tempdatacs)->get());


        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->unsignedBigInteger('statuscontainer_id')->nullable();
            $table->string('container', 1000)->nullable();
            $table->string('statuscontainer', 1000)->nullable();
            $table->string('containerstatuscontainer', 1000)->nullable();
            $table->string('containerstatuscontainerliter', 1000)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('liter', 10, 2)->nullable();
        });

        $query = DB::table($tempdatacs)->from(DB::raw($tempdatacs . " as a"))
            ->select(
                'a.upah_id as id',
                'a.container_id as container_id',
                'a.container as container',
                'a.statuscontainer_id as statuscontainer_id',
                'a.statuscontainer as statuscontainer',
                DB::raw("ltrim(rtrim(a.container))+'_'+ltrim(rtrim(a.statuscontainer)) as containerstatuscontainer"),
                DB::raw("'liter'+trim(rtrim(a.container))+'_'+ltrim(rtrim(a.statuscontainer)) as containerstatuscontainerliter"),
                DB::raw("isnull(b.nominalsupir,0) as nominal"),
                DB::raw("isnull(b.liter,0) as liter"),
            )
            ->leftJoin(DB::raw("upahsupirrincian b with (readuncommitted)"), function ($join) {
                $join->on('a.container_id', '=', 'b.container_id');
                $join->on('a.statuscontainer_id', '=', 'b.statuscontainer_id');
                $join->on('a.upah_id', '=', 'b.upahsupir_id');
            })
            ->leftJoin(DB::raw("upahsupir with (readuncommitted)"), 'upahsupir.id', '=', 'b.upahsupir_id')
            ->orderBy('a.upah_id', 'asc')
            ->orderBy('a.statuscontainer', 'desc')
            ->orderBy('a.container', 'asc');
        // ->whereRaw("upahsupir.tglmulaiberlaku >= '$dari'")
        // ->whereRaw("upahsupir.tglmulaiberlaku <= '$sampai'");


        DB::table($tempdata)->insertUsing([
            'id',
            'container_id',
            'container',
            'statuscontainer_id',
            'statuscontainer',
            'containerstatuscontainer',
            'containerstatuscontainerliter',
            'nominal',
            'liter',
        ], $query);

        $id = DB::table($tempdata)->first();



        if ($id == null) {
            return null;
        } else {

            $tempupah = '##tempupah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempupah, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->string('dari')->nullable();
                $table->string('tujuan')->nullable();
                $table->string('penyesuaian')->nullable();
                $table->unsignedBigInteger('jarak')->nullable();
                $table->date('tglmulaiberlaku')->nullable();
            });

            $querytempupah = DB::table('upahsupir')->from(DB::raw("upahsupir with (readuncommitted)"))
                ->select(
                    'upahsupir.id as id',
                    'dari.keterangan as dari',
                    'kota.keterangan as tujuan',
                    'upahsupir.penyesuaian',
                    'upahsupir.jarak',
                    'upahsupir.tglmulaiberlaku'
                )
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'upahsupir.kotasampai_id', '=', 'kota.id')
                ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'upahsupir.kotadari_id', '=', 'dari.id');

            DB::table($tempupah)->insertUsing([
                'id',
                'dari',
                'tujuan',
                'penyesuaian',
                'jarak',
                'tglmulaiberlaku',
            ], $querytempupah);

            $tempdatagroup = '##tempdatagroup' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempdatagroup, function ($table) {
                $table->unsignedBigInteger('container_id')->nullable();
                $table->string('container', 200)->nullable();
                $table->unsignedBigInteger('statuscontainer_id')->nullable();
                $table->string('statuscontainer', 200)->nullable();
            });

            $querydatagroup =  DB::table($tempdata)->from(
                DB::raw($tempdata)
            )
                ->select(
                    'container_id',
                    'container',
                    'statuscontainer_id',
                    'statuscontainer',
                )
                ->groupBy('container_id')
                ->groupBy('container')
                ->groupBy('statuscontainer_id')
                ->groupBy('statuscontainer');



            DB::table($tempdatagroup)->insertUsing([
                'container_id',
                'container',
                'statuscontainer_id',
                'statuscontainer',
            ], $querydatagroup);


            $queryloop = DB::table($tempdatagroup)->from(
                DB::raw($tempdatagroup . " a with (readuncommitted)")
            )
                ->select(
                    'a.container as container',
                    'a.statuscontainer as statuscontainer',
                )
                ->orderBy('container_id', 'asc')
                ->orderBy('statuscontainer', 'desc')
                ->get();



            // 
            $columnid = '';
            $columnliterid = '';
            $a = 0;
            $datadetail = json_decode($queryloop, true);

            foreach ($datadetail as $item) {
                if ($a == 0) {
                    $columnid = $columnid . '[' . $item['container'] . '_' . $item['statuscontainer'] . ']';
                    $columnliterid = $columnliterid . '[liter' . $item['container'] . '_' . $item['statuscontainer'] . ']';
                } else {
                    $columnid = $columnid . ',[' . $item['container'] . '_' . $item['statuscontainer'] . ']';
                    $columnliterid = $columnliterid . ',[liter' . $item['container'] . '_' . $item['statuscontainer'] . ']';
                }

                $a = $a + 1;
            }
            // dd($columnid);

            $statement = ' select b.dari,b.tujuan,b.penyesuaian,b.jarak,A.* from (select id,' . $columnid . ' from 
                (select A.id,A.containerstatuscontainer,A.nominal
                    from ' . $tempdata . ' A) as SourceTable
            
                Pivot (
                    max(nominal)
                    for containerstatuscontainer in (' . $columnid . ')
                    ) as PivotTable)A
                inner join ' . $tempupah . ' b with (readuncommitted) on A.id=B.id
            ';

            $statement2 = 'select b.tujuan,A.* from (select id,' . $columnliterid . ' from 
                (select A.id,A.containerstatuscontainerliter,A.liter
                    from ' . $tempdata . ' A) as SourceTable
            
                Pivot (
                    max(liter)
                    for containerstatuscontainerliter in (' . $columnliterid . ')
                    ) as PivotTable)A
                inner join ' . $tempupah . ' b with (readuncommitted) on A.id=B.id
            ';

            $data1 = DB::select(DB::raw($statement));
            // dd($data1);
            $data2 = DB::select(DB::raw($statement2));
            $merger = [];
            foreach ($data1 as $key => $value) {
                $datas2 = json_decode(json_encode($data2[$key]), true);
                $datas1 = json_decode(json_encode($data1[$key]), true);
                $merger[] = array_merge($datas1, $datas2);
            }

            // dd('test');

            return $merger;
        }
    }


    public function sort($query)
    {
        // if ($this->params['sortIndex'] == 'penyesuaian' || $this->params['sortIndex'] == 'jarak' || $this->params['sortIndex'] == 'tglmulaiberlaku' || $this->params['sortIndex'] == 'modifiedby' || $this->params['sortIndex'] == 'created_at' || $this->params['sortIndex'] == 'updated_at' || $this->params['sortIndex'] == 'statusaktif' || $this->params['sortIndex'] == 'kotadari' || $this->params['sortIndex'] == 'kotasampai') {
        //     return $query->orderBy('B.' . $this->params['sortIndex'], $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'container') {
        //     return $query->orderBy('container.kodecontainer', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'statuscontainer') {
        //     return $query->orderBy('statuscontainer.kodestatuscontainer', $this->params['sortOrder']);
        // } else {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        // }
    }

    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {

            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {

                        // if ($filters['field'] == 'statusaktif') {
                        //     $query = $query->where('parameter.text', '=', "$filters[data]");
                        // } elseif ($filters['field'] == 'container') {
                        //     $query = $query->where('container.kodecontainer', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'statuscontainer') {
                        //     $query = $query->where('statuscontainer.kodestatuscontainer', 'LIKE', "%$filters[data]%");
                        // } elseif ($filters['field'] == 'tglmulaiberlaku') {
                        //     $query = $query->WhereRaw("format(B.tglmulaiberlaku,'dd-MM-yyyy') like '%$filters[data]%'");
                        // } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                        //     $query = $query->whereRaw("format(B." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        // } else 
                        if ($filters['field'] == 'nominalsupir' || $filters['field'] == 'nominalkenek' || $filters['field'] == 'nominalkomisi' || $filters['field'] == 'omset') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'check') {
                            $query = $query->whereRaw('1 = 1');
                        } else {
                            $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            // if ($filters['field'] == 'statusaktif') {
                            //     $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            // } elseif ($filters['field'] == 'container') {
                            //     $query = $query->orWhere('container.kodecontainer', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'statuscontainer') {
                            //     $query = $query->orWhere('statuscontainer.kodestatuscontainer', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'tglmulaiberlaku') {
                            //     $query = $query->orWhereRaw("format(B.tglmulaiberlaku,'dd-MM-yyyy') like '%$filters[data]%'");
                            // } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            //     $query = $query->orWhereRaw("format(B." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            // } else 
                            if ($filters['field'] == 'nominalsupir' || $filters['field'] == 'nominalkenek' || $filters['field'] == 'nominalkomisi' || $filters['field'] == 'omset') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'check') {
                                $query = $query->whereRaw('1 = 1');
                            } else {
                                $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    // public function processStore(UpahSupir $upahsupir, array $data): UpahSupirRincian
    public function processStore(array $data, UpahSupirRincian $upahSupirRincian): UpahSupirRincian
    {
        // $upahSupirRincian = new UpahSupirRincian();
        $upahSupirRincian->upahsupir_id = $data['upahsupir_id'];
        $upahSupirRincian->container_id = $data['container_id'];
        $upahSupirRincian->statuscontainer_id = $data['statuscontainer_id'];
        $upahSupirRincian->nominalsupir = $data['nominalsupir'];
        $upahSupirRincian->nominalkenek = $data['nominalkenek'];
        $upahSupirRincian->nominalkomisi = $data['nominalkomisi'];
        $upahSupirRincian->nominaltol = $data['nominaltol'];
        $upahSupirRincian->liter = $data['liter'];
        $upahSupirRincian->modifiedby = auth('api')->user()->name;
        $upahSupirRincian->info = html_entity_decode(request()->info);
        $upahSupirRincian->tas_id = $data['tas_id'];

        if (!$upahSupirRincian->save()) {
            throw new \Exception("Error storing upah supir in detail.");
        }

        return $upahSupirRincian;
    }

    public function getExistNominalUpahSupir($container_id, $statuscontainer_id, $id)
    {
        $statusaktif = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('text', '=', 'AKTIF')
            ->first();


        $query = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'upahsupirrincian.nominalsupir'
            )
            ->join(DB::raw("upahsupirrincian with (readuncommitted)"), 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
            ->whereRaw("upahsupir.id in ($id)")
            ->where('upahsupirrincian.container_id', '=', $container_id)
            ->where('upahsupirrincian.statuscontainer_id', '=', $statuscontainer_id);
        $cekNominal = $query->first();
        if ($cekNominal != '') {
            if ($cekNominal->nominalsupir == null || $cekNominal->nominalsupir == 0) {
                return [
                    'status' => false,
                    'error' => 'gaji supir kosong'
                ];
            }
            $query->where('upahsupir.statusaktif', '=', $statusaktif->id);
            $cekstatus = $query->first();
            if ($cekstatus == '') {
                return [
                    'status' => false,
                    'error' => 'status non aktif'
                ];
            }
        } else {
            return [
                'status' => false,
                'error' => 'tarif tidak ada'
            ];
        }

        return [
            'status' => true
        ];
    }


    public function cekupdateharga($data)
    {
        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->string('kotadari', 1000)->nullable();
            $table->string('kotasampai', 1000)->nullable();
            $table->string('penyesuaian', 1000)->nullable();
            $table->integer('jarak')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
        });

        $tglsaldo = (new Parameter())->cekText('SALDO', 'SALDO') ?? '1900-01-01';
        foreach ($data as $item) {
            $values = array(
                'kotadari' => $item['kotadari'],
                'kotasampai' => $item['kotasampai'],
                'penyesuaian' => $item['penyesuaian'],
                'jarak' => $item['jarak'],
                'tglmulaiberlaku' => $tglsaldo,
            );
            DB::table($tempdata)->insert($values);
        }

        $temptgl = '##temptgl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptgl, function ($table) {
            $table->string('kotadari', 1000)->nullable();
            $table->string('kotasampai', 1000)->nullable();
            $table->string('penyesuaian', 1000)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
        });

        $querytgl = DB::table('upahsupir')
            ->from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'kotadari.keterangan as kotadari',
                'kotasampai.keterangan as kotasampai',
                db::raw("isnull(upahsupir.penyesuaian,'') as penyesuaian"),
                'tglmulaiberlaku'
            )
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id');

        DB::table($temptgl)->insertUsing(['kotadari', 'kotasampai', 'penyesuaian', 'tglmulaiberlaku'], $querytgl);


        $query = DB::table($tempdata)
            ->from(DB::raw($tempdata . " as a"))
            ->join(DB::raw($temptgl . " as b"), 'a.tglmulaiberlaku', 'b.tglmulaiberlaku')
            ->whereRaw("trim(a.kotadari) = trim(b.kotadari)")
            ->whereRaw("trim(a.kotasampai) = trim(b.kotasampai)")
            ->whereRaw("trim(a.penyesuaian) = trim(b.penyesuaian)")
            ->first();

        if (isset($query)) {
            $kondisi = true;
        } else {
            $kondisi = false;
        }

        return $kondisi;
    }

    public function updateharga($data)
    {

        foreach ($data as $item) {

            $kotadari = Kota::from(DB::raw("kota with (readuncommitted)"))->where('kodekota', strtoupper(trim($item['kotadari'])))->first();
            $kotasampai = Kota::from(DB::raw("kota with (readuncommitted)"))->where('kodekota', strtoupper(trim($item['kotasampai'])))->first();

            $querydetail = DB::table('container')
                ->from(
                    DB::raw("container  with (readuncommitted)")
                )
                ->select(
                    'id'
                )
                ->orderBy('id', 'Asc');

            $statusContainer = DB::table('statuscontainer')
                ->from(
                    DB::raw("statuscontainer  with (readuncommitted)")
                )
                ->select(
                    'id'
                )
                ->orderBy('id', 'Asc');
            $datadetail = json_decode($querydetail->get(), true);
            $dataStatus = json_decode($statusContainer->get(), true);
            $a = 0;
            $container_id = [];
            $nominal = [];
            $nominalkenek = [];
            $nominalkomisi = [];
            $nominaltol = [];
            $detail_tas_id = [];
            $liter = [];
            foreach ($datadetail as $key => $itemdetail) {

                foreach ($dataStatus as $itemStatus) {
                    $a = $a + 1;
                    $kolom = 'kolom' . $a;
                    $nominal[] = $item[$kolom];
                    $container_id[] = $itemdetail['id'];
                    $statuscontainer_id[] = $itemStatus['id'];
                    $nominalkenek[] = 0;
                    $nominalkomisi[] = 0;
                    $nominaltol[] = 0;
                    $detail_tas_id[] = 0;
                }
            }
            $i = 0;
            foreach ($datadetail as $itemdetail) {

                foreach ($dataStatus as $itemStatus) {
                    $i = $i + 1;
                    $kolomliter = 'liter' . $i;
                    $liter[] = $item[$kolomliter];
                }
            }

            $statusSimpanKandang = DB::table('parameter')
                ->where('grp', 'STATUS SIMPAN KANDANG')
                ->where('text', 'TIDAK SIMPAN KANDANG')
                ->first();
            $getBukanUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS UPAH ZONA')
                ->where('text', 'NON UPAH ZONA')->first();
            $getBukanPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS POSTING TNL')
                ->where('text', 'TIDAK POSTING TNL')->first();
            $getBukanLangsir = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS LANGSIR')
                ->where('text', 'BUKAN LANGSIR')->first();
            $tglsaldo = (new Parameter())->cekText('SALDO', 'SALDO') ?? '1900-01-01';

            $upahRitasiRequest = [
                'parent_id' => 0,
                'tarif_id' => 0,
                'kotadari_id' => $kotadari->id,
                'kotasampai_id' => $kotasampai->id,
                'penyesuaian' => $item['penyesuaian'],
                'jarak' => $item['jarak'],
                'jarakfullempty' => $item['jarak'] * 2,
                'zona_id' => 0,
                'zonadari_id' => 0,
                'zonasampai_id' => 0,
                'statusaktif' =>  1,
                'tglmulaiberlaku' => date('Y-m-d', strtotime($tglsaldo)),
                'modifiedby' => $item['modifiedby'],
                'container_id' => $container_id,
                'statuscontainer_id' => $statuscontainer_id,
                'nominalsupir' => $nominal,
                'nominalkenek' => $nominalkenek,
                'nominalkomisi' => $nominalkomisi,
                'nominaltol' => $nominaltol,
                'detail_tas_id' => $detail_tas_id,
                'liter' => $liter,
                'statuslangsir' => $getBukanLangsir->id,
                'statussimpankandang' => $statusSimpanKandang->id,
                'statusupahzona' => $getBukanUpahZona->id,
                'statuspostingtnl' => $getBukanPostingTnl->id,
                'tas_id' => 0,
                'from' => '',
                'keterangan' => ''
            ];
            $upahsupir = new UpahSupir();
            $upahRitasi = (new UpahSupir())->processStore($upahRitasiRequest, $upahsupir);
        }




        return $data;
    }

    public function cekValidasiInputTripUpah($statuscontainer_id, $jenisorder_id, $upahsupir_id)
    {
        if ($statuscontainer_id != '' && $jenisorder_id != '') {

            // dd($upahsupir_id);
            $getJenisOrderMuatan = DB::table("jenisorder")->from(DB::raw("jenisorder with (readuncommitted)"))
                ->select('id')
                ->where("kodejenisorder", 'MUAT')
                ->orWhere("kodejenisorder", 'EKS')
                ->get();

            $jenisorderanmuatan = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
                ->where('a.grp', 'JENIS ORDERAN MUATAN')
                ->where('a.subgrp', 'JENIS ORDERAN MUATAN')
                ->first()->id ?? 0;

            $jenisorderanbongkaran = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
                ->where('a.grp', 'JENIS ORDERAN BONGKARAN')
                ->where('a.subgrp', 'JENIS ORDERAN BONGKARAN')
                ->first()->id ?? 0;

            $jenisorderanimport = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
                ->where('a.grp', 'JENIS ORDERAN IMPORT')
                ->where('a.subgrp', 'JENIS ORDERAN IMPORT')
                ->first()->id ?? 0;

            $jenisorderanexport = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
                ->where('a.grp', 'JENIS ORDERAN EXPORT')
                ->where('a.subgrp', 'JENIS ORDERAN EXPORT')
                ->first()->id ?? 0;

            $getJenisOrderMuatan = json_decode($getJenisOrderMuatan, true);
            foreach ($getJenisOrderMuatan as $item) {
                $dataMuatanEksport[] = $item['id'];
            }
            $temp = '##tempUpah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->bigInteger('id')->nullable();
                $table->integer('kotadari_id')->length(11)->nullable();
                $table->integer('kotasampai_id')->length(11)->nullable();
                $table->string('kotadari')->nullable();
                $table->string('kotasampai')->nullable();
                $table->integer('tarif_id')->nullable();
                $table->string('tarif')->nullable();
                $table->string('penyesuaian')->nullable();
            });

            if (in_array($jenisorder_id, $dataMuatanEksport)) {
                $queryFull = DB::table("statuscontainer")->from(DB::raw("statuscontainer with (readuncommitted)"))->where('kodestatuscontainer', 'FULL')->first();
                // jika empty
                if ($statuscontainer_id == $queryFull->id) {
                    $getKota = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                        ->select(
                            'upahsupir.id',
                            'upahsupir.kotasampai_id as kotadari_id',
                            'upahsupir.kotadari_id as kotasampai_id',
                            'kotasampai.kodekota as kotadari',
                            'kotadari.kodekota as kotasampai',
                            // 'upahsupir.tarif_id',
                            // 'tarif.tujuan as tarif',
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.id,0)  
                    when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.id,0)  
                    when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.id,0)  
                    when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.id,0)  
                    else  isnull(tarif.id,0) end) as tarif_id
                 "),
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.tujuan,0)  
                    when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.tujuan,0)  
                    when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.tujuan,0)  
                    when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.tujuan,0)  
                    else  isnull(tarif.tujuan,'') end) as tarif
                    "),

                            'upahsupir.penyesuaian'
                        )
                        ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                        ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                        ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                        ->leftJoin(DB::raw("tarif as tarifmuatan with (readuncommitted)"), 'upahsupir.tarifmuatan_id', 'tarifmuatan.id')
                        ->leftJoin(DB::raw("tarif as tarifbongkaran with (readuncommitted)"), 'upahsupir.tarifbongkaran_id', 'tarifbongkaran.id')
                        ->leftJoin(DB::raw("tarif as tarifimport with (readuncommitted)"), 'upahsupir.tarifimport_id', 'tarifimport.id')
                        ->leftJoin(DB::raw("tarif as tarifexport with (readuncommitted)"), 'upahsupir.tarifexport_id', 'tarifexport.id')

                        ->where("upahsupir.id", $upahsupir_id);
                } else {

                    // 
                    $getKota = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                        ->select(
                            'upahsupir.id',
                            'upahsupir.kotadari_id',
                            'upahsupir.kotasampai_id',
                            'kotadari.kodekota as kotadari',
                            'kotasampai.kodekota as kotasampai',
                            // 'upahsupir.tarif_id',
                            // 'tarif.tujuan as tarif',
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.id,0)  
                        when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.id,0)  
                        when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.id,0)  
                        when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.id,0)  
                        else  isnull(tarif.id,0) end) as tarif_id
                     "),
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.tujuan,0)  
                        when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.tujuan,0)  
                        when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.tujuan,0)  
                        when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.tujuan,0)  
                        else  isnull(tarif.tujuan,'') end) as tarif
                        "),
                            'upahsupir.penyesuaian'
                        )
                        ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                        ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                        ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                        ->leftJoin(DB::raw("tarif as tarifmuatan with (readuncommitted)"), 'upahsupir.tarifmuatan_id', 'tarifmuatan.id')
                        ->leftJoin(DB::raw("tarif as tarifbongkaran with (readuncommitted)"), 'upahsupir.tarifbongkaran_id', 'tarifbongkaran.id')
                        ->leftJoin(DB::raw("tarif as tarifimport with (readuncommitted)"), 'upahsupir.tarifimport_id', 'tarifimport.id')
                        ->leftJoin(DB::raw("tarif as tarifexport with (readuncommitted)"), 'upahsupir.tarifexport_id', 'tarifexport.id')

                        ->where("upahsupir.id", $upahsupir_id);
                }
                //  dd($getKota->tosql());
                DB::table($temp)->insertUsing(['id', 'kotadari_id', 'kotasampai_id', 'kotadari', 'kotasampai', 'tarif_id', 'tarif', 'penyesuaian'], $getKota);
                // dd('test');
            } else {
                $queryEmpty = DB::table("statuscontainer")->from(DB::raw("statuscontainer with (readuncommitted)"))->where('kodestatuscontainer', 'EMPTY')->first();
                // jika empty
                if ($statuscontainer_id == $queryEmpty->id) {
                    $getKota = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                        ->select(
                            'upahsupir.id',
                            'upahsupir.kotasampai_id as kotadari_id',
                            'upahsupir.kotadari_id as kotasampai_id',
                            'kotasampai.kodekota as kotadari',
                            'kotadari.kodekota as kotasampai',
                            // 'upahsupir.tarif_id',
                            // 'tarif.tujuan as tarif',
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.id,0)  
                        when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.id,0)  
                        when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.id,0)  
                        when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.id,0)  
                        else  isnull(tarif.id,0) end) as tarif_id
                     "),
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.tujuan,0)  
                        when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.tujuan,0)  
                        when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.tujuan,0)  
                        when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.tujuan,0)  
                        else  isnull(tarif.tujuan,'') end) as tarif
                        "),

                            'upahsupir.penyesuaian'
                        )
                        ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                        ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                        ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                        ->leftJoin(DB::raw("tarif as tarifmuatan with (readuncommitted)"), 'upahsupir.tarifmuatan_id', 'tarifmuatan.id')
                        ->leftJoin(DB::raw("tarif as tarifbongkaran with (readuncommitted)"), 'upahsupir.tarifbongkaran_id', 'tarifbongkaran.id')
                        ->leftJoin(DB::raw("tarif as tarifimport with (readuncommitted)"), 'upahsupir.tarifimport_id', 'tarifimport.id')
                        ->leftJoin(DB::raw("tarif as tarifexport with (readuncommitted)"), 'upahsupir.tarifexport_id', 'tarifexport.id')

                        ->where("upahsupir.id", $upahsupir_id);
                } else {
                    $getKota = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                        ->select(
                            'upahsupir.id',
                            'upahsupir.kotadari_id',
                            'upahsupir.kotasampai_id',
                            'kotadari.kodekota as kotadari',
                            'kotasampai.kodekota as kotasampai',
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.id,0)  
                        when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.id,0)  
                        when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.id,0)  
                        when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.id,0)  
                        else  isnull(tarif.id,0) end) as tarif_id
                     "),
                            db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $jenisorder_id  . " then isnull(tarifmuatan.tujuan,0)  
                        when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $jenisorder_id  . " then isnull(tarifbongkaran.tujuan,0)  
                        when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $jenisorder_id  . " then isnull(tarifimport.tujuan,0)  
                        when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $jenisorder_id  . " then isnull(tarifexport.tujuan,0)  
                        else  isnull(tarif.tujuan,'') end) as tarif
                        "),

                            // 'upahsupir.tarif_id',
                            // 'tarif.tujuan as tarif',
                            'upahsupir.penyesuaian'
                        )
                        ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                        ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                        ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                        ->leftJoin(DB::raw("tarif as tarifmuatan with (readuncommitted)"), 'upahsupir.tarifmuatan_id', 'tarifmuatan.id')
                        ->leftJoin(DB::raw("tarif as tarifbongkaran with (readuncommitted)"), 'upahsupir.tarifbongkaran_id', 'tarifbongkaran.id')
                        ->leftJoin(DB::raw("tarif as tarifimport with (readuncommitted)"), 'upahsupir.tarifimport_id', 'tarifimport.id')
                        ->leftJoin(DB::raw("tarif as tarifexport with (readuncommitted)"), 'upahsupir.tarifexport_id', 'tarifexport.id')

                        ->where("upahsupir.id", $upahsupir_id);
                }
                DB::table($temp)->insertUsing(['id', 'kotadari_id', 'kotasampai_id', 'kotadari', 'kotasampai', 'tarif_id', 'tarif', 'penyesuaian'], $getKota);
            }



            $query = DB::table("$temp")->from(DB::raw("$temp as B with (readuncommitted)"))
                ->select(
                    'B.id',
                    'B.kotadari_id',
                    'B.kotasampai_id',
                    'B.tarif_id',
                    DB::raw("TRIM(B.tarif) as tarif"),
                    DB::raw("TRIM(B.kotadari) as kotadari"),
                    DB::raw("TRIM(B.kotasampai) as kotasampai"),
                    'B.penyesuaian',
                );

            // dd($query->get());
            return $query->first();
        }
    }

    public function processDestroy(UpahSupirRincian $upahsupirRincian, $idheader): UpahSupirRincian
    {

        // dd($idheader);
        // $mandor = new Mandor();
        // dd($mandorDetail->get());
        UpahSupirRincian::where('upahsupir_id', $idheader)->delete();



        (new LogTrail())->processStore([
            'namatabel' => 'TARIF RINCIAN',
            'postingdari' => 'DELETE TARIF RINCIAN',
            'idtrans' => $idheader,
            'nobuktitrans' => $idheader,
            'aksi' => 'DELETE',
            'datajson' => '',
            'modifiedby' => auth('api')->user()->name
        ]);

        return $upahsupirRincian;
    }
}
