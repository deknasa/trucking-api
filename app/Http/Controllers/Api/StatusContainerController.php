<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\StatusContainer;
use App\Http\Requests\StoreStatusContainerRequest;
use App\Http\Requests\UpdateStatusContainerRequest;
use Illuminate\Database\QueryException;
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
        $statusContainer = new StatusContainer();

        return response([
            'data' => $statusContainer->get(),
            'attributes' => [
                'totalRows' => $statusContainer->totalRows,
                'totalPages' => $statusContainer->totalPages
            ]
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

            }

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($statusContainer, $statusContainer->getTable());
            $statusContainer->position = $selected->position;
            $statusContainer->page = ceil($statusContainer->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $statusContainer
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function update(UpdateStatusContainerRequest $request, StatusContainer $statuscontainer)
    {

        try {
            $statuscontainer->kodestatuscontainer = $request->kodestatuscontainer;
            $statuscontainer->keterangan = $request->keterangan;
            $statuscontainer->statusaktif = $request->statusaktif;
            $statuscontainer->modifiedby = auth('api')->user()->name;

            if ($statuscontainer->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($statuscontainer->getTable()),
                    'postingdari' => 'EDIT STATUS CONTAINER',
                    'idtrans' => $statuscontainer->id,
                    'nobuktitrans' => $statuscontainer->id,
                    'aksi' => 'EDIT',
                    'datajson' => $statuscontainer->toArray(),
                    'modifiedby' => $statuscontainer->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $selected = $this->getPosition($statuscontainer, $statuscontainer->getTable());
                $statuscontainer->position = $selected->position;
                $statuscontainer->page = ceil($statuscontainer->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $statuscontainer
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
    public function destroy(StatusContainer $statuscontainer,  Request $request)
    {

        DB::beginTransaction();
        try {

            $delete = StatusContainer::destroy($statuscontainer->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($statuscontainer->getTable()),
                    'postingdari' => 'DELETE STSTUS CONTAINER',
                    'idtrans' => $statuscontainer->id,
                    'nobuktitrans' => $statuscontainer->id,
                    'aksi' => 'DELETE',
                    'datajson' => $statuscontainer->toArray(),
                    'modifiedby' => $statuscontainer->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($statuscontainer, $statuscontainer->getTable(), true);
                $statuscontainer->position = $selected->position;
                $statuscontainer->id = $selected->id;
                $statuscontainer->page = ceil($statuscontainer->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $statuscontainer
                ]);
            } else {
                DB::rollBack();

                return response([
                    'status' => false,
                    'message' => 'Gagal dihapus'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
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

    /**
     * @ClassName
     */
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
