<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisEmkl;
use App\Http\Requests\StoreJenisEmklRequest;
use App\Http\Requests\UpdateJenisEmklRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JenisEmklController extends Controller
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

        $totalRows = JenisEmkl::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = JenisEmkl::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = JenisEmkl::select(
                'jenisemkl.id',
                'jenisemkl.kodejenisemkl',
                'jenisemkl.keterangan',
                'jenisemkl.modifiedby',
                'jenisemkl.created_at',
                'jenisemkl.updated_at'
            )->orderBy('jenisemkl.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'kodejenisemkl' or $params['sortIndex'] == 'keterangan') {
            $query = JenisEmkl::select(
                'jenisemkl.id',
                'jenisemkl.kodejenisemkl',
                'jenisemkl.keterangan',
                'jenisemkl.modifiedby',
                'jenisemkl.created_at',
                'jenisemkl.updated_at'
            )
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('jenisemkl.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = JenisEmkl::select(
                    'jenisemkl.id',
                    'jenisemkl.kodejenisemkl',
                    'jenisemkl.keterangan',
                    'jenisemkl.modifiedby',
                    'jenisemkl.created_at',
                    'jenisemkl.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('jenisemkl.id', $params['sortOrder']);
            } else {
                $query = JenisEmkl::select(
                    'jenisemkl.id',
                    'jenisemkl.kodejenisemkl',
                    'jenisemkl.keterangan',
                    'jenisemkl.modifiedby',
                    'jenisemkl.created_at',
                    'jenisemkl.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('jenisemkl.id', 'asc');
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

        $jenisemkl = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $jenisemkl,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function create()
    {
        //
    }

    public function store(StoreJenisEmklRequest $request)
    {
        DB::beginTransaction();

        try {
            $jenisemkl = new JenisEmkl();
            $jenisemkl->kodejenisemkl = $request->kodejenisemkl;
            $jenisemkl->keterangan = $request->keterangan;
            $jenisemkl->modifiedby = $request->modifiedby;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($jenisemkl->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisemkl->getTable()),
                    'postingdari' => 'ENTRY JENISEMKL',
                    'idtrans' => $jenisemkl->id,
                    'nobuktitrans' => $jenisemkl->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $jenisemkl->toArray(),
                    'modifiedby' => $jenisemkl->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($jenisemkl->id, $request, $del);
            $jenisemkl->position = $data->row;

            if (isset($request->limit)) {
                $jenisemkl->page = ceil($jenisemkl->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenisemkl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(JenisEmkl $jenisemkl)
    {
        return response([
            'status' => true,
            'data' => $jenisemkl
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JenisEmkl  $jenisEmkl
     * @return \Illuminate\Http\Response
     */
    public function edit(JenisEmkl $jenisEmkl)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJenisEmklRequest  $request
     * @param  \App\Models\JenisEmkl  $jenisEmkl
     * @return \Illuminate\Http\Response
     */
    public function update(StoreJenisEmklRequest $request, JenisEmkl $jenisemkl)
    {
        try {
            $jenisemkl = JenisEmkl::findOrFail($jenisemkl->id);
            $jenisemkl->kodejenisemkl = $request->kodejenisemkl;
            $jenisemkl->keterangan = $request->keterangan;
            $jenisemkl->modifiedby = $request->modifiedby;

            if ($jenisemkl->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisemkl->getTable()),
                    'postingdari' => 'EDIT JENISEMKL',
                    'idtrans' => $jenisemkl->id,
                    'nobuktitrans' => $jenisemkl->id,
                    'aksi' => 'EDIT',
                    'datajson' => $jenisemkl->toArray(),
                    'modifiedby' => $jenisemkl->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $jenisemkl->position = $this->getid($jenisemkl->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $jenisemkl->page = ceil($jenisemkl->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $jenisemkl
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JenisEmkl  $jenisEmkl
     * @return \Illuminate\Http\Response
     */
    public function destroy(JenisEmkl $jenisemkl, Request $request)
    {
        $delete = JenisEmkl::destroy($jenisemkl->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($jenisemkl->getTable()),
                'postingdari' => 'DELETE JENISEMKL',
                'idtrans' => $jenisemkl->id,
                'nobuktitrans' => $jenisemkl->id,
                'aksi' => 'DELETE',
                'datajson' => $jenisemkl->toArray(),
                'modifiedby' => $jenisemkl->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($jenisemkl->id, $request, $del);
            $jenisemkl->position = $data->row;
            $jenisemkl->id = $data->id;
            if (isset($request->limit)) {
                $jenisemkl->page = ceil($jenisemkl->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $jenisemkl
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenisemkl')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getPosition($jenisemkl, $request)
    {
        return JenisEmkl::where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $jenisemkl->{$request->sortname})
            /* Jika sortname modifiedby atau ada data duplikat */
            // ->where('id', $request->sortorder == 'desc' ? '>=' : '<=', $parameter->id)
            ->count();
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
            $table->string('kodejenisemkl', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = JenisEmkl::select(
                'jenisemkl.id as id_',
                'jenisemkl.kodejenisemkl',
                'jenisemkl.keterangan',
                'jenisemkl.modifiedby',
                'jenisemkl.created_at',
                'jenisemkl.updated_at'
            )
                ->orderBy('jenisemkl.id', $params['sortorder']);
        } else if ($params['sortname'] == 'kodejenisemkl' or $params['sortname'] == 'keterangan') {
            $query = JenisEmkl::select(
                'jenisemkl.id as id_',
                'jenisemkl.kodejenisemkl',
                'jenisemkl.keterangan',
                'jenisemkl.modifiedby',
                'jenisemkl.created_at',
                'jenisemkl.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('jenisemkl.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = JenisEmkl::select(
                    'jenisemkl.id as id_',
                    'jenisemkl.kodejenisemkl',
                    'jenisemkl.keterangan',
                    'jenisemkl.modifiedby',
                    'jenisemkl.created_at',
                    'jenisemkl.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('jenisemkl.id', $params['sortorder']);
            } else {
                $query = JenisEmkl::select(
                    'jenisemkl.id as id_',
                    'jenisemkl.kodejenisemkl',
                    'jenisemkl.keterangan',
                    'jenisemkl.modifiedby',
                    'jenisemkl.created_at',
                    'jenisemkl.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('jenisemkl.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodejenisemkl', 'keterangan', 'modifiedby', 'created_at', 'updated_at'], $query);


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
}
