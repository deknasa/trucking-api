<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsenTradoRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAbsenTradoRequest;
use App\Http\Requests\DestroyAbsenTradoRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Models\AbsenTrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class AbsenTradoController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $absenTrado = new AbsenTrado();

        return response([
            'data' => $absenTrado->get(),
            'attributes' => [
                'totalRows' => $absenTrado->totalRows,
                'totalPages' => $absenTrado->totalPages
            ]
        ]);
    }

    public function default()
    {
        $absenTrado = new AbsenTrado();
        return response([
            'status' => true,
            'data' => $absenTrado->default()
        ]);
    }

    public function cekValidasi($id)
    {
        $absenTrado = new AbsenTrado();
        $cekdata = $absenTrado->cekvalidasihapus($id);
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


    /**
     * @ClassName 
     */
    public function store(StoreAbsenTradoRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodeabsen' => $request->kodeabsen ?? '',
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                'key' => $request->key ?? '',
                'value' => $request->value ?? ''
            ];
            $absenTrado = (new AbsenTrado())->processStore($data);
            $absenTrado->position = $this->getPosition($absenTrado, $absenTrado->getTable())->position;
            $absenTrado->page = ceil($absenTrado->position / ($request->limit ?? 10));

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $absenTrado
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(AbsenTrado $absentrado)
    {
        return response([
            'status' => true,
            'data' => $absentrado
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateAbsenTradoRequest $request, AbsenTrado $absentrado): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodeabsen' => $request->kodeabsen ?? '',
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                'key' => $request->key ?? '',
                'value' => $request->value ?? ''
            ];

            $absentrado = (new AbsenTrado())->processUpdate($absentrado, $data);
            $absentrado->position = $this->getPosition($absentrado, $absentrado->getTable())->position;
            $absentrado->page = ceil($absentrado->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $absentrado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    /**
     * @ClassName 
     */
    public function destroy(DestroyAbsenTradoRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $absenTrado = (new AbsenTrado())->processDestroy($id);
            $selected = $this->getPosition($absenTrado, $absenTrado->getTable(), true);
            $absenTrado->position = $selected->position;
            $absenTrado->id = $selected->id;
            $absenTrado->page = ceil($absenTrado->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $absenTrado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function detail()
    {
        $query = AbsenTrado::select('memo')->where('id', request()->id)->first();

        $memo = json_decode($query->memo);

        $array = [];
        if ($memo != '') {

            $i = 0;
            foreach ($memo as $index => $value) {
                $array[$i]['key'] = $index;
                $array[$i]['value'] = $value;

                $i++;
            }
        }

        return response([
            'data' => $array
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('absentrado')->getColumns();

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
            $absentrados = $decodedResponse['data'];

            $judulLaporan = $absentrados[0]['judulLaporan'];

            $i = 0;
            foreach ($absentrados as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $absentrados[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Absen',
                    'index' => 'kodeabsen',
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

            $this->toExcel($judulLaporan, $absentrados, $columns);
        }
    }
}
