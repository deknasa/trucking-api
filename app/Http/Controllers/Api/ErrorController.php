<?php

namespace App\Http\Controllers;





namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreErrorRequest;
use App\Http\Requests\UpdateErrorRequest;
use App\Http\Requests\DestroyErrorRequest;

use App\Models\Error;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

class ErrorController extends Controller
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

        $totalRows = Error::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = Error::select(
                'error.id',
                'error.keterangan',
                'error.modifiedby',
                'error.created_at',
                'error.updated_at'
            )
                ->orderBy('error.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = Error::select(
                    'error.id',
                    'error.keterangan',
                    'error.modifiedby',
                    'error.created_at',
                    'error.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('error.id', $params['sortOrder']);
            } else {
                $query = Error::select(
                    'error.id',
                    'error.keterangan',
                    'error.modifiedby',
                    'error.created_at',
                    'error.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('error.id', 'asc');
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

        $errors = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
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
    public function store(StoreErrorRequest $request)
    {
        DB::beginTransaction();
        try {
            $error = new Error();
            $error->keterangan = strtoupper($request->keterangan);
            $error->modifiedby = strtoupper($request->modifiedby);

            $error->save();

            $datajson = [
                'id' => $error->id,
                'keterangan' => strtoupper($request->keterangan),
                'modifiedby' => strtoupper($request->modifiedby),
            ];

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'ERROR';
            $logtrail->postingdari = 'ENTRY ERROR';
            $logtrail->idtrans = $error->id;
            $logtrail->nobuktitrans = $error->id;
            $logtrail->aksi = 'ENTRY';
            $logtrail->datajson = json_encode($datajson);

            $logtrail->save();

            DB::commit();
            /* Set position and page */
            $del = 0;
            $data = $this->getid($error->id, $request, $del);
            $error->position = $data->row;
            // dd($error->position );
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
    public function update(UpdateErrorRequest $request, Error $error)
    {
        DB::beginTransaction();
        try {
            $error->update(array_map('strtoupper', $request->validated()));

            $datajson = [
                'id' => $error->id,
                'keterangan' => strtoupper($request->keterangan),
                'modifiedby' => strtoupper($request->modifiedby),
            ];

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'KETERANGAN';
            $logtrail->postingdari = 'EDIT KETERANGAN';
            $logtrail->idtrans = $error->id;
            $logtrail->nobuktitrans = $error->id;
            $logtrail->aksi = 'EDIT';
            $logtrail->datajson = json_encode($datajson);

            $logtrail->save();
            DB::commit();

            /* Set position and page */
            $error->position = error::orderBy($request->sortname ?? 'id', $request->sortorder ?? 'asc')
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $error->{$request->sortname})
                ->where('id', '<=', $error->id)
                ->count();

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
    public function destroy(Error $error,DestroyErrorRequest $request)
    {
        DB::beginTransaction();
        try {

            Error::destroy($error->id);

            $datajson = [
                'id' => $error->id,
                'modifiedby' => strtoupper($request->modifiedby),
            ];

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'KETERANGAN';
            $logtrail->postingdari = 'DELETE KETERANGAN';
            $logtrail->idtrans = $error->id;
            $logtrail->nobuktitrans = $error->id;
            $logtrail->aksi = 'DELETE';
            $logtrail->datajson = json_encode($datajson);

            $logtrail->save();

            DB::commit();
            Error::destroy($error->id);
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

        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });



        if ($request->sortname == 'id') {
            $query = Error::select(
                'error.id as id_',
                'error.keterangan',
                'error.modifiedby',
                'error.created_at',
                'error.updated_at'
            )
                ->orderBy('error.id', $request->sortorder);
        } else {
            if ($request->sortorder == 'asc') {
                $query = Error::select(
                    'error.id as id_',
                    'error.keterangan',
                    'error.modifiedby',
                    'error.created_at',
                    'error.updated_at'
                )
                    ->orderBy($request->sortname, $request->sortorder)
                    ->orderBy('error.id', $request->sortorder);
            } else {
                $query = Error::select(
                    'error.id as id_',
                    'error.keterangan',
                    'error.modifiedby',
                    'error.created_at',
                    'error.updated_at'
                )
                    ->orderBy($request->sortname, $request->sortorder)

                    ->orderBy('error.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'keterangan', 'modifiedby', 'created_at', 'updated_at'], $query);


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
}
