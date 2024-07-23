<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Carbon\Carbon;
use App\Models\Error;
use App\Models\Mandor;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\LogTrail;

use App\Models\MandorDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreMandorRequest;
use App\Http\Requests\UpdateMandorRequest;
use App\Http\Requests\DestroyMandorRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;

class MandorController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $mandor = new Mandor();

        return response([
            'data' => $mandor->get(),
            'attributes' => [
                'totalRows' => $mandor->totalRows,
                'totalPages' => $mandor->totalPages
            ]
        ]);
    }

    public function cekValidasi(Request $request, $id)
    {
        $mandor = new Mandor();
        $dataMaster = $mandor->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $server = '';
        if ($request->from == 'tas') {
            $id = db::table('mandor')->from(db::raw("mandor a with (readuncommitted)"))
                ->select('a.id')
                ->where('a.tas_id', $id)->first()->id ?? 0;
            $server = ' tnl';
        }
        $cekdata = $mandor->cekvalidasihapus($id);

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();

        if ($cekdata['kondisi'] == true) {
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $server = ' tas';
            }
            goto selesai;
        }

        $data['tas_id'] = $id;
        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            $data = [
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            $cektnl = $this->CekValidasiToTnl("mandor/" . $id . "/cekValidasi", $data);
            return response($cektnl['data']);
        }
        selesai:

        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . "$server)' as keterangan")
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
                    (new MyModel())->updateEditingBy('mandor', $id, $aksi);
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
                $keterror = 'Data <b>' . $dataMaster->namamandor . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                
                $data = [
                    'status' => true,
                    'message' => ["keterangan"=>$keterror],
                    'errors' => '',
                    'kondisi' => true,
                ];

                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('mandor', $id, $aksi);
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

        $mandor = new Mandor();
        return response([
            'status' => true,
            'data' => $mandor->default(),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreMandorRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'namamandor' => $request->namamandor,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
                'users' => $request->users,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
                'tas_id' => $request->tas_id,
                'user_id' => $request->user_id
            ];
            // $mandor = (new Mandor())->processStore($data);
            $mandor = new Mandor();
            $datamandor=$mandor->processStore($data, $mandor);
            // $mandor->processStore($data, $mandor);

            if ($request->from == '') {
                $datamandor->position = $this->getPosition($datamandor, $datamandor->getTable())->position;
                if ($request->limit == 0) {
                    $datamandor->page = ceil($datamandor->position / (10));
                } else {
                    $datamandor->page = ceil($datamandor->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $datamandor->id;
            $data['detail_tas_id'] = $datamandor->detailTasId;
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $datamandortnl=$this->SaveTnlMasterDetail('mandor', 'add', $data);
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $datamandor
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Mandor $mandor)
    {
        return response([
            'status' => true,
            'data' => $mandor->findAll($mandor->id),
            'detail' => (new MandorDetail())->findAll($mandor->id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateMandorRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = [
                'namamandor' => $request->namamandor,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
                'users' => $request->users,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
                'tas_id' => $request->tas_id,
                'user_id' => $request->user_id
            ];
            // $mandor = (new Mandor())->processUpdate($mandor, $data);
            $mandor = new Mandor();
            $mandors = $mandor->findOrFail($id);
            $datamandor = $mandor->processUpdate($mandors, $data);            
            if ($request->from == '') {
                $datamandor->position = $this->getPosition($datamandor, $datamandor->getTable())->position;
                if ($request->limit == 0) {
                    $datamandor->page = ceil($datamandor->position / (10));
                } else {
                    $datamandor->page = ceil($datamandor->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            
            // 
            $data['tas_id'] = $datamandor->id;
            $data['detail_tas_id'] = $datamandor->detailTasId;
            
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $datamandortnl=$this->SaveTnlMasterDetail('mandor', 'edit', $data);
            }

            // 
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $datamandor
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
    public function destroy(DestroyMandorRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            // $mandor = (new Mandor())->processDestroy($id);
            $mandor = new Mandor();
            $mandors = $mandor->findOrFail($id);
            $mandor = $mandor->processDestroy($mandors);            
            if ($request->from == '') {
                $selected = $this->getPosition($mandor, $mandor->getTable(), true);
                $mandor->position = $selected->position;
                $mandor->id = $selected->id;
                if ($request->limit == 0) {
                    $mandor->page = ceil($mandor->position / (10));
                } else {
                    $mandor->page = ceil($mandor->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();

            $data = [
                'namamandor' => $request->namamandor,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
                'user_id' => $request->user_id,
            ];
            $data['tas_id'] = $id;
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('mandor', 'delete', $data);
            }
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $mandor
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('mandor')->getColumns();

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
            $mandors = $decodedResponse['data'];

            $judulLaporan = $mandors[0]['judulLaporan'];


            $i = 0;
            foreach ($mandors as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $mandors[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Nama Mandor',
                    'index' => 'namamandor',
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

            $this->toExcel($judulLaporan, $mandors, $columns);
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
    public function approvalnonaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Mandor())->processApprovalnonaktif($data);

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
            (new Mandor())->processApprovalaktif($data);

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
