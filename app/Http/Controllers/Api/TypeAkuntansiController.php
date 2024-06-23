<?php


namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\TypeAkuntansi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreTypeAkuntansiRequest;
use App\Http\Requests\UpdateTypeAkuntansiRequest;
use App\Http\Requests\DestroyTypeAkuntansiRequest;
use App\Models\MyModel;
use App\Models\Error;
use DateTime;

class TypeAkuntansiController extends Controller
{

    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {

        $typeakuntansi = new TypeAkuntansi();
        return response([
            'data' => $typeakuntansi->get(),
            'attributes' => [
                'totalRows' => $typeakuntansi->totalRows,
                'totalPages' => $typeakuntansi->totalPages
            ]
        ]);
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

        $typeakuntansi = new TypeAkuntansi();
        return response([
            'status' => true,
            'data' => $typeakuntansi->default(),
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreTypeAkuntansiRequest $request): JsonResponse
    {
        DB::beginTransaction();

        $data = [
            "kodetype" => $request->kodetype,
            "order" => $request->order,
            "keterangantype" => $request->keterangantype,
            "akuntansi_id" => $request->akuntansi_id,
            "statusaktif" => $request->statusaktif,
        ];

        try {
            // $typeakuntansi = (new TypeAkuntansi())->processStore([              
            // ]);
            $typeakuntansi = new TypeAkuntansi();
            $typeakuntansi->processStore($data, $typeakuntansi);

            if ($request->from == '') {
            $typeakuntansi->position = $this->getPosition($typeakuntansi, $typeakuntansi->getTable())->position;
            if ($request->limit == 0) {
                $typeakuntansi->page = ceil($typeakuntansi->position / (10));
            } else {
                $typeakuntansi->page = ceil($typeakuntansi->position / ($request->limit ?? 10));
            }
        }

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $typeakuntansi->id;

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('typeakuntansi', 'add', $data);
            $this->SaveTnlNew('typeakuntansi', 'add', $data);
        }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $typeakuntansi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function show($id)
    {
        $typeakuntansi = new TypeAkuntansi();
        return response([
            'status' => true,
            'data' => $typeakuntansi->find($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTypeAkuntansiRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        $data = [
            "kodetype" => $request->kodetype,
            "order" => $request->order,
            "keterangantype" => $request->keterangantype,
            "akuntansi_id" => $request->akuntansi_id,
            "statusaktif" => $request->statusaktif,
        ];

        try {
            // $typeakuntansi = (new TypeAkuntansi())->processUpdate($typeakuntansi, [
            //     "kodetype" => $request->kodetype,
            //     "order" => $request->order,
            //     "keterangantype" => $request->keterangantype,
            //     "akuntansi_id" => $request->akuntansi_id,
            //     "statusaktif" => $request->statusaktif,
            // ]);
            $typeakuntansi = new TypeAkuntansi();
            $typeakuntansis = $typeakuntansi->findOrFail($id);
            $typeakuntansi = $typeakuntansi->processUpdate($typeakuntansis, $data);


            if ($request->from == '') {

            $typeakuntansi->position = $this->getPosition($typeakuntansi, $typeakuntansi->getTable())->position;
            if ($request->limit == 0) {
                $typeakuntansi->page = ceil($typeakuntansi->position / (10));
            } else {
                $typeakuntansi->page = ceil($typeakuntansi->position / ($request->limit ?? 10));
            }
        }

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $typeakuntansi->id;

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('typeakuntansi', 'edit', $data);
            $this->SaveTnlNew('typeakuntansi', 'edit', $data);
        }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah.',
                'data' => $typeakuntansi
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
    public function destroy(DestroyTypeAkuntansiRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            // $typeakuntansi = (new TypeAkuntansi())->processDestroy($id);
            $typeakuntansi = new TypeAkuntansi();
            $typeakuntansis = $typeakuntansi->findOrFail($id);
            $typeakuntansi = $typeakuntansi->processDestroy($typeakuntansis);


            if ($request->from == '') {

            $selected = $this->getPosition($typeakuntansi, $typeakuntansi->getTable(), true);
            $typeakuntansi->position = $selected->position;
            $typeakuntansi->id = $selected->id;
            if ($request->limit == 0) {
                $typeakuntansi->page = ceil($typeakuntansi->position / (10));
            } else {
                $typeakuntansi->page = ceil($typeakuntansi->position / ($request->limit ?? 10));
            }
        }

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $id;

        $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('typeakuntansi', 'delete', $data);
            $this->SaveTnlNew('typeakuntansi', 'delete', $data);
        }


            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $typeakuntansi
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
            (new TypeAkuntansi())->processApprovalnonaktif($data);

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
        $dataMaster = TypeAkuntansi::where('id',$id)->first();
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

                    (new MyModel())->updateEditingBy('typeakuntansi', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->kodetype . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
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
                (new MyModel())->updateEditingBy('typeakuntansi', $id, $aksi);
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
            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $akuntansi = $decodedResponse['data'];

            $judulLaporan = $akuntansi[0]['judulLaporan'];

            $i = 0;
            foreach ($akuntansi as $index => $params) {


                $statusaktif = $params['statusaktif'];


                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $akuntansi[$i]['statusaktif'] = $statusaktif;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Tipe',
                    'index' => 'kodetype',
                ],
                [
                    'label' => 'Order',
                    'index' => 'order',
                ],
                [
                    'label' => 'Akuntansi',
                    'index' => 'akuntansi',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangantype',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $akuntansi, $columns);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('typeakuntansi')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    public function combostatus(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->nullable();
                $table->string('parameter', 50)->nullable();
                $table->string('param', 50)->nullable();
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }
}
