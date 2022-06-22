<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreErrorRequest;
use App\Http\Requests\UpdateErrorRequest;
use App\Http\Requests\DestroyErrorRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\Error;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\LogTrailController;

class ErrorController extends Controller
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

        $totalRows = DB::table((new Error)->getTable())->count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new Error)->getTable())->select(
                'error.id',
                'error.kodeerror',
                'error.keterangan',
                'error.modifiedby',
                'error.created_at',
                'error.updated_at'
            )
                ->orderBy('error.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new Error)->getTable())->select(
                    'error.id',
                    'error.kodeerror',
                    'error.keterangan',
                    'error.modifiedby',
                    'error.created_at',
                    'error.updated_at'
                )
                    ->orderBy('error.' . $params['sortIndex'], $params['sortOrder'])
                    ->orderBy('error.id', $params['sortOrder']);
            } else {
                $query = DB::table((new Error)->getTable())->select(
                    'error.id',
                    'error.kodeerror',
                    'error.keterangan',
                    'error.modifiedby',
                    'error.created_at',
                    'error.updated_at'
                )
                    ->orderBy('error.' . $params['sortIndex'], $params['sortOrder'])
                    ->orderBy('error.id', 'asc');
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

        $errors = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        return response([
            'status' => true,
            'data' => $errors,
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
     * @param  \App\Http\Requests\StoreErrorRequest  $request
     * @return \Illuminate\Http\Response
     */
          /**
     * @ClassName 
     */
    public function store(StoreErrorRequest $request)
    {
        DB::beginTransaction();
        try {
            $error = new Error();
            $error->kodeerror = $request->kodeerror;
            $error->keterangan = $request->keterangan;
            $error->modifiedby = auth('api')->user()->name;

            if ($error->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($error->getTable()),
                    'postingdari' => 'ENTRY ERROR',
                    'idtrans' => $error->id,
                    'nobuktitrans' => $error->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $error->toArray(),
                    'modifiedby' => $error->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($error->id, $request, $del);
            $error->position = $data->row;

            if (isset($request->limit)) {
                $error->page = ceil($error->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $error
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Error  $error
     * @return \Illuminate\Http\Response
     */
    public function show(Error $error)
    {
        return response([
            'status' => true,
            'data' => $error
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Error  $error
     * @return \Illuminate\Http\Response
     */
    public function edit(Error $error)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateErrorRequest  $request
     * @param  \App\Models\Error  $error
     * @return \Illuminate\Http\Response
     */
          /**
     * @ClassName 
     */
    public function update(UpdateErrorRequest $request, Error $error)
    {
        DB::beginTransaction();
        try {
            $error->kodeerror = $request->kodeerror;
            $error->keterangan = $request->keterangan;
            $error->modifiedby = auth('api')->user()->name;

            if ($error->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($error->getTable()),
                    'postingdari' => 'EDIT ERROR',
                    'idtrans' => $error->id,
                    'nobuktitrans' => $error->id,
                    'aksi' => 'EDIT',
                    'datajson' => $error->toArray(),
                    'modifiedby' => $error->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $error->position = $this->getid($error->id, $request, 0)->row;


            if (isset($request->limit)) {
                $error->page = ceil($error->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $error
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Error  $error
     * @return \Illuminate\Http\Response
     */
          /**
     * @ClassName 
     */
    public function destroy(Error $error, Request $request)
    {
        DB::beginTransaction();
        try {
            if ($error->delete()) {
                $logTrail = [
                    'namatabel' => strtoupper($error->getTable()),
                    'postingdari' => 'DELETE ERROR',
                    'idtrans' => $error->id,
                    'nobuktitrans' => $error->id,
                    'aksi' => 'DELETE',
                    'datajson' => $error->toArray(),
                    'modifiedby' => $error->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            $del = 1;
            $data = $this->getid($error->id, $request, $del);
            $error->position = $data->row;
            $error->id = $data->id;
            if (isset($request->limit)) {
                $error->page = ceil($error->position / $request->limit);
            }
            // dd($error);
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $error
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $errors = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Kode Error',
                'index' => 'kodeerror',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
        ];

        $this->toExcel('Error', $errors, $columns);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('error')->getColumns();

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
            $table->string('kodeerror', 50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });



        if ($params['sortname'] == 'id') {
            $query = Error::select(
                'error.id as id_',
                'error.kodeerror',
                'error.keterangan',
                'error.modifiedby',
                'error.created_at',
                'error.updated_at'
            )
                ->orderBy('error.id', $params['sortorder']);
        } else {
            if ($request->sortorder == 'asc') {
                $query = Error::select(
                    'error.id as id_',
                    'error.kodeerror',
                    'error.keterangan',
                    'error.modifiedby',
                    'error.created_at',
                    'error.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('error.id', $params['sortorder']);
            } else {
                $query = Error::select(
                    'error.id as id_',
                    'error.kodeerror',
                    'error.keterangan',
                    'error.modifiedby',
                    'error.created_at',
                    'error.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])

                    ->orderBy('error.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodeerror', 'keterangan', 'modifiedby', 'created_at', 'updated_at'], $query);


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
    public function geterror($kodeerror)
    {
        // dd($request->aco_id);

        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->string('keterangan', 50)->default('');
        });

        DB::table($temp)->insert(
            [
                'keterangan' => 'kode error belum terdaftar',
            ]
        );

        if (Error::select('keterangan')
            ->where('kodeerror', '=', $kodeerror)
            ->exists()
        ) {
            $data = Error::select('keterangan')
                ->where('kodeerror', '=', $kodeerror)
                ->first();
        } else {
            $data = DB::table($temp)
                ->select('keterangan')
                ->first();
        }

        return $data;
    }
}
