<?php

namespace App\Http\Controllers\Api;

use App\Models\Error;

use App\Models\Tujuan;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTujuanRequest;
use App\Http\Requests\UpdateTujuanRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;

class TujuanController extends Controller
{
     /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $tujuan = new Tujuan();

        return response([
            'data' => $tujuan->get(),
            'attributes' => [
                'totalRows' => $tujuan->totalRows,
                'totalPages' => $tujuan->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $tujuan = new Tujuan();
        $dataMaster = $tujuan->where('id', $id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        $cekdata = ["kondisi"=>false];
        if ($cekdata['kondisi'] == true && $aksi != 'EDIT') {
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
                    (new MyModel())->updateEditingBy('zona', $id, $aksi);
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
                $keterror = 'Data <b>' . $dataMaster->kodezona . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;

                $data = [
                    'status' => true,
                    'message' => ["keterangan" => $keterror],
                    'errors' => '',
                    'kondisi' => true,
                    'editblok' => true,
                ];

                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('zona', $id, $aksi);

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
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreTujuanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tujuan' => $request->tujuan,
                'statusaktif' => $request->statusaktif,
                'keterangan' => $request->keterangan ?? '',
                'tas_id' => $request->tas_id,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            // $tujuan = (new Tujuan())->processStore($data);
            $tujuan = new Tujuan();
            $tujuan->processStore($data, $tujuan);
            if ($request->from == '') {
                $tujuan->position = $this->getPosition($tujuan, $tujuan->getTable())->position;
                if ($request->limit == 0) {
                    $tujuan->page = ceil($tujuan->position / (10));
                } else {
                    $tujuan->page = ceil($tujuan->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $tujuan->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                // $this->SaveTnlNew('tujuan', 'add', $data);
            }
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $tujuan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function default()
    {
        $tujuan = new Tujuan();
        return response([
            'status' => true,
            'data' => $tujuan->default()
        ]);
    }

  
    public function show($id)
    {
        $tujuan = new Tujuan();
        return response([
            'status' => true,
            'data' => $tujuan->findAll($id)
        ]);
    }

   /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTujuanRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = [
                'tujuan' => $request->tujuan,
                'statusaktif' => $request->statusaktif,
                'keterangan' => $request->keterangan ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            // $tujuan = (new Tujuan())->processUpdate($tujuan, $data);
            $tujuan = new Tujuan();
            $tujuans = $tujuan->findOrFail($id);
            $tujuan = $tujuan->processUpdate($tujuans, $data);            
            if ($request->from == '') {
                $tujuan->position = $this->getPosition($tujuan, $tujuan->getTable())->position;
                if ($request->limit == 0) {
                    $tujuan->page = ceil($tujuan->position / (10));
                } else {
                    $tujuan->page = ceil($tujuan->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $tujuan->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                // $this->SaveTnlNew('tujuan', 'edit', $data);
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $tujuan
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

            // $zona = (new Tujuan())->processDestroy($id);
            $tujuan = new Tujuan();
            $tujuans = $tujuan->findOrFail($id);
            $tujuan = $tujuan->processDestroy($tujuans);            
            if ($request->from == '') {
                $selected = $this->getPosition($tujuan, $tujuan->getTable(), true);
                $tujuan->position = $selected->position;
                $tujuan->id = $selected->id;
                if ($request->limit == 0) {
                    $tujuan->page = ceil($tujuan->position / (10));
                } else {
                    $tujuan->page = ceil($tujuan->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;

            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                // $this->SaveTnlNew('tujuan', 'delete', $data);
            }
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $tujuan
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
    public function export(RangeExportReportRequest $request) {
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
            $zonas = $decodedResponse['data'];

            $judulLaporan = $zonas[0]['judulLaporan'];

            $i = 0;
            foreach ($zonas as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $zonas[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Tujuan',
                    'index' => 'kodetujuan',
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

            $this->toExcel($judulLaporan, $zonas, $columns);
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
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalKaryawanRequest $request){
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Tujuan())->processApprovalnonaktif($data);

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
     * @Keterangan APRROVAL AKTIF
     */
    public function approvalaktif(ApprovalKaryawanRequest $request){
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Tujuan())->processApprovalaktif($data);

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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('tujuan')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    
}
