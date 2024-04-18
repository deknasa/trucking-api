<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\SubKelompok;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\StoreSubKelompokRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\UpdateSubKelompokRequest;
use App\Http\Requests\DestroySubKelompokRequest;

class SubKelompokController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
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

      /**
     * @ClassName 
     * @Keterangan EDIT DATA USER
     */
    public function updateuser()
    {
    }

    public function cekValidasi($id, request $request)
    {
        $subKelompok = new SubKelompok();
        $dataMaster = $subKelompok->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $cekdata = $subKelompok->cekvalidasihapus($id);

        $aksi=$request->aksi ?? '';
        $acoid = db::table('acos')->from(db::raw("acos a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.class', 'kategori')
            ->where('a.method', 'update')
            ->first()->id ?? 0;
        $userid = auth('api')->user()->id;

        $data = (new MyModel())->hakuser($userid, $acoid);
        if ($data == true) {
            $hakutama = 1;
        } else {
            $hakutama = 0;
        }
        if ($aksi == 'edit') {
            if ($cekdata['kondisi'] == true) {
                if ($hakutama == 1) {
                    $cekdata['kondisi'] = false;
                }
            }
        }
                
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
        } else  if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');
            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('subKelompok', $id, $aksi);
                }
                
                $data = [
                    'status' => false,
                    'message' => '',
                    'errors' => '',
                    'kondisi' => false,
                    'editblok' => false,
                ];
                
                // return response($data);
            } else {
                
                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->keterangan . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                
                $data = [
                    'status' => true,
                    'message' => ["keterangan"=>$keterror],
                    'errors' => '',
                    'kondisi' => true,
                    'editblok' => true,
                ];
                
                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('subKelompok', $id, $aksi);
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
        return response([
            'status' => true,
            'data' => (new SubKelompok())->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
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
            if ($request->limit==0) {
                $subKelompok->page = ceil($subKelompok->position / (10));
            } else {
                $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $subKelompok->id;
            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('subkelompok', 'add', $data);
            }
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
     * @Keterangan EDIT DATA
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
            if ($request->limit==0) {
                $subKelompok->page = ceil($subKelompok->position / (10));
            } else {
                $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $subKelompok->id;
            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('subkelompok', 'edit', $data);
            }
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
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroySubKelompokRequest $request, $id)
    {
        try {
            $subKelompok = (new SubKelompok())->processDestroy($id);
            $selected = $this->getPosition($subKelompok, $subKelompok->getTable(), true);
            $subKelompok->position = $selected->position;
            $subKelompok->id = $selected->id;
            if ($request->limit==0) {
                $subKelompok->page = ceil($subKelompok->position / (10));
            } else {
                $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;
            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('subkelompok', 'delete', $data);
            }

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
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new SubKelompok())->processApprovalnonaktif($data);

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
                    'label' => 'Sub Kelompok',
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
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }
}
