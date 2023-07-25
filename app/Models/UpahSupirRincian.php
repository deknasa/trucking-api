<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\StatusContainer;
use Illuminate\Support\Facades\Schema;

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
            ->leftJoin('container', 'container.id', 'upahsupirrincian.container_id')
            ->leftJoin('statuscontainer', 'statuscontainer.id', 'upahsupirrincian.statuscontainer_id')
            ->where('upahsupir_id', '=', $id)
            ->orderBy('container.id', 'asc')
            ->orderBy('statuscontainer.kodestatuscontainer', 'desc');


        $data = $query->get();


        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';

        $container_id = request()->container_id ?? 0;
        $statuscontainer_id = request()->statuscontainer_id ?? 0;
        $jenisorder_id = request()->jenisorder_id ?? 0;
        $statusUpahZona = request()->statusupahzona ?? 0;
        $getJenisOrderMuatan = DB::table("jenisorder")->from(DB::raw("jenisorder with (readuncommitted)"))
            ->select('id')
            ->where("kodejenisorder", 'MUAT')
            ->orWhere("kodejenisorder", 'EKS')
            ->get();
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
            $table->integer('statusaktif')->length(11)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        if (in_array($jenisorder_id, $dataMuatanEksport)) {
            $queryEmpty = DB::table("statuscontainer")->from(DB::raw("statuscontainer with (readuncommitted)"))->where('kodestatuscontainer', 'EMPTY')->first();
            // jika empty
            if ($statuscontainer_id == $queryEmpty->id) {
                $getKota = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
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
                        'upahsupir.tarif_id',
                        'tarif.tujuan as tarif',
                        'upahsupir.penyesuaian',
                        'upahsupir.jarak',
                        'upahsupir.statusaktif',
                        'upahsupir.tglmulaiberlaku',
                        'upahsupir.modifiedby',
                        'upahsupir.created_at',
                        'upahsupir.updated_at'
                    )
                    ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                    ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                    ->leftJoin(DB::raw("zona as zonadari with (readuncommitted)"), 'upahsupir.zonadari_id', 'zonadari.id')
                    ->leftJoin(DB::raw("zona as zonasampai with (readuncommitted)"), 'upahsupir.zonasampai_id', 'zonasampai.id')
                    ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                    ->where('upahsupir.statusupahzona', $statusUpahZona);
            } else {
                $getKota = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
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
                        'upahsupir.tarif_id',
                        'tarif.tujuan as tarif',
                        'upahsupir.penyesuaian',
                        'upahsupir.jarak',
                        'upahsupir.statusaktif',
                        'upahsupir.tglmulaiberlaku',
                        'upahsupir.modifiedby',
                        'upahsupir.created_at',
                        'upahsupir.updated_at'
                    )
                    ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                    ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                    ->leftJoin(DB::raw("zona as zonadari with (readuncommitted)"), 'upahsupir.zonadari_id', 'zonadari.id')
                    ->leftJoin(DB::raw("zona as zonasampai with (readuncommitted)"), 'upahsupir.zonasampai_id', 'zonasampai.id')
                    ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                    ->where('upahsupir.statusupahzona', $statusUpahZona);
            }
            DB::table($temp)->insertUsing(['id', 'kotadari_id', 'kotasampai_id', 'kotadari', 'kotasampai', 'zonadari_id', 'zonasampai_id', 'zonadari', 'zonasampai', 'tarif_id', 'tarif', 'penyesuaian', 'jarak', 'statusaktif', 'tglmulaiberlaku', 'modifiedby', 'created_at', 'updated_at'], $getKota);
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
                        'upahsupir.zonasampai_id as zonadari_id',
                        'upahsupir.zonadari_id as zonasampai_id',
                        'zonasampai.zona as zonadari',
                        'zonadari.zona as zonasampai',
                        'upahsupir.tarif_id',
                        'tarif.tujuan as tarif',
                        'upahsupir.penyesuaian',
                        'upahsupir.jarak',
                        'upahsupir.statusaktif',
                        'upahsupir.tglmulaiberlaku',
                        'upahsupir.modifiedby',
                        'upahsupir.created_at',
                        'upahsupir.updated_at'
                    )
                    ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                    ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                    ->leftJoin(DB::raw("zona as zonadari with (readuncommitted)"), 'upahsupir.zonadari_id', 'zonadari.id')
                    ->leftJoin(DB::raw("zona as zonasampai with (readuncommitted)"), 'upahsupir.zonasampai_id', 'zonasampai.id')
                    ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                    ->where('upahsupir.statusupahzona', $statusUpahZona);
            } else {
                $getKota = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
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
                        'upahsupir.tarif_id',
                        'tarif.tujuan as tarif',
                        'upahsupir.penyesuaian',
                        'upahsupir.jarak',
                        'upahsupir.statusaktif',
                        'upahsupir.tglmulaiberlaku',
                        'upahsupir.modifiedby',
                        'upahsupir.created_at',
                        'upahsupir.updated_at'
                    )
                    ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                    ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                    ->leftJoin(DB::raw("zona as zonadari with (readuncommitted)"), 'upahsupir.zonadari_id', 'zonadari.id')
                    ->leftJoin(DB::raw("zona as zonasampai with (readuncommitted)"), 'upahsupir.zonasampai_id', 'zonasampai.id')
                    ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                    ->where('upahsupir.statusupahzona', $statusUpahZona);
            }
            DB::table($temp)->insertUsing(['id', 'kotadari_id', 'kotasampai_id', 'kotadari', 'kotasampai', 'zonadari_id', 'zonasampai_id', 'zonadari', 'zonasampai', 'tarif_id', 'tarif', 'penyesuaian', 'jarak', 'statusaktif', 'tglmulaiberlaku', 'modifiedby', 'created_at', 'updated_at'], $getKota);
        }



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
                'upahsupirrincian.nominalsupir',
                'upahsupirrincian.nominalkenek',
                'upahsupirrincian.nominalkomisi',
                'B.tglmulaiberlaku',
                'B.modifiedby',
                'B.created_at',
                'B.updated_at'
            )
            ->leftJoin(DB::raw("$temp as B with (readuncommitted)"), 'B.id', 'upahsupirrincian.upahsupir_id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'B.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'upahsupirrincian.container_id', 'container.id')
            ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'upahsupirrincian.statuscontainer_id', 'statuscontainer.id');


        $this->sort($query);

        $this->filter($query);

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

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);

        $data = $query->get();

        return $data;
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
    public function listpivot($dari, $sampai)
    {
        $tempdatacs = '##tempdatacontainerstatus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatacs, function ($table) {
            $table->unsignedBigInteger('upah_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->unsignedBigInteger('statuscontainer_id')->nullable();
            $table->string('container', 1000)->nullable();
            $table->string('statuscontainer', 1000)->nullable();
        });

        $queryupah = DB::table('upahsupir')->from(DB::raw("upahsupir as a with (readuncommitted)"))
            ->select(
                'a.id',
            )
            ->whereRaw("a.tglmulaiberlaku >= '$dari'")
            ->whereRaw("a.tglmulaiberlaku <= '$sampai'")
            ->orderBy('a.id', 'asc')
            ->get();

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

            $statement = ' select b.dari,b.tujuan,b.penyesuaian,b.jarak,b.tglmulaiberlaku,A.* from (select id,' . $columnid . ' from 
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
        if ($this->params['sortIndex'] == 'penyesuaian' || $this->params['sortIndex'] == 'jarak' || $this->params['sortIndex'] == 'tglmulaiberlaku' || $this->params['sortIndex'] == 'modifiedby' || $this->params['sortIndex'] == 'created_at' || $this->params['sortIndex'] == 'updated_at' || $this->params['sortIndex'] == 'statusaktif' || $this->params['sortIndex'] == 'kotadari' || $this->params['sortIndex'] == 'kotasampai') {
            return $query->orderBy('B.' . $this->params['sortIndex'], $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'container') {
            return $query->orderBy('container.kodecontainer', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'statuscontainer') {
            return $query->orderBy('statuscontainer.kodestatuscontainer', $this->params['sortOrder']);
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
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'container') {
                            $query = $query->where('container.kodecontainer', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'statuscontainer') {
                            $query = $query->where('statuscontainer.kodestatuscontainer', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->WhereRaw("format(B.tglmulaiberlaku,'dd-MM-yyyy') like '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(B." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'nominalsupir' || $filters['field'] == 'nominalkenek' || $filters['field'] == 'nominalkomisi') {
                            $query = $query->whereRaw("format(upahsupirrincian." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where('B.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } elseif ($filters['field'] == 'container') {
                                $query = $query->orWhere('container.kodecontainer', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'statuscontainer') {
                                $query = $query->orWhere('statuscontainer.kodestatuscontainer', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->orWhereRaw("format(B.tglmulaiberlaku,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(B." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominalsupir' || $filters['field'] == 'nominalkenek' || $filters['field'] == 'nominalkomisi') {
                                $query = $query->orWhereRaw("format(upahsupirrincian." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere('B.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function processStore(UpahSupir $upahsupir, array $data): UpahSupirRincian
    {
        $upahSupirRincian = new UpahSupirRincian();
        $upahSupirRincian->upahsupir_id = $data['upahsupir_id'];
        $upahSupirRincian->container_id = $data['container_id'];
        $upahSupirRincian->statuscontainer_id = $data['statuscontainer_id'];
        $upahSupirRincian->nominalsupir = $data['nominalsupir'];
        $upahSupirRincian->nominalkenek = $data['nominalkenek'];
        $upahSupirRincian->nominalkomisi = $data['nominalkomisi'];
        $upahSupirRincian->nominaltol = $data['nominaltol'];
        $upahSupirRincian->liter = $data['liter'];
        $upahSupirRincian->modifiedby = auth('api')->user()->name;

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
            ->where('upahsupirrincian.statuscontainer_id', '=', $statuscontainer_id)
            ->where('upahsupir.statusaktif', '=', $statusaktif->id);

        return $query->first();
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

        foreach ($data as $item) {
            $values = array(
                'kotadari' => $item['kotadari'],
                'kotasampai' => $item['kotasampai'],
                'penyesuaian' => $item['penyesuaian'],
                'jarak' => $item['jarak'],
                'tglmulaiberlaku' => $item['tglmulaiberlaku'],
            );
            DB::table($tempdata)->insert($values);
        }

        $temptgl = '##temptgl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptgl, function ($table) {
            $table->string('kotadari', 1000)->nullable();
            $table->string('kotasampai', 1000)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
        });

        $querytgl = DB::table('upahsupir')
            ->from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'kotadari.keterangan as kotadari',
                'kotasampai.keterangan as kotasampai',
                'tglmulaiberlaku'
            )
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id');

        DB::table($temptgl)->insertUsing(['kotadari', 'kotasampai', 'tglmulaiberlaku'], $querytgl);


        $query = DB::table($tempdata)
            ->from(DB::raw($tempdata . " as a"))
            ->join(DB::raw($temptgl . " as b"), 'a.tglmulaiberlaku', 'b.tglmulaiberlaku')
            ->whereRaw("trim(a.kotadari) = trim(b.kotadari)")
            ->whereRaw("trim(a.kotasampai) = trim(b.kotasampai)")
            ->whereRaw("a.tglmulaiberlaku = b.tglmulaiberlaku")
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

            $kotadari = Kota::from(DB::raw("kota with (readuncommitted)"))->where('keterangan', strtoupper(trim($item['kotadari'])))->first();
            $kotasampai = Kota::from(DB::raw("kota with (readuncommitted)"))->where('keterangan', strtoupper(trim($item['kotasampai'])))->first();

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
            $liter = [];

            foreach ($datadetail as $key => $itemdetail) {

                foreach ($dataStatus as $itemStatus) {
                    $a = $a + 1;
                    $kolom = 'kolom' . $a;
                    $nominal[] = $item[$kolom];
                    $container_id[] = $itemdetail['id'];
                    $statuscontainer_id[] = $itemStatus['id'];
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
            $upahRitasiRequest = [
                'parent_id' => 0,
                'tarif_id' => 0,
                'kotadari_id' => $kotadari->id,
                'kotasampai_id' => $kotasampai->id,
                'penyesuaian' => $item['penyesuaian'],
                'jarak' => $item['jarak'],
                'zona_id' => 0,
                'statusaktif' =>  1,
                'tglmulaiberlaku' => $item['tglmulaiberlaku'],
                'modifiedby' => $item['modifiedby'],
                'container_id' => $container_id,
                'statuscontainer_id' => $statuscontainer_id,
                'nominalsupir' => $nominal,
                'liter' => $liter,
                'statussimpankandang' => $statusSimpanKandang->id,
                'statusupahzona' => $getBukanUpahZona->id
            ];

            $upahRitasi = (new UpahSupir())->processStore($upahRitasiRequest);
        }




        return $data;
    }

    public function cekValidasiInputTripUpah($statuscontainer_id, $jenisorder_id, $upahsupir_id)
    {

        $getJenisOrderMuatan = DB::table("jenisorder")->from(DB::raw("jenisorder with (readuncommitted)"))
            ->select('id')
            ->where("kodejenisorder", 'MUAT')
            ->orWhere("kodejenisorder", 'EKS')
            ->get();
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
            $queryEmpty = DB::table("statuscontainer")->from(DB::raw("statuscontainer with (readuncommitted)"))->where('kodestatuscontainer', 'EMPTY')->first();
            // jika empty
            if ($statuscontainer_id == $queryEmpty->id) {
                $getKota = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                    ->select(
                        'upahsupir.id',
                        'upahsupir.kotadari_id',
                        'upahsupir.kotasampai_id',
                        'kotadari.kodekota as kotadari',
                        'kotasampai.kodekota as kotasampai',
                        'upahsupir.tarif_id',
                        'tarif.tujuan as tarif',
                        'upahsupir.penyesuaian'
                    )
                    ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                    ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                    ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                    ->where("upahsupir.id", $upahsupir_id);
            } else {
                $getKota = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                    ->select(
                        'upahsupir.id',
                        'upahsupir.kotasampai_id as kotadari_id',
                        'upahsupir.kotadari_id as kotasampai_id',
                        'kotasampai.kodekota as kotadari',
                        'kotadari.kodekota as kotasampai',
                        'upahsupir.tarif_id',
                        'tarif.tujuan as tarif',
                        'upahsupir.penyesuaian'
                    )
                    ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                    ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                    ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                    ->where("upahsupir.id", $upahsupir_id);
            }
            DB::table($temp)->insertUsing(['id', 'kotadari_id', 'kotasampai_id', 'kotadari', 'kotasampai', 'tarif_id', 'tarif', 'penyesuaian'], $getKota);
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
                        'upahsupir.tarif_id',
                        'tarif.tujuan as tarif',
                        'upahsupir.penyesuaian'
                    )
                    ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                    ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                    ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                    ->where("upahsupir.id", $upahsupir_id);
            } else {
                $getKota = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                    ->select(
                        'upahsupir.id',
                        'upahsupir.kotadari_id',
                        'upahsupir.kotasampai_id',
                        'kotadari.kodekota as kotadari',
                        'kotasampai.kodekota as kotasampai',
                        'upahsupir.tarif_id',
                        'tarif.tujuan as tarif',
                        'upahsupir.penyesuaian'
                    )
                    ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'upahsupir.kotadari_id', 'kotadari.id')
                    ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kotasampai.id')
                    ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
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

        return $query->first();
    }
}
