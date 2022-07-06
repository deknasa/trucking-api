<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Http\Requests\ParameterRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Resources\Parameter as ResourcesParameter;
use App\Http\Resources\ParameterResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ParameterController extends Controller
{
    public function index()
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        /* Sorting */
        $query = DB::table((new Parameter)->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        $totalRows = $query->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new Parameter)->getTable())->select(
                'parameter.id',
                'parameter.grp',
                'parameter.subgrp',
                'parameter.text',
                'parameter.memo',
                'parameter.modifiedby',
                'parameter.created_at',
                'parameter.updated_at'
            )->orderBy('parameter.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'grp' or $params['sortIndex'] == 'subgrp') {
            $query = DB::table((new Parameter)->getTable())->select(
                'parameter.id',
                'parameter.grp',
                'parameter.subgrp',
                'parameter.text',
                'parameter.memo',
                'parameter.modifiedby',
                'parameter.created_at',
                'parameter.updated_at'
            )
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('parameter.text', $params['sortOrder'])
                ->orderBy('parameter.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new Parameter)->getTable())->select(
                    'parameter.id',
                    'parameter.grp',
                    'parameter.subgrp',
                    'parameter.text',
                    'parameter.memo',
                    'parameter.modifiedby',
                    'parameter.created_at',
                    'parameter.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('parameter.id', $params['sortOrder']);
            } else {
                $query = DB::table((new Parameter)->getTable())->select(
                    'parameter.id',
                    'parameter.grp',
                    'parameter.subgrp',
                    'parameter.text',
                    'parameter.memo',
                    'parameter.modifiedby',
                    'parameter.created_at',
                    'parameter.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('parameter.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        $query = $query->orWhere($filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                default:

                    break;
            }

            $totalRows = $query->count();
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $parameters = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $parameters,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ParameterRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ParameterRequest $request)
    {
        DB::beginTransaction();

        try {
            $parameter = new Parameter();
            $parameter->grp = $request->grp;
            $parameter->subgrp = $request->subgrp;
            $parameter->text = $request->text;
            $parameter->memo = $request->memo;
            $parameter->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($parameter->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($parameter->getTable()),
                    'postingdari' => 'ENTRY PARAMETER',
                    'idtrans' => $parameter->id,
                    'nobuktitrans' => $parameter->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $parameter->toArray(),
                    'modifiedby' => $parameter->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($parameter, $parameter->getTable());
            $parameter->position = $selected->position;
            $parameter->page = ceil($parameter->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $parameter->page = ceil($parameter->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $parameter
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Parameter  $parameter
     * @return \Illuminate\Http\Response
     */
    public function show(Parameter $parameter)
    {
        return response([
            'status' => true,
            'data' => $parameter
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ParameterRequest  $request
     * @param  \App\Models\Parameter  $parameter
     * @return \Illuminate\Http\Response
     */
    public function update(ParameterRequest $request, Parameter $parameter)
    {
        try {
            $parameter->grp = $request->grp;
            $parameter->subgrp = $request->subgrp;
            $parameter->text = $request->text;
            $parameter->memo = $request->memo;
            $parameter->modifiedby = auth('api')->user()->name;

            if ($parameter->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($parameter->getTable()),
                    'postingdari' => 'EDIT PARAMETER',
                    'idtrans' => $parameter->id,
                    'nobuktitrans' => $parameter->id,
                    'aksi' => 'EDIT',
                    'datajson' => $parameter->toArray(),
                    'modifiedby' => $parameter->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $selected = $this->getPosition($parameter, $parameter->getTable());
                $parameter->position = $selected->position;
                $parameter->page = ceil($parameter->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $parameter
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
     * @param  \App\Models\Parameter  $parameter
     * @return \Illuminate\Http\Response
     */
    public function destroy(Parameter $parameter, Request $request)
    {
        $delete = Parameter::destroy($parameter->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($parameter->getTable()),
                'postingdari' => 'DELETE PARAMETER',
                'idtrans' => $parameter->id,
                'nobuktitrans' => $parameter->id,
                'aksi' => 'DELETE',
                'datajson' => $parameter->toArray(),
                'modifiedby' => $parameter->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($parameter, $parameter->getTable(), true);
            $parameter->position = $selected->position;
            $parameter->id = $selected->id;

            if (isset($request->limit)) {
                $parameter->page = ceil($parameter->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $parameter
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('parameter')->getColumns();

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
            $query = Parameter::select(
                'parameter.id as id_',
                'parameter.grp',
                'parameter.subgrp',
                'parameter.text',
                'parameter.memo',
                'parameter.modifiedby',
                'parameter.created_at',
                'parameter.updated_at'
            )
                ->orderBy('parameter.id', $params['sortorder']);
        } else if ($params['sortname'] == 'grp' or $params['sortname'] == 'subgrp') {
            $query = Parameter::select(
                'parameter.id as id_',
                'parameter.grp',
                'parameter.subgrp',
                'parameter.text',
                'parameter.memo',
                'parameter.modifiedby',
                'parameter.created_at',
                'parameter.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('parameter.text', $params['sortorder'])
                ->orderBy('parameter.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = Parameter::select(
                    'parameter.id as id_',
                    'parameter.grp',
                    'parameter.subgrp',
                    'parameter.text',
                    'parameter.memo',
                    'parameter.modifiedby',
                    'parameter.created_at',
                    'parameter.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('parameter.id', $params['sortorder']);
            } else {
                $query = Parameter::select(
                    'parameter.id as id_',
                    'parameter.grp',
                    'parameter.subgrp',
                    'parameter.text',
                    'parameter.memo',
                    'parameter.modifiedby',
                    'parameter.created_at',
                    'parameter.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('parameter.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'grp', 'subgrp', 'text', 'memo', 'modifiedby', 'created_at', 'updated_at'], $query);


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

    public function getparameterid($grp, $subgrp, $text)
    {

        $querydata = Parameter::select('id as id', 'text')
            ->where('grp', '=',  $grp)
            ->where('subgrp', '=',  $subgrp)
            ->where('text', '=',  $text)
            ->orderBy('id');


        $data = $querydata->first();
        return $data;
    }

    public function export()
    {
        header('Access-Control-Allow-Origin: *');

        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $parameters = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Group',
                'index' => 'grp',
            ],
            [
                'label' => 'Subgroup',
                'index' => 'subgrp',
            ],
            [
                'label' => 'Text',
                'index' => 'text',
            ],
            [
                'label' => 'Memo',
                'index' => 'memo',
            ],
        ];

        $this->toExcel('Parameter', $parameters, $columns);
    }

    public function combo(Request $request)
    {
        $parameters = Parameter::where('grp', '=', $request->grp)
            ->where('subgrp', '=', $request->subgrp)
            ->get();

        return response([
            'data' => $parameters
        ]);
    }
}
