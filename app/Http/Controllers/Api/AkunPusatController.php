<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AkunPusat;
use App\Http\Requests\StoreAkunPusatRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAkunPusatRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AkunPusatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @ClassName 
     */
    public function index()
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        $totalRows = DB::table((new AkunPusat)->getTable())->count();

        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = DB::table((new AkunPusat)->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new AkunPusat)->getTable())->select(
                'akunpusat.id',
                'akunpusat.coa',
                'akunpusat.keterangancoa',
                'akunpusat.type',
                'akunpusat.level',
                'parameter_statusaktif.text as statusaktif',
                'akunpusat.parent',
                'parameter_statuscoa.text as statuscoa',
                'parameter_statusaccountpayable.text as statusaccountpayable',
                'parameter_statusneraca.text as statusneraca',
                'parameter_statuslabarugi.text as statuslabarugi',
                'akunpusat.coamain',
                'akunpusat.modifiedby',
                'akunpusat.created_at',
                'akunpusat.updated_at'
            )
                ->leftJoin('parameter as parameter_statusaktif', 'akunpusat.statusaktif', '=', 'parameter_statusaktif.id')
                ->orderBy('akunpusat.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new AkunPusat)->getTable())->select(
                    'akunpusat.id',
                    'akunpusat.coa',
                    'akunpusat.keterangancoa',
                    'akunpusat.type',
                    'akunpusat.level',
                    'parameter_statusaktif.text as statusaktif',
                    'akunpusat.parent',
                    'parameter_statuscoa.text as statuscoa',
                    'parameter_statusaccountpayable.text as statusaccountpayable',
                    'parameter_statusneraca.text as statusneraca',
                    'parameter_statuslabarugi.text as statuslabarugi',
                    'akunpusat.coamain',
                    'akunpusat.modifiedby',
                    'akunpusat.created_at',
                    'akunpusat.updated_at'
                )
                    ->leftJoin('parameter as parameter_statusaktif', 'akunpusat.statusaktif', '=', 'parameter_statusaktif.id')
                    ->leftJoin('parameter as parameter_statuscoa', 'akunpusat.statuscoa', '=', 'parameter_statuscoa.id')
                    ->leftJoin('parameter as parameter_statusaccountpayable', 'akunpusat.statusaccountpayable', '=', 'parameter_statusaccountpayable.id')
                    ->leftJoin('parameter as parameter_statusneraca', 'akunpusat.statusneraca', '=', 'parameter_statusneraca.id')
                    ->leftJoin('parameter as parameter_statuslabarugi', 'akunpusat.statuslabarugi', '=', 'parameter_statuslabarugi.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('akunpusat.id', $params['sortOrder']);
            } else {
                $query = DB::table((new AkunPusat)->getTable())->select(
                    'akunpusat.id',
                    'akunpusat.coa',
                    'akunpusat.keterangancoa',
                    'akunpusat.type',
                    'akunpusat.level',
                    'parameter_statusaktif.text as statusaktif',
                    'akunpusat.parent',
                    'parameter_statuscoa.text as statuscoa',
                    'parameter_statusaccountpayable.text as statusaccountpayable',
                    'parameter_statusneraca.text as statusneraca',
                    'parameter_statuslabarugi.text as statuslabarugi',
                    'akunpusat.coamain',
                    'akunpusat.modifiedby',
                    'akunpusat.created_at',
                    'akunpusat.updated_at'
                )
                    ->leftJoin('parameter as parameter_statusaktif', 'akunpusat.statusaktif', '=', 'parameter_statusaktif.id')
                    ->leftJoin('parameter as parameter_statuscoa', 'akunpusat.statuscoa', '=', 'parameter_statuscoa.id')
                    ->leftJoin('parameter as parameter_statusaccountpayable', 'akunpusat.statusaccountpayable', '=', 'parameter_statusaccountpayable.id')
                    ->leftJoin('parameter as parameter_statusneraca', 'akunpusat.statusneraca', '=', 'parameter_statusneraca.id')
                    ->leftJoin('parameter as parameter_statuslabarugi', 'akunpusat.statuslabarugi', '=', 'parameter_statuslabarugi.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('akunpusat.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
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

        $akunPusats = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $akunPusats,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAkunPusatRequest  $request
     * @return \Illuminate\Http\Response
     */

    /**
     * @ClassName 
     */
    public function store(StoreAkunPusatRequest $request)
    {
        DB::beginTransaction();

        try {
            $akunPusat = new AkunPusat();
            $akunPusat->coa = $request->coa;
            $akunPusat->keterangancoa = $request->keterangancoa;
            $akunPusat->type = $request->type;
            $akunPusat->level = $request->level;
            $akunPusat->aktif = $request->aktif;
            $akunPusat->parent = $request->parent;
            $akunPusat->statuscoa = $request->statuscoa;
            $akunPusat->statusaccountpayable = $request->statusaccountpayable;
            $akunPusat->statusneraca = $request->statusneraca;
            $akunPusat->statuslabarugi = $request->statuslabarugi;
            $akunPusat->coamain = $request->coamain;
            $akunPusat->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($akunPusat->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($akunPusat->getTable()),
                    'postingdari' => 'ENTRY AKUN PUSAT',
                    'idtrans' => $akunPusat->id,
                    'nobuktitrans' => $akunPusat->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $akunPusat->toArray(),
                    'modifiedby' => $akunPusat->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($akunPusat->id, $request, $del);
            $akunPusat->position = $data->row;

            if (isset($request->limit)) {
                $akunPusat->page = ceil($akunPusat->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $akunPusat
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AkunPusat  $akunPusat
     * @return \Illuminate\Http\Response
     */
    public function show(AkunPusat $akunPusat)
    {
        return response([
            'status' => true,
            'data' => $akunPusat
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAkunPusatRequest  $request
     * @param  \App\Models\AkunPusat  $akunPusat
     * @return \Illuminate\Http\Response
     */
    /**
     * @ClassName 
     */
    public function update(UpdateAkunPusatRequest $request, AkunPusat $akunPusat)
    {
        try {
            $akunPusat = DB::table((new AkunPusat)->getTable())->findOrFail($akunPusat->id);
            $akunPusat->coa = $request->coa;
            $akunPusat->keterangancoa = $request->keterangancoa;
            $akunPusat->type = $request->type;
            $akunPusat->level = $request->level;
            $akunPusat->aktif = $request->aktif;
            $akunPusat->parent = $request->parent;
            $akunPusat->statuscoa = $request->statuscoa;
            $akunPusat->statusaccountpayable = $request->statusaccountpayable;
            $akunPusat->statusneraca = $request->statusneraca;
            $akunPusat->statuslabarugi = $request->statuslabarugi;
            $akunPusat->coamain = $request->coamain;
            $akunPusat->modifiedby = auth('api')->user()->name;

            if ($akunPusat->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($akunPusat->getTable()),
                    'postingdari' => 'EDIT AKUN PUSAT',
                    'idtrans' => $akunPusat->id,
                    'nobuktitrans' => $akunPusat->id,
                    'aksi' => 'EDIT',
                    'datajson' => $akunPusat->toArray(),
                    'modifiedby' => $akunPusat->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $akunPusat->position = $this->getid($akunPusat->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $akunPusat->page = ceil($akunPusat->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $akunPusat
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AkunPusat  $akunPusat
     * @return \Illuminate\Http\Response
     */
    /**
     * @ClassName 
     */
    public function destroy(AkunPusat $akunPusat, Request $request)
    {
        $delete = AkunPusat::destroy($akunPusat->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($akunPusat->getTable()),
                'postingdari' => 'DELETE AKUN PUSAT',
                'idtrans' => $akunPusat->id,
                'nobuktitrans' => $akunPusat->id,
                'aksi' => 'DELETE',
                'datajson' => $akunPusat->toArray(),
                'modifiedby' => $akunPusat->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($akunPusat->id, $request, $del);
            $akunPusat->position = $data->row;
            $akunPusat->id = $data->id;
            if (isset($request->limit)) {
                $akunPusat->page = ceil($akunPusat->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $akunPusat
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('akunPusat')->getColumns();

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
            $table->string('grp', 300)->default('');
            $table->string('subgrp', 300)->default('');
            $table->string('text', 300)->default('');
            $table->string('memo', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new AkunPusat)->getTable())->select(
                'akunpusat.id as id_',
                'akunpusat.coa',
                'akunpusat.keterangancoa',
                'akunpusat.type',
                'akunpusat.level',
                'parameter_statusaktif.text as statusaktif',
                'akunpusat.parent',
                'parameter_statuscoa.text as statuscoa',
                'parameter_statusaccountpayable.text as statusaccountpayable',
                'parameter_statusneraca.text as statusneraca',
                'parameter_statuslabarugi.text as statuslabarugi',
                'akunpusat.coamain',
                'akunpusat.modifiedby',
                'akunpusat.created_at',
                'akunpusat.updated_at'
            )
                ->orderBy('akunpusat.id', $params['sortorder']);
        } else if ($params['sortname'] == 'grp' or $params['sortname'] == 'subgrp') {
            $query = DB::table((new AkunPusat)->getTable())->select(
                'akunpusat.id as id_',
                'akunpusat.coa',
                'akunpusat.keterangancoa',
                'akunpusat.type',
                'akunpusat.level',
                'parameter_statusaktif.text as statusaktif',
                'akunpusat.parent',
                'parameter_statuscoa.text as statuscoa',
                'parameter_statusaccountpayable.text as statusaccountpayable',
                'parameter_statusneraca.text as statusneraca',
                'parameter_statuslabarugi.text as statuslabarugi',
                'akunpusat.coamain',
                'akunpusat.modifiedby',
                'akunpusat.created_at',
                'akunpusat.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('akunpusat.text', $params['sortorder'])
                ->orderBy('akunpusat.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new AkunPusat)->getTable())->select(
                    'akunpusat.id as id_',
                    'akunpusat.coa',
                    'akunpusat.keterangancoa',
                    'akunpusat.type',
                    'akunpusat.level',
                    'akunpusat.statusaktif',
                    'akunpusat.parent',
                    'parameter_statuscoa.text as statuscoa',
                    'parameter_statusaccountpayable.text as statusaccountpayable',
                    'parameter_statusneraca.text as statusneraca',
                    'parameter_statuslabarugi.text as statuslabarugi',
                    'akunpusat.coamain',
                    'akunpusat.modifiedby',
                    'akunpusat.created_at',
                    'akunpusat.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('akunpusat.id', $params['sortorder']);
            } else {
                $query = DB::table((new AkunPusat)->getTable())->select(
                    'akunpusat.id as id_',
                    'akunpusat.coa',
                    'akunpusat.keterangancoa',
                    'akunpusat.type',
                    'akunpusat.level',
                    'akunpusat.statusaktif',
                    'akunpusat.parent',
                    'parameter_statuscoa.text as statuscoa',
                    'parameter_statusaccountpayable.text as statusaccountpayable',
                    'parameter_statusneraca.text as statusneraca',
                    'parameter_statuslabarugi.text as statuslabarugi',
                    'akunpusat.coamain',
                    'akunpusat.modifiedby',
                    'akunpusat.created_at',
                    'akunpusat.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('akunpusat.id', 'asc');
            }
        }

        DB::table($temp)->insertUsing([
            'id_',
            'coa',
            'keterangancoa',
            'type',
            'level',
            'aktif',
            'parent',
            'statuscoa',
            'statusaccountpayable',
            'statusneraca',
            'statuslabarugi',
            'coamain',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $query);

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
