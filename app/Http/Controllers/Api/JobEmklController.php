<?php

namespace App\Http\Controllers\Api;

use App\Models\Error;
use App\Models\JobEmkl;
use App\Models\MyModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobEmklRequest;
use App\Http\Requests\RangeExportReportRequest;
use Symfony\Component\HttpKernel\Controller\ErrorController;

class JobEmklController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $jobEmkl = new JobEmkl();

        return response([
            'data' => $jobEmkl->get(),
            'attributes' => [
                'totalRows' => $jobEmkl->totalRows,
                'totalPages' => $jobEmkl->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreJobEmklRequest $request)
    {
        
        DB::beginTransaction();

        try {
            $data = [
                "tglbukti" => $request->tglbukti ?? '',
                "shipper_id" => $request->shipper_id ?? '',
                "shipper" => $request->shipper ?? '',
                "tujuan_id" => $request->tujuan_id ?? '',
                "tujuan" => $request->tujuan ?? '',
                "container_id" => $request->container_id ?? '',
                "container" => $request->container ?? '',
                "jenisorder_id" => $request->jenisorder_id ?? '',
                "jenisorder" => $request->jenisorder ?? '',
                "kapal" => $request->kapal ?? '',
                "voy" => $request->voy ?? '',
                "destination" => $request->destination ?? '',
                "nocont" => $request->nocont ?? '',
                "noseal" => $request->noseal ?? '',
                "marketing_id" => $request->marketing_id ?? '',
                "lokasibongkarmuat" => $request->lokasibongkarmuat ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            $jobEmkl = new JobEmkl();
            $jobEmkl->processStore($data, $jobEmkl);            
            if ($request->from == '') {
                $jobEmkl->position = $this->getPosition($jobEmkl, $jobEmkl->getTable())->position;
                // dd($jobEmkl);
                if ($request->limit == 0) {
                    $jobEmkl->page = ceil($jobEmkl->position / (10));
                } else {
                    $jobEmkl->page = ceil($jobEmkl->position / ($request->limit ?? 10));
                }
            }


            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $jobEmkl->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('jobemkl', 'add', $data);
            }


            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jobEmkl
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $jobEmkl = new JobEmkl();
        return response([
            'status' => true,
            'data' => $jobEmkl->findAll($id)
        ]);
    }
    
    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(StoreJobEmklRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $data = [
                "tglbukti" => $request->tglbukti ?? '',
                "shipper_id" => $request->shipper_id ?? '',
                "shipper" => $request->shipper ?? '',
                "tujuan_id" => $request->tujuan_id ?? '',
                "tujuan" => $request->tujuan ?? '',
                "container_id" => $request->container_id ?? '',
                "container" => $request->container ?? '',
                "jenisorder_id" => $request->jenisorder_id ?? '',
                "jenisorder" => $request->jenisorder ?? '',
                "kapal" => $request->kapal ?? '',
                "voy" => $request->voy ?? '',
                "destination" => $request->destination ?? '',
                "nocont" => $request->nocont ?? '',
                "noseal" => $request->noseal ?? '',
                "marketing_id" => $request->marketing_id ?? '',
                "lokasibongkarmuat" => $request->lokasibongkarmuat ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',

            ];

            $jobEmkl = new JobEmkl();
            $jobEmkls = $jobEmkl->findOrFail($id);
            $jobEmkl = $jobEmkl->processUpdate($jobEmkls, $data);
            if ($request->from == '') {
                $jobEmkl->position = $this->getPosition($jobEmkl, $jobEmkl->getTable())->position;
                if ($request->limit == 0) {
                    $jobEmkl->page = ceil($jobEmkl->position / (10));
                } else {
                    $jobEmkl->page = ceil($jobEmkl->position / ($request->limit ?? 10));
                }
            }


            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $jobEmkl->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('jobemkl', 'edit', $data);
            }
            DB::commit();
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jobEmkl
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
            $jobEmkl = new JobEmkl();
            $jobEmkls = $jobEmkl->findOrFail($id);
            $jobEmkl = $jobEmkl->processDestroy($jobEmkls);
            if ($request->from == '') {
                $selected = $this->getPosition($jobEmkl, $jobEmkl->getTable(), true);
                $jobEmkl->position = $selected->position;
                $jobEmkl->id = $selected->id;
                if ($request->limit == 0) {
                    $jobEmkl->page = ceil($jobEmkl->position / (10));
                } else {
                    $jobEmkl->page = ceil($jobEmkl->position / ($request->limit ?? 10));
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
                'data' => $jobEmkl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function cekValidasi($id)
    {
        $jobEmkl = new JobEmkl();
        $dataMaster = $jobEmkl->where('id', $id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        // $cekdata = $jobEmkl->cekvalidasihapus($id);
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
                    (new MyModel())->updateEditingBy('jobemkl', $id, $aksi);
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
            (new MyModel())->updateEditingBy('jobemkl', $id, $aksi);

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
    public function nominalprediksi(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                "nominal" => $request->nominal,
                "keteranganBiaya" => $request->keteranganBiaya,
            ];

            $jobEmkl = new JobEmkl();
            $jobEmkls = $jobEmkl->findOrFail($request->id);
            $jobEmkl = $jobEmkl->processNominalPrediksi($jobEmkls, $data);
            
            DB::commit();
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jobEmkl
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function default()
    {

        $jobEmkl = new JobEmkl();
        return response([
            'status' => true,
            'data' => [],
        ]);
    }
    public function fieldLength()
    {

        $jobEmkl = new JobEmkl();
        return response([
            'status' => true,
            'data' => [],
        ]);
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
            $jobEmkl = $decodedResponse['data'];
            
            $judulLaporan = $jobEmkl[0]['judulLaporan'];
            
            $i = 0;
            // foreach ($jobEmkl as $index => $params) {
                
            //     $statusaktif = $params['statusaktif'];
                
            //     $result = json_decode($statusaktif, true);
                
            //     $statusaktif = $result['MEMO'];
                
                
            //     $jobEmkl[$i]['statusaktif'] = $statusaktif;
                
                
            //     $i++;
            // }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    "label"=>"nobukti",
                    "index"=>"nobukti",
                ],
                [
                    "label"=>"tglbukti",
                    "index"=>"tglbukti",
                ],
                [
                    "label"=>"shipper",
                    "index"=>"shipper",
                ],
                [
                    "label"=>"tujuan",
                    "index"=>"tujuan",
                ],
                [
                    "label"=>"container",
                    "index"=>"container",
                ],
                [
                    "label"=>"jenis order",
                    "index"=>"jenisorder",
                ],
                [
                    "label"=>"kapal",
                    "index"=>"kapal",
                ],
                [
                    "label"=>"destination",
                    "index"=>"destination",
                ],
                [
                    "label"=>"nocont",
                    "index"=>"nocont",
                ],
                [
                    "label"=>"noseal",
                    "index"=>"noseal",
                ],
            ];
            
            $this->toExcel($judulLaporan, $jobEmkl, $columns);
        }
    }
    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }
    
}
