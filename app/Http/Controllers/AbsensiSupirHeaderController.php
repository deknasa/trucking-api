<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbsensiSupirHeaderRequest;
use App\Models\AbsensiSupirHeader;
use Illuminate\Http\Request;

class AbsensiSupirHeaderController extends Controller
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

        $totalRows = AbsensiSupirHeader::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        $query = AbsensiSupirHeader::orderBy($params['sortIndex'], $params['sortOrder']);

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
     * @param  \App\Http\Requests\StoreAbsensiSupirHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAbsensiSupirHeaderRequest $request)
    {
        try {
            $parameter = new AbsensiSupirHeader();
            $parameter->nobukti = $request->nobukti;
            $parameter->tgl = $request->tgl;
            $parameter->keterangan = $request->keterangan;
            $parameter->kasgantung_nobukti = $request->kasgantung_nobukti;
            $parameter->nominal = $request->nominal;
            $parameter->modifiedby = $request->modifiedby;

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($parameter->save()) {
                /* Set position and page */
                $parameter->position = AbsensiSupirHeader::orderBy($request->sortname, $request->sortorder)
                    ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $parameter->{$request->sortname})
                    ->where('id', '<=', $parameter->id)
                    ->count();

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
}
