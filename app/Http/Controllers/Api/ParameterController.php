<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Http\Requests\StoreParameterRequest;
use App\Http\Requests\UpdateParameterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Builder\Param;

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
            'limit' => $request->limit ?? 100,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = Parameter::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        $query = Parameter::orderBy($params['sortIndex'], $params['sortOrder']);

        /* Searching */
        if (count($params['search']) > 0) {
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
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
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
     * @param  \App\Http\Requests\StoreParameterRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreParameterRequest $request)
    {
        try {
            // $store = Parameter::create($request->validated());
            $parameter = new Parameter();
            $parameter->grp = $request->grp;
            $parameter->subgrp = $request->subgrp;
            $parameter->text = $request->text;
            $parameter->memo = $request->memo;

            if ($parameter->save()) {
                $data = $parameter;
                $data->position = Parameter::orderBy('grp', 'asc')
                    ->where('grp', '<=', $parameter->grp)
                    ->where('id', '<=', $parameter->id)
                    ->count();
                
                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $data
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
     * @param  \App\Http\Requests\UpdateParameterRequest  $request
     * @param  \App\Models\Parameter  $parameter
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateParameterRequest $request, Parameter $parameter)
    {
        try {
            $update = $parameter->update($request->validated());
            // $parameter = Parameter::findOrFail($parameter->id);
            // $parameter->modifiedby = $request->modifiedby;
            // $parameter->grp = $request->grp;
            // $parameter->subgrp = $request->subgrp;
            // $parameter->text = $request->text;
            // $parameter->memo = $request->memo;

            if ($update) {
                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah'
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
}
