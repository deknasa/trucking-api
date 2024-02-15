<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Models\TarifDiscountHarga;
use App\Http\Requests\StoreTarifDiscountHargaRequest;
use App\Http\Requests\UpdateTarifDiscountHargaRequest;
use App\Http\Requests\DestroyTarifDiscountHargaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Container;
use App\Models\Tarif;
use App\Models\TarifRincian;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\GetUpahSupirRangeRequest;
use App\Http\Requests\RangeExportReportRequest;

class TarifDiscountHargaController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $tarifDiscountHarga = new TarifDiscountHarga();
        return response([
            'data' => $tarifDiscountHarga->get(),
            'attributes' => [
                'totalRows' => $tarifDiscountHarga->totalRows,
                'totalPages' => $tarifDiscountHarga->totalPages
            ]
        ]);
    }

    public function default()
    {
        $tarifDiscountHarga = new TarifDiscountHarga();
        return response([
            'status' => true,
            'data' => $tarifDiscountHarga->default()
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreTarifDiscountHargaRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tarif_id' => $request->tarif_id,
                'container_id' => $request->container_id,
                'tujuanbongkar' => $request->tujuanbongkar,
                'lokasidooring' => $request->lokasidooring,
                'lokasidooring_id' => $request->lokasidooring_id,
                'shipper' => $request->shipper,
                'nominal' => $request->nominal,
                'cabang' => $request->cabang,
                'statuscabang' => $request->statuscabang,
                'statusaktif' => $request->statusaktif,
            ];
            $tarifDiscountHarga = (new TarifDiscountHarga())->processStore($data);
            $tarifDiscountHarga->position = $this->getPosition($tarifDiscountHarga, $tarifDiscountHarga->getTable())->position;
            if ($request->limit == 0) {
                $tarifDiscountHarga->page = ceil($tarifDiscountHarga->position / (10));
            } else {
                $tarifDiscountHarga->page = ceil($tarifDiscountHarga->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $tarifDiscountHarga
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $tarifDiscountHarga = (new TarifDiscountHarga)->findAll($id);

        return response([
            'status' => true,
            'data' => $tarifDiscountHarga
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTarifDiscountHargaRequest $request, TarifDiscountHarga $tarifdiscountharga): JsonResponse
    {
        DB::beginTransaction();

        try {

            $data = [
                'tarif_id' => $request->tarif_id,
                'container_id' => $request->container_id,
                'tujuanbongkar' => $request->tujuanbongkar,
                'lokasidooring' => $request->lokasidooring,
                'lokasidooring_id' => $request->lokasidooring_id,
                'shipper' => $request->shipper,
                'nominal' => $request->nominal,
                'cabang' => $request->cabang,
                'statuscabang' => $request->statuscabang,
                'statusaktif' => $request->statusaktif,
            ];
            $tarifDiscountHarga = (new TarifDiscountHarga())->processUpdate($tarifdiscountharga, $data);
            $tarifDiscountHarga->position = $this->getPosition($tarifDiscountHarga, $tarifDiscountHarga->getTable())->position;
            if ($request->limit == 0) {
                $tarifDiscountHarga->page = ceil($tarifDiscountHarga->position / (10));
            } else {
                $tarifDiscountHarga->page = ceil($tarifDiscountHarga->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $tarifDiscountHarga
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
    public function destroy(DestroyTarifDiscountHargaRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $tarifDiscountHarga = (new TarifDiscountHarga())->processDestroy($id);
            $selected = $this->getPosition($tarifDiscountHarga, $tarifDiscountHarga->getTable(), true);
            $tarifDiscountHarga->position = $selected->position;
            $tarifDiscountHarga->id = $selected->id;
            if ($request->limit == 0) {
                $tarifDiscountHarga->page = ceil($tarifDiscountHarga->position / (10));
            } else {
                $tarifDiscountHarga->page = ceil($tarifDiscountHarga->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $tarifDiscountHarga
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('tarifdiscountharga')->getColumns();

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
            $tarifs = $decodedResponse['data'];
            $judulLaporan = $tarifs[0]['judulLaporan'];

            $i = 0;
            foreach ($tarifs as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statuscabang = $params['statuscabang'];

                $result = json_decode($statusaktif, true);
                $resultcabang = json_decode($statuscabang, true);

                $statusaktif = $result['MEMO'];
                $statuscabang = $resultcabang['MEMO'];


                $tarifs[$i]['statusaktif'] = $statusaktif;
                $tarifs[$i]['statuscabang'] = $statuscabang;


                $i++;
            }
            
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Tujuan',
                    'index' => 'tujuan',
                ],
                [
                    'label' => 'Penyesuaian',
                    'index' => 'penyesuaian',
                ],
                [
                    'label' => 'Lokasi Dooring',
                    'index' => 'lokasidooring',
                ],
                [
                    'label' => 'Container',
                    'index' => 'container',
                ],
                [
                    'label' => 'Shipper',
                    'index' => 'shipper',
                ],
                [
                    'label' => 'Nominal',
                    'index' => 'nominal',
                ],
                [
                    'label' => 'Status Cabang',
                    'index' => 'statuscabang',
                ],
                [
                    'label' => 'Status',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $tarifs, $columns);
        }
    }
    public function combo(Request $request)
    {

        // dd($request->all());


        // $pilih = $request->status ?? '';
        // $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // $temp1 = '##temp1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        // if ($pilih == 'list') {

        //     Schema::create($temp, function ($table) {
        //         $table->integer('id')->length(11)->nullable();
        //         $table->string('parameter', 50)->nullable();
        //         $table->string('param', 50)->nullable();
        //     });

        //     DB::table($temp)->insert(
        //         [
        //             'id' => '0',
        //             'parameter' => 'ALL',
        //             'param' => '',
        //         ]
        //     );

        //     $queryall = Parameter::select('id', 'text as parameter', 'text as param')
        //         ->where('grp', "=", 'status aktif')
        //         ->where('subgrp', "=", 'status aktif');

        //     $query = DB::table($temp)
        //         ->unionAll($queryall);

        //     Schema::create($temp1, function ($table) {
        //         $table->integer('id')->length(11)->nullable();
        //         $table->string('parameter', 50)->nullable();
        //         $table->string('param', 50)->nullable();
        //     });

        //     DB::table($temp1)->insert(
        //         [
        //             'id' => '0',
        //             'parameter' => 'ALL',
        //             'param' => '',
        //         ]
        //     );

        //     $queryall1 = Parameter::select('id', 'text as parameter', 'text as param')
        //         ->where('grp', "=", 'status cabang')
        //         ->where('subgrp', "=", 'status cabang');

        //     $query1 = DB::table($temp1)
        //         ->unionAll($queryall1);

        //     $data = [
        //         'status' => $query->get(),
        //         'statuscabang' => $query1->get(),
        //     ];
        // } else {
        //     $data = [
        //         'status' => Parameter::where(['grp' => 'status aktif'])->get(),
        //         'statuscabang' => Parameter::where(['grp' => 'status cabang'])->get(),
        //     ];
        // }

        $data = [
            'status' => Parameter::where(['grp' => 'status aktif'])->get(),
            'statuscabang' => Parameter::where(['grp' => 'status cabang'])->get(),
        ];


        return response([
            'data' => $data
        ]);
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
           (new TarifDiscountHarga())->processApprovalnonaktif($data);

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
