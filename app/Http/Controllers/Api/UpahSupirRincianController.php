<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetUpahSupirRincianRequest;
use App\Models\UpahSupirRincian;
use App\Models\Parameter;
use App\Http\Requests\StoreUpahSupirRincianRequest;
use App\Http\Requests\UpdateUpahSupirRincianRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UpahSupirRincianController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'upahsupir_id' => $request->upahsupir_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        try {
            $query = UpahSupirRincian::from(DB::raw("upahsupirrincian as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['upahsupir_id'])) {
                $query->where('detail.upahsupir_id', $params['upahsupir_id']);
            }

            if ($params['withHeader']) {
                $query->join('upahsupir', 'upahsupir.id', 'detail.upahsupir_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('upahsupir_id', $params['whereIn']);
            }
            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();

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

            if ($params['forReport']) {
                $query->select(
                    'kotadari.keterangan as kotadari',
                    'kotasampai.keterangan as kotasampai',
                    'header.jarak',
                    'zona.keterangan as zona',
                    'statusluarkota.text as statusluarkota',
                    'container.keterangan as container_id',
                    'statuscontainer.keterangan as statuscontainer_id',
                    'header.tglmulaiberlaku',
                    'detail.nominalsupir',
                    'detail.nominalkenek',
                    'detail.nominalkomisi',
                    'detail.nominaltol',
                    'detail.liter',
                    DB::raw("'Laporan Upah Supir' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul"),
                    DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                    DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
                )
                    ->Join(DB::raw("upahsupir as header with (readuncommitted)"), 'header.id', 'detail.upahsupir_id')
                    ->Join(DB::raw($tempkota . " as kotadari "), 'kotadari.id', '=', db::raw("isnull(header.kotadari_id,0)"))
                    ->Join(DB::raw($tempkota . " as kotasampai "), 'kotasampai.id', '=', db::raw("isnull(header.kotasampai_id,0)"))
                    ->Join(DB::raw($tempzona . " as zona with (readuncommitted)"), db::raw("isnull(header.zona_id,0)"), 'zona.id')
                    ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'header.statusluarkota', 'statusluarkota.id')
                    ->Join(DB::raw($tempcontainer . " as container with (readuncommitted)"), 'container.id', db::raw("isnull(detail.container_id,0)"))
                    ->Join(DB::raw($tempstatuscontainer . " as statuscontainer with (readuncommitted)"), 'statuscontainer.id', db::raw("isnull(detail.statuscontainer_id,0)"));

                $upahsupir = $query->get();
            } else {
                // dd(db::table($tempcontainer)->get()); 

                $query->select(
                    'container.keterangan as container_id',
                    'statuscontainer.keterangan as statuscontainer_id',
                    'detail.nominalsupir',
                    'detail.nominalkenek',
                    'detail.nominalkomisi',
                    'detail.nominaltol',
                    'detail.liter',
                )
                    ->Join(DB::raw($tempcontainer . " as container with (readuncommitted)"), 'container.id', db::raw("isnull(detail.container_id,0)"))
                    ->Join(DB::raw($tempstatuscontainer . " as statuscontainer with (readuncommitted)"), 'statuscontainer.id', db::raw("isnull(detail.statuscontainer_id,0)"))
                    // ->leftJoin(DB::raw("container with (readuncommitted)"), 'container.id', 'detail.container_id')
                    //         ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'statuscontainer.id', 'detail.statuscontainer_id')
                    ->orderBy('container.id', 'asc')
                    ->orderBy('statuscontainer.keterangan', 'desc');
                $upahsupir = $query->get();
            }


            $idUser = auth('api')->user()->id;
            $getuser = User::select('name', 'cabang.namacabang as cabang_id')
                ->where('user.id', $idUser)->join('cabang', 'user.cabang_id', 'cabang.id')->first();

            return response([
                'data' => $upahsupir,
                'user' => $getuser,

            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }
    public function get(GetUpahSupirRincianRequest $request)
    {
        $upahSupirRincian = new UpahSupirRincian();

        return response([
            'data' => $upahSupirRincian->get(),
            'attributes' => [
                'totalRows' => $upahSupirRincian->totalRows,
                'totalPages' => $upahSupirRincian->totalPages
            ]
        ]);
    }


    public function store(StoreUpahSupirRincianRequest $request)
    {
        $upahSupirRincian = new UpahSupirRincian();

        $upahSupirRincian->upahsupir_id = $request->upahsupir_id;
        $upahSupirRincian->container_id = $request->container_id;
        $upahSupirRincian->statuscontainer_id = $request->statuscontainer_id;
        $upahSupirRincian->nominalsupir = $request->nominalsupir;
        $upahSupirRincian->nominalkenek = $request->nominalkenek;
        $upahSupirRincian->nominalkomisi = $request->nominalkomisi;
        $upahSupirRincian->nominaltol = $request->nominaltol;
        $upahSupirRincian->liter = $request->liter;
        $upahSupirRincian->modifiedby = auth('api')->user()->name;

        if (!$upahSupirRincian->save()) {
            throw new \Exception("Gagal menyimpan upah supir detail.");
        }

        return [
            'error' => false,
            'detail' => $upahSupirRincian,
            'id' => $upahSupirRincian->id,
            'tabel' => $upahSupirRincian->getTable(),
        ];
    }

    public function setUpRow()
    {
        $upahSupirRincian = new UpahSupirRincian();

        return response([
            'status' => true,
            'detail' => $upahSupirRincian->setUpRow()
        ]);
    }
    public function setUpRowExcept($id)
    {
        $upahSupirRincian = new UpahSupirRincian();
        $rincian = $upahSupirRincian->where('upahsupir_id', $id)->get();
        foreach ($rincian as $e) {
            $data[] = [
                "container_id" => $e->container_id,
                "statuscontainer_id" => $e->statuscontainer_id
            ];
        }
        // return $data;
        return response([
            'status' => true,
            'detail' => $upahSupirRincian->setUpRowExcept($data)
        ]);
    }
}
