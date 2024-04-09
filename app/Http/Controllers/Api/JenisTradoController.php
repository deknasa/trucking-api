<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Carbon\Carbon;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\JenisTrado;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreJenisTradoRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\UpdateJenisTradoRequest;
use App\Http\Requests\DestroyJenisTradoRequest;
use App\Http\Requests\RangeExportReportRequest;

class JenisTradoController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $jenistrado = new JenisTrado();

        return response([
            'data' => $jenistrado->get(),
            'attributes' => [
                'totalRows' => $jenistrado->totalRows,
                'totalPages' => $jenistrado->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $jenisTrado = new JenisTrado();
        $dataMaster = $jenisTrado->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $cekdata = $jenisTrado->cekvalidasihapus($id);
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
                    (new MyModel())->updateEditingBy('jenisTrado', $id, $aksi);
                }

                $data = [
                    'status' => false,
                    'message' => '',
                    'errors' => '',
                    'kondisi' => false,
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
                ];

                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('jenisTrado', $id, $aksi);
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

        $jenisTrado = new JenisTrado();
        return response([
            'status' => true,
            'data' => $jenisTrado->default(),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreJenisTradoRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodejenistrado' => $request->kodejenistrado,
                'statusaktif' => $request->statusaktif,
                'keterangan' => $request->keterangan ?? '',
                'tas_id' => $request->tas_id ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            $jenistrado = (new JenisTrado())->processStore($data);
            if ($request->from == '') {
                $jenistrado->position = $this->getPosition($jenistrado, $jenistrado->getTable())->position;
                if ($request->limit == 0) {
                    $jenistrado->page = ceil($jenistrado->position / (10));
                } else {
                    $jenistrado->page = ceil($jenistrado->position / ($request->limit ?? 10));
                }
            }


            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $jenistrado->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('jenistrado', 'add', $data);
            }


            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenistrado
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(jenistrado $jenistrado)
    {
        return response([
            'status' => true,
            'data' => $jenistrado
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateJenisTradoRequest $request, JenisTrado $jenistrado)
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodejenistrado' => $request->kodejenistrado,
                'statusaktif' => $request->statusaktif,
                'keterangan' => $request->keterangan ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            $jenistrado = (new JenisTrado())->processUpdate($jenistrado, $data);
            if ($request->from == '') {
                $jenistrado->position = $this->getPosition($jenistrado, $jenistrado->getTable())->position;
                if ($request->limit == 0) {
                    $jenistrado->page = ceil($jenistrado->position / (10));
                } else {
                    $jenistrado->page = ceil($jenistrado->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $jenistrado->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('jenistrado', 'edit', $data);
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $jenistrado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }


    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyJenisTradoRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $jenistrado = (new JenisTrado())->processDestroy($id);
            if ($request->from == '') {
                $selected = $this->getPosition($jenistrado, $jenistrado->getTable(), true);
                $jenistrado->position = $selected->position;
                $jenistrado->id = $selected->id;
                if ($request->limit == 0) {
                    $jenistrado->page = ceil($jenistrado->position / (10));
                } else {
                    $jenistrado->page = ceil($jenistrado->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;

            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('jenistrado', 'delete', $data);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $jenistrado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenistrado')->getColumns();

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
            $jenisTrados = $decodedResponse['data'];

            $judulLaporan = $jenisTrados[0]['judulLaporan'];

            $i = 0;
            foreach ($jenisTrados as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $jenisTrados[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Jenis Trado',
                    'index' => 'kodejenistrado',
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

            $this->toExcel($judulLaporan, $jenisTrados, $columns);
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
            (new JenisTrado())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
