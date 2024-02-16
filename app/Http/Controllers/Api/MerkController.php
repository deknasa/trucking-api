<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Merk;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreMerkRequest;
use Illuminate\Database\QueryException;
use App\Http\Requests\UpdateMerkRequest;
use App\Http\Requests\DestroyMerkRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;

class MerkController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $merk = new Merk();

        return response([
            'data' => $merk->get(),
            'attributes' => [
                'totalRows' => $merk->totalRows,
                'totalPages' => $merk->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $merk = new Merk();
        $cekdata = $merk->cekvalidasihapus($id);
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
        $merk = new Merk();
        return response([
            'status' => true,
            'data' => $merk->default()
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreMerkRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodemerk' => $request->kodemerk,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
            ];
            $merk = (new Merk())->processStore($data);
            $merk->position = $this->getPosition($merk, $merk->getTable())->position;
            if ($request->limit==0) {
                $merk->page = ceil($merk->position / (10));
            } else {
                $merk->page = ceil($merk->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $merk
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Merk $merk)
    {
        return response([
            'status' => true,
            'data' => (new Merk())->findAll($merk->id)
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateMerkRequest $request, Merk $merk): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodemerk' => $request->kodemerk,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
            ];

            $merk = (new Merk())->processUpdate($merk, $data);
            $merk->position = $this->getPosition($merk, $merk->getTable())->position;
            if ($request->limit==0) {
                $merk->page = ceil($merk->position / (10));
            } else {
                $merk->page = ceil($merk->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $merk
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
    public function destroy(DestroyMerkRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $merk = (new Merk())->processDestroy($id);
            $selected = $this->getPosition($merk, $merk->getTable(), true);
            $merk->position = $selected->position;
            $merk->id = $selected->id;
            if ($request->limit==0) {
                $merk->page = ceil($merk->position / (10));
            } else {
                $merk->page = ceil($merk->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $merk
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
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
            (new Merk())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('merk')->getColumns();

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
            $merks = $decodedResponse['data'];

            $judulLaporan = $merks[0]['judulLaporan'];

            $i = 0;
            foreach ($merks as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $merks[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Merk',
                    'index' => 'kodemerk',
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

            $this->toExcel($judulLaporan, $merks, $columns);
        }
    }
}
