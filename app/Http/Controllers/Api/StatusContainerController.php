<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\StatusContainer;
use App\Http\Requests\StoreStatusContainerRequest;
use App\Http\Requests\UpdateStatusContainerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatusContainerController extends Controller
{
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

        /* Sorting */
        $query = DB::table((new StatusContainer())->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        $totalRows = $query->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new StatusContainer())->getTable())->select(
                'statuscontainer.id',
                'statuscontainer.kodestatuscontainer',
                'statuscontainer.keterangan',
                'parameter.text as statusaktif',
                'statuscontainer.modifiedby',
                'statuscontainer.created_at',
                'statuscontainer.updated_at'
            )
                ->leftJoin('parameter', 'statuscontainer.statusaktif', '=', 'parameter.id')
                ->orderBy('statuscontainer.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new StatusContainer())->getTable())->select(
                    'statuscontainer.id',
                    'statuscontainer.kodestatuscontainer',
                    'statuscontainer.keterangan',
                    'parameter.text as statusaktif',
                    'statuscontainer.modifiedby',
                    'statuscontainer.created_at',
                    'statuscontainer.updated_at'
                )
                    ->leftJoin('parameter', 'statuscontainer.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('statuscontainer.id', $params['sortOrder']);
            } else {
                $query = DB::table((new StatusContainer())->getTable())->select(
                    'statuscontainer.id',
                    'statuscontainer.kodestatuscontainer',
                    'statuscontainer.keterangan',
                    'parameter.text as statusaktif',
                    'statuscontainer.modifiedby',
                    'statuscontainer.created_at',
                    'statuscontainer.updated_at'
                )
                    ->leftJoin('parameter', 'statuscontainer.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('statuscontainer.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where('statuscontainer.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere('statuscontainer.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }

            $totalRows = $query->count();
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $statusContainers = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $statusContainers,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function show(StatusContainer $statusContainer)
    {
        return response([
            'status' => true,
            'data' => $statusContainer
        ]);
    }
 /**
     * @ClassName 
     */
    public function store(StoreStatusContainerRequest $request)
    {
        DB::beginTransaction();

        try {
            $statusContainer = new StatusContainer();
            $statusContainer->kodestatuscontainer = $request->kodestatuscontainer;
            $statusContainer->keterangan = $request->keterangan;
            $statusContainer->statusaktif = $request->statusaktif;
            $statusContainer->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($statusContainer->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($statusContainer->getTable()),
                    'postingdari' => 'ENTRY STATUS CONTAINER',
                    'idtrans' => $statusContainer->id,
                    'nobuktitrans' => $statusContainer->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $statusContainer->toArray(),
                    'modifiedby' => $statusContainer->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($statusContainer->id, $request, $del);
            $statusContainer->position = $data->row;

            if (isset($request->limit)) {
                $statusContainer->page = ceil($statusContainer->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $statusContainer
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

 /**
     * @ClassName 
     */
    public function update(UpdateStatusContainerRequest $request, StatusContainer $statusContainer)
    {
        try {
            $statusContainer = new StatusContainer();
            $statusContainer->kodestatuscontainer = $request->kodestatuscontainer;
            $statusContainer->keterangan = $request->keterangan;
            $statusContainer->statusaktif = $request->statusaktif;
            $statusContainer->modifiedby = auth('api')->user()->name;

            if ($statusContainer->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($statusContainer->getTable()),
                    'postingdari' => 'EDIT STATUS CONTAINER',
                    'idtrans' => $statusContainer->id,
                    'nobuktitrans' => $statusContainer->id,
                    'aksi' => 'EDIT',
                    'datajson' => $statusContainer->toArray(),
                    'modifiedby' => $statusContainer->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $statusContainer->position = $this->getid($statusContainer->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $statusContainer->page = ceil($statusContainer->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $statusContainer
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
     * @ClassName 
     */
    public function destroy(StatusContainer $statusContainer, Request $request)
    {
        $delete = StatusContainer::destroy($statusContainer->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($statusContainer->getTable()),
                'postingdari' => 'DELETE STATUS CONTAINER',
                'idtrans' => $statusContainer->id,
                'nobuktitrans' => $statusContainer->id,
                'aksi' => 'DELETE',
                'datajson' => $statusContainer->toArray(),
                'modifiedby' => $statusContainer->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($statusContainer->id, $request, $del);
            $statusContainer->position = $data->row;
            $statusContainer->id = $data->id;
            if (isset($request->limit)) {
                $statusContainer->page = ceil($statusContainer->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $statusContainer
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('statuscontainer')->getColumns();

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
            $table->string('kodestatuscontainer', 300)->default('');
            $table->string('keterangan', 300)->default('');
            $table->string('statusaktif', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = StatusContainer::select(
                'statuscontainer.id as id_',
                'statuscontainer.kodestatuscontainer',
                'statuscontainer.keterangan',
                'parameter.text as statusaktif',
                'statuscontainer.modifiedby',
                'statuscontainer.created_at',
                'statuscontainer.updated_at'
            )
                ->leftJoin('parameter', 'statuscontainer.statusaktif', '=', 'parameter.id')
                ->orderBy('statuscontainer.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = StatusContainer::select(
                    'statuscontainer.id as id_',
                    'statuscontainer.kodestatuscontainer',
                    'statuscontainer.keterangan',
                    'parameter.text as statusaktif',
                    'statuscontainer.modifiedby',
                    'statuscontainer.created_at',
                    'statuscontainer.updated_at'
                )
                    ->leftJoin('parameter', 'statuscontainer.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('statuscontainer.id', $params['sortorder']);
            } else {
                $query = StatusContainer::select(
                    'statuscontainer.id as id_',
                    'statuscontainer.kodestatuscontainer',
                    'statuscontainer.keterangan',
                    'parameter.text as statusaktif',
                    'statuscontainer.modifiedby',
                    'statuscontainer.created_at',
                    'statuscontainer.updated_at'
                )
                    ->leftJoin('parameter', 'statuscontainer.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('statuscontainer.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing([
            'id_',
            'kodestatuscontainer',
            'keterangan',
            'statusaktif',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $query);


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
    
    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $statusContainers = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Kode Status Container',
                'index' => 'kodestatuscontainer',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
        ];

        $this->toExcel('Status Container', $statusContainers, $columns);
    }
}
