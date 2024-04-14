<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotDeletableModel;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyAgenRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Models\Agen;
use App\Http\Requests\StoreAgenRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAgenRequest;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ApprovalAgenRequest;
use App\Models\MyModel;
use DateTime;
use App\Models\Error;

class CustomerController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $agen = new Agen();

        return response([
            'data' => $agen->get(),
            'attributes' => [
                'totalRows' => $agen->totalRows,
                'totalPages' => $agen->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $agen = new Agen();
        $cekdata = $agen->cekvalidasihapus($id);
        $dataMaster = Agen::where('id',$id)->first();
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

                    (new MyModel())->updateEditingBy('agen', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->kodeagen . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
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
            $keterror = 'Data <b>' . $dataMaster->kodeagen . '</b><br>' . $keteranganerror . ' <b> <br> ' . $keterangantambahanerror;

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
                (new MyModel())->updateEditingBy('agen', $id, $aksi);
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
    public function default()
    {

        $agen = new Agen();
        return response([
            'status' => true,
            'data' => $agen->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreAgenRequest $request): JsonResponse
    {
        $data = [
            'id' => $request->id,
            'kodeagen' => $request->kodeagen,
            'namaagen' => $request->namaagen,
            'keterangan' => $request->keterangan,
            'statusaktif' => $request->statusaktif,
            'namaperusahaan' => $request->namaperusahaan,
            'alamat' => $request->alamat,
            'notelp' => $request->notelp,
            'contactperson' => $request->contactperson,
            'top' => $request->top,
            'statustas' => $request->statustas,
            'coa' => $request->coa,
            'coapendapatan' => $request->coapendapatan,
        ];
        DB::beginTransaction();

        try {
            $agen = (new Agen())->processStore($data);
            $agen->position = $this->getPosition($agen, $agen->getTable())->position;
            if ($request->limit==0) {
                $agen->page = ceil($agen->position / (10));
            } else {
                $agen->page = ceil($agen->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $agen
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
            'data' => (new Agen())->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateAgenRequest $request, Agen $customer): JsonResponse
    {

        $data = [
            'id' => $request->id,
            'kodeagen' => $request->kodeagen,
            'namaagen' => $request->namaagen,
            'keterangan' => $request->keterangan,
            'statusaktif' => $request->statusaktif,
            'namaperusahaan' => $request->namaperusahaan,
            'alamat' => $request->alamat,
            'notelp' => $request->notelp,
            'contactperson' => $request->contactperson,
            'top' => $request->top,
            'statustas' => $request->statustas,
            'coa' => $request->coa,
            'coapendapatan' => $request->coapendapatan,
        ];
        DB::beginTransaction();

        try {
            $agen = (new Agen())->processUpdate($customer, $data);
            $agen->position = $this->getPosition($agen, $agen->getTable())->position;
            if ($request->limit==0) {
                $agen->page = ceil($agen->position / (10));
            } else {
                $agen->page = ceil($agen->position / ($request->limit ?? 10));
            }


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $agen
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
    public function destroy(DestroyAgenRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $agen = (new Agen())->processDestroy($id);
            $selected = $this->getPosition($agen, $agen->getTable(), true);
            $agen->position = $selected->position;
            $agen->id = $selected->id;
            if ($request->limit==0) {
                $agen->page = ceil($agen->position / (10));
            } else {
                $agen->page = ceil($agen->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $agen
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

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $agens = $decodedResponse['data'];

            $judulLaporan = $agens[0]['judulLaporan'];

            $i = 0;
            foreach ($agens as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusApproval = $params['statusapproval'];
                $statusTas = $params['statustas'];

                $result = json_decode($statusaktif, true);
                $resultApproval = json_decode($statusApproval, true);
                $resultTas = json_decode($statusTas, true);

                $statusaktif = $result['MEMO'];
                $statusApproval = $resultApproval['MEMO'];
                $statusTas = $resultTas['MEMO'];

                $agens[$i]['statusaktif'] = $statusaktif;
                $agens[$i]['statusapproval'] = $statusApproval;
                $agens[$i]['statustas'] = $statusTas;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Customer',
                    'index' => 'kodeagen',
                ],
                [
                    'label' => 'Nama Customer',
                    'index' => 'namaagen',
                ],
                [
                    'label' => 'Status Tas',
                    'index' => 'statustas',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Nama Perusahaan',
                    'index' => 'namaperusahaan',
                ],
                [
                    'label' => 'Alamat',
                    'index' => 'alamat',
                ],
                [
                    'label' => 'No Telp/HP',
                    'index' => 'notelp',
                ],
                [
                    'label' => 'Nama Kontak',
                    'index' => 'contactperson',
                ],
                [
                    'label' => 'Status Approval',
                    'index' => 'statusapproval',
                ],
                [
                    'label' => 'TOP',
                    'index' => 'top',
                ],
                [
                    'label' => 'User approval',
                    'index' => 'userapproval',
                ],
                [
                    'label' => 'Tgl Approval',
                    'index' => 'tglapproval',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                // [
                //     'label' => 'Jenis Emkl',
                //     'index' => 'jenisemkl',
                // ],
            ];

            $this->toExcel($judulLaporan, $agens, $columns);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('agen')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(ApprovalAgenRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
                'nama' => $request->nama
            ];
            (new Agen())->processApproval($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    
    public function approvalOld(Agen $agen)
    {
        DB::beginTransaction();

        try {
            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($agen->statusapproval == $statusApproval->id) {
                $agen->statusapproval = $statusNonApproval->id;
            } else {
                $agen->statusapproval = $statusApproval->id;
            }

            $agen->tglapproval = date('Y-m-d', time());
            $agen->userapproval = auth('api')->user()->name;

            if ($agen->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($agen->getTable()),
                    'postingdari' => 'UN/APPROVE AGEN',
                    'idtrans' => $agen->id,
                    'nobuktitrans' => $agen->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $agen->toArray(),
                    'modifiedby' => $agen->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $agen
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

     /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalAgenRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
                'nama' => $request->nama
            ];
            (new Agen())->processApprovalnonaktif($data);

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
