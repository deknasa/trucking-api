<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\LogTrail;

use App\Models\Parameter;
use App\Models\DataRitasi;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

use Illuminate\Database\QueryException;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Requests\StoreDataRitasiRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\UpdateDataRitasiRequest;
use App\Http\Requests\DestroyDataRitasiRequest;
use App\Http\Requests\RangeExportReportRequest;

class DataRitasiController extends Controller
{

   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $dataritasi = new dataritasi();
        return response([
            'data' => $dataritasi->get(),
            'attributes' => [
                'totalRows' => $dataritasi->totalRows,
                'totalPages' => $dataritasi->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $dataMaster = DataRitasi::where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        if ($useredit != '' && $useredit != $user) {
           
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->updateEditingBy('dataritasi', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' .$dataMaster->email . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }            
            
        } else {
            
            (new MyModel())->updateEditingBy('dataritasi', $id, $aksi);
                
            $data = [
                'error' => false,
                'message' => '',
                'kodeerror' => '',
                'statuspesan' => 'success',
            ];
            

            return response($data);
        }
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    public function default()
    {

        $dataritasi = new DataRitasi();
        return response([
            'status' => true,
            'data' => $dataritasi->default(),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA     
     */

    public function store(StoreDataRitasiRequest $request)
    {
        DB::beginTransaction();
        $data = [
            'id' => $request->id,
            'statusritasi' => $request->statusritasi,
            'nominal' => $request->nominal,
            'statusaktif' => $request->statusaktif,
        ];

        try {

            $dataritasi = new DataRitasi();
            $dataritasi->processStore($data, $dataritasi);
            
            if ($request->from == '') {
                /* Set position and page */
                $selected = $this->getPosition($dataritasi, $dataritasi->getTable());
                $dataritasi->position = $selected->position;
                if ($request->limit==0) {
                    $dataritasi->page = ceil($dataritasi->position / (10));
                } else {
                    $dataritasi->page = ceil($dataritasi->position / ($request->limit ?? 10));
                }
            }
            
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $dataritasi->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                // $this->saveToTnl('dataritasi', 'add', $data);
                $this->SaveTnlNew('dataritasi', 'add', $data);
            }
            DB::commit();
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $dataritasi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(DataRitasi $dataritasi)
    {
        return response([
            'status' => true,
            'data' => (new DataRitasi())->findAll($dataritasi->id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateDataRitasiRequest $request, $id)
    {
        DB::beginTransaction();
        $data = [
            'id' => $request->id,
            'statusritasi' => $request->statusritasi,
            'nominal' => $request->nominal,
            'statusaktif' => $request->statusaktif,
        ];        
        try {
            // $dataritasi = new dataritasi();


 
            $dataritasi = new DataRitasi();
            $dataritasis = $dataritasi->findOrFail($id);
            $dataritasi = $dataritasi->processUpdate($dataritasis, $data);
            if ($request->from == '') {
                /* Set position and page */
                $selected = $this->getPosition($dataritasi, $dataritasi->getTable());
                $dataritasi->position = $selected->position;
                if ($request->limit==0) {
                    $dataritasi->page = ceil($dataritasi->position / (10));
                } else {
                    $dataritasi->page = ceil($dataritasi->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $dataritasi->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                // $this->saveToTnl('dataritasi', 'edit', $data);
                $this->SaveTnlNew('dataritasi', 'edit', $data);
            }
         
            DB::commit();
            
            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $dataritasi
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

    public function destroy(DestroyDataRitasiRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $dataritasi = new DataRitasi();
            $dataritasis = $dataritasi->findOrFail($id);
            $dataritasi = $dataritasi->processDestroy($dataritasis);

            if ($request->from == '') {
                $selected = $this->getPosition($dataritasi, $dataritasi->getTable(), true);
                $dataritasi->position = $selected->position;
                $dataritasi->id = $selected->id;
                if ($request->limit == 0) {
                    $dataritasi->page = ceil($dataritasi->position / (10));
                } else {
                    $dataritasi->page = ceil($dataritasi->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;

            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                // $this->saveToTnl('dataritasi', 'delete', $data);
                $this->SaveTnlNew('dataritasi', 'delete', $data);
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $dataritasi
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
            (new DataRitasi())->processApprovalnonaktif($data);

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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('dataritasi')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
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
            $dataritasi = $decodedResponse['data'];

            $judulLaporan = $dataritasi[0]['judulLaporan'];
            $i = 0;
            foreach ($dataritasi as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $dataritasi[$i]['statusaktif'] = $statusaktif;

                $nominal = number_format($params['nominal'], 2, ',', '.');
                $dataritasi[$i]['nominal'] = $nominal;

                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Status Ritasi',
                    'index' => 'statusritasi',
                ],
                [
                    'label' => 'nominal',
                    'index' => 'nominal',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $dataritasi, $columns);
        }
    }
}
