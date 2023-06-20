<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPenerimaRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Penerima;
use App\Http\Requests\StorePenerimaRequest;
use App\Http\Requests\UpdatePenerimaRequest;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class PenerimaController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $penerima = new Penerima();

        return response([
            'data' => $penerima->get(),
            'attributes' => [
                'totalRows' => $penerima->totalRows,
                'totalPages' => $penerima->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $penerima = new Penerima();
        $cekdata = $penerima->cekvalidasihapus($id);
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
        $penerima = new Penerima();
        return response([
            'status' => true,
            'data' => $penerima->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StorePenerimaRequest $request): JsonResponse
    {

        DB::beginTransaction();
        // dd($request->npwp);
        try {
            $data = [
                'namapenerima' => $request->namapenerima,
                'npwp' => $request->npwp,
                'noktp' => $request->noktp,
                'statusaktif' => $request->statusaktif,
                'statuskaryawan' => $request->statuskaryawan,
            ];
            $penerima = (new Penerima())->processStore($data);
            $penerima->position = $this->getPosition($penerima, $penerima->getTable())->position;
            $penerima->page = ceil($penerima->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerima
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(Penerima $penerima)
    {
        return response([
            'status' => true,
            'data' => $penerima
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdatePenerimaRequest $request, Penerima $penerima): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'namapenerima' => $request->namapenerima,
                'npwp' => $request->npwp,
                'noktp' => $request->noktp,
                'statusaktif' => $request->statusaktif,
                'statuskaryawan' => $request->statuskaryawan,
            ];

            $penerima = (new Penerima())->processUpdate($penerima, $data);
            $penerima->position = $this->getPosition($penerima, $penerima->getTable())->position;
            $penerima->page = ceil($penerima->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $penerima
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyPenerimaRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $penerima = (new Penerima())->processDestroy($id);
            $selected = $this->getPosition($penerima, $penerima->getTable(), true);
            $penerima->position = $selected->position;
            $penerima->id = $selected->id;
            $penerima->page = ceil($penerima->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerima
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
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
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerima')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

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
            $penerimas = $decodedResponse['data'];

            $judulLaporan = $penerimas[0]['judulLaporan'];

            $i = 0;
            foreach ($penerimas as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusKaryawan = $params['statuskaryawan'];

                $result = json_decode($statusaktif, true);
                $resultKaryawan = json_decode($statusKaryawan, true);

                $statusaktif = $result['MEMO'];
                $statusKaryawan = $resultKaryawan['MEMO'];

                $penerimas[$i]['statusaktif'] = $statusaktif;
                $penerimas[$i]['statuskaryawan'] = $statusKaryawan;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Nama Penerima',
                    'index' => 'namapenerima',
                ],
                [
                    'label' => 'NPWP',
                    'index' => 'npwp',
                ],
                [
                    'label' => 'No KTP',
                    'index' => 'noktp',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Status Karyawan',
                    'index' => 'statuskaryawan',
                ],
            ];

            $this->toExcel($judulLaporan, $penerimas, $columns);
        }
    }
}
