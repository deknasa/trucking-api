<?php

namespace App\Models;

use App\Http\Controllers\Api\UpahRitasiController;
use App\Http\Controllers\Api\UpahRitasiRincianController;
use App\Http\Requests\StoreUpahRitasiRequest;
use App\Http\Requests\StoreUpahRitasiRincianRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpahRitasiRincian extends MyModel
{
    use HasFactory;

    protected $table = 'upahritasirincian';

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
        $query = DB::table('upahritasirincian')->from(DB::raw("upahritasirincian with (readuncommitted)"))
            ->select(
                'upahritasirincian.container_id',
                'container.keterangan as container',
                'upahritasirincian.nominalsupir',
                'upahritasirincian.liter',
            )
            ->leftJoin('container', 'container.id', 'upahritasirincian.container_id')
            ->where('upahritasi_id', '=', $id);


        $data = $query->get();

        return $data;
    }


    public function cekupdateharga($data)
    {
        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->string('kotadari', 1000)->nullable();
            $table->string('kotasampai', 1000)->nullable();
            $table->double('nominalsupir', 15, 2)->nullable();
            $table->double('jarak', 15, 2)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
        });

        foreach ($data as $item) {
            $values = array(
                'kotadari' => $item['kotadari'],
                'kotasampai' => $item['kotasampai'],
                'nominalsupir' => $item['nominalsupir'],
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

        $querytgl = DB::table('upahritasi')
            ->from(DB::raw("upahritasi with (readuncommitted)"))
            ->select(
                'kotadari.keterangan as kotadari',
                'kotasampai.keterangan as kotasampai',
                'tglmulaiberlaku',
            )

            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahritasi.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahritasi.kotasampai_id');

        DB::table($temptgl)->insertUsing(['kotadari', 'kotasampai', 'tglmulaiberlaku'], $querytgl);

                // dd( DB::table($tempdata)->get(),  DB::table($temptgl)->get());
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

        // dd($datadetail);
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
            $datadetail = json_decode($querydetail->get(), true);
            $a = 0;
            $container_id = [];
            $nominal = [];
            $liter = [];

            foreach ($datadetail as $itemdetail) {
                $a = $a + 1;
                $kolom = 'kolom' . $a;

                $container_id[] = $itemdetail['id'];
                $liter[] = $item[$kolom];
            }

            $upahRitasiRequest = [
                'parent_id' => 0,
                'tarif_id' => 0,
                'kotadari_id' => $kotadari->id,
                'kotasampai_id' => $kotasampai->id,
                'jarak' => $item['jarak'],
                'nominalsupir' => $item['nominalsupir'],
                'zona_id' => 0,
                'statusaktif' =>  1,
                'tglmulaiberlaku' => $item['tglmulaiberlaku'],
                'modifiedby' => $item['modifiedby'],
                'container_id' => $container_id,
                'liter' => $liter
            ];

            $upahRitasi = new StoreUpahRitasiRequest($upahRitasiRequest);
            app(UpahRitasiController::class)->store($upahRitasi);
        }




        return $data;
    }
    public function listpivot($dari, $sampai)
    {

        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->string('container', 1000)->nullable();
            $table->string('litercontainer', 1000)->nullable();
            $table->double('liter', 10, 2)->nullable();
        });

        $query = DB::table('container')->from(DB::raw("container with (readuncommitted)"))
            ->select(
                'upahritasi.id as id',
                'container.id as container_id',
                'container.keterangan as container',
                'container.keterangan as litercontainer',
                DB::raw("isnull(upahritasirincian.liter,0) as liter"),
            )
            ->leftJoin(DB::raw("upahritasirincian with (readuncommitted)"), 'container.id', '=', 'upahritasirincian.container_id')
            ->leftJoin(DB::raw("upahritasi with (readuncommitted)"), 'upahritasi.id', '=', 'upahritasirincian.upahritasi_id')
            ->whereRaw("upahritasi.tglmulaiberlaku >= '$dari'")
            ->whereRaw("upahritasi.tglmulaiberlaku <= '$sampai'");

        DB::table($tempdata)->insertUsing([
            'id',
            'container_id',
            'container',
            'litercontainer',
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
                $table->string('jarak')->nullable();
                $table->string('tglmulaiberlaku')->nullable();
                $table->double('nominal', 15, 2)->nullable();
            });

            $querytempupah = DB::table('upahritasi')->from(DB::raw("upahritasi with (readuncommitted)"))
                ->select(
                    'upahritasi.id as id',
                    'dari.keterangan as dari',
                    'kota.keterangan as tujuan',
                    'upahritasi.jarak',
                    'upahritasi.tglmulaiberlaku',
                    DB::raw("isnull(upahritasi.nominalsupir,0) as nominal"),

                )
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'upahritasi.kotasampai_id', '=', 'kota.id')
                ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'upahritasi.kotadari_id', '=', 'dari.id');


            DB::table($tempupah)->insertUsing([
                'id',
                'dari',
                'tujuan',
                'jarak',
                'tglmulaiberlaku',
                'nominal',

            ], $querytempupah);

            $tempdatagroup = '##tempdatagroup' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempdatagroup, function ($table) {
                $table->unsignedBigInteger('container_id')->nullable();
            });

            $querydatagroup =  DB::table($tempdata)->from(
                DB::raw($tempdata)
            )
                ->select(
                    'container_id',
                )
                ->groupBy('container_id',);

            DB::table($tempdatagroup)->insertUsing([
                'container_id',
            ], $querydatagroup);

            $queryloop = DB::table($tempdatagroup)->from(
                DB::raw($tempdatagroup)
            )
                ->select(
                    'container.keterangan as container',
                    'container.keterangan as litercontainer'
                )
                ->leftJoin('container', "$tempdatagroup.container_id", 'container.id')
                ->orderBy('container.id', 'asc')
                ->get();

            $columnid = '';
            $columnliterid = '';
            $a = 0;
            $datadetail = json_decode($queryloop, true);

            foreach ($datadetail as $item) {
                if ($a == 0) {
                    $columnid = $columnid . '[' . $item['container'] . ']';
                    $columnliterid = $columnliterid . '[liter' . $item['litercontainer'] . ']';

                    DB::table($tempdata)
                        ->where('container', $item['container'])
                        ->update(['litercontainer' => 'liter' . $item['container']]);
                } else {
                    $columnid = $columnid . ',[' . $item['container'] . ']';
                    $columnliterid = $columnliterid . ',[liter' . $item['litercontainer'] . ']';

                    DB::table($tempdata)
                        ->where('container', $item['container'])
                        ->update(['litercontainer' => 'liter' . $item['container']]);
                }

                $a = $a + 1;
            }

            // $statement = ' select b.dari as [Dari],b.tujuan as [Tujuan],b.jarak as [Jarak], b.nominal as [Nominal],b.tglmulaiberlaku as [Tgl Mulai Berlaku],A.* from (select id,' . $columnid . ' from 
            //     (select A.id,A.container
            //         from ' . $tempdata . ' A) as SourceTable

            //     Pivot (
            //         max(nominal)
            //         for container in (' . $columnid . ')
            //         ) as PivotTable)A
            //     inner join ' . $tempupah . ' b with (readuncommitted) on A.id=B.id


            // ';

            $statement2 = 'select b.dari as [Dari],b.tujuan as [Tujuan],b.jarak as [Jarak],b.tglmulaiberlaku as [Tgl Mulai Berlaku], b.nominal as [Nominal],A.* from (select id,' . $columnliterid . ' from 
                (select A.id,A.litercontainer,A.liter
                    from ' . $tempdata . ' A) as SourceTable
            
                Pivot (
                    max(liter)
                    for litercontainer in (' . $columnliterid . ')
                    ) as PivotTable)A
                inner join ' . $tempupah . ' b with (readuncommitted) on A.id=B.id
            ';

            // $data1 = DB::select(DB::raw($statement));
            $data2 = DB::select(DB::raw($statement2));
            // $merger = [];
            // foreach ($data1 as $key => $value) {
            //     $datas2 = json_decode(json_encode($data2[$key]), true);
            //     $datas1 = json_decode(json_encode($data1[$key]), true);
            //     $merger[] = array_merge($datas1, $datas2);
            // }


            return $data2;
        }
    }

    public function setUpRow()
    {
        $query = DB::table('container')->select(
            'container.keterangan as container',
            'container.id as container_id',
            db::Raw("0 as nominalsupir"),
            db::Raw("0 as liter"),
        );

        return $query->get();
    }
    public function setUpRowExcept($rincian)
    {
        $data = DB::table('container')->select(
            'container.keterangan as container',
            'container.id as container_id'
        );
        $temp = '##tempcrossjoin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->increments('id');
            $table->string('container')->nullable();
            $table->string('containerId')->nullable();
        });

        DB::table($temp)->insertUsing([
            "container",
            "containerId"
        ], $data);

        //select yang sudah ada
        $except = DB::table($temp)->select(
            "$temp.id",
        );
        for ($i = 0; $i < count($rincian); $i++) {
            $except->orWhere(function ($query) use ($rincian, $i) {
                $query->where('containerId', $rincian[$i]['container_id']);
            });
        }

        foreach ($except->get() as $e) {
            $arr[] = $e->id;
        }

        //select semua keluali
        $query = DB::table($temp)->select(
            "$temp.id",
            "$temp.container",
            "$temp.containerId as container_id"
        )->whereNotIn('id', $arr);

        // ->whereRaw(" NOT EXIST  ( select $temp.statuscontainer, $temp.container from   [$temp]  WHERE (statuscontainer = 'empty' and container = '20`') or (statuscontainer = 'FULL' and container = '40`') ) ");
        // ->whereRaw("(statuscontainer = 'FULL' and container = '40`')");

        return $query->get();
    }

    public function processStore(UpahRitasi $upahritasi, array $data): UpahRitasiRincian
    {
        $upahritasirincian = new UpahRitasiRincian();
        $upahritasirincian->upahritasi_id = $data['upahritasi_id'];
        $upahritasirincian->container_id = $data['container_id'];
        $upahritasirincian->nominalsupir = 0;
        $upahritasirincian->liter = $data['liter'];
        $upahritasirincian->modifiedby = auth('api')->user()->name;
        $upahritasirincian->info = html_entity_decode(request()->info);

        if (!$upahritasirincian->save()) {
            throw new \Exception("Gagal menyimpan upah ritasi detail.");
        }

        return $upahritasirincian;
    }
}
