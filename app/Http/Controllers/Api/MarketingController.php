<?php

namespace App\Http\Controllers\Api;

use App\Models\Error;
use App\Models\MyModel;
use App\Models\Marketing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMarketingRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\UpadateMarketingRequest;
use App\Http\Requests\RangeExportReportRequest;

class MarketingController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $marketing = new Marketing();

        return response([
            'data' => $marketing->get(),
            'attributes' => [
                'totalRows' => $marketing->totalRows,
                'totalPages' => $marketing->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreMarketingRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                "kodemarketing" => $request->kodemarketing ?? '',
                "keterangan" => $request->keterangan ?? '',
                "statusaktif" => $request->statusaktif ?? '',
            ];
            $marketing = new Marketing();
            $marketing->processStore($data, $marketing);            
            if ($request->from == '') {
                $marketing->position = $this->getPosition($marketing, $marketing->getTable())->position;
                if ($request->limit == 0) {
                    $marketing->page = ceil($marketing->position / (10));
                } else {
                    $marketing->page = ceil($marketing->position / ($request->limit ?? 10));
                }
            }


            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $marketing->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('marketing', 'add', $data);
            }


            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $marketing
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $marketing = new Marketing();
        return response([
            'status' => true,
            'data' => $marketing->findAll($id)
        ]);
    }
    
    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpadateMarketingRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $data = [
                "kodemarketing" => $request->kodemarketing ?? '',
                "keterangan" => $request->keterangan ?? '',
                "statusaktif" => $request->statusaktif ?? '',
            ];

            $marketing = new Marketing();
            $marketings = $marketing->findOrFail($id);
            $marketing = $marketing->processUpdate($marketings, $data);
            if ($request->from == '') {
                $marketing->position = $this->getPosition($marketing, $marketing->getTable())->position;
                if ($request->limit == 0) {
                    $marketing->page = ceil($marketing->position / (10));
                } else {
                    $marketing->page = ceil($marketing->position / ($request->limit ?? 10));
                }
            }


            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $marketing->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('marketing', 'edit', $data);
            }
            DB::commit();
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $marketing
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    
    }
    
    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request,$id)
    {
        DB::beginTransaction();
        try {
            $marketing = new Marketing();
            $marketings = $marketing->findOrFail($id);
            $marketing = $marketing->processDestroy($marketings);
            if ($request->from == '') {
                $selected = $this->getPosition($marketing, $marketing->getTable(), true);
                $marketing->position = $selected->position;
                $marketing->id = $selected->id;
                if ($request->limit == 0) {
                    $marketing->page = ceil($marketing->position / (10));
                } else {
                    $marketing->page = ceil($marketing->position / ($request->limit ?? 10));
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
                'data' => $marketing
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
            $merks = $decodedResponse['data'];

            $judulLaporan = $merks[0]['judulLaporan'];

            $i = 0;
            foreach ($merks as $index => $params) {

                $statusaktif = $params['statusaktif_memo'];

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
                    'label' => 'Kode Marketing',
                    'index' => 'kodemarketing',
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
            (new Marketing())->processApprovalnonaktif($data);

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
    public function approvalaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Marketing())->processApprovalaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function cekValidasi($id)
    {
        $marketing = new Marketing();
        $dataMaster = $marketing->where('id', $id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        // $cekdata = $marketing->cekvalidasihapus($id);
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
                    (new MyModel())->updateEditingBy('marketing', $id, $aksi);
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
                $keterror = 'Data <b>' . $dataMaster->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;

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
            (new MyModel())->updateEditingBy('marketing', $id, $aksi);

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    public function fieldLength()
    {

        $marketing = new Marketing();
        return response([
            'status' => true,
            'data' => [],
        ]);
    }
    public function default()
    {

        $marketing = new Marketing();
        return response([
            'status' => true,
            'data' => $marketing->default()
        ]);
    }
}
