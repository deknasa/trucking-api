<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Http\Requests\ParameterRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 10,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = Parameter::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = Parameter::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = Parameter::select(
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
            $query = Parameter::select(
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
                $query = Parameter::select(
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
                $query = Parameter::select(
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
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
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
            $parameter->grp = strtoupper($request->grp);
            $parameter->subgrp = strtoupper($request->subgrp);
            $parameter->text = strtoupper($request->text);
            $parameter->memo = strtoupper($request->memo);
            $parameter->modifiedby = strtoupper($request->modifiedby);
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            $parameter->save();
            DB::commit();
            /* Set position and page */
            $del = 0;
            $data = $this->getid($parameter->id, $request, $del);
            $parameter->position = $data->row;

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
            return response($th->getMessage());
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
            $parameter = Parameter::findOrFail($parameter->id);
            $parameter->modifiedby = strtoupper($request->modifiedby);
            $parameter->grp = strtoupper($request->grp);
            $parameter->subgrp = strtoupper($request->subgrp);
            $parameter->text = strtoupper($request->text);
            $parameter->memo = strtoupper($request->memo);
            $parameter->modifiedby = strtoupper($request->modifiedby);

            if ($parameter->save()) {
                /* Set position and page */
                $parameter->position = $this->getPosition($parameter, $request);

                if (isset($request->limit)) {
                    $parameter->page = ceil($parameter->position / $request->limit);
                }

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
            return response($th->getMessage());
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
            $data = $this->getid($parameter->id, $request, $del);
            $parameter->position = $data->row;
            $parameter->id = $data->id;
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

    public function getPosition($parameter, $request)
    {
        return Parameter::where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $parameter->{$request->sortname})
            /* Jika sortname modifiedby atau ada data duplikat */
            // ->where('id', $request->sortorder == 'desc' ? '>=' : '<=', $parameter->id)
            ->count();
    }

    public function getid($id, $request, $del)
    {

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

        if ($request->sortname == 'id') {
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
                ->orderBy('parameter.id', $request->sortorder);
        } else if ($request->sortname == 'grp' or $request->sortname == 'subgrp') {
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
                ->orderBy($request->sortname, $request->sortorder)
                ->orderBy('parameter.text', $request->sortorder)
                ->orderBy('parameter.id', $request->sortorder);
        } else {
            if ($request->sortorder == 'asc') {
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
                    ->orderBy($request->sortname, $request->sortorder)
                    ->orderBy('parameter.id', $request->sortorder);
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
                    ->orderBy($request->sortname, $request->sortorder)
                    ->orderBy('parameter.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'grp', 'subgrp', 'text', 'memo', 'modifiedby', 'created_at', 'updated_at'], $query);


        if ($del == 1) {
            if ($request->page == 1) {
                $baris = $request->indexRow + 1;
            } else {
                $hal = $request->page - 1;
                $bar = $hal * $request->limit;
                $baris = $request->indexRow + $bar + 1;
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

        $querydata = Parameter::select('id as id')
            ->where('grp', '=',  $grp)
            ->where('subgrp', '=',  $subgrp)
            ->where('text', '=',  $text)
            ->orderBy('id');


        $data = $querydata->first();
        return $data;
    }
}
