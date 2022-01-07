<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Http\Requests\StoreParameterRequest;
use App\Http\Requests\UpdateParameterRequest;
use Illuminate\Http\Request;
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
        $attributes = [];
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 100,
        ];

        $parameters = Parameter::skip($params['offset'])
                        ->take($params['limit'])
                        ->get();

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
            $parameter = new Parameter();
            $parameter->modifiedby = $request->modifiedby;
            $parameter->grp = $request->grp;
            $parameter->subgrp = $request->subgrp;
            $parameter->text = $request->text;
            $parameter->memo = $request->memo;

            if ($parameter->save()) {
                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan'
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
            $parameter = Parameter::findOrFail($parameter->id);
            $parameter->modifiedby = $request->modifiedby;
            $parameter->grp = $request->grp;
            $parameter->subgrp = $request->subgrp;
            $parameter->text = $request->text;
            $parameter->memo = $request->memo;

            if ($parameter->save()) {
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
        Parameter::destroy($parameter->id);
    }
}
