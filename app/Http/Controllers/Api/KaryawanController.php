<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use App\Models\Karyawan;
use App\Http\Requests\StoreKaryawanRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateKaryawanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class KaryawanController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $karyawan = new Karyawan();

        return response([
            'data' => $karyawan->get(),
            'attributes' => [
                'totalRows' => $karyawan->totalRows,
                'totalPages' => $karyawan->totalPages,
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $karyawan = new Karyawan();
        $cekdata = $karyawan->cekvalidasihapus($id);
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
        $karyawan = new Karyawan();
        return response([
            'status' => true,
            'data' => $karyawan->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreKaryawanRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'namakaryawan' => $request->namakaryawan,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
                'statusstaff' => $request->statusstaff,
            ];
            $karyawan = (new Karyawan())->processStore($data);
            $karyawan->position = $this->getPosition($karyawan, $karyawan->getTable())->position;
            $karyawan->page = ceil($karyawan->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $karyawan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Karyawan $karyawan)
    {
        return response([
            'status' => true,
            'data' => $karyawan
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateKaryawanRequest $request, Karyawan $karyawan)
    {
        DB::beginTransaction();

        try {
            $data = [
                'namakaryawan' => $request->namakaryawan,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
                'statusstaff' => $request->statusstaff,
            ];
            $karyawan = (new Karyawan())->processUpdate($karyawan, $data);
            $karyawan->position = $this->getPosition($karyawan, $karyawan->getTable())->position;
            $karyawan->page = ceil($karyawan->position / ($request->limit ?? 10));

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $karyawan
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

        try {
            $karyawan = (new Karyawan())->processDestroy($id);
            $selected = $this->getPosition($karyawan, $karyawan->getTable(), true);
            $karyawan->position = $selected->position;
            $karyawan->id = $selected->id;
            $karyawan->page = ceil($karyawan->position / ($request->limit ?? 10));

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $karyawan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('karyawan')->getColumns();

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

            if (request()->offset == "-1" && request()->limit == '1') {
                return response([
                    'status' => false,
                    'message' => app(ErrorController::class)->geterror('DTA')->keterangan
                ], 422);
            } else {
                return response([
                    'status' => true,
                ]);
            }
        } else {

            header('Access-Control-Allow-Origin: *');

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $karyawans = $decodedResponse['data'];

            $judulLaporan = $karyawans[0]['judulLaporan'];

            $i = 0;
            foreach ($karyawans as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statustaff = $params['statusstaff'];

                $result = json_decode($statusaktif, true);
                $resultStaff = json_decode($statustaff, true);

                $statusaktif = $result['MEMO'];
                $statustaff = $resultStaff['MEMO'];


                $karyawans[$i]['statusaktif'] = $statusaktif;
                $karyawans[$i]['statusstaff'] = $statustaff;


                $i++;
            }



            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Nama Karyawan',
                    'index' => 'namakaryawan',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Status Staff',
                    'index' => 'statusstaff',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $karyawans, $columns);
        }
    }
}
