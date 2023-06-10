<?php

namespace App\Http\Controllers\Api;

use App\Models\Zona;
use App\Http\Requests\StoreZonaRequest;
use App\Http\Requests\UpdateZonaRequest;
use App\Http\Requests\DestroyZonaRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\Parameter;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ZonaController extends Controller
{

    /**
     * @ClassName 
     */
    public function index()
    {
        $zona = new Zona();

        return response([
            'data' => $zona->get(),
            'attributes' => [
                'totalRows' => $zona->totalRows,
                'totalPages' => $zona->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $zona = new Zona();
        $cekdata = $zona->cekvalidasihapus($id);
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
        $zona = new Zona();
        return response([
            'status' => true,
            'data' => $zona->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreZonaRequest $request)
    {
        DB::beginTransaction();

        try {
            $zona = new Zona();
            $zona->zona = $request->zona;
            $zona->statusaktif = $request->statusaktif;
            $zona->keterangan = $request->keterangan ?? '';
            $zona->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($zona->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($zona->getTable()),
                    'postingdari' => 'ENTRY ZONA',
                    'idtrans' => $zona->id,
                    'nobuktitrans' => $zona->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $zona->toArray(),
                    'modifiedby' => $zona->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($zona, $zona->getTable());
            $zona->position = $selected->position;
            $zona->page = ceil($zona->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $zona
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Zona $zona)
    {
        return response([
            'status' => true,
            'data' => $zona
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateZonaRequest $request, Zona $zona)
    {
        DB::beginTransaction();
        try {
            $zona->zona = $request->zona;
            $zona->keterangan = $request->keterangan ?? '';
            $zona->statusaktif = $request->statusaktif;
            $zona->modifiedby = auth('api')->user()->name;

            if ($zona->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($zona->getTable()),
                    'postingdari' => 'EDIT ZONA',
                    'idtrans' => $zona->id,
                    'nobuktitrans' => $zona->id,
                    'aksi' => 'EDIT',
                    'datajson' => $zona->toArray(),
                    'modifiedby' => $zona->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($zona, $zona->getTable());
            $zona->position = $selected->position;
            $zona->page = ceil($zona->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $zona
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyZonaRequest $request, $id)
    {
        DB::beginTransaction();

        $zona = new Zona();
        $zona = $zona->lockAndDestroy($id);

        if ($zona) {
            $logTrail = [
                'namatabel' => strtoupper($zona->getTable()),
                'postingdari' => 'DELETE ZONA',
                'idtrans' => $zona->id,
                'nobuktitrans' => $zona->id,
                'aksi' => 'DELETE',
                'datajson' => $zona->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);
            DB::commit();

            $selected = $this->getPosition($zona, $zona->getTable(), true);
            $zona->position = $selected->position;
            $zona->id = $selected->id;
            $zona->page = ceil($zona->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $zona
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('zona')->getColumns();

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

    public function export(RangeExportReportRequest $request
    )
    {
        if (request()->cekExport) {
            return response([
                'status' => true,
            ]);
        } else {

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $zonas = $decodedResponse['data'];

            $judulLaporan = $zonas[0]['judulLaporan'];

            $i = 0;
            foreach ($zonas as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $zonas[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Zona',
                    'index' => 'zona',
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

            $this->toExcel($judulLaporan, $zonas, $columns);
        }
    }
}
