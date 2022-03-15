<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsenTradoRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAbsenTradoRequest;
use App\Models\AbsenTrado;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsenTradoController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 10,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = AbsenTrado::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = AbsenTrado::select(
                'absentrado.id',
                'absentrado.kodeabsen',
                'absentrado.keterangan',
                'parameter.text as statusaktif',
                'absentrado.modifiedby',
                'absentrado.created_at',
                'absentrado.updated_at'
            )
                ->leftJoin('parameter', 'absentrado.statusaktif', '=', 'parameter.id')
                ->orderBy('absentrado.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = AbsenTrado::select(
                    'absentrado.id',
                    'absentrado.kodeabsen',
                    'absentrado.keterangan',
                    'parameter.text as statusaktif',
                    'absentrado.modifiedby',
                    'absentrado.created_at',
                    'absentrado.updated_at'
                )
                    ->leftJoin('parameter', 'absentrado.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('absentrado.id', $params['sortOrder']);
            } else {
                $query = AbsenTrado::select(
                    'absentrado.id',
                    'absentrado.kodeabsen',
                    'absentrado.keterangan',
                    'parameter.text as statusaktif',
                    'absentrado.modifiedby',
                    'absentrado.created_at',
                    'absentrado.updated_at'
                )
                    ->leftJoin('parameter', 'absentrado.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('absentrado.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }

            $totalRows = count($query->get());
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $absenTrados = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $absenTrados,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function store(StoreAbsenTradoRequest $request)
    {
        DB::beginTransaction();
        try {
            $absenTrado = new AbsenTrado();
            $absenTrado->kodeabsen = $request->kodeabsen;
            $absenTrado->keterangan = $request->keterangan;
            $absenTrado->statusaktif = $request->statusaktif;
            $absenTrado->modifiedby = $request->modifiedby;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($absenTrado->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($absenTrado->getTable()),
                    'postingdari' => 'ENTRY ABSEN TRADO',
                    'idtrans' => $absenTrado->id,
                    'nobuktitrans' => $absenTrado->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $absenTrado->toArray(),
                    'modifiedby' => $absenTrado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($absenTrado->id, $request, $del);
            $absenTrado->position = $data->row;

            if (isset($request->limit)) {
                $absenTrado->page = ceil($absenTrado->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $absenTrado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(AbsenTrado $absenTrado)
    {
        return response([
            'status' => true,
            'data' => $absenTrado
        ]);
    }

    public function update(UpdateAbsenTradoRequest $request, AbsenTrado $absenTrado)
    {
        try {
            $absenTrado->kodeabsen = $request->kodeabsen;
            $absenTrado->keterangan = $request->keterangan;
            $absenTrado->statusaktif = $request->statusaktif;
            $absenTrado->modifiedby = $request->modifiedby;

            if ($absenTrado->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($absenTrado->getTable()),
                    'postingdari' => 'EDIT ABSEN TRADO',
                    'idtrans' => $absenTrado->id,
                    'nobuktitrans' => $absenTrado->id,
                    'aksi' => 'EDIT',
                    'datajson' => $absenTrado->toArray(),
                    'modifiedby' => $absenTrado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $absenTrado->position = $this->getid($absenTrado->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $absenTrado->page = ceil($absenTrado->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $absenTrado
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(AbsenTrado $absenTrado, Request $request)
    {
        $delete = AbsenTrado::destroy($absenTrado->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($absenTrado->getTable()),
                'postingdari' => 'DELETE ABSEN TRADO',
                'idtrans' => $absenTrado->id,
                'nobuktitrans' => $absenTrado->id,
                'aksi' => 'DELETE',
                'datajson' => $absenTrado->toArray(),
                'modifiedby' => $absenTrado->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($absenTrado->id, $request, $del);
            $absenTrado->position = $data->row;
            $absenTrado->id = $data->id;
            if (isset($request->limit)) {
                $absenTrado->page = ceil($absenTrado->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $absenTrado
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('absentrado')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getid($id, $request, $del)
    {

        $params = [
            'indexRow' => $request->indexRow ?? 1,
            'limit' => $request->limit ?? 100,
            'page' => $request->page ?? 1,
            'sortname' => $request->sortname ?? 'id',
            'sortorder' => $request->sortorder ?? 'asc',
        ];

        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('kodeabsen', 300)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('statusaktif', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = AbsenTrado::select(
                'absentrado.id as id_',
                'absentrado.kodeabsen',
                'absentrado.keterangan',
                'absentrado.statusaktif',
                'absentrado.modifiedby',
                'absentrado.created_at',
                'absentrado.updated_at'
            )
                ->orderBy('absentrado.id', $params['sortorder']);
        } else if ($params['sortname'] == 'grp' or $params['sortname'] == 'subgrp') {
            $query = AbsenTrado::select(
                'absentrado.id as id_',
                'absentrado.kodeabsen',
                'absentrado.keterangan',
                'absentrado.statusaktif',
                'absentrado.modifiedby',
                'absentrado.created_at',
                'absentrado.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('parameter.text', $params['sortorder'])
                ->orderBy('absentrado.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = AbsenTrado::select(
                    'absentrado.id as id_',
                    'absentrado.kodeabsen',
                    'absentrado.keterangan',
                    'absentrado.statusaktif',
                    'absentrado.modifiedby',
                    'absentrado.created_at',
                    'absentrado.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('absentrado.id', $params['sortorder']);
            } else {
                $query = AbsenTrado::select(
                    'absentrado.id as id_',
                    'absentrado.kodeabsen',
                    'absentrado.keterangan',
                    'absentrado.statusaktif',
                    'absentrado.modifiedby',
                    'absentrado.created_at',
                    'absentrado.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('absentrado.id', 'asc');
            }
        }

        DB::table($temp)->insertUsing(['id_', 'kodeabsen', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);

        if ($del == 1) {
            if ($params['page'] == 1) {
                $baris = $params['indexRow'] + 1;
            } else {
                $hal = $params['page'] - 1;
                $bar = $hal * $params['limit'];
                $baris = $params['indexRow'] + $bar + 1;
            }


            if (DB::table($temp)
                ->where('id', '=', $baris)->exists()
            ) {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', $baris)
                    ->orderBy('id');
            } else {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', ($baris - 1))
                    ->orderBy('id');
            }
        } else {
            $querydata = DB::table($temp)
                ->select('id as row')
                ->where('id_', '=',  $id)
                ->orderBy('id');
        }


        $data = $querydata->first();
        return $data;
    }
}
