<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MainAkunPusat;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreMainAkunPusatRequest;
use App\Http\Requests\UpdateMainAkunPusatRequest;
use App\Http\Requests\DestroyMainAkunPusatRequest;
use App\Models\MyModel;
use App\Models\Error;
use DateTime;
use App\Models\Parameter;

class MainAkunPusatController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $mainAkunPusat = new MainAkunPusat();

        return response([
            'data' => $mainAkunPusat->get(),
            'attributes' => [
                'totalRows' => $mainAkunPusat->totalRows,
                'totalPages' => $mainAkunPusat->totalPages
            ]
        ]);
    }
    public function default()
    {
        $mainAkunPusat = new MainAkunPusat();
        return response([
            'status' => true,
            'data' => $mainAkunPusat->default()
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreMainAkunPusatRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'coa' => $request->coa,
                'keterangancoa' => $request->keterangancoa,
                'type' => $request->type,
                'type_id' => $request->type_id,
                'akuntansi_id' => $request->akuntansi_id,
                'parent' => $request->parent,
                'statusparent' => $request->statusparent,
                'statusneraca' => $request->statusneraca,
                'statuslabarugi' => $request->statuslabarugi,
                'statusaktif' => $request->statusaktif,
            ];
            // $mainAkunPusat = (new MainAkunPusat())->processStore($data);
            $mainAkunPusat = new MainAkunPusat();
            $mainAkunPusat->processStore($data, $mainAkunPusat);            
            if ($request->from == '') {

            $mainAkunPusat->position = $this->getPosition($mainAkunPusat, $mainAkunPusat->getTable())->position;
            if ($request->limit==0) {
                $mainAkunPusat->page = ceil($mainAkunPusat->position / (10));
            } else {
                $mainAkunPusat->page = ceil($mainAkunPusat->position / ($request->limit ?? 10));
            }

        }

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $mainAkunPusat->id;

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('mainAkunPusat', 'add', $data);
            $this->SaveTnlNew('mainAkunPusat', 'add', $data);
        }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $mainAkunPusat
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $mainAkunPusat = (new MainAkunPusat())->findAll($id);
        return response([
            'status' => true,
            'data' => $mainAkunPusat
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateMainAkunPusatRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'coa' => $request->coa,
                'keterangancoa' => $request->keterangancoa,
                'type' => $request->type,
                'type_id' => $request->type_id,
                'akuntansi_id' => $request->akuntansi_id,
                'parent' => $request->parent,
                'statusparent' => $request->statusparent,
                'statusneraca' => $request->statusneraca,
                'statuslabarugi' => $request->statuslabarugi,
                'statusaktif' => $request->statusaktif,
            ];
            $mainAkunPusat = new MainAkunPusat();
            $mainAkunPusats = $mainAkunPusat->findOrFail($id);
            $mainAkunPusat = $mainAkunPusat->processUpdate($mainAkunPusats, $data);


            // $mainAkunPusat = (new MainAkunPusat())->processUpdate($mainakunpusat, $data);
            if ($request->from == '') {

            $mainAkunPusat->position = $this->getPosition($mainAkunPusat, $mainAkunPusat->getTable())->position;
            if ($request->limit==0) {
                $mainAkunPusat->page = ceil($mainAkunPusat->position / (10));
            } else {
                $mainAkunPusat->page = ceil($mainAkunPusat->position / ($request->limit ?? 10));
            }
        }

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $mainAkunPusat->id;

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('mainAkunPusat', 'edit', $data);
            $this->SaveTnlNew('mainAkunPusat', 'edit', $data);
        }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $mainAkunPusat
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
    public function destroy(DestroyMainAkunPusatRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $mainAkunPusat = new MainAkunPusat();
            $mainAkunPusats = $mainAkunPusat->findOrFail($id);
            $mainAkunPusat = $mainAkunPusat->processDestroy($mainAkunPusats);


            // $mainAkunPusat = (new MainAkunPusat())->processDestroy($id);
            if ($request->from == '') {

            $selected = $this->getPosition($mainAkunPusat, $mainAkunPusat->getTable(), true);
            $mainAkunPusat->position = $selected->position;
            $mainAkunPusat->id = $selected->id;
            if ($request->limit==0) {
                $mainAkunPusat->page = ceil($mainAkunPusat->position / (10));
            } else {
                $mainAkunPusat->page = ceil($mainAkunPusat->position / ($request->limit ?? 10));
            }
        }
        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $id;

        $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('mainAkunPusat', 'delete', $data);
            $this->SaveTnlNew('mainAkunPusat', 'delete', $data);
        }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $mainAkunPusat
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
            (new MainAkunPusat())->processApprovalnonaktif($data);

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
            (new MainAkunPusat())->processApprovalaktif($data);

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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('mainakunpusat')->getColumns();

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
            return response([
                'status' => true,
            ]);
        } else {
            header('Access-Control-Allow-Origin: *');

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $akunpusats = $decodedResponse['data'];

            $judulLaporan = $akunpusats[0]['judulLaporan'];


            $i = 0;
            foreach ($akunpusats as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusParent = $params['statusparent'];
                $statusNeraca = $params['statusneraca'];
                $statusLabaRugi = $params['statuslabarugi'];

                $result = json_decode($statusaktif, true);
                $resultParent = json_decode($statusParent, true);
                $resultNeraca = json_decode($statusNeraca, true);
                $resultLabaRugi = json_decode($statusLabaRugi, true);

                $format = $result['MEMO'];
                $statusParent = ($resultParent != '') ? $resultParent['MEMO'] : '';
                $statusNeraca = $resultNeraca['MEMO'];
                $statusLabaRugi = $resultLabaRugi['MEMO'];


                $akunpusats[$i]['statusaktif'] = $format;
                $akunpusats[$i]['statusparent'] = $statusParent;
                $akunpusats[$i]['statusneraca'] = $statusNeraca;
                $akunpusats[$i]['statuslabarugi'] = $statusLabaRugi;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Perkiraan',
                    'index' => 'coa',
                ],
                [
                    'label' => 'Nama Perkiraan',
                    'index' => 'keterangancoa',
                ],
                [
                    'label' => 'Type',
                    'index' => 'type',
                ],
                [
                    'label' => 'Parent',
                    'index' => 'parent',
                ],
                [
                    'label' => 'Status Parent',
                    'index' => 'statusparent',
                ],
                [
                    'label' => 'Status Neraca',
                    'index' => 'statusneraca',
                ],
                [
                    'label' => 'Status Laba Rugi',
                    'index' => 'statuslabarugi',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],

            ];
            $this->toExcel($judulLaporan, $akunpusats, $columns);
        }
    }

    public function cekValidasi($id)
    {
        $mainakunPusat = new MainAkunPusat();
        $cekdata = $mainakunPusat->cekValidasi($id);
        $dataMaster = MainAkunPusat::where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
      
        $aksi = request()->aksi ?? '';
        $aksi =strtoupper($aksi);
        if( $aksi == 'EDIT'){
            $cekdata['kondisi'] = false;
        }
        if ($useredit != '' && $useredit != $user) {
           
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->updateEditingBy('mainakunpusat', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->keterangancoa . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }            
            

            } else if ($cekdata['kondisi'] == true) {
            // $query = DB::table('error')
            //     ->select(
            //         DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
            //     )
            //     ->where('kodeerror', '=', 'SATL')
            //     ->get();
            // $keterangan = $query['0'];
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $keterror = 'Data <b>' . $dataMaster->keterangancoa . '</b><br>' . $keteranganerror . ' <b> <br> ' . $keterangantambahanerror;

            $data = [
                // 'status' => false,
                // 'message' => $keterangan,
                // 'errors' => '',
                // 'kondisi' => $cekdata['kondisi'],
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SATL2',
                'statuspesan' => 'warning',

            ];

            return response($data);

        } else {
            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->updateEditingBy('mainakunpusat', $id, $aksi);
            }            
            $data = [
                'error' => false,
                'message' => '',
                'kodeerror' => '',
                'statuspesan' => 'success',                
                // 'status' => false,
                // 'message' => '',
                // 'errors' => '',
                // 'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }
}
