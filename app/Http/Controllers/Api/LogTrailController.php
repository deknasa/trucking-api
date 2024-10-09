<?php

namespace App\Http\Controllers\Api;

use App\Models\LogTrail;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateLogTrailRequest;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

class LogTrailController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        $totalRows = DB::table((new LogTrail)->getTable())->count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new LogTrail)->getTable())->select(
                'logtrail.id',
                'logtrail.namatabel',
                'logtrail.postingdari',
                'logtrail.idtrans',
                'logtrail.nobuktitrans',
                'logtrail.aksi',
                'logtrail.modifiedby',
                'logtrail.created_at',
                'logtrail.updated_at'
            )
                ->orderBy('logtrail.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new LogTrail)->getTable())->select(
                    'logtrail.id',
                    'logtrail.namatabel',
                    'logtrail.postingdari',
                    'logtrail.idtrans',
                    'logtrail.nobuktitrans',
                    'logtrail.aksi',
                    'logtrail.modifiedby',
                    'logtrail.created_at',
                    'logtrail.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('logtrail.id', $params['sortOrder']);
            } else {
                $query = DB::table((new LogTrail)->getTable())->select(
                    'logtrail.id',
                    'logtrail.namatabel',
                    'logtrail.postingdari',
                    'logtrail.idtrans',
                    'logtrail.nobuktitrans',
                    'logtrail.aksi',
                    'logtrail.modifiedby',
                    'logtrail.created_at',
                    'logtrail.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('logtrail.id', 'asc');
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


            $totalRows = count($query->get());

            $totalPages = ceil($totalRows / $params['limit']);
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $logtrails = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        return response([
            'status' => true,
            'data' => $logtrails,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

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
     * @param  \App\Http\Requests\StorelogtrailRequest  $request
     * @return \Illuminate\Http\Response
     */

    public function store(StoreLogTrailRequest $request)
    {
        $LogTrail = new LogTrail();

        $LogTrail->namatabel = $request->namatabel;
        $LogTrail->postingdari = $request->postingdari;
        $LogTrail->idtrans = $request->idtrans;
        $LogTrail->nobuktitrans = $request->nobuktitrans;
        $LogTrail->aksi = $request->aksi;
        $LogTrail->datajson = $request->datajson;
        $LogTrail->modifiedby = auth('api')->user()->name;

        if (!$LogTrail->save()) {
            throw new \Exception("Gagal menyimpan logtrail.", 1);
        }

        if ($LogTrail->save()) {
            DB::commit();
            return [
                'error' => false,
                'id' => $LogTrail->id
            ];
        }
    }

    /**
     * @ClassName 
     */
    public function header(Request $request)
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        $query = LogTrail::select(
            'datajson',
            'namatabel',
        )->where('id', '=',  $request->id);

        $data = $query->first();

        if (isset($data)) {
            $datajson = $data->datajson ?? [];
            $table_name = strtolower($data->namatabel);

            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            $fields = [];
            $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails($table_name)->getColumns();

            foreach ($columns as $index => $column) {
                $type = DB::connection()->getDoctrineColumn($table_name, $column->getName())->getType()->getName();

                if ($type == 'bigint') {
                    $type = 'biginteger';
                } elseif ($type == 'string') {
                    $type = 'longText';
                }

                $fields[] = [
                    'name' => $column->getName(),
                    'type' => $type,
                ];
            };

            Schema::create($temp, function ($table)  use ($fields, $table_name) {
                if (count($fields) > 0) {
                    foreach ($fields as $field) {
                        $table->{$field['type']}($field['name'])->nullable();
                    }
                }
            });

            if (isset($datajson[0]) && is_array($datajson[0])) {
                foreach ($datajson as $index => &$json) {
                    $datajson[$index]['created_at'] = date('m/d/Y H:i:s', strtotime($datajson[$index]['created_at']));
                    $datajson[$index]['updated_at'] = date('m/d/Y H:i:s', strtotime($datajson[$index]['updated_at']));
                }
            } else {
                $datajson['created_at'] = date('m/d/Y H:i:s', strtotime($datajson['created_at']));
                $datajson['updated_at'] = date('m/d/Y H:i:s', strtotime($datajson['updated_at']));
            }

            DB::table($temp)->insert($datajson);

            $totalRows = DB::table($temp)->count();
            $totalPages = ceil($totalRows / $params['limit']);

            /* Sorting */
            if ($params['sortIndex'] == 'id') {
                $query = DB::table($temp)
                    ->orderBy('id', $params['sortOrder']);
            } else {
                if ($params['sortOrder'] == 'asc') {
                    $query = DB::table($temp)
                        ->orderBy($params['sortIndex'], $params['sortOrder'])
                        ->orderBy('id', $params['sortOrder']);
                } else {
                    $query = DB::table($temp)
                        ->orderBy($params['sortIndex'], $params['sortOrder'])
                        ->orderBy('logtrail.id', 'asc');
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


                $totalRows = count($query->get());

                $totalPages = ceil($totalRows / $params['limit']);
            }

            /* Paging */
            $query = $query->skip($params['offset'])
                ->take($params['limit']);

            $logtrails = $query->get();

            /* Set attributes */
            $attributes = [
                'totalRows' => $totalRows,
                'totalPages' => $totalPages
            ];
        }

        return response([
            'status' => true,
            'data' => $logtrails ?? [],
            'attributes' => $attributes ?? [],
            'params' => $params
        ]);
    }
    /**
     * @ClassName 
     */
    public function detail(Request $request)
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        $query = LogTrail::select(
            'datajson',
            'namatabel',
        )->where('idtrans', '=',  $request->id);

        $data = $query->first();

        if (isset($data)) {
            $datajson = $data->datajson;
            $table_name = strtolower($data->namatabel);
            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            $fields = [];
            $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails($table_name)->getColumns();

            foreach ($columns as $index => $column) {
                $type = DB::connection()->getDoctrineColumn($table_name, $column->getName())->getType()->getName();

                if ($type == 'bigint') {
                    $type = 'biginteger';
                } elseif ($type == 'string') {
                    $type = 'longText';
                }

                $fields[] = [
                    'name' => $column->getName(),
                    'type' => $type,
                ];
            };

            Schema::create($temp, function ($table)  use ($fields, $table_name) {
                if (count($fields) > 0) {
                    foreach ($fields as $field) {
                        $table->{$field['type']}($field['name'])->nullable();
                    }
                }
            });

            if (isset($datajson[0]) && is_array($datajson[0])) {
                foreach ($datajson as $index => &$json) {
                    $datajson[$index]['created_at'] = date('m/d/Y H:i:s', strtotime($datajson[$index]['created_at']));
                    $datajson[$index]['updated_at'] = date('m/d/Y H:i:s', strtotime($datajson[$index]['updated_at']));
                }
            } else {
                $datajson['created_at'] = date('m/d/Y H:i:s', strtotime($datajson['created_at']));
                $datajson['updated_at'] = date('m/d/Y H:i:s', strtotime($datajson['updated_at']));
            }

            DB::table($temp)->insert($datajson);

            $totalRows = DB::table($temp)->count();
            $totalPages = ceil($totalRows / $params['limit']);

            /* Sorting */
            if ($params['sortIndex'] == 'id') {
                $query = DB::table($temp)
                    ->orderBy('id', $params['sortOrder']);
            } else {
                if ($params['sortOrder'] == 'asc') {
                    $query = DB::table($temp)
                        ->orderBy($params['sortIndex'], $params['sortOrder'])
                        ->orderBy('id', $params['sortOrder']);
                } else {
                    $query = DB::table($temp)
                        ->orderBy($params['sortIndex'], $params['sortOrder'])
                        ->orderBy('logtrail.id', 'asc');
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


                $totalRows = count($query->get());

                $totalPages = ceil($totalRows / $params['limit']);
            }

            /* Paging */
            $query = $query->skip($params['offset'])
                ->take($params['limit']);

            $logtrails = $query->get();

            /* Set attributes */
            $attributes = [
                'totalRows' => $totalRows,
                'totalPages' => $totalPages
            ];
        }
        return response([
            'status' => true,
            'data' => $logtrails ?? [],
            'attributes' => $attributes ?? [],
            'params' => $params
        ]);
    }
}
