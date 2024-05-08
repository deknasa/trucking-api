<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\DestroyTarifTangkiRequest;
use App\Http\Requests\StoreTarifTangkiRequest;
use App\Http\Requests\UpdateTarifTangkiRequest;
use App\Models\TarifTangki;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class TarifTangkiController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $tarifTangki = new TarifTangki();

        return response([
            'data' => $tarifTangki->get(),
            'attributes' => [
                'totalRows' => $tarifTangki->totalRows,
                'totalPages' => $tarifTangki->totalPages
            ]
        ]);
    }

    public function default()
    {
        $tarifTangki = new TarifTangki();
        return response([
            'status' => true,
            'data' => $tarifTangki->default()
        ]);
    }

    public function cekValidasi($id)
    {
        $tarifTangki = new TarifTangki();
        $dataMaster = $tarifTangki->where('id', $id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $cekdata = $tarifTangki->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true && $aksi != 'EDIT') {
            $keterangan = $error->cekKeteranganError('SATL') ?? '';

            $data = [
                'status' => false,
                'message' => $keterangan . " (" . $cekdata['keterangan'] . ")",
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
                    (new MyModel())->updateEditingBy('tariftangki', $id, $aksi);
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
                $keterror = 'Data <b>' . $dataMaster->keterangan . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;

                $data = [
                    'status' => true,
                    'message' => $keterror,
                    'errors' => '',
                    'kondisi' => true,
                    'editblok' => true,
                ];

                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('tariftangki', $id, $aksi);
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
    public function store(StoreTarifTangkiRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'parent_id' => $request->parent_id ?? '',
                'upahsupirtangki_id' => $request->upahsupirtangki_id ?? '',
                'tujuan' => $request->tujuan ?? '',
                'penyesuaian' => $request->penyesuaian ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                'kota_id' => $request->kota_id ?? '',
                'tglmulaiberlaku' => $request->tglmulaiberlaku ?? '',
                'statuspenyesuaianharga' => $request->statuspenyesuaianharga ?? '',
                'statuspostingtnl' => $request->statuspostingtnl ?? '',
                'keterangan' => $request->keterangan ?? '',
                'nominal' => $request->nominal ?? '',
                'tas_id' => $request->tas_id ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            $tarifTangki = (new TarifTangki())->processStore($data);

            if ($request->from == '') {
                $tarifTangki->position = $this->getPosition($tarifTangki, $tarifTangki->getTable())->position;
                if ($request->limit == 0) {
                    $tarifTangki->page = ceil($tarifTangki->position / (10));
                } else {
                    $tarifTangki->page = ceil($tarifTangki->position / ($request->limit ?? 10));
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $tarifTangki
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
            'data' => (new TarifTangki())->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTarifTangkiRequest $request, TarifTangki $tariftangki): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'parent_id' => $request->parent_id ?? '',
                'upahsupirtangki_id' => $request->upahsupirtangki_id ?? '',
                'tujuan' => $request->tujuan ?? '',
                'penyesuaian' => $request->penyesuaian ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                'kota_id' => $request->kota_id ?? '',
                'tglmulaiberlaku' => $request->tglmulaiberlaku ?? '',
                'statuspenyesuaianharga' => $request->statuspenyesuaianharga ?? '',
                'statuspostingtnl' => $request->statuspostingtnl ?? '',
                'keterangan' => $request->keterangan ?? '',
                'nominal' => $request->nominal ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];

            $tarifTangki = (new TarifTangki())->processUpdate($tariftangki, $data);
            if ($request->from == '') {
                $tarifTangki->position = $this->getPosition($tarifTangki, $tarifTangki->getTable())->position;
                if ($request->limit == 0) {
                    $tarifTangki->page = ceil($tarifTangki->position / (10));
                } else {
                    $tarifTangki->page = ceil($tarifTangki->position / ($request->limit ?? 10));
                }
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $tarifTangki
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
    public function destroy(DestroyTarifTangkiRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $tarifTangki = (new TarifTangki())->processDestroy($id);

            if ($request->from == '') {
                $selected = $this->getPosition($tarifTangki, $tarifTangki->getTable(), true);
                $tarifTangki->position = $selected->position;
                $tarifTangki->id = $selected->id;
                if ($request->limit == 0) {
                    $tarifTangki->page = ceil($tarifTangki->position / (10));
                } else {
                    $tarifTangki->page = ceil($tarifTangki->position / ($request->limit ?? 10));
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $tarifTangki
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('tariftangki')->getColumns();

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
    public function export()
    {
        // $cekData = DB::table("tariftangki")->from(DB::raw("tariftangki with (readuncommitted)"))
        //     ->whereBetween('tglmulaiberlaku', [date('Y-m-d', strtotime(request()->dari)), date('Y-m-d', strtotime(request()->sampai))])
        //     ->first();
        // if ($cekData == null) {
        //     return response([
        //         'errors' => [
        //             "sampai" => [
        //                 0 => "tidak ada data"
        //             ]
        //         ],
        //         'message' => "The given data was invalid.",
        //     ], 422);
        // } else {

            $response = (new TarifTangki())->export(request()->dari, request()->sampai);
            $triptangkis = json_decode($response, true);

            return response([
                'data' => $triptangkis
            ]);
        // }
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
            (new TarifTangki())->processApprovalnonaktif($data);

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
