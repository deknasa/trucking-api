<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyMainAkunPusatRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreMainAkunPusatRequest;
use App\Http\Requests\UpdateMainAkunPusatRequest;
use App\Models\MainAkunPusat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class MainAkunPusatController extends Controller
{
    /**
     * @ClassName 
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
                'statuscoa' => $request->statuscoa,
                'statusaccountpayable' => $request->statusaccountpayable,
                'statusneraca' => $request->statusneraca,
                'statuslabarugi' => $request->statuslabarugi,
                'statusaktif' => $request->statusaktif,
            ];
            $mainAkunPusat = (new MainAkunPusat())->processStore($data);
            $mainAkunPusat->position = $this->getPosition($mainAkunPusat, $mainAkunPusat->getTable())->position;
            $mainAkunPusat->page = ceil($mainAkunPusat->position / ($request->limit ?? 10));

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
     */
    public function update(UpdateMainAkunPusatRequest $request, MainAkunPusat $mainakunpusat): JsonResponse
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
                'statuscoa' => $request->statuscoa,
                'statusaccountpayable' => $request->statusaccountpayable,
                'statusneraca' => $request->statusneraca,
                'statuslabarugi' => $request->statuslabarugi,
                'statusaktif' => $request->statusaktif,
            ];
            $mainAkunPusat = (new MainAkunPusat())->processUpdate($mainakunpusat, $data);
            $mainAkunPusat->position = $this->getPosition($mainAkunPusat, $mainAkunPusat->getTable())->position;
            $mainAkunPusat->page = ceil($mainAkunPusat->position / ($request->limit ?? 10));

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
     */
    public function destroy(DestroyMainAkunPusatRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $mainAkunPusat = (new MainAkunPusat())->processDestroy($id);
            $selected = $this->getPosition($mainAkunPusat, $mainAkunPusat->getTable(), true);
            $mainAkunPusat->position = $selected->position;
            $mainAkunPusat->id = $selected->id;
            $mainAkunPusat->page = ceil($mainAkunPusat->position / ($request->limit ?? 10));

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
     */
    public function report()
    {
    }



    /**
     * @ClassName 
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
                $statuscoa = $params['statuscoa'];
                $statusAkunPayable = $params['statusaccountpayable'];
                $statusNeraca = $params['statusneraca'];
                $statusLabaRugi = $params['statuslabarugi'];

                $result = json_decode($statusaktif, true);
                $resultStatuscoa = json_decode($statuscoa, true);
                $resultAkunPayable = json_decode($statusAkunPayable, true);
                $resultNeraca = json_decode($statusNeraca, true);
                $resultLabaRugi = json_decode($statusLabaRugi, true);

                $format = $result['MEMO'];
                $statusStatuscoa = $resultStatuscoa['MEMO'];
                $statusAkunPayable = $resultAkunPayable['MEMO'];
                $statusNeraca = $resultNeraca['MEMO'];
                $statusLabaRugi = $resultLabaRugi['MEMO'];


                $akunpusats[$i]['statusaktif'] = $format;
                $akunpusats[$i]['statuscoa'] = $statusStatuscoa;
                $akunpusats[$i]['statusaccountpayable'] = $statusAkunPayable;
                $akunpusats[$i]['statusneraca'] = $statusNeraca;
                $akunpusats[$i]['statuslabarugi'] = $statusLabaRugi;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'COA',
                    'index' => 'coa',
                ],
                [
                    'label' => 'Keterangan COA',
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
                    'label' => 'Status COA',
                    'index' => 'statuscoa',
                ],
                [
                    'label' => 'Status Account Payable',
                    'index' => 'statusaccountpayable',
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
        $mainAkunPusat = new MainAkunPusat();
        $cekdata = $mainAkunPusat->cekValidasi($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {

            $data = [
                'status' => true,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }
}
