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

class PelangganController extends Controller
{
    /**
     * @ClassName 
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
     */
    public function store(StorePelangganRequest $request)
    {
        DB::beginTransaction();

        try {
            $pelanggan = new Pelanggan();
            $pelanggan->kodepelanggan = $request->kodepelanggan;
            $pelanggan->namapelanggan = $request->namapelanggan;
            $pelanggan->telp = $request->telp;
            $pelanggan->alamat = $request->alamat;
            $pelanggan->alamat2 = $request->alamat2 ?? '';
            $pelanggan->kota = $request->kota;
            $pelanggan->kodepos = $request->kodepos;
            $pelanggan->keterangan = $request->keterangan ?? '';
            $pelanggan->modifiedby = auth('api')->user()->name;
            $pelanggan->statusaktif = $request->statusaktif;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($pelanggan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pelanggan->getTable()),
                    'postingdari' => 'ENTRY PELANGGAN',
                    'idtrans' => $pelanggan->id,
                    'nobuktitrans' => $pelanggan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $pelanggan->toArray(),
                    'modifiedby' => $pelanggan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($pelanggan, $pelanggan->getTable());
            $pelanggan->position = $selected->position;
            $pelanggan->page = ceil($pelanggan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pelanggan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Pelanggan $pelanggan)
    {
        return response([
            'status' => true,
            'data' => $pelanggan
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdatePelangganRequest $request, Pelanggan $pelanggan)
    {
        DB::beginTransaction();

        try {
            $pelanggan->kodepelanggan = $request->kodepelanggan;
            $pelanggan->namapelanggan = $request->namapelanggan;
            $pelanggan->telp = $request->telp;
            $pelanggan->alamat = $request->alamat;
            $pelanggan->alamat2 = $request->alamat2 ?? '';
            $pelanggan->kota = $request->kota;
            $pelanggan->kodepos = $request->kodepos;
            $pelanggan->keterangan = $request->keterangan ?? '';
            $pelanggan->statusaktif = $request->statusaktif;
            $pelanggan->modifiedby = auth('api')->user()->name;

            if ($pelanggan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pelanggan->getTable()),
                    'postingdari' => 'EDIT PELANGGAN',
                    'idtrans' => $pelanggan->id,
                    'nobuktitrans' => $pelanggan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $pelanggan->toArray(),
                    'modifiedby' => $pelanggan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);


                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($pelanggan, $pelanggan->getTable());
            $pelanggan->position = $selected->position;
            $pelanggan->page = ceil($pelanggan->position / ($request->limit ?? 10));

            return response([
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
     */
    public function destroy(DestroyPelangganRequest $request, $id)
    {
        DB::beginTransaction();

        $pelanggan = new Pelanggan();
        $pelanggan = $pelanggan->lockAndDestroy($id);

        if ($pelanggan) {
            $logTrail = [
                'namatabel' => strtoupper($pelanggan->getTable()),
                'postingdari' => 'DELETE PARAMETER',
                'idtrans' => $pelanggan->id,
                'nobuktitrans' => $pelanggan->id,
                'aksi' => 'DELETE',
                'datajson' => $pelanggan->toArray(),
                'modifiedby' => $pelanggan->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($pelanggan, $pelanggan->getTable(), true);
            $pelanggan->position = $selected->position;
            $pelanggan->id = $selected->id;
            $pelanggan->page = ceil($pelanggan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pelanggan
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
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



    public function export(RangeExportReportRequest $request)
    {

        if (request()->cekExport) {
            return response([
                'status' => true,
            ]);
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
                    'label' => 'ID',
                    'index' => 'id',
                ],
                [
                    'label' => 'Kode Pelanggan',
                    'index' => 'kodepelanggan',
                ],
                [
                    'label' => 'Nama Pelanggan',
                    'index' => 'namapelanggan',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Telp',
                    'index' => 'telp',
                ],
                [
                    'label' => 'Alamat',
                    'index' => 'alamat',
                ],
                [
                    'label' => 'Alamat2',
                    'index' => 'alamat2',
                ],
                [
                    'label' => 'Kota',
                    'index' => 'kota',
                ],
                [
                    'label' => 'Kode Pos',
                    'index' => 'kodepos',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
            ];

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
