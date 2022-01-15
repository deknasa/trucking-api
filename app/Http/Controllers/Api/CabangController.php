<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Http\Requests\StoreCabangRequest;
use App\Http\Requests\UpdateCabangRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Requests;
use Illuminate\Support\Facades\DB;
use PhpParser\Builder\Param;

class CabangController extends Controller
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

        $totalRows = Cabang::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        $query = Cabang::orderBy($params['sortIndex'], $params['sortOrder']);

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

        $cabangs = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        return response([
            'status' => true,
            'data' => $cabangs,
            'attributes' => $attributes,
            'params' => $params
        ]);    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCabangRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCabangRequest $request)
    {
        try {
            $cabang = new Cabang();
            $cabang->grp = $request->grp;
            $cabang->subgrp = $request->subgrp;
            $cabang->text = $request->text;
            $cabang->memo = $request->memo;

            if ($cabang->save()) {
                /* Set position and page */
                $cabang->position = cabang::orderBy($request->sortname ?? 'id', $request->sortorder ?? 'asc')
                    ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $cabang->{$request->sortname})
                    ->where('id', '<=', $cabang->id)
                    ->count();

                if (isset($request->limit)) {
                    $cabang->page = ceil($cabang->position / $request->limit);
                }
                
                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $cabang
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal disimpan'
                ]);
            }
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cabang  $cabang
     * @return \Illuminate\Http\Response
     */
    public function show(Cabang $cabang)
    {
        return response([
            'status' => true,
            'data' => $cabang
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cabang  $cabang
     * @return \Illuminate\Http\Response
     */
    public function edit(Cabang $cabang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCabangRequest  $request
     * @param  \App\Models\Cabang  $cabang
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCabangRequest $request, Cabang $cabang)
    {
        try {
            $update = $cabang->update($request->validated());
            // $cabang = cabang::findOrFail($cabang->id);
            // $cabang->modifiedby = $request->modifiedby;
            // $cabang->grp = $request->grp;
            // $cabang->subgrp = $request->subgrp;
            // $cabang->text = $request->text;
            // $cabang->memo = $request->memo;

            if ($update) {
                /* Set position and page */
                $cabang->position = cabang::orderBy($request->sortname ?? 'id', $request->sortorder ?? 'asc')
                    ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $cabang->{$request->sortname})
                    ->where('id', '<=', $cabang->id)
                    ->count();

                if (isset($request->limit)) {
                    $cabang->page = ceil($cabang->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $cabang
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
     * @param  \App\Models\Cabang  $cabang
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cabang $cabang)
    {
        $delete = Cabang::destroy($cabang->id);

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
