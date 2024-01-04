<?php

namespace App\Http\Controllers\Api;

use App\Models\Container;
use App\Http\Requests\StoreContainerRequest;
use App\Http\Requests\UpdateContainerRequest;
use App\Http\Requests\DestroyContainerRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class ContainerController extends Controller
{


   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $container = new Container();

        return response([
            'data' => $container->get(),
            'attributes' => [
                'totalRows' => $container->totalRows,
                'totalPages' => $container->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $container = new Container();
        $cekdata = $container->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
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
        } else {
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

        $container = new Container();
        return response([
            'status' => true,
            'data' => $container->default(),
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreContainerRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodecontainer' => strtoupper($request->kodecontainer),
                'keterangan' => strtoupper($request->keterangan) ?? '',
                'nominalsumbangan' => $request->nominalsumbangan,
                'statusaktif' => $request->statusaktif,
            ];

            $container = (new container())->processStore($data);
            $container->position = $this->getPosition($container, $container->getTable())->position;
            if ($request->limit==0) {
                $container->page = ceil($container->position / (10));
            } else {
                $container->page = ceil($container->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $container
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show(Container $container)
    {
        return response([
            'status' => true,
            'data' => $container
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateContainerRequest $request, Container $container)
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodecontainer' => strtoupper($request->kodecontainer),
                'keterangan' => strtoupper($request->keterangan) ?? '',
                'nominalsumbangan' => $request->nominalsumbangan,
                'statusaktif' => $request->statusaktif,
            ];

            $container = (new Container())->processUpdate($container, $data);
            $container->position = $this->getPosition($container, $container->getTable())->position;
            if ($request->limit==0) {
                $container->page = ceil($container->position / (10));
            } else {
                $container->page = ceil($container->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $container
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $container = (new container())->processDestroy($id);
            $selected = $this->getPosition($container, $container->getTable(), true);
            $container->position = $selected->position;
            $container->id = $selected->id;
            if ($request->limit==0) {
                $container->page = ceil($container->position / (10));
            } else {
                $container->page = ceil($container->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $container
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('container')->getColumns();

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
            header('Access-Control-Allow-Origin: *');
            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $containers = $decodedResponse['data'];

            $judulLaporan = $containers[0]['judulLaporan'];
            $i = 0;
            foreach ($containers as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $containers[$i]['statusaktif'] = $statusaktif;

                $nominalsumbangan = number_format($params['nominalsumbangan'], 2, ',', '.');
                $containers[$i]['nominalsumbangan'] = $nominalsumbangan;

                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Container',
                    'index' => 'kodecontainer',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Nominal Sumbangan',
                    'index' => 'nominalsumbangan',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $containers, $columns);
        }
    }
}
