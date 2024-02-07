<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
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
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreStatusContainerRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodestatuscontainer' => $request->kodestatuscontainer,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif
            ];
            $statusContainer = (new StatusContainer())->processStore($data);
            $statusContainer->position = $this->getPosition($statusContainer, $statusContainer->getTable())->position;
            if ($request->limit==0) {
                $statusContainer->page = ceil($statusContainer->position / (10));
            } else {
                $statusContainer->page = ceil($statusContainer->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $statusContainer
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateStatusContainerRequest $request, StatusContainer $statusContainer)
    {

        DB::beginTransaction();
        try {
            $data = [
                'kodestatuscontainer' => $request->kodestatuscontainer,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif
            ];
            $statusContainer = (new StatusContainer())->processUpdate($statusContainer, $data);
            $statusContainer->position = $this->getPosition($statusContainer, $statusContainer->getTable())->position;
            if ($request->limit==0) {
                $statusContainer->page = ceil($statusContainer->position / (10));
            } else {
                $statusContainer->page = ceil($statusContainer->position / ($request->limit ?? 10));
            }

            DB::commit();

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
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyStatusContainerRequest $request, $id)
    {

        DB::beginTransaction();

        try {
            $statusContainer = (new StatusContainer())->processDestroy($id);
            $selected = $this->getPosition($statusContainer, $statusContainer->getTable(), true);
            $statusContainer->position = $selected->position;
            $statusContainer->id = $selected->id;
            if ($request->limit==0) {
                $statusContainer->page = ceil($statusContainer->position / (10));
            } else {
                $statusContainer->page = ceil($statusContainer->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $statusContainer
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(RangeExportReportRequest $request)
    {

        if (request()->cekExport) {

            if (request()->offset == "-1" && request()->limit == '1') {
                
                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],
                    'status' => false,
                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'status' => true,
                ]);
            }
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

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }
    
    /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new StatusContainer())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
