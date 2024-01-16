<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPelangganRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Pelanggan;
use App\Http\Requests\StorePelangganRequest;
use App\Http\Requests\UpdatePelangganRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Models\Parameter;
use Illuminate\Http\JsonResponse;

class ShipperController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $pelanggan = new Pelanggan();

        return response([
            'data' => $pelanggan->get(),
            'attributes' => [
                'totalRows' => $pelanggan->totalRows,
                'totalPages' => $pelanggan->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $pelanggan = new Pelanggan();
        $cekdata = $pelanggan->cekvalidasihapus($id);
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

        $pelanggan = new Pelanggan();
        return response([
            'status' => true,
            'data' => $pelanggan->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePelangganRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodepelanggan' => $request->kodepelanggan,
                'namapelanggan' => $request->namapelanggan,
                'namakontak' => $request->namakontak,
                'telp' => $request->telp,
                'alamat' => $request->alamat,
                'alamat2' => $request->alamat2 ?? '',
                'kota' => $request->kota,
                'kodepos' => $request->kodepos,
                'keterangan' => $request->keterangan ?? '',
                'modifiedby' => auth('api')->user()->name,
                'statusaktif' => $request->statusaktif,
            ];
            $pelanggan = (new Pelanggan())->processStore($data);
            $pelanggan->position = $this->getPosition($pelanggan, $pelanggan->getTable())->position;
            if ($request->limit==0) {
                $pelanggan->page = ceil($pelanggan->position / (10));
            } else {
                $pelanggan->page = ceil($pelanggan->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pelanggan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Pelanggan $shipper)
    {
        return response([
            'status' => true,
            'data' => $shipper
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePelangganRequest $request, Pelanggan $shipper): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodepelanggan' => $request->kodepelanggan,
                'namapelanggan' => $request->namapelanggan,
                'namakontak' => $request->namakontak,
                'telp' => $request->telp,
                'alamat' => $request->alamat,
                'alamat2' => $request->alamat2 ?? '',
                'kota' => $request->kota,
                'kodepos' => $request->kodepos,
                'keterangan' => $request->keterangan ?? '',
                'modifiedby' => auth('api')->user()->name,
                'statusaktif' => $request->statusaktif,
            ];

            $pelanggan = (new Pelanggan())->processUpdate($shipper, $data);
            $pelanggan->position = $this->getPosition($pelanggan, $pelanggan->getTable())->position;
            if ($request->limit==0) {
                $pelanggan->page = ceil($pelanggan->position / (10));
            } else {
                $pelanggan->page = ceil($pelanggan->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $pelanggan
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
    public function destroy(DestroyPelangganRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $pelanggan = (new Pelanggan())->processDestroy($id);
            $selected = $this->getPosition($pelanggan, $pelanggan->getTable(), true);
            $pelanggan->position = $selected->position;
            $pelanggan->id = $selected->id;
            if ($request->limit==0) {
                $pelanggan->page = ceil($pelanggan->position / (10));
            } else {
                $pelanggan->page = ceil($pelanggan->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pelanggan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pelanggan')->getColumns();

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
            $pelanggans = $decodedResponse['data'];

            $judulLaporan = $pelanggans[0]['judulLaporan'];

            $i = 0;
            foreach ($pelanggans as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];

                $pelanggans[$i]['statusaktif'] = $statusaktif;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Shipper',
                    'index' => 'kodepelanggan',
                ],
                [
                    'label' => 'Nama Shipper',
                    'index' => 'namapelanggan',
                ],
                [
                    'label' => 'Alamat',
                    'index' => 'alamat',
                ],
                [
                    'label' => 'Telepon',
                    'index' => 'telp',
                ],
                [
                    'label' => 'Alamat2',
                    'index' => 'alamat2',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Kota',
                    'index' => 'kota',
                ],
                [
                    'label' => 'Kode Pos',
                    'index' => 'kodepos',
                ],
            ];

            // foreach ($columns as &$column) {
            //     if (isset($column['label'])) {
            //         $column['label'] = strtoupper($column['label']);
            //     }
            // }

            $this->toExcel($judulLaporan, $pelanggans, $columns);
        }
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
