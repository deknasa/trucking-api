<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\StatusContainer;
use App\Http\Requests\StoreStatusContainerRequest;
use App\Http\Requests\UpdateStatusContainerRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Requests\DestroyStatusContainerRequest;
use App\Http\Requests\RangeExportReportRequest;
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

    public function cekValidasi($id)
    {
        $statusContainer = new StatusContainer();
        $cekdata = $statusContainer->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', 'SATL')
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {
            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    public function default()
    {

        $statusContainer = new StatusContainer();
        return response([
            'status' => true,
            'data' => $statusContainer->default(),
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
            $statusContainer->keterangan = $request->keterangan ?? '';
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
    public function update(UpdateStatusContainerRequest $request, StatusContainer $statusContainer)
    {

        DB::beginTransaction();
        try {
            $statusContainer->kodestatuscontainer = $request->kodestatuscontainer;
            $statusContainer->keterangan = $request->keterangan ?? '';
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
            }
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($statusContainer, $statusContainer->getTable());
            $statusContainer->position = $selected->position;
            $statusContainer->page = ceil($statusContainer->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
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
    public function destroy(DestroyStatusContainerRequest $request, $id)
    {

        DB::beginTransaction();
        $statusContainer = new StatusContainer();
        $statusContainer = $statusContainer->lockAndDestroy($id);

        if ($statusContainer) {
            $logTrail = [
                'namatabel' => strtoupper($statusContainer->getTable()),
                'postingdari' => 'DELETE STATUS CONTAINER',
                'idtrans' => $statusContainer->id,
                'nobuktitrans' => $statusContainer->id,
                'aksi' => 'DELETE',
                'datajson' => $statusContainer->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($statusContainer, $statusContainer->getTable(), true);
            $statusContainer->position = $selected->position;
            $statusContainer->id = $selected->id;
            $statusContainer->page = ceil($statusContainer->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $statusContainer
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
    public function export(RangeExportReportRequest $request)
    {

        if (request()->cekExport) {
            return response([
                'status' => true,
            ]);
        } else {
            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $statusContainers = $decodedResponse['data'];

            $judulLaporan = $statusContainers[0]['judulLaporan'];

            $i = 0;
            foreach ($statusContainers as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $statusContainers[$i]['statusaktif'] = $statusaktif;


                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
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

            $this->toExcel($judulLaporan, $statusContainers, $columns);
        }
    }
}
