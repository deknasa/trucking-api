<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisOrder;
use App\Http\Requests\StoreJenisOrderRequest;
use App\Http\Requests\UpdateJenisOrderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JenisOrderController extends Controller
{
      /**
     * @ClassName 
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

        $totalRows = JenisOrder::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = JenisOrder::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = JenisOrder::select(
                'jenisorder.id',
                'jenisorder.kodejenisorder',
                'jenisorder.keterangan',
                'parameter.text as statusaktif',
                'jenisorder.modifiedby',
                'jenisorder.created_at',
                'jenisorder.updated_at'
            )
            ->leftJoin('parameter', 'jenisorder.statusaktif', '=', 'parameter.id')
            ->orderBy('jenisorder.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'kodejenisorder' or $params['sortIndex'] == 'keterangan') {
            $query = JenisOrder::select(
                'jenisorder.id',
                'jenisorder.kodejenisorder',
                'jenisorder.keterangan',
                'parameter.text as statusaktif',
                'jenisorder.modifiedby',
                'jenisorder.created_at',
                'jenisorder.updated_at'
            )
                ->leftJoin('parameter', 'jenisorder.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('jenisorder.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = JenisOrder::select(
                    'jenisorder.id',
                    'jenisorder.kodejenisorder',
                    'jenisorder.keterangan',
                    'parameter.text as statusaktif',
                    'jenisorder.modifiedby',
                    'jenisorder.created_at',
                    'jenisorder.updated_at'
                )
                    ->leftJoin('parameter', 'jenisorder.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('jenisorder.id', $params['sortOrder']);
            } else {
                $query = JenisOrder::select(
                    'jenisorder.id',
                    'jenisorder.kodejenisorder',
                    'jenisorder.keterangan',
                    'parameter.text as statusaktif',
                    'jenisorder.modifiedby',
                    'jenisorder.created_at',
                    'jenisorder.updated_at'
                )
                    ->leftJoin('parameter', 'jenisorder.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('jenisorder.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where('jenisorder.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('jenisorder.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
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

        $jenisorder = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $jenisorder,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }
      /**
     * @ClassName 
     */
    public function store(StoreJenisOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $jenisorder = new JenisOrder();
            $jenisorder->kodejenisorder = $request->kodejenisorder;
            $jenisorder->statusaktif = $request->statusaktif;
            $jenisorder->keterangan = $request->keterangan;
            $jenisorder->modifiedby = $request->modifiedby;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($jenisorder->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisorder->getTable()),
                    'postingdari' => 'ENTRY JENIS ORDER',
                    'idtrans' => $jenisorder->id,
                    'nobuktitrans' => $jenisorder->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $jenisorder->toArray(),
                    'modifiedby' => $jenisorder->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($jenisorder->id, $request, $del);
            $jenisorder->position = $data->row;

            if (isset($request->limit)) {
                $jenisorder->page = ceil($jenisorder->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenisorder
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
    public function show(JenisOrder $jenisorder)
    {
        return response([
            'status' => true,
            'data' => $jenisorder
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(JenisOrder $jenisOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJenisOrderRequest  $request
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
          /**
     * @ClassName 
     */
    public function update(StoreJenisOrderRequest $request, JenisOrder $jenisorder)
    {
        try {
            $jenisorder = JenisOrder::findOrFail($jenisorder->id);
            $jenisorder->kodejenisorder = $request->kodejenisorder;
            $jenisorder->keterangan = $request->keterangan;
            $jenisorder->statusaktif = $request->statusaktif;
            $jenisorder->modifiedby = $request->modifiedby;

            if ($jenisorder->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisorder->getTable()),
                    'postingdari' => 'EDIT JENIS ORDER',
                    'idtrans' => $jenisorder->id,
                    'nobuktitrans' => $jenisorder->id,
                    'aksi' => 'EDIT',
                    'datajson' => $jenisorder->toArray(),
                    'modifiedby' => $jenisorder->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $jenisorder->position = $this->getid($jenisorder->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $jenisorder->page = ceil($jenisorder->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $jenisorder
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
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
          /**
     * @ClassName 
     */
    public function destroy(JenisOrder $jenisorder, Request $request)
    {
        DB::beginTransaction();
        $delete = JenisOrder::destroy($jenisorder->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($jenisorder->getTable()),
                'postingdari' => 'DELETE JENIS ORDER',
                'idtrans' => $jenisorder->id,
                'nobuktitrans' => $jenisorder->id,
                'aksi' => 'DELETE',
                'datajson' => $jenisorder->toArray(),
                'modifiedby' => $jenisorder->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($jenisorder->id, $request, $del);
            $jenisorder->position = $data->row;
            $jenisorder->id = $data->id;
            if (isset($request->limit)) {
                $jenisorder->page = ceil($jenisorder->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $jenisorder
            ]);
        } else {
            DB::rollBack();
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenisorder')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getPosition($jenisorder, $request)
    {
        return JenisOrder::where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $jenisorder->{$request->sortname})
            /* Jika sortname modifiedby atau ada data duplikat */
            // ->where('id', $request->sortorder == 'desc' ? '>=' : '<=', $parameter->id)
            ->count();
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp'=>'status aktif'])->get(),
        ];

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
            $table->string('kodejenisorder', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = JenisOrder::select(
                'jenisorder.id as id_',
                'jenisorder.kodejenisorder',
                'jenisorder.keterangan',
                'jenisorder.statusaktif',
                'jenisorder.modifiedby',
                'jenisorder.created_at',
                'jenisorder.updated_at'
            )
                ->orderBy('jenisorder.id', $params['sortorder']);
        } else if ($params['sortname'] == 'kodejenisorder' or $params['sortname'] == 'keterangan') {
            $query = JenisOrder::select(
                'jenisorder.id as id_',
                'jenisorder.kodejenisorder',
                'jenisorder.keterangan',
                'jenisorder.statusaktif',
                'jenisorder.modifiedby',
                'jenisorder.created_at',
                'jenisorder.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('jenisorder.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = JenisOrder::select(
                    'jenisorder.id as id_',
                    'jenisorder.kodejenisorder',
                    'jenisorder.keterangan',
                    'jenisorder.statusaktif',
                    'jenisorder.modifiedby',
                    'jenisorder.created_at',
                    'jenisorder.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('jenisorder.id', $params['sortorder']);
            } else {
                $query = JenisOrder::select(
                    'jenisorder.id as id_',
                    'jenisorder.kodejenisorder',
                    'jenisorder.keterangan',
                    'jenisorder.statusaktif',
                    'jenisorder.modifiedby',
                    'jenisorder.created_at',
                    'jenisorder.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('jenisorder.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodejenisorder', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
