<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\HariLibur;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreHariLiburRequest;
use App\Http\Requests\UpdateHariLiburRequest;
use App\Http\Requests\DestroyHariLiburRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Models\Locking;

class HariLiburController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $hariLibur = new HariLibur();
        return response([
            'data' => $hariLibur->get(),
            'attributes' => [
                'totalRows' => $hariLibur->totalRows,
                'totalPages' => $hariLibur->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $locking = new Locking();
        $getEditing = $locking->getEditing('harilibur', $id);

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $user = auth('api')->user()->name;
        $useredit = $getEditing->editing_by ?? '';
        $aksi = request()->aksi ?? '';
       
        if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));

            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'harilibur',$useredit);                  
                }

                $data = [
                    'error' => false,
                    'message' => '',
                    'kodeerror' => '',
                    'statuspesan' => 'success',
                ];
                return response($data);
            } else {
                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $queryHariLibur = DB::table("harilibur")->from(DB::raw("harilibur with (readuncommitted)"))->where('id', $id)->first();
                $keterror = 'Data ' . $queryHariLibur->keterangan . ' ' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }            
            
        } else {
            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                
                // (new MyModel())->updateEditingBy('harilibur', $id, $aksi);
                (new MyModel())->createLockEditing($id, 'harilibur');
            }
            $data = [
                'error' => false,
                'message' => '',
                'kodeerror' => '',
                'statuspesan' => 'success',
            ];
            

            return response($data);
        }
    }

    public function default()
    {
        $hariLibur = new HariLibur();
        return response([
            'status' => true,
            'data' => $hariLibur->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreHariLiburRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tgl' => date('Y-m-d', strtotime($request->tgl)),
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
                'tas_id' => $request->tas_id ?? '',
                "key" => $request->key,
                "value" => $request->value,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',                
            ];
            // $hariLibur = (new HariLibur())->processStore($data);
            $hariLibur = new HariLibur();
            $hariLibur->processStore($data, $hariLibur);

            $hariLibur->position = $this->getPosition($hariLibur, $hariLibur->getTable())->position;
            if ($request->from == '') {            
                    if ($request->limit==0) {
                        $hariLibur->page = ceil($hariLibur->position / (10));
                    } else {
                        $hariLibur->page = ceil($hariLibur->position / ($request->limit ?? 10));
                    }
              }       

              $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
              $data['tas_id'] = $hariLibur->id;
  
              if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                  // $this->saveToTnl('cabang', 'add', $data);
                  $this->SaveTnlNew('harilibur', 'add', $data);
              }              
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hariLibur
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $hariLibur = HariLibur::findAll($id);
        return response([
            'status' => true,
            'data' => $hariLibur
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateHariLiburRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $data = [
                'id' => $request->id,
                'tgl' => date('Y-m-d', strtotime($request->tgl)),
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
                "key" => $request->key,
                "value" => $request->value,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',            ];

                
            // $harilibur = (new harilibur())->processUpdate($harilibur, $data);
            $harilibur = new HariLibur();
            $hariliburs = $harilibur->findOrFail($id);
            $harilibur = $harilibur->processUpdate($hariliburs, $data);            
            $harilibur->position = $this->getPosition($harilibur, $harilibur->getTable())->position;
            if ($request->from == '') {
                if ($request->limit==0) {
                $harilibur->page = ceil($harilibur->position / (10));
            } else {
                $harilibur->page = ceil($harilibur->position / ($request->limit ?? 10));
            }
        }

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $harilibur->id;

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('cabang', 'edit', $data);
            $this->SaveTnlNew('harilibur', 'edit', $data);
        }        

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah.',
                'data' => $harilibur
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
            $harilibur = new HariLibur();
            $hariliburs = $harilibur->findOrFail($id);
            $harilibur = $harilibur->processDestroy($hariliburs);            
            // $harilibur = (new HariLibur())->processDestroy($id);
            $selected = $this->getPosition($harilibur, $harilibur->getTable(), true);
            $harilibur->position = $selected->position;
            $harilibur->id = $selected->id;
            if ($request->from == '') {            
            if ($request->limit==0) {
                $harilibur->page = ceil($harilibur->position / (10));
            } else {
                $harilibur->page = ceil($harilibur->position / ($request->limit ?? 10));
            }
        }
        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $id;

        $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('harilibur', 'delete', $data);
            $this->SaveTnlNew('harilibur', 'delete', $data);
        }        
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $harilibur
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('harilibur')->getColumns();

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
            $hariLiburs = $decodedResponse['data'];

            $judulLaporan = $hariLiburs[0]['judulLaporan'];

            $i = 0;
            foreach ($hariLiburs as $index => $params) {


                $statusaktif = $params['statusaktif'];


                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $hariLiburs[$i]['statusaktif'] = $statusaktif;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',

                ],
                [
                    'label' => 'Tanggal',
                    'index' => 'tgl',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Status',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $hariLiburs, $columns);
        }
    }
}
