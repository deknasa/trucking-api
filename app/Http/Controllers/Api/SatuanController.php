<?php

namespace App\Http\Controllers\Api;

use App\Models\Satuan;
use App\Http\Requests\StoreSatuanRequest;
use App\Http\Requests\UpdateSatuanRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class SatuanController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $satuan = new Satuan();

        return response([
            'data' => $satuan->get(),
            'attributes' => [
                'totalRows' => $satuan->totalRows,
                'totalPages' => $satuan->totalPages
            ]
        ]);
    }

    public function default()
    {
        $satuan = new Satuan();
        return response([
            'status' => true,
            'data' => $satuan->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreSatuanRequest $request)
    {
        DB::beginTransaction();

        try {
            $satuan = new Satuan();
            $satuan->satuan = $request->satuan;
            $satuan->statusaktif = $request->statusaktif;
            $satuan->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($satuan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($satuan->getTable()),
                    'postingdari' => 'ENTRY SATUAN',
                    'idtrans' => $satuan->id,
                    'nobuktitrans' => $satuan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $satuan->toArray(),
                    'modifiedby' => $satuan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($satuan, $satuan->getTable());
            $satuan->position = $selected->position;
            $satuan->page = ceil($satuan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $satuan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Satuan $satuan)
    {
        return response([
            'status' => true,
            'data' => $satuan
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateSatuanRequest $request, Satuan $satuan)
    {
        DB::beginTransaction();

        try {
            $satuan->satuan = $request->satuan;
            $satuan->statusaktif = $request->statusaktif;
            $satuan->modifiedby = auth('api')->user()->name;

            if ($satuan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($satuan->getTable()),
                    'postingdari' => 'EDIT SATUAN',
                    'idtrans' => $satuan->id,
                    'nobuktitrans' => $satuan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $satuan->toArray(),
                    'modifiedby' => $satuan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);


                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($satuan, $satuan->getTable());
            $satuan->position = $selected->position;
            $satuan->page = ceil($satuan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $satuan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $satuan = new Satuan();
        $satuan = $satuan->lockAndDestroy($id);
        if ($satuan) {
            $logTrail = [
                'namatabel' => strtoupper($satuan->getTable()),
                'postingdari' => 'DELETE SATUAN',
                'idtrans' => $satuan->id,
                'nobuktitrans' => $satuan->id,
                'aksi' => 'DELETE',
                'datajson' => $satuan->toArray(),
                'modifiedby' => $satuan->modifiedby
            ];

            $data = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($satuan, $satuan->getTable(), true);
            $satuan->position = $selected->position;
            $satuan->id = $selected->id;
            $satuan->page = ceil($satuan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $satuan
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('satuan')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
        ];

        return response([
            'data' => $data
        ]);
    }
    public function export(RangeExportReportRequest $request)
    {

        if (request()->cekExport) {
            return response([
                'status' => true,
            ]);
        } else {

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $satuans = $decodedResponse['data'];

            $judulLaporan = $satuans[0]['judulLaporan'];


            $i = 0;
            foreach ($satuans as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $satuans[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Satuan',
                    'index' => 'satuan',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $satuans, $columns);
        }
    }
}
