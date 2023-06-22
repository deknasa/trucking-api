<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreDataRitasiRequest;
use App\Http\Requests\UpdateDataRitasiRequest;
use App\Http\Requests\DestroyDataRitasiRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\DataRitasi;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;

use Illuminate\Database\QueryException;

class DataRitasiController extends Controller
{

    /**
     * @ClassName 
     */
    public function index()
    {
        $dataritasi = new dataritasi();
        return response([
            'data' => $dataritasi->get(),
            'attributes' => [
                'totalRows' => $dataritasi->totalRows,
                'totalPages' => $dataritasi->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function report()
    {
    }

    public function default()
    {

        $dataritasi = new DataRitasi();
        return response([
            'status' => true,
            'data' => $dataritasi->default(),
        ]);
    }
    /**
     * @ClassName 
     */

    public function store(StoreDataRitasiRequest $request)
    {
        DB::beginTransaction();
        try {
            $dataritasi = new DataRitasi();
            $dataritasi->statusritasi = $request->statusritasi;
            $dataritasi->nominal = $request->nominal;
            $dataritasi->statusaktif = $request->statusaktif;
            $dataritasi->modifiedby = auth('api')->user()->name;

            if ($dataritasi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($dataritasi->getTable()),
                    'postingdari' => 'ENTRY dataritasi',
                    'idtrans' => $dataritasi->id,
                    'nobuktitrans' => $dataritasi->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $dataritasi->toArray(),
                    'modifiedby' => $dataritasi->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($dataritasi, $dataritasi->getTable());
            $dataritasi->position = $selected->position;
            $dataritasi->page = ceil($dataritasi->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $dataritasi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(DataRitasi $dataritasi)
    {
        // dd($cabang);
        return response([
            'status' => true,
            'data' => $dataritasi
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateDataRitasiRequest $request, dataritasi $dataritasi)
    {
        DB::beginTransaction();
        try {
            // $dataritasi = new dataritasi();
            $dataritasi->statusritasi = $request->statusritasi;
            $dataritasi->nominal = $request->nominal;
            $dataritasi->statusaktif = $request->statusaktif;
            $dataritasi->modifiedby = auth('api')->user()->name;

            if ($dataritasi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($dataritasi->getTable()),
                    'postingdari' => 'EDIT dataritasi',
                    'idtrans' => $dataritasi->id,
                    'nobuktitrans' => $dataritasi->id,
                    'aksi' => 'EDIT',
                    'datajson' => $dataritasi->toArray(),
                    'modifiedby' => $dataritasi->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($dataritasi, $dataritasi->getTable());
            $dataritasi->position = $selected->position;
            $dataritasi->page = ceil($dataritasi->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $dataritasi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */

    public function destroy(DestroyDataRitasiRequest $request, $id)
    {
        DB::beginTransaction();
        $dataritasi = new dataritasi();
        $dataritasi = $dataritasi->lockAndDestroy($id);
        if ($dataritasi) {
            $logTrail = [
                'namatabel' => strtoupper($dataritasi->getTable()),
                'postingdari' => 'DELETE dataritasi',
                'idtrans' => $dataritasi->id,
                'nobuktitrans' => $dataritasi->id,
                'aksi' => 'DELETE',
                'datajson' => $dataritasi->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($dataritasi, $dataritasi->getTable(), true);
            $dataritasi->position = $selected->position;
            $dataritasi->id = $selected->id;
            $dataritasi->page = ceil($dataritasi->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $dataritasi
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('dataritasi')->getColumns();

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
            header('Access-Control-Allow-Origin: *');
            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $dataritasi = $decodedResponse['data'];

            $judulLaporan = $dataritasi[0]['judulLaporan'];
            $i = 0;
            foreach ($dataritasi as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $dataritasi[$i]['statusaktif'] = $statusaktif;

                $nominal = number_format($params['nominal'], 2, ',', '.');
                $dataritasi[$i]['nominal'] = $nominal;

                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Status Ritasi',
                    'index' => 'statusritasi',
                ],
                [
                    'label' => 'nominal',
                    'index' => 'nominal',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $dataritasi, $columns);
        }
    }
}
