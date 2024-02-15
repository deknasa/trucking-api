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
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\DestroyCabangRequest;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class CabangController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $cabang = new Cabang();
        // dd(system('getmac'));
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
     * @Keterangan CETAK DATA
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
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreCabangRequest $request): JsonResponse
    {


        $data = [
            'id' => $request->id,
            'kodecabang' => $request->kodecabang,
            'namacabang' => $request->namacabang,
            'statusaktif' => $request->statusaktif,
            'tas_id' => $request->tas_id ?? '',
            "key" => $request->key,
            "value" => $request->value,
            "accessTokenTnl" => $request->accessTokenTnl ?? '',
        ];
        // dd($data);
        DB::beginTransaction();

        try {
            $cabang = (new Cabang())->processStore($data);
            if ($request->from == '') {
                $cabang->position = $this->getPosition($cabang, $cabang->getTable())->position;
                if ($request->limit == 0) {
                    $cabang->page = ceil($cabang->position / (10));
                } else {
                    $cabang->page = ceil($cabang->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $cabang->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('cabang', 'add', $data);
            }

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

    public function show($id)
    {
        return response([
            'status' => true,
            'data' => (new Cabang())->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateCabangRequest $request, Cabang $cabang): JsonResponse
    {
        $data = [
            'id' => $request->id,
            'kodecabang' => $request->kodecabang,
            'namacabang' => $request->namacabang,
            'statusaktif' => $request->statusaktif,
            "key" => $request->key,
            "value" => $request->value,
            "accessTokenTnl" => $request->accessTokenTnl ?? '',
        ];
        DB::beginTransaction();

        try {
            $cabang = (new Cabang())->processUpdate($cabang, $data);
            if ($request->from == '') {
                $cabang->position = $this->getPosition($cabang, $cabang->getTable())->position;
                if ($request->limit == 0) {
                    $cabang->page = ceil($cabang->position / (10));
                } else {
                    $cabang->page = ceil($cabang->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $cabang->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('cabang', 'edit', $data);
            }

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
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $cabang = (new Cabang())->processDestroy($id);

            if ($request->from == '') {
                $selected = $this->getPosition($cabang, $cabang->getTable(), true);
                $cabang->position = $selected->position;
                $cabang->id = $selected->id;
                if ($request->limit == 0) {
                    $cabang->page = ceil($cabang->position / (10));
                } else {
                    $cabang->page = ceil($cabang->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;

            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('cabang', 'delete', $data);
            }
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
     * @Keterangan APPROVAL KONEKSI
     */
    public function approvalKonensi(Request $request, Cabang $cabang)
    {
        DB::beginTransaction();

        try {
            $cabang = (new Cabang())->procesApprovalKonensi(
                $cabang
            );
            DB::commit();
            return response()->json([
                'message' => 'Berhasil Diubah',
                'data' => $cabang
            ]);
        } catch (\Throwable $th) {
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
            (new Cabang())->processApprovalnonaktif($data);

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
