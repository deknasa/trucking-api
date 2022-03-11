<?php


namespace App\Http\Controllers;

namespace App\Http\Controllers\Api;


use App\Models\LogTrail;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateLogTrailRequest;
use App\Http\Requests\DestroyLogTrailRequest;


use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class LogTrailController extends Controller
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

        $totalRows = LogTrail::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = LogTrail::select(
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
                $query = LogTrail::select(
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
                $query = LogTrail::select(
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

        $logtrails = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
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
        DB::beginTransaction();
        try {
            $LogTrail = new LogTrail();

            $LogTrail->namatabel = $request->namatabel;
            $LogTrail->postingdari = $request->postingdari;
            $LogTrail->idtrans = $request->idtrans;
            $LogTrail->nobuktitrans = $request->nobuktitrans;
            $LogTrail->aksi = $request->aksi;
            $LogTrail->datajson = $request->datajson;
            $LogTrail->modifiedby = $request->modifiedby;

            $LogTrail->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function show(LogTrail $logtrail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function edit(LogTrail $logtrail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatelogtrailRequest  $request
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLogTrailRequest $request, logtrail $logtrail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function destroy(LogTrail $logtrail)
    {
        //
    }

    public function header(Request $request)

    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 100,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        $query = LogTrail::select(
            'datajson',
            'namatabel',
        )
            ->where('id', '=',  $request->id);

        $data = $query->first();
        if (isset($data)) {

        

        $datajson = $data->datajson;
        $table_name = strtolower($data->namatabel);

        // dd($datajson);
        $temp = '##temp' . rand(1, 10000);

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

//  dump($fields);
//         dd($datajson);
        Schema::create($temp, function ($table)  use ($fields, $table_name) {
            if (count($fields) > 0) {
                foreach ($fields as $field) {
                    $table->{$field['type']}($field['name']);
                }
            }
        });



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

    public function detail(Request $request)

    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 100,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        
        $query = LogTrail::select(
            'datajson',
            'namatabel',
        )
            ->where('idtrans', '=',  $request->id);

        $data = $query->first();
        if (isset($data)) {
        $datajson = $data->datajson;
        $table_name = strtolower($data->namatabel);

        $temp = '##temp' . rand(1, 10000);
        
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

        // dd('test');
        Schema::create($temp, function ($table)  use ($fields, $table_name) {
            if (count($fields) > 0) {
                foreach ($fields as $field) {
                    $table->{$field['type']}($field['name']);
                }
            }
        });

        // dump($fields);
        // dd($datajson);
 
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
