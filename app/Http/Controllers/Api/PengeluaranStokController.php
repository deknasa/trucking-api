<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;
use App\Models\PengeluaranStok;

use App\Http\Requests\StorePengeluaranStokRequest;
use App\Http\Requests\UpdatePengeluaranStokRequest;
use App\Http\Requests\DestroyPengeluaranStokRequest;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class PengeluaranStokController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $pengeluaranStok = new PengeluaranStok();
        return response([
            'data' => $pengeluaranStok->get(),
            'attributes' => [
                'totalRows' => $pengeluaranStok->totalRows,
                'totalPages' => $pengeluaranStok->totalPages
            ]
        ]);
    }


    public function cekValidasi($id)
    {
        $pengeluaranStok = new PengeluaranStok();
        $cekdata = $pengeluaranStok->cekvalidasihapus($id);
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
        $pengeluaranStok = new PengeluaranStok();
        return response([
            'status' => true,
            'data' => $pengeluaranStok->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StorePengeluaranStokRequest $request) : JsonResponse
    {
        DB::beginTransaction();

        try {
            $pengeluaranStok = (new PengeluaranStok())->processStore($request->all());
            $pengeluaranStok->position = $this->getPosition($pengeluaranStok, $pengeluaranStok->getTable())->position;
            $pengeluaranStok->page = ceil($pengeluaranStok->position / ($request->limit ?? 10));

            DB::commit();   

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show(PengeluaranStok $pengeluaranStok, $id)
    {
        $pengeluaranStok = new PengeluaranStok();
        return response([
            'data' => $pengeluaranStok->find($id),
            'attributes' => [
                'totalRows' => $pengeluaranStok->totalRows,
                'totalPages' => $pengeluaranStok->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdatePengeluaranStokRequest $request, PengeluaranStok $pengeluaranStok, $id) : JsonResponse
    {
        DB::beginTransaction();
        try {
            $pengeluaranStok = PengeluaranStok::findOrFail($id);
            $pengeluaranStok = (new PengeluaranStok())->processUpdate($pengeluaranStok, $request->all());
            $pengeluaranStok->position = $this->getPosition($pengeluaranStok, $pengeluaranStok->getTable())->position;
            $pengeluaranStok->page = ceil($pengeluaranStok->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $pengeluaranStok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluaranstok')->getColumns();

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
    public function destroy(DestroyPengeluaranStokRequest $request, $id)
    {
        DB::beginTransaction();

        
        try {
            $pengeluaranStok = (new PengeluaranStok())->processDestroy($id);
            $selected = $this->getPosition($pengeluaranStok, $pengeluaranStok->getTable(), true);
            $pengeluaranStok->position = $selected->position;
            $pengeluaranStok->id = $selected->id;
            $pengeluaranStok->page = ceil($pengeluaranStok->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranStok
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

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
            $pengeluarans = $decodedResponse['data'];

            $judulLaporan = $pengeluarans[0]['judulLaporan'];

            $i = 0;
            foreach ($pengeluarans as $index => $params) {

                $format = $params['format'];
                $statusHitungStok = $params['statushitungstok'];

                $result = json_decode($format, true);
                $resultHitungStok = json_decode($statusHitungStok, true);

                $format = $result['MEMO'];
                $statusHitungStok = $resultHitungStok['MEMO'];


                $pengeluarans[$i]['format'] = $format;
                $pengeluarans[$i]['statushitungstok'] = $statusHitungStok;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Pengeluaran',
                    'index' => 'kodepengeluaran',
                ],
                [
                    'label' => 'keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'coa',
                    'index' => 'coa',
                ],
                [
                    'label' => 'status format',
                    'index' => 'format',
                ],
                [
                    'label' => 'status hitung stok',
                    'index' => 'statushitungstok',
                ],
            ];
            $this->toExcel($judulLaporan, $pengeluarans, $columns);
        }
    }
}
