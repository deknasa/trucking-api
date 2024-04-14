<?php


namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\MainTypeAkuntansi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreMainTypeAkuntansiRequest;
use App\Http\Requests\UpdateMainTypeAkuntansiRequest;
use App\Http\Requests\DestroyMainTypeAkuntansiRequest;
use App\Models\MyModel;
use App\Models\Error;
use DateTime;


class MainTypeAkuntansiController extends Controller
{

    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {

        $maintypeakuntansi = new MainTypeAkuntansi();
        return response([
            'data' => $maintypeakuntansi->get(),
            'attributes' => [
                'totalRows' => $maintypeakuntansi->totalRows,
                'totalPages' => $maintypeakuntansi->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $dataMaster = MainTypeAkuntansi::where('id',$id)->first();
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

                    (new MyModel())->updateEditingBy('maintypeakuntansi', $id, $aksi);
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
                (new MyModel())->updateEditingBy('maintypeakuntansi', $id, $aksi);
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
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    public function default()
    {

        $maintypeakuntansi = new MainTypeAkuntansi();
        return response([
            'status' => true,
            'data' => $maintypeakuntansi->default(),
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreMainTypeAkuntansiRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $maintypeakuntansi = (new MainTypeAkuntansi())->processStore([
                "kodetype" => $request->kodetype,
                "order" => $request->order,
                "keterangantype" => $request->keterangantype,
                "akuntansi_id" => $request->akuntansi_id,
                "statusaktif" => $request->statusaktif,
            ]);
            $maintypeakuntansi->position = $this->getPosition($maintypeakuntansi, $maintypeakuntansi->getTable())->position;
            if ($request->limit == 0) {
                $maintypeakuntansi->page = ceil($maintypeakuntansi->position / (10));
            } else {
                $maintypeakuntansi->page = ceil($maintypeakuntansi->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $maintypeakuntansi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function show($id)
    {
        $maintypeakuntansi = new MainTypeAkuntansi();
        return response([
            'status' => true,
            'data' => $maintypeakuntansi->find($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateMainTypeAkuntansiRequest $request, MainTypeAkuntansi $maintypeakuntansi): JsonResponse
    {
        DB::beginTransaction();

        try {
            $maintypeakuntansi = (new MainTypeAkuntansi())->processUpdate($maintypeakuntansi, [
                "kodetype" => $request->kodetype,
                "order" => $request->order,
                "keterangantype" => $request->keterangantype,
                "akuntansi_id" => $request->akuntansi_id,
                "statusaktif" => $request->statusaktif,
            ]);
            $maintypeakuntansi->position = $this->getPosition($maintypeakuntansi, $maintypeakuntansi->getTable())->position;
            if ($request->limit == 0) {
                $maintypeakuntansi->page = ceil($maintypeakuntansi->position / (10));
            } else {
                $maintypeakuntansi->page = ceil($maintypeakuntansi->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah.',
                'data' => $maintypeakuntansi
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
    public function destroy(DestroyMainTypeAkuntansiRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $maintypeakuntansi = (new MainTypeAkuntansi())->processDestroy($id);
            $selected = $this->getPosition($maintypeakuntansi, $maintypeakuntansi->getTable(), true);
            $maintypeakuntansi->position = $selected->position;
            $maintypeakuntansi->id = $selected->id;
            if ($request->limit == 0) {
                $maintypeakuntansi->page = ceil($maintypeakuntansi->position / (10));
            } else {
                $maintypeakuntansi->page = ceil($maintypeakuntansi->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $maintypeakuntansi
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
            (new MainTypeAkuntansi())->processApprovalnonaktif($data);

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
            $maintypeakuntansi = $decodedResponse['data'];

            $judulLaporan = $maintypeakuntansi[0]['judulLaporan'];

            $i = 0;
            foreach ($maintypeakuntansi as $index => $params) {


                $statusaktif = $params['statusaktif'];


                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $maintypeakuntansi[$i]['statusaktif'] = $statusaktif;
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

            $this->toExcel($judulLaporan, $maintypeakuntansi, $columns);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('maintypeakuntansi')->getColumns();

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
