<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTypeAkuntansiRequest;
use App\Http\Requests\UpdateTypeAkuntansiRequest;
use App\Models\TypeAkuntansi;
use App\Models\Parameter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class TypeAkuntansiController extends Controller
{
    
/**
     * @ClassName 
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
     */
    public function store(StoreTypeAkuntansiRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $typeakuntansi = (new TypeAkuntansi())->processStore($request->all());
            $typeakuntansi->position = $this->getPosition($typeakuntansi, $typeakuntansi->getTable())->position;
            $typeakuntansi->page = ceil($typeakuntansi->position / ($request->limit ?? 10));

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
    public function show(TypeAkuntansi $typeakuntansi)
    {
        return response([
            'status' => true,
            'data' => $typeakuntansi
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateTypeAkuntansiRequest $request, TypeAkuntansi $typeakuntansi): JsonResponse
    {
        DB::beginTransaction();

        try {
            $typeakuntansi = (new TypeAkuntansi())->processUpdate($typeakuntansi, $request->all());
            $typeakuntansi->position = $this->getPosition($typeakuntansi, $typeakuntansi->getTable())->position;
            $typeakuntansi->page = ceil($typeakuntansi->position / ($request->limit ?? 10));

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
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $typeakuntansi = (new TypeAkuntansi())->processDestroy($id);
            $selected = $this->getPosition($typeakuntansi, $typeakuntansi->getTable(), true);
            $typeakuntansi->position = $selected->position;
            $typeakuntansi->id = $selected->id;
            $typeakuntansi->page = ceil($typeakuntansi->position / ($request->limit ?? 10));

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
            $cabangs = $decodedResponse['data'];

            $judulLaporan = $cabangs[0]['judulLaporan'];

            $i = 0;
            foreach ($cabangs as $index => $params) {


                $statusaktif = $params['statusaktif'];


                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $cabangs[$i]['statusaktif'] = $statusaktif;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Cabang',
                    'index' => 'kodecabang',
                ],
                [
                    'label' => 'Nama Cabang',
                    'index' => 'namacabang',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $cabangs, $columns);
        }
    }

    public function fieldLength()
     {
         $data = [];
         $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('cabang')->getColumns();
 
         foreach ($columns as $index => $column) {
             $data[$index] = $column->getLength();
         }
 
         return response([
             'data' => $data
         ]);
     }


}