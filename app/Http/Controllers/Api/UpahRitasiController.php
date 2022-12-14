<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpahRitasi;
use App\Models\UpahRitasiRincian;
use App\Models\Kota;
use App\Models\Zona;
use App\Models\Container;
use App\Models\StatusContainer;
use App\Http\Requests\StoreUpahRitasiRequest;
use App\Http\Requests\UpdateUpahRitasiRequest;
use App\Http\Requests\StoreUpahRitasiRincianRequest;
use App\Http\Requests\UpdateUpahRitasiRincianRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpahRitasiController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {

        $upahritasi = new UpahRitasi();

        return response([
            'data' => $upahritasi->get(),
            'attributes' => [
                'totalRows' => $upahritasi->totalRows,
                'totalPages' => $upahritasi->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreUpahRitasiRequest $request)
    {
        DB::beginTransaction();

        try {
            $upahritasi = new UpahRitasi();

            $upahritasi->kotadari_id = $request->kotadari_id;
            $upahritasi->kotasampai_id = $request->kotasampai_id;
            $upahritasi->jarak = str_replace(',', '', str_replace('.', '', $request->jarak));
            $upahritasi->zona_id = $request->zona_id;
            $upahritasi->statusaktif = $request->statusaktif;
            $upahritasi->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            $upahritasi->tglakhirberlaku = date('Y-m-d', strtotime($request->tglakhirberlaku));
            $upahritasi->statusluarkota = $request->statusluarkota;

            $upahritasi->modifiedby = auth('api')->user()->name;

            if ($upahritasi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($upahritasi->getTable()),
                    'postingdari' => 'ENTRY UPAH RITASI',
                    'idtrans' => $upahritasi->id,
                    'nobuktitrans' => $upahritasi->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $upahritasi->toArray(),
                    'modifiedby' => $upahritasi->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {
                    $datadetail = [
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i] ?? 0,
                        'nominalkomisi' => $request->nominalkomisi[$i] ?? 0,
                        'nominaltol' =>  $request->nominaltol[$i] ?? 0,
                        'liter' => $request->liter[$i] ?? 0,
                        'modifiedby' => $upahritasi->modifiedby,
                    ];
                    $data = new StoreUpahRitasiRincianRequest($datadetail);
                    $datadetails = app(UpahRitasiRincianController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i] ?? 0,
                        'nominalkomisi' => $request->nominalkomisi[$i] ?? 0,
                        'nominaltol' =>  $request->nominaltol[$i] ?? 0,
                        'liter' => $request->liter[$i] ?? 0,
                        'modifiedby' => $upahritasi->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($upahritasi->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($upahritasi->updated_at)),
                    ];

                    $detaillog[] = $datadetaillog;
                }


                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY UPAH RITASI RINCIAN',
                    'idtrans' =>  $upahritasi->id,
                    'nobuktitrans' => $upahritasi->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $upahritasi->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($upahritasi, $upahritasi->getTable());
            $upahritasi->position = $selected->position;
            $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahritasi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($upahritasi->upahritasiRincian());
    }


    public function show($id)
    {

        $data = UpahRitasi::findAll($id);
        $detail = UpahRitasiRincian::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateUpahRitasiRequest $request, UpahRitasi $upahritasi)
    {
        DB::beginTransaction();

        try {
            $upahritasi->kotadari_id = $request->kotadari_id;
            $upahritasi->kotasampai_id = $request->kotasampai_id;
            $upahritasi->jarak = str_replace(',', '', str_replace('.', '', $request->jarak));
            $upahritasi->zona_id = $request->zona_id;
            $upahritasi->statusaktif = $request->statusaktif;
            $upahritasi->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            $upahritasi->tglakhirberlaku = date('Y-m-d', strtotime($request->tglakhirberlaku));
            $upahritasi->statusluarkota = $request->statusluarkota;

            $upahritasi->modifiedby = auth('api')->user()->name;

            if ($upahritasi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($upahritasi->getTable()),
                    'postingdari' => 'EDIT UPAH RITASI',
                    'idtrans' => $upahritasi->id,
                    'nobuktitrans' => $upahritasi->id,
                    'aksi' => 'EDIT',
                    'datajson' => $upahritasi->toArray(),
                    'modifiedby' => $upahritasi->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                UpahRitasiRincian::where('upahritasi_id', $upahritasi->id)->lockForUpdate()->delete();
                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {
                    $datadetail = [
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i] ?? 0,
                        'nominalkomisi' => $request->nominalkomisi[$i] ?? 0,
                        'nominaltol' =>  $request->nominaltol[$i] ?? 0,
                        'liter' => $request->liter[$i] ?? 0,
                        'modifiedby' => $upahritasi->modifiedby,
                    ];

                    $data = new StoreUpahRitasiRincianRequest($datadetail);
                    $datadetails = app(UpahRitasiRincianController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i] ?? 0,
                        'nominalkomisi' => $request->nominalkomisi[$i] ?? 0,
                        'nominaltol' =>  $request->nominaltol[$i] ?? 0,
                        'liter' => $request->liter[$i] ?? 0,
                        'modifiedby' => $upahritasi->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($upahritasi->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($upahritasi->updated_at)),
                    ];
                    $detaillog[] = $datadetaillog;
                }

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'EDIT UPAH RITASI RINCIAN',
                    'idtrans' =>  $upahritasi->id,
                    'nobuktitrans' => $upahritasi->id,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $upahritasi->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($upahritasi, $upahritasi->getTable());
            $upahritasi->position = $selected->position;
            $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahritasi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(UpahRitasi $upahritasi, Request $request)
    {

        DB::beginTransaction();
        try {
            $getDetail = UpahRitasiRincian::where('upahritasi_id', $upahritasi->id)->get();
            $delete = UpahRitasiRincian::where('upahritasi_id', $upahritasi->id)->lockForUpdate()->delete();
            $delete = UpahRitasi::destroy($upahritasi->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($upahritasi->getTable()),
                    'postingdari' => 'DELETE UPAH RITASI',
                    'idtrans' => $upahritasi->id,
                    'nobuktitrans' => $upahritasi->id,
                    'aksi' => 'DELETE',
                    'datajson' => $upahritasi->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                // DELETE UPAH RITASI RINCIAN
                $logTrailUpahRitasiRincian = [
                    'namatabel' => 'UPAHRITASIRINCIAN',
                    'postingdari' => 'DELETE UPAH RITASI RINCIAN',
                    'idtrans' => $upahritasi->id,
                    'nobuktitrans' => $upahritasi->id,
                    'aksi' => 'DELETE',
                    'datajson' => $getDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailUpahRitasiRincian = new StoreLogTrailRequest($logTrailUpahRitasiRincian);
                app(LogTrailController::class)->store($validatedLogTrailUpahRitasiRincian);

                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($upahritasi, $upahritasi->getTable(), true);
            $upahritasi->position = $selected->position;
            $upahritasi->id = $selected->id;
            $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $upahritasi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'kota' => Kota::all(),
            'zona' => Zona::all(),
            'container' => Container::all(),
            'statuscontainer' => StatusContainer::all(),
            'statusaktif' => Parameter::where('grp', 'STATUS AKTIF')->get(),
            'statusluarkota' => Parameter::where('grp', 'STATUS LUAR KOTA')->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function comboLuarKota(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, 10000);
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->default(0);
                $table->string('parameter', 50)->default(0);
                $table->string('param', 50)->default(0);
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('upahritasi')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
