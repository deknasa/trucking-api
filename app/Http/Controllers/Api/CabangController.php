<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCabangRequest;
use App\Http\Requests\UpdateCabangRequest;
use App\Models\Cabang;
use App\Models\Parameter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class CabangController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $cabang = new Cabang();
        return response([
            'data' => $cabang->get(),
            'attributes' => [
                'totalRows' => $cabang->totalRows,
                'totalPages' => $cabang->totalPages
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

        $cabang = new Cabang();
        return response([
            'status' => true,
            'data' => $cabang->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreCabangRequest $request): JsonResponse
    {


        $data = [
            'id' => $request->id,
            'kodecabang' => $request->kodecabang,
            'namacabang' => $request->namacabang,
            'statusaktif' => $request->statusaktif,
        ];
        DB::beginTransaction();

        try {
            $cabang = (new Cabang())->processStore($data);
            $cabang->position = $this->getPosition($cabang, $cabang->getTable())->position;
            $cabang->page = ceil($cabang->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $cabang
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(Cabang $cabang)
    {
        return response([
            'status' => true,
            'data' => $cabang
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateCabangRequest $request, Cabang $cabang): JsonResponse
    {
        $data = [
            'id' => $request->id,
            'kodecabang' => $request->kodecabang,
            'namacabang' => $request->namacabang,
            'statusaktif' => $request->statusaktif,
        ];
        DB::beginTransaction();

        try {
            $cabang = (new Cabang())->processUpdate($cabang, $data);
            $cabang->position = $this->getPosition($cabang, $cabang->getTable())->position;
            $cabang->page = ceil($cabang->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah.',
                'data' => $cabang
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
            $cabang = (new Cabang())->processDestroy($id);
            $selected = $this->getPosition($cabang, $cabang->getTable(), true);
            $cabang->position = $selected->position;
            $cabang->id = $selected->id;
            $cabang->page = ceil($cabang->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $cabang
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
