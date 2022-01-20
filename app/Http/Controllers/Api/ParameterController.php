<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Http\Requests\ParameterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        $query = Parameter::orderBy($params['sortIndex'], $params['sortOrder']);

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
            $totalPages = ceil($totalRows / $params['limit']);
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
        try {
            $parameter = new Parameter();
            $parameter->grp = $request->grp;
            $parameter->subgrp = $request->subgrp;
            $parameter->text = $request->text;
            $parameter->memo = $request->memo;
            $parameter->modifiedby = Auth::user()->name ?? 'ADMIN';
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($parameter->save()) {
                /* Set position and page */
                $parameter->position = $this->getPosition($parameter, $request);

                if (isset($request->limit)) {
                    $parameter->page = ceil($parameter->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $parameter
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal disimpan'
                ]);
            }
        } catch (\Throwable $th) {
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
            $parameter->modifiedby = $request->modifiedby;
            $parameter->grp = $request->grp;
            $parameter->subgrp = $request->subgrp;
            $parameter->text = $request->text;
            $parameter->memo = $request->memo;
            $parameter->modifiedby = $request->modifiedby ?? 'ADMIN';

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
    public function destroy(Parameter $parameter)
    {
        $delete = Parameter::destroy($parameter->id);

        if ($delete) {
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus'
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
}
