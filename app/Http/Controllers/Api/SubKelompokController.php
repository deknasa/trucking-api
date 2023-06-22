<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\SubKelompok;
use App\Http\Requests\StoreSubKelompokRequest;
use App\Http\Requests\UpdateSubKelompokRequest;
use App\Http\Requests\DestroySubKelompokRequest;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class SubKelompokController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $subKelompok = new SubKelompok();

        return response([
            'data' => $subKelompok->get(),
            'attributes' => [
                'totalRows' => $subKelompok->totalRows,
                'totalPages' => $subKelompok->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $subKelompok = new SubKelompok();
        $cekdata = $subKelompok->cekvalidasihapus($id);
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
        $subKelompok = new SubKelompok();
        return response([
            'status' => true,
            'data' => $subKelompok->default()
        ]);
    }
    public function show($id)
    {
        $subKelompok = SubKelompok::select('subkelompok.*', 'kelompok.keterangan as kelompok')->leftJoin('kelompok', 'subkelompok.kelompok_id', 'kelompok.id')->where('subkelompok.id', $id)->first();
        return response([
            'status' => true,
            'data' => $subKelompok
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreSubKelompokRequest $request): JsonResponse
    {

        DB::beginTransaction();

        try {
            $data = [
                'kodesubkelompok' => $request->kodesubkelompok,
                'keterangan' => $request->keterangan ?? '',
                'kelompok_id' => $request->kelompok_id,
                'statusaktif' => $request->statusaktif
            ];
            $subKelompok = (new SubKelompok())->processStore($data);
            $subKelompok->position = $this->getPosition($subKelompok, $subKelompok->getTable())->position;
            $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $subKelompok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function update(UpdateSubKelompokRequest $request, SubKelompok $subKelompok): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodesubkelompok' => $request->kodesubkelompok,
                'keterangan' => $request->keterangan ?? '',
                'kelompok_id' => $request->kelompok_id,
                'statusaktif' => $request->statusaktif
            ];

            $subKelompok = (new SubKelompok())->processUpdate($subKelompok, $data);
            $subKelompok->position = $this->getPosition($subKelompok, $subKelompok->getTable())->position;
            $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $subKelompok
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(DestroySubKelompokRequest $request, $id)
    {
        try {
            $subKelompok = (new SubKelompok())->processDestroy($id);
            $selected = $this->getPosition($subKelompok, $subKelompok->getTable(), true);
            $subKelompok->position = $selected->position;
            $subKelompok->id = $selected->id;
            $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $subKelompok
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
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
            $subKelompoks = $decodedResponse['data'];

            $judulLaporan = $subKelompoks[0]['judulLaporan'];

            $i = 0;
            foreach ($subKelompoks as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $subKelompoks[$i]['statusaktif'] = $statusaktif;


                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Subkelompok',
                    'index' => 'kodesubkelompok',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Kelompok',
                    'index' => 'kelompok_id',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $subKelompoks, $columns);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('subkelompok')->getColumns();

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
    public function report()
    {
    }
}
