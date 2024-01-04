<?php

namespace App\Http\Controllers\Api;

use App\Models\Kerusakan;
use App\Http\Requests\StoreKerusakanRequest;
use App\Http\Requests\UpdateKerusakanRequest;
use App\Http\Requests\DestroyKerusakanRequest;
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

class KerusakanController extends Controller
{

   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $kerusakan = new Kerusakan();

        return response([
            'data' => $kerusakan->get(),
            'attributes' => [
                'totalRows' => $kerusakan->totalRows,
                'totalPages' => $kerusakan->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $kerusakan = new Kerusakan();
        $cekdata = $kerusakan->cekvalidasihapus($id);
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

        $kerusakan = new Kerusakan();
        return response([
            'status' => true,
            'data' => $kerusakan->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreKerusakanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif
            ];
            $kerusakan = (new Kerusakan())->processStore($data);
            $kerusakan->position = $this->getPosition($kerusakan, $kerusakan->getTable())->position;
            if ($request->limit==0) {
                $kerusakan->page = ceil($kerusakan->position / (10));
            } else {
                $kerusakan->page = ceil($kerusakan->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kerusakan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Kerusakan $kerusakan)
    {
        return response([
            'status' => true,
            'data' => $kerusakan
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateKerusakanRequest $request, Kerusakan $kerusakan)
    {
        DB::beginTransaction();
        try {
            $data = [
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif
            ];
            $kerusakan = (new Kerusakan())->processUpdate($kerusakan, $data);
            $kerusakan->position = $this->getPosition($kerusakan, $kerusakan->getTable())->position;
            if ($request->limit==0) {
                $kerusakan->page = ceil($kerusakan->position / (10));
            } else {
                $kerusakan->page = ceil($kerusakan->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $kerusakan
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
    public function destroy(DestroyKerusakanRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $kerusakan = (new Kerusakan())->processDestroy($id);
            $selected = $this->getPosition($kerusakan, $kerusakan->getTable(), true);
            $kerusakan->position = $selected->position;
            $kerusakan->id = $selected->id;
            if ($request->limit==0) {
                $kerusakan->page = ceil($kerusakan->position / (10));
            } else {
                $kerusakan->page = ceil($kerusakan->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $kerusakan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kerusakan')->getColumns();

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

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
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
            $kerusakans = $decodedResponse['data'];

            $judulLaporan = $kerusakans[0]['judulLaporan'];


            $i = 0;
            foreach ($kerusakans as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $kerusakans[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
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

            $this->toExcel($judulLaporan, $kerusakans, $columns);
        }
    }
}
