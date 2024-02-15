<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
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
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan TAMBAH DATA
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
                'jabatan' => $request->jabatan,
                'tas_id' => $request->tas_id,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',

            ];
            // dd($data);
            $karyawan = (new Karyawan())->processStore($data);
            if ($request->from == '') {
                $karyawan->position = $this->getPosition($karyawan, $karyawan->getTable())->position;
                if ($request->limit == 0) {
                    $karyawan->page = ceil($karyawan->position / (10));
                } else {
                    $karyawan->page = ceil($karyawan->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $karyawan->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('karyawan', 'add', $data);
            }


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

    public function show($id)
    {
        return response([
            'status' => true,
            'data' => (new Karyawan())->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
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
                'jabatan' => $request->jabatan,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            $karyawan = (new Karyawan())->processUpdate($karyawan, $data);
            if ($request->from == '') {
                $karyawan->position = $this->getPosition($karyawan, $karyawan->getTable())->position;
                if ($request->limit == 0) {
                    $karyawan->page = ceil($karyawan->position / (10));
                } else {
                    $karyawan->page = ceil($karyawan->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $karyawan->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('karyawan', 'edit', $data);
            }

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
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $karyawan = (new Karyawan())->processDestroy($id);
            if ($request->from == '') {
                $selected = $this->getPosition($karyawan, $karyawan->getTable(), true);
                $karyawan->position = $selected->position;
                $karyawan->id = $selected->id;
                if ($request->limit == 0) {
                    $karyawan->page = ceil($karyawan->position / (10));
                } else {
                    $karyawan->page = ceil($karyawan->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;

            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('karyawan', 'delete', $data);
            }

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
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Karyawan())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
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
                    'label' => 'Jabatan',
                    'index' => 'jabatan',
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
